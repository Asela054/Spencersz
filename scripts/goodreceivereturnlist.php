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
$table = 'tbl_print_grn_return';

// Table's primary key
$primaryKey = 'idtbl_print_grn_return';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
	array( 'db' => '`u`.`idtbl_print_grn_return`', 'dt' => 'idtbl_print_grn_return', 'field' => 'idtbl_print_grn_return' ),
	array( 'db' => '`u`.`batchno`', 'dt' => 'batchno', 'field' => 'batchno' ),
	array( 'db' => '`u`.`grn_no`', 'dt' => 'grn_no', 'field' => 'grn_no' ),
	array( 'db' => '`u`.`grn_type`', 'dt' => 'grn_type', 'field' => 'grn_type' ),
	array( 'db' => '`u`.`discount`', 'dt' => 'discount', 'field' => 'discount' ),
	array( 'db' => '`u`.`subtotal`', 'dt' => 'subtotal', 'field' => 'subtotal' ),
	array( 'db' => '`u`.`vat`', 'dt' => 'vat', 'field' => 'vat' ),
	array( 'db' => '`u`.`totalpayment`', 'dt' => 'totalpayment', 'field' => 'totalpayment' ),
	array( 'db' => '`u`.`remark`', 'dt' => 'remark', 'field' => 'remark' ),
	array( 'db' => '`u`.`approvestatus`', 'dt' => 'approvestatus', 'field' => 'approvestatus' ),
	array( 'db' => '`u`.`tbl_supplier_idtbl_supplier`', 'dt' => 'tbl_supplier_idtbl_supplier', 'field' => 'tbl_supplier_idtbl_supplier' ),
	array( 'db' => '`ua`.`suppliername` AS `suppliername`', 'dt' => 'suppliername', 'field' => 'suppliername' ),
	array( 'db' => '`ub`.`group` AS `group`', 'dt' => 'group', 'field' => 'group' ),
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

$joinQuery = "FROM `tbl_print_grn_return` AS `u` LEFT JOIN `tbl_supplier` AS `ua` ON (`ua`.`idtbl_supplier` = `u`.`tbl_supplier_idtbl_supplier`) LEFT JOIN `tbl_material_group` AS `ub` ON (`ub`.`idtbl_material_group` = `u`.`grn_type`)";

$extraWhere = "`u`.`status` IN (1,2)";

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere)
);