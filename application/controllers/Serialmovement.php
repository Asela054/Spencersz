<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Serialmovement extends CI_Controller {
    public function index(){
		$this->load->model('Serialmovementinfo');
		$this->load->model('Commeninfo');
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$result['productlist']=$this->Serialmovementinfo->Getproducts();
		$this->load->view('serialmovement',$result);
	}
   
	public function Getsummary(){
		$this->load->model('Serialmovementinfo');
        $result=$this->Serialmovementinfo->Getsummary();
	}
	public function Getserialtimeline(){
		$this->load->model('Serialmovementinfo');
        $result=$this->Serialmovementinfo->Getserialtimeline();
	}
	
}