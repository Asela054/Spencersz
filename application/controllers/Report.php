<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Colombo');

class Report extends CI_Controller {

	public function index(){
		$this->load->model('Reportinfo');
		$this->load->model('Commeninfo');
		$result['menuaccess']  = $this->Commeninfo->Getmenuprivilege();
		$result['getcategory'] = $this->Reportinfo->Categoryget();
		$result['getsupplier'] = $this->Reportinfo->Supplierget();
		$result['canviewall']  = $this->Reportinfo->canViewAllBranches();
		$this->load->view('report', $result);
	}

	public function stockReport() {
		$this->load->model('Reportinfo');

		// all branches only when requested AND the user holds allstockview-check
		$allBranches = ($this->input->post('all_branches') == '1')
			&& $this->Reportinfo->canViewAllBranches();

		$rows = $this->Reportinfo->stockReport($allBranches);

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($rows));
	}
}