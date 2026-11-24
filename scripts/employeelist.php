<?php

/*
 * DataTables server-side processing script.
 * @license MIT - http://datatables.net/license_mit
 */

// DB table to use
$table = 'tbl_employee';

// Table's primary key
$primaryKey = 'idtbl_employee';

$columns = array(
	array( 'db' => '`u`.`idtbl_employee`', 'dt' => 'idtbl_employee', 'field' => 'idtbl_employee' ),
	array( 'db' => '`u`.`emp_no`', 'dt' => 'emp_no', 'field' => 'emp_no' ),
	array( 'db' => '`u`.`full_name`', 'dt' => 'full_name', 'field' => 'full_name' ),
	array( 'db' => '`u`.`nic`', 'dt' => 'nic', 'field' => 'nic' ),
	array( 'db' => '`u`.`designation`', 'dt' => 'designation', 'field' => 'designation' ),
	array( 'db' => '`u`.`department`', 'dt' => 'department', 'field' => 'department' ),
	array( 'db' => '`u`.`phone`', 'dt' => 'phone', 'field' => 'phone' ),
	array( 'db' => '`u`.`join_date`', 'dt' => 'join_date', 'field' => 'join_date' ),
	array( 'db' => '`u`.`status`', 'dt' => 'status', 'field' => 'status' ),
	array( 'db' => '`b`.`branch`', 'dt' => 'branch', 'field' => 'branch' )
);

// SQL server connection information
require('config.php');
$sql_details = array(
	'user' => $db_username,
	'pass' => $db_password,
	'db'   => $db_name,
	'host' => $db_host
);

require('ssp.customized.class.php' );

$joinQuery = "FROM `tbl_employee` AS `u` LEFT JOIN `tbl_company_branch` AS `b` ON `b`.`idtbl_company_branch` = `u`.`tbl_company_branch_idtbl_company_branch`";
$extraWhere = "`u`.`status` IN (1, 2)";

// branch filter (cast to int, so it is safe)
if (isset($_POST['branch']) && (int)$_POST['branch'] > 0) {
	$extraWhere .= " AND `u`.`tbl_company_branch_idtbl_company_branch` = " . (int)$_POST['branch'];
}

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns,$joinQuery, $extraWhere)
);