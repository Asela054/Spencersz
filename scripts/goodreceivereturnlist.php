<?php

$table = 'tbl_grn_return';
$primaryKey = 'idtbl_grn_return';

$columns = array(
	array( 'db' => '`u`.`idtbl_grn_return`', 'dt' => 'idtbl_grn_return', 'field' => 'idtbl_grn_return' ),
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
	array( 'db' => '`ua`.`suppliername`', 'dt' => 'suppliername', 'field' => 'suppliername' ),
	array( 'db' => '`u`.`status`', 'dt' => 'status', 'field' => 'status' ),
);

require('config.php');
$sql_details = array(
	'user' => $db_username,
	'pass' => $db_password,
	'db'   => $db_name,
	'host' => $db_host
);

require('ssp.customized.class.php');

$joinQuery = "FROM `tbl_grn_return` AS `u` 
    LEFT JOIN `tbl_supplier` AS `ua` ON (`ua`.`idtbl_supplier` = `u`.`tbl_supplier_idtbl_supplier`)";

$extraWhere = "`u`.`status` IN (1,2)";

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere)
);