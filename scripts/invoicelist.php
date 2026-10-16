<?php
require_once '../external.php';

$CI =& get_instance();
$CI->load->library('session');

// DB table to use
$table = 'tbl_invoice';

// Table's primary key
$primaryKey = 'idtbl_invoice';

$columns = array(
	array( 'db' => '`u`.`idtbl_invoice`',   'dt' => 'idtbl_invoice',   'field' => 'idtbl_invoice' ),
	array( 'db' => '`u`.`invoice_no`',      'dt' => 'invoice_no',      'field' => 'invoice_no' ),
	array( 'db' => '`u`.`invoice_date`',    'dt' => 'invoice_date',    'field' => 'invoice_date' ),
	array( 'db' => '`u`.`invoice_type`',    'dt' => 'invoice_type',    'field' => 'invoice_type' ),
	array( 'db' => '`u`.`customer_name`',   'dt' => 'customer_name',   'field' => 'customer_name' ),
	array( 'db' => '`u`.`net_total`',       'dt' => 'net_total',       'field' => 'net_total' ),
	array( 'db' => '`u`.`paid_total`',      'dt' => 'paid_total',      'field' => 'paid_total' ),
	array( 'db' => '`u`.`balance_due`',     'dt' => 'balance_due',     'field' => 'balance_due' ),
	array( 'db' => '`u`.`payment_status`',  'dt' => 'payment_status',  'field' => 'payment_status' )
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

$joinQuery  = "FROM `tbl_invoice` AS `u`";
$extraWhere = "`u`.`status` = 1 AND `u`.`tbl_company_idtbl_company` = $companyid AND `u`.`tbl_company_branch_idtbl_company_branch` = $branchid";

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere)
);