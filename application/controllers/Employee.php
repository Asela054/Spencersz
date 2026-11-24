<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Employee extends CI_Controller {
    public function index(){
		$this->load->model('Employeeinfo');
		$this->load->model('Commeninfo');
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$result['branches']=$this->Employeeinfo->Getbranches();
		$result['departments']=$this->Employeeinfo->Getdistinct('department');
		$result['designations']=$this->Employeeinfo->Getdistinct('designation');
		$this->load->view('employee',$result);
	}
   
	public function Employeeinsertupdate(){
		$this->load->model('Employeeinfo');
        $result=$this->Employeeinfo->Employeeinsertupdate();
	}
	public function Employeeedit(){
		$this->load->model('Employeeinfo');
        $result=$this->Employeeinfo->Employeeedit();
	}
	public function Employeestatus($x, $y){
		$this->load->model('Employeeinfo');
        $result=$this->Employeeinfo->Employeestatus($x, $y);
	}
	
}