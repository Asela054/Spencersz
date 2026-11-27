<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Colombo');

class Allstockview extends CI_Controller {

	public function index(){
		$this->load->model('Commeninfo');
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$result['canviewall']=$this->canViewAllBranches();
		$this->load->view('advancedStockSearch', $result);
	}

	private function canViewAllBranches(){
		$uid = (int)$_SESSION['userid']; 
		$q = $this->db->query(
			"SELECT 1 FROM tbl_user_has_tbl_roles ur
			JOIN tbl_roles_has_tbl_permissions rp ON rp.tbl_roles_idtbl_roles = ur.tbl_roles_idtbl_roles
			JOIN tbl_permissions p ON p.idtbl_permissions = rp.tbl_permissions_idtbl_permissions
			WHERE ur.tbl_user_idtbl_user = ? AND p.permission = 'allstockview-check'
			AND p.guard_name = 'web-erp' LIMIT 1", array($uid));
		return $q->num_rows() > 0;
	}
}