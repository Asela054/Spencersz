<?php

/*
 * DataTables server-side processing script.
 * @license MIT - http://datatables.net/license_mit
 */

// DB table to use
$table = 'tbl_product_serial';

// Table's primary key
$primaryKey = 'idtbl_product_serial';

$columns = array(
	array( 'db' => '`u`.`idtbl_product_serial`', 'dt' => 'idtbl_product_serial', 'field' => 'idtbl_product_serial' ),
	array( 'db' => '`u`.`serialno`', 'dt' => 'serialno', 'field' => 'serialno' ),
	array( 'db' => '`g`.`grn_no`', 'dt' => 'grn_no', 'field' => 'grn_no' ),
	array( 'db' => '`u`.`tbl_grn_idtbl_grn`', 'dt' => 'tbl_grn_idtbl_grn', 'field' => 'tbl_grn_idtbl_grn' ),
	array( 'db' => '`u`.`costunitprice`', 'dt' => 'costunitprice', 'field' => 'costunitprice' ),
	array( 'db' => '`u`.`stock_status`', 'dt' => 'stock_status', 'field' => 'stock_status' ),
	array( 'db' => '`u`.`sold_invoice_id`', 'dt' => 'sold_invoice_id', 'field' => 'sold_invoice_id' ),
	array( 'db' => '`u`.`sold_date`', 'dt' => 'sold_date', 'field' => 'sold_date' ),
	array( 'db' => '`u`.`warranty_end`', 'dt' => 'warranty_end', 'field' => 'warranty_end' ),
	array( 'db' => '`u`.`insertdatetime`', 'dt' => 'insertdatetime', 'field' => 'insertdatetime' ),
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

// Product chosen on the page (cast to int so it is safe in the WHERE clause)
$productID = isset($_POST['productID']) ? (int)$_POST['productID'] : 0;

$joinQuery = "FROM `tbl_product_serial` AS `u` LEFT JOIN `tbl_grn` AS `g` ON (`g`.`idtbl_grn` = `u`.`tbl_grn_idtbl_grn`)";
$extraWhere = "`u`.`status` IN (1, 2) AND `u`.`tbl_product_idtbl_product` = ".$productID;

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns,$joinQuery, $extraWhere)
);