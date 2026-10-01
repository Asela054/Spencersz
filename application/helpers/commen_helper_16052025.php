<?php 
function CompanyList(){
    $CI = get_instance();
    $CI->db->select('`idtbl_company`, `company`, `code`');
    $CI->db->from('tbl_company');
    $CI->db->where('status', 1);

    return $CI->db->get()->result();
}
function CompanyBranchList($companyid){
    $CI = get_instance();
    $CI->db->where('status', 1);
    $CI->db->where('tbl_company_idtbl_company', $companyid);
    $CI->db->select('idtbl_company_branch, branch, code, tbl_company_idtbl_company');
    $CI->db->from('tbl_company_branch');
    echo json_encode($CI->db->get()->result());
}
function SearchSupplierList($searchTerm){
    $companyid=$_SESSION['company_id'];
    $branchid=$_SESSION['branch_id'];

    if(!isset($searchTerm)){
        $CI = get_instance();
        $CI->db->where('status', 1);
        $CI->db->where('tbl_company_idtbl_company', $companyid);
        $CI->db->where('tbl_company_branch_idtbl_company_branch', $branchid);
        $CI->db->select('idtbl_supplier, suppliername, telephone_no');
        $CI->db->from('tbl_supplier');
        $CI->db->limit(5);
        $respond=$CI->db->get();
    }
    else{            
        if(!empty($searchTerm)){
            $CI = get_instance();
            $CI->db->where('status', 1);
            $CI->db->where('tbl_company_idtbl_company', $companyid);
            $CI->db->where('tbl_company_branch_idtbl_company_branch', $branchid);
            $CI->db->select('idtbl_supplier, suppliername, telephone_no');
            $CI->db->from('tbl_supplier');
            $CI->db->like('suppliername', $searchTerm, 'both'); 
            $respond=$CI->db->get();
        }
        else{
            $CI = get_instance();
            $CI->db->where('status', 1);
            $CI->db->where('tbl_company_idtbl_company', $companyid);
            $CI->db->where('tbl_company_branch_idtbl_company_branch', $branchid);
            $CI->db->select('idtbl_supplier, suppliername, telephone_no');
            $CI->db->from('tbl_supplier');
            $CI->db->limit(5);
            $respond=$CI->db->get();             
        }
    }
    
    $data=array();
    
    foreach ($respond->result() as $row) {
        $data[]=array("id"=>$row->idtbl_supplier, "text"=>$row->suppliername);
    }   
    echo json_encode($data);
}
function SearchCustomerList($searchTerm){
    $companyid=$_SESSION['company_id'];
    $branchid=$_SESSION['branch_id'];

    if(!isset($searchTerm)){
        $CI = get_instance();
        $CI->db->where('status', 1);
        $CI->db->where('tbl_company_idtbl_company', $companyid);
        $CI->db->where('tbl_company_branch_idtbl_company_branch', $branchid);
        $CI->db->select('idtbl_customer, customer');
        $CI->db->from('tbl_customer');
        $CI->db->limit(5);
        $respond=$CI->db->get();
    }
    else{            
        if(!empty($searchTerm)){
            $CI = get_instance();
            $CI->db->where('status', 1);
            $CI->db->where('tbl_company_idtbl_company', $companyid);
            $CI->db->where('tbl_company_branch_idtbl_company_branch', $branchid);
            $CI->db->select('idtbl_customer, customer');
            $CI->db->from('tbl_customer');
            $CI->db->like('customer', $searchTerm, 'both'); 
            $respond=$CI->db->get();
        }
        else{
            $CI = get_instance();
            $CI->db->where('status', 1);
            $CI->db->where('tbl_company_idtbl_company', $companyid);
            $CI->db->where('tbl_company_branch_idtbl_company_branch', $branchid);
            $CI->db->select('idtbl_customer, customer');
            $CI->db->from('tbl_customer');
            $CI->db->limit(5);
            $respond=$CI->db->get();             
        }
    }
    
    $data=array();
    
    foreach ($respond->result() as $row) {
        $data[]=array("id"=>$row->idtbl_customer, "text"=>$row->customer);
    }   
    echo json_encode($data);
}
function GetMaterialList($searchTerm, $searchCategory) {
    if(!isset($searchTerm)){
        $CI = get_instance();
        $CI->db->where('status', 1);
        if(!empty($searchCategory)){$CI->db->where_in('tbl_material_type_idtbl_material_type', $searchCategory);}
        $CI->db->select('`idtbl_print_material_info`, `materialname`');
        $CI->db->from('tbl_print_material_info');
        $CI->db->limit(5);
        $respond=$CI->db->get();
    }
    else{            
        if(!empty($searchTerm)){
            $CI = get_instance();
            $CI->db->where('status', 1);
            if(!empty($searchCategory)){$CI->db->where_in('tbl_material_type_idtbl_material_type', $searchCategory);}
            $CI->db->select('`idtbl_print_material_info`, `materialname`');
            $CI->db->from('tbl_print_material_info');
            $CI->db->like('materialname', $searchTerm, 'both'); 
            $respond=$CI->db->get();
        }
        else{
            $CI = get_instance();
            $CI->db->where('status', 1);
            if(!empty($searchCategory)){$CI->db->where_in('tbl_material_type_idtbl_material_type', $searchCategory);}
            $CI->db->select('`idtbl_print_material_info`, `materialname`');
            $CI->db->from('tbl_print_material_info');
            $CI->db->limit(5);
            $respond=$CI->db->get();             
        }
    }
    
    $data=array();
    
    foreach ($respond->result() as $row) {
        $data[]=array("id"=>$row->idtbl_print_material_info, "text"=>$row->materialname);
    }   
    echo json_encode($data);
}
function get_all_accounts($searchTerm, $companyid, $branchid){
    $CI = get_instance();
    if(!empty($CI->input->post('accountcategory'))){$accountcategory=$CI->input->post('accountcategory');}else{$accountcategory='';}

    if(!isset($searchTerm)){
        $CI = get_instance();
        $sql="SELECT `tbl_account`.`idtbl_account` AS `accountid`, `tbl_account`.`accountno`, `tbl_account`.`accountname`, '1' AS `acctype` FROM `tbl_account` LEFT JOIN `tbl_account_allocation` ON `tbl_account_allocation`.`tbl_account_idtbl_account`=`tbl_account`.`idtbl_account` LEFT JOIN `tbl_account_detail` ON `tbl_account_detail`.`tbl_account_idtbl_account`=`tbl_account`.`idtbl_account` WHERE `tbl_account`.`status`=? AND `tbl_account_allocation`.`status`=? AND `tbl_account_allocation`.`companybank`=? AND `tbl_account_allocation`.`branchcompanybank`=? AND `tbl_account_detail`.`tbl_account_idtbl_account` IS NULL";
        if(!empty($accountcategory)){$sql.=" AND `tbl_account`.`tbl_account_category_idtbl_account_category`='$accountcategory'";}
        $sql.=" UNION ALL
        SELECT `tbl_account_detail`.`idtbl_account_detail` AS `accountid`, `tbl_account_detail`.`accountno`, `tbl_account_detail`.`accountname`, '2' AS `acctype` FROM `tbl_account_detail`"; 
        if(!empty($accountcategory)){$sql.=" LEFT JOIN `tbl_account` ON `tbl_account`.`idtbl_account`=`tbl_account_detail`.`tbl_account_idtbl_account`";}
        $sql.=" LEFT JOIN `tbl_account_allocation` ON `tbl_account_allocation`.`tbl_account_detail_idtbl_account_detail`=`tbl_account_detail`.`idtbl_account_detail` WHERE `tbl_account_detail`.`status`=? AND `tbl_account_allocation`.`status`=? AND `tbl_account_allocation`.`companybank`=? AND `tbl_account_allocation`.`branchcompanybank`=? ";
        if(!empty($accountcategory)){$sql.=" AND `tbl_account`.`tbl_account_category_idtbl_account_category`='$accountcategory'";}
        $sql.="LIMIT 5";
        $respond=$CI->db->query($sql, array(1, 1, $companyid, $branchid, 1, 1, $companyid, $branchid));
    }
    else{            
        if(!empty($searchTerm)){
            $CI = get_instance();
            $sql="SELECT `tbl_account`.`idtbl_account` AS `accountid`, `tbl_account`.`accountno`, `tbl_account`.`accountname`, '1' AS `acctype` FROM `tbl_account` LEFT JOIN `tbl_account_allocation` ON `tbl_account_allocation`.`tbl_account_idtbl_account`=`tbl_account`.`idtbl_account` LEFT JOIN `tbl_account_detail` ON `tbl_account_detail`.`tbl_account_idtbl_account`=`tbl_account`.`idtbl_account` WHERE (`tbl_account`.`accountno` LIKE '%$searchTerm%' OR `tbl_account`.`accountname` LIKE '$searchTerm%') AND `tbl_account`.`status`=? AND `tbl_account_allocation`.`status`=? AND `tbl_account_allocation`.`companybank`=? AND `tbl_account_allocation`.`branchcompanybank`=? AND `tbl_account_detail`.`tbl_account_idtbl_account` IS NULL";
            if(!empty($accountcategory)){$sql.=" AND `tbl_account`.`tbl_account_category_idtbl_account_category`='$accountcategory'";}
            $sql.=" UNION ALL
            SELECT `tbl_account_detail`.`idtbl_account_detail` AS `accountid`, `tbl_account_detail`.`accountno`, `tbl_account_detail`.`accountname`, '2' AS `acctype` FROM `tbl_account_detail`"; 
            if(!empty($accountcategory)){$sql.=" LEFT JOIN `tbl_account` ON `tbl_account`.`idtbl_account`=`tbl_account_detail`.`tbl_account_idtbl_account`";}
            $sql.=" LEFT JOIN `tbl_account_allocation` ON `tbl_account_allocation`.`tbl_account_detail_idtbl_account_detail`=`tbl_account_detail`.`idtbl_account_detail` WHERE (`tbl_account_detail`.`accountno` LIKE '$searchTerm%' OR `tbl_account_detail`.`accountname` LIKE '%$searchTerm%') AND `tbl_account_detail`.`status`=? AND `tbl_account_allocation`.`status`=? AND `tbl_account_allocation`.`companybank`=? AND `tbl_account_allocation`.`branchcompanybank`=?";
            if(!empty($accountcategory)){$sql.=" AND `tbl_account`.`tbl_account_category_idtbl_account_category`='$accountcategory'";}
            $respond=$CI->db->query($sql, array(1, 1, $companyid, $branchid, 1, 1, $companyid, $branchid));
        }
        else{
            $CI = get_instance();
            $sql="SELECT `tbl_account`.`idtbl_account` AS `accountid`, `tbl_account`.`accountno`, `tbl_account`.`accountname`, '1' AS `acctype` FROM `tbl_account` LEFT JOIN `tbl_account_allocation` ON `tbl_account_allocation`.`tbl_account_idtbl_account`=`tbl_account`.`idtbl_account` LEFT JOIN `tbl_account_detail` ON `tbl_account_detail`.`tbl_account_idtbl_account`=`tbl_account`.`idtbl_account` WHERE `tbl_account`.`status`=? AND `tbl_account_allocation`.`status`=? AND `tbl_account_allocation`.`companybank`=? AND `tbl_account_allocation`.`branchcompanybank`=? AND `tbl_account_detail`.`tbl_account_idtbl_account` IS NULL";
            if(!empty($accountcategory)){$sql.=" AND `tbl_account`.`tbl_account_category_idtbl_account_category`='$accountcategory'";}
            $sql.=" UNION ALL
            SELECT `tbl_account_detail`.`idtbl_account_detail` AS `accountid`, `tbl_account_detail`.`accountno`, `tbl_account_detail`.`accountname`, '2' AS `acctype` FROM `tbl_account_detail`"; 
            if(!empty($accountcategory)){$sql.=" LEFT JOIN `tbl_account` ON `tbl_account`.`idtbl_account`=`tbl_account_detail`.`tbl_account_idtbl_account`";}
            $sql.=" LEFT JOIN `tbl_account_allocation` ON `tbl_account_allocation`.`tbl_account_detail_idtbl_account_detail`=`tbl_account_detail`.`idtbl_account_detail` WHERE `tbl_account_detail`.`status`=? AND `tbl_account_allocation`.`status`=? AND `tbl_account_allocation`.`companybank`=? AND `tbl_account_allocation`.`branchcompanybank`=? ";
            if(!empty($accountcategory)){$sql.=" AND `tbl_account`.`tbl_account_category_idtbl_account_category`='$accountcategory'";}
            $sql.="LIMIT 5";
            $respond=$CI->db->query($sql, array(1, 1, $companyid, $branchid, 1, 1, $companyid, $branchid));
        }
    }

    // echo json_encode($respond->result()); 

    $data=array();
    
    foreach ($respond->result() as $row) {
        $data[]=array("id"=>$row->accountid, "text"=>$row->accountname.' - '.$row->accountno, "acctype" => $row->acctype);
    }
    
    echo json_encode($data);
}