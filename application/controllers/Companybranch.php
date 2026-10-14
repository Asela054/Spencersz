<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Companybranch extends CI_Controller {
    public function index(){
		$this->load->model('Companybranchinfo');
		$this->load->model('Commeninfo');
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$result['companylist']=$this->Companybranchinfo->Getcompanylist();
		$this->load->view('companybranch',$result);
	}

	public function Companybranchinsertupdate(){
		$this->load->model('Companybranchinfo');
        $result=$this->Companybranchinfo->Companybranchinsertupdate();
	}
	public function Companybranchedit(){
		$this->load->model('Companybranchinfo');
        $result=$this->Companybranchinfo->Companybranchedit();
	}
	public function Companybranchstatus($x, $y){
		$this->load->model('Companybranchinfo');
        $result=$this->Companybranchinfo->Companybranchstatus($x, $y);
	}
}
