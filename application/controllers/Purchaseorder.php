<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Purchaseorder extends CI_Controller {

    public function index(){
        $this->load->model('Commeninfo');
        $this->load->model('Purchaseorderinfo');
        $result['menuaccess']   = $this->Commeninfo->Getmenuprivilege();
        $result['supplierlist'] = $this->Purchaseorderinfo->Getsupplier();
        $result['porderlist']   = $this->Purchaseorderinfo->Getporder();
        $this->load->view('purchaseorder', $result);
    }

    public function Purchaseorderinsertupdate(){
        $this->load->model('Purchaseorderinfo');
        $this->Purchaseorderinfo->Purchaseorderinsertupdate();
    }

    public function Purchaseorderupdate(){
        $this->load->model('Purchaseorderinfo');
        $this->Purchaseorderinfo->Purchaseorderupdate();
    }

    public function Purchaseorderstatus(){
        $this->load->model('Purchaseorderinfo');
        $this->Purchaseorderinfo->Purchaseorderstatus();
    }

    public function Purchaseordercheckstatus(){
        $this->load->model('Purchaseorderinfo');
        $this->Purchaseorderinfo->Purchaseordercheckstatus();
    }

    public function POmanualconfirm($x){
        $this->load->model('Purchaseorderinfo');
        $this->Purchaseorderinfo->POmanualconfirm($x);
    }

    public function Purchaseorderedit(){
        $this->load->model('Purchaseorderinfo');
        $this->Purchaseorderinfo->Purchaseorderedit();
    }

    public function Purchaseorderview(){
        $this->load->model('Purchaseorderinfo');
        $this->Purchaseorderinfo->Purchaseorderview();
    }

    public function porderviewheader(){
        $this->load->model('Purchaseorderinfo');
        $this->Purchaseorderinfo->porderviewheader();
    }

    public function Getporderreqdetails(){
        $this->load->model('Purchaseorderinfo');
        $this->Purchaseorderinfo->Getporderreqdetails();
    }

    public function Getproductprice(){
        $this->load->model('Purchaseorderinfo');
        $this->Purchaseorderinfo->Getproductprice();
    }

    public function GetProductList(){
        $this->load->model('Purchaseorderinfo');
        $term = trim((string)$this->input->post('searchTerm'));
        echo json_encode($this->Purchaseorderinfo->searchProducts($term));
    }

    public function Getsupplierlist(){
        $this->load->model('Purchaseorderinfo');
        $term = trim((string)$this->input->post('searchTerm'));
        echo json_encode($this->Purchaseorderinfo->searchSuppliers($term));
    }

    public function Printinvoice($x){
        $this->load->model('PorderPrintinfo');
        $this->PorderPrintinfo->Printinvoice($x);
    }
}