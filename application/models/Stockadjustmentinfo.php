<?php
class Stockadjustmentinfo extends CI_Model{

    // statuses that can be brought back into stock by a PLUS adjustment
    // 3 returned to supplier, 4 damaged, 5 returned by customer, 7 defective, 8 adjusted out
    private $plusStatuses = array(2,3,4,5,7,8);


    private function flash($icon, $message, $type){
        $o=new stdClass();
        $o->icon=$icon; $o->title=''; $o->message=$message;
        $o->url=''; $o->target='_blank'; $o->type=$type;
        $this->session->set_flashdata('msg', json_encode($o));
    }

    private function serialPending($serialID){
        return $this->db->query("SELECT d.idtbl_stock_adjustment_detail
            FROM tbl_stock_adjustment_detail d
            JOIN tbl_stock_adjustment a ON a.idtbl_stock_adjustment=d.tbl_stock_adjustment_idtbl_stock_adjustment
            WHERE d.tbl_product_serial_idtbl_product_serial=? AND d.status=1 AND a.status=1 AND a.approve_status=0
            LIMIT 1", array($serialID))->num_rows()>0;
    }

    private function serialInTransfer($serialID){
        return $this->db->query("SELECT idtbl_stock_transfer_serial FROM tbl_stock_transfer_serial
            WHERE tbl_product_serial_idtbl_product_serial=? AND status=1 LIMIT 1", array($serialID))->num_rows()>0;
    }

    public function Getbranches(){
        $this->db->select('idtbl_company_branch, branch, type');
        $this->db->from('tbl_company_branch');
        $this->db->where('status', 1);
        return $this->db->get()->result();
    }

    public function Getproducts(){
        $q=trim($this->input->post('q'));
        $this->db->select('idtbl_product, product_code, product_name, model_no, has_serial');
        $this->db->from('tbl_product');
        $this->db->where('status', 1);
        $this->db->where('is_combo', 0);
        $this->db->group_start();
        $this->db->like('product_code', $q);
        $this->db->or_like('product_name', $q);
        $this->db->or_like('model_no', $q);
        $this->db->group_end();
        $this->db->limit(30);
        echo json_encode($this->db->get()->result());
    }


    // batches (stock rows) of the product in the branch
    public function Getbatches(){
        $productID=$this->input->post('product_id');
        $branchID=$this->input->post('branch_id');
        $this->db->select('idtbl_stock, batchno, qty, costunitprice, insertdatetime');
        $this->db->from('tbl_stock');
        $this->db->where('tbl_product_idtbl_product', $productID);
        $this->db->where('tbl_company_branch_idtbl_company_branch', $branchID);
        $this->db->where('status', 1);
        $this->db->order_by('insertdatetime', 'ASC');
        echo json_encode($this->db->get()->result());
    }

    // serials of the selected batch, filtered by adjustment type
    public function Getserials(){
        $stockID=(int)$this->input->post('stock_id');
        $type=(int)$this->input->post('adjustment_type');
        $branchID=(int)$this->input->post('branch_id');

        $stock=$this->db->get_where('tbl_stock', array('idtbl_stock'=>$stockID,'status'=>1))->row();
        if(!$stock){ echo json_encode(array()); return; }

        if($type==2){
            $statusSql="s.stock_status=1 AND s.tbl_company_branch_idtbl_company_branch=".$branchID;
        }else{
            $statusSql="s.stock_status IN (".implode(',', $this->plusStatuses).")";
        }

        $rows=$this->db->query("SELECT s.idtbl_product_serial, s.serialno, s.stock_status, s.costunitprice
            FROM tbl_product_serial s
            WHERE s.tbl_product_idtbl_product=? AND s.status=1
            AND (s.tbl_grn_idtbl_grn=? OR (?>0 AND s.tbl_grndetail_idtbl_grndetail=?))
            AND ".$statusSql."
            AND NOT EXISTS (SELECT 1 FROM tbl_stock_transfer_serial t
                            WHERE t.tbl_product_serial_idtbl_product_serial=s.idtbl_product_serial AND t.status=1)
            AND NOT EXISTS (SELECT 1 FROM tbl_stock_adjustment_detail d
                            JOIN tbl_stock_adjustment a ON a.idtbl_stock_adjustment=d.tbl_stock_adjustment_idtbl_stock_adjustment
                            WHERE d.tbl_product_serial_idtbl_product_serial=s.idtbl_product_serial AND d.status=1 AND a.status=1 AND a.approve_status=0)
            ORDER BY s.serialno ASC LIMIT 1000",
            array($stock->tbl_product_idtbl_product, $stock->tbl_grn_idtbl_grn,
                $stock->tbl_grndetail_idtbl_grndetail, $stock->tbl_grndetail_idtbl_grndetail))->result();
        echo json_encode($rows);
    }

    public function Stockadjustmentinsert(){
        $userID=$_SESSION['userid'];
        $now=date('Y-m-d H:i:s');

        $date=$this->input->post('adjustment_date');
        $type=(int)$this->input->post('adjustment_type');
        $reason=$this->input->post('reason');
        $remark=$this->input->post('remark');
        $branchID=(int)$this->input->post('branch_id');
        $items=json_decode($this->input->post('items_json'), true);

        if(!in_array($type, array(1,2)) || empty($items) || empty($reason) || $branchID<=0){
            $this->flash('fas fa-warning','Please fill all required fields and add at least one item','danger');
            redirect('Stockadjustment'); return;
        }
        $branch=$this->db->get_where('tbl_company_branch', array('idtbl_company_branch'=>$branchID))->row();
        if(!$branch){ $this->flash('fas fa-warning','Invalid branch','danger'); redirect('Stockadjustment'); return; }

        $this->db->trans_begin();
        $totalValue=0; $totalQty=0; $lines=array(); $seen=array(); $seenStock=array();

        foreach($items as $it){
            $product=$this->db->get_where('tbl_product', array('idtbl_product'=>(int)$it['product_id'],'status'=>1))->row();
            $stock=$this->db->get_where('tbl_stock', array(
                'idtbl_stock'=>(int)$it['stock_id'],
                'tbl_product_idtbl_product'=>(int)$it['product_id'],
                'tbl_company_branch_idtbl_company_branch'=>$branchID,
                'status'=>1))->row();

            $err=''; $qty=1; $serialID=0; $serialNo='';

            if(!$product || !$stock){ $err='Invalid product or batch selected'; }
            else if($product->has_serial==1){
                // ---------- SERIAL ITEM : always qty 1
                $ps=$this->db->get_where('tbl_product_serial', array('idtbl_product_serial'=>(int)$it['serial_id'],'status'=>1))->row();
                if(!$ps){ $err='Invalid serial selected'; }
                else{
                    $serialID=$ps->idtbl_product_serial; $serialNo=$ps->serialno;
                    $inBatch=($ps->tbl_product_idtbl_product==$stock->tbl_product_idtbl_product) &&
                            ($ps->tbl_grn_idtbl_grn==$stock->tbl_grn_idtbl_grn ||
                            ($stock->tbl_grndetail_idtbl_grndetail>0 && $ps->tbl_grndetail_idtbl_grndetail==$stock->tbl_grndetail_idtbl_grndetail));
                    if(!$inBatch){ $err='Serial '.$serialNo.' does not belong to the selected batch'; }
                    else if(isset($seen[$serialID])){ $err='Duplicate serial: '.$serialNo; }
                    else if($type==2 && ($ps->stock_status!=1 || $ps->tbl_company_branch_idtbl_company_branch!=$branchID)){ $err='Serial '.$serialNo.' is not in stock at this branch'; }
                    else if($type==2 && $stock->qty<1){ $err='Batch '.$stock->batchno.' has no stock qty'; }
                    else if($type==1 && !in_array((int)$ps->stock_status, $this->plusStatuses)){ $err='Serial '.$serialNo.' is already in stock'; }
                    else if($this->serialInTransfer($serialID) || $this->serialPending($serialID)){ $err='Serial '.$serialNo.' is in an open transfer or pending adjustment'; }
                    $cost=$ps->costunitprice;
                    $seen[$serialID]=1;
                }
            }else{
                // ---------- NON SERIAL ITEM : qty based
                $qty=(double)$it['qty'];
                if($qty<=0){ $err='Quantity must be greater than 0 for '.$product->product_name; }
                else if(isset($seenStock[$stock->idtbl_stock])){ $err='Batch '.$stock->batchno.' added twice'; }
                else if($type==2 && $qty>$stock->qty){ $err='Only '.$stock->qty.' available in batch '.$stock->batchno; }
                $seenStock[$stock->idtbl_stock]=1;
                $cost=$stock->costunitprice;
            }

            if($err!==''){
                $this->db->trans_rollback();
                $this->flash('fas fa-warning',$err,'danger');
                redirect('Stockadjustment'); return;
            }

            $totalValue+=$cost*$qty; $totalQty+=$qty;
            $lines[]=array(
                'qty'=>$qty,
                'costunitprice'=>$cost,
                'line_value'=>$cost*$qty,
                'serialno'=>$serialNo,
                'tbl_product_serial_idtbl_product_serial'=>$serialID,   // 0 = non serial
                'status'=>1,
                'insertdatetime'=>$now,
                'tbl_user_idtbl_user'=>$userID,
                'tbl_product_idtbl_product'=>$stock->tbl_product_idtbl_product,
                'tbl_stock_idtbl_stock'=>$stock->idtbl_stock
            );
        }

        $row=$this->db->query("SELECT MAX(idtbl_stock_adjustment) AS m FROM tbl_stock_adjustment")->row();
        $adjNo='SA'.date('y').str_pad(((int)$row->m)+1, 4, '0', STR_PAD_LEFT);

        $this->db->insert('tbl_stock_adjustment', array(
            'adjustment_no'=>$adjNo,'adjustment_date'=>$date,'adjustment_type'=>$type,
            'reason'=>$reason,'remark'=>$remark,'total_qty'=>$totalQty,'total_value'=>$totalValue,
            'approve_status'=>0,'status'=>1,'insertdatetime'=>$now,'tbl_user_idtbl_user'=>$userID,
            'tbl_company_idtbl_company'=>$branch->tbl_company_idtbl_company,
            'tbl_company_branch_idtbl_company_branch'=>$branchID
        ));
        $headerID=$this->db->insert_id();
        foreach($lines as $l){
            $l['tbl_stock_adjustment_idtbl_stock_adjustment']=$headerID;
            $this->db->insert('tbl_stock_adjustment_detail', $l);
        }

        if($this->db->trans_status()===TRUE){
            $this->db->trans_commit();
            $this->flash('fas fa-save','Adjustment '.$adjNo.' sent for approval','success');
        }else{
            $this->db->trans_rollback();
            $this->flash('fas fa-warning','Record Error','danger');
        }
        redirect('Stockadjustment');
    }

    public function Stockadjustmentview(){
        $id=$this->input->post('recordID');
        $h=$this->db->query("SELECT a.*, b.branch, u.name AS created_by, ap.name AS approved_by_name
            FROM tbl_stock_adjustment a
            LEFT JOIN tbl_company_branch b ON b.idtbl_company_branch=a.tbl_company_branch_idtbl_company_branch
            LEFT JOIN tbl_user u ON u.idtbl_user=a.tbl_user_idtbl_user
            LEFT JOIN tbl_user ap ON ap.idtbl_user=a.approve_by
            WHERE a.idtbl_stock_adjustment=?", array($id))->row();

        $d=$this->db->query("SELECT d.*, p.product_code, p.product_name, s.batchno, s.qty AS stock_qty, ps.stock_status AS serial_status
            FROM tbl_stock_adjustment_detail d
            JOIN tbl_product p ON p.idtbl_product=d.tbl_product_idtbl_product
            JOIN tbl_stock s ON s.idtbl_stock=d.tbl_stock_idtbl_stock
            LEFT JOIN tbl_product_serial ps ON ps.idtbl_product_serial=d.tbl_product_serial_idtbl_product_serial
            WHERE d.tbl_stock_adjustment_idtbl_stock_adjustment=? AND d.status=1", array($id))->result();

        $o=new stdClass(); $o->header=$h; $o->lines=$d;
        echo json_encode($o);
    }

    public function Stockadjustmentapprove(){
        $userID=$_SESSION['userid'];
        $now=date('Y-m-d H:i:s');
        $id=(int)$this->input->post('recordID');
        $action=(int)$this->input->post('action'); // 1 approve, 2 reject
        $remark=$this->input->post('remark');

        $this->db->trans_begin();

        $h=$this->db->query("SELECT * FROM tbl_stock_adjustment WHERE idtbl_stock_adjustment=? AND status=1 FOR UPDATE", array($id))->row();
        if(!$h || $h->approve_status!=0){
            $this->db->trans_rollback();
            $this->flash('fas fa-warning','This adjustment is already processed','danger');
            echo 'ERR'; return;
        }

        if($action==1){
            $details=$this->db->get_where('tbl_stock_adjustment_detail', array(
                'tbl_stock_adjustment_idtbl_stock_adjustment'=>$id,'status'=>1))->result();

            foreach($details as $d){
                $s=$this->db->query("SELECT * FROM tbl_stock WHERE idtbl_stock=? FOR UPDATE", array($d->tbl_stock_idtbl_stock))->row();
                if(!$s){ $this->db->trans_rollback(); $this->flash('fas fa-warning','Stock row not found','danger'); echo 'ERR'; return; }

                // ---------- NON SERIAL ----------
                if((int)$d->tbl_product_serial_idtbl_product_serial==0){
                    if($h->adjustment_type==2 && $s->qty<$d->qty){
                        $this->db->trans_rollback();
                        $this->flash('fas fa-warning','Not enough stock in batch '.$s->batchno.'. Cannot approve.','danger');
                        echo 'ERR'; return;
                    }
                    $newQty=($h->adjustment_type==1)?$s->qty+$d->qty:$s->qty-$d->qty;
                    $this->db->where('idtbl_stock', $s->idtbl_stock);
                    $this->db->update('tbl_stock', array('qty'=>$newQty,'updateuser'=>$userID,'updatedatetime'=>$now));
                    continue;   // no serial movement for non serial items
                }

                // ---------- SERIAL ----------
                $ps=$this->db->query("SELECT * FROM tbl_product_serial WHERE idtbl_product_serial=? FOR UPDATE",
                        array($d->tbl_product_serial_idtbl_product_serial))->row();
                $err='';
                if(!$ps){ $err='Serial not found'; }
                else if($this->serialInTransfer($ps->idtbl_product_serial)){ $err='Serial '.$d->serialno.' is now in an open transfer'; }
                else if($h->adjustment_type==2){
                    if($ps->stock_status!=1 || $ps->tbl_company_branch_idtbl_company_branch!=$h->tbl_company_branch_idtbl_company_branch){
                        $err='Serial '.$d->serialno.' is no longer in stock at this branch';
                    }else if($s->qty<1){ $err='Stock qty is zero for batch '.$s->batchno; }
                }else if(!in_array((int)$ps->stock_status, $this->plusStatuses)){
                    $err='Serial '.$d->serialno.' is now in stock, so it cannot be added';
                }
                if($err!==''){
                    $this->db->trans_rollback();
                    $this->flash('fas fa-warning',$err.'. Cannot approve.','danger');
                    echo 'ERR'; return;
                }

                if($h->adjustment_type==1){
                    $from=(int)$ps->stock_status; $to=1; $mt=10; $newQty=$s->qty+1;
                    $this->db->where('idtbl_product_serial', $ps->idtbl_product_serial);
                    $this->db->update('tbl_product_serial', array(
                        'stock_status'=>1,
                        'sold_invoice_id'=>NULL,'sold_date'=>NULL,'warranty_end'=>NULL,   // clears a previous sale
                        'tbl_company_idtbl_company'=>$h->tbl_company_idtbl_company,
                        'tbl_company_branch_idtbl_company_branch'=>$h->tbl_company_branch_idtbl_company_branch,
                        'updateuser'=>$userID,'updatedatetime'=>$now));
                }else{
                    $from=1; $to=8; $mt=11; $newQty=$s->qty-1;
                    $this->db->where('idtbl_product_serial', $ps->idtbl_product_serial);
                    $this->db->update('tbl_product_serial', array('stock_status'=>8,'updateuser'=>$userID,'updatedatetime'=>$now));
                }

                $this->db->where('idtbl_stock', $s->idtbl_stock);
                $this->db->update('tbl_stock', array('qty'=>$newQty,'updateuser'=>$userID,'updatedatetime'=>$now));

                $this->db->insert('tbl_serial_movement', array(
                    'movement_type'=>$mt,'from_status'=>$from,'to_status'=>$to,
                    'doc_type'=>'ADJUSTMENT','doc_id'=>$id,'doc_detail_id'=>$d->idtbl_stock_adjustment_detail,
                    'branch_id'=>$h->tbl_company_branch_idtbl_company_branch,
                    'remark'=>$h->adjustment_no.' - '.$h->reason,
                    'insertdatetime'=>$now,'tbl_user_idtbl_user'=>$userID,
                    'tbl_product_serial_idtbl_product_serial'=>$ps->idtbl_product_serial
                ));
            }
        }

        $this->db->where('idtbl_stock_adjustment', $id);
        $this->db->update('tbl_stock_adjustment', array(
            'approve_status'=>($action==1)?1:2,
            'approve_by'=>$userID,
            'approve_datetime'=>$now,
            'approve_remark'=>$remark,
            'updateuser'=>$userID,
            'updatedatetime'=>$now
        ));

        if($this->db->trans_status()===TRUE){
            $this->db->trans_commit();
            $this->flash('fas fa-check', $action==1?'Adjustment approved and stock updated':'Adjustment rejected', $action==1?'success':'warning');
            echo 'OK';
        }else{
            $this->db->trans_rollback();
            $this->flash('fas fa-warning','Record Error','danger');
            echo 'ERR';
        }
    }

    public function Stockadjustmentstatus($x, $y){
        $userID=$_SESSION['userid'];
        $now=date('Y-m-d H:i:s');
        $h=$this->db->get_where('tbl_stock_adjustment', array('idtbl_stock_adjustment'=>$x))->row();

        if($y==3 && $h && $h->approve_status==0){
            $this->db->where('idtbl_stock_adjustment', $x);
            $this->db->update('tbl_stock_adjustment', array('status'=>3,'updateuser'=>$userID,'updatedatetime'=>$now));
            $this->flash('fas fa-trash-alt','Record Remove Successfully','danger');
        }else{
            $this->flash('fas fa-warning','Only pending adjustments can be removed','danger');
        }
        redirect('Stockadjustment');
    }
}