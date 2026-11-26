<?php
use Dompdf\Dompdf;
use Dompdf\Options;

class Invoiceinfo extends CI_Model{
    private $printOffX = 0;   // shift the printing right (+) / left (-), in points
    private $printOffY = 0;   // shift the printing down (+) / up (-), in points

    const FIFO_PERMISSION = 'invoice-approve'; 
    const DISCOUNT_PERMISSION = 'invoice-approve';
    const ITEMDISC_PERMISSION = 'invoice-approve';
    const FIFO_GUARD      = 'web-erp';
    const FIFO_TOKEN_MINUTES = 15;

    public function Getemployeelist(){
        $this->db->select('idtbl_employee, emp_no, full_name');
        $this->db->from('tbl_employee');
        $this->db->where('status', 1);
        $this->db->order_by('full_name');
        return $this->db->get()->result();
    }
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
    // public function Getproductdetails(){
    //     $productbarcordname = trim((string)$this->input->post('productbarcordname'));

    //     // $this->db->select('idtbl_product, product_code, product_name, model_no, description, cost_price, selling_price, warranty_months');
    //     $this->db->select('idtbl_product, product_code, product_name, model_no, description, cost_price, selling_price, warranty_months, discount_percent, discount_start, discount_end');
    //     $this->db->from('tbl_product');
    //     $this->db->where('status', 1);
    //     $this->db->group_start();
    //         $this->db->where('product_code', $productbarcordname);
    //         $this->db->or_like('product_name', $productbarcordname);
    //     $this->db->group_end();
    //     $respond = $this->db->get();

    //     if ($respond->num_rows() > 0) {
    //         foreach ($respond->result() as $row) {
    //             $productList[] = array(
    //                 'idtbl_product' => $row->idtbl_product,
    //                 'product_code' => $row->product_code,
    //                 'product_name' => $row->product_name,
    //                 'model_no' => $row->model_no,
    //                 'description' => $row->description,
    //                 'cost_price' => $row->cost_price,
    //                 'selling_price' => $row->selling_price,
    //                 'warranty_months' => $row->warranty_months,
    //                 'discount_percent' => $this->activediscount($row)
    //             );

    //             // $this->db->select('`idtbl_product_serial`, `serialno`, `costunitprice`, `saleprice`, `tbl_product_idtbl_product`');
    //             // $this->db->from('tbl_product_serial');
    //             // $this->db->where('tbl_product_idtbl_product', $row->idtbl_product);
    //             // $this->db->where('status', 1);
    //             // $this->db->where_in('stock_status', array(1, 5, 6));

    //             $this->db->select('ps.idtbl_product_serial, ps.serialno, ps.costunitprice, ps.saleprice, ps.tbl_product_idtbl_product, ps.tbl_grn_idtbl_grn AS grn, g.grn_no, g.batchno, g.grndate');
    //             $this->db->from('tbl_product_serial ps');
    //             $this->db->join('tbl_grn g', 'g.idtbl_grn = ps.tbl_grn_idtbl_grn');
    //             $this->db->where('ps.tbl_product_idtbl_product', $row->idtbl_product);
    //             $this->db->where('ps.status', 1);
    //             $this->db->where('ps.tbl_company_branch_idtbl_company_branch', (int)$_SESSION['branch_id']);
    //             $this->db->where_in('ps.stock_status', array(1, 5, 6));
    //             $this->db->order_by('g.grndate');
    //             $this->db->order_by('g.idtbl_grn');
    //             $this->db->order_by('ps.idtbl_product_serial');

