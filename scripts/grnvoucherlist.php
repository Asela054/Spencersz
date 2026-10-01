<?php
require_once '../external.php';

$CI =& get_instance();
$CI->load->library('session');
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
$table = 'tbl_grn_vouchar_import_cost';

// Table's primary key
$primaryKey = 'idtbl_grn_vouchar_import_cost';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
	array( 'db' => '`derived_table`.`idtbl_grn_vouchar_import_cost`', 'dt' => 'idtbl_grn_vouchar_import_cost', 'field' => 'idtbl_grn_vouchar_import_cost' ),
	array( 'db' => '`derived_table`.`date`', 'dt' => 'date', 'field' => 'date' ),
	array( 'db' => '`derived_table`.`total`', 'dt' => 'total', 'field' => 'total' ),
    array( 'db' => '`derived_table`.`grntotal`', 'dt' => 'grntotal', 'field' => 'grntotal' ),
	array( 'db' => '`derived_table`.`suppliername`', 'dt' => 'suppliername', 'field' => 'suppliername' ),
	array( 'db' => '`derived_table`.`grn_no`', 'dt' => 'grn_no', 'field' => 'grn_no' ),
	array( 'db' => '`derived_table`.`approvestatus`', 'dt' => 'approvestatus', 'field' => 'approvestatus' ),
	array( 'db' => '`derived_table`.`status`', 'dt' => 'status', 'field' => 'status' ),
	array( 'db' => '`derived_table`.`checkby`', 'dt' => 'checkby', 'field' => 'checkby' ),
	array( 'db' => '`derived_table`.`tbl_print_grn_idtbl_print_grn`', 'dt' => 'tbl_print_grn_idtbl_print_grn', 'field' => 'tbl_print_grn_idtbl_print_grn' ),
	array( 'db' => '`derived_table`.`name`', 'dt' => 'name', 'field' => 'name' ),
	array( 'db' => '`derived_table`.`status_display`', 'dt' => 'status_display', 'field' => 'status_display' )
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

$companyid=$_SESSION['company_id'];
$branchid=$_SESSION['branch_id'];

$joinQuery = "FROM (
    SELECT 
        u.*, 
        ua.grn_no,
        ub.suppliername,
        uc.name,
        CASE 
            WHEN u.approvestatus = 1 THEN 'GRN Voucher Approved'
            WHEN u.approvestatus = 2 THEN 'GRN Voucher Rejected'
            ELSE 'Pending'
        END AS status_display
    FROM `tbl_grn_vouchar_import_cost` AS `u` 
    LEFT JOIN `tbl_print_grn` AS `ua` ON (`ua`.`idtbl_print_grn` = `u`.`tbl_print_grn_idtbl_print_grn`)
    LEFT JOIN `tbl_supplier` AS `ub` ON (`ub`.`idtbl_supplier` = `ua`.`tbl_supplier_idtbl_supplier`)
    LEFT JOIN `tbl_user` AS `uc` ON (`uc`.`idtbl_user` = `u`.`approveby`)
    WHERE `u`.`status` IN (1, 2) 
      AND `u`.`tbl_company_idtbl_company` = '$companyid'
      AND `u`.`tbl_company_branch_idtbl_company_branch` = '$branchid'
    GROUP BY `u`.`idtbl_grn_vouchar_import_cost`
) AS derived_table";

$extraWhere = "";

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere)
);
