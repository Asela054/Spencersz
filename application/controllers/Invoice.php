<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Invoice extends CI_Controller {
    public function __construct(){
        parent::__construct();
        $this->load->model("Commeninfo");
        $this->load->model("Invoiceinfo");
    }
    public function index(){
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$this->load->view('invoice', $result);
	}
    public function SubmitNewCustomer(){
        $result=$this->Invoiceinfo->SubmitNewCustomer();
    }
    public function SearchCustomer(){
        $result=$this->Invoiceinfo->SearchCustomer();
    }
    public function Getproductdetails(){
       $result=$this->Invoiceinfo->Getproductdetails();
    }
    public function CheckGiftVoucher(){
       $result=$this->Invoiceinfo->CheckGiftVoucher();
    }
    public function Getpaymenttypelist(){
       $result=$this->Invoiceinfo->Getpaymenttypelist();
    }
    public function Insertinvoice(){
       $result=$this->Invoiceinfo->Insertinvoice();
    }
    public function Printinvoice($x){
       $result=$this->Invoiceinfo->Printinvoice($x);
    }
    public function ajax_credit_notes() {
        $cid = $this->input->post('customer_id');
        echo json_encode($this->Invoiceinfo->get_open_credit_notes($cid));
    }
}