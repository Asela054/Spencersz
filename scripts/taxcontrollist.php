<?php

/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

// DB table to use
$table = 'tbl_tax_control';

// Table's primary key
$primaryKey = 'id_tax_control';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
	array( 'db' => '`u`.`id_tax_control`', 'dt' => 'id_tax_control', 'field' => 'id_tax_control' ),
	array( 'db' => '`u`.`tax_controlname`', 'dt' => 'tax_controlname', 'field' => 'tax_controlname' ),
	array( 'db' => '`u`.`tax_controldate`', 'dt' => 'tax_controldate', 'field' => 'tax_controldate' ),
	array( 'db' => '`u`.`percentage`', 'dt' => 'percentage', 'field' => 'percentage' ),
	array( 'db' => '`u`.`effective_from`', 'dt' => 'effective_from', 'field' => 'effective_from' ),
	array( 'db' => '`u`.`chart_of_accounts`', 'dt' => 'chart_of_accounts', 'field' => 'chart_of_accounts' ),
	array( 'db' => '`u`.`status`', 'dt' => 'status', 'field' => 'status' ),
);

// SQL server connection information
require('config.php');
$sql_details = array(
	'user' => $db_username,
	'pass' => $db_password,
	'db'   => $db_name,
	'host' => $db_host
);

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

// require( 'ssp.class.php' );
require('ssp.customized.class.php' );

$joinQuery = "FROM `tbl_tax_control` AS `u`";

$extraWhere = "`u`.`status` IN (1, 2)";

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere)
);
