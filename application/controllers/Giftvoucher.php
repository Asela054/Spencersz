<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Giftvoucher extends CI_Controller {
    public function index(){
		$this->load->model('Giftvoucherinfo');
		$this->load->model('Commeninfo');
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$this->load->view('giftvoucher',$result);
	}
   
	public function Giftvoucherinsertupdate(){
		$this->load->model('Giftvoucherinfo');
        $result=$this->Giftvoucherinfo->Giftvoucherinsertupdate();
	}
	public function Giftvoucheredit(){
		$this->load->model('Giftvoucherinfo');
        $result=$this->Giftvoucherinfo->Giftvoucheredit();
	}
	public function Giftvoucherstatus($x, $y){
		$this->load->model('Giftvoucherinfo');
        $result=$this->Giftvoucherinfo->Giftvoucherstatus($x, $y);
	}
	public function Giftvoucherlog(){
		$this->load->model('Giftvoucherinfo');
        $result=$this->Giftvoucherinfo->Giftvoucherlog();
	}
	
}