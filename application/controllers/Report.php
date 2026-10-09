<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Colombo');

class Report extends CI_Controller {

	public function index(){
		$this->load->model('Reportinfo');
		$this->load->model('Commeninfo');
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$result['getcategory']=$this->Reportinfo->Categoryget();
		$this->load->view('report', $result);
	}

	public function stockReport() {
		$this->load->model('Reportinfo');
		$result=$this->Reportinfo->stockReport();
	}
}