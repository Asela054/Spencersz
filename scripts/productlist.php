<?php

/*
 * DataTables server-side processing script.
 * @license MIT - http://datatables.net/license_mit
 */

// DB table to use
$table = 'tbl_product';

// Table's primary key
$primaryKey = 'idtbl_product';

$columns = array(
	array( 'db' => '`u`.`idtbl_product`', 'dt' => 'idtbl_product', 'field' => 'idtbl_product' ),
	array( 'db' => '`u`.`product_code`', 'dt' => 'product_code', 'field' => 'product_code' ),
	array( 'db' => '`u`.`barcode`', 'dt' => 'barcode', 'field' => 'barcode' ),
	array( 'db' => '`u`.`product_name`', 'dt' => 'product_name', 'field' => 'product_name' ),
	array( 'db' => '`u`.`model_no`', 'dt' => 'model_no', 'field' => 'model_no' ),
	array( 'db' => '`uc`.`category`', 'dt' => 'category', 'field' => 'category' ),
	array( 'db' => '`ub`.`brand`', 'dt' => 'brand', 'field' => 'brand' ),
	array( 'db' => '`u`.`cost_price`', 'dt' => 'cost_price', 'field' => 'cost_price' ),
	array( 'db' => '`u`.`selling_price`', 'dt' => 'selling_price', 'field' => 'selling_price' ),
	array( 'db' => '`u`.`has_serial`', 'dt' => 'has_serial', 'field' => 'has_serial' ),
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

$joinQuery = "FROM `tbl_product` AS `u` LEFT JOIN `tbl_category` AS `uc` ON (`uc`.`idtbl_category` = `u`.`tbl_category_idtbl_category`) LEFT JOIN `tbl_brand` AS `ub` ON (`ub`.`idtbl_brand` = `u`.`tbl_brand_idtbl_brand`)";
$extraWhere = "`u`.`status` IN (1, 2)";

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns,$joinQuery, $extraWhere)
);
