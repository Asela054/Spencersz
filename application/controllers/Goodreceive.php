<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Goodreceive extends CI_Controller {
    public function index(){
        $this->load->model('Commeninfo');
        $this->load->model('Goodreceiveinfo');
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$result['companylist']=$this->Goodreceiveinfo->Getcompany();
		$result['locationlist']=$this->Goodreceiveinfo->Getlocation();
		$result['warehouselist']=$this->Goodreceiveinfo->Getwarehouse();
		$result['branchlist']=$this->Goodreceiveinfo->Getcompanybranch();
		$result['ordertypelist']=$this->Goodreceiveinfo->Getordertype();
		$result['measurelist']=$this->Goodreceiveinfo->Getmeasuretype();
		$this->load->view('goodreceive', $result);
	}
    public function Goodreceiveinsertupdate(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Goodreceiveinsertupdate();
	}
    public function Goodreceivestatus($x, $y, $z){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Goodreceivestatus($x, $y, $z);
	}
	public function Approvestatus(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Approvestatus();
	}
	public function Goodreceivecheckstatus(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Goodreceivecheckstatus();
	}
	public function Goodreceiverejectstatus(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Goodreceiverejectstatus();
	}
    public function Goodreceiveedit(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Goodreceiveedit();
	}
    public function Getproductaccosupplier(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getproductaccosupplier();
	}
	public function Getproductformachine(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getproductformachine();
	}
	public function Getproductforsparepart(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getproductforsparepart();
	}
    public function Goodreceiveview(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Goodreceiveview();
	}
    public function Getsupplieraccoporder(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getsupplieraccoporder();
	}
	public function Getsupplier(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getsupplier();
	}
	public function Getcompanyaccoporder(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getcompanyaccoporder();
	}
	public function Getbranchaccoporder(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getbranchaccoporder();
	}
    public function Getproductaccoporder(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getproductaccoporder();
	}
    public function Getproductinfoaccoproduct(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getproductinfoaccoproduct();
	}
	public function Getproductinfoamachine(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getproductinfoamachine();
	}
	public function Getproductinfosparepart(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getproductinfosparepart();
	}
    public function Getexpdateaccoquater(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getexpdateaccoquater();
	}
    public function Getbatchnoaccosupplier(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getbatchnoaccosupplier();
	}
	public function GetBatchNoFromMachineAndMaterialInfo(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->GetBatchNoFromMachineAndMaterialInfo();
	}
    public function Getpordertpeaccoporder(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getpordertpeaccoporder();
	}
	public function Getgoodreceiveid(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getgoodreceiveid();
	}
	public function Costinsertupdate(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Costinsertupdate();
	}
	public function Getmateriallistaccogrn(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getmateriallistaccogrn();
	}
	public function Getporderaccsupllier(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getporderaccsupllier();
	}
	public function Getvatpresentage(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getvatpresentage();
	}
	public function Getservicematerials(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getservicematerials();
	}
	public function Getservicematerialsprices(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getservicematerialsprices();
	}
	public function pdfgrnget($x) {
        $this->load->model('PdfGRNinfo');
        $this->PdfGRNinfo->pdfgrnget($x);
    }
	public function getPorderList() {
		$this->load->model('Goodreceiveinfo');

		$searchTerm = $this->input->post('searchTerm');
		$result = $this->Goodreceiveinfo->Getporder($searchTerm);

		$data = array();

		if ($result && $result->num_rows() > 0) {
			foreach ($result->result() as $row) {
				$data[] = array(
					"id" => $row->idtbl_print_porder,
					"text" => $row->porder_no
				);
			}
		}
		echo json_encode($data);
	}
	public function Getvattype(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getvattype();
	}
	public function Goodreceivevattype(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Goodreceivevattype();
	}
	public function Getporderdetails(){
		$this->load->model('Goodreceiveinfo');
        $result=$this->Goodreceiveinfo->Getporderdetails();
	}
	public function Geteditgrn(){
		$this->load->model('Goodreceiveinfo');
		$result=$this->Goodreceiveinfo->Geteditgrn();
	}
	public function Goodreceiveeditupdate(){
		$this->load->model('Goodreceiveinfo');
		$result=$this->Goodreceiveinfo->Goodreceiveeditupdate();
	}
}