<?php
class Giftvoucherinfo extends CI_Model{
    public function Giftvoucherinsertupdate(){
        $this->db->trans_begin();

        $userID=$_SESSION['userid'];

        $vouchercode=strtoupper(trim($this->input->post('vouchercode')));
        $vouchervalue=$this->input->post('vouchervalue');
        $issuedate=$this->input->post('issuedate');
        $expirydate=$this->input->post('expirydate');

        $recordOption=$this->input->post('recordOption');
        if(!empty($this->input->post('recordID'))){$recordID=$this->input->post('recordID');}

        $insertdatetime=date('Y-m-d H:i:s');

        // voucher code must be unique (removed records are ignored)
        $this->db->select('idtbl_gift_voucher');
        $this->db->from('tbl_gift_voucher');
        $this->db->where('voucher_code', $vouchercode);
        $this->db->where('status !=', 3);
        if($recordOption==2){$this->db->where('idtbl_gift_voucher !=', $recordID);}
        $duplicate=$this->db->get();

        if($duplicate->num_rows()>0){
            $this->db->trans_rollback();
            $this->Giftvoucherflash('fas fa-warning', 'Voucher Code Already Exists', 'danger');
            redirect('Giftvoucher');
        }

        if($recordOption==1){
            $data = array(
                'voucher_code'=> $vouchercode, 
                'voucher_value'=> $vouchervalue, 
                'balance'=> 0, 
                'issue_date'=> $issuedate, 
                'expiry_date'=> $expirydate, 
                'status'=> '1', 
                'insertdatetime'=> $insertdatetime, 
                'tbl_user_idtbl_user'=> $userID,
            );

            $this->db->insert('tbl_gift_voucher', $data);


            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                $this->Giftvoucherflash('fas fa-save', 'Record Added Successfully', 'success');
                redirect('Giftvoucher');                
            } else {
                $this->db->trans_rollback();
                $this->Giftvoucherflash('fas fa-warning', 'Record Error', 'danger');
                redirect('Giftvoucher');
            }
        }
        else{
            $data = array(
                'voucher_code'=> $vouchercode, 
                'voucher_value'=> $vouchervalue, 
                'issue_date'=> $issuedate, 
                'expiry_date'=> $expirydate, 
                'status'=> '1', 
            );

            $this->db->where('idtbl_gift_voucher', $recordID);
            $this->db->update('tbl_gift_voucher', $data);


            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                $this->Giftvoucherflash('fas fa-save', 'Record Update Successfully', 'primary');
                redirect('Giftvoucher');                
            } else {
                $this->db->trans_rollback();
                $this->Giftvoucherflash('fas fa-warning', 'Record Error', 'danger');
                redirect('Giftvoucher');
            }
        }
    }
    public function Giftvoucherstatus($x, $y){
        $this->db->trans_begin();

        $recordID=$x;
        $type=$y;
        if($type==1){
            $icon='fas fa-check';
            $message='Record Activate Successfully';
            $msgtype='success';
        }
        else if($type==2){
            $icon='fas fa-times';
            $message='Record Deactivate Successfully';
            $msgtype='warning';
        }
        else{
            $type=3;
            $icon='fas fa-trash-alt';
            $message='Record Remove Successfully';
            $msgtype='danger';
        }

        $data = array(
            'status' => (string)$type,
        );

        $this->db->where('idtbl_gift_voucher', $recordID);
        $this->db->update('tbl_gift_voucher', $data);


        $this->db->trans_complete();

        if ($this->db->trans_status() === TRUE) {
            $this->db->trans_commit();
            $this->Giftvoucherflash($icon, $message, $msgtype);
            redirect('Giftvoucher');                
        } else {
            $this->db->trans_rollback();
            $this->Giftvoucherflash('fas fa-warning', 'Record Error', 'danger');
            redirect('Giftvoucher');
        }
    }
    public function Giftvoucheredit(){
        $recordID=$this->input->post('recordID');

        $this->db->select('*');
        $this->db->from('tbl_gift_voucher');
        $this->db->where('idtbl_gift_voucher', $recordID);
        $this->db->where('status', 1);

        $respond=$this->db->get();

        $obj=new stdClass();
        $obj->id=$respond->row(0)->idtbl_gift_voucher;
        $obj->vouchercode=$respond->row(0)->voucher_code;
        $obj->vouchervalue=$respond->row(0)->voucher_value;
        $obj->issuedate=substr($respond->row(0)->issue_date, 0, 10);
        $obj->expirydate=substr($respond->row(0)->expiry_date, 0, 10);
        echo json_encode($obj);
    }
    public function Giftvoucherlog(){
        $recordID=$this->input->post('recordID');

        $this->db->select('l.action, l.amount, l.balance_after, l.insertdatetime, u.name, l.tbl_invoice_idtbl_invoice');
        $this->db->from('tbl_gift_voucher_log AS l');
        $this->db->join('tbl_user AS u', 'u.idtbl_user = l.tbl_user_idtbl_user', 'left');
        $this->db->where('l.tbl_gift_voucher_idtbl_gift_voucher', $recordID);
        $this->db->order_by('l.idtbl_gift_voucher_log', 'DESC');

        $respond=$this->db->get();

        $rows=array();
        foreach($respond->result() as $row){
            $obj=new stdClass();
            $obj->action=$row->action;
            $obj->amount=$row->amount;
            $obj->insertdatetime=$row->insertdatetime;
            $obj->user=$row->name;
            $obj->invoice=$row->tbl_invoice_idtbl_invoice;
            $rows[]=$obj;
        }
        echo json_encode($rows);
    }

    // helpers
    private function Giftvoucherflash($icon, $message, $type){
        $actionObj=new stdClass();
        $actionObj->icon=$icon;
        $actionObj->title='';
        $actionObj->message=$message;
        $actionObj->url='';
        $actionObj->target='_blank';
        $actionObj->type=$type;

        $actionJSON=json_encode($actionObj);
        
        $this->session->set_flashdata('msg', $actionJSON);
    }
}