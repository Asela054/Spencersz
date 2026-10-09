<?php
/*
 * Returns all stock lines (JSON) with category, for the All Stock View report
 */
require('config.php');

header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

$companyID = (int) ($_POST['company_id'] ?? 0);

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
            `un`.`unit`
        FROM `tbl_stock` AS `s`
        LEFT JOIN `tbl_product`   AS `p`  ON `p`.`idtbl_product`    = `s`.`tbl_product_idtbl_product`
        LEFT JOIN `tbl_unit`      AS `un` ON `un`.`idtbl_unit`      = `p`.`tbl_unit_idtbl_unit`
        WHERE `s`.`status` IN (1,2)
          AND `s`.`tbl_company_idtbl_company` = ?
          AND `s`.`qty` > 0
        ORDER BY `p`.`product_name`, `s`.`batchno`";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $companyID);
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