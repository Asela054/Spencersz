<?php

/*
 * DataTables server-side processing script.
 * @license MIT - http://datatables.net/license_mit
 */

// DB table to use
$table = 'tbl_gift_voucher';

// Table's primary key
$primaryKey = 'idtbl_gift_voucher';

$columns = array(
	array( 'db' => '`u`.`idtbl_gift_voucher`', 'dt' => 'idtbl_gift_voucher', 'field' => 'idtbl_gift_voucher' ),
	array( 'db' => '`u`.`voucher_code`', 'dt' => 'voucher_code', 'field' => 'voucher_code' ),
	array( 'db' => '`u`.`voucher_value`', 'dt' => 'voucher_value', 'field' => 'voucher_value' ),
	array( 'db' => '`u`.`issue_date`', 'dt' => 'issue_date', 'field' => 'issue_date' ),
	array( 'db' => '`u`.`expiry_date`', 'dt' => 'expiry_date', 'field' => 'expiry_date' ),
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

$joinQuery = "FROM `tbl_gift_voucher` AS `u`";
$extraWhere = "`u`.`status` IN (1, 2)";

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns,$joinQuery, $extraWhere)
);