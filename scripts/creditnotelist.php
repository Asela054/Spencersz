<?php
require_once '../external.php';

$CI =& get_instance();
$CI->load->library('session');

// DB table to use
$table = 'tbl_credit_note';

// Table's primary key
$primaryKey = 'idtbl_credit_note';

// Columns sent back to DataTables ('dt' must match the "data" names in the page's DataTable)
$columns = array(
	array( 'db' => '`cn`.`idtbl_credit_note`', 'dt' => 'idtbl_credit_note', 'field' => 'idtbl_credit_note' ),
	array( 'db' => '`cn`.`creditnote_no`',     'dt' => 'creditnote_no',     'field' => 'creditnote_no' ),
	array( 'db' => '`cn`.`creditnote_date`',   'dt' => 'creditnote_date',   'field' => 'creditnote_date' ),
	array( 'db' => '`cn`.`totalamount`',       'dt' => 'totalamount',       'field' => 'totalamount' ),
	array( 'db' => '`cn`.`settle_status`',     'dt' => 'settle_status',     'field' => 'settle_status' ),
	array( 'db' => '`i`.`invoice_no`',         'dt' => 'invoice_no',        'field' => 'invoice_no' ),
	array( 'db' => '`i`.`customer_name`',      'dt' => 'customer_name',     'field' => 'customer_name' ),
	array( 'db' => '`s`.`invoice_no`',         'dt' => 'settle_invoice_no', 'field' => 'settle_invoice_no', 'as' => 'settle_invoice_no' ),
    array( 'db' => '`cn`.`approve_status`', 'dt' => 'approve_status', 'field' => 'approve_status' )
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

$companyid = (int)$_SESSION['company_id'];
$branchid  = (int)$_SESSION['branch_id'];

// i = original invoice, s = the invoice that used this credit note (empty while still open)
$joinQuery = "FROM `tbl_credit_note` AS `cn`
              INNER JOIN `tbl_invoice` AS `i` ON `i`.`idtbl_invoice` = `cn`.`tbl_invoice_idtbl_invoice`
              LEFT JOIN `tbl_invoice` AS `s` ON `s`.`idtbl_invoice` = `cn`.`settle_invoice_id`";

$extraWhere = "`cn`.`status` = 1
               AND `i`.`tbl_company_idtbl_company` = $companyid
               AND `i`.`tbl_company_branch_idtbl_company_branch` = $branchid";

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere)
);