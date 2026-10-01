<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Supplierbank extends CI_Controller {
	public function Supplierbankinsertupdate(){
		$this->load->model('Supplierbankinfo');
        $result=$this->Supplierbankinfo->Supplierbankinsertupdate();
	}
	public function Supplierbankedit(){
		$this->load->model('Supplierbankinfo');
        $result=$this->Supplierbankinfo->Supplierbankedit();
	}
	public function Supplierbankstatus($x,$z,$y){
		$this->load->model('Supplierbankinfo');
        $result=$this->Supplierbankinfo->Supplierbankstatus($x,$z,$y);
	}
	
}