    //             $serialRespond = $this->db->get();
    //             $productList[count($productList) - 1]['serials'] = $serialRespond->result();
    //         }
    //         echo json_encode(array(
    //             'status' => 1,
    //             'products' => $productList
    //         ));
    //     } else {
    //         echo json_encode(array(
    //             'status' => 0,
    //             'message' => 'Product not found'
    //         ));
    //     }
    // }
    public function Getproductdetails(){
        $productbarcordname = trim((string)$this->input->post('productbarcordname'));

        // $this->db->select('idtbl_product, product_code, product_name, model_no, description, cost_price, selling_price, warranty_months, discount_percent, discount_start, discount_end');
        $this->db->select('idtbl_product, product_code, product_name, model_no, description, cost_price, selling_price, warranty_months, has_serial, discount_percent, discount_start, discount_end');
        $this->db->from('tbl_product');
        $this->db->where('status', 1);
        $this->db->group_start();
            $this->db->where('product_code', $productbarcordname);
            $this->db->or_like('product_name', $productbarcordname);
        $this->db->group_end();
        $products = $this->db->get()->result();

        if (empty($products)) {
            echo json_encode(array('status' => 0, 'message' => 'Product not found'));
            return;
        }

        // ONE query for the serials of all these products (was: one query per product)
        $ids = array();
        foreach ($products as $row) { $ids[] = (int)$row->idtbl_product; }
        $availMap = $this->availableqty($ids, (int)$_SESSION['branch_id']);

        $partsByProduct = $this->partwarranties($ids);

        $this->db->select('ps.idtbl_product_serial, ps.serialno, ps.costunitprice, ps.saleprice, ps.tbl_product_idtbl_product, ps.tbl_grn_idtbl_grn AS grn, g.grn_no, g.batchno, g.grndate');
        $this->db->from('tbl_product_serial ps');
        $this->db->join('tbl_grn g', 'g.idtbl_grn = ps.tbl_grn_idtbl_grn');
        $this->db->where_in('ps.tbl_product_idtbl_product', $ids);
        $this->db->where('ps.status', 1);
        $this->db->where('ps.tbl_company_branch_idtbl_company_branch', (int)$_SESSION['branch_id']);
        $this->db->where_in('ps.stock_status', array(1, 5, 6));
        $this->db->order_by('g.grndate');
        $this->db->order_by('g.idtbl_grn');
        $this->db->order_by('ps.idtbl_product_serial');
        $serialRows = $this->db->get()->result();

        $serialsByProduct = array();
        foreach ($serialRows as $s) {
            $serialsByProduct[(int)$s->tbl_product_idtbl_product][] = $s;   // keeps the GRN (FIFO) order
        }

        $productList = array();
        foreach ($products as $row) {
            $productList[] = array(
                'idtbl_product' => $row->idtbl_product,
                'product_code' => $row->product_code,
                'product_name' => $row->product_name,
                'model_no' => $row->model_no,
                'description' => $row->description,
                'cost_price' => $row->cost_price,
                'selling_price' => $row->selling_price,
                'warranty_months' => $row->warranty_months,
                'has_serial' => (int)$row->has_serial,
                'discount_percent' => $this->activediscount($row),
                'available_qty' => isset($availMap[(int)$row->idtbl_product]) ? $availMap[(int)$row->idtbl_product] : 0,
                'warranty_parts' => isset($partsByProduct[(int)$row->idtbl_product]) ? $partsByProduct[(int)$row->idtbl_product] : array(),
                'serials' => isset($serialsByProduct[(int)$row->idtbl_product]) ? $serialsByProduct[(int)$row->idtbl_product] : array()
            );
        }

        echo json_encode(array('status' => 1, 'products' => $productList));
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

            $fifoTokens = (isset($inv['fifoTokens']) && is_array($inv['fifoTokens'])) ? $inv['fifoTokens'] : [];
            $bypassed   = [];
            $itemDiscLog    = [];
            $itemDiscTokens = [];

            $userid=$_SESSION['userid'];
            $company_id=$_SESSION['company_id'];
            $branch_id=$_SESSION['branch_id'];
            $now        = date('Y-m-d H:i:s');
            $today      = date('Y-m-d');
            $vat_rate   = 18;
            // invoice date chosen by the cashier (default today): not in the future, and not on a day that is already closed
            $invoiceDate = isset($inv['invoiceDate']) ? trim((string)$inv['invoiceDate']) : $today;
            $idt = DateTime::createFromFormat('Y-m-d', $invoiceDate);
            if (!$idt || $idt->format('Y-m-d') !== $invoiceDate) throw new RuntimeException('Invalid invoice date');
            if ($invoiceDate > $today) throw new RuntimeException('Invoice date cannot be in the future');
            if ($invoiceDate < $today) {
                $closedDay = $this->db->query("SELECT 1 FROM tbl_day_end WHERE day_date = ? AND tbl_company_branch_idtbl_company_branch = ? AND status = 1",
                                              array($invoiceDate, $branch_id))->row();
                if ($closedDay) throw new RuntimeException('Day end is already done for ' . $invoiceDate . '. Choose another date.');
            }

            $invoiceType = $inv['invoiceType'];
            $isQuote     = ($invoiceType === 'Corporate Quotation');
            $customer_id = !empty($inv['customerId']) ? (int)$inv['customerId'] : null;
            $sales_rep_id = !empty($inv['salesRepId']) ? (int)$inv['salesRepId'] : null;
            $discount    = max(0, round((float)$inv['discount'], 2));

            // a discount needs the approval of an authorised person
            $discountToken = isset($inv['discountToken']) ? (string)$inv['discountToken'] : '';
            $discountOv    = null;
            if ($discount > 0) {
                $discountOv = ($discountToken !== '') ? $this->discountoverride($discountToken, $discount) : null;
                if (!$discountOv) throw new RuntimeException('Discount needs approval from an authorised person.');
            }

            $this->db->trans_begin();
            $inTrans = true;

            // =============== 1. ITEMS (prices and stock come from the DB) ===============
            $lines = []; $gross = 0; $reserved = []; $usedSerial = []; $serialLines = [];
            foreach ($inv['items'] as $it) {
                $p = $this->db->where('idtbl_product', (int)$it['id'])->get('tbl_product')->row();
                if (!$p) throw new RuntimeException('Product not found');
                
                $serial = null; $qty = max(1, (int)$it['qty']); $stockPrice = 0;

                $sn = isset($it['serialNo']) ? trim((string)$it['serialNo']) : '';

                // PRE-ORDER line: sold now, delivered later (stock / serial / cost are taken at the delivery)
                $isPre = (!$isQuote && !empty($it['preorder']));
                $deliveryDate = null;
                if ($isPre) {
                    $deliveryDate = isset($it['deliveryDate']) ? trim((string)$it['deliveryDate']) : '';
                    $dt = DateTime::createFromFormat('Y-m-d', $deliveryDate);
                    if (!$dt || $dt->format('Y-m-d') !== $deliveryDate || $deliveryDate < $today)
                        throw new RuntimeException($p->product_name . ': enter the delivery date (today or later)');
                    if (!$customer_id) throw new RuntimeException('Select the customer for pre-order items');
                    if ((int)$p->has_serial === 1) $qty = 1;   // serial items: one line per unit, the serial is chosen at the delivery
                }

                if ($isPre) {
                    // nothing to take from the stock now
                } elseif (!empty($it['serialId'])) {
                    $serial = $this->db->where('idtbl_product_serial', (int)$it['serialId'])
                                    ->where('tbl_product_idtbl_product', $p->idtbl_product)
                                    ->where('stock_status', 1)->where('status', 1)
                                    ->where('tbl_company_branch_idtbl_company_branch', $branch_id)
                                    ->get('tbl_product_serial')->row();
                                    
                    if (!$serial) throw new RuntimeException($p->product_name . ': serial not available');
                    $qty = 1;
                } elseif ($sn !== '') {
                    // TEMPORARY: serial typed by the cashier
                    $serial = $this->_manual_serial($p, $sn, (float)$p->selling_price, $isQuote, $branch_id, $company_id, $userid, $now);
                    $qty = 1;
                } else {
                    $batches = $this->db->select('idtbl_stock, qty, saleprice')
                        ->where('tbl_product_idtbl_product', $p->idtbl_product)
                        ->where('tbl_company_branch_idtbl_company_branch', $branch_id)
                        // ->where('tbl_location_idtbl_location', $location_id)   // if you sell from one location only
                        ->where('stock_status', 1)->where('status', 1)->where('qty >', 0)
                        ->order_by('idtbl_stock', 'ASC')->get('tbl_stock')->result();

                    // if (!$isQuote) {
                    //     $avail = 0; foreach ($batches as $b) $avail += (float)$b->qty;
                    //     $already = isset($reserved[$p->idtbl_product]) ? $reserved[$p->idtbl_product] : 0;
                    //     if ($avail - $already < $qty)
                    //         throw new RuntimeException($p->product_name . ': only ' . ($avail - $already) . ' in stock');
                    //     $reserved[$p->idtbl_product] = $already + $qty;
                    // }
                    if (!$isQuote) {
                        $avail = 0; foreach ($batches as $b) $avail += (float)$b->qty;
                        $already = isset($reserved[$p->idtbl_product]) ? $reserved[$p->idtbl_product] : 0;
                        $pend    = $this->pendingqty($p->idtbl_product, $branch_id);   // reserved for pending deliveries
                        if ($avail - $pend - $already < $qty)
                            throw new RuntimeException($p->product_name . ': only ' . max(0, $avail - $pend - $already) . ' available' . ($pend ? ' (' . $pend . ' reserved for pending deliveries)' : ''));
                        $reserved[$p->idtbl_product] = $already + $qty;
                    }
                    $stockPrice = $batches ? (float)$batches[0]->saleprice : 0;
                }

                // the same serial cannot be on two lines
                if ($serial && $serial->idtbl_product_serial) {
                    if (isset($usedSerial[$serial->idtbl_product_serial])) throw new RuntimeException('Serial added twice: ' . $serial->serialno);
                    $usedSerial[$serial->idtbl_product_serial] = true;
                    if ($sn === '' && !$isQuote) {
                        if (!isset($serialLines[$p->idtbl_product])) $serialLines[$p->idtbl_product] = ['n' => 0, 'name' => $p->product_name];
                        $serialLines[$p->idtbl_product]['n']++;
                    }
                }

                if ($serial && (float)$serial->saleprice > 0) $price = (float)$serial->saleprice;
                elseif ($stockPrice > 0)                      $price = $stockPrice;
                else                                          $price = (float)$p->selling_price;   // CHANGE (fallback only)

                // product discount (the browser price is never trusted, it is calculated here)
                // $listPrice = $price;
                // $discPct   = $this->activediscount($p);
                // if ($discPct > 0) $price = round($listPrice * (1 - $discPct / 100), 2);

                // product discount, then the extra item discount given by an authorised person
                $listPrice = $price;
                $discPct   = $this->activediscount($p);
                if ($discPct > 0) $price = round($listPrice * (1 - $discPct / 100), 2);

                $extraPct = isset($it['extraDisc']) ? round((float)$it['extraDisc'], 2) : 0;
                if ($extraPct > 0) {
                    $extraTok = isset($it['extraToken']) ? (string)$it['extraToken'] : '';
                    $extraOv  = ($extraPct < 100 && $extraTok !== '')
                              ? $this->itemdiscountoverride($extraTok, (int)$p->idtbl_product, $extraPct) : null;
                    if (!$extraOv) throw new RuntimeException($p->product_name . ': item discount needs approval from an authorised person.');

                    $before = $price;
                    $price  = round($before * (1 - $extraPct / 100), 2);
                    $itemDiscLog[]            = array('pid' => (int)$p->idtbl_product, 'pct' => $extraPct,
                                                      'amount' => round(($before - $price) * $qty, 2), 'ov' => $extraOv);
                    $itemDiscTokens[$extraTok] = true;
                } else {
                    $extraPct = 0;
                }

                $total = round($price * $qty, 2);
                $gross += $total;

                $lines[] = [
                    'product_code'    => $p->product_code,        // CHANGE
                    'product_name'    => $p->product_name,        // CHANGE
                    'serialno'        => $serial ? $serial->serialno : '',
                    'warranty_months' => $p->warranty_months,     // CHANGE
                    'warranty_parts'  => $this->partsjson($p->idtbl_product),
                    'qty'             => $qty,
                    'is_preorder'     => $isPre ? 1 : 0,
                    'delivery_status' => $isPre ? 2 : 1,
                    'delivered_qty'   => $isPre ? 0 : $qty,
                    'delivery_date'   => $deliveryDate,
                    'unitprice'       => $price,
                    'list_price'      => $listPrice,
                    'discount_percent'=> $discPct,
                    'extra_discount_percent' => $extraPct,
                    'discount_amount' => round(($listPrice - $price) * $qty, 2),
                    'costunitprice'   => $serial ? (float)$serial->costunitprice : 0,
                    'line_total'      => $total,
                    'tbl_product_idtbl_product'               => $p->idtbl_product,
                    'tbl_product_serial_idtbl_product_serial' => $serial ? $serial->idtbl_product_serial : null,
                    '_grndetail'      => $serial ? $serial->tbl_grndetail_idtbl_grndetail : null,   // removed before insert
                    '_manual'         => ($sn !== ''),   // removed before insert
                ];
            }
            // serials that are reserved for pending deliveries are not sold to other customers
            foreach ($serialLines as $pid => $sl) {
                $free = (int)$this->db->query("SELECT COUNT(*) AS c FROM tbl_product_serial
                                               WHERE tbl_product_idtbl_product = ? AND status = 1 AND stock_status = 1
                                               AND tbl_company_branch_idtbl_company_branch = ?", array((int)$pid, $branch_id))->row()->c;
                $pend = $this->pendingqty($pid, $branch_id);
                if ($free - $pend < $sl['n'])
                    throw new RuntimeException($sl['name'] . ': ' . $pend . ' unit(s) are reserved for pending deliveries');
            }

            $net = max(0, round($gross - $discount, 2));
            $vat = round($net - ($net / (1 + $vat_rate / 100)), 2);   // VAT is included in the price

            // FIFO: a serial from a newer GRN cannot be sold while serials of an older GRN are still free
            if (!$isQuote) {
                $byProduct = [];
                foreach ($lines as $l) {
                    if ($l['tbl_product_serial_idtbl_product_serial'] && empty($l['_manual'])) {
                        $byProduct[$l['tbl_product_idtbl_product']][] = (int)$l['tbl_product_serial_idtbl_product_serial'];
                    }
                }
                // foreach ($byProduct as $pid => $ids) {
                //     $fifo = $this->fifocheck((int)$pid, $branch_id, $ids);
                //     if ($fifo !== true) throw new RuntimeException($fifo);
                // }
                foreach ($byProduct as $pid => $ids) {
                    $note = '';
                    $fifo = $this->fifocheck((int)$pid, $branch_id, $ids, $note);
                    if ($fifo === true) continue;

                    $token = isset($fifoTokens[$pid]) ? (string)$fifoTokens[$pid] : '';
                    if ($token === '') throw new RuntimeException($fifo);

                    $ov = $this->fifooverride($token, (int)$pid);
                    if (!$ov) throw new RuntimeException('FIFO approval has expired. Request the approval again.');

                    $bypassed[] = ['pid' => (int)$pid, 'msg' => $note, 'ov' => $ov, 'token' => $token];
                }
            }

            // =============== 2. CREDIT NOTE ===============
            $cn_id = 0; $creditUsed = 0;
            if (!$isQuote && !empty($inv['creditNoteId'])) {
                $cn_id = (int)$inv['creditNoteId'];
                $cn = $this->db->select('cn.*')->from('tbl_credit_note cn')
                    ->join('tbl_invoice i', 'i.idtbl_invoice = cn.tbl_invoice_idtbl_invoice')
                    ->where('cn.idtbl_credit_note', $cn_id)
                    ->where('i.tbl_customer_idtbl_customer', $customer_id)   // must belong to this customer
                    ->where('cn.status', 1)->where('cn.settle_status', 0)
                    ->where('cn.approve_status', 1)
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

            $quotation_id = !empty($inv['quotationId']) ? (int)$inv['quotationId'] : 0;
            $reference = '';
            if ($quotation_id && !$isQuote) {
                $q = $this->db->where('idtbl_invoice', $quotation_id)->where('invoice_type', 'Corporate Quotation')
                            ->where('converted_invoice_id IS NULL', null, false)->get('tbl_invoice')->row();
                if (!$q) throw new RuntimeException('Quotation not found or already converted');
                $reference = 'Quotation ' . $q->invoice_no;
            }

            // =============== 4. SAVE (all or nothing) ===============

            $branch = $this->db->select('code')->where('idtbl_company_branch', $branch_id)
                   ->get('tbl_company_branch')->row();
            if (!$branch) throw new RuntimeException('Branch not found');

            // QQQQ: exactly 4 letters/digits, upper case, no symbols (example: BR03)
            $qqqq = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string)$branch->code));
            if (strlen($qqqq) !== 4) throw new RuntimeException('Branch code must be exactly 4 letters/digits (example: BR03)');

