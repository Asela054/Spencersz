<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class User extends CI_Controller {
    public function Useraccount(){
		$this->load->model('Userinfo');
		$this->load->model('Commeninfo');
		$result['usertype']=$this->Userinfo->Usertype();
		$result['userroles']=$this->Userinfo->Getuserroles();
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$this->load->view('useraccount', $result);
	}
    public function Usertype(){
		$this->load->model('Userinfo');
		$this->load->model('Commeninfo');
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$this->load->view('usertype', $result);
	}
    public function Userprivilege(){
		$this->load->model('Userinfo');
		$this->load->model('Commeninfo');
		$result['useraccount']=$this->Userinfo->Useraccountmenu();
		$result['menulist']=$this->Userinfo->Menulist();
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$this->load->view('userprivilege', $result);
	}
    public function Userpermissions(){
		$this->load->model('Userinfo');
		$this->load->model('Commeninfo');
		$result['useraccount']=$this->Userinfo->Useraccountmenu();
		$result['menulist']=$this->Userinfo->Menulist();
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$this->load->view('userpermissions', $result);
	}
    public function Userroles(){
		$this->load->model('Userinfo');
		$this->load->model('Commeninfo');
		$result['useraccount']=$this->Userinfo->Useraccountmenu();
		$result['menulist']=$this->Userinfo->Menulist();
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$result['permissionlist']=$this->Userinfo->Getpermissionlist();
		$this->load->view('userroles', $result);
	}
	public function Useraccountinsertupdate(){
		$this->load->model('Userinfo');
        $result=$this->Userinfo->Useraccountinsertupdate();
	}
	public function Useraccountedit(){
		$this->load->model('Userinfo');
        $result=$this->Userinfo->Useraccountedit();
	}
	public function Useraccountstatus($x, $y){
		$this->load->model('Userinfo');
        $result=$this->Userinfo->Useraccountstatus($x, $y);
	}
	public function Usertypeedit(){
		$this->load->model('Userinfo');
        $result=$this->Userinfo->Usertypeedit();
	}
	public function Usertypeinsertupdate(){
		$this->load->model('Userinfo');
        $result=$this->Userinfo->Usertypeinsertupdate();
	}
	public function Usertypestatus($x, $y){
		$this->load->model('Userinfo');
        $result=$this->Userinfo->Usertypestatus($x, $y);
	}
	public function Userprivilegeinsertupdate(){
		$this->load->model('Userinfo');
        $result=$this->Userinfo->Userprivilegeinsertupdate();
	}
	public function Userprivilegeedit(){
		$this->load->model('Userinfo');
        $result=$this->Userinfo->Userprivilegeedit();
	}
	public function Userprivilegestatus($x, $y){
		$this->load->model('Userinfo');
        $result=$this->Userinfo->Userprivilegestatus($x, $y);
	}
	public function Userpermissionsinsertupdate(){
		$this->load->model('Userinfo');
		$result=$this->Userinfo->Userpermissionsinsertupdate();
	}
	public function Userpermissionsstatus($x, $y){
		$this->load->model('Userinfo');
        $result=$this->Userinfo->Userpermissionsstatus($x, $y);
	}
	public function Userrolesinsertupdate(){
		$this->load->model('Userinfo');
		$result=$this->Userinfo->Userrolesinsertupdate();
	}
	public function Userrolesedit(){
		$this->load->model('Userinfo');
		$result=$this->Userinfo->Userrolesedit();
	}
	public function Userrolesstatus($x, $y){
		$this->load->model('Userinfo');
		$result=$this->Userinfo->Userrolesstatus($x, $y);
	}
}