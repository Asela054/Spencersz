<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Newpurchaserequest extends CI_Controller {

    public function index(){
        $this->load->model('Commeninfo');
        $result['menuaccess'] = $this->Commeninfo->Getmenuprivilege();
        $this->load->view('newpurchaserequest', $result);
    }

    public function Newpurchaserequestinsertupdate(){
        $this->load->model('Newpurchaserequestinfo');
        $this->Newpurchaserequestinfo->Newpurchaserequestinsertupdate();
    }

    public function Newpurchaserequeststatus(){
        $this->load->model('Newpurchaserequestinfo');
        $this->Newpurchaserequestinfo->Newpurchaserequeststatus();
    }

    public function Newpurchaserequestcheckstatus(){
        $this->load->model('Newpurchaserequestinfo');
        $this->Newpurchaserequestinfo->Newpurchaserequestcheckstatus();
    }

    public function Purchaseorderview(){
        $this->load->model('Newpurchaserequestinfo');
        $this->Newpurchaserequestinfo->Purchaseorderview();
    }

    public function porderviewheader(){
        $this->load->model('Newpurchaserequestinfo');
        $this->Newpurchaserequestinfo->porderviewheader();
    }

    public function Printinvoice($x){
        $this->load->model('PorderreqPrintinfo');
        $this->PorderreqPrintinfo->Printinvoice($x);
    }

    public function Getstockqty(){
        $this->load->model('Newpurchaserequestinfo');
        $this->Newpurchaserequestinfo->Getstockqty();
    }
	
    public function GetProducts(){
        $this->load->model('Newpurchaserequestinfo');
        $query = trim((string)$this->input->post('query'));
        echo json_encode($this->Newpurchaserequestinfo->searchProducts($query));
    }
}