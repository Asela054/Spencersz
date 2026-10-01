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
        $CI->db->where('tbl_supplier.status', 1);
        $CI->db->where('tbl_supplier.tbl_company_idtbl_company', $companyid);
        $CI->db->where('tbl_supplier.tbl_company_branch_idtbl_company_branch', $branchid);
        $CI->db->where('tbl_supplier_type.idtbl_supplier_type !=', 5);
        $CI->db->select('tbl_supplier.idtbl_supplier, tbl_supplier.suppliername, tbl_supplier.telephone_no');
        $CI->db->from('tbl_supplier');
        $CI->db->join('tbl_supplier_type', 'tbl_supplier_type.idtbl_supplier_type = tbl_supplier.tbl_supplier_type_idtbl_supplier_type');
        $CI->db->limit(5);
        $respond=$CI->db->get();
    }
    else{            
        if(!empty($searchTerm)){
            $CI = get_instance();
            $CI->db->where('tbl_supplier.status', 1);
            $CI->db->where('tbl_supplier.tbl_company_idtbl_company', $companyid);
            $CI->db->where('tbl_supplier.tbl_company_branch_idtbl_company_branch', $branchid);
            $CI->db->where('tbl_supplier_type.idtbl_supplier_type !=', 5);
            $CI->db->select('tbl_supplier.idtbl_supplier, tbl_supplier.suppliername, tbl_supplier.telephone_no');
            $CI->db->from('tbl_supplier');
            $CI->db->join('tbl_supplier_type', 'tbl_supplier_type.idtbl_supplier_type = tbl_supplier.tbl_supplier_type_idtbl_supplier_type');
            $CI->db->like('tbl_supplier.suppliername', $searchTerm, 'both'); 
            $respond=$CI->db->get();
        }
        else{
            $CI = get_instance();
            $CI->db->where('tbl_supplier.status', 1);
            $CI->db->where('tbl_supplier.tbl_company_idtbl_company', $companyid);
            $CI->db->where('tbl_supplier.tbl_company_branch_idtbl_company_branch', $branchid);
            $CI->db->where('tbl_supplier_type.idtbl_supplier_type !=', 5);
            $CI->db->select('tbl_supplier.idtbl_supplier, tbl_supplier.suppliername, tbl_supplier.telephone_no');
            $CI->db->from('tbl_supplier');
            $CI->db->join('tbl_supplier_type', 'tbl_supplier_type.idtbl_supplier_type = tbl_supplier.tbl_supplier_type_idtbl_supplier_type');
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
    $companyid=$_SESSION['company_id'];
    $branchid=$_SESSION['branch_id'];
    
    if(!isset($searchTerm)){
        $CI = get_instance();
        $CI->db->where('status', 1);
        $CI->db->where('tbl_company_idtbl_company', $companyid);
        $CI->db->where('tbl_company_branch_idtbl_company_branch', $branchid);
        if(!empty($searchCategory)){$CI->db->where_in('tbl_material_type_idtbl_material_type', $searchCategory);}
        $CI->db->select('`idtbl_print_material_info`, `materialname`, `materialinfocode`');
        $CI->db->from('tbl_print_material_info');
        $CI->db->limit(5);
        $respond=$CI->db->get();
    }
    else{            
        if(!empty($searchTerm)){
            $CI = get_instance();
            $CI->db->where('status', 1);
            $CI->db->where('tbl_company_idtbl_company', $companyid);
            $CI->db->where('tbl_company_branch_idtbl_company_branch', $branchid);
            if(!empty($searchCategory)){$CI->db->where_in('tbl_material_type_idtbl_material_type', $searchCategory);}
            $CI->db->select('`idtbl_print_material_info`, `materialname`, `materialinfocode`');
            $CI->db->from('tbl_print_material_info');
            $CI->db->like('materialname', $searchTerm, 'both'); 
            $respond=$CI->db->get();
        }
        else{
            $CI = get_instance();
            $CI->db->where('status', 1);
            $CI->db->where('tbl_company_idtbl_company', $companyid);
            $CI->db->where('tbl_company_branch_idtbl_company_branch', $branchid);
            if(!empty($searchCategory)){$CI->db->where_in('tbl_material_type_idtbl_material_type', $searchCategory);}
            $CI->db->select('`idtbl_print_material_info`, `materialname`, `materialinfocode`');
            $CI->db->from('tbl_print_material_info');
            $CI->db->limit(5);
            $respond=$CI->db->get();             
        }
    }
    
    $data=array();
    
    foreach ($respond->result() as $row) {
        $data[]=array("id"=>$row->idtbl_print_material_info, "text"=>$row->materialname, "code" => $row->materialinfocode);
    }   
    echo json_encode($data);
}
function GetAllMaterialList($searchTerm, $searchCategory) {
    $companyid=$_SESSION['company_id'];
    $branchid=$_SESSION['branch_id'];
    if(!isset($searchTerm)){
        $CI = get_instance();
        $CI->db->where('status', 1);
        $CI->db->where('tbl_company_idtbl_company', $companyid);
        $CI->db->where('tbl_company_branch_idtbl_company_branch', $branchid);
        $CI->db->select('`idtbl_print_material_info`, `materialname`');
        $CI->db->from('tbl_print_material_info');
        $CI->db->limit(5);
        $respond=$CI->db->get();
    }
    else{            
        if(!empty($searchTerm)){
            $CI = get_instance();
            $CI->db->where('status', 1);
            $CI->db->where('tbl_company_idtbl_company', $companyid);
            $CI->db->where('tbl_company_branch_idtbl_company_branch', $branchid);
            $CI->db->select('`idtbl_print_material_info`, `materialname`');
            $CI->db->from('tbl_print_material_info');
            $CI->db->like('materialname', $searchTerm, 'both'); 
            $respond=$CI->db->get();
        }
        else{
            $CI = get_instance();
            $CI->db->where('status', 1);
            $CI->db->where('tbl_company_idtbl_company', $companyid);
            $CI->db->where('tbl_company_branch_idtbl_company_branch', $branchid);
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
function SearchJobList($searchTerm){
    $companyid=$_SESSION['company_id'];

    if(!isset($searchTerm)){
        $CI = get_instance();
        $CI->db->where('i.status', 1);
        $CI->db->where('i.approvestatus', 1);
        $CI->db->where('d.tbl_company_idtbl_company', $companyid);
        $CI->db->where('d.dispatch_finish_status', 0);
        $CI->db->select('d.idtbl_customerinquiry_detail, d.job_no, d.job');
        $CI->db->from('tbl_customerinquiry_detail d');
        $CI->db->join('tbl_customerinquiry i', 'i.idtbl_customerinquiry = d.tbl_customerinquiry_idtbl_customerinquiry');
        $CI->db->order_by('d.idtbl_customerinquiry_detail', 'DESC');
        $CI->db->limit(5);
        $respond=$CI->db->get();
    }
    else{            
        if(!empty($searchTerm)){
            $CI = get_instance();
            $CI->db->where('i.status', 1);
            $CI->db->where('i.approvestatus', 1);
            $CI->db->where('d.tbl_company_idtbl_company', $companyid);
            $CI->db->where('d.dispatch_finish_status', 0);
            $CI->db->select('d.idtbl_customerinquiry_detail, d.job_no, d.job');
            $CI->db->from('tbl_customerinquiry_detail d');
            $CI->db->join('tbl_customerinquiry i', 'i.idtbl_customerinquiry = d.tbl_customerinquiry_idtbl_customerinquiry');
            $CI->db->like('d.job_no', $searchTerm, 'both');
            $respond=$CI->db->get();
        }
        else{
            $CI = get_instance();
            $CI->db->where('i.status', 1);
            $CI->db->where('i.approvestatus', 1);
            $CI->db->where('d.tbl_company_idtbl_company', $companyid);
            $CI->db->where('d.dispatch_finish_status', 0);
            $CI->db->select('d.idtbl_customerinquiry_detail, d.job_no, d.job');
            $CI->db->from('tbl_customerinquiry_detail d');
            $CI->db->join('tbl_customerinquiry i', 'i.idtbl_customerinquiry = d.tbl_customerinquiry_idtbl_customerinquiry');
            $CI->db->order_by('d.idtbl_customerinquiry_detail', 'DESC');
            $CI->db->limit(5);
            $respond=$CI->db->get();             
        }
    }
    
    $data=array();
    
    foreach ($respond->result() as $row) {
        $data[]=array("id"=>$row->idtbl_customerinquiry_detail, "text"=>$row->job . ' - ' . $row->job_no);
    }   
    echo json_encode($data);
}
function tr_batch_num($prefix, $branch){
    $CI = get_instance();
    //start the transaction
    $CI->db->trans_begin();
    $flag = true;
    
    
    /*
    begin-process-to-generate-new-gatepasscode
    */
    $new_ref = ''; //NULL; // purposely-breaking-gatepass-creation-process-without-valid-refnum
    
    $res_callback = 0; // get-updated-result-of-ref-num
    
    /*
    locking-and-generating-with-update-
    assuming-the-most-frequent-operation
    */
    $CI->db->where('idtbl_batch_num_register', $prefix);
    $CI->db->where('tbl_company_branch_idtbl_company_branch', $branch);
    $CI->db->where('acq_locked', '0');
    $CI->db->set('ref_no', 'ref_no+1', FALSE);
    
    $update = $CI->db->update('tbl_batch_num_register', array('acq_locked'=>1));
    
    $affectedRowCnt = $CI->db->affected_rows();
    
    if($affectedRowCnt!=1){
        /*
        fallback-generating-with-insert-where-update-is-refused-
        leaving-primary-key-to-prevent-duplicates-as-less-frequent-incident
        
        */
        $insert = $CI->db->insert('tbl_batch_num_register', 
                                    array('idtbl_batch_num_register'=>$prefix, 
                                'tbl_company_branch_idtbl_company_branch'=>$branch)
                                );
        $affectedRowCnt = $CI->db->affected_rows();
        $res_callback = 1; // set-newly-inserted-value
    }	
    
    if($affectedRowCnt==1){
        if($res_callback==0){
            /*read-the-locked-and-generated-number*/
            $resQuery = $CI->db->get_where('tbl_batch_num_register', 
                                                array('idtbl_batch_num_register'=>$prefix,
                                                    'tbl_company_branch_idtbl_company_branch'=>$branch,
                                                    'acq_locked'=>1)
                                    )->row();
            
            //var_dump($resQuery);
            
            if(!empty($resQuery)){
                $res_callback = $resQuery->ref_no;
            }
            
            
            /*release-the-locked-number*/
            $CI->db->where('idtbl_batch_num_register', $prefix);
            $CI->db->where('tbl_company_branch_idtbl_company_branch', $branch);
            $CI->db->where('acq_locked', '1');
            $ResultOut = $CI->db->update('tbl_batch_num_register', array('acq_locked'=>0));
            
            if(!$ResultOut){
                $flag = false;
            }
        }
        
        if($res_callback>0){
            $str_callback = '000000'.$res_callback;
            $new_ref = $prefix.substr($str_callback, strlen($str_callback)-6, strlen($str_callback));
        }
    }else{
        $flag = false;
    }
    
    /*end-process-new-ref-number*/
    
    $CI->db->trans_complete();
    //check if transaction status TRUE or FALSE
    if(($CI->db->trans_status()===FALSE)||($flag==FALSE)){
        //if something went wrong, rollback everything
        $CI->db->trans_rollback();
        $importmsg = 'Transaction error';//.$detailData['order_qty']
        $msgclass = 'bg-warning text-white';
    }else{
        //if everything went right, commit the data to the database
        $CI->db->trans_commit();
        $msgclass = 'bg-success text-white';
    }
    
    return $new_ref;
}
function SearchSupplierListGRNVoucher($searchTerm){
    $companyid=$_SESSION['company_id'];
    $branchid=$_SESSION['branch_id'];

    if(!isset($searchTerm)){
        $CI = get_instance();
        $CI->db->where('tbl_supplier.status', 1);
        $CI->db->where('tbl_supplier.tbl_company_idtbl_company', $companyid);
        $CI->db->where('tbl_supplier.tbl_company_branch_idtbl_company_branch', $branchid);
        $CI->db->select('tbl_supplier.idtbl_supplier, tbl_supplier.suppliername, tbl_supplier.telephone_no');
        $CI->db->from('tbl_supplier');
        $CI->db->join('tbl_supplier_type', 'tbl_supplier_type.idtbl_supplier_type = tbl_supplier.tbl_supplier_type_idtbl_supplier_type');
        $CI->db->limit(5);
        $respond=$CI->db->get();
    }
    else{            
        if(!empty($searchTerm)){
            $CI = get_instance();
            $CI->db->where('tbl_supplier.status', 1);
            $CI->db->where('tbl_supplier.tbl_company_idtbl_company', $companyid);
            $CI->db->where('tbl_supplier.tbl_company_branch_idtbl_company_branch', $branchid);
            $CI->db->select('tbl_supplier.idtbl_supplier, tbl_supplier.suppliername, tbl_supplier.telephone_no');
            $CI->db->from('tbl_supplier');
            $CI->db->join('tbl_supplier_type', 'tbl_supplier_type.idtbl_supplier_type = tbl_supplier.tbl_supplier_type_idtbl_supplier_type');
            $CI->db->like('tbl_supplier.suppliername', $searchTerm, 'both'); 
            $respond=$CI->db->get();
        }
        else{
            $CI = get_instance();
            $CI->db->where('tbl_supplier.status', 1);
            $CI->db->where('tbl_supplier.tbl_company_idtbl_company', $companyid);
            $CI->db->where('tbl_supplier.tbl_company_branch_idtbl_company_branch', $branchid);
            $CI->db->select('tbl_supplier.idtbl_supplier, tbl_supplier.suppliername, tbl_supplier.telephone_no');
            $CI->db->from('tbl_supplier');
            $CI->db->join('tbl_supplier_type', 'tbl_supplier_type.idtbl_supplier_type = tbl_supplier.tbl_supplier_type_idtbl_supplier_type');
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