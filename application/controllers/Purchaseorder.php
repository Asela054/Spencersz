<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Purchaseorder extends CI_Controller {
    public function index(){
        $this->load->model('Commeninfo');
        $this->load->model('Purchaseorderinfo');
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$result['companylist']=$this->Purchaseorderinfo->Getcompany();
		$result['supplierlist']=$this->Purchaseorderinfo->Getsupplier();
		$result['ordertypelist']=$this->Purchaseorderinfo->Getordertype();
		$result['servicetypelist']=$this->Purchaseorderinfo->Getservicetype();
		$result['measurelist']=$this->Purchaseorderinfo->Getmeasuretype();
		$result['porderlist']=$this->Purchaseorderinfo->Getporder();
		$result['contactpersonlist'] = $this->Purchaseorderinfo->Getcontactperson();
		$this->load->view('purchaseorder', $result);
	}
    public function Purchaseorderinsertupdate(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Purchaseorderinsertupdate();
	}
    public function Purchaseorderstatus(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Purchaseorderstatus();
	}
	public function Purchaseordercheckstatus(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Purchaseordercheckstatus();
	}
	public function POmanualconfirm($x){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->POmanualconfirm($x);
	}
	public function Purchaseorderrejectstatus(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Purchaseorderrejectstatus();
	}
    public function Purchaseorderedit(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Purchaseorderedit();
	}
    public function Getproductaccosupplier(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Getproductaccosupplier();
	}
	public function Getpordertpeaccoporderrequest(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Getpordertpeaccoporderrequest();
	}
	public function Getmesuretpeaccorproduct(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Getmesuretpeaccorproduct();
	}

	public function Getservicetyperequest(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Getservicetyperequest();
	}
	public function Getproductforvehicle(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Getproductforvehicle();
	}
	public function Getproductformachine(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Getproductformachine();
	}
	public function Getproductforsparepart(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Getproductforsparepart();
	}
	public function Getproductaccoporder(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Getproductaccoporder();
	}
	public function Getsupplieraccoporderreq(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Getsupplieraccoporderreq();
	}
	public function Getporderreqdetails(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Getporderreqdetails();
	}
	public function Getproductinfoaccoproduct(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Getproductinfoaccoproduct();
	}
	public function Getproductinfoamachine(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Getproductinfoamachine();
	}
	public function Getproductinfosparepart(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Getproductinfosparepart();
	}
		public function Getproductinfoservice(){
			$this->load->model('Purchaseorderinfo');
			$result=$this->Purchaseorderinfo->Getproductinfoservice();
		}
    public function Purchaseorderview(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Purchaseorderview();
	}
	public function porderviewheader(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->porderviewheader();
	}
	public function Getvatpresentage(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Getvatpresentage();
	}
	public function Printinvoice($x){
		$this->load->model('InvoicePrintinfo');
        $result=$this->InvoicePrintinfo->Printinvoice($x);
	}
	public function GetProductList() {
        $this->load->model('Purchaseorderinfo');
        $products = $this->Purchaseorderinfo->getProductsByType();
    }
	public function Getpiecesforqty(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Getpiecesforqty();
	}
	public function Purchaseorderupdate(){
		$this->load->model('Purchaseorderinfo');
        $result=$this->Purchaseorderinfo->Purchaseorderupdate();
	}
	public function Getsupplierlist(){
		$searchTerm=$this->input->post('searchTerm');
        $result=SearchSupplierList($searchTerm);
	}
}
