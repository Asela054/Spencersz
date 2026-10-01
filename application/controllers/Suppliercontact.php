<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Suppliercontact extends CI_Controller {
	public function Suppliercontactinsertupdate(){
		$this->load->model('Suppliercontactinfo');
        $result=$this->Suppliercontactinfo->Suppliercontactinsertupdate();
	}
	public function Suppliercontactedit(){
		$this->load->model('Suppliercontactinfo');
        $result=$this->Suppliercontactinfo->Suppliercontactedit();
	}
	public function Suppliercontactstatus($x,$z,$y){
		$this->load->model('Suppliercontactinfo');
        $result=$this->Suppliercontactinfo->Suppliercontactstatus($x,$z,$y);
	}
	
}
