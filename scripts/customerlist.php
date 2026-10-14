<?php
require_once '../external.php';

$CI =& get_instance();
$CI->load->library('session');
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
$table = 'tbl_customer';

// Table's primary key
$primaryKey = 'idtbl_customer';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
	array( 'db' => '`u`.`idtbl_customer`', 'dt' => 'idtbl_customer', 'field' => 'idtbl_customer' ),
	array( 'db' => '`u`.`customer`', 'dt' => 'customer', 'field' => 'customer' ),
	array( 'db' => '`u`.`ref_no`', 'dt' => 'ref_no', 'field' => 'ref_no' ),
	array( 'db' => '`u`.`address_line1`', 'dt' => 'address_line1', 'field' => 'address_line1' ),
	array( 'db' => '`u`.`address_line2`', 'dt' => 'address_line2', 'field' => 'address_line2' ),
	array( 'db' => '`u`.`city`', 'dt' => 'city', 'field' => 'city' ),
	array( 'db' => '`u`.`vat_no`', 'dt' => 'vat_no', 'field' => 'vat_no' ),
	array( 'db' => '`u`.`bus_reg_no`', 'dt' => 'bus_reg_no', 'field' => 'bus_reg_no' ),
	array( 'db' => '`u`.`imagepath`', 'dt' => 'imagepath', 'field' => 'imagepath' ),
	array( 'db' => '`u`.`nbt_no`', 'dt' => 'nbt_no', 'field' => 'nbt_no' ),
	array( 'db' => '`u`.`svat_no`', 'dt' => 'svat_no', 'field' => 'svat_no' ),
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

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

// require( 'ssp.class.php' );
require('ssp.customized.class.php' );

$companyid=$_SESSION['company_id'];
$branchid=$_SESSION['branch_id'];

$joinQuery = "FROM `tbl_customer` AS `u`";
$extraWhere = "`u`.`status` IN (1, 2) AND `u`.`tbl_company_idtbl_company`='$companyid' AND `u`.`tbl_company_branch_idtbl_company_branch`='$branchid'";
echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns,$joinQuery, $extraWhere)
);
