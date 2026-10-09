<?php

// DB table to use
$table = 'tbl_stock';

// Table's primary key
$primaryKey = 'idtbl_stock';

$columns = array(
    array( 'db' => '`u`.`idtbl_stock`',    'dt' => 'idtbl_stock',    'field' => 'idtbl_stock' ),
    array( 'db' => '`u`.`batchno`',        'dt' => 'batchno',        'field' => 'batchno' ),
    array( 'db' => '`ua`.`location`',      'dt' => 'location',       'field' => 'location' ),
    array( 'db' => '`uw`.`wh_name`',       'dt' => 'wh_name',        'field' => 'wh_name' ),
    array( 'db' => '`u`.`grnqty`',         'dt' => 'grnqty',         'field' => 'grnqty' ),
    array( 'db' => '`u`.`qty`',            'dt' => 'qty',            'field' => 'qty' ),
    array( 'db' => '`u`.`costunitprice`',  'dt' => 'costunitprice',  'field' => 'costunitprice' ),
    array( 'db' => '`ub`.`product_code`',  'dt' => 'product_code',   'field' => 'product_code' ),
    array( 'db' => '`ub`.`product_name`',  'dt' => 'product_name',   'field' => 'product_name' ),
    array( 'db' => '`uu`.`unit`',          'dt' => 'unit',           'field' => 'unit' )
);

// SQL server connection information
require('config.php');
$sql_details = array(
    'user' => $db_username,
    'pass' => $db_password,
    'db'   => $db_name,
    'host' => $db_host
);

require('ssp.customized.class.php');

$joinQuery = "FROM `tbl_stock` AS `u`
    LEFT JOIN `tbl_location` AS `ua` ON (`ua`.`idtbl_location` = `u`.`tbl_location_idtbl_location`)
    LEFT JOIN `tbl_warehouse` AS `uw` ON (`uw`.`idtbl_warehouse` = `u`.`warehouse_id`)
    LEFT JOIN `tbl_product` AS `ub` ON (`ub`.`idtbl_product` = `u`.`tbl_product_idtbl_product`)
    LEFT JOIN `tbl_unit` AS `uu` ON (`uu`.`idtbl_unit` = `ub`.`tbl_unit_idtbl_unit`)";

$extraWhere = "`u`.`status` IN (1, 2)";

// Optional company filter (send company_id from the DataTable ajax data, like your GRN list)
if (isset($_POST['company_id']) && $_POST['company_id'] !== '') {
    $extraWhere .= " AND `u`.`tbl_company_idtbl_company` = " . intval($_POST['company_id']);
}

// Optional: hide empty stock rows
if (isset($_POST['hide_empty']) && $_POST['hide_empty'] == 1) {
    $extraWhere .= " AND `u`.`qty` > 0";
}

echo json_encode(
    SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere)
);

?>