<?php

/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

// DB table to use
$table = 'tbl_print_grn';

// Table's primary key
$primaryKey = 'idtbl_print_grn';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
	array( 'db' => '`u`.`idtbl_print_grn`', 'dt' => 'idtbl_print_grn', 'field' => 'idtbl_print_grn' ),
	array( 'db' => '`u`.`batchno`', 'dt' => 'batchno', 'field' => 'batchno' ),
	array( 'db' => '`u`.`grndate`', 'dt' => 'grndate', 'field' => 'grndate' ),
	array( 'db' => '`u`.`totalcost`', 'dt' => 'totalcost', 'field' => 'totalcost' ),
	array( 'db' => '`u`.`grn_no`', 'dt' => 'grn_no', 'field' => 'grn_no' ),
	array( 'db' => '`u`.`approvestatus`', 'dt' => 'approvestatus', 'field' => 'approvestatus' ),
	array( 'db' => '`ua`.`suppliername`', 'dt' => 'suppliername', 'field' => 'suppliername' ),
	array( 'db' => '`ub`.`location`', 'dt' => 'location', 'field' => 'location' ),
	array( 'db' => '`uc`.`group`', 'dt' => 'group', 'field' => 'group' ),
	array( 'db' => '`u`.`status`', 'dt' => 'status', 'field' => 'status' ),
	array( 'db' => '`u`.`check_by`', 'dt' => 'check_by', 'field' => 'check_by' ),
	array( 'db' => '`ue`.`name`', 'dt' => 'name', 'field' => 'name' ),
	array( 'db' => '`ud`.`porder_no`', 'dt' => 'porder_no', 'field' => 'porder_no' ),
	array(
        'db' => "CONCAT(
            CASE 
                WHEN `u`.`approvestatus` = 1 THEN '<i class=\"fas fa-check text-success mr-2\"></i>Approved GRN'
                WHEN `u`.`approvestatus` = 2 THEN '<i class=\"fa fa-times text-danger mr-2\"></i>Reject GRN'
                ELSE '<i class=\"fa fa-spinner text-warning mr-2\"></i>Pending GRN for Approval'
            END
        )",
        'dt' => 'approvestatus_display',
        'field' => 'approvestatus_display',
        'as' => 'approvestatus_display'
		),
);

// SQL server connection information
require('config.php');
$sql_details = array(
	'user' => $db_username,
	'pass' => $db_password,
	'db'   => $db_name,
	'host' => $db_host
);

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

// require( 'ssp.class.php' );
require('ssp.customized.class.php' );
$companyID = $_POST['company_id'];

$joinQuery = "FROM `tbl_print_grn` AS `u` LEFT JOIN `tbl_supplier` AS `ua` ON (`ua`.`idtbl_supplier` = `u`.`tbl_supplier_idtbl_supplier`) LEFT JOIN `tbl_location` AS `ub` ON (`ub`.`idtbl_location` = `u`.`tbl_location_idtbl_location`) LEFT JOIN `tbl_material_group` AS `uc` ON (`uc`.`idtbl_material_group` = `u`.`tbl_material_group_idtbl_material_group`) LEFT JOIN `tbl_print_porder` AS `ud` ON (`ud`.`idtbl_print_porder` = `u`.`tbl_print_porder_idtbl_print_porder`) LEFT JOIN `tbl_user` AS `ue` ON (`ue`.`idtbl_user` = `u`.`approve_by`)";

$extraWhere = "`u`.`status` IN (1,2) AND `u`.`tbl_company_idtbl_company`='$companyID'";

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere)
);
