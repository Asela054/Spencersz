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
                $this->db->where_in('stock_status', array(1, 5, 6));

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
        $inTrans   = false;
        $old_debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;   // SQL errors are handled by trans_status(), not an error page

        try {
            $inv = json_decode($this->input->post('invoice'), true);
            if (!is_array($inv) || empty($inv['items'])) {
                throw new RuntimeException('Invalid invoice data');
            }

            $userid=$_SESSION['userid'];
            $company_id=$_SESSION['company_id'];
            $branch_id=$_SESSION['branch_id'];
            $now        = date('Y-m-d H:i:s');
            $today      = date('Y-m-d');
            $vat_rate   = 18;

            $invoiceType = $inv['invoiceType'];
            $isQuote     = ($invoiceType === 'Corporate Quotation');
            $customer_id = !empty($inv['customerId']) ? (int)$inv['customerId'] : null;
            $discount    = max(0, round((float)$inv['discount'], 2));

            // =============== 1. ITEMS (prices and stock come from the DB) ===============
            $lines = []; $gross = 0; $reserved = [];
            foreach ($inv['items'] as $it) {
                $p = $this->db->where('idtbl_product', (int)$it['id'])->get('tbl_product')->row();
                if (!$p) throw new RuntimeException('Product not found');

                $serial = null; $qty = max(1, (int)$it['qty']); $stockPrice = 0;

                if (!empty($it['serialId'])) {
                    $serial = $this->db->where('idtbl_product_serial', (int)$it['serialId'])
                                    ->where('tbl_product_idtbl_product', $p->idtbl_product)
                                    ->where('stock_status', 1)->where('status', 1)
                                    ->where('tbl_company_branch_idtbl_company_branch', $branch_id)
                                    ->get('tbl_product_serial')->row();
                                    
                    if (!$serial) throw new RuntimeException($p->product_name . ': serial not available');
                    $qty = 1;
                } else {
                    $batches = $this->db->select('idtbl_stock, qty, saleprice')
                        ->where('tbl_product_idtbl_product', $p->idtbl_product)
                        ->where('tbl_company_branch_idtbl_company_branch', $branch_id)
                        // ->where('tbl_location_idtbl_location', $location_id)   // if you sell from one location only
                        ->where('stock_status', 1)->where('status', 1)->where('qty >', 0)
                        ->order_by('idtbl_stock', 'ASC')->get('tbl_stock')->result();

                    if (!$isQuote) {
                        $avail = 0; foreach ($batches as $b) $avail += (float)$b->qty;
                        $already = isset($reserved[$p->idtbl_product]) ? $reserved[$p->idtbl_product] : 0;
                        if ($avail - $already < $qty)
                            throw new RuntimeException($p->product_name . ': only ' . ($avail - $already) . ' in stock');
                        $reserved[$p->idtbl_product] = $already + $qty;
                    }
                    $stockPrice = $batches ? (float)$batches[0]->saleprice : 0;
                }

                if ($serial && (float)$serial->saleprice > 0) $price = (float)$serial->saleprice;
                elseif ($stockPrice > 0)                      $price = $stockPrice;
                else                                          $price = (float)$p->sellingprice;   // CHANGE (fallback only)

                $total = round($price * $qty, 2);
                $gross += $total;

                $lines[] = [
                    'product_code'    => $p->product_code,        // CHANGE
                    'product_name'    => $p->product_name,        // CHANGE
                    'serialno'        => $serial ? $serial->serialno : null,
                    'warranty_months' => $p->warranty_months,     // CHANGE
                    'qty'             => $qty,
                    'unitprice'       => $price,
                    'costunitprice'   => $serial ? (float)$serial->costunitprice : 0,
                    'line_total'      => $total,
                    'tbl_product_idtbl_product'               => $p->idtbl_product,
                    'tbl_product_serial_idtbl_product_serial' => $serial ? $serial->idtbl_product_serial : null,
                    '_grndetail'      => $serial ? $serial->tbl_grndetail_idtbl_grndetail : null,   // removed before insert
                ];
            }

            $net = max(0, round($gross - $discount, 2));
            $vat = round($net - ($net / (1 + $vat_rate / 100)), 2);   // VAT is included in the price

            // =============== 2. CREDIT NOTE ===============
            $cn_id = 0; $creditUsed = 0;
            if (!$isQuote && !empty($inv['creditNoteId'])) {
                $cn_id = (int)$inv['creditNoteId'];
                $cn = $this->db->select('cn.*')->from('tbl_credit_note cn')
                    ->join('tbl_invoice i', 'i.idtbl_invoice = cn.tbl_invoice_idtbl_invoice')
                    ->where('cn.idtbl_credit_note', $cn_id)
                    ->where('i.tbl_customer_idtbl_customer', $customer_id)   // must belong to this customer
                    ->where('cn.status', 1)->where('cn.settle_status', 0)
                    ->get()->row();
                if (!$cn) throw new RuntimeException('Credit note not available');
                $creditUsed = round(min((float)$cn->totalamount, $net), 2);
            }
            $payable = round($net - $creditUsed, 2);

            // =============== 3. PAYMENTS ===============
            $pays = []; $totalPaid = 0; $cash = 0; $usedVouchers = [];
            if (!$isQuote) {
                foreach ($inv['payments'] as $p) {
                    $amt = round((float)$p['amount'], 2);
                    if ($amt <= 0) continue;

                    $pt = $this->db->select('pt.idtbl_payment_type, pt.paytype, pg.type AS group_type')
                        ->from('tbl_payment_type pt')
                        ->join('tbl_payment_group pg', 'pg.idtbl_payment_group = pt.tbl_payment_group_idtbl_payment_group')
                        ->where('pt.paytype', $p['mode'])
                        ->where('pt.status', 1)      // 1 = active, change if your value is different
                        ->where('pg.status', 1)
                        ->get()->row();
                    if (!$pt) throw new RuntimeException('Invalid payment type: ' . $p['mode']);
                    $type = $pt->group_type;

                    if (in_array($type, ['card', 'installment']) &&
                        (!preg_match('/^\d{4}$/', $p['ref']) || $p['ref2'] === ''))
                        throw new RuntimeException($p['mode'] . ': card last 4 digits and approval code are required');
                    if ($type === 'cheque' &&
                        (!preg_match('/^\d{6}$/', $p['ref']) || $p['bank'] === '' || $p['chequeDate'] === ''))
                        throw new RuntimeException('Cheque: bank, 6-digit number and date are required');
                    if ($type === 'transfer' && $p['ref'] === '')
                        throw new RuntimeException('Transfer: reference number is required');

                    $voucher_id = null;
                    if ($type === 'voucher') {
                        $voucher_id = (int)$p['voucherId'];
                        if (in_array($voucher_id, $usedVouchers)) throw new RuntimeException('Voucher added twice');
                        $usedVouchers[] = $voucher_id;
                        $v = $this->db->where('idtbl_gift_voucher', $voucher_id)->get('tbl_gift_voucher')->row();   // CHANGE
                        if (!$v || (float)$v->balance < $amt) throw new RuntimeException('Voucher balance is not enough');   // CHANGE column
                    }

                    $totalPaid += $amt;
                    if ($type === 'cash') $cash += $amt;
                    $pays[] = ['pt' => $pt, 'type' => $type, 'amt' => $amt, 'p' => $p, 'voucher_id' => $voucher_id];
                }
                $totalPaid = round($totalPaid, 2);

                if ($invoiceType === 'Retail Tax Invoice (VAT)' && $totalPaid < $payable)
                    throw new RuntimeException('Payment is less than the invoice total');
                if ($totalPaid > $payable && round($totalPaid - $payable, 2) > round($cash, 2))
                    throw new RuntimeException('Over-payment can only be returned as cash change');
            }

            $change    = $isQuote ? 0 : max(0, round($totalPaid - $payable, 2));
            $paidTotal = $isQuote ? 0 : round(min($totalPaid, $payable) + $creditUsed, 2);   // includes the credit note
            $balance   = $isQuote ? $net : max(0, round($net - $paidTotal, 2));
            $payStatus = $isQuote ? 0 : ($balance <= 0 ? 1 : ($paidTotal > 0 ? 2 : 0));       // CHANGE: 0 unpaid, 1 paid, 2 partial

            // =============== 4. SAVE (all or nothing) ===============
            $this->db->trans_begin();
            $inTrans = true;

            $companybranch = $this->db->select('code')
                                ->where('idtbl_company_branch', $branch_id)
                                ->get('tbl_company_branch')->row();

            $branch_code = $companybranch->code;
            $prefix = date('y') . strtoupper(date('M')) . '_' . $branch_code . '_';
            $seq = $this->db
                ->like('invoice_no', $prefix, 'after')
                ->count_all_results('tbl_invoice') + 1;
            $invoice_no = $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);

            $this->db->insert('tbl_invoice', [
                'invoice_no' => $invoice_no, 'invoice_date' => $today, 'invoice_type' => $invoiceType,
                'customer_nic' => $inv['customerNic'], 'customer_name' => $inv['customerName'],
                'fulfillment' => $inv['fulfillment'],
                'gross_total' => $gross, 'discount' => $discount, 'vat_rate' => $vat_rate, 'vatamount' => $vat,
                'net_total' => $net, 'paid_total' => $paidTotal, 'balance_due' => $balance,
                'change_given' => $change, 'remark' => $cn_id ? 'Credit note #' . $cn_id . ' applied' : '',
                'payment_status' => $payStatus, 'status' => 1,
                'insertdatetime' => $now, 'updateuser' => $userid, 'updatedatetime' => $now,
                'tbl_user_idtbl_user' => $userid, 'tbl_customer_idtbl_customer' => $customer_id,
                'tbl_company_idtbl_company' => $company_id, 'tbl_company_branch_idtbl_company_branch' => $branch_id,
            ]);
            $invoice_id = $this->db->insert_id();
            if (!$invoice_id) throw new Exception('Invoice insert failed');

            // ----- invoice lines + serial / stock -----
            foreach ($lines as $l) {
                $sid       = $l['tbl_product_serial_idtbl_product_serial'];
                $grnDetail = $l['_grndetail'];
                unset($l['_grndetail']);

                // non-serial: take stock first, the real cost comes from the batches used
                if (!$isQuote && !$sid) {
                    $l['costunitprice'] = $this->_deduct_stock($l['tbl_product_idtbl_product'], $l['qty'], $branch_id, $userid, $now);
                }

                $l['status'] = 1; $l['insertdatetime'] = $now;
                $l['tbl_user_idtbl_user'] = $userid; $l['tbl_invoice_idtbl_invoice'] = $invoice_id;
                $this->db->insert('tbl_invoice_detail', $l);
                $detail_id = $this->db->insert_id();

                if (!$isQuote && $sid) {
                    // 1 (in stock) -> 2 (sold), only if still in stock
                    $this->db->where('idtbl_product_serial', $sid)->where('stock_status', 1)->where('status', 1)
                            ->update('tbl_product_serial', [
                                'stock_status' => 2, 'sold_invoice_id' => $invoice_id, 'sold_date' => $today,
                                'updateuser' => $userid, 'updatedatetime' => $now,
                            ]);
                    if ($this->db->affected_rows() !== 1)
                        throw new RuntimeException($l['product_name'] . ' (' . $l['serialno'] . '): serial was just sold');

                    $this->db->insert('tbl_serial_movement', [
                        'movement_type' => 2, 'from_status' => 1, 'to_status' => 2,
                        'doc_type' => 'INVOICE', 'doc_id' => $invoice_id, 'doc_detail_id' => $detail_id,
                        'branch_id' => $branch_id, 'remark' => 'Sold on ' . $invoice_no,
                        'insertdatetime' => $now, 'tbl_user_idtbl_user' => $userid,
                        'tbl_product_serial_idtbl_product_serial' => $sid,
                    ]);

                    // keep tbl_stock in step (delete these 2 statements if serial items are not in tbl_stock)
                    $this->db->set('qty', 'qty - 1', FALSE)->set('updatedatetime', $now)->set('updateuser', $userid)
                            ->where('tbl_grndetail_idtbl_grndetail', $grnDetail)
                            ->where('tbl_product_idtbl_product', $l['tbl_product_idtbl_product'])
                            ->where('qty >=', 1)->update('tbl_stock');
                    $this->db->where('tbl_grndetail_idtbl_grndetail', $grnDetail)
                            ->where('qty <=', 0)->where('stock_status', 1)
                            ->update('tbl_stock', ['stock_status' => 2]);
                }
            }

            // ----- payments -----
            $changeLeft = $change;
            foreach ($pays as $x) {
                $tendered = $x['amt']; $applied = $x['amt'];
                if ($x['type'] === 'cash' && $changeLeft > 0) {      // change is taken from the cash lines
                    $cut = min($changeLeft, $applied);
                    $applied -= $cut; $changeLeft -= $cut;
                }
                $this->db->insert('tbl_invoice_payment', [
                    'paytype' => $x['pt']->paytype, 'group_type' => $x['type'],
                    'amount' => $applied, 'tendered_amount' => $tendered,
                    'ref_no' => $x['p']['ref'], 'approval_code' => $x['p']['ref2'], 'bank' => $x['p']['bank'],
                    'installment_months' => $x['type'] === 'installment' ? (int)$x['p']['months'] : null,
                    'cheque_date'   => $x['type'] === 'cheque' ? $x['p']['chequeDate'] : null,
                    'cheque_status' => $x['type'] === 'cheque' ? 0 : null,
                    'tbl_gift_voucher_idtbl_gift_voucher' => $x['voucher_id'],
                    'status' => 1, 'insertdatetime' => $now, 'tbl_user_idtbl_user' => $userid,
                    'tbl_invoice_idtbl_invoice' => $invoice_id,
                    'tbl_payment_type_idtbl_payment_type' => $x['pt']->idtbl_payment_type,   // CHANGE
                ]);

                if ($x['type'] === 'voucher') {
                    $this->db->set('balance', 'balance - ' . (float)$applied, FALSE)          // CHANGE column
                            ->where('idtbl_gift_voucher', $x['voucher_id'])
                            ->where('balance >=', $applied)->update('tbl_gift_voucher');
                    if ($this->db->affected_rows() !== 1)
                        throw new RuntimeException('Voucher balance changed, please verify again');

                    $after = $this->db->select('balance')->where('idtbl_gift_voucher', $x['voucher_id'])
                                    ->get('tbl_gift_voucher')->row()->balance;
                    $this->db->insert('tbl_gift_voucher_log', [
                        'action' => 'redeem', 'amount' => $applied, 'balance_after' => $after, 'status' => 1,
                        'insertdatetime' => $now, 'tbl_user_idtbl_user' => $userid,
                        'tbl_gift_voucher_idtbl_gift_voucher' => $x['voucher_id'],
                        'tbl_invoice_idtbl_invoice' => $invoice_id,
                    ]);
                }
            }

            // ----- credit note -----
            if ($cn_id && $creditUsed > 0) {
                $this->db->insert('tbl_invoice_payment', [
                    'paytype' => 'Credit Note', 'group_type' => 'creditnote',
                    'amount' => $creditUsed, 'tendered_amount' => $creditUsed, 'ref_no' => $cn_id,
                    'status' => 1, 'insertdatetime' => $now, 'tbl_user_idtbl_user' => $userid,
                    'tbl_invoice_idtbl_invoice' => $invoice_id,
                    'tbl_payment_type_idtbl_payment_type' => null,   // column must allow NULL
                ]);

                $this->db->where('idtbl_credit_note', $cn_id)->where('settle_status', 0)
                        ->update('tbl_credit_note', [
                            'settle_status' => 1, 'settle_invoice_id' => $invoice_id,
                            'updateuser' => $userid, 'updatedatetime' => $now,
                        ]);
                if ($this->db->affected_rows() !== 1)
                    throw new RuntimeException('Credit note was already used');
            }

            if ($this->db->trans_status() === FALSE) {
                $err = $this->db->error();
                throw new Exception('DB error: ' . $err['code'] . ' ' . $err['message']);
            }

            $this->db->trans_commit();
            $inTrans = false;

            return $this->_jsonResponse(1, 'fas fa-check-circle', 'Invoice saved', 'success', ['invoice_no' => $invoice_no, 'invoice_id' => $invoice_id]);

        } catch (RuntimeException $e) {
            // expected problem: show the message to the cashier
            if ($inTrans) $this->db->trans_rollback();
            return $this->_jsonResponse(0, 'fas fa-exclamation-triangle', $e->getMessage(), 'danger');

        } catch (Throwable $e) {
            // unexpected error: log the details, show a short message
            if ($inTrans) $this->db->trans_rollback();
            log_message('error', 'Insertinvoice failed: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            return $this->_jsonResponse(0, 'fas fa-exclamation-triangle', $e->getMessage(), 'danger');

        } finally {
            $this->db->db_debug = $old_debug;
        }
    }
    private function _deduct_stock($product_id, $qty, $branch_id, $userid, $now){
        // FOR UPDATE locks these rows, so two terminals cannot take the same stock
        $rows = $this->db->query(
            "SELECT idtbl_stock, qty, costunitprice FROM tbl_stock
            WHERE tbl_product_idtbl_product = ? AND tbl_company_branch_idtbl_company_branch = ?
            AND stock_status = 1 AND status = 1 AND qty > 0
            ORDER BY idtbl_stock ASC FOR UPDATE", [$product_id, $branch_id])->result();

        $left = $qty; $costSum = 0;
        foreach ($rows as $r) {
            if ($left <= 0) break;
            $take = min($left, (float)$r->qty);

            $this->db->set('qty', 'qty - ' . (float)$take, FALSE)
                    ->set('updatedatetime', $now)->set('updateuser', $userid)
                    ->where('idtbl_stock', $r->idtbl_stock)->where('qty >=', $take)
                    ->update('tbl_stock');
            if ($this->db->affected_rows() !== 1) throw new RuntimeException('Stock changed, please try again');

            $this->db->where('idtbl_stock', $r->idtbl_stock)->where('qty <=', 0)
                    ->update('tbl_stock', ['stock_status' => 2]);   // batch finished

            $costSum += $take * (float)$r->costunitprice;
            $left    -= $take;
        }
        if ($left > 0) throw new RuntimeException('Not enough stock');
        return round($costSum / $qty, 2);
    }
    public function get_open_credit_notes($customer_id) {
        return $this->db->select('cn.idtbl_credit_note AS id, cn.creditnote_date AS date, cn.totalamount AS balance')
            ->from('tbl_credit_note cn')
            ->join('tbl_invoice i', 'i.idtbl_invoice = cn.tbl_invoice_idtbl_invoice')
            ->where('i.tbl_customer_idtbl_customer', $customer_id)   
            ->where('cn.status', 1)
            ->where('cn.settle_status', 0)                           
            ->get()->result();
    }
    // public function Printinvoice($invoice_id = 0){
    //     $branch_id=$_SESSION['branch_id'];
    //     $inv = $this->db->where('idtbl_invoice', (int)$invoice_id)
    //                     ->where('tbl_company_branch_idtbl_company_branch', $branch_id)   // only this branch's invoices
    //                     ->get('tbl_invoice')->row();
    //     if (!$inv) show_404();

    //     $items = $this->db->where('tbl_invoice_idtbl_invoice', $inv->idtbl_invoice)->where('status', 1)
    //                     ->order_by('idtbl_invoice_detail', 'ASC')->get('tbl_invoice_detail')->result();
    //     $pays  = $this->db->where('tbl_invoice_idtbl_invoice', $inv->idtbl_invoice)->where('status', 1)
    //                     ->get('tbl_invoice_payment')->result();
    //     $customer = $inv->tbl_customer_idtbl_customer
    //         ? $this->db->where('idtbl_customer', $inv->tbl_customer_idtbl_customer)->get('tbl_customer')->row()   // CHANGE columns in the view
    //         : null;

    //     // mode of payment text
    //     $modes = [];
    //     foreach ($pays as $p) {
    //         if ((float)$p->amount > 0) $modes[] = $p->paytype . ' (' . number_format($p->amount, 2) . ')';
    //     }

    //     $logoFile = FCPATH . 'images/header.png'; 
    //     $logo = is_file($logoFile) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile)) : '';

    //     $data = [
    //         'inv'      => $inv,
    //         'items'    => $items,
    //         'customer' => $customer,
    //         'logo'     => $logo,
    //         'title'    => ($inv->invoice_type === 'Corporate Quotation') ? 'QUOTATION' : 'TAX INVOICE',
    //         'rate'     => 1 + ((float)$inv->vat_rate / 100),
    //         'supply'   => round($inv->net_total - $inv->vatamount, 2),   // total value of supply (excl. VAT, after discount)
    //         'words'    => $this->_amount_in_words($inv->net_total),
    //         'modes'    => $modes ? implode('  |  ', $modes) : '-',
    //         // supplier details: CHANGE (or read them from your company table)
    //         'company'  => [
    //             'tin'     => '000000000-7000',
    //             'name'    => 'Spencersz Lanka (Pvt) Ltd',
    //             'address' => '295, Negombo Road, Wattala',
    //             'tel'     => '+94 11 000 0000',
    //             'place'   => 'Wattala',
    //         ],
    //     ];

    //     $html = $this->load->view('invoice_pdf', $data, TRUE);

    //     $this->load->library('pdf');

    //     // only if your library does not already set these
    //     $this->pdf->getOptions()->set('defaultFont', 'DejaVu Sans');
    //     $this->pdf->getOptions()->set('isRemoteEnabled', false);

    //     $this->pdf->loadHtml($html);
    //     $this->pdf->setPaper('A4', 'portrait');
    //     $this->pdf->render();

    //     if (ob_get_length()) ob_end_clean();   // nothing may be printed before the PDF
    //     $this->pdf->stream('SPENCERSZ-INVOICE-' . $inv->invoice_no . '.pdf', ['Attachment' => 0]);
    // }
    public function Printinvoice($invoice_id = 0){
        $branch_id = $_SESSION['branch_id'];
        $inv = $this->db->where('idtbl_invoice', (int)$invoice_id)
                        ->where('tbl_company_branch_idtbl_company_branch', $branch_id) 
                        ->get('tbl_invoice')->row();
        if (!$inv) show_404();

        $items = $this->db->where('tbl_invoice_idtbl_invoice', $inv->idtbl_invoice)->where('status', 1)
                        ->order_by('idtbl_invoice_detail', 'ASC')->get('tbl_invoice_detail')->result();
        $pays  = $this->db->where('tbl_invoice_idtbl_invoice', $inv->idtbl_invoice)->where('status', 1)
                        ->get('tbl_invoice_payment')->result();
        $customer = $inv->tbl_customer_idtbl_customer
            ? $this->db->where('idtbl_customer', $inv->tbl_customer_idtbl_customer)->get('tbl_customer')->row() 
            : null;

        $modes = [];
        foreach ($pays as $p) {
            if ((float)$p->amount > 0) $modes[] = $p->paytype . ' (' . number_format($p->amount, 2) . ')';
        }

        // Load Header & Footer images as Base64
        $headerFile = FCPATH . 'images/header.png';
        $headerImg  = is_file($headerFile) ? 'data:image/png;base64,' . base64_encode(file_get_contents($headerFile)) : '';

        $footerFile = FCPATH . 'images/footer.png';
        $footerImg  = is_file($footerFile) ? 'data:image/png;base64,' . base64_encode(file_get_contents($footerFile)) : '';

        $data = [
            'inv'       => $inv,
            'items'     => $items,
            'customer'  => $customer,
            'headerImg' => $headerImg,
            'footerImg' => $footerImg,
            'title'     => ($inv->invoice_type === 'Corporate Quotation') ? 'QUOTATION' : (($inv->invoice_type === 'Retail Invoice') ? 'INVOICE' : 'TAX INVOICE'),
            'rate'      => 1 + ((float)$inv->vat_rate / 100),
            'supply'    => round($inv->net_total - $inv->vatamount, 2),
            'words'     => $this->_amount_in_words($inv->net_total),
            'modes'     => $modes ? implode('  |  ', $modes) : '-',
            'company'   => [
                'tin'     => '000000000-7000',
                'name'    => 'Spencersz Lanka (Pvt) Ltd',
                'address' => '295, Negombo Road, Wattala',
                'tel'     => '+94 11 000 0000',
                'place'   => 'Wattala',
            ],
        ];

        $html = $this->load->view('invoice_pdf', $data, TRUE);

        $this->load->library('pdf');
        $this->pdf->getOptions()->set('defaultFont', 'DejaVu Sans');
        $this->pdf->getOptions()->set('isRemoteEnabled', false);

        $this->pdf->loadHtml($html);
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->render();

        if (ob_get_length()) ob_end_clean();
        $this->pdf->stream('SPENCERSZ-INVOICE-' . $inv->invoice_no . '.pdf', ['Attachment' => 0]);
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
    private function _amount_in_words($amount){
        $amount = round((float)$amount, 2);
        $rupees = (int)floor($amount);
        $cents  = (int)round(($amount - $rupees) * 100);
        $words  = 'Rupees ' . $this->_num_words($rupees);
        if ($cents > 0) $words .= ' and Cents ' . $this->_num_words($cents);
        return $words . ' Only';
    }
    private function _num_words($n){
        if ($n == 0) return 'Zero';
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven',
                'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $scales = [1000000000 => 'Billion', 1000000 => 'Million', 1000 => 'Thousand', 100 => 'Hundred'];

        $out = '';
        foreach ($scales as $v => $name) {
            if ($n >= $v) {
                $out .= $this->_num_words((int)floor($n / $v)) . ' ' . $name . ' ';
                $n %= $v;
            }
        }
        if ($n > 0) {
            $out .= ($n < 20) ? $ones[$n] : $tens[(int)($n / 10)] . ($n % 10 ? ' ' . $ones[$n % 10] : '');
        }
        return trim($out);
    }
}