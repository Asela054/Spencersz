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
	array( 'db' => '`u`.`product_name`', 'dt' => 'product_name', 'field' => 'product_name' ),
	array( 'db' => '`u`.`model_no`', 'dt' => 'model_no', 'field' => 'model_no' ),
	array( 'db' => '`u`.`selling_price`', 'dt' => 'selling_price', 'field' => 'selling_price' ),
	array( 'db' => '`u`.`discount_percent`', 'dt' => 'discount_percent', 'field' => 'discount_percent' ),
	array( 'db' => '`u`.`discount_start`', 'dt' => 'discount_start', 'field' => 'discount_start' ),
	array( 'db' => '`u`.`discount_end`', 'dt' => 'discount_end', 'field' => 'discount_end' ),
	array( 'db' => '`u`.`warranty_months`', 'dt' => 'warranty_months', 'field' => 'warranty_months' )
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

$joinQuery = "FROM `tbl_product` AS `u`";
$extraWhere = "`u`.`status` = 1";

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns,$joinQuery, $extraWhere)
);