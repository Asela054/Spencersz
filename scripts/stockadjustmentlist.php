<?php
$table = 'tbl_stock_adjustment';
$primaryKey = 'idtbl_stock_adjustment';

$columns = array(
	array( 'db' => '`u`.`idtbl_stock_adjustment`', 'dt' => 'idtbl_stock_adjustment', 'field' => 'idtbl_stock_adjustment' ),
	array( 'db' => '`u`.`adjustment_no`', 'dt' => 'adjustment_no', 'field' => 'adjustment_no' ),
	array( 'db' => '`u`.`adjustment_date`', 'dt' => 'adjustment_date', 'field' => 'adjustment_date' ),
	array( 'db' => '`u`.`adjustment_type`', 'dt' => 'adjustment_type', 'field' => 'adjustment_type' ),
	array( 'db' => '`u`.`reason`', 'dt' => 'reason', 'field' => 'reason' ),
	array( 'db' => '`u`.`total_qty`', 'dt' => 'total_qty', 'field' => 'total_qty' ),
	array( 'db' => '`u`.`total_value`', 'dt' => 'total_value', 'field' => 'total_value' ),
	array( 'db' => '`u`.`approve_status`', 'dt' => 'approve_status', 'field' => 'approve_status' ),
	array( 'db' => '`b`.`branch`', 'dt' => 'branch', 'field' => 'branch' ),
	array( 'db' => '`c`.`name`', 'dt' => 'name', 'field' => 'name' )
);

require('config.php');
$sql_details = array(
	'user' => $db_username,
	'pass' => $db_password,
	'db'   => $db_name,
	'host' => $db_host
);

require('ssp.customized.class.php' );

$joinQuery = "FROM `tbl_stock_adjustment` AS `u`
 LEFT JOIN `tbl_company_branch` AS `b` ON `b`.`idtbl_company_branch` = `u`.`tbl_company_branch_idtbl_company_branch`
 LEFT JOIN `tbl_user` AS `c` ON `c`.`idtbl_user` = `u`.`tbl_user_idtbl_user`";
$extraWhere = "`u`.`status` IN (1, 2)";

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns,$joinQuery, $extraWhere)
);