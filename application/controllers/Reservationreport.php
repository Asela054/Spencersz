<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Colombo');

class Reservationreport extends CI_Controller {

	public function index(){
		$this->load->model('Commeninfo');
		$result['menuaccess'] = $this->Commeninfo->Getmenuprivilege();
		$result['report']    = 'reservation';
		$result['branches']  = $this->db->select('idtbl_company_branch, branch')->where('status', 1)->order_by('branch')->get('tbl_company_branch')->result();
		$result['customers'] = $this->db->select('idtbl_customer, customer')->where('status', 1)->order_by('customer')->get('tbl_customer')->result();
		$result['products']  = $this->db->select('idtbl_product, product_code, product_name')->where('status', 1)->order_by('product_name')->get('tbl_product')->result();
		$this->load->view('reservationreports_view', $result);
	}
}