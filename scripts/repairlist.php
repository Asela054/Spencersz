<?php
require_once '../external.php';

$CI =& get_instance();
$CI->load->library('session');

$table      = 'tbl_repair_job';
$primaryKey = 'idtbl_repair_job';

$columns = array(
	array( 'db' => '`u`.`idtbl_repair_job`', 'dt' => 'idtbl_repair_job', 'field' => 'idtbl_repair_job' ),
	array( 'db' => '`u`.`job_no`',           'dt' => 'job_no',           'field' => 'job_no' ),
	array( 'db' => '`u`.`job_date`',         'dt' => 'job_date',         'field' => 'job_date' ),
	array( 'db' => '`u`.`repair_type`',      'dt' => 'repair_type',      'field' => 'repair_type' ),
	array( 'db' => '`u`.`warranty_status`',  'dt' => 'warranty_status',  'field' => 'warranty_status' ),
	array( 'db' => '`u`.`warranty_end`',     'dt' => 'warranty_end',     'field' => 'warranty_end' ),
	array( 'db' => '`u`.`product_name`',     'dt' => 'product_name',     'field' => 'product_name' ),
	array( 'db' => '`u`.`serialno`',         'dt' => 'serialno',         'field' => 'serialno' ),
	array( 'db' => '`u`.`customer_name`',    'dt' => 'customer_name',    'field' => 'customer_name' ),
	array( 'db' => '`u`.`customer_phone`',   'dt' => 'customer_phone',   'field' => 'customer_phone' ),
	array( 'db' => '`u`.`total_charge`',     'dt' => 'total_charge',     'field' => 'total_charge' ),
	array( 'db' => '`u`.`job_status`',       'dt' => 'job_status',       'field' => 'job_status' )
);

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

$joinQuery  = "FROM `tbl_repair_job` AS `u`";
$extraWhere = "`u`.`status` = 1 AND `u`.`tbl_company_idtbl_company` = $companyid AND `u`.`tbl_company_branch_idtbl_company_branch` = $branchid";

// status filter from the page: 'open' = not delivered / cancelled
$js = isset($_POST['job_status']) ? $_POST['job_status'] : '';
if ($js === 'open')        { $extraWhere .= " AND `u`.`job_status` IN (1, 2, 3, 4)"; }
elseif ((int)$js > 0)      { $extraWhere .= " AND `u`.`job_status` = " . (int)$js; }

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere)
);