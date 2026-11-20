<?php
/*
 * Returns all stock lines (JSON) with supplier, for the All Stock View report
 */
require('config.php');

header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    $branchID = (int) ($_POST['branch_id'] ?? 0);

    $conn = new mysqli($db_host, $db_username, $db_password, $db_name);
    $conn->set_charset('utf8mb4');

    $sql = "SELECT
                `s`.`idtbl_stock`,
                `s`.`batchno`,
                `s`.`qty`,
                `s`.`costunitprice`,
                `s`.`saleprice`,
                `p`.`product_code`,
                `p`.`product_name`,
                `un`.`unit`,
                `l`.`location` AS `wh_name`,
                `sup`.`idtbl_supplier` AS `supplier_id`,
                `sup`.`suppliername` AS `supplier_name`
            FROM `tbl_stock` AS `s`
            LEFT JOIN `tbl_product`  AS `p`   ON `p`.`idtbl_product`    = `s`.`tbl_product_idtbl_product`
            LEFT JOIN `tbl_unit`     AS `un`  ON `un`.`idtbl_unit`      = `p`.`tbl_unit_idtbl_unit`
            LEFT JOIN `tbl_location` AS `l`   ON `l`.`idtbl_location`   = `s`.`tbl_location_idtbl_location`
            LEFT JOIN `tbl_grn`      AS `g`   ON `g`.`idtbl_grn`        = `s`.`tbl_grn_idtbl_grn`
            LEFT JOIN `tbl_supplier` AS `sup` ON `sup`.`idtbl_supplier` = `g`.`tbl_supplier_idtbl_supplier`
            WHERE `s`.`status` IN (1,2)
              AND `s`.`tbl_company_branch_idtbl_company_branch` = ?
              AND `s`.`qty` > 0
            ORDER BY `p`.`product_name`, `s`.`batchno`";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $branchID);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($r = $result->fetch_assoc()) {
        $rows[] = $r;
    }

    echo json_encode(['data' => $rows]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}