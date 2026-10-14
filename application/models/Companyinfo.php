<?php
class Companyinfo extends CI_Model{

    // sets the flash message used by the page toast
    private function Setmsg($icon, $message, $type){
        $actionObj=new stdClass();
        $actionObj->icon=$icon;
        $actionObj->title='';
        $actionObj->message=$message;
        $actionObj->url='';
        $actionObj->target='_blank';
        $actionObj->type=$type;

        $this->session->set_flashdata('msg', json_encode($actionObj));
    }

    public function Companyinsertupdate(){
        $this->db->trans_begin();

        $userID=$_SESSION['userid'];

        $company=$this->input->post('company');
        $code=$this->input->post('code');
        $address1=$this->input->post('address1');
        $address2=$this->input->post('address2');
        $mobile=$this->input->post('mobile');
        $phone=$this->input->post('phone');
        $email=$this->input->post('email');
        $refno=$this->input->post('ref_no');
        $vatregno=$this->input->post('vat_reg_no');
        $svatno=$this->input->post('svat_no');

        $recordOption=$this->input->post('recordOption');
        if(!empty($this->input->post('recordID'))){$recordID=$this->input->post('recordID');}

        $insertdatetime=date('Y-m-d H:i:s');

        $data = array(
            'company'=> $company,
            'code'=> $code,
            'address1'=> $address1,
            'address2'=> $address2,
            'mobile'=> $mobile,
            'phone'=> $phone,
            'email'=> $email,
            'ref_no'=> $refno,
            'vat_reg_no'=> $vatregno,
            'svat_no'=> $svatno,
        );

        if($recordOption==1){
            $data['status']='1';
            $data['insertdatetime']=$insertdatetime;
            $data['tbl_user_idtbl_user']=$userID;

            $this->db->insert('tbl_company', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->Setmsg('fas fa-save', 'Record Added Successfully', 'success');
            } else {
                $this->Setmsg('fas fa-warning', 'Record Error', 'danger');
            }
            redirect('Company');
        }
        else{
            $data['updatedatetime']=$insertdatetime;
            $data['updateuser']=$userID;

            $this->db->where('idtbl_company', $recordID);
            $this->db->update('tbl_company', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->Setmsg('fas fa-save', 'Record Update Successfully', 'primary');
            } else {
                $this->Setmsg('fas fa-warning', 'Record Error', 'danger');
            }
            redirect('Company');
        }
    }
    public function Companystatus($x, $y){
        $this->db->trans_begin();

        $userID=$_SESSION['userid'];
        $recordID=$x;
        $type=$y;
        $updatedatetime=date('Y-m-d H:i:s');

        if($type==1){ $icon='fas fa-check'; $message='Record Activate Successfully'; $color='success'; }
        else if($type==2){ $icon='fas fa-times'; $message='Record Deactivate Successfully'; $color='warning'; }
        else if($type==3){ $icon='fas fa-trash-alt'; $message='Record Remove Successfully'; $color='danger'; }
        else{ redirect('Company'); }

        $data = array(
            'status' => $type,
            'updateuser'=> $userID,
            'updatedatetime'=> $updatedatetime
        );

        $this->db->where('idtbl_company', $recordID);
        $this->db->update('tbl_company', $data);

        $this->db->trans_complete();

        if ($this->db->trans_status() === TRUE) {
            $this->Setmsg($icon, $message, $color);
        } else {
            $this->Setmsg('fas fa-warning', 'Record Error', 'danger');
        }
        redirect('Company');
    }
    public function Companyedit(){
        $recordID=$this->input->post('recordID');

        $this->db->select('*');
        $this->db->from('tbl_company');
        $this->db->where('idtbl_company', $recordID);
        $this->db->where_in('status', array(1, 2));

        $respond=$this->db->get();

        $obj=new stdClass();
        $obj->id=$respond->row(0)->idtbl_company;
        $obj->company=$respond->row(0)->company;
        $obj->code=$respond->row(0)->code;
        $obj->address1=$respond->row(0)->address1;
        $obj->address2=$respond->row(0)->address2;
        $obj->mobile=$respond->row(0)->mobile;
        $obj->phone=$respond->row(0)->phone;
        $obj->email=$respond->row(0)->email;
        $obj->ref_no=$respond->row(0)->ref_no;
        $obj->vat_reg_no=$respond->row(0)->vat_reg_no;
        $obj->svat_no=$respond->row(0)->svat_no;
        echo json_encode($obj);
    }
}
