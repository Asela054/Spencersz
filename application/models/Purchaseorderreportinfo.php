<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Purchaseorderreportinfo extends CI_Model{


public function Supplierget(){

    $company_id = $this->session->userdata('company_id');

    $this->db->select('idtbl_supplier,suppliername');
    $this->db->where('status',1);
    $this->db->where('tbl_company_idtbl_company', $company_id);
    return $this->db->get('tbl_supplier');

}


}