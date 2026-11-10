<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Dayend extends CI_Controller {
    public function index(){
		$this->load->model('Dayendinfo');
		$this->load->model('Commeninfo');
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$result['date']=$this->Dayendinfo->Cleandate($this->input->get('date'), date('Y-m-d'));
		$result['denoms']=$this->Dayendinfo->denoms;
		$this->load->view('dayend',$result);
	}
	public function Dayendpreview(){
		$this->load->model('Dayendinfo');
        $result=$this->Dayendinfo->Dayendpreview();
	}
	public function Cashierclose(){
		$this->load->model('Dayendinfo');
        $result=$this->Dayendinfo->Cashierclose();
	}
	public function Dayendsave(){
		$this->load->model('Dayendinfo');
        $result=$this->Dayendinfo->Dayendsave();
	}
}