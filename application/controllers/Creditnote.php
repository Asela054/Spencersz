<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Creditnote extends CI_Controller
{
    public function __construct(){
        parent::__construct();
        $this->load->model("Commeninfo");
    }
    public function index(){
        $result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
        $this->load->view('creditnote', $result);
    }
    public function Searchinvoice(){
        $company_id=$_SESSION['company_id'];
        $no = trim((string)$this->input->post('invoice_no'));

        $inv = $this->db->where('invoice_no', $no)->where('status', 1)
                        ->where('invoice_type !=', 'Corporate Quotation')
                        ->where('tbl_company_idtbl_company', $company_id)
                        ->get('tbl_invoice')->row();
        if (!$inv) return $this->_jsonResponse(0, 'fas fa-exclamation-triangle', 'Invoice not found', 'danger');

        echo json_encode([
            'status'  => 1,
            'invoice' => [
                'id' => (int)$inv->idtbl_invoice, 'invoice_no' => $inv->invoice_no,
                'invoice_date' => $inv->invoice_date, 'customer_name' => $inv->customer_name,
                'has_customer' => !empty($inv->tbl_customer_idtbl_customer),
                'net_total' => (float)$inv->net_total, 'vat_rate' => (float)$inv->vat_rate,
            ],
            'lines' => $this->_creditable_lines($inv),
        ]);
    }
    public function Insertcreditnote(){
        $inTrans   = false;
        $old_debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;

        try {
            $in = json_decode($this->input->post('creditnote'), true);
            if (!is_array($in) || empty($in['invoiceId']) || empty($in['items']))
                throw new RuntimeException('Select at least one item to return');

            $userid=$_SESSION['userid'];
            $company_id=$_SESSION['company_id'];
            $branch_id=$_SESSION['branch_id'];
            $now        = date('Y-m-d H:i:s');
            $today      = date('Y-m-d');

            $inv = $this->db->where('idtbl_invoice', (int)$in['invoiceId'])->where('status', 1)
                            ->where('invoice_type !=', 'Corporate Quotation')
                            ->where('tbl_company_idtbl_company', $company_id)
                            ->get('tbl_invoice')->row();
            if (!$inv) throw new RuntimeException('Invoice not found');

            // ----- validate every line against the DB (the browser values are never trusted) -----
            $byId = [];
            foreach ($this->_creditable_lines($inv) as $l) $byId[$l->idtbl_invoice_detail] = $l;

            $rows = []; $seen = []; $total = 0;
            foreach ($in['items'] as $it) {
                $did = (int)$it['detailId'];
                if (!isset($byId[$did]))  throw new RuntimeException('Invalid item');
                if (isset($seen[$did]))   throw new RuntimeException('Item added twice');
                $seen[$did] = true;

                $l   = $byId[$did];
                $qty = $l->serialno ? 1 : (int)$it['qty'];            // a serial item is always 1
                if ($qty <= 0) continue;
                if ($qty > $l->returnable_qty)
                    throw new RuntimeException($l->product_name . ': only ' . $l->returnable_qty . ' can be returned');

                $amt = round($l->paid_unit * $qty, 2);
                $total += $amt;
                $rows[] = ['l' => $l, 'qty' => $qty, 'amt' => $amt,
                           'restock' => !empty($it['restock']), 'remark' => mb_substr(trim((string)$it['remark']), 0, 250)];
            }
            if (!$rows) throw new RuntimeException('Select at least one item to return');

            // credit amount is VAT inclusive, same as the invoice
            $rate  = 1 + ((float)$inv->vat_rate / 100);
            $total = round($total, 2);
            $vat   = round($total - ($total / $rate), 2);
            $sub   = round($total - $vat, 2);

            // ----- save (all or nothing) -----
            $this->db->trans_begin();
            $inTrans = true;

            // number: 26OCT_CN03_00001 (own series, QT-style, so tax invoice numbers are not affected)
            $code4  = $this->_branch_code4($branch_id);
            $prefix = date('y') . strtoupper(date('M')) . '_CN' . substr($code4, -2) . '_';
            $last = $this->db->query(
                "SELECT MAX(CAST(SUBSTRING(creditnote_no, ?) AS UNSIGNED)) AS m
                   FROM tbl_credit_note WHERE creditnote_no LIKE ? FOR UPDATE",
                [strlen($prefix) + 1, str_replace('_', '\_', $prefix) . '%']
            )->row();
            $cn_no = $prefix . str_pad(((int)$last->m) + 1, 5, '0', STR_PAD_LEFT);

            $this->db->insert('tbl_credit_note', [
                'creditnote_no' => $cn_no, 'creditnote_date' => $today,
                'subtotal' => $sub, 'vatamount' => $vat, 'totalamount' => $total,
                'settle_status' => 0, 'settle_invoice_id' => 0, 'status' => 1,
                'insertdatetime' => $now, 'updateuser' => $userid, 'updatedatetime' => $now,
                'tbl_user_idtbl_user' => $userid, 'tbl_invoice_idtbl_invoice' => $inv->idtbl_invoice,
            ]);
            $cn_id = $this->db->insert_id();
            if (!$cn_id) throw new Exception('Credit note insert failed');

            foreach ($rows as $r) {
                $l = $r['l'];

                // early check, so the employee sees the problem now (nothing is changed yet)
                if ($l->serialno) {
                    $s = $this->db->where('serialno', $l->serialno)->where('tbl_product_idtbl_product', $l->product_id)
                                ->where('sold_invoice_id', $inv->idtbl_invoice)->where('stock_status', 2)
                                ->get('tbl_product_serial')->row();
                    if (!$s) throw new RuntimeException($l->product_name . ' (' . $l->serialno . '): serial is not in sold status');
                }

                $this->db->insert('tbl_credit_note_detail', [
                    'serialno' => $l->serialno, 'qty' => $r['qty'], 'unitprice' => $l->paid_unit, 'total' => $r['amt'],
                    'restock' => $l->serialno ? 0 : ($r['restock'] ? 1 : 0),
                    'remark' => $r['remark'], 'status' => 1,
                    'insertdatetime' => $now, 'updateuser' => $userid, 'updatedatetime' => $now,
                    'tbl_user_idtbl_user' => $userid,
                    'tbl_credit_note_idtbl_credit_note' => $cn_id,
                    'tbl_product_idtbl_product' => $l->product_id,
                    'tbl_invoice_detail_idtbl_invoice_detail' => $l->idtbl_invoice_detail,
                ]);
            }

            if ($this->db->trans_status() === FALSE) {
                $err = $this->db->error();
                throw new Exception('DB error: ' . $err['code'] . ' ' . $err['message']);
            }
            $this->db->trans_commit();
            $inTrans = false;

            return $this->_jsonResponse(1, 'fas fa-check-circle', 'Credit note ' . $cn_no . ' created and sent for approval', 'success',
                ['creditnote_no' => $cn_no, 'creditnote_id' => $cn_id, 'total' => $total]);

        } catch (RuntimeException $e) {
            if ($inTrans) $this->db->trans_rollback();
            return $this->_jsonResponse(0, 'fas fa-exclamation-triangle', $e->getMessage(), 'danger');
        } catch (Throwable $e) {
            if ($inTrans) $this->db->trans_rollback();
            log_message('error', 'Insertcreditnote failed: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            return $this->_jsonResponse(0, 'fas fa-exclamation-triangle', 'Could not create the credit note. Please try again.'.$e->getMessage(), 'danger');
        } finally {
            $this->db->db_debug = $old_debug;
        }
    }
    public function Creditnoteview(){
        $cn = $this->_load_creditnote((int)$this->input->post('recordID'));
        if (!$cn) { echo '<div class="alert alert-danger">Credit note not found</div>'; return; }

        $items = $this->_creditnote_items($cn->idtbl_credit_note);
        $fm = function ($n) { return number_format((float)$n, 2); };

        echo '<div class="row small mb-2">'
        . '<div class="col-md-6"><div><b>Credit Note No:</b> ' . html_escape($cn->creditnote_no) . '</div>'
        . '<div><b>Date:</b> ' . html_escape($cn->creditnote_date) . '</div>'
        . '<div><b>Status:</b> ' . ($cn->settle_status ? 'Used on ' . html_escape($cn->settle_invoice_no) : 'Open') . '</div></div>'
        . '<div class="col-md-6"><div><b>Original Invoice:</b> ' . html_escape($cn->invoice_no) . ' (' . html_escape($cn->invoice_date) . ')</div>'
        . '<div><b>Customer:</b> ' . html_escape($cn->customer_name) . '</div></div></div>';

        echo '<table class="table table-striped table-bordered table-sm small"><thead><tr>'
        . '<th>Item Name</th><th>Serial</th><th class="text-center">Qty</th><th class="text-right">Unit Price</th>'
        . '<th>Reason</th><th class="text-right">Total</th></tr></thead><tbody>';
        foreach ($items as $it) {
            echo '<tr><td>' . html_escape($it->product_name) . '</td><td>' . html_escape($it->serialno) . '</td>'
            . '<td class="text-center">' . (int)$it->qty . '</td><td class="text-right">' . $fm($it->unitprice) . '</td>'
            . '<td>' . html_escape($it->remark) . '</td><td class="text-right">' . $fm($it->total) . '</td></tr>';
        }
        echo '</tbody></table>'
        . '<div class="text-right small">Subtotal (excl. VAT): <b>' . $fm($cn->subtotal) . '</b><br>'
        . 'VAT: <b>' . $fm($cn->vatamount) . '</b><br>'
        . '<span class="h6">Credit Total: <b>Rs. ' . $fm($cn->totalamount) . '</b></span></div>'
        . '<div class="alert alert-' . ($cn->approve_status == 1 ? 'success' : ($cn->approve_status == 2 ? 'danger' : 'warning')) . ' mt-2" role="alert"><b>Approval:</b> ' . ($cn->approve_status == 1 ? 'Approved on ' . html_escape($cn->approve_datetime) : ($cn->approve_status == 2 ? 'Rejected: ' . html_escape($cn->approve_remark) : 'Pending')) . '</div>';
    }
    public function Printcreditnote($id = 0){
        $cn = $this->_load_creditnote((int)$id);
        if (!$cn) show_404();

        $logoFile = FCPATH . 'assets/images/logo.png';   // CHANGE: same logo as the invoice
        $html = $this->load->view('creditnote_pdf', [
            'cn'    => $cn,
            'items' => $this->_creditnote_items($cn->idtbl_credit_note),
            'rate'  => 1 + ((float)$cn->vat_rate / 100),
            'logo'  => is_file($logoFile) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile)) : '',
            'company' => ['name' => 'Spencersz Lanka (Pvt) Ltd', 'address' => '295, Negombo Road, Wattala',
                        'tin' => '000000000-7000', 'tel' => '+94 11 000 0000'],   // CHANGE: same values as Printinvoice
        ], TRUE);

        $this->load->library('pdf');
        $this->pdf->loadHtml($html);
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->render();
        if (ob_get_length()) ob_end_clean();
        $this->pdf->stream('CREDIT-NOTE-' . $cn->creditnote_no . '.pdf', ['Attachment' => 0]);
    }  
    public function Creditnotestatus(){
        $inTrans   = false;
        $old_debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;

        try {
            // CHANGE: add the same "approve permission" check you use in Purchaseorder/Purchaseorderstatus.
            // Hiding the button in the page is not enough, the server must check it too.

            $userid=$_SESSION['userid'];
            $company_id=$_SESSION['company_id'];
            $branch_id=$_SESSION['branch_id'];
            $now       = date('Y-m-d H:i:s');
            $id        = (int)$this->input->post('cnid');
            $decision  = (int)$this->input->post('confirmnot');          // 1 = approve, 2 = reject
            $remark    = mb_substr(trim((string)$this->input->post('remark')), 0, 250);

            if (!in_array($decision, [1, 2], true)) throw new RuntimeException('Invalid request');
            if ($decision === 2 && $remark === '')   throw new RuntimeException('Please enter the reject reason');

            $this->db->trans_begin();
            $inTrans = true;

            // lock the row: two approvers cannot act on the same credit note at once
            $cn = $this->db->query(
                "SELECT cn.* FROM tbl_credit_note cn
                JOIN tbl_invoice i ON i.idtbl_invoice = cn.tbl_invoice_idtbl_invoice
                WHERE cn.idtbl_credit_note = ? AND cn.status = 1
                    AND i.tbl_company_branch_idtbl_company_branch = ? FOR UPDATE", [$id, $branch_id])->row();
            if (!$cn) throw new RuntimeException('Credit note not found');
            if ((int)$cn->approve_status !== 0)
                throw new RuntimeException('This credit note is already ' . ($cn->approve_status == 1 ? 'approved' : 'rejected'));

            if ($decision === 1) {
                if (!$this->_has_permission('Creditnote', 5))
                    throw new RuntimeException('You do not have permission to approve credit notes');

                $lines = $this->db->where('tbl_credit_note_idtbl_credit_note', $id)->where('status', 1)
                                ->get('tbl_credit_note_detail')->result();
                foreach ($lines as $l) {
                    if ($l->serialno) {
                        // serial: 2 (sold) -> 5 (returned by customer)
                        $s = $this->db->where('serialno', $l->serialno)
                                    ->where('tbl_product_idtbl_product', $l->tbl_product_idtbl_product)
                                    ->where('sold_invoice_id', $cn->tbl_invoice_idtbl_invoice)
                                    ->where('stock_status', 2)->get('tbl_product_serial')->row();
                        if (!$s) throw new RuntimeException($l->serialno . ': serial is not in sold status any more');

                        $this->db->where('idtbl_product_serial', $s->idtbl_product_serial)->where('stock_status', 2)
                                ->update('tbl_product_serial', ['stock_status' => 5, 'updateuser' => $userid, 'updatedatetime' => $now]);
                        if ($this->db->affected_rows() !== 1)
                            throw new RuntimeException($l->serialno . ': serial status changed, try again');

                        $this->db->insert('tbl_serial_movement', [
                            'movement_type' => 3, 'from_status' => 2, 'to_status' => 5,
                            'doc_type' => 'CREDIT_NOTE', 'doc_id' => $id, 'doc_detail_id' => $l->idtbl_credit_note_detail,
                            'branch_id' => $branch_id, 'remark' => 'Returned on ' . $cn->creditnote_no,
                            'insertdatetime' => $now, 'tbl_user_idtbl_user' => $userid,
                            'tbl_product_serial_idtbl_product_serial' => $s->idtbl_product_serial,
                        ]);
                    } elseif ((int)$l->restock === 1) {
                        // non-serial: put the quantity back into the latest batch of this branch
                        $b = $this->db->select('idtbl_stock')->where('tbl_product_idtbl_product', $l->tbl_product_idtbl_product)
                                    ->where('tbl_company_branch_idtbl_company_branch', $branch_id)->where('status', 1)
                                    ->order_by('idtbl_stock', 'DESC')->limit(1)->get('tbl_stock')->row();
                        if (!$b) throw new RuntimeException('No stock batch found to put the item back');

                        $this->db->set('qty', 'qty + ' . (int)$l->qty, FALSE)->set('stock_status', 1)
                                ->set('updatedatetime', $now)->set('updateuser', $userid)
                                ->where('idtbl_stock', $b->idtbl_stock)->update('tbl_stock');
                    }
                }
            }

            $this->db->where('idtbl_credit_note', $id)->update('tbl_credit_note', [
                'approve_status' => $decision, 'approve_by' => $userid, 'approve_datetime' => $now,
                'approve_remark' => $remark, 'updateuser' => $userid, 'updatedatetime' => $now,
            ]);

            if ($this->db->trans_status() === FALSE) {
                $err = $this->db->error();
                throw new Exception('DB error: ' . $err['code'] . ' ' . $err['message']);
            }
            $this->db->trans_commit();
            $inTrans = false;

            return $this->_jsonResponse(1, 'fas fa-check-circle',
                $decision === 1 ? 'Credit note approved. Stock updated.' : 'Credit note rejected', 'success');

        } catch (RuntimeException $e) {
            if ($inTrans) $this->db->trans_rollback();
            return $this->_jsonResponse(0, 'fas fa-exclamation-triangle', $e->getMessage(), 'danger');
        } catch (Throwable $e) {
            if ($inTrans) $this->db->trans_rollback();
            log_message('error', 'Creditnotestatus failed: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            return $this->_jsonResponse(0, 'fas fa-exclamation-triangle', 'Could not update the credit note. Please try again.', 'danger');
        } finally {
            $this->db->db_debug = $old_debug;
        }
    }    

    // ── Private helper ────────────────────────────────────────────────────────────
    private function _jsonResponse($status, $icon, $message, $type, $extra = []) {
        $actionObj          = new stdClass();
        $actionObj->icon    = $icon;
        $actionObj->title   = '';
        $actionObj->message = $message;
        $actionObj->url     = '';
        $actionObj->target  = '_blank';
        $actionObj->type    = $type;

        $obj         = new stdClass();
        $obj->status = $status;
        $obj->action = json_encode($actionObj);

        foreach ($extra as $k => $v) {     // extra fields, e.g. invoice_no
            $obj->$k = $v;
        }

        echo json_encode($obj);
    }
    private function _load_creditnote($id){
        return $this->db->select('cn.*, i.invoice_no, i.invoice_date, i.customer_name, i.customer_nic, i.vat_rate, s.invoice_no AS settle_invoice_no')
            ->from('tbl_credit_note cn')
            ->join('tbl_invoice i', 'i.idtbl_invoice = cn.tbl_invoice_idtbl_invoice')
            ->join('tbl_invoice s', 's.idtbl_invoice = cn.settle_invoice_id', 'left')
            ->where('cn.idtbl_credit_note', $id)->where('cn.status', 1)
            ->where('i.tbl_company_branch_idtbl_company_branch', $_SESSION['branch_id'])
            ->get()->row();
    }
    private function _creditnote_items($cn_id){
        return $this->db->select('cd.*, d.product_code, d.product_name')
            ->from('tbl_credit_note_detail cd')
            ->join('tbl_invoice_detail d', 'd.idtbl_invoice_detail = cd.tbl_invoice_detail_idtbl_invoice_detail', 'left')
            ->where('cd.tbl_credit_note_idtbl_credit_note', $cn_id)->where('cd.status', 1)
            ->order_by('cd.idtbl_credit_note_detail')->get()->result();
    }
    private function _creditable_lines($inv){
        // invoice-level discount is shared across the lines, so the customer is not credited more than they paid
        $ratio = ((float)$inv->gross_total > 0) ? ((float)$inv->net_total / (float)$inv->gross_total) : 1;

        $lines = $this->db->query(
            "SELECT d.idtbl_invoice_detail, d.tbl_product_idtbl_product AS product_id,
                    d.product_code, d.product_name, d.serialno, d.qty, d.unitprice,
                    COALESCE((SELECT SUM(cd.qty) FROM tbl_credit_note_detail cd
                                JOIN tbl_credit_note cn ON cn.idtbl_credit_note = cd.tbl_credit_note_idtbl_credit_note
                               WHERE cd.tbl_invoice_detail_idtbl_invoice_detail = d.idtbl_invoice_detail
                                 AND cd.status = 1 AND cn.status = 1), 0) AS returned_qty
               FROM tbl_invoice_detail d
              WHERE d.tbl_invoice_idtbl_invoice = ? AND d.status = 1
              ORDER BY d.idtbl_invoice_detail", [$inv->idtbl_invoice])->result();

        foreach ($lines as $l) {
            $l->returned_qty    = (int)$l->returned_qty;
            $l->qty             = (int)$l->qty;
            $l->returnable_qty  = max(0, $l->qty - $l->returned_qty);
            $l->paid_unit       = round((float)$l->unitprice * $ratio, 2);
        }
        return $lines;
    }
    private function _branch_code4($branch_id){
        $b = $this->db->select('code')->where('idtbl_company_branch', $branch_id)->get('tbl_company_branch')->row();
        if (!$b) throw new RuntimeException('Branch not found');
        $c = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string)$b->code));
        if (strlen($c) !== 4) throw new RuntimeException('Branch code must be exactly 4 letters/digits (example: BR03)');
        return $c;
    }
    private function _has_permission($module, $type){
        $list = $this->Commeninfo->Getmenuprivilege();

        // works whether the model returns a CI result object or an array
        $rows = (is_object($list) && method_exists($list, 'result')) ? $list->result() : (array)$list;

        foreach ($rows as $r) {
            $r = (object)$r;
            if (strcasecmp(trim($r->module), $module) === 0 && (int)$r->permission_type === (int)$type) {
                return true;
            }
        }
        return false;
    }
}