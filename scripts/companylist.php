<?php

/*
 * DataTables server-side processing script.
 * @license MIT - http://datatables.net/license_mit
 */

// DB table to use
$table = 'tbl_company';

// Table's primary key
$primaryKey = 'idtbl_company';

$columns = array(
	array( 'db' => '`u`.`idtbl_company`', 'dt' => 'idtbl_company', 'field' => 'idtbl_company' ),
	array( 'db' => '`u`.`company`', 'dt' => 'company', 'field' => 'company' ),
	array( 'db' => '`u`.`code`', 'dt' => 'code', 'field' => 'code' ),
	array( 'db' => '`u`.`mobile`', 'dt' => 'mobile', 'field' => 'mobile' ),
	array( 'db' => '`u`.`phone`', 'dt' => 'phone', 'field' => 'phone' ),
	array( 'db' => '`u`.`email`', 'dt' => 'email', 'field' => 'email' ),
	array( 'db' => '`u`.`vat_reg_no`', 'dt' => 'vat_reg_no', 'field' => 'vat_reg_no' ),
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

$joinQuery = "FROM `tbl_company` AS `u`";
$extraWhere = "`u`.`status` IN (1, 2)";

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns,$joinQuery, $extraWhere)
);
