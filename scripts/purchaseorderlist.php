<?php
$table = 'tbl_porder';
$primaryKey = 'idtbl_porder';

$columns = array(
	array( 'db' => '`u`.`idtbl_porder`', 'dt' => 'idtbl_porder', 'field' => 'idtbl_porder' ),
	array( 'db' => '`u`.`orderdate`', 'dt' => 'orderdate', 'field' => 'orderdate' ),
	array( 'db' => '`u`.`tbl_porder_req_idtbl_porder_req`', 'dt' => 'tbl_porder_req_idtbl_porder_req', 'field' => 'tbl_porder_req_idtbl_porder_req' ),
	array( 'db' => '`u`.`nettotal`', 'dt' => 'nettotal', 'field' => 'nettotal' ),
	array( 'db' => '`u`.`confirmstatus`', 'dt' => 'confirmstatus', 'field' => 'confirmstatus' ),
	array( 'db' => '`u`.`grnconfirm`', 'dt' => 'grnconfirm', 'field' => 'grnconfirm' ),
	array( 'db' => '`u`.`porder_no`', 'dt' => 'porder_no', 'field' => 'porder_no' ),
	array( 'db' => '`ua`.`suppliername`', 'dt' => 'suppliername', 'field' => 'suppliername' ),
	array( 'db' => '`u`.`status`', 'dt' => 'status', 'field' => 'status' ),
	array( 'db' => '`u`.`check_by`', 'dt' => 'check_by', 'field' => 'check_by' ),
	array( 'db' => '`u`.`updateuser`', 'dt' => 'updateuser', 'field' => 'updateuser' ),
	array( 'db' => '`ue`.`name`', 'dt' => 'name', 'field' => 'name' ),
	array(
		'db' => "CONCAT(
			CASE 
				WHEN `u`.`confirmstatus` = 1 THEN '<i class=\"fas fa-check text-success mr-2\"></i>Confirm PO'
				WHEN `u`.`confirmstatus` = 2 THEN '<i class=\"fa fa-times text-danger mr-2\"></i>Reject PO'
				ELSE '<i class=\"fa fa-spinner text-warning mr-2\"></i>Pending PO for Approval'
			END
		)",
		'dt' => 'confirmstatus_display',
		'field' => 'confirmstatus_display',
		'as' => 'confirmstatus_display'
	),
	array(
		'db' => "CONCAT(
			CASE 
				WHEN `u`.`grnconfirm` = 1 THEN '<i class=\"fas fa-check text-success mr-2\"></i>GRN Issued'
				ELSE '<i class=\"fa fa-spinner text-danger mr-2\"></i>Pending GRN for Approval'
			END
		)",
		'dt' => 'grnconfirm_display',
		'field' => 'grnconfirm_display',
		'as' => 'grnconfirm_display'
	)
);

require('config.php');
$sql_details = array(
	'user' => $db_username,
	'pass' => $db_password,
	'db'   => $db_name,
	'host' => $db_host
);

require('ssp.customized.class.php' );
$companyID = (int)$_POST['company_id'];

$joinQuery = "FROM `tbl_porder` AS `u` LEFT JOIN `tbl_supplier` AS `ua` ON (`ua`.`idtbl_supplier` = `u`.`tbl_supplier_idtbl_supplier`) LEFT JOIN `tbl_porder_req` AS `ud` ON (`ud`.`idtbl_porder_req` = `u`.`tbl_porder_req_idtbl_porder_req`) LEFT JOIN `tbl_user` AS `ue` ON (`ue`.`idtbl_user` = `u`.`approve_by`)";

$extraWhere = "`u`.`status` IN (1,2) AND `u`.`tbl_company_idtbl_company`='$companyID'";

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere)
);