<?php

/*
 * DataTables server-side processing script - Combo list
 * Save as: scripts/combolist.php
 */

// DB table to use
$table = 'tbl_combo';

// Table's primary key
$primaryKey = 'idtbl_combo';

$columns = array(
	array( 'db' => '`c`.`idtbl_combo`',  'dt' => 'idtbl_combo',  'field' => 'idtbl_combo' ),
	array( 'db' => '`p`.`product_code`', 'dt' => 'product_code', 'field' => 'product_code' ),
	array( 'db' => '`c`.`combo_name`',   'dt' => 'combo_name',   'field' => 'combo_name' ),
	array( 'db' => '`c`.`total_cost`',   'dt' => 'total_cost',   'field' => 'total_cost' ),
	array( 'db' => '`c`.`combo_price`',  'dt' => 'combo_price',  'field' => 'combo_price' ),
	array( 'db' => '`c`.`status`',       'dt' => 'status',       'field' => 'status' )
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

$joinQuery = "FROM `tbl_combo` AS `c` INNER JOIN `tbl_product` AS `p` ON (`p`.`idtbl_product` = `c`.`tbl_product_idtbl_product`)";
$extraWhere = "`c`.`status` IN (1, 2)";

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns,$joinQuery, $extraWhere)
);