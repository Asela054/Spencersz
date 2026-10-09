<?php

/*
 * DataTables server-side processing script.
 * @license MIT - http://datatables.net/license_mit
 */

// DB table to use
$table = 'tbl_serial_movement';

// Table's primary key
$primaryKey = 'idtbl_serial_movement';

$columns = array(
	array( 'db' => '`u`.`idtbl_serial_movement`', 'dt' => 'idtbl_serial_movement', 'field' => 'idtbl_serial_movement' ),
	array( 'db' => '`u`.`insertdatetime`', 'dt' => 'insertdatetime', 'field' => 'insertdatetime' ),
	array( 'db' => '`ps`.`idtbl_product_serial`', 'dt' => 'idtbl_product_serial', 'field' => 'idtbl_product_serial' ),
	array( 'db' => '`ps`.`serialno`', 'dt' => 'serialno', 'field' => 'serialno' ),
	array( 'db' => '`p`.`product_name`', 'dt' => 'product_name', 'field' => 'product_name' ),
	array( 'db' => '`p`.`product_code`', 'dt' => 'product_code', 'field' => 'product_code' ),
	array( 'db' => '`u`.`movement_type`', 'dt' => 'movement_type', 'field' => 'movement_type' ),
	array( 'db' => '`u`.`from_status`', 'dt' => 'from_status', 'field' => 'from_status' ),
	array( 'db' => '`u`.`to_status`', 'dt' => 'to_status', 'field' => 'to_status' ),
	array( 'db' => '`u`.`doc_type`', 'dt' => 'doc_type', 'field' => 'doc_type' ),
	array( 'db' => '`u`.`doc_id`', 'dt' => 'doc_id', 'field' => 'doc_id' ),
	array( 'db' => '`g`.`grn_no`', 'dt' => 'grn_no', 'field' => 'grn_no' ),
	array( 'db' => '`us`.`name`', 'dt' => 'name', 'field' => 'name' ),
	array( 'db' => '`u`.`remark`', 'dt' => 'remark', 'field' => 'remark' )
);

require('config.php');
$sql_details = array(
	'user' => $db_username,
	'pass' => $db_password,
	'db'   => $db_name,
	'host' => $db_host
);

require('ssp.customized.class.php' );

$joinQuery = "FROM `tbl_serial_movement` AS `u`
	LEFT JOIN `tbl_product_serial` AS `ps` ON (`ps`.`idtbl_product_serial` = `u`.`tbl_product_serial_idtbl_product_serial`)
	LEFT JOIN `tbl_product` AS `p` ON (`p`.`idtbl_product` = `ps`.`tbl_product_idtbl_product`)
	LEFT JOIN `tbl_user` AS `us` ON (`us`.`idtbl_user` = `u`.`tbl_user_idtbl_user`)
	LEFT JOIN `tbl_grn` AS `g` ON (`g`.`idtbl_grn` = `u`.`doc_id` AND `u`.`doc_type` = 'GRN')";

$where = array('1 = 1');

if (!empty($_POST['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['from'])) {
	$where[] = "DATE(`u`.`insertdatetime`) >= '".$_POST['from']."'";
}
if (!empty($_POST['to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['to'])) {
	$where[] = "DATE(`u`.`insertdatetime`) <= '".$_POST['to']."'";
}
if (!empty($_POST['type'])) {
	$where[] = "`u`.`movement_type` = ".(int)$_POST['type'];
}
if (!empty($_POST['product'])) {
	$where[] = "`ps`.`tbl_product_idtbl_product` = ".(int)$_POST['product'];
}
if (!empty($_POST['serial'])) {
	$serial = preg_replace('/[^A-Za-z0-9\-\._\/ ]/', '', trim($_POST['serial']));
	if ($serial !== '') {
		$where[] = "`ps`.`serialno` LIKE '%".$serial."%'";
	}
}

$extraWhere = implode(' AND ', $where);

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns,$joinQuery, $extraWhere)
);