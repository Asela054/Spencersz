<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Product extends CI_Controller {
    public function index(){
		$this->load->model('Productinfo');
		$this->load->model('Commeninfo');
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$result['categorylist']=$this->Productinfo->Getcategorylist();
		$result['brandlist']=$this->Productinfo->Getbrandlist();
		$result['unitlist']=$this->Productinfo->Getunitlist();
		$this->load->view('product',$result);
	}
   
	public function Productinsertupdate(){
		$this->load->model('Productinfo');
        $result=$this->Productinfo->Productinsertupdate();
	}
	public function Productedit(){
		$this->load->model('Productinfo');
        $result=$this->Productinfo->Productedit();
	}
	public function Productstatus($x, $y){
		$this->load->model('Productinfo');
        $result=$this->Productinfo->Productstatus($x, $y);
	}
	
}
