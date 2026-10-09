<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Taxcontrol extends CI_Controller {
	public function index(){
		$this->load->model('Taxcontrolinfo');
		$this->load->model('Commeninfo');
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$this->load->view('taxcontrol', $result);
	}
	public function Taxcontrolinsertupdate(){
		$this->load->model('Taxcontrolinfo');
        $result=$this->Taxcontrolinfo->Taxcontrolinsertupdate();
	}
	public function Taxcontroledit(){
		$this->load->model('Taxcontrolinfo');
        $result=$this->Taxcontrolinfo->Taxcontroledit();
	}
	public function Taxcontrolstatus($x, $y){
		$this->load->model('Taxcontrolinfo');
        $result=$this->Taxcontrolinfo->Taxcontrolstatus($x, $y);
	}
}
