<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Brand extends CI_Controller {
    public function index(){
		$this->load->model('Brandinfo');
		$this->load->model('Commeninfo');
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$this->load->view('brand',$result);
	}
   
	public function Brandinsertupdate(){
		$this->load->model('Brandinfo');
        $result=$this->Brandinfo->Brandinsertupdate();
	}
	public function Brandedit(){
		$this->load->model('Brandinfo');
        $result=$this->Brandinfo->Brandedit();
	}
	public function Brandstatus($x, $y){
		$this->load->model('Brandinfo');
        $result=$this->Brandinfo->Brandstatus($x, $y);
	}
	
}
