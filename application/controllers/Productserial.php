<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Productserial extends CI_Controller {
    public function index(){
		$this->load->model('Productserialinfo');
		$this->load->model('Commeninfo');
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$result['productlist']=$this->Productserialinfo->Getproductlist();
		$this->load->view('productserial',$result);
	}
   
	public function Getproductdetail(){
		$this->load->model('Productserialinfo');
        $result=$this->Productserialinfo->Getproductdetail();
	}
	
}