<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Cashbalance extends CI_Controller {
    public function index(){
		$this->load->model('Dayendinfo');
		$this->load->model('Commeninfo');
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$result['branches']=$this->Dayendinfo->Getbranches();
		$this->load->view('cashbalance',$result);
	}
	public function Cashbalancedata(){
		$this->load->model('Dayendinfo');
        $result=$this->Dayendinfo->Cashbalancedata();
	}
}