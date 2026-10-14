<?php
use Dompdf\Dompdf;
use Dompdf\Options;

class Invoiceinfo extends CI_Model{
    public function SubmitNewCustomer(){
        try{
            $this->db->trans_begin();

            $userID=$_SESSION['userid'];
            $companyid=$_SESSION['company_id'];
            $branchid=$_SESSION['branch_id'];
            $regCustName=$this->input->post('regCustName');
            $regCustNIC=$this->input->post('regCustNIC');
            $regCustMobile=$this->input->post('regCustMobile');
            $regCustAddress1=$this->input->post('regCustAddress1');
            $regCustAddress2=$this->input->post('regCustAddress2');
            $regCustCity=$this->input->post('regCustCity');
            $regCustVAT=$this->input->post('regCustVAT');

            $insertdatetime=date('Y-m-d H:i:s');

            $this->db->select('count(*) as count');
            $this->db->from('tbl_customer');
            $this->db->where('nic', $regCustNIC);
            $this->db->or_where('telephone_no', $regCustMobile);
            $respond = $this->db->get();

            if($respond->row(0)->count>0){
                throw new Exception("Customer Already Exists");
            }
            else{
                $data = array(
                    'customer' => $regCustName,
                    'nic' => $regCustNIC,
                    'address_line1' => $regCustAddress1,
                    'address_line2' => $regCustAddress2,
                    'city' => $regCustCity,
                    'telephone_no' => $regCustMobile,
                    'vat_no' => $regCustVAT,
                    'vat_customer' => ($regCustVAT != '') ? 1 : 0,
                    'status' => '1',
                    'insertdatetime' => $insertdatetime,
                    'tbl_user_idtbl_user' => $userID,
                    'tbl_company_idtbl_company' => $companyid,
                    'tbl_company_branch_idtbl_company_branch ' => $branchid,
                );
                $this->db->insert('tbl_customer', $data);

                if ($this->db->trans_status() === FALSE) {
                    throw new Exception('Transaction failed');
                }

                $this->db->trans_commit();
                $this->_jsonResponse(1, 'fas fa-save', 'Record Added Successfully', 'success');

            }
        }
        catch(Exception $e){
            $this->db->trans_rollback();
            error_log('Receivablesettleinsertupdate Error: ' . $e->getMessage());
            $this->_jsonResponse(0, 'fas fa-exclamation-triangle', 'Record Error: ' . $e->getMessage(), 'danger');
        }
    }
    public function SearchCustomer(){
        $searchTerm = $this->input->post('searchValue');

        $this->db->select('idtbl_customer, customer, nic, telephone_no');
        $this->db->from('tbl_customer');
        $this->db->where('nic', $searchTerm);
        $this->db->or_where('telephone_no', $searchTerm);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $customer = $query->row();
            $response = array(
                'status' => 1,
                'customer_id' => $customer->idtbl_customer,
                'customer_name' => $customer->customer,
                'customer_nic' => $customer->nic,
                'customer_mobile' => $customer->telephone_no
            );
        } else {
            $response = array(
                'status' => 0,
                'message' => 'Customer not found'
            );
        }

