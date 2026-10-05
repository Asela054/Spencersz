<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Unit extends CI_Controller {
    public function index(){
		$this->load->model('Unitinfo');
		$this->load->model('Commeninfo');
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$this->load->view('unit',$result);
	}
   
	public function Unitinsertupdate(){
		$this->load->model('Unitinfo');
        $result=$this->Unitinfo->Unitinsertupdate();
	}
	public function Unitedit(){
		$this->load->model('Unitinfo');
        $result=$this->Unitinfo->Unitedit();
	}
	public function Unitstatus($x, $y){
		$this->load->model('Unitinfo');
        $result=$this->Unitinfo->Unitstatus($x, $y);
	}
	
}
