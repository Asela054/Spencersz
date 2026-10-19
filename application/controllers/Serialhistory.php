<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Colombo');

class Serialhistory extends CI_Controller {

	public function index(){
		$this->load->model('Commeninfo');
		$result['menuaccess'] = $this->Commeninfo->Getmenuprivilege();
		$result['report']   = 'history';
		$result['branches'] = $this->db->select('idtbl_company_branch, branch, type')
			->where('status', 1)->order_by('type')->order_by('branch')
			->get('tbl_company_branch')->result();
		$this->load->view('serialreports_view', $result);
	}
}