            // quotation = its own series (QT + last 2 characters of the branch code), so tax invoice numbers stay continuous
            if ($isQuote) $qqqq = 'QT' . substr($qqqq, -2);

            $prefix     = date('y') . strtoupper(date('M')) . '_' . $qqqq . '_';   // 26OCT_BR03_   or   26OCT_QT03_
            $likePrefix = str_replace('_', '\_', $prefix) . '%';                   // "_" is a LIKE wildcard, so escape it

            // FOR UPDATE: a second cashier waits here until this invoice is saved, so numbers can never repeat
            $last = $this->db->query(
                "SELECT MAX(CAST(SUBSTRING(invoice_no, ?) AS UNSIGNED)) AS m
                FROM tbl_invoice WHERE invoice_no LIKE ? FOR UPDATE",
                [strlen($prefix) + 1, $likePrefix]
            )->row();

            $invoice_no = $prefix . str_pad(((int)$last->m) + 1, 5, '0', STR_PAD_LEFT);   // 26OCT_BR03_00001

            $this->db->insert('tbl_invoice', [
                'invoice_no' => $invoice_no, 'invoice_date' => $invoiceDate, 'invoice_type' => $invoiceType,
                'customer_nic' => $inv['customerNic'], 'customer_name' => $inv['customerName'],
                'fulfillment' => $inv['fulfillment'],
                'gross_total' => $gross, 'discount' => $discount, 'vat_rate' => $vat_rate, 'vatamount' => $vat,
                'net_total' => $net, 'paid_total' => $paidTotal, 'balance_due' => $balance,
                'change_given' => $change, 'remark' => trim($reference . ($cn_id ? ' | Credit note #' . $cn_id . ' applied' : ''), ' |'),
                'payment_status' => $payStatus, 'status' => 1,
                'insertdatetime' => $now, 'updateuser' => $userid, 'updatedatetime' => $now,
                'tbl_user_idtbl_user' => $userid, 'tbl_customer_idtbl_customer' => $customer_id,
                'tbl_company_idtbl_company' => $company_id, 'tbl_company_branch_idtbl_company_branch' => $branch_id, 'salesrepid' => $sales_rep_id
            ]);
            $invoice_id = $this->db->insert_id();
            if (!$invoice_id) throw new Exception('Invoice insert failed');

            foreach ($bypassed as $b) {
                $this->db->insert('tbl_fifo_ignore_log', [
                    'doc_type'                  => 'INVOICE',
                    'doc_id'                    => $invoice_id,
                    'tbl_product_idtbl_product' => $b['pid'],
                    'approved_by'               => $b['ov']['approved_by'],
                    'reason'                    => $b['ov']['reason'],
                    'remark'                    => $b['msg'],
                    'status'                    => 1,
                    'insertdatetime'            => $now,
                    'tbl_user_idtbl_user'       => $userid,
                ]);
            }

            foreach ($itemDiscLog as $d) {
                $this->db->insert('tbl_discount_approval_log', [
                    'doc_type'                  => 'INVOICE_ITEM',
                    'doc_id'                    => $invoice_id,
                    'tbl_product_idtbl_product' => $d['pid'],
                    'discount_percent'          => $d['pct'],
                    'discount_amount'           => $d['amount'],
                    'approved_by'               => $d['ov']['approved_by'],
                    'reason'                    => $d['ov']['reason'],
                    'status'                    => 1,
                    'insertdatetime'            => $now,
                    'tbl_user_idtbl_user'       => $userid,
                ]);
            }

            if ($discountOv) {
                $this->db->insert('tbl_discount_approval_log', [
                    'doc_type'            => 'INVOICE',
                    'doc_id'              => $invoice_id,
                    'discount_amount'     => $discount,
                    'approved_by'         => $discountOv['approved_by'],
                    'reason'              => $discountOv['reason'],
                    'status'              => 1,
                    'insertdatetime'      => $now,
                    'tbl_user_idtbl_user' => $userid,
                ]);
            }

            // ----- hold invoice -----
            $hold_id = !empty($inv['holdId']) ? (int)$inv['holdId'] : 0;
            if ($hold_id) {
                $this->db->where('idtbl_invoice_hold', $hold_id)->where('status', 1)
                        ->update('tbl_invoice_hold', ['status' => 2, 'updatedatetime' => $now, 'updateuser' => $userid]);
            }

            if ($quotation_id && $reference !== '') {
                $this->db->where('idtbl_invoice', $quotation_id)
                        ->update('tbl_invoice', ['converted_invoice_id' => $invoice_id, 'updatedatetime' => $now, 'updateuser' => $userid]);
            }
            
