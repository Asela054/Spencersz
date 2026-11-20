<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Pendingdelivery extends CI_Controller {
    public function index(){
		$this->load->model('Commeninfo');
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$this->load->view('pendingdelivery',$result);
	}
	public function Getpending(){
		$this->load->model('Invoiceinfo');
        $result=$this->Invoiceinfo->Getpending();
	}
	public function Getdeliveryserials(){
		$this->load->model('Invoiceinfo');
        $result=$this->Invoiceinfo->Getdeliveryserials();
	}
	public function Deliverpending(){
		$this->load->model('Invoiceinfo');
        $result=$this->Invoiceinfo->Deliverpending();
	}
	public function Deliverynote($invoice_id=0, $date=''){
		$this->load->model('Invoiceinfo');
		$data=$this->Invoiceinfo->Deliverynotedata((int)$invoice_id, $date);
		if(!$data){ show_404(); }
		$this->load->view('deliverynote', $data);
	}
}