<?php
$table = 'tbl_reservation';
$primaryKey = 'idtbl_reservation';

$columns = array(
	array( 'db' => '`u`.`idtbl_reservation`', 'dt' => 'idtbl_reservation', 'field' => 'idtbl_reservation' ),
	array( 'db' => '`u`.`reservation_no`', 'dt' => 'reservation_no', 'field' => 'reservation_no' ),
	array( 'db' => '`u`.`reservation_date`', 'dt' => 'reservation_date', 'field' => 'reservation_date' ),
	array( 'db' => '`u`.`expiry_date`', 'dt' => 'expiry_date', 'field' => 'expiry_date' ),
	array( 'db' => '`c`.`customer`', 'dt' => 'customer', 'field' => 'customer' ),
	array( 'db' => '`u`.`total_qty`', 'dt' => 'total_qty', 'field' => 'total_qty' ),
	array( 'db' => '`u`.`subtotal`', 'dt' => 'subtotal', 'field' => 'subtotal' ),
	array( 'db' => '`u`.`vat_amount`', 'dt' => 'vat_amount', 'field' => 'vat_amount' ),
	array( 'db' => '`u`.`total_amount`', 'dt' => 'total_amount', 'field' => 'total_amount' ),
	array( 'db' => '`u`.`advance_amount`', 'dt' => 'advance_amount', 'field' => 'advance_amount' ),
	array( 'db' => '`u`.`reserve_status`', 'dt' => 'reserve_status', 'field' => 'reserve_status' ),
	array( 'db' => '`u`.`status`', 'dt' => 'status', 'field' => 'status' )
);

require('config.php');
$sql_details = array(
	'user' => $db_username,
	'pass' => $db_password,
	'db'   => $db_name,
	'host' => $db_host
);

require('ssp.customized.class.php' );

$joinQuery = "FROM `tbl_reservation` AS `u` LEFT JOIN `tbl_customer` AS `c` ON (`c`.`idtbl_customer` = `u`.`tbl_customer_idtbl_customer`)";
$extraWhere = "`u`.`status` IN (1, 2)";

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns,$joinQuery, $extraWhere)
);