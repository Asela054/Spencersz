<?php

/*
 * DataTables server-side processing script.
 * @license MIT - http://datatables.net/license_mit
 */

// DB table to use
$table = 'tbl_unit';

// Table's primary key
$primaryKey = 'idtbl_unit';

$columns = array(
	array( 'db' => '`u`.`idtbl_unit`', 'dt' => 'idtbl_unit', 'field' => 'idtbl_unit' ),
	array( 'db' => '`u`.`unit`', 'dt' => 'unit', 'field' => 'unit' ),
	array( 'db' => '`u`.`unit_short`', 'dt' => 'unit_short', 'field' => 'unit_short' ),
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

$joinQuery = "FROM `tbl_unit` AS `u`";
$extraWhere = "`u`.`status` IN (1, 2)";

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns,$joinQuery, $extraWhere)
);
