<?php
class Userinfo extends CI_Model{
    public function LoginUser(){
        $username=$this->input->post('username');
        $password=$this->input->post('password');
        $company_id=$this->input->post('company_id');
        $branch_id=$this->input->post('branch_id');
        $companyname=$this->input->post('company_text');
        $branchname=$this->input->post('branch_text');
        
        $this->db->select('*');
        $this->db->from('tbl_user');
        $this->db->join('tbl_user_type', 'tbl_user_type.idtbl_user_type = tbl_user.tbl_user_type_idtbl_user_type');
        $this->db->where('tbl_user.username', $username);
        $this->db->where('tbl_user.status', 1);
        // $this->db->where('password', $password);
        // $this->db->where('status', 1);
        
        $respond=$this->db->get();

        $hash_password = password_verify($password, $respond->row(0)->password);
		if($hash_password === true) {          
            // return $respond->row(0);
            $user_data = $respond->row(0);
            return [
                'user_data' => $user_data,
                'company_id' => $company_id,
                'branch_id' => $branch_id,
                'company_name' => $companyname,
                'branch_name' => $branchname,
            ];
        }
        else{
            return false; 
        }
    }
    public function Usertype(){
        $this->db->select('idtbl_user_type, type');
        $this->db->from('tbl_user_type');
        if($_SESSION['type']==1){
            $this->db->where('status', 1);
        }
        else{
            $this->db->where('idtbl_user_type >', '1');
            $this->db->where('status', 1);

        }

        return $respond=$this->db->get();
    }
    public function Useraccountedit(){
        $recordID=$this->input->post('recordID');

        $this->db->select('*');
        $this->db->from('tbl_user');
        $this->db->where('idtbl_user', $recordID);
        $this->db->where('status', 1);

        $respond=$this->db->get();

        $this->db->select('tbl_roles_idtbl_roles');
        $this->db->from('tbl_user_has_tbl_roles');
        $this->db->where('tbl_user_idtbl_user', $recordID);

        $respond2=$this->db->get();

        $roleArray=array();
        if(!empty($respond2->result())):
            foreach($respond2->result() as $rowrespond2):
                $objrole=new stdClass();
                $objrole->roleID=$rowrespond2->tbl_roles_idtbl_roles;
                array_push($roleArray, $objrole);
            endforeach;
        endif;

        $obj=new stdClass();
        $obj->id=$respond->row(0)->idtbl_user;
        $obj->name=$respond->row(0)->name;
        $obj->username=$respond->row(0)->username;
        $obj->type=$respond->row(0)->tbl_user_type_idtbl_user_type;
        $obj->roles=$roleArray;

        echo json_encode($obj);
    }
    public function Useraccountinsertupdate(){
        $this->db->trans_begin();

        $userID=$_SESSION['userid'];

        $accountname=$this->input->post('accountname');
        $username=$this->input->post('username');
        if (!empty($this->input->post('password'))) {
            $password = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
        }
        $usertype=$this->input->post('usertype');
        $userroles=$this->input->post('userroles');

        $recordOption=$this->input->post('recordOption');
        if(!empty($this->input->post('recordID'))){$recordID=$this->input->post('recordID');}

        $updatedatetime=date('Y-m-d H:i:s');

        if($recordOption==1){
            $data = array(
                'name'=>$accountname, 
                'username'=>$username, 
                'password'=>$password, 
                'status'=>'1', 
                'insertdatetime'=>$updatedatetime, 
                'tbl_user_type_idtbl_user_type'=>$usertype
            );

            $this->db->insert('tbl_user', $data);

            $lastID=$this->db->insert_id();

            if(!empty($userroles)){
                foreach($userroles as $rowuserroles){
                    $data2 = array(
                        'tbl_user_idtbl_user'=>$lastID, 
                        'tbl_roles_idtbl_roles'=>$rowuserroles
                    );
    
                    $this->db->insert('tbl_user_has_tbl_roles', $data2);
                }
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                
                $actionObj=new stdClass();
                $actionObj->icon='fas fa-save';
                $actionObj->title='';
                $actionObj->message='Record Added Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='success';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Useraccount');                
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Useraccount');
            }
        }
        else{
            if(!empty($this->input->post('password'))){

                $data = array(
                    'name'=>$accountname, 
                    'username'=>$username, 
                    'password'=>$password, 
                    'updateuser'=>$userID, 
                    'updatedatetime'=>$updatedatetime, 
                    'tbl_user_type_idtbl_user_type'=>$usertype
                );
    
                $this->db->where('idtbl_user', $recordID);
                $this->db->update('tbl_user', $data);
            }
            else{
                $data = array(
                    'name'=>$accountname, 
                    'username'=>$username, 
                    'updateuser'=>$userID, 
                    'updatedatetime'=>$updatedatetime, 
                    'tbl_user_type_idtbl_user_type'=>$usertype
                );
    
                $this->db->where('idtbl_user', $recordID);
                $this->db->update('tbl_user', $data);
            }

            $this->db->delete('tbl_user_has_tbl_roles', array('tbl_user_idtbl_user' => $recordID));

            if(!empty($userroles)){
                foreach($userroles as $rowuserroles){
                    $data2 = array(
                        'tbl_user_idtbl_user'=>$recordID, 
                        'tbl_roles_idtbl_roles'=>$rowuserroles
                    );
    
                    $this->db->insert('tbl_user_has_tbl_roles', $data2);
                }
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                
                $actionObj=new stdClass();
                $actionObj->icon='fas fa-save';
                $actionObj->title='';
                $actionObj->message='Record Update Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='primary';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Useraccount');                
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Useraccount');
            }
        }
    }
    public function Useraccountstatus($x, $y){
        $this->db->trans_begin();

        $userID=$_SESSION['userid'];
        $recordID=$x;
        $type=$y;
        $updatedatetime=date('Y-m-d H:i:s');

        if($type==1){
            $data = array(
                'status' => '1',
                'modified_user_id'=> $userID, 
                'updated_at'=> $updatedatetime
            );

            $this->db->where('id', $recordID);
            $this->db->update('users', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                
                $actionObj=new stdClass();
                $actionObj->icon='fas fa-check';
                $actionObj->title='';
                $actionObj->message='Record Activate Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='success';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Useraccount');                
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Useraccount');
            }
        }
        else if($type==2){
            $data = array(
                'status' => '2',
                'modified_user_id'=> $userID, 
                'updated_at'=> $updatedatetime
            );

            $this->db->where('id', $recordID);
            $this->db->update('users', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                
                $actionObj=new stdClass();
                $actionObj->icon='fas fa-times';
                $actionObj->title='';
                $actionObj->message='Record Deactivate Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='warning';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Useraccount');                
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Useraccount');
            }
        }
        else if($type==3){
            $data = array(
                'status' => '3',
                'modified_user_id'=> $userID, 
                'updated_at'=> $updatedatetime
            );

            $this->db->where('id', $recordID);
            $this->db->update('users', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                
                $actionObj=new stdClass();
                $actionObj->icon='fas fa-trash-alt';
                $actionObj->title='';
                $actionObj->message='Record Remove Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Useraccount');                
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Useraccount');
            }
        }
    }
    public function Usertypeedit(){
        $recordID=$this->input->post('recordID');

        $this->db->select('*');
        $this->db->from('tbl_user_type');
        $this->db->where('idtbl_user_type', $recordID);
        $this->db->where('status', 1);

        $respond=$this->db->get();

        $obj=new stdClass();
        $obj->id=$respond->row(0)->idtbl_user_type;
        $obj->type=$respond->row(0)->type;

        echo json_encode($obj);
    }
    public function Usertypeinsertupdate(){
        $this->db->trans_begin();

        $userID=$_SESSION['userid'];

        $usertype=$this->input->post('usertype');

        $recordOption=$this->input->post('recordOption');
        if(!empty($this->input->post('recordID'))){$recordID=$this->input->post('recordID');}

        $updatedatetime=date('Y-m-d H:i:s');

        if($recordOption==1){
            $data = array(
                'type'=> $usertype,
                'status'=> '1',
                'insertdatetime'=> $updatedatetime
            );

            $this->db->insert('tbl_user_type', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                
                $actionObj=new stdClass();
                $actionObj->icon='fas fa-save';
                $actionObj->title='';
                $actionObj->message='Record Added Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='success';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Usertype');                
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Usertype');
            }
        }
        else{
            $data = array(
                'type'=> $usertype,
                'updatedatetime'=> $updatedatetime
            );

            $this->db->where('idtbl_user_type', $recordID);
            $this->db->update('tbl_user_type', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                
                $actionObj=new stdClass();
                $actionObj->icon='fas fa-save';
                $actionObj->title='';
                $actionObj->message='Record Update Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='primary';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Usertype');                
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Usertype');
            }
        }
    }
    public function Usertypestatus($x, $y){
        $this->db->trans_begin();

        $userID=$_SESSION['userid'];
        $recordID=$x;
        $type=$y;
        $updatedatetime=date('Y-m-d H:i:s');

        if($type==1){
            $data = array(
                'status' => '1',
                'updatedatetime'=> $updatedatetime
            );

            $this->db->where('idtbl_user_type', $recordID);
            $this->db->update('tbl_user_type', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                
                $actionObj=new stdClass();
                $actionObj->icon='fas fa-check';
                $actionObj->title='';
                $actionObj->message='Record Activate Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='success';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Usertype');                
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Usertype');
            }
        }
        else if($type==2){
            $data = array(
                'status' => '2',
                'updatedatetime'=> $updatedatetime
            );

            $this->db->where('idtbl_user_type', $recordID);
            $this->db->update('tbl_user_type', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                
                $actionObj=new stdClass();
                $actionObj->icon='fas fa-times';
                $actionObj->title='';
                $actionObj->message='Record Deactivate Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='warning';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Usertype');                
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Usertype');
            }
        }
        else if($type==3){
            $data = array(
                'status' => '3',
                'updatedatetime'=> $updatedatetime
            );

            $this->db->where('idtbl_user_type', $recordID);
            $this->db->update('tbl_user_type', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                
                $actionObj=new stdClass();
                $actionObj->icon='fas fa-trash-alt';
                $actionObj->title='';
                $actionObj->message='Record Remove Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Usertype');                
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Usertype');
            }
        }
    }
    public function Menulist(){
        $this->db->select('idtbl_menu_list, menu');
        $this->db->from('tbl_menu_list');
        $this->db->where('status', 1);

        return $respond=$this->db->get();
    }
    public function Useraccountmenu(){
        if($_SESSION['userid']==1){
            $this->db->select('idtbl_user, name');
            $this->db->from('tbl_user');
            $this->db->where('status', 1);
        }
        else{
            $this->db->select('idtbl_user, name');
            $this->db->from('tbl_user');
            $this->db->where('idtbl_user >', '1');
            $this->db->where('status', 1);
        }        

        return $respond=$this->db->get();
    }
    public function Userprivilegeinsertupdate(){
        $this->db->trans_begin();

        $userID=$_SESSION['userid'];

        $userlist=$this->input->post('userlist');
        $menulist=$this->input->post('menulist');
        if(!empty($this->input->post('addcheck'))){$addcheck=$this->input->post('addcheck');}else{$addcheck=0;}
        if(!empty($this->input->post('editcheck'))){$editcheck=$this->input->post('editcheck');}else{$editcheck=0;}
        if(!empty($this->input->post('statuscheck'))){$statuscheck=$this->input->post('statuscheck');}else{$statuscheck=0;}
        if(!empty($this->input->post('removecheck'))){$removecheck=$this->input->post('removecheck');}else{$removecheck=0;}
        if(!empty($this->input->post('approvecheck'))){$approvecheck=$this->input->post('approvecheck');}else{$approvecheck=0;}
        if(!empty($this->input->post('checkstatus'))){$checkstatus=$this->input->post('checkstatus');}else{$checkstatus=0;}

        $recordOption=$this->input->post('recordOption');
        if(!empty($this->input->post('recordID'))){$recordID=$this->input->post('recordID');}

        $updatedatetime=date('Y-m-d h:i:s');

        if($recordOption==1){
            foreach($menulist as $rowmenulist){
                $data = array(
                    'add'=>$addcheck,
                    'edit'=>$editcheck,
                    'statuschange'=>$statuscheck,
                    'remove'=>$removecheck,
                    'approvestatus'=>$approvecheck,
                    'checkstatus'=>$checkstatus,
                    'access_status'=>'1',
                    'status'=>'1',
                    'insertdatetime'=>$updatedatetime,
                    'tbl_user_idtbl_user'=>$userlist,
                    'tbl_menu_list_idtbl_menu_list'=>$rowmenulist
                );

                $this->db->insert('tbl_user_privilege', $data);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                
                $actionObj=new stdClass();
                $actionObj->icon='fas fa-save';
                $actionObj->title='';
                $actionObj->message='Record Added Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='success';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Userprivilege');                
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Userprivilege');
            }
        }
        else{
            foreach($menulist as $rowmenulist){
                $data = array(
                    'add'=>$addcheck,
                    'edit'=>$editcheck,
                    'statuschange'=>$statuscheck,
                    'remove'=>$removecheck,
                    'approvestatus'=>$approvecheck,
                    'checkstatus'=>$checkstatus,
                    'access_status'=>'1',
                    'updateuser'=>$userID,
                    'updatedatetime'=>$updatedatetime,
                    'tbl_user_idtbl_user'=>$userlist,
                    'tbl_menu_list_idtbl_menu_list'=>$rowmenulist
                );

                $this->db->where('idtbl_user_privilege', $recordID);
                $this->db->update('tbl_user_privilege', $data);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                
                $actionObj=new stdClass();
                $actionObj->icon='fas fa-save';
                $actionObj->title='';
                $actionObj->message='Record Update Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='primary';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Userprivilege');                
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Userprivilege');
            }
        }
    }
    public function Userprivilegeedit(){
        $recordID=$this->input->post('recordID');

        $this->db->select('*');
        $this->db->from('tbl_user_privilege');
        $this->db->where('idtbl_user_privilege', $recordID);
        $this->db->where('status', 1);

        $respond=$this->db->get();

        $menulistArray=array();
        $objmenulist=new stdClass();
        $objmenulist->menulistID=$respond->row(0)->tbl_menu_list_idtbl_menu_list;
        array_push($menulistArray, $objmenulist);

        $obj=new stdClass();
        $obj->id=$respond->row(0)->idtbl_user_privilege;
        $obj->add=$respond->row(0)->add;
        $obj->edit=$respond->row(0)->edit;
        $obj->statuschange=$respond->row(0)->statuschange;
        $obj->remove=$respond->row(0)->remove;
        $obj->approvestatus=$respond->row(0)->approvestatus;
        $obj->checkstatus=$respond->row(0)->checkstatus;
        $obj->user=$respond->row(0)->tbl_user_idtbl_user;
        $obj->menu=$menulistArray;

        echo json_encode($obj);
    }
    public function Userprivilegestatus($x, $y){
        $this->db->trans_begin();

        $userID=$_SESSION['userid'];
        $recordID=$x;
        $type=$y;
        $updatedatetime=date('Y-m-d h:i:s');

        if($type==1){
            $data = array(
                'status' => '1',
                'updateuser'=>$userID,
                'updatedatetime'=>$updatedatetime,
            );

            $this->db->where('idtbl_user_privilege', $recordID);
            $this->db->update('tbl_user_privilege', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                
                $actionObj=new stdClass();
                $actionObj->icon='fas fa-check';
                $actionObj->title='';
                $actionObj->message='Record Activate Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='success';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Userprivilege');                
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Userprivilege');
            }
        }
        else if($type==2){
            $data = array(
                'status' => '2',
                'updateuser'=>$userID,
                'updatedatetime'=>$updatedatetime,
            );

            $this->db->where('idtbl_user_privilege', $recordID);
            $this->db->update('tbl_user_privilege', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                
                $actionObj=new stdClass();
                $actionObj->icon='fas fa-times';
                $actionObj->title='';
                $actionObj->message='Record Deactivate Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='warning';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Userprivilege');                
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Userprivilege');
            }
        }
        else if($type==3){
            $data = array(
                'status' => '3',
                'updateuser'=>$userID,
                'updatedatetime'=>$updatedatetime,
            );

            $this->db->where('idtbl_user_privilege', $recordID);
            $this->db->update('tbl_user_privilege', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                
                $actionObj=new stdClass();
                $actionObj->icon='fas fa-trash-alt';
                $actionObj->title='';
                $actionObj->message='Record Remove Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Userprivilege');                
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Userprivilege');
            }
        }
    }
    public function Userpermissionsinsertupdate(){
        $this->db->trans_begin();

        $userID=$_SESSION['userid'];
        $userpermission = $this->input->post('userpermission');
        $permission = array();
        if(!empty($this->input->post('permission_create'))){$permission[] = array('name' => strtolower($userpermission).'-create', 'type' => '1');}
        if(!empty($this->input->post('permission_edit'))){$permission[] = array('name' => strtolower($userpermission).'-edit', 'type' => '2');}
        if(!empty($this->input->post('permission_status'))){$permission[] = array('name' => strtolower($userpermission).'-status', 'type' => '3');}
        if(!empty($this->input->post('permission_delete'))){$permission[] = array('name' => strtolower($userpermission).'-delete', 'type' => '4');}
        if(!empty($this->input->post('permission_approve'))){$permission[] = array('name' => strtolower($userpermission).'-approve', 'type' => '5');}
        if(!empty($this->input->post('permission_check'))){$permission[] = array('name' => strtolower($userpermission).'-check', 'type' => '6');}
        if(!empty($this->input->post('permission_account'))){$permission[] = array('name' => strtolower($userpermission).'-account', 'type' => '7');}

        $updatedatetime=date('Y-m-d h:i:s');
        foreach($permission as $rowpermission){
            $data = array(
                'permission'=>$rowpermission['name'],
                'guard_name'=>'web-erp',
                'module'=>$userpermission,
                'permission_type'=>$rowpermission['type'],
                'insertdatetime'=>$updatedatetime,
                'tbl_user_idtbl_user'=>$userID
            );

            $this->db->insert('tbl_permissions', $data);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === TRUE) {
            $this->db->trans_commit();
            $actionObj=new stdClass();
            $actionObj->icon='fas fa-save';
            $actionObj->title='';
            $actionObj->message='Record Added Successfully';
            $actionObj->url='';
            $actionObj->target='_blank';
            $actionObj->type='success';

            $actionJSON=json_encode($actionObj);
            $this->session->set_flashdata('msg', $actionJSON);
            redirect('User/Userpermissions');
        } else {
            $this->db->trans_rollback();
            $actionObj=new stdClass();
            $actionObj->icon='fas fa-warning';
            $actionObj->title='';
            $actionObj->message='Record Error';
            $actionObj->url='';
            $actionObj->target='_blank';
            $actionObj->type='danger';

            $actionJSON=json_encode($actionObj);
            $this->session->set_flashdata('msg', $actionJSON);
            redirect('User/Userpermissions');
        }
    }
    public function Userpermissionsstatus($id, $status){
        $this->db->trans_begin();

        $this->db->delete('tbl_permissions', array('idtbl_permissions' => $id));

        $this->db->trans_complete();

        if ($this->db->trans_status() === TRUE) {
            $this->db->trans_commit();
            $actionObj=new stdClass();
            $actionObj->icon='fas fa-trash-alt';
            $actionObj->title='';
            $actionObj->message='Record Removed Successfully';
            $actionObj->url='';
            $actionObj->target='_blank';
            $actionObj->type='success';

            $actionJSON=json_encode($actionObj);
            $this->session->set_flashdata('msg', $actionJSON);
            redirect('User/Userpermissions');
        } else {
            $this->db->trans_rollback();
            $actionObj=new stdClass();
            $actionObj->icon='fas fa-warning';
            $actionObj->title='';
            $actionObj->message='Record Error';
            $actionObj->url='';
            $actionObj->target='_blank';
            $actionObj->type='danger';

            $actionJSON=json_encode($actionObj);
            $this->session->set_flashdata('msg', $actionJSON);
            redirect('User/Userpermissions');
        }
    }
    public function Getpermissionlist(){
        $this->db->select('`idtbl_permissions`, `permission`, `guard_name`, `module`, `permission_type`');
        $this->db->from('tbl_permissions');
        $this->db->where('guard_name', 'web-erp');
        $this->db->order_by('module', 'ASC');

        $respond=$this->db->get();

        $permissionlistArray=array();
        foreach($respond->result() as $rowrespond){
            $grouped_permissions[$rowrespond->module][] = $rowrespond;
        }
        return $grouped_permissions;
    }
    public function Userrolesinsertupdate(){       
        $userID=$_SESSION['userid'];
        $rolename = $this->input->post('rolename');
        $permission = $this->input->post('permission');

        $recordOption=$this->input->post('recordOption');
        if(!empty($this->input->post('recordID'))){$recordID=$this->input->post('recordID');}

        $updatedatetime=date('Y-m-d H:i:s');

        if($recordOption==1){
            $this->db->trans_begin();   

            $data = array(
                'role'=>$rolename,
                'guard_name'=>'web-erp',
                'status'=>'1',
                'insertdatetime'=>$updatedatetime,
                'tbl_user_idtbl_user'=>$userID
            );

            $this->db->insert('tbl_roles', $data);
            $lastID = $this->db->insert_id();

            foreach($permission as $rowpermission){
                $data = array(
                    'tbl_roles_idtbl_roles'=>$lastID,
                    'tbl_permissions_idtbl_permissions'=>$rowpermission,
                );

                $this->db->insert('tbl_roles_has_tbl_permissions', $data);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                
                $actionObj=new stdClass();
                $actionObj->icon='fas fa-save';
                $actionObj->title='';
                $actionObj->message='Record Added Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='success';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Userroles');                
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Userroles');
            }
        }
        else{
            $this->db->trans_begin();   

            $data = array(
                'role'=>$rolename,
                'guard_name'=>'web-erp',
                'updateuser'=>$updatedatetime,                
                'updatedatetime'=>$updatedatetime               
            );
            $this->db->where('idtbl_roles', $recordID);
            $this->db->update('tbl_roles', $data);

            $this->db->where('tbl_roles_idtbl_roles', $recordID);
            $this->db->delete('tbl_roles_has_tbl_permissions');

            foreach($permission as $rowpermission){
                $data = array(
                    'tbl_roles_idtbl_roles'=>$recordID,
                    'tbl_permissions_idtbl_permissions'=>$rowpermission,
                );

                $this->db->insert('tbl_roles_has_tbl_permissions', $data);
            }   

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                
                $actionObj=new stdClass();
                $actionObj->icon='fas fa-save';
                $actionObj->title='';
                $actionObj->message='Record Update Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='primary';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Userroles');                
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Userroles');
            }
        }
    }
    public function Userrolesedit(){
        $recordID=$this->input->post('recordID');

        $this->db->select('*');
        $this->db->from('tbl_roles');
        $this->db->where('idtbl_roles', $recordID);
        $this->db->where('status', 1);

        $respond=$this->db->get();

        $permissionArray=array();
        $this->db->select('tbl_permissions_idtbl_permissions');
        $this->db->from('tbl_roles_has_tbl_permissions');
        $this->db->where('tbl_roles_idtbl_roles', $recordID);

        $respondpermission=$this->db->get();

        foreach($respondpermission->result() as $rowrespondpermission){
            array_push($permissionArray, $rowrespondpermission->tbl_permissions_idtbl_permissions);
        }

        $obj=new stdClass();
        $obj->id=$respond->row(0)->idtbl_roles;
        $obj->role=$respond->row(0)->role;
        $obj->guard_name=$respond->row(0)->guard_name;
        $obj->status=$respond->row(0)->status;
        $obj->permission=$permissionArray;

        echo json_encode($obj);
    }
    public function Userrolesstatus($x, $y){
        $userID=$_SESSION['userid'];
        $recordID=$x;
        $type=$y;
        $updatedatetime=date('Y-m-d H:i:s');

        if($type==1){
            $this->db->trans_begin();

            $data = array(
                'status' => '1',
                'updateuser'=> $userID, 
                'updatedatetime'=> $updatedatetime
            );

            $this->db->where('idtbl_roles', $recordID);
            $this->db->update('tbl_roles', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                
                $actionObj=new stdClass();
                $actionObj->icon='fas fa-check';
                $actionObj->title='';
                $actionObj->message='Record Activate Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='success';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Userroles');                
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Userroles');
            }
        }
        else if($type==2){
            $this->db->trans_begin();

            $data = array(
                'status' => '2',
                'updateuser'=> $userID, 
                'updatedatetime'=> $updatedatetime
            );

            $this->db->where('idtbl_roles', $recordID);
            $this->db->update('tbl_roles', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                
                $actionObj=new stdClass();
                $actionObj->icon='fas fa-times';
                $actionObj->title='';
                $actionObj->message='Record Deactivate Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='warning';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Userroles');                
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Userroles');
            }
        }
        else if($type==3){
            $this->db->trans_begin();

            $data = array(
                'status' => '3',
                'updateuser'=> $userID, 
                'updatedatetime'=> $updatedatetime
            );

            $this->db->where('idtbl_roles', $recordID);
            $this->db->update('tbl_roles', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                
                $actionObj=new stdClass();
                $actionObj->icon='fas fa-trash-alt';
                $actionObj->title='';
                $actionObj->message='Record Remove Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Userroles');                
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('User/Userroles');
            }
        }
    }
    public function Getuserroles(){
        $this->db->select('idtbl_roles, role');
        $this->db->from('tbl_roles');
        $this->db->where('status', 1);

        return $respond=$this->db->get();
    }   
}
