<?php
class Companybranchinfo extends CI_Model{

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

    // active companies for the company dropdown
    public function Getcompanylist(){
        $this->db->select('idtbl_company, company');
        $this->db->from('tbl_company');
        $this->db->where('status', 1);
        $this->db->order_by('company', 'ASC');
        return $this->db->get()->result();
    }

    public function Companybranchinsertupdate(){
        $this->db->trans_begin();

        $userID=$_SESSION['userid'];

        $companyID=$this->input->post('company');
        $type=$this->input->post('type');
        $branch=$this->input->post('branch');
        $code=$this->input->post('code');
        $address1=$this->input->post('address1');
        $address2=$this->input->post('address2');
        $mobile=$this->input->post('mobile');
        $phone=$this->input->post('phone');
        $email=$this->input->post('email');

        $recordOption=$this->input->post('recordOption');
        if(!empty($this->input->post('recordID'))){$recordID=$this->input->post('recordID');}

        $insertdatetime=date('Y-m-d H:i:s');

        $data = array(
            'tbl_company_idtbl_company'=> $companyID,
            'type'=> $type,
            'branch'=> $branch,
            'code'=> $code,
            'address1'=> $address1,
            'address2'=> $address2,
            'mobile'=> $mobile,
            'phone'=> $phone,
            'email'=> $email,
        );

        if($recordOption==1){
            $data['status']='1';
            $data['insertdatetime']=$insertdatetime;
            $data['tbl_user_idtbl_user']=$userID;

            $this->db->insert('tbl_company_branch', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->Setmsg('fas fa-save', 'Record Added Successfully', 'success');
            } else {
                $this->Setmsg('fas fa-warning', 'Record Error', 'danger');
            }
            redirect('Companybranch');
        }
        else{
            $data['updatedatetime']=$insertdatetime;
            $data['updateuser']=$userID;

            $this->db->where('idtbl_company_branch', $recordID);
            $this->db->update('tbl_company_branch', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->Setmsg('fas fa-save', 'Record Update Successfully', 'primary');
            } else {
                $this->Setmsg('fas fa-warning', 'Record Error', 'danger');
            }
            redirect('Companybranch');
        }
    }
    public function Companybranchstatus($x, $y){
        $this->db->trans_begin();

        $userID=$_SESSION['userid'];
        $recordID=$x;
        $type=$y;
        $updatedatetime=date('Y-m-d H:i:s');

        if($type==1){ $icon='fas fa-check'; $message='Record Activate Successfully'; $color='success'; }
        else if($type==2){ $icon='fas fa-times'; $message='Record Deactivate Successfully'; $color='warning'; }
        else if($type==3){ $icon='fas fa-trash-alt'; $message='Record Remove Successfully'; $color='danger'; }
        else{ redirect('Companybranch'); }

        $data = array(
            'status' => $type,
            'updateuser'=> $userID,
            'updatedatetime'=> $updatedatetime
        );

        $this->db->where('idtbl_company_branch', $recordID);
        $this->db->update('tbl_company_branch', $data);

        $this->db->trans_complete();

        if ($this->db->trans_status() === TRUE) {
            $this->Setmsg($icon, $message, $color);
        } else {
            $this->Setmsg('fas fa-warning', 'Record Error', 'danger');
        }
        redirect('Companybranch');
    }
    public function Companybranchedit(){
        $recordID=$this->input->post('recordID');

        $this->db->select('*');
        $this->db->from('tbl_company_branch');
        $this->db->where('idtbl_company_branch', $recordID);
        $this->db->where_in('status', array(1, 2));

        $respond=$this->db->get();

        $obj=new stdClass();
        $obj->id=$respond->row(0)->idtbl_company_branch;
        $obj->companyid=$respond->row(0)->tbl_company_idtbl_company;
        $obj->type=$respond->row(0)->type;
        $obj->branch=$respond->row(0)->branch;
        $obj->code=$respond->row(0)->code;
        $obj->address1=$respond->row(0)->address1;
        $obj->address2=$respond->row(0)->address2;
        $obj->mobile=$respond->row(0)->mobile;
        $obj->phone=$respond->row(0)->phone;
        $obj->email=$respond->row(0)->email;
        echo json_encode($obj);
    }
}