            // ----- invoice lines + serial / stock -----
            foreach ($lines as $l) {
                $sid       = $l['tbl_product_serial_idtbl_product_serial'];
                $grnDetail = $l['_grndetail'];
                unset($l['_grndetail'], $l['_manual']);

                // non-serial: take stock first, the real cost comes from the batches used
                // if (!$isQuote && !$sid) {
                //     $l['costunitprice'] = $this->_deduct_stock($l['tbl_product_idtbl_product'], $l['qty'], $branch_id, $userid, $now);
                // }

                // non-serial: take stock first, the real cost comes from the batches used
                if (!$isQuote && !$sid && empty($l['is_preorder'])) {
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
                                'stock_status' => 2, 'sold_invoice_id' => $invoice_id, 'sold_date' => $invoiceDate,
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
            foreach ($bypassed as $b) { unset($_SESSION['fifo_override'][$b['token']]); }
            if ($discountToken !== '') { unset($_SESSION['discount_override'][$discountToken]); }
            foreach (array_keys($itemDiscTokens) as $t) { unset($_SESSION['itemdisc_override'][$t]); }

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
            ->where('cn.approve_status', 1)                       
            ->get()->result();
    }
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
        // $headerFile = FCPATH . 'images/header.png';
        // $headerImg  = is_file($headerFile) ? 'data:image/png;base64,' . base64_encode(file_get_contents($headerFile)) : '';

        // $footerFile = FCPATH . 'images/footer.png';
        // $footerImg  = is_file($footerFile) ? 'data:image/png;base64,' . base64_encode(file_get_contents($footerFile)) : '';

        $data = [
            'inv'       => $inv,
            'items'     => $items,
            'customer'  => $customer,
            // 'headerImg' => $headerImg,
            // 'footerImg' => $footerImg,
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
        'offX'          => ($this->input->get('x') !== null) ? max(-40, min(40, (float)$this->input->get('x'))) : $this->printOffX,
        'offY'          => ($this->input->get('y') !== null) ? max(-40, min(40, (float)$this->input->get('y'))) : $this->printOffY,
        'guide'         => (bool)$this->input->get('guide'),
        ];

        $html = $this->load->view('invoice_pdf', $data, TRUE);

        $this->load->library('pdf');
        $this->pdf->getOptions()->set('defaultFont', 'DejaVu Sans');
        $this->pdf->getOptions()->set('isRemoteEnabled', false);

        $this->pdf->loadHtml($html);
        // $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->setPaper(array(0, 0, 684, 792), 'portrait');   // 9.5 x 11 inch continuous paper
        $this->pdf->render();

        if (ob_get_length()) ob_end_clean();
        $this->pdf->stream('SPENCERSZ-INVOICE-' . $inv->invoice_no . '.pdf', ['Attachment' => 0]);
    }
    public function Holdinvoice(){
        try {
            $inv = json_decode($this->input->post('invoice'), true);
            if (!is_array($inv) || empty($inv['items'])) throw new RuntimeException('Cart is empty');

            $userid=$_SESSION['userid'];
            $company_id=$_SESSION['company_id'];
            $branch_id=$_SESSION['branch_id'];
            $now        = date('Y-m-d H:i:s');

            $items = [];
            foreach ($inv['items'] as $it) {
                $items[] = [
                    'id'       => (int)$it['id'],
                    'serialId' => !empty($it['serialId']) ? (int)$it['serialId'] : null,
                    'qty'      => max(1, (int)$it['qty']),
                ];
            }

            $row = [
                'label'         => mb_substr(trim((string)$inv['label']), 0, 100),
                'customer_nic'  => $inv['customerNic'],
                'customer_name' => $inv['customerName'],
                'tbl_customer_idtbl_customer' => !empty($inv['customerId']) ? (int)$inv['customerId'] : null,
                'invoice_type'  => $inv['invoiceType'],
                'fulfillment'   => $inv['fulfillment'],
                'discount'      => max(0, round((float)$inv['discount'], 2)),
                'items_json'    => json_encode($items),
                'item_count'    => array_sum(array_column($items, 'qty')),
                'updatedatetime' => $now, 'updateuser' => $userid,
            ];

            $hold_id = !empty($inv['holdId']) ? (int)$inv['holdId'] : 0;
            $old = $hold_id ? $this->db->where('idtbl_invoice_hold', $hold_id)->where('status', 1)
                        ->where('tbl_company_branch_idtbl_company_branch', $branch_id)
                        ->get('tbl_invoice_hold')->row() : null;

            if ($old) {                                   // resumed bill held again: update the same draft
                $this->db->where('idtbl_invoice_hold', $hold_id)->update('tbl_invoice_hold', $row);
                $hold_no = $old->hold_no;
            } else {
                $prefix  = 'HLD' . date('ymd');
                $seq     = $this->db->like('hold_no', $prefix, 'after')->count_all_results('tbl_invoice_hold') + 1;
                $hold_no = $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
                $row += ['hold_no' => $hold_no, 'status' => 1, 'insertdatetime' => $now,
                        'tbl_user_idtbl_user' => $userid, 'tbl_company_idtbl_company' => $company_id,
                        'tbl_company_branch_idtbl_company_branch' => $branch_id];
                $this->db->insert('tbl_invoice_hold', $row);
                if (!$this->db->insert_id()) throw new Exception('Hold insert failed');
            }

            return $this->_jsonResponse(1, 'fas fa-pause-circle', 'Bill held: ' . $hold_no, 'success', ['hold_no' => $hold_no]);

        } catch (RuntimeException $e) {
            return $this->_jsonResponse(0, 'fas fa-exclamation-triangle', $e->getMessage(), 'danger');
        } catch (Throwable $e) {
            log_message('error', 'Holdinvoice failed: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            return $this->_jsonResponse(0, 'fas fa-exclamation-triangle', 'Could not hold the bill.', 'danger');
        }
    }
    public function Getholdlist(){
        $branch_id=$_SESSION['branch_id'];
        $rows = $this->db->select('idtbl_invoice_hold AS id, hold_no, label, customer_name, item_count, discount, insertdatetime')
            ->where('status', 1)->where('tbl_company_branch_idtbl_company_branch', $branch_id)
            ->order_by('idtbl_invoice_hold', 'DESC')->limit(50)->get('tbl_invoice_hold')->result();
        echo json_encode(['status' => 1, 'rows' => $rows]);
    }
    public function Resumehold(){
        $branch_id=$_SESSION['branch_id'];
        $h = $this->db->where('idtbl_invoice_hold', (int)$this->input->post('id'))->where('status', 1)
                    ->where('tbl_company_branch_idtbl_company_branch', $branch_id)
                    ->get('tbl_invoice_hold')->row();
        if (!$h) return $this->_jsonResponse(0, 'fas fa-exclamation-triangle', 'Held bill not found', 'danger');

        echo json_encode(['status' => 1, 'bill' => [
            'id'           => (int)$h->idtbl_invoice_hold,
            'customerId'   => $h->tbl_customer_idtbl_customer,
            'customerNic'  => $h->customer_nic,
            'customerName' => $h->customer_name,
            'invoiceType'  => $h->invoice_type,
            'fulfillment'  => $h->fulfillment,
            'discount'     => (float)$h->discount,
            'items'        => json_decode($h->items_json, true),
        ]]);
    }
    public function Deletehold(){
        $branch_id=$_SESSION['branch_id'];
        $this->db->where('idtbl_invoice_hold', (int)$this->input->post('id'))->where('status', 1)
                ->where('tbl_company_branch_idtbl_company_branch', $branch_id)
                ->update('tbl_invoice_hold', ['status' => 3, 'updatedatetime' => date('Y-m-d H:i:s'),
                                            'updateuser' => $this->session->userdata('userid')]);   // CHANGE
        return $this->_jsonResponse(1, 'fas fa-trash', 'Held bill deleted', 'success');
    }
    // called by the POS page when a serial is chosen
    public function Fifocheckserial()
    {
        $branch_id = $_SESSION['branch_id'];
        $pid = (int)$this->input->post('product_id');
        $ids = array_map('intval', (array)$this->input->post('serial_ids'));

        $r = $this->fifocheck($pid, $branch_id, $ids);
        echo json_encode($r === true ? ['status' => 1] : ['status' => 0, 'message' => $r]);
    }
    /* Approver's username/password + permission check. Token is bound to ONE product. */
    public function Fifoignoreverify(){
        $userID   = (int)$_SESSION['userid'];
        $pid      = (int)$this->input->post('product_id');
        $username = trim($this->input->post('username'));
        $password = (string)$this->input->post('password');
        $reason   = trim($this->input->post('reason'));

        if(!$pid){ return $this->_fifoJson(false, 'Product not selected.'); }
        if($username==='' || $password===''){ return $this->_fifoJson(false, 'Enter the username and password.'); }
        if($reason===''){ return $this->_fifoJson(false, 'Enter the reason for ignoring FIFO.'); }

        $u=$this->db->query("SELECT idtbl_user, name, password FROM tbl_user WHERE username = ? AND status = 1", array($username))->row();
        if(!$u || !password_verify($password, $u->password)){ return $this->_fifoJson(false, 'Invalid username or password.'); }

        $perm=$this->db->query("SELECT 1
                                FROM tbl_user_has_tbl_roles ur
                                JOIN tbl_roles r ON r.idtbl_roles = ur.tbl_roles_idtbl_roles AND r.status = 1
                                JOIN tbl_roles_has_tbl_permissions rp ON rp.tbl_roles_idtbl_roles = r.idtbl_roles
                                JOIN tbl_permissions p ON p.idtbl_permissions = rp.tbl_permissions_idtbl_permissions
                                WHERE ur.tbl_user_idtbl_user = ? AND p.permission = ? AND p.guard_name = ?
                                LIMIT 1", array((int)$u->idtbl_user, self::FIFO_PERMISSION, self::FIFO_GUARD))->row();
        if(!$perm){ return $this->_fifoJson(false, 'This user does not have permission to approve ignoring FIFO.'); }

        $token=bin2hex(random_bytes(16));
        if(!isset($_SESSION['fifo_override']) || !is_array($_SESSION['fifo_override'])){ $_SESSION['fifo_override']=array(); }
        foreach($_SESSION['fifo_override'] as $k => $o){ if($o['expires']<time()){ unset($_SESSION['fifo_override'][$k]); } }
        $_SESSION['fifo_override'][$token]=array(
            'product_id'   => $pid,
            'approved_by'  => (int)$u->idtbl_user,
            'requested_by' => $userID,
            'reason'       => $reason,
            'expires'      => time()+(self::FIFO_TOKEN_MINUTES*60),
        );
        echo json_encode(array('ok'=>true, 'token'=>$token, 'approver'=>$u->name));
    }
    /* Approver's username/password + permission check. The token is bound to the discount amount. */
    public function Discountverify(){
        $userID   = (int)$_SESSION['userid'];
        $amount   = round((float)$this->input->post('amount'), 2);
        $username = trim($this->input->post('username'));
        $password = (string)$this->input->post('password');
        $reason   = trim($this->input->post('reason'));

        if($amount<=0){ return $this->_fifoJson(false, 'Invalid discount amount.'); }
        if($username==='' || $password===''){ return $this->_fifoJson(false, 'Enter the username and password.'); }
        if($reason===''){ return $this->_fifoJson(false, 'Enter the reason for the discount.'); }

        $u=$this->db->query("SELECT idtbl_user, name, password FROM tbl_user WHERE username = ? AND status = 1", array($username))->row();
        if(!$u || !password_verify($password, $u->password)){ return $this->_fifoJson(false, 'Invalid username or password.'); }

        // same permission lookup as Fifoignoreverify() (use the table names you use there)
        $perm=$this->db->query("SELECT 1
                                FROM tbl_user_has_tbl_roles ur
                                JOIN tbl_roles r ON r.idtbl_roles = ur.tbl_roles_idtbl_roles AND r.status = 1
                                JOIN tbl_roles_has_tbl_permissions rp ON rp.tbl_roles_idtbl_roles = r.idtbl_roles
                                JOIN tbl_permissions p ON p.idtbl_permissions = rp.tbl_permissions_idtbl_permissions
                                WHERE ur.tbl_user_idtbl_user = ? AND p.permission = ? AND p.guard_name = ?
                                LIMIT 1", array((int)$u->idtbl_user, self::DISCOUNT_PERMISSION, self::FIFO_GUARD))->row();
        if(!$perm){ return $this->_fifoJson(false, 'This user does not have permission to approve discounts.'); }

        $token=bin2hex(random_bytes(16));
        if(!isset($_SESSION['discount_override']) || !is_array($_SESSION['discount_override'])){ $_SESSION['discount_override']=array(); }
        foreach($_SESSION['discount_override'] as $k => $o){ if($o['expires']<time()){ unset($_SESSION['discount_override'][$k]); } }
        $_SESSION['discount_override'][$token]=array(
            'amount'       => $amount,
            'approved_by'  => (int)$u->idtbl_user,
            'requested_by' => $userID,
            'reason'       => $reason,
            'expires'      => time()+(self::FIFO_TOKEN_MINUTES*60),
        );
        echo json_encode(array('ok'=>true, 'token'=>$token, 'approver'=>$u->name));
    }

    /* approval of an extra discount % for ONE product (the token is bound to the product and the percentage) */
    public function Itemdiscountverify(){
        $userID   = (int)$_SESSION['userid'];
        $pid      = (int)$this->input->post('product_id');
        $pct      = round((float)$this->input->post('percent'), 2);
        $username = trim($this->input->post('username'));
        $password = (string)$this->input->post('password');
        $reason   = trim($this->input->post('reason'));

        if(!$pid || $pct<=0 || $pct>=100){ return $this->_fifoJson(false, 'Invalid discount percentage.'); }
        if($reason===''){ return $this->_fifoJson(false, 'Enter the reason for the discount.'); }

        list($u, $err)=$this->approver($username, $password, self::ITEMDISC_PERMISSION);
        if(!$u){ return $this->_fifoJson(false, $err); }

        $token=bin2hex(random_bytes(16));
        if(!isset($_SESSION['itemdisc_override']) || !is_array($_SESSION['itemdisc_override'])){ $_SESSION['itemdisc_override']=array(); }
        foreach($_SESSION['itemdisc_override'] as $k => $o){ if($o['expires']<time()){ unset($_SESSION['itemdisc_override'][$k]); } }
        $_SESSION['itemdisc_override'][$token]=array(
            'product_id'   => $pid,
            'percent'      => $pct,
            'approved_by'  => (int)$u->idtbl_user,
            'requested_by' => $userID,
            'reason'       => $reason,
            'expires'      => time()+(self::FIFO_TOKEN_MINUTES*60),
        );
        echo json_encode(array('ok'=>true, 'token'=>$token, 'approver'=>$u->name));
    }

    /* ---------- pending deliveries of this branch ---------- */
    public function Getpending(){
        $branch=(int)$_SESSION['branch_id'];
        $rows=$this->db->query("SELECT d.idtbl_invoice_detail AS id, i.idtbl_invoice AS invoice_id, i.invoice_no, i.invoice_date, i.customer_name,
                                    c.telephone_no AS phone, d.tbl_product_idtbl_product AS product_id, d.product_code, d.product_name,
                                    p.has_serial, d.qty, d.delivered_qty, (d.qty - d.delivered_qty) AS remaining, d.delivery_date,
                                    DATEDIFF(d.delivery_date, CURDATE()) AS days_left,
                                    IF(p.has_serial = 1,
                                        (SELECT COUNT(*) FROM tbl_product_serial ps WHERE ps.tbl_product_idtbl_product = d.tbl_product_idtbl_product
                                        AND ps.status = 1 AND ps.stock_status = 1 AND ps.tbl_company_branch_idtbl_company_branch = i.tbl_company_branch_idtbl_company_branch),
                                        (SELECT COALESCE(SUM(s.qty),0) FROM tbl_stock s WHERE s.tbl_product_idtbl_product = d.tbl_product_idtbl_product
                                        AND s.tbl_company_branch_idtbl_company_branch = i.tbl_company_branch_idtbl_company_branch AND s.stock_status = 1 AND s.status = 1)
                                    ) AS in_stock
                                FROM tbl_invoice_detail d
                                JOIN tbl_invoice i ON i.idtbl_invoice = d.tbl_invoice_idtbl_invoice
                                JOIN tbl_product p ON p.idtbl_product = d.tbl_product_idtbl_product
                                LEFT JOIN tbl_customer c ON c.idtbl_customer = i.tbl_customer_idtbl_customer
                                WHERE d.is_preorder = 1 AND d.delivery_status = 2 AND d.status = 1 AND i.status = 1
                                AND i.tbl_company_branch_idtbl_company_branch = ?
                                ORDER BY d.delivery_date, i.idtbl_invoice, d.idtbl_invoice_detail", array($branch))->result();
        echo json_encode($rows);
    }

    /* free serials for a pending serial line, oldest GRN first (FIFO) */
    public function Getdeliveryserials(){
        $branch=(int)$_SESSION['branch_id'];
        $detailID=(int)$this->input->post('detail_id');
        $rows=$this->db->query("SELECT ps.idtbl_product_serial AS id, ps.serialno, g.grn_no, g.batchno, g.grndate
                                FROM tbl_invoice_detail d
                                JOIN tbl_invoice i ON i.idtbl_invoice = d.tbl_invoice_idtbl_invoice AND i.tbl_company_branch_idtbl_company_branch = ?
                                JOIN tbl_product_serial ps ON ps.tbl_product_idtbl_product = d.tbl_product_idtbl_product
                                    AND ps.status = 1 AND ps.stock_status = 1 AND ps.tbl_company_branch_idtbl_company_branch = ?
                                JOIN tbl_grn g ON g.idtbl_grn = ps.tbl_grn_idtbl_grn
                                WHERE d.idtbl_invoice_detail = ? AND d.is_preorder = 1 AND d.delivery_status = 2
                                AND NOT EXISTS (SELECT 1 FROM tbl_stock_transfer_serial ts
                                                WHERE ts.tbl_product_serial_idtbl_product_serial = ps.idtbl_product_serial AND ts.status = 1)
                                ".$this->transferable()."
                                ORDER BY g.grndate, g.idtbl_grn, ps.idtbl_product_serial", array($branch, $branch, $detailID))->result();
        echo json_encode($rows);
    }

    /* ---------- deliver a pending line (full or part) ---------- */
    public function Deliverpending(){
        $userID=(int)$_SESSION['userid'];
        $company=(int)$_SESSION['company_id'];
        $branch=(int)$_SESSION['branch_id'];
        $now=date('Y-m-d H:i:s');
        $today=date('Y-m-d');

        $inTrans=false;
        $old_debug=$this->db->db_debug;
        $this->db->db_debug=FALSE;

        try{
            $detailID=(int)$this->input->post('detail_id');
            $date=trim((string)$this->input->post('delivered_date'));
            $dt=DateTime::createFromFormat('Y-m-d', $date);
            if(!$dt || $dt->format('Y-m-d')!==$date || $date>$today){ throw new RuntimeException('Enter a valid delivered date (not in the future).'); }

            $this->db->trans_begin();
            $inTrans=true;

            $d=$this->db->query("SELECT d.*, i.invoice_no, i.invoice_date
                                FROM tbl_invoice_detail d JOIN tbl_invoice i ON i.idtbl_invoice = d.tbl_invoice_idtbl_invoice
                                WHERE d.idtbl_invoice_detail = ? AND d.is_preorder = 1 AND d.delivery_status = 2 AND d.status = 1
                                AND i.status = 1 AND i.tbl_company_branch_idtbl_company_branch = ? FOR UPDATE", array($detailID, $branch))->row();
            if(!$d){ throw new RuntimeException('Pending line not found (it may already be delivered).'); }
            if($date<$d->invoice_date){ throw new RuntimeException('Delivered date cannot be before the invoice date ('.$d->invoice_date.').'); }

            $p=$this->db->where('idtbl_product', (int)$d->tbl_product_idtbl_product)->get('tbl_product')->row();
            $remaining=(int)$d->qty-(int)$d->delivered_qty;

            $sid=null; $qty=1; $cost=0.0; $upd=array();

            if((int)$p->has_serial===1){
                // ----- serial item: one serial, FIFO -----
                $ids=array_values(array_unique(array_map('intval', (array)$this->input->post('serial_ids'))));
                if(count($ids)!==1){ throw new RuntimeException('Select the serial number to deliver.'); }
                $sid=$ids[0];

                $s=$this->db->where('idtbl_product_serial', $sid)->where('tbl_product_idtbl_product', (int)$p->idtbl_product)
                            ->where('stock_status', 1)->where('status', 1)->where('tbl_company_branch_idtbl_company_branch', $branch)
                            ->get('tbl_product_serial')->row();
                if(!$s){ throw new RuntimeException('This serial is not available in stock.'); }

                $fifo=$this->fifocheck((int)$p->idtbl_product, $branch, array($sid));
                if($fifo!==true){ throw new RuntimeException($fifo); }

                $this->db->where('idtbl_product_serial', $sid)->where('stock_status', 1)->where('status', 1)
                        ->update('tbl_product_serial', array('stock_status'=>2, 'sold_invoice_id'=>$d->tbl_invoice_idtbl_invoice, 'sold_date'=>$date,
                                                            'updateuser'=>$userID, 'updatedatetime'=>$now));
                if($this->db->affected_rows()!==1){ throw new RuntimeException('The serial was just taken by another sale.'); }

                $this->db->insert('tbl_serial_movement', array(
                    'movement_type'=>2, 'from_status'=>1, 'to_status'=>2,
                    'doc_type'=>'INVOICE', 'doc_id'=>$d->tbl_invoice_idtbl_invoice, 'doc_detail_id'=>$detailID,
                    'branch_id'=>$branch, 'remark'=>'Delivered on '.$d->invoice_no,
                    'insertdatetime'=>$now, 'tbl_user_idtbl_user'=>$userID,
                    'tbl_product_serial_idtbl_product_serial'=>$sid));

                // keep tbl_stock in step (same two statements as the normal sale)
                $this->db->set('qty', 'qty - 1', FALSE)->set('updatedatetime', $now)->set('updateuser', $userID)
                        ->where('tbl_grndetail_idtbl_grndetail', $s->tbl_grndetail_idtbl_grndetail)
                        ->where('tbl_product_idtbl_product', (int)$p->idtbl_product)
                        ->where('qty >=', 1)->update('tbl_stock');
                $this->db->where('tbl_grndetail_idtbl_grndetail', $s->tbl_grndetail_idtbl_grndetail)
                        ->where('qty <=', 0)->where('stock_status', 1)
                        ->update('tbl_stock', array('stock_status'=>2));

                $cost=(float)$s->costunitprice;
                $upd=array('serialno'=>$s->serialno, 'tbl_product_serial_idtbl_product_serial'=>$sid, 'costunitprice'=>$cost,
                        'delivered_qty'=>(int)$d->delivered_qty+1, 'delivery_status'=>1);
            }
            else{
                // ----- item without serial: stock is taken now (FIFO batches), part delivery is allowed -----
                $qty=(int)$this->input->post('qty');
                if($qty<1 || $qty>$remaining){ throw new RuntimeException('Quantity must be between 1 and '.$remaining.'.'); }

                $cost=(float)$this->_deduct_stock((int)$p->idtbl_product, $qty, $branch, $userID, $now);   // throws when the stock is not enough

                $done=(int)$d->delivered_qty;
                $upd=array('costunitprice'=>round((((float)$d->costunitprice*$done)+($cost*$qty))/($done+$qty), 2),   // weighted average of the deliveries
                        'delivered_qty'=>$done+$qty,
                        'delivery_status'=>($done+$qty>=(int)$d->qty) ? 1 : 2);
            }

            $this->db->where('idtbl_invoice_detail', $detailID)->update('tbl_invoice_detail', $upd);

            $this->db->insert('tbl_invoice_delivery', array(
                'tbl_invoice_idtbl_invoice'=>$d->tbl_invoice_idtbl_invoice,
                'tbl_invoice_detail_idtbl_invoice_detail'=>$detailID,
                'tbl_product_idtbl_product'=>(int)$p->idtbl_product,
                'tbl_product_serial_idtbl_product_serial'=>$sid,
                'qty'=>$qty, 'costunitprice'=>$cost, 'delivered_date'=>$date, 'status'=>1,
                'insertdatetime'=>$now, 'tbl_user_idtbl_user'=>$userID, 'tbl_company_idtbl_company'=>$company, 'tbl_company_branch_idtbl_company_branch'=>$branch));

            if($this->db->trans_status()===FALSE){
                $err=$this->db->error();
                throw new Exception('DB error: '.$err['code'].' '.$err['message']);
            }
            $this->db->trans_commit();
            $inTrans=false;

            echo json_encode(array('ok'=>true, 'msg'=>'Delivered', 'invoice_id'=>(int)$d->tbl_invoice_idtbl_invoice, 'date'=>$date));
        }
        catch(RuntimeException $e){
            if($inTrans){ $this->db->trans_rollback(); }
            echo json_encode(array('ok'=>false, 'msg'=>$e->getMessage()));
        }
        catch(Throwable $e){
            if($inTrans){ $this->db->trans_rollback(); }
            log_message('error', 'Deliverpending failed: '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine());
            echo json_encode(array('ok'=>false, 'msg'=>'Could not save the delivery.', 'Deliverpending failed: '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine()));
        }
        finally{
            $this->db->db_debug=$old_debug;
        }
    }

    /* data of one delivery note: everything delivered for an invoice on a date */
    public function Deliverynotedata($invoice_id, $date){
        $branch=(int)$_SESSION['branch_id'];
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$date)){ return null; }

        $inv=$this->db->query("SELECT i.*, c.address_line1, c.address_line2, c.city, c.telephone_no
                            FROM tbl_invoice i LEFT JOIN tbl_customer c ON c.idtbl_customer = i.tbl_customer_idtbl_customer
                            WHERE i.idtbl_invoice = ? AND i.tbl_company_branch_idtbl_company_branch = ?", array((int)$invoice_id, $branch))->row();
        if(!$inv){ return null; }

        $rows=$this->db->query("SELECT v.idtbl_invoice_delivery, v.qty, d.product_code, d.product_name, d.warranty_months, ps.serialno
                                FROM tbl_invoice_delivery v
                                JOIN tbl_invoice_detail d ON d.idtbl_invoice_detail = v.tbl_invoice_detail_idtbl_invoice_detail
                                LEFT JOIN tbl_product_serial ps ON ps.idtbl_product_serial = v.tbl_product_serial_idtbl_product_serial
                                WHERE v.tbl_invoice_idtbl_invoice = ? AND v.delivered_date = ? AND v.status = 1
                                ORDER BY v.idtbl_invoice_delivery", array((int)$invoice_id, $date))->result();
        if(!$rows){ return null; }

        return array('inv'=>$inv, 'rows'=>$rows, 'date'=>$date);
    }

    /* available qty of the products on one page of the catalog modal: {product_id: qty} */
    public function Getavailableqty(){
        $ids = array_values(array_unique(array_filter(array_map('intval', (array)$this->input->post('ids')))));
        echo json_encode((object)$this->availableqty($ids, (int)$_SESSION['branch_id']));
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
    /* FIFO: a serial from a newer GRN cannot be sold while serials of an older GRN are still free */
    // private function fifocheck($productID, $from, $ids){
    //     $rows=$this->db->query("SELECT ps.idtbl_product_serial AS id, ps.tbl_grn_idtbl_grn AS grn, g.grn_no, g.grndate
    //                             FROM tbl_product_serial ps
    //                             JOIN tbl_grn g ON g.idtbl_grn = ps.tbl_grn_idtbl_grn
    //                             WHERE ps.tbl_product_idtbl_product = ? AND ps.stock_status = 1 AND ps.status = 1
    //                             AND ps.tbl_company_branch_idtbl_company_branch = ?
    //                             AND NOT EXISTS (SELECT 1 FROM tbl_stock_transfer_serial ts
    //                                             WHERE ts.tbl_product_serial_idtbl_product_serial = ps.idtbl_product_serial AND ts.status = 1)
    //                             ".$this->transferable()."
    //                             ORDER BY g.grndate, g.idtbl_grn, ps.idtbl_product_serial", array($productID, $from))->result();

    //     $rank=-1; $last=null; $ranks=array(); $names=array(); $maxSel=-1;
    //     foreach($rows as $r){
    //         if($r->grn!==$last){ $rank++; $last=$r->grn; $names[$rank]=$r->grn_no.' ('.$r->grndate.')'; }
    //         $ranks[(int)$r->id]=$rank;
    //         if(in_array((int)$r->id, $ids)){ $maxSel=max($maxSel, $rank); }
    //     }
    //     foreach($rows as $r){
    //         if($ranks[(int)$r->id] < $maxSel && !in_array((int)$r->id, $ids)){
    //             return 'FIFO: batch '.$names[$maxSel].' cannot be sold because older stock from '.$names[$ranks[(int)$r->id]].' is still available. Sell the older serial numbers first.';
    //         }
    //     }
    //     return true;
    // }
    private function fifocheck($productID, $from, $ids, &$note = null){
        $rows=$this->db->query("SELECT ps.idtbl_product_serial AS id, ps.tbl_grn_idtbl_grn AS grn, g.grn_no, g.grndate
                                FROM tbl_product_serial ps
                                JOIN tbl_grn g ON g.idtbl_grn = ps.tbl_grn_idtbl_grn
                                WHERE ps.tbl_product_idtbl_product = ? AND ps.stock_status = 1 AND ps.status = 1
                                AND ps.tbl_company_branch_idtbl_company_branch = ?
                                AND NOT EXISTS (SELECT 1 FROM tbl_stock_transfer_serial ts
                                                WHERE ts.tbl_product_serial_idtbl_product_serial = ps.idtbl_product_serial AND ts.status = 1)
                                ".$this->transferable()."
                                ORDER BY g.grndate, g.idtbl_grn, ps.idtbl_product_serial", array($productID, $from))->result();

        $rank=-1; $last=null; $ranks=array(); $names=array(); $maxSel=-1;
        foreach($rows as $r){
            if($r->grn!==$last){ $rank++; $last=$r->grn; $names[$rank]=$r->grn_no.' ('.$r->grndate.')'; }
            $ranks[(int)$r->id]=$rank;
            if(in_array((int)$r->id, $ids)){ $maxSel=max($maxSel, $rank); }
        }

        $skipped=array();
        foreach($rows as $r){
            $rk=$ranks[(int)$r->id];
            if($rk < $maxSel && !in_array((int)$r->id, $ids)){ $skipped[$rk]=isset($skipped[$rk]) ? $skipped[$rk]+1 : 1; }
        }
        if(!count($skipped)){ return true; }

        ksort($skipped);
        $parts=array();
        foreach($skipped as $rk => $n){ $parts[]=$names[$rk].' - '.$n.' serial number(s)'; }
        $note='FIFO ignored with approval: batch '.$names[$maxSel].' was sold while older stock was still available ('.implode('; ', $parts).').';

        $first=key($skipped);
        return 'FIFO: batch '.$names[$maxSel].' cannot be sold because older stock from '.$names[$first].' is still available. Sell the older serial numbers first.';
    }
    /* discount % that is valid TODAY for this product row (0 = no discount) */
    private function activediscount($p){
        $d=(float)$p->discount_percent;
        if($d<=0 || $d>=100){ return 0.0; }

        $today=date('Y-m-d');
        if(!empty($p->discount_start) && $p->discount_start>$today){ return 0.0; }
        if(!empty($p->discount_end) && $p->discount_end<$today){ return 0.0; }

        return round($d, 2);
    }
    /* only these serial statuses can be transferred:
       1 = in stock, 5 = returned by customer, 6 = display item
       everything else (2 sold, 3 returned to supplier, 4 damaged, 7 defective) is excluded.
       Uses tbl_product_serial only, no tbl_serial_movement lookup. */
    private function transferable(){
        return " AND ps.stock_status IN (1, 5, 6) ";
    }
    private function fifooverride($token, $pid){
        $o = isset($_SESSION['fifo_override'][$token]) ? $_SESSION['fifo_override'][$token] : null;
        if(!$o) return null;
        if($o['expires'] < time() || (int)$o['requested_by'] !== (int)$_SESSION['userid'] || (int)$o['product_id'] !== (int)$pid) return null;
        return $o;
    }

    private function _fifoJson($ok, $msg){
        echo json_encode(array('ok'=>$ok, 'msg'=>$msg));
    }

    // TEMPORARY: a serial typed by the cashier at the invoice. It is registered the same way Goodreceiveserialinsert() does it
    // (tbl_product_serial + tbl_serial_movement), under the oldest approved GRN line of this product that still has room.
    private function _manual_serial($p, $serialno, $price, $isQuote, $branch_id, $company_id, $userid, $now)
    {
        if ($serialno === '' || mb_strlen($serialno) > 100) throw new RuntimeException('Invalid serial number');

        $ex = $this->db->where('serialno', $serialno)->where('status', 1)->get('tbl_product_serial')->row();
        if ($ex) {
            // already in the system: only free stock of this product in this branch can be sold
            if ((int)$ex->tbl_product_idtbl_product === (int)$p->idtbl_product && (int)$ex->stock_status === 1
                && (int)$ex->tbl_company_branch_idtbl_company_branch === (int)$branch_id) {
                return $ex;
            }
            throw new RuntimeException('Serial number ' . $serialno . ' already exists and is not available for sale');
        }

        if ($isQuote) {                                   // a quotation saves nothing
            return (object)['idtbl_product_serial' => null, 'serialno' => $serialno, 'saleprice' => 0,
                            'costunitprice' => 0, 'tbl_grndetail_idtbl_grndetail' => null];
        }

        // GRN information (tbl_grn + tbl_grndetail): oldest approved line with room for one more serial number
        $d = $this->db->query(
            "SELECT d.idtbl_grndetail, d.tbl_grn_idtbl_grn AS grn_id, d.costunitprice, g.grn_no, g.batchno
            FROM tbl_grndetail d
            JOIN tbl_grn g ON g.idtbl_grn = d.tbl_grn_idtbl_grn
            WHERE d.tbl_product_idtbl_product = ? AND d.status = 1
                AND g.status = 1 AND g.approvestatus = 1
                AND g.tbl_company_idtbl_company = ? AND g.tbl_company_branch_idtbl_company_branch = ?
                AND FLOOR(d.qty) > (SELECT COUNT(*) FROM tbl_product_serial ps
                                    WHERE ps.tbl_grndetail_idtbl_grndetail = d.idtbl_grndetail AND ps.status = 1)
            ORDER BY g.grndate, g.idtbl_grn, d.idtbl_grndetail
            LIMIT 1 FOR UPDATE", [$p->idtbl_product, $company_id, $branch_id])->row();
        if (!$d) {
            throw new RuntimeException('No GRN stock is available for ' . $p->product_name
                . ' (every received quantity already has a serial number). Please receive the stock first.');
        }

        $this->db->insert('tbl_product_serial', [
            'serialno' => $serialno, 'batchno' => $d->batchno,
            'tbl_grn_idtbl_grn' => $d->grn_id, 'tbl_grndetail_idtbl_grndetail' => $d->idtbl_grndetail,
            'costunitprice' => $d->costunitprice, 'saleprice' => $price,
            'tbl_product_idtbl_product' => $p->idtbl_product,
            'stock_status' => 1, 'status' => 1,
            'insertdatetime' => $now, 'tbl_user_idtbl_user' => $userid,
            'tbl_company_idtbl_company' => $company_id, 'tbl_company_branch_idtbl_company_branch' => $branch_id,
        ]);
        $sid = $this->db->insert_id();
        if (!$sid) throw new Exception('Serial insert failed');

        $this->db->insert('tbl_serial_movement', [
            'movement_type' => 1, 'from_status' => NULL, 'to_status' => 1,
            'doc_type' => 'GRN', 'doc_id' => $d->grn_id, 'doc_detail_id' => $d->idtbl_grndetail,
            'branch_id' => $branch_id, 'remark' => 'Serial added from ' . $d->grn_no . ' (entered at invoice)',
            'insertdatetime' => $now, 'tbl_user_idtbl_user' => $userid,
            'tbl_product_serial_idtbl_product_serial' => $sid,
        ]);

        return $this->db->where('idtbl_product_serial', $sid)->get('tbl_product_serial')->row();
    }

    /* part warranties (tbl_product_warranty): [product_id => [ ['name'=>..., 'months'=>...], ... ]] */
    private function partwarranties($ids){
        $map = array();
        if (empty($ids)) { return $map; }

        $rows = $this->db->select('tbl_product_idtbl_product AS pid, warranty_name, warranty_months')
                        ->from('tbl_product_warranty')
                        ->where_in('tbl_product_idtbl_product', $ids)
                        ->where('status', 1)
                        ->order_by('idtbl_product_warranty')
                        ->get()->result();
        foreach ($rows as $r) {
            $map[(int)$r->pid][] = array('name' => $r->warranty_name, 'months' => (int)$r->warranty_months);
        }
        return $map;
    }

    /* part warranties of one product as a JSON string (NULL when it has none) */
    private function partsjson($pid){
        $map = $this->partwarranties(array((int)$pid));
        return !empty($map[(int)$pid]) ? json_encode($map[(int)$pid], JSON_UNESCAPED_UNICODE) : null;
    }

    /* the approval row when the token is valid for THIS cashier and covers this discount, otherwise null */
    private function discountoverride($token, $discount){
        $o = isset($_SESSION['discount_override'][$token]) ? $_SESSION['discount_override'][$token] : null;
        if(!$o) return null;
        if($o['expires'] < time() || (int)$o['requested_by'] !== (int)$_SESSION['userid'] || round($discount, 2) > round((float)$o['amount'], 2)) return null;
        return $o;
    }

    /* the approval row when the token is for THIS cashier + THIS product and covers this percentage, otherwise null */
    private function itemdiscountoverride($token, $pid, $pct){
        $o = isset($_SESSION['itemdisc_override'][$token]) ? $_SESSION['itemdisc_override'][$token] : null;
        if(!$o) return null;
        if($o['expires'] < time() || (int)$o['requested_by'] !== (int)$_SESSION['userid']
        || (int)$o['product_id'] !== (int)$pid || round($pct, 2) > round((float)$o['percent'], 2)) return null;
        return $o;
    }

    /* [user row, null] when username/password are right and the user has the permission, otherwise [null, message] */
    private function approver($username, $password, $permission){
        if($username==='' || $password===''){ return array(null, 'Enter the username and password.'); }

        $u=$this->db->query("SELECT idtbl_user, name, password FROM tbl_user WHERE username = ? AND status = 1", array($username))->row();
        if(!$u || !password_verify($password, $u->password)){ return array(null, 'Invalid username or password.'); }

        // same permission lookup as Fifoignoreverify() / Discountverify()
        $perm=$this->db->query("SELECT 1
                                FROM tbl_user_has_tbl_roles ur
                                JOIN tbl_roles r ON r.idtbl_roles = ur.tbl_roles_idtbl_roles AND r.status = 1
                                JOIN tbl_roles_has_tbl_permissions rp ON rp.tbl_roles_idtbl_roles = r.idtbl_roles
                                JOIN tbl_permissions p ON p.idtbl_permissions = rp.tbl_permissions_idtbl_permissions
                                WHERE ur.tbl_user_idtbl_user = ? AND p.permission = ? AND p.guard_name = ?
                                LIMIT 1", array((int)$u->idtbl_user, $permission, self::FIFO_GUARD))->row();
        if(!$perm){ return array(null, 'This user does not have permission to approve this.'); }

        return array($u, null);
    }

    /* quantity of a product that is sold to customers but not delivered yet (it is reserved for them) */
    private function pendingqty($pid, $branch){
        $r=$this->db->query("SELECT COALESCE(SUM(d.qty - d.delivered_qty),0) AS q
                            FROM tbl_invoice_detail d JOIN tbl_invoice i ON i.idtbl_invoice = d.tbl_invoice_idtbl_invoice
                            WHERE d.tbl_product_idtbl_product = ? AND d.is_preorder = 1 AND d.delivery_status = 2 AND d.status = 1
                            AND i.status = 1 AND i.tbl_company_branch_idtbl_company_branch = ?", array((int)$pid, (int)$branch))->row();
        return (int)$r->q;
    }

    /* [product_id => available qty] in this branch (tbl_stock), minus what is reserved for pending deliveries */
    private function availableqty($ids, $branch){
        $out = array();
        foreach ($ids as $id) { $out[(int)$id] = 0; }
        if (empty($ids)) { return $out; }

        // stock of these products in this branch
        $rows = $this->db->select('tbl_product_idtbl_product AS pid, COALESCE(SUM(qty),0) AS q', FALSE)
                        ->from('tbl_stock')
                        ->where_in('tbl_product_idtbl_product', $ids)
                        ->where('status', 1)->where('stock_status', 1)
                        ->where('tbl_company_branch_idtbl_company_branch', $branch)
                        ->group_by('tbl_product_idtbl_product')->get()->result();
        foreach ($rows as $r) { $out[(int)$r->pid] = (int)floor((float)$r->q); }

        // reserved for pending deliveries (pre-orders): remove this block to show the plain stock qty
        $rows = $this->db->select('d.tbl_product_idtbl_product AS pid, SUM(d.qty - d.delivered_qty) AS q', FALSE)
                        ->from('tbl_invoice_detail d')
                        ->join('tbl_invoice i', 'i.idtbl_invoice = d.tbl_invoice_idtbl_invoice')
                        ->where_in('d.tbl_product_idtbl_product', $ids)
                        ->where('d.is_preorder', 1)->where('d.delivery_status', 2)->where('d.status', 1)
                        ->where('i.status', 1)->where('i.tbl_company_branch_idtbl_company_branch', $branch)
                        ->group_by('d.tbl_product_idtbl_product')->get()->result();
        foreach ($rows as $r) { $out[(int)$r->pid] = max(0, $out[(int)$r->pid] - (int)$r->q); }

        return $out;
    }
}