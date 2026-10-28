<?php
class Stocktransferinfo extends CI_Model{

    private $stepby=array(2 => 'from', 3 => 'from', 4 => 'to', 5 => 'to', 6 => 'to');

    const SERIAL_INSTOCK=1;
    const SERIAL_TRANSIT=6;
    const MOVE_TRANSFER=6;   // tbl_serial_movement.movement_type

    public function Getbootdata(){
        $obj=new stdClass();
        $obj->branches=$this->Getbranches();
        $obj->mybranch=$this->Userbranch();
        echo json_encode($obj);
    }
    public function Getlist(){
        $ub=$this->Userbranch();

        $this->headerquery();
        if($ub>0){   // branch users only see transfers sent from or coming to their branch
            $this->db->group_start();
            $this->db->where('t.from_branch_id', $ub);
            $this->db->or_where('t.to_branch_id', $ub);
            $this->db->group_end();
        }
        $this->db->order_by('t.idtbl_stock_transfer', 'DESC');

        $respond=$this->db->get();
        $rows=$respond->result();

        foreach($rows as $r){
            $this->decorate($r, $ub);
        }
        echo json_encode($rows);
    }
    public function Gettransfer(){
        $id=(int)$this->input->post('id');
        $ub=$this->Userbranch();

        $this->headerquery();
        $this->db->where('t.idtbl_stock_transfer', $id);
        $respond=$this->db->get();
        $t=$respond->row();

        if(!$t || ($ub>0 && $ub!=(int)$t->from_branch_id && $ub!=(int)$t->to_branch_id)){
            return $this->respond(false, 'Transfer not found.');
        }
        $this->decorate($t, $ub);

        $this->db->select('*');
        $this->db->from('tbl_stock_transfer_note');
        $this->db->where('tbl_stock_transfer_idtbl_stock_transfer', $id);
        $respondnote=$this->db->get();
        $n=$respondnote->row();
        $t->note_no=$n ? $n->note_no : '';

        $details=$this->db->query("SELECT d.idtbl_stock_transfer_detail AS did, p.product_code AS code, p.product_name AS name
                                   FROM tbl_stock_transfer_detail d
                                   JOIN tbl_product p ON p.idtbl_product = d.tbl_product_idtbl_product
                                   WHERE d.tbl_stock_transfer_idtbl_stock_transfer = ? AND d.status = 1
                                   ORDER BY d.idtbl_stock_transfer_detail", array($id))->result();
        $ser=$this->db->query("SELECT tbl_stock_transfer_detail_idtbl_stock_transfer_detail AS did, serialno
                               FROM tbl_stock_transfer_serial WHERE tbl_stock_transfer_idtbl_stock_transfer = ?
                               ORDER BY idtbl_stock_transfer_serial", array($id))->result();
        $map=array();
        foreach($ser as $s){
            $map[$s->did][]=$s->serialno;
        }
        foreach($details as $d){
            $d->serials=isset($map[$d->did]) ? $map[$d->did] : array();
        }
        $t->items=$details;

        $t->log=$this->db->query("SELECT tr.step, tr.step_datetime AS at, u.name AS user, b.branch AS branch
                                  FROM tbl_stock_transfer_track tr
                                  JOIN tbl_user u ON u.idtbl_user = tr.tbl_user_idtbl_user
                                  LEFT JOIN tbl_company_branch b ON b.idtbl_company_branch = tr.branch_id
                                  WHERE tr.tbl_stock_transfer_idtbl_stock_transfer = ?
                                  ORDER BY tr.step", array($id))->result();

        $obj=new stdClass();
        $obj->ok=true;
        $obj->data=$t;
        echo json_encode($obj);
    }
    public function Getproducts(){
        $from=(int)$this->input->post('from');

        $respond=$this->db->query("SELECT p.idtbl_product AS id, p.product_code AS code, p.product_name AS name, COUNT(ps.idtbl_product_serial) AS free
                                   FROM tbl_product_serial ps
                                   JOIN tbl_product p ON p.idtbl_product = ps.tbl_product_idtbl_product
                                   WHERE ps.stock_status = 1 AND ps.status = 1 AND p.status = 1
                                   AND ps.tbl_company_branch_idtbl_company_branch = ?
                                   AND NOT EXISTS (SELECT 1 FROM tbl_stock_transfer_serial ts
                                                   WHERE ts.tbl_product_serial_idtbl_product_serial = ps.idtbl_product_serial AND ts.status = 1)
                                   ".$this->notreturned()."
                                   GROUP BY p.idtbl_product, p.product_code, p.product_name
                                   ORDER BY p.product_name", array($from));
        echo json_encode($respond->result());
    }
    public function Getserials(){
        $pid=(int)$this->input->post('pid');
        $from=(int)$this->input->post('from');

        $respond=$this->db->query("SELECT ps.idtbl_product_serial AS id, ps.serialno AS no,
                                        ps.tbl_grn_idtbl_grn AS grn, g.grn_no, g.batchno, g.grndate
                                FROM tbl_product_serial ps
                                JOIN tbl_grn g ON g.idtbl_grn = ps.tbl_grn_idtbl_grn
                                WHERE ps.tbl_product_idtbl_product = ? AND ps.stock_status = 1 AND ps.status = 1
                                AND ps.tbl_company_branch_idtbl_company_branch = ?
                                AND NOT EXISTS (SELECT 1 FROM tbl_stock_transfer_serial ts
                                                WHERE ts.tbl_product_serial_idtbl_product_serial = ps.idtbl_product_serial AND ts.status = 1)
                                ".$this->notreturned()."
                                ORDER BY g.grndate, g.idtbl_grn, ps.idtbl_product_serial", array($pid, $from));
        echo json_encode($respond->result());
    }
    public function Stocktransferinsert(){
        $userID=(int)$_SESSION['userid'];
        $ub=$this->Userbranch();

        $from=(int)$this->input->post('from');
        $to=(int)$this->input->post('to');
        $date=trim($this->input->post('date'));
        $vehicle=trim($this->input->post('vehicle'));
        $driver=trim($this->input->post('driver'));
        $contact=trim($this->input->post('contact'));
        $remark=trim($this->input->post('remark'));
        $items=json_decode($this->input->post('items'), true);

        $insertdatetime=date('Y-m-d H:i:s');

        if(!$from || !$to){ return $this->respond(false, 'Select the from and to locations.'); }
        if($from==$to){ return $this->respond(false, 'From and To locations must be different.'); }
        if($date=='' || $vehicle=='' || $driver==''){ return $this->respond(false, 'Enter the date, vehicle number and driver.'); }
        if(empty($items)){ return $this->respond(false, 'Add at least one item to the transfer.'); }
        if($ub>0 && $ub!=$from){ return $this->respond(false, 'You can only create transfers from your own location.'); }

        $fb=$this->branchrow($from);
        $tb=$this->branchrow($to);
        if(!$fb || !$tb || !in_array((int)$fb->type, array(1, 2)) || !in_array((int)$tb->type, array(1, 2))){
            return $this->respond(false, 'Invalid location selected.');
        }

        // 1 showroom>showroom, 2 warehouse>warehouse, 3 showroom>warehouse, 4 warehouse>showroom
        if($fb->type==1 && $tb->type==1){ $transfertype=1; }
        elseif($fb->type==2 && $tb->type==2){ $transfertype=2; }
        elseif($fb->type==1){ $transfertype=3; }
        else{ $transfertype=4; }

        $companyID=isset($_SESSION['company_id']) ? (int)$_SESSION['company_id'] : 0;
        if(!$companyID){ $companyID=$fb->tbl_company_idtbl_company; }

        $this->db->trans_begin();

        $data=array(
            'transfer_no'=> '',
            'transfer_date'=> $date,
            'transfer_type'=> $transfertype,
            'from_branch_id'=> $from,
            'to_branch_id'=> $to,
            'vehicle_no'=> $vehicle,
            'driver_name'=> $driver,
            'driver_contact'=> $contact,
            'remark'=> $remark,
            'total_qty'=> 0,
            'transfer_status'=> 1,
            'status'=> '1',
            'insertdatetime'=> $insertdatetime,
            'tbl_user_idtbl_user'=> $userID,
            'tbl_company_idtbl_company'=> $companyID,
        );
        $this->db->insert('tbl_stock_transfer', $data);

        $transferID=$this->db->insert_id();
        $transferNo='TRF'.date('y').str_pad($transferID, 4, '0', STR_PAD_LEFT);

        $total=0;
        foreach($items as $it){
            $productID=(int)$it['pid'];
            $ids=array_values(array_unique(array_map('intval', (array)$it['serials'])));
            if(!$productID || !count($ids)){ return $this->fail('Invalid item in the transfer.'); }

            $ph=$this->placeholders($ids);
            $rows=$this->db->query("SELECT ps.idtbl_product_serial AS id, ps.serialno FROM tbl_product_serial ps
                                    WHERE ps.tbl_product_idtbl_product = ? AND ps.stock_status = 1 AND ps.status = 1
                                        AND ps.tbl_company_branch_idtbl_company_branch = ?
                                        AND ps.idtbl_product_serial IN ($ph)
                                        ".$this->notreturned()." FOR UPDATE", array_merge(array($productID, $from), $ids))->result();
            if(count($rows)!=count($ids)){ return $this->fail('One or more serial numbers are not available at the sending location.'); }

            $locked=$this->db->query("SELECT 1 FROM tbl_stock_transfer_serial WHERE status = 1
                                      AND tbl_product_serial_idtbl_product_serial IN ($ph) LIMIT 1", $ids)->row();
            if($locked){ return $this->fail('One or more serial numbers are already in another open transfer.'); }

            $fifo=$this->fifocheck($productID, $from, $ids);          // <-- new
            if($fifo!==true){ return $this->fail($fifo); }            // <-- new

            $data=array(
                'tbl_stock_transfer_idtbl_stock_transfer'=> $transferID,
                'tbl_product_idtbl_product'=> $productID,
                'qty'=> count($ids),
                'status'=> '1',
                'insertdatetime'=> $insertdatetime,
                'tbl_user_idtbl_user'=> $userID,
            );
            $this->db->insert('tbl_stock_transfer_detail', $data);

            $detailID=$this->db->insert_id();

            foreach($rows as $r){
                $data=array(
                    'tbl_stock_transfer_idtbl_stock_transfer'=> $transferID,
                    'tbl_stock_transfer_detail_idtbl_stock_transfer_detail'=> $detailID,
                    'tbl_product_serial_idtbl_product_serial'=> $r->id,
                    'serialno'=> $r->serialno,
                    'status'=> '1',
                    'insertdatetime'=> $insertdatetime,
                );
                $this->db->insert('tbl_stock_transfer_serial', $data);
            }
            $total+=count($ids);
        }

        $data=array(
            'transfer_no'=> $transferNo,
            'total_qty'=> $total,
        );
        $this->db->where('idtbl_stock_transfer', $transferID);
        $this->db->update('tbl_stock_transfer', $data);

        // transfer note is created automatically
        $data=array(
            'note_no'=> 'TN'.date('y').str_pad($transferID, 4, '0', STR_PAD_LEFT),
            'tbl_stock_transfer_idtbl_stock_transfer'=> $transferID,
            'status'=> '1',
            'insertdatetime'=> $insertdatetime,
            'tbl_user_idtbl_user'=> $userID,
        );
        $this->db->insert('tbl_stock_transfer_note', $data);

        $this->track($transferID, 1, $from, $userID, 'Transfer created');

        if($this->db->trans_status()===FALSE){ return $this->fail('Could not save the transfer.'); }
        $this->db->trans_commit();

        $obj=new stdClass();
        $obj->ok=true;
        $obj->id=$transferID;
        $obj->no=$transferNo;
        echo json_encode($obj);
    }
    public function Stocktransferadvance(){
        $userID=(int)$_SESSION['userid'];
        $ub=$this->Userbranch();
        $recordID=(int)$this->input->post('id');
        $updatedatetime=date('Y-m-d H:i:s');

        $this->db->trans_begin();

        $t=$this->db->query("SELECT * FROM tbl_stock_transfer WHERE idtbl_stock_transfer = ? AND status = 1 FOR UPDATE", array($recordID))->row();
        if(!$t){ return $this->fail('Transfer not found.'); }
        if($t->transfer_status>=6){ return $this->fail('This transfer is already completed.'); }

        $next=(int)$t->transfer_status+1;
        if(!$this->canact((int)$t->transfer_status, (int)$t->from_branch_id, (int)$t->to_branch_id, $ub)){
            $who=($this->stepby[$next]=='from') ? 'sending' : 'receiving';
            return $this->fail('This step must be done by the '.$who.' location.');
        }

        if($next==3){
            $r=$this->dispatch($t, $userID);
            if($r!==true){ return $this->fail($r); }
        }
        if($next==6){
            $r=$this->receive($t, $userID);
            if($r!==true){ return $this->fail($r); }
        }

        $data=array(
            'transfer_status'=> $next,
            'updateuser'=> $userID,
            'updatedatetime'=> $updatedatetime,
        );
        $this->db->where('idtbl_stock_transfer', $recordID);
        $this->db->update('tbl_stock_transfer', $data);

        $branchID=($this->stepby[$next]=='from') ? $t->from_branch_id : $t->to_branch_id;
        $this->track($recordID, $next, $branchID, $userID);

        if($this->db->trans_status()===FALSE){ return $this->fail('Could not update the transfer.'); }
        $this->db->trans_commit();

        $obj=new stdClass();
        $obj->ok=true;
        $obj->status=$next;
        echo json_encode($obj);
    }

    /* Step 3: stock reduced at sending location, serials set to In Transit */
    private function dispatch($t, $userID){
        $insertdatetime=date('Y-m-d H:i:s');
        $transferID=(int)$t->idtbl_stock_transfer;

        $this->db->select('*');
        $this->db->from('tbl_stock_transfer_detail');
        $this->db->where('tbl_stock_transfer_idtbl_stock_transfer', $transferID);
        $this->db->where('status', 1);
        $respond=$this->db->get();
        $details=$respond->result();

        foreach($details as $d){
            $detailID=(int)$d->idtbl_stock_transfer_detail;
            $serials=$this->db->query("SELECT ts.tbl_product_serial_idtbl_product_serial AS sid, ps.serialno, ps.stock_status,
                                              ps.tbl_company_branch_idtbl_company_branch AS br, ps.tbl_grn_idtbl_grn AS grn
                                       FROM tbl_stock_transfer_serial ts
                                       JOIN tbl_product_serial ps ON ps.idtbl_product_serial = ts.tbl_product_serial_idtbl_product_serial
                                       WHERE ts.tbl_stock_transfer_detail_idtbl_stock_transfer_detail = ? AND ts.status = 1 FOR UPDATE", array($detailID))->result();

            $bygrn=array();
            foreach($serials as $s){
                if((int)$s->stock_status!=self::SERIAL_INSTOCK || (int)$s->br!=(int)$t->from_branch_id){
                    return 'Serial '.$s->serialno.' is no longer available at the sending location.';
                }
                $bygrn[$s->grn]=isset($bygrn[$s->grn]) ? $bygrn[$s->grn]+1 : 1;
            }

            // reduce tbl_stock (by GRN so batch and cost are carried over later)
            foreach($bygrn as $grn => $need){
                $rows=$this->db->query("SELECT idtbl_stock, qty FROM tbl_stock
                                        WHERE tbl_product_idtbl_product = ? AND tbl_grn_idtbl_grn = ?
                                          AND tbl_company_branch_idtbl_company_branch = ? AND status = 1 AND qty > 0
                                        ORDER BY idtbl_stock FOR UPDATE", array($d->tbl_product_idtbl_product, $grn, $t->from_branch_id))->result();
                foreach($rows as $r){
                    if($need<=0){ break; }
                    $take=min($need, (float)$r->qty);

                    $this->db->set('qty', 'qty - '.(float)$take, FALSE);
                    $this->db->set('updateuser', $userID);
                    $this->db->set('updatedatetime', $insertdatetime);
                    $this->db->where('idtbl_stock', $r->idtbl_stock);
                    $this->db->update('tbl_stock');

                    $data=array(
                        'tbl_stock_transfer_idtbl_stock_transfer'=> $transferID,
                        'tbl_stock_transfer_detail_idtbl_stock_transfer_detail'=> $detailID,
                        'tbl_stock_idtbl_stock'=> $r->idtbl_stock,
                        'tbl_grn_idtbl_grn'=> $grn,
                        'qty'=> $take,
                        'new_stock_id'=> NULL,
                        'status'=> '1',
                        'insertdatetime'=> $insertdatetime,
                    );
                    $this->db->insert('tbl_stock_transfer_detail_stock', $data);

                    $need-=$take;
                }
                if($need>0){ return 'Not enough stock at the sending location to dispatch this transfer.'; }
            }

            // serials -> in transit
            foreach($serials as $s){
                $data=array(
                    'stock_status'=> self::SERIAL_TRANSIT,
                    'updatedatetime'=> $insertdatetime,
                    'updateuser'=> $userID,
                );
                $this->db->where('idtbl_product_serial', $s->sid);
                $this->db->update('tbl_product_serial', $data);

                $this->movement($s->sid, self::SERIAL_INSTOCK, self::SERIAL_TRANSIT, $transferID, $detailID, $t->from_branch_id,
                                'Dispatched on transfer '.$t->transfer_no, $userID);
            }
        }
        return true;
    }

    /* Step 6: new stock records at the receiving location, serials now belong to it */
    private function receive($t, $userID){
        $insertdatetime=date('Y-m-d H:i:s');
        $transferID=(int)$t->idtbl_stock_transfer;

        $tb=$this->branchrow($t->to_branch_id);
        if(!$tb){ return 'Receiving location not found.'; }

        $this->db->select('*');
        $this->db->from('tbl_stock_transfer_detail_stock');
        $this->db->where('tbl_stock_transfer_idtbl_stock_transfer', $transferID);
        $this->db->where('status', 1);
        $respond=$this->db->get();
        $parts=$respond->result();

        foreach($parts as $p){
            $this->db->select('*');
            $this->db->from('tbl_stock');
            $this->db->where('idtbl_stock', $p->tbl_stock_idtbl_stock);
            $respondstock=$this->db->get();
            $src=$respondstock->row();
            if(!$src){ return 'Source stock record missing.'; }

            $data=array(
                'batchno'=> $src->batchno,
                'grnqty'=> $p->qty,
                'qty'=> $p->qty,
                'costunitprice'=> $src->costunitprice,
                'saleprice'=> $src->saleprice,
                'stock_status'=> 1,
                'status'=> '1',
                'insertdatetime'=> $insertdatetime,
                'tbl_user_idtbl_user'=> $userID,
                'tbl_product_idtbl_product'=> $src->tbl_product_idtbl_product,
                'tbl_grn_idtbl_grn'=> $src->tbl_grn_idtbl_grn,
                'tbl_grndetail_idtbl_grndetail'=> $src->tbl_grndetail_idtbl_grndetail,
                'tbl_location_idtbl_location'=> $src->tbl_location_idtbl_location,
                'tbl_company_idtbl_company'=> $tb->tbl_company_idtbl_company,
                'tbl_company_branch_idtbl_company_branch'=> $t->to_branch_id,
            );
            $this->db->insert('tbl_stock', $data);

            $newStockID=$this->db->insert_id();

            $data=array(
                'new_stock_id'=> $newStockID,
            );
            $this->db->where('idtbl_stock_transfer_detail_stock', $p->idtbl_stock_transfer_detail_stock);
            $this->db->update('tbl_stock_transfer_detail_stock', $data);
        }

        $this->db->select('*');
        $this->db->from('tbl_stock_transfer_serial');
        $this->db->where('tbl_stock_transfer_idtbl_stock_transfer', $transferID);
        $this->db->where('status', 1);
        $respondserial=$this->db->get();
        $serials=$respondserial->result();

        foreach($serials as $s){
            $data=array(
                'stock_status'=> self::SERIAL_INSTOCK,
                'tbl_company_branch_idtbl_company_branch'=> $t->to_branch_id,
                'updatedatetime'=> $insertdatetime,
                'updateuser'=> $userID,
            );
            $this->db->where('idtbl_product_serial', $s->tbl_product_serial_idtbl_product_serial);
            $this->db->update('tbl_product_serial', $data);

            $this->movement($s->tbl_product_serial_idtbl_product_serial, self::SERIAL_TRANSIT, self::SERIAL_INSTOCK, $transferID,
                            $s->tbl_stock_transfer_detail_idtbl_stock_transfer_detail, $t->to_branch_id,
                            'Received on transfer '.$t->transfer_no, $userID);

            $data=array(
                'status'=> 2,   // 2 = completed
            );
            $this->db->where('idtbl_stock_transfer_serial', $s->idtbl_stock_transfer_serial);
            $this->db->update('tbl_stock_transfer_serial', $data);
        }
        return true;
    }

    /* ---------------------------------------------------------- common functions */

    /* FIFO: a serial from a newer GRN cannot be sent while serials of an older GRN are still free */
    private function fifocheck($productID, $from, $ids){
        $rows=$this->db->query("SELECT ps.idtbl_product_serial AS id, ps.tbl_grn_idtbl_grn AS grn, g.grn_no, g.grndate
                                FROM tbl_product_serial ps
                                JOIN tbl_grn g ON g.idtbl_grn = ps.tbl_grn_idtbl_grn
                                WHERE ps.tbl_product_idtbl_product = ? AND ps.stock_status = 1 AND ps.status = 1
                                AND ps.tbl_company_branch_idtbl_company_branch = ?
                                AND NOT EXISTS (SELECT 1 FROM tbl_stock_transfer_serial ts
                                                WHERE ts.tbl_product_serial_idtbl_product_serial = ps.idtbl_product_serial AND ts.status = 1)
                                ".$this->notreturned()."
                                ORDER BY g.grndate, g.idtbl_grn, ps.idtbl_product_serial", array($productID, $from))->result();

        $rank=-1; $last=null; $ranks=array(); $names=array(); $maxSel=-1;
        foreach($rows as $r){
            if($r->grn!==$last){ $rank++; $last=$r->grn; $names[$rank]=$r->grn_no.' ('.$r->grndate.')'; }
            $ranks[(int)$r->id]=$rank;
            if(in_array((int)$r->id, $ids)){ $maxSel=max($maxSel, $rank); }
        }
        foreach($rows as $r){
            if($ranks[(int)$r->id] < $maxSel && !in_array((int)$r->id, $ids)){
                return 'FIFO: batch '.$names[$maxSel].' cannot be selected because older stock from '.$names[$ranks[(int)$r->id]].' is still available. Transfer the older serial numbers first.';
            }
        }
        return true;
    }

    /* the logged-in branch comes from the session ($_SESSION['branch_id']).
       If that branch is not a showroom/warehouse (type 1 or 2) it returns 0 = can act for all locations */
    private function Userbranch(){
        $b=isset($_SESSION['branch_id']) ? (int)$_SESSION['branch_id'] : 0;
        $r=$this->branchrow($b);
        return ($r && in_array((int)$r->type, array(1, 2))) ? $b : 0;
    }

    private function Getbranches(){
        $this->db->select('idtbl_company_branch AS id, branch AS name, type');
        $this->db->from('tbl_company_branch');
        $this->db->where('status', 1);
        $this->db->where_in('type', array(1, 2));
        $this->db->order_by('type');
        $this->db->order_by('branch');

        $respond=$this->db->get();
        return $respond->result();
    }

    private function branchrow($id){
        $this->db->select('*');
        $this->db->from('tbl_company_branch');
        $this->db->where('idtbl_company_branch', (int)$id);
        $this->db->where('status', 1);

        $respond=$this->db->get();
        return $respond->row();
    }

    private function headerquery(){
        $this->db->select('t.idtbl_stock_transfer AS id, t.transfer_no AS no, t.transfer_date AS date, t.transfer_type AS type,
                           t.from_branch_id, t.to_branch_id, t.vehicle_no AS vehicle, t.driver_name AS driver,
                           t.driver_contact AS contact, t.remark, t.total_qty AS qty, t.transfer_status AS status,
                           fb.branch AS from_name, fb.type AS from_type, tb.branch AS to_name, tb.type AS to_type');
        $this->db->from('tbl_stock_transfer t');
        $this->db->join('tbl_company_branch fb', 'fb.idtbl_company_branch = t.from_branch_id');
        $this->db->join('tbl_company_branch tb', 'tb.idtbl_company_branch = t.to_branch_id');
        $this->db->where('t.status', 1);
    }

    private function decorate($r, $ub){
        $r->can_act=$this->canact((int)$r->status, (int)$r->from_branch_id, (int)$r->to_branch_id, $ub);
        $r->wait_name='';
        if((int)$r->status<6){
            $r->wait_name=($this->stepby[$r->status+1]=='from') ? $r->from_name : $r->to_name;
        }
        return $r;
    }

    private function canact($status, $from, $to, $ub){
        if($status>=6){ return false; }
        $actor=($this->stepby[$status+1]=='from') ? $from : $to;
        return ($ub==0 || $ub==(int)$actor);
    }

    private function placeholders($arr){
        return implode(',', array_fill(0, count($arr), '?'));
    }

    /* serials returned to supplier via GRN return must never be transferred */
    private function notreturned(){
        return " AND NOT EXISTS (SELECT 1 FROM tbl_serial_movement sm
                                WHERE sm.tbl_product_serial_idtbl_product_serial = ps.idtbl_product_serial
                                AND sm.movement_type = 4)
                AND NOT EXISTS (SELECT 1 FROM tbl_grn_return_detail rd
                                WHERE rd.tbl_product_serial_idtbl_product_serial = ps.idtbl_product_serial
                                AND rd.status = 1) ";
    }

    private function movement($serialID, $fromstatus, $tostatus, $transferID, $detailID, $branchID, $remark, $userID){
        $data=array(
            'movement_type'=> self::MOVE_TRANSFER,
            'from_status'=> $fromstatus,
            'to_status'=> $tostatus,
            'doc_type'=> 'TRANSFER',
            'doc_id'=> $transferID,
            'doc_detail_id'=> $detailID,
            'branch_id'=> $branchID,
            'remark'=> $remark,
            'insertdatetime'=> date('Y-m-d H:i:s'),
            'tbl_user_idtbl_user'=> $userID,
            'tbl_product_serial_idtbl_product_serial'=> $serialID,
        );
        $this->db->insert('tbl_serial_movement', $data);
    }

    private function track($transferID, $step, $branchID, $userID, $remark=''){
        $data=array(
            'tbl_stock_transfer_idtbl_stock_transfer'=> $transferID,
            'step'=> $step,
            'branch_id'=> $branchID,
            'step_datetime'=> date('Y-m-d H:i:s'),
            'remark'=> $remark,
            'tbl_user_idtbl_user'=> $userID,
        );
        $this->db->insert('tbl_stock_transfer_track', $data);
    }

    private function respond($ok, $msg){
        $obj=new stdClass();
        $obj->ok=$ok;
        $obj->msg=$msg;
        echo json_encode($obj);
    }

    private function fail($msg){
        $this->db->trans_rollback();
        $this->respond(false, $msg);
    }
}