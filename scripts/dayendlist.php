<?php

/*
 * DataTables server-side processing script.
 * @license MIT - http://datatables.net/license_mit
 */

// DB table to use
$table = 'tbl_day_end';

// Table's primary key
$primaryKey = 'idtbl_day_end';

$columns = array(
	array( 'db' => '`d`.`idtbl_day_end`', 'dt' => 'idtbl_day_end', 'field' => 'idtbl_day_end' ),
	array( 'db' => '`d`.`day_date`', 'dt' => 'day_date', 'field' => 'day_date' ),
	array( 'db' => '`d`.`invoice_count`', 'dt' => 'invoice_count', 'field' => 'invoice_count' ),
	array( 'db' => '`d`.`net_total`', 'dt' => 'net_total', 'field' => 'net_total' ),
	array( 'db' => '`d`.`expected_cash`', 'dt' => 'expected_cash', 'field' => 'expected_cash' ),
	array( 'db' => '`d`.`counted_cash`', 'dt' => 'counted_cash', 'field' => 'counted_cash' ),
	array( 'db' => '`d`.`difference_total`', 'dt' => 'difference_total', 'field' => 'difference_total' ),
	array( 'db' => '`d`.`insertdatetime`', 'dt' => 'insertdatetime', 'field' => 'insertdatetime' ),
	array( 'db' => '`us`.`name`', 'dt' => 'done_by', 'field' => 'name' )
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

$joinQuery = "FROM `tbl_day_end` AS `d` LEFT JOIN `tbl_user` AS `us` ON `us`.`idtbl_user` = `d`.`tbl_user_idtbl_user`";
$extraWhere = "`d`.`status` = 1";

// branch filter (cast to int, so it is safe)
if (isset($_POST['branch']) && (int)$_POST['branch'] > 0) {
	$extraWhere .= " AND `d`.`tbl_company_branch_idtbl_company_branch` = " . (int)$_POST['branch'];
}

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns,$joinQuery, $extraWhere)
);