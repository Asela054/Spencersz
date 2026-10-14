<?php

/*
 * DataTables server-side processing script.
 * @license MIT - http://datatables.net/license_mit
 */

// DB table to use
$table = 'tbl_company_branch';

// Table's primary key
$primaryKey = 'idtbl_company_branch';

$columns = array(
	array( 'db' => '`u`.`idtbl_company_branch`', 'dt' => 'idtbl_company_branch', 'field' => 'idtbl_company_branch' ),
	array( 'db' => '`c`.`company`', 'dt' => 'company', 'field' => 'company' ),
	array( 'db' => '`u`.`branch`', 'dt' => 'branch', 'field' => 'branch' ),
	array( 'db' => '`u`.`type`', 'dt' => 'type', 'field' => 'type' ),
	array( 'db' => '`u`.`code`', 'dt' => 'code', 'field' => 'code' ),
	array( 'db' => '`u`.`mobile`', 'dt' => 'mobile', 'field' => 'mobile' ),
	array( 'db' => '`u`.`phone`', 'dt' => 'phone', 'field' => 'phone' ),
	array( 'db' => '`u`.`email`', 'dt' => 'email', 'field' => 'email' ),
	array( 'db' => '`u`.`status`', 'dt' => 'status', 'field' => 'status' )
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

$joinQuery = "FROM `tbl_company_branch` AS `u` LEFT JOIN `tbl_company` AS `c` ON (`c`.`idtbl_company` = `u`.`tbl_company_idtbl_company`)";
$extraWhere = "`u`.`status` IN (1, 2)";

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns,$joinQuery, $extraWhere)
);
