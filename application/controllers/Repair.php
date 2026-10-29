<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Repair extends CI_Controller
{
    const VAT_RATE = 18;

    // CHANGE: the column of tbl_user that holds the user's name
    private $userNameCol = 'name';

    public function __construct()
    {
        parent::__construct();
        $this->load->model("Commeninfo");
    }

    public function index(){
        $u = $this->userNameCol;

        // CHANGE: add your active / company condition if tbl_user has them
        $result['technicians'] = $this->db->query("SELECT idtbl_user AS id, `$u` AS name FROM tbl_user ORDER BY `$u`")->result();

        $result['paytypes'] = $this->db->query(
            "SELECT pt.idtbl_payment_type AS id, pt.paytype
               FROM tbl_payment_type pt
               JOIN tbl_payment_group pg ON pg.idtbl_payment_group = pt.tbl_payment_group_idtbl_payment_group
              WHERE pt.status = 1 AND pg.status = 1 AND pg.type IN ('cash', 'card', 'transfer')
              ORDER BY pt.paytype")->result();

        $result['menuaccess']=$this->Commeninfo->Getmenuprivilege();

        $this->load->view('repair', $result);
    }

    // ---------------------------------------------------------------- find the sold item + warranty
    public function Searchserial(){
        $company = (int)$_SESSION['company_id'];
        $sn = trim((string)$this->input->post('serial'));
        if ($sn === '') return $this->_jsonResponse(0, 'fas fa-exclamation-triangle', 'Enter the serial number', 'danger');

        $open = $this->db->select('job_no')->where('serialno', $sn)->where('status', 1)
                         ->where_not_in('job_status', [5, 6])->limit(1)->get('tbl_repair_job')->row();
        $done = $this->db->where('serialno', $sn)->where('status', 1)->where('job_status', 5)->count_all_results('tbl_repair_job');

        $item = $this->_sold_item('d.serialno = ?', [$sn], $company);

        echo json_encode([
            'status'   => 1,
            'found'    => (bool)$item,
            'item'     => $item,
            'open_job' => $open ? $open->job_no : null,
            'repairs'  => $done,
        ]);
    }

    // ---------------------------------------------------------------- create the job
    public function Insertrepair(){
        $inTrans   = false;
        $old_debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;

        try {
            $in = json_decode($this->input->post('job'), true);
            if (!is_array($in)) throw new RuntimeException('Invalid data');

            $userid     = $_SESSION['userid'];             // CHANGE: your user id key
            $company_id = (int)$_SESSION['company_id'];
            $branch_id  = (int)$_SESSION['branch_id'];
            $now        = date('Y-m-d H:i:s');
            $today      = date('Y-m-d');

            $name  = mb_substr(trim((string)$in['customerName']), 0, 150);
            $phone = trim((string)$in['customerPhone']);
            $fault = mb_substr(trim((string)$in['fault']), 0, 500);
            if ($name === '')  throw new RuntimeException('Enter the customer name');
            if (!preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) throw new RuntimeException('Enter a valid phone number');
            if (mb_strlen($fault) < 3) throw new RuntimeException('Enter the fault description');

            $notCovered = mb_substr(trim((string)$in['notCovered']), 0, 250);
            $promised   = (isset($in['promised']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $in['promised'])) ? $in['promised'] : null;

            // warranty is always worked out again here, the browser value is never trusted
            $item = null;
            if (!empty($in['detailId'])) {
                $item = $this->_sold_item('d.idtbl_invoice_detail = ?', [(int)$in['detailId']], $company_id);
                if (!$item) throw new RuntimeException('Sold item not found');
                if ($item->returned_on)
                    throw new RuntimeException('This item was returned on credit note ' . $item->returned_on . ', so it is not a customer sale any more');
                $type   = ($item->in_warranty && $notCovered === '') ? 1 : 2;
                $wst    = $item->in_warranty ? 1 : 2;
                $prod   = $item->product_name;
                $serial = (string)$item->serialno;
            } else {
                $prod   = mb_substr(trim((string)$in['productName']), 0, 200);
                $serial = mb_substr(trim((string)$in['serial']), 0, 100);
                if ($prod === '') throw new RuntimeException('Enter the product name');
                $type = 2; $wst = 3;
            }

            if ($serial !== '') {
                $open = $this->db->select('job_no')->where('serialno', $serial)->where('status', 1)
                                 ->where_not_in('job_status', [5, 6])->limit(1)->get('tbl_repair_job')->row();
                if ($open) throw new RuntimeException('There is already an open repair job for this serial number: ' . $open->job_no);
            }

            $this->db->trans_begin();
            $inTrans = true;

            // number: 26OCT_RP03_00001
            $code   = $this->_branch_code4($branch_id);
            $prefix = date('y') . strtoupper(date('M')) . '_RP' . substr($code, -2) . '_';
            $last = $this->db->query(
                "SELECT MAX(CAST(SUBSTRING(job_no, ?) AS UNSIGNED)) AS m FROM tbl_repair_job WHERE job_no LIKE ? FOR UPDATE",
                [strlen($prefix) + 1, str_replace('_', '\_', $prefix) . '%'])->row();
            $job_no = $prefix . str_pad(((int)$last->m) + 1, 5, '0', STR_PAD_LEFT);

            $this->db->insert('tbl_repair_job', [
                'job_no' => $job_no, 'job_date' => $today, 'repair_type' => $type, 'warranty_status' => $wst,
                'warranty_end' => $item ? $item->warranty_end : null,
                'not_covered_reason' => ($item && $item->in_warranty && $notCovered !== '') ? $notCovered : null,
                'tbl_invoice_idtbl_invoice' => $item ? $item->invoice_id : null,
                'tbl_invoice_detail_idtbl_invoice_detail' => $item ? $item->detail_id : null,
                'tbl_product_serial_idtbl_product_serial' => $item ? $item->serial_id : null,
                'tbl_product_idtbl_product' => $item ? $item->product_id : null,
                'product_name' => $prod, 'serialno' => ($serial !== '' ? $serial : null),
                'tbl_customer_idtbl_customer' => $item ? $item->customer_id : null,
                'customer_name' => $name, 'customer_phone' => $phone,
                'fault' => $fault,
                'accessories' => mb_substr(trim((string)$in['accessories']), 0, 250),
                'condition_note' => mb_substr(trim((string)$in['condition']), 0, 250),
                'promised_date' => $promised, 'job_status' => 1, 'status' => 1,
                'insertdatetime' => $now, 'updateuser' => $userid, 'updatedatetime' => $now,
                'tbl_user_idtbl_user' => $userid, 'tbl_company_idtbl_company' => $company_id,
                'tbl_company_branch_idtbl_company_branch' => $branch_id,
            ]);
            $job_id = $this->db->insert_id();
            if (!$job_id) throw new Exception('Job insert failed');

            $this->_log($job_id, null, 1, 'Job received', $userid, $now);

            // serial history (only when the serial is known, the column cannot be empty)
            if ($item && $item->serial_id) {
                $this->db->insert('tbl_serial_movement', [
                    'movement_type' => ($type === 1) ? 7 : 8,
                    'from_status'   => null,
                    'to_status'     => ($type === 1) ? 7 : 8,
                    'doc_type'      => ($type === 1) ? 'WARRANTY_REPAIR' : 'NONE_WARRANTY_REPAIR',
                    'doc_id'        => $job_id,
                    'doc_detail_id' => $item->detail_id,
                    'branch_id'     => $branch_id,
                    'remark'        => 'Repair on ' . $job_no,
                    'insertdatetime' => $now,
                    'tbl_user_idtbl_user' => $userid,
                    'tbl_product_serial_idtbl_product_serial' => $item->serial_id,
                ]);
            }

            if ($this->db->trans_status() === FALSE) {
                $err = $this->db->error();
                throw new Exception('DB error: ' . $err['code'] . ' ' . $err['message']);
            }
            $this->db->trans_commit();
            $inTrans = false;

            return $this->_jsonResponse(1, 'fas fa-check-circle', 'Repair job created: ' . $job_no, 'success',
                ['job_id' => $job_id, 'job_no' => $job_no]);

        } catch (RuntimeException $e) {
            if ($inTrans) $this->db->trans_rollback();
            return $this->_jsonResponse(0, 'fas fa-exclamation-triangle', $e->getMessage(), 'danger');
        } catch (Throwable $e) {
            if ($inTrans) $this->db->trans_rollback();
            log_message('error', 'Insertrepair failed: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            return $this->_jsonResponse(0, 'fas fa-exclamation-triangle', 'Could not create the job. Please try again.'.$e->getMessage(), 'danger');
        } finally {
            $this->db->db_debug = $old_debug;
        }
    }

    // ---------------------------------------------------------------- one job for the manage window
    public function Getrepair(){
        $job = $this->_job((int)$this->input->post('id'));
        if (!$job) return $this->_jsonResponse(0, 'fas fa-exclamation-triangle', 'Repair job not found', 'danger');

        $u = $this->userNameCol;
        echo json_encode([
            'status' => 1,
            'job'    => $job,
            'parts'  => $this->db->where('tbl_repair_job_idtbl_repair_job', $job->idtbl_repair_job)->where('status', 1)
                                 ->order_by('idtbl_repair_part')->get('tbl_repair_part')->result(),
            'log'    => $this->db->query(
                "SELECT l.from_status, l.to_status, l.remark, l.insertdatetime, us.`$u` AS by_user
                   FROM tbl_repair_log l LEFT JOIN tbl_user us ON us.idtbl_user = l.tbl_user_idtbl_user
                  WHERE l.tbl_repair_job_idtbl_repair_job = ? ORDER BY l.idtbl_repair_log", [$job->idtbl_repair_job])->result(),
        ]);
    }

    // ---------------------------------------------------------------- every status change
    public function Repairaction(){
        $inTrans   = false;
        $old_debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;

        try {
            $userid = $_SESSION['userid'];                 // CHANGE: your user id key
            $now    = date('Y-m-d H:i:s');
            $today  = date('Y-m-d');
            $id     = (int)$this->input->post('job_id');
            $act    = (string)$this->input->post('act');
            $remark = mb_substr(trim((string)$this->input->post('remark')), 0, 250);
            $tech   = (int)$this->input->post('technician');

            $this->db->trans_begin();
            $inTrans = true;

            $job = $this->db->query(
                "SELECT * FROM tbl_repair_job WHERE idtbl_repair_job = ? AND status = 1
                    AND tbl_company_idtbl_company = ? AND tbl_company_branch_idtbl_company_branch = ? FOR UPDATE",
                [$id, (int)$_SESSION['company_id'], (int)$_SESSION['branch_id']])->row();
            if (!$job) throw new RuntimeException('Repair job not found');

            $from = (int)$job->job_status;
            $type = (int)$job->repair_type;

            // charges sent together with the action (only while they can still be edited)
            if (in_array($act, ['charges', 'estimate', 'start', 'ready'], true) && $this->input->post('labour') !== null) {
                if (!in_array($from, [1, 3], true)) throw new RuntimeException('Charges cannot be changed at this stage');
                $this->_save_charges($job, $this->input->post('labour'), $this->input->post('parts'), $userid, $now);
                $job = $this->db->where('idtbl_repair_job', $id)->get('tbl_repair_job')->row();
            }

            $to = null; $set = []; $msg = 'Saved';

            switch ($act) {
                case 'charges':
                    if (!in_array($from, [1, 3], true)) throw new RuntimeException('Charges cannot be changed at this stage');
                    $msg = 'Charges saved';
                    break;

                case 'estimate':
                    if ($type !== 2 || $from !== 1) throw new RuntimeException('An estimate can only be sent for a new over-warranty job');
                    if ((float)$job->total_charge <= 0) throw new RuntimeException('Enter the charges first');
                    $to = 2; $msg = 'Estimate recorded. Waiting for the customer to approve.';
                    break;

                case 'approve':                                   // the customer accepted the estimate
                    if ($type !== 2 || $from !== 2) throw new RuntimeException('This job is not waiting for approval');
                    $to = 3; $set['technician_id'] = $tech ? $tech : null;
                    $remark = ($remark !== '') ? $remark : 'Customer approved the estimate';
                    $msg = 'Repair started';
                    break;

                case 'reject':                                    // the customer declined the estimate
                    if ($from !== 2) throw new RuntimeException('This job is not waiting for approval');
                    $to = 6; $remark = 'Estimate declined by the customer'; $msg = 'Job closed';
                    break;

                case 'start':                                     // warranty repair: no approval needed
                    if ($type !== 1 || $from !== 1) throw new RuntimeException('Only a new warranty repair can be started here');
                    $to = 3; $set['technician_id'] = $tech ? $tech : null; $msg = 'Repair started';
                    break;

                case 'ready':
                    if ($from !== 3) throw new RuntimeException('This job is not in repair');
                    $to = 4; $set['completed_date'] = $today; $msg = 'Marked as ready for delivery';
                    break;

                case 'deliver':
                    if ($from !== 4) throw new RuntimeException('This job is not ready for delivery');
                    $set['delivered_date'] = $today;
                    $set['received_by'] = mb_substr(trim((string)$this->input->post('collector')), 0, 150);
                    if ($type === 2 && (float)$job->total_charge > 0) {
                        $ptid = (int)$this->input->post('paytype');
                        $paid = round((float)$this->input->post('paid'), 2);
                        if (!$this->db->where('idtbl_payment_type', $ptid)->where('status', 1)->count_all_results('tbl_payment_type'))
                            throw new RuntimeException('Select the payment method');
                        if ($paid + 0.001 < (float)$job->total_charge)
                            throw new RuntimeException('The full amount must be paid before delivery (' . number_format($job->total_charge, 2) . ')');
                        $set['paid_amount'] = $job->total_charge;
                        $set['tbl_payment_type_idtbl_payment_type'] = $ptid;
                    }
                    $to = 5; $msg = 'Delivered';
                    break;

                case 'cancel':
                    if (!in_array($from, [1, 2, 3, 4], true)) throw new RuntimeException('This job cannot be cancelled');
                    if ($remark === '') throw new RuntimeException('Enter the reason');
                    $to = 6; $msg = 'Job cancelled';
                    break;

                default:
                    throw new RuntimeException('Unknown action');
            }

            if ($to !== null) {
                $set['job_status'] = $to; $set['updateuser'] = $userid; $set['updatedatetime'] = $now;
                $this->db->where('idtbl_repair_job', $id)->update('tbl_repair_job', $set);
            }
            $this->_log($id, $from, ($to !== null ? $to : $from), ($remark !== '' ? $remark : $msg), $userid, $now);

            // serial history: the item goes back to the customer
            if ($act === 'deliver' && !empty($job->tbl_product_serial_idtbl_product_serial)) {
                $this->db->insert('tbl_serial_movement', [
                    'movement_type' => 9,                                   // customer collected
                    'from_status'   => ($type === 1) ? 7 : 8,               // same value as the repair movement's to_status
                    'to_status'     => 9,                                   // back with the customer (sold)
                    'doc_type'      => ($type === 1) ? 'WARRANTY_REPAIR' : 'NONE_WARRANTY_REPAIR',
                    'doc_id'        => $id,
                    'doc_detail_id' => $job->tbl_invoice_detail_idtbl_invoice_detail,
                    'branch_id'     => (int)$_SESSION['branch_id'],
                    'remark'        => 'Delivered to customer on ' . $job->job_no,
                    'insertdatetime' => $now,
                    'tbl_user_idtbl_user' => $userid,
                    'tbl_product_serial_idtbl_product_serial' => $job->tbl_product_serial_idtbl_product_serial,
                ]);
            }

            if ($this->db->trans_status() === FALSE) {
                $err = $this->db->error();
                throw new Exception('DB error: ' . $err['code'] . ' ' . $err['message']);
            }
            $this->db->trans_commit();
            $inTrans = false;

            return $this->_jsonResponse(1, 'fas fa-check-circle', $msg, 'success');

        } catch (RuntimeException $e) {
            if ($inTrans) $this->db->trans_rollback();
            return $this->_jsonResponse(0, 'fas fa-exclamation-triangle', $e->getMessage(), 'danger');
        } catch (Throwable $e) {
            if ($inTrans) $this->db->trans_rollback();
            log_message('error', 'Repairaction failed: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            return $this->_jsonResponse(0, 'fas fa-exclamation-triangle', 'Could not update the job. Please try again.', 'danger');
        } finally {
            $this->db->db_debug = $old_debug;
        }
    }

    // ---------------------------------------------------------------- job card PDF (uses your Pdf library)
    public function Printrepair($id = 0){
        $job = $this->_job((int)$id);
        if (!$job) show_404();

        $logoFile = FCPATH . 'assets/images/logo.png';   // CHANGE: same logo as the invoice
        $html = $this->load->view('repair_pdf', [
            'job'   => $job,
            'parts' => $this->db->where('tbl_repair_job_idtbl_repair_job', $job->idtbl_repair_job)->where('status', 1)
                                ->order_by('idtbl_repair_part')->get('tbl_repair_part')->result(),
            'logo'  => is_file($logoFile) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile)) : '',
            'company' => ['name' => 'Spencersz Lanka (Pvt) Ltd', 'address' => '295, Negombo Road, Wattala',
                          'tel' => '+94 11 000 0000'],   // CHANGE: same values as Printinvoice
        ], TRUE);

        $this->load->library('pdf');
        $this->pdf->loadHtml($html);
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->render();
        if (ob_get_length()) ob_end_clean();
        $this->pdf->stream('REPAIR-' . $job->job_no . '.pdf', ['Attachment' => 0]);
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
    // the sale line of a serial (or of one invoice line) with its warranty worked out
    private function _sold_item($where, $bind, $company){
        $bind[] = $company;
        $r = $this->db->query(
            "SELECT d.idtbl_invoice_detail AS detail_id, d.tbl_product_idtbl_product AS product_id, d.product_code, d.product_name,
                    d.serialno, d.tbl_product_serial_idtbl_product_serial AS serial_id, IFNULL(d.warranty_months, 0) AS warranty_months,
                    i.idtbl_invoice AS invoice_id, i.invoice_no, i.invoice_date, i.customer_name,
                    i.tbl_customer_idtbl_customer AS customer_id, c.telephone_no AS phone,
                    DATE_ADD(i.invoice_date, INTERVAL IFNULL(d.warranty_months, 0) MONTH) AS warranty_end,
                    DATEDIFF(DATE_ADD(i.invoice_date, INTERVAL IFNULL(d.warranty_months, 0) MONTH), CURDATE()) AS days_left
               FROM tbl_invoice_detail d
               JOIN tbl_invoice i ON i.idtbl_invoice = d.tbl_invoice_idtbl_invoice
               LEFT JOIN tbl_customer c ON c.idtbl_customer = i.tbl_customer_idtbl_customer
              WHERE $where AND d.status = 1 AND i.status = 1 AND i.invoice_type <> 'Corporate Quotation'
                AND i.tbl_company_idtbl_company = ?
              ORDER BY i.invoice_date DESC, i.idtbl_invoice DESC LIMIT 1", $bind)->row();
        if (!$r) return null;

        $r->in_warranty = ((int)$r->warranty_months > 0 && (int)$r->days_left >= 0);

        // a returned item (credit note) is no longer a customer sale
        $cn = $this->db->query(
            "SELECT cn.creditnote_no FROM tbl_credit_note_detail cd
               JOIN tbl_credit_note cn ON cn.idtbl_credit_note = cd.tbl_credit_note_idtbl_credit_note
              WHERE cd.tbl_invoice_detail_idtbl_invoice_detail = ? AND cd.status = 1 AND cn.status = 1
                AND cn.approve_status IN (0, 1) LIMIT 1", [$r->detail_id])->row();
        $r->returned_on = $cn ? $cn->creditnote_no : null;
        return $r;
    }
    private function _job($id){
        $u = $this->userNameCol;
        return $this->db->query(
            "SELECT j.*, t.`$u` AS technician_name, pt.paytype AS paytype_name, i.invoice_no AS sale_invoice_no
               FROM tbl_repair_job j
               LEFT JOIN tbl_user t ON t.idtbl_user = j.technician_id
               LEFT JOIN tbl_payment_type pt ON pt.idtbl_payment_type = j.tbl_payment_type_idtbl_payment_type
               LEFT JOIN tbl_invoice i ON i.idtbl_invoice = j.tbl_invoice_idtbl_invoice
              WHERE j.idtbl_repair_job = ? AND j.status = 1
                AND j.tbl_company_idtbl_company = ? AND j.tbl_company_branch_idtbl_company_branch = ?",
            [$id, (int)$_SESSION['company_id'], (int)$_SESSION['branch_id']])->row();
    }
    // labour + parts. A warranty repair keeps the parts list but the charge is always 0
    private function _save_charges($job, $labour, $partsJson, $userid, $now){
        $labour = max(0, round((float)$labour, 2));
        $parts  = json_decode((string)$partsJson, true);
        if (!is_array($parts)) $parts = [];

        $this->db->where('tbl_repair_job_idtbl_repair_job', $job->idtbl_repair_job)->where('status', 1)
                 ->update('tbl_repair_part', ['status' => 2]);

        $partsTotal = 0;
        foreach ($parts as $p) {
            $desc = mb_substr(trim((string)$p['desc']), 0, 200);
            if ($desc === '') continue;
            $qty   = max(1, (int)$p['qty']);
            $price = max(0, round((float)$p['price'], 2));
            $tot   = round($qty * $price, 2);
            $partsTotal += $tot;
            $this->db->insert('tbl_repair_part', [
                'description' => $desc, 'qty' => $qty, 'unitprice' => $price, 'total' => $tot, 'status' => 1,
                'insertdatetime' => $now, 'tbl_user_idtbl_user' => $userid,
                'tbl_repair_job_idtbl_repair_job' => $job->idtbl_repair_job,
            ]);
        }
        $partsTotal = round($partsTotal, 2);

        $chargeable = ((int)$job->repair_type === 2);
        $total = $chargeable ? round($labour + $partsTotal, 2) : 0;
        $vat   = $total > 0 ? round($total - $total / (1 + self::VAT_RATE / 100), 2) : 0;

        $this->db->where('idtbl_repair_job', $job->idtbl_repair_job)->update('tbl_repair_job', [
            'labour_charge' => $chargeable ? $labour : 0, 'parts_total' => $partsTotal,
            'total_charge' => $total, 'vatamount' => $vat,
            'updateuser' => $userid, 'updatedatetime' => $now,
        ]);
    }
    private function _log($job_id, $from, $to, $remark, $userid, $now){
        $this->db->insert('tbl_repair_log', [
            'from_status' => $from, 'to_status' => $to, 'remark' => $remark,
            'insertdatetime' => $now, 'tbl_user_idtbl_user' => $userid, 'tbl_repair_job_idtbl_repair_job' => $job_id,
        ]);
    }
    // same rule as the invoice number: exactly 4 letters / digits
    private function _branch_code4($branch_id){
        $b = $this->db->select('code')->where('idtbl_company_branch', $branch_id)->get('tbl_company_branch')->row();
        if (!$b) throw new RuntimeException('Branch not found');
        $c = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string)$b->code));
        if (strlen($c) !== 4) throw new RuntimeException('Branch code must be exactly 4 letters/digits (example: BR03)');
        return $c;
    }
}