<?php
class Reservationinfo extends CI_Model{

    public function Getcustomerlist(){
        $this->db->select('idtbl_customer, customer, telephone_no');
        $this->db->from('tbl_customer');
        $this->db->where('status', 1);
        $this->db->order_by('customer', 'ASC');
        return $this->db->get();
    }

    public function Getproductlist(){
        $this->db->select('idtbl_product, product_code, product_name, selling_price');
        $this->db->from('tbl_product');
        $this->db->where('status', 1);
        $this->db->order_by('product_name', 'ASC');
        return $this->db->get();
    }

    public function Getvatrate($date){
        if(empty($date)){ $date=date('Y-m-d'); }

        $this->db->select('percentage');
        $this->db->from('tbl_tax_control');
        $this->db->where('tax_controlname', 'Vat');
        $this->db->where('status', 1);
        $this->db->where('effective_from <=', $date);
        $this->db->where('(effective_to IS NULL OR effective_to >= '.$this->db->escape($date).')', NULL, FALSE);
        $this->db->order_by('effective_from', 'DESC');
        $this->db->limit(1);
        $respond=$this->db->get();

        if($respond->num_rows()>0){
            return (float)$respond->row(0)->percentage;
        }
        return 0;
    }

    public function Reservationinsertupdate(){
        $this->db->trans_begin();

        $userID=$_SESSION['userid'];
        $companyID=$_SESSION['company_id'];  
        $branchID=$_SESSION['branch_id']; 

        $customer=$this->input->post('customer');
        $reservationdate=$this->input->post('reservationdate');
        $expirydate=$this->input->post('expirydate');
        $vatapplicable=$this->input->post('vatapplicable')==1 ? 1 : 0;
        $advance=$this->input->post('advance');
        $remark=$this->input->post('remark');
        $tableData=json_decode($this->input->post('tableData'));

        $recordOption=$this->input->post('recordOption');
        if(!empty($this->input->post('recordID'))){$recordID=$this->input->post('recordID');}

        $insertdatetime=date('Y-m-d H:i:s');

        if(empty($tableData)){
            $this->db->trans_rollback();
            $this->Respond('fas fa-warning', 'Please add at least one item', 'danger');
        }

        $vatrate=$vatapplicable==1 ? $this->Getvatrate($reservationdate) : 0;

        $totalqty=0;
        $subtotal=0;
        $vatamount=0;
        $lines=array();
        foreach($tableData as $rowdata){
            $linetotal=round($rowdata->qty*$rowdata->unitprice, 2);
            $linevat=round($linetotal*$vatrate/100, 2);

            $totalqty+=$rowdata->qty;
            $subtotal+=$linetotal;
            $vatamount+=$linevat;

            $lines[]=array(
                'productID'=>$rowdata->productID,
                'qty'=>$rowdata->qty,
                'unitprice'=>$rowdata->unitprice,
                'line_total'=>$linetotal,
                'vat_amount'=>$linevat,
                'line_net_total'=>$linetotal+$linevat,
                'comment'=>$rowdata->comment,
            );
        }
        $totalamount=$subtotal+$vatamount;

        if($advance>$totalamount){
            $this->db->trans_rollback();
            $this->Respond('fas fa-warning', 'Advance amount cannot be greater than the total', 'danger');
        }

        if($recordOption==1){
            $year=date('y');
            $this->db->like('reservation_no', 'RSV'.$year, 'after');
            $count=$this->db->count_all_results('tbl_reservation');
            $reservationno='RSV'.$year.str_pad($count+1, 4, '0', STR_PAD_LEFT);

            $data = array(
                'reservation_no'=> $reservationno,
                'reservation_date'=> $reservationdate,
                'expiry_date'=> $expirydate,
                'total_qty'=> $totalqty,
                'subtotal'=> $subtotal,
                'vat_applicable'=> $vatapplicable,
                'vat_rate'=> $vatrate,
                'vat_amount'=> $vatamount,
                'total_amount'=> $totalamount,
                'advance_amount'=> $advance,
                'remark'=> $remark,
                'reserve_status'=> '1',
                'status'=> '1',
                'insertdatetime'=> $insertdatetime,
                'tbl_user_idtbl_user'=> $userID,
                'tbl_customer_idtbl_customer'=> $customer,
                'tbl_company_idtbl_company'=> $companyID,
                'tbl_company_branch_idtbl_company_branch'=> $branchID,
            );
            $this->db->insert('tbl_reservation', $data);
            $reservationID=$this->db->insert_id();

            $this->Insertdetails($lines, $reservationID, $userID, $insertdatetime);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                $this->Respond('fas fa-save', 'Reservation '.$reservationno.' Added Successfully', 'success');
            } else {
                $this->db->trans_rollback();
                $this->Respond('fas fa-warning', 'Record Error', 'danger');
            }
        }
        else{
            $data = array(
                'reservation_date'=> $reservationdate,
                'expiry_date'=> $expirydate,
                'total_qty'=> $totalqty,
                'subtotal'=> $subtotal,
                'vat_applicable'=> $vatapplicable,
                'vat_rate'=> $vatrate,
                'vat_amount'=> $vatamount,
                'total_amount'=> $totalamount,
                'advance_amount'=> $advance,
                'remark'=> $remark,
                'updatedatetime'=> $insertdatetime,
                'updateuser'=> $userID,
                'tbl_customer_idtbl_customer'=> $customer,
            );
            $this->db->where('idtbl_reservation', $recordID);
            $this->db->where('reserve_status', 1);
            $this->db->update('tbl_reservation', $data);

            $this->db->where('tbl_reservation_idtbl_reservation', $recordID);
            $this->db->update('tbl_reservation_detail', array('status'=>'3'));

            $this->Insertdetails($lines, $recordID, $userID, $insertdatetime);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                $this->Respond('fas fa-save', 'Record Update Successfully', 'primary');
            } else {
                $this->db->trans_rollback();
                $this->Respond('fas fa-warning', 'Record Error', 'danger');
            }
        }
    }

    public function Reservationstatus($x, $y){
        $this->db->trans_begin();

        $userID=$_SESSION['userid'];
        $recordID=$x;
        $type=$y;
        $updatedatetime=date('Y-m-d H:i:s');

        if($type==1){
            $data = array('reserve_status'=>'1', 'updateuser'=>$userID, 'updatedatetime'=>$updatedatetime);
            $icon='fas fa-check'; $msg='Reservation Reopened Successfully'; $alert='success';
        }
        else if($type==2){
            $data = array('reserve_status'=>'3', 'updateuser'=>$userID, 'updatedatetime'=>$updatedatetime);
            $icon='fas fa-times'; $msg='Reservation Cancelled Successfully'; $alert='warning';
        }
        else{
            $data = array('status'=>'3', 'updateuser'=>$userID, 'updatedatetime'=>$updatedatetime);
            $icon='fas fa-trash-alt'; $msg='Record Remove Successfully'; $alert='danger';
        }

        $this->db->where('idtbl_reservation', $recordID);
        $this->db->where('reserve_status !=', 2);
        $this->db->update('tbl_reservation', $data);

        $this->db->trans_complete();

        if ($this->db->trans_status() === TRUE) {
            $this->db->trans_commit();
            $this->Respond($icon, $msg, $alert);
        } else {
            $this->db->trans_rollback();
            $this->Respond('fas fa-warning', 'Record Error', 'danger');
        }
    }

    public function Reservationedit(){
        $recordID=$this->input->post('recordID');

        $this->db->select('*');
        $this->db->from('tbl_reservation');
        $this->db->where('idtbl_reservation', $recordID);
        $this->db->where('status', 1);
        $this->db->where('reserve_status', 1);
        $respond=$this->db->get();

        $obj=new stdClass();
        $obj->id=$respond->row(0)->idtbl_reservation;
        $obj->customer=$respond->row(0)->tbl_customer_idtbl_customer;
        $obj->reservationdate=$respond->row(0)->reservation_date;
        $obj->expirydate=$respond->row(0)->expiry_date;
        $obj->vatapplicable=$respond->row(0)->vat_applicable;
        $obj->vatrate=$this->Getvatrate($respond->row(0)->reservation_date);
        $obj->advance=$respond->row(0)->advance_amount;
        $obj->remark=$respond->row(0)->remark;
        $obj->items=$this->Getitems($recordID);
        echo json_encode($obj);
    }

    public function Reservationitems(){
        $recordID=$this->input->post('recordID');

        $this->db->select('subtotal, vat_rate, vat_amount, total_amount');
        $this->db->from('tbl_reservation');
        $this->db->where('idtbl_reservation', $recordID);
        $header=$this->db->get()->row(0);

        $obj=new stdClass();
        $obj->items=$this->Getitems($recordID);
        $obj->subtotal=$header->subtotal;
        $obj->vatrate=$header->vat_rate;
        $obj->vatamount=$header->vat_amount;
        $obj->total=$header->total_amount;
        echo json_encode($obj);
    }

    private function Getitems($reservationID){
        $this->db->select('d.tbl_product_idtbl_product AS productID, p.product_code, p.product_name, d.qty, d.unitprice, d.line_total, d.vat_amount, d.line_net_total, d.comment');
        $this->db->from('tbl_reservation_detail AS d');
        $this->db->join('tbl_product AS p', 'p.idtbl_product = d.tbl_product_idtbl_product');
        $this->db->where('d.tbl_reservation_idtbl_reservation', $reservationID);
        $this->db->where('d.status', 1);
        return $this->db->get()->result();
    }

    private function Insertdetails($lines, $reservationID, $userID, $insertdatetime){
        foreach($lines as $line){
            $detail = array(
                'qty'=> $line['qty'],
                'unitprice'=> $line['unitprice'],
                'line_total'=> $line['line_total'],
                'vat_amount'=> $line['vat_amount'],
                'line_net_total'=> $line['line_net_total'],
                'comment'=> $line['comment'],
                'status'=> '1',
                'insertdatetime'=> $insertdatetime,
                'tbl_user_idtbl_user'=> $userID,
                'tbl_reservation_idtbl_reservation'=> $reservationID,
                'tbl_product_idtbl_product'=> $line['productID'],
            );
            $this->db->insert('tbl_reservation_detail', $detail);
        }
    }

    private function Respond($icon, $message, $type){
        $actionObj=new stdClass();
        $actionObj->icon=$icon;
        $actionObj->title='';
        $actionObj->message=$message;
        $actionObj->url='';
        $actionObj->target='_blank';
        $actionObj->type=$type;

        $this->session->set_flashdata('msg', json_encode($actionObj));
        redirect('Reservation');
    }
}