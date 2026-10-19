<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Colombo');

class Stockcard extends CI_Controller {

	public function index(){
		$this->load->model('Commeninfo');
		$result['menuaccess'] = $this->Commeninfo->Getmenuprivilege();
		$result['report']   = 'card';
		$result['branches'] = $this->db->select('idtbl_company_branch, branch, type')
			->where('status', 1)->order_by('type')->order_by('branch')
			->get('tbl_company_branch')->result();
		$result['products'] = $this->db->select('idtbl_product, product_code, product_name')
			->where('status', 1)->order_by('product_name')->get('tbl_product')->result();
		$this->load->view('stockreports_view', $result);
	}
}
