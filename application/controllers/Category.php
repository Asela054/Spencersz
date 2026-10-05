<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Category extends CI_Controller {
    public function index(){
		$this->load->model('Categoryinfo');
		$this->load->model('Commeninfo');
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$this->load->view('category',$result);
	}
   
	public function Categoryinsertupdate(){
		$this->load->model('Categoryinfo');
        $result=$this->Categoryinfo->Categoryinsertupdate();
	}
	public function Categoryedit(){
		$this->load->model('Categoryinfo');
        $result=$this->Categoryinfo->Categoryedit();
	}
	public function Categorystatus($x, $y){
		$this->load->model('Categoryinfo');
        $result=$this->Categoryinfo->Categorystatus($x, $y);
	}
	
}
