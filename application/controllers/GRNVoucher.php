<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class GRNVoucher extends CI_Controller {
    public function index(){
        $this->load->model('Commeninfo');
        $this->load->model('GRNVoucherinfo');
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$result['supplierlist']=$this->GRNVoucherinfo->Getsupplierlist();
		$result['costlist']=$this->GRNVoucherinfo->Getcostlisttype();
		$this->load->view('grnvoucher', $result);
	}
    public function Insertgrnvoucher(){
		$this->load->model('GRNVoucherinfo');
        $result=$this->GRNVoucherinfo->Insertgrnvoucher();
	}
    public function Getgrnaccsupllier(){
		$this->load->model('GRNVoucherinfo');
        $result=$this->GRNVoucherinfo->Getgrnaccsupllier();
	}
    public function Goodreceivevoucherview(){
		$this->load->model('GRNVoucherinfo');
        $result=$this->GRNVoucherinfo->Goodreceivevoucherview();
	}
    public function Goodreceivevoucherstatus($x, $y){
		$this->load->model('GRNVoucherinfo');
        $result=$this->GRNVoucherinfo->Goodreceivevoucherstatus($x, $y);
	}
    public function GRNVoucherapprove(){
		$this->load->model('GRNVoucherinfo');
        $result=$this->GRNVoucherinfo->GRNVoucherapprove();
	}
    public function get_grn_details() {
        $grnno = $this->input->post('grnno');
        $this->load->model('GRNVoucherinfo');
        $grn_details = $this->GRNVoucherinfo->get_grn_details($grnno);
        if (!empty($grn_details)) {
            $response = array(
                'status' => 'success',
                'data' => $grn_details
            );
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'No data found'
            );
        }
        
        echo json_encode($response);
    }
    public function VoucherPdf($x){
        $this->load->model('GRNVoucherinfo');
        $result=$this->GRNVoucherinfo->VoucherPdf($x);
    }
    public function GRNVouchercheckstatus(){
        $this->load->model('GRNVoucherinfo');
        $result=$this->GRNVoucherinfo->GRNVouchercheckstatus();
    }
    public function Getsupplierlist(){
        $searchTerm=$this->input->post('searchTerm');
        $result=SearchSupplierListGRNVoucher($searchTerm);
    }
}