        echo json_encode($response);
    }
    public function Getproductdetails(){
        $productbarcordname = trim((string)$this->input->post('productbarcordname'));

        $this->db->select('idtbl_product, product_code, product_name, model_no, description, cost_price, selling_price, warranty_months');
        $this->db->from('tbl_product');
        $this->db->where('status', 1);
        $this->db->group_start();
            $this->db->where('product_code', $productbarcordname);
            $this->db->or_like('product_name', $productbarcordname);
        $this->db->group_end();
        $respond = $this->db->get();

        if ($respond->num_rows() > 0) {
            foreach ($respond->result() as $row) {
                $productList[] = array(
                    'idtbl_product' => $row->idtbl_product,
                    'product_code' => $row->product_code,
                    'product_name' => $row->product_name,
                    'model_no' => $row->model_no,
                    'description' => $row->description,
                    'cost_price' => $row->cost_price,
                    'selling_price' => $row->selling_price,
                    'warranty_months' => $row->warranty_months
                );

                $this->db->select('`idtbl_product_serial`, `serialno`, `costunitprice`, `saleprice`, `tbl_product_idtbl_product`');
                $this->db->from('tbl_product_serial');
                $this->db->where('tbl_product_idtbl_product', $row->idtbl_product);
                $this->db->where('status', 1);

                $serialRespond = $this->db->get();
                $productList[count($productList) - 1]['serials'] = $serialRespond->result();
            }
            echo json_encode(array(
                'status' => 1,
                'products' => $productList
            ));
        } else {
            echo json_encode(array(
                'status' => 0,
                'message' => 'Product not found'
            ));
        }
    }
    public function CheckGiftVoucher(){
        $code = strtoupper(trim((string)$this->input->post('vouchercode')));

        $this->db->select('idtbl_gift_voucher, voucher_code, voucher_value, balance, expiry_date, status');
        $this->db->from('tbl_gift_voucher');
        $this->db->where('voucher_code', $code);
        $row = $this->db->get()->row();

        if (!$row) {
            echo json_encode(array('status' => 0, 'message' => 'Invalid voucher number'));
        } else if ($row->status != 1) {
            echo json_encode(array('status' => 0, 'message' => 'Voucher is not active / already used'));
        } else if (!empty($row->expiry_date) && $row->expiry_date < date('Y-m-d')) {
            echo json_encode(array('status' => 0, 'message' => 'Voucher expired on ' . $row->expiry_date));
        } else if ($row->balance <= 0) {
            echo json_encode(array('status' => 0, 'message' => 'No balance left on this voucher'));
        } else {
            echo json_encode(array(
                'status' => 1,
                'idtbl_gift_voucher' => $row->idtbl_gift_voucher,
                'balance' => $row->balance,
                'expiry_date' => $row->expiry_date
            ));
        }
    }
    public function Getpaymenttypelist() {
        $this->db->select('
            tbl_payment_type.idtbl_payment_type, 
            tbl_payment_type.paytype, 
            tbl_payment_type.status as type_status, 
            tbl_payment_group.idtbl_payment_group, 
            tbl_payment_group.paygroup, 
            tbl_payment_group.type as group_type
        ');
        $this->db->from('tbl_payment_type');
        $this->db->join(
            'tbl_payment_group', 
            'tbl_payment_group.idtbl_payment_group = tbl_payment_type.tbl_payment_group_idtbl_payment_group', 
            'inner'
        );
        
        // Optional: Filter only active records if needed
        $this->db->where('tbl_payment_type.status', 1);
        $this->db->where('tbl_payment_group.status', 1);
        
        // Optional: Order by group or type name
        $this->db->order_by('tbl_payment_group.idtbl_payment_group', 'ASC');
        $this->db->order_by('tbl_payment_type.paytype', 'ASC');
        
        $query = $this->db->get();
        
        // Return as an array of objects (use result_array() if you prefer arrays)
        if (!empty($query->result())) {
            $response = [
                'status' => 1,
                'data'   => $query->result()
            ];
        } else {
            $response = [
                'status' => 0,
                'message' => 'No payment types found'
            ];
        }
        
        // Send JSON response
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    public function Insertinvoice(){
        $this->db->db_debug = FALSE;   // let us catch DB errors ourselves
        $inv    = json_decode($this->input->post('invoice'), true);
        $userID = $this->session->userdata('userid');   // <-- adjust to your session key
        $today  = date('Y-m-d');
        $now    = date('Y-m-d H:i:s');
        print_r($inv);
        die();

        if (empty($inv['items'])) {
            echo json_encode(array('status' => 0, 'message' => 'Cart is empty'));
            return;
        }
        $isQuote = ($inv['invoiceType'] === 'Corporate Quotation');

        $this->db->trans_begin();
        try {
            // 1. Lines: prices always come from the DB, never from the browser
            $lines = array();
            $gross = 0;
            foreach ($inv['items'] as $it) {
                if (!empty($it['serialId'])) {
                    $s = $this->db->query(
                        "SELECT s.idtbl_product_serial, s.serialno, s.costunitprice, s.saleprice,
                                p.idtbl_product, p.product_code, p.product_name, p.selling_price, p.warranty_months
                        FROM tbl_product_serial s
                        JOIN tbl_product p ON p.idtbl_product = s.tbl_product_idtbl_product
                        WHERE s.idtbl_product_serial = ? AND s.status = 1 FOR UPDATE",
                        array((int)$it['serialId']))->row();
                    if (!$s) throw new Exception('A serial number is no longer available. Please re-add the item.');

                    $price = ((float)$s->saleprice > 0) ? (float)$s->saleprice : (float)$s->selling_price;
                    $qty   = 1;
                    $lines[] = array(
                        'product_id' => $s->idtbl_product, 'serial_id' => $s->idtbl_product_serial,
                        'code' => $s->product_code, 'name' => $s->product_name, 'serialno' => $s->serialno,
                        'warranty' => (int)$s->warranty_months, 'qty' => $qty,
                        'cost' => (float)$s->costunitprice, 'price' => $price
                    );
                } else {
                    $p = $this->db->query(
                        "SELECT idtbl_product, product_code, product_name, cost_price, selling_price, warranty_months
                        FROM tbl_product WHERE idtbl_product = ? AND status = 1",
                        array((int)$it['id']))->row();
                    if (!$p) throw new Exception('Product not found.');

                    $qty   = max(1, (int)$it['qty']);
                    $price = (float)$p->selling_price;
                    $lines[] = array(
                        'product_id' => $p->idtbl_product, 'serial_id' => null,
                        'code' => $p->product_code, 'name' => $p->product_name, 'serialno' => null,
                        'warranty' => (int)$p->warranty_months, 'qty' => $qty,
                        'cost' => (float)$p->cost_price, 'price' => $price
                    );
                }
                $gross += $price * $qty;
            }

            $discount = min(max((float)$inv['discount'], 0), $gross);
            $net      = round($gross - $discount, 2);
            $vat      = round($net - ($net / 1.18), 2);   // prices include 18% VAT

            // 2. Payments
            $pays = array();
            $paid = 0;
            $cash = 0;
            $usedVouchers = array();
            if (!$isQuote) {
                foreach ((array)$inv['payments'] as $pm) {
                    $amt = round((float)$pm['amount'], 2);
                    if ($amt <= 0) continue;

                    // pay type comes from the database, not from the browser
                    $pt = $this->db->query(
                        "SELECT idtbl_paytype, paytype, group_type FROM tbl_paytype
                        WHERE idtbl_paytype = ? AND status = 1",
                        array((int)$pm['paytypeId']))->row();
                    if (!$pt) throw new Exception('Invalid payment type.');
                    $gtype = strtolower(trim($pt->group_type));

                    $voucherId = null;
                    if ($gtype === 'voucher') {
                        $voucherId = (int)$pm['voucherId'];
                        if (in_array($voucherId, $usedVouchers)) throw new Exception('Same gift voucher used twice.');
                        $usedVouchers[] = $voucherId;

                        $v = $this->db->query(
                            "SELECT balance, expiry_date, status FROM tbl_gift_voucher
                            WHERE idtbl_gift_voucher = ? FOR UPDATE", array($voucherId))->row();
                        if (!$v || $v->status != 1
                            || (!empty($v->expiry_date) && $v->expiry_date < $today)
                            || (float)$v->balance < $amt) {
                            throw new Exception('Gift voucher is invalid, expired or has insufficient balance.');
                        }
                    }

                    $pays[] = array(
                        'paytype_id' => $pt->idtbl_paytype,
                        'paytype' => $pt->paytype, 'group_type' => $gtype,
                        'amount' => $amt, 'tendered' => $amt,
                        'ref' => $pm['ref'], 'approval' => $pm['ref2'], 'bank' => $pm['bank'],
                        'months' => ($pm['months'] !== '' ? (int)$pm['months'] : null),
                        'cheque_date' => (!empty($pm['chequeDate']) ? $pm['chequeDate'] : null),
                        'voucher_id' => $voucherId
                    );
                    $paid += $amt;
                    if ($gtype === 'cash') $cash += $amt;
                }

                // change can only come out of cash
                $change = max(0, round($paid - $net, 2));
                if ($change > round($cash, 2)) throw new Exception('Over-payment can only be returned as cash.');
                $left = $change;
                foreach ($pays as &$pp) {
                    if ($pp['group_type'] === 'cash' && $left > 0) {
                        $cut = min($pp['amount'], $left);
                        $pp['amount'] -= $cut;
                        $left -= $cut;
                    }
                }
                unset($pp);
            } else {
                $change = 0;
            }

            $applied = round($paid - $change, 2);
            if ($inv['invoiceType'] === 'Retail Tax Invoice (VAT)' && $applied < $net) {
                throw new Exception('Payment is less than the invoice total.');
            }
            $balance   = $isQuote ? 0 : max(0, round($net - $applied, 2));
            $payStatus = ($applied <= 0) ? 0 : (($balance > 0) ? 1 : 2);

            // 3. Header
            $this->db->insert('tbl_invoice', array(
                'invoice_date' => $today, 'invoice_type' => $inv['invoiceType'],
                'customer_nic' => $inv['customerNic'], 'customer_name' => $inv['customerName'],
                'fulfillment' => $inv['fulfillment'],
                'gross_total' => $gross, 'discount' => $discount, 'vat_rate' => 18, 'vat_amount' => $vat,
                'net_total' => $net, 'paid_total' => $applied, 'balance_due' => $balance,
                'change_given' => $change, 'payment_status' => $payStatus,
                'status' => 1, 'insertdatetime' => $now, 'tbl_user_idtbl_user' => $userID
            ));
            $invoiceId = $this->db->insert_id();
            $invoiceNo = 'INV-' . date('ym') . str_pad($invoiceId, 6, '0', STR_PAD_LEFT);
            $this->db->where('idtbl_invoice', $invoiceId)->update('tbl_invoice', array('invoice_no' => $invoiceNo));

            // 4. Lines + mark serials sold
            foreach ($lines as $l) {
                $this->db->insert('tbl_invoice_detail', array(
                    'tbl_invoice_idtbl_invoice' => $invoiceId,
                    'tbl_product_idtbl_product' => $l['product_id'],
                    'tbl_product_serial_idtbl_product_serial' => $l['serial_id'],
                    'product_code' => $l['code'], 'product_name' => $l['name'], 'serialno' => $l['serialno'],
                    'warranty_months' => $l['warranty'], 'qty' => $l['qty'],
                    'cost_price' => $l['cost'], 'unit_price' => $l['price'],
                    'line_total' => $l['price'] * $l['qty']
                ));

                if (!$isQuote && $l['serial_id']) {
                    $this->db->where('idtbl_product_serial', $l['serial_id'])->where('status', 1)
                        ->update('tbl_product_serial', array(
                            'status' => 2, 'tbl_invoice_idtbl_invoice' => $invoiceId, 'sold_datetime' => $now));
                    if ($this->db->affected_rows() != 1) throw new Exception('Serial ' . $l['serialno'] . ' was just sold.');
                }
            }

            // 5. Payments + voucher redemption
            foreach ($pays as $pp) {
                $this->db->insert('tbl_invoice_payment', array(
                    'tbl_invoice_idtbl_invoice' => $invoiceId,
                    'paytype' => $pp['paytype'], 'group_type' => $pp['group_type'],
                    'amount' => $pp['amount'], 'tendered_amount' => $pp['tendered'],
                    'ref_no' => $pp['ref'], 'approval_code' => $pp['approval'], 'bank' => $pp['bank'],
                    'installment_months' => $pp['months'], 'cheque_date' => $pp['cheque_date'],
                    'cheque_status' => ($pp['group_type'] === 'cheque' ? 0 : null),
                    'tbl_gift_voucher_idtbl_gift_voucher' => $pp['voucher_id'],
                    'insertdatetime' => $now
                ));

                if ($pp['voucher_id']) {
                    $this->db->query(
                        "UPDATE tbl_gift_voucher
                            SET balance = balance - ?, status = IF(balance <= 0, 2, 1)
                        WHERE idtbl_gift_voucher = ? AND status = 1 AND balance >= ?",
                        array($pp['amount'], $pp['voucher_id'], $pp['amount']));
                    if ($this->db->affected_rows() != 1) throw new Exception('Gift voucher balance changed. Please retry.');

                    $bal = $this->db->query("SELECT balance FROM tbl_gift_voucher WHERE idtbl_gift_voucher = ?",
                            array($pp['voucher_id']))->row()->balance;
                    $this->db->insert('tbl_gift_voucher_log', array(
                        'tbl_gift_voucher_idtbl_gift_voucher' => $pp['voucher_id'],
                        'tbl_invoice_idtbl_invoice' => $invoiceId,
                        'action' => 'redeem', 'amount' => $pp['amount'], 'balance_after' => $bal,
                        'insertdatetime' => $now, 'tbl_user_idtbl_user' => $userID
                    ));
                }
            }

            if ($this->db->trans_status() === FALSE) throw new Exception('Database error while saving the invoice.');
            $this->db->trans_commit();
            echo json_encode(array('status' => 1, 'invoice_id' => $invoiceId, 'invoice_no' => $invoiceNo));

        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo json_encode(array('status' => 0, 'message' => $e->getMessage()));
        }
    }

    // ── Private helper ────────────────────────────────────────────────────────────
    private function _jsonResponse($status, $icon, $message, $type) {
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

        echo json_encode($obj);
    }
}