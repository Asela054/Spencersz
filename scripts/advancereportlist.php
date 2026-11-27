<?php
/*
 * All Stock View data (JSON)
 * Prices and totals come ONLY from tbl_grndetail and tbl_grn (approved GRNs).
 * tbl_stock is used only for the quantity still on hand.
 *
 * cost_price = GRN line total / GRN line qty   (net of line discount and free items)
 *              x (tbl_grn.total / sum of that GRN's line totals)   [header adjustment]
 */
require('config.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* true  = spread the GRN header adjustment over the lines (matches GRN & Return report)
 * false = plain line cost (matches the Purchases tab) */
$applyHeaderAdjustment = true;

try {

    $branchID = (int) ($_POST['branch_id'] ?? 0);

    $conn = new mysqli($db_host, $db_username, $db_password, $db_name);
    $conn->set_charset('utf8mb4');

    /* all branches: only when requested AND the user holds allstockview-check */
    $allBranches = false;
    if (($_POST['all_branches'] ?? '') === '1') {
        $uid = (int) ($_SESSION['userid'] ?? 0);
        if ($uid > 0) {
            $pc = $conn->prepare(
                "SELECT 1
                 FROM tbl_user_has_tbl_roles ur
                 JOIN tbl_roles_has_tbl_permissions rp
                      ON rp.tbl_roles_idtbl_roles = ur.tbl_roles_idtbl_roles
                 JOIN tbl_permissions p
                      ON p.idtbl_permissions = rp.tbl_permissions_idtbl_permissions
                 WHERE ur.tbl_user_idtbl_user = ?
                   AND p.permission = 'allstockview-check'
                   AND p.guard_name = 'web-erp'
                 LIMIT 1"
            );
            $pc->bind_param('i', $uid);
            $pc->execute();
            $allBranches = $pc->get_result()->num_rows > 0;
            $pc->close();
        }
    }

    $branchCond = $allBranches ? '' : 'AND tbl_company_branch_idtbl_company_branch = ?';

    $factorSql = $applyHeaderAdjustment
        ? "IF(gt.lines_total > 0 AND g.total > 0, g.total / gt.lines_total, 1)"
        : "1";

    $orderSql = $allBranches
        ? "ORDER BY p.product_name, p.model_no, l.branch, g.grn_no"
        : "ORDER BY p.product_name, p.model_no, g.grn_no";

    $sql = "SELECT
                CONCAT(g.idtbl_grn, '-', gl.pid, '-', st.loc_id) AS idtbl_stock,
                g.grn_no,
                st.qty                                       AS qty,
                p.product_code,
                p.product_name,
                p.model_no,
                un.unit,
                l.branch                                     AS wh_name,
                sup.idtbl_supplier                           AS supplier_id,
                sup.suppliername                             AS supplier_name,
                IF(gl.qty > 0, gl.total / gl.qty, 0) * $factorSql AS cost_price,
                IF(gl.qty > 0, gl.price_value / gl.qty, 0)   AS grn_unit_price
            FROM (
                /* approved GRN lines, one row per GRN + product */
                SELECT gd.tbl_grn_idtbl_grn          AS grn_id,
                       gd.tbl_product_idtbl_product  AS pid,
                       SUM(gd.qty)                   AS qty,
                       SUM(gd.total)                 AS total,
                       SUM(gd.qty * gd.unitprice)    AS price_value
                FROM tbl_grndetail gd
                JOIN tbl_grn gg ON gg.idtbl_grn = gd.tbl_grn_idtbl_grn
                WHERE gd.status = 1
                  AND gg.status = 1
                  AND gg.approvestatus = 1
                GROUP BY gd.tbl_grn_idtbl_grn, gd.tbl_product_idtbl_product
            ) AS gl
            JOIN tbl_grn g ON g.idtbl_grn = gl.grn_id
            JOIN (
                /* sum of all line totals per GRN, to compare with tbl_grn.total */
                SELECT tbl_grn_idtbl_grn AS grn_id, SUM(total) AS lines_total
                FROM tbl_grndetail
                WHERE status = 1
                GROUP BY tbl_grn_idtbl_grn
            ) AS gt ON gt.grn_id = g.idtbl_grn
            JOIN tbl_supplier sup ON sup.idtbl_supplier = g.tbl_supplier_idtbl_supplier
            JOIN (
                /* quantity only: what is still on hand per GRN + product + branch */
                SELECT tbl_grn_idtbl_grn                        AS grn_id,
                       tbl_product_idtbl_product                AS pid,
                       tbl_company_branch_idtbl_company_branch  AS loc_id,
                       SUM(qty)                                 AS qty
                FROM tbl_stock
                WHERE status IN (1,2)
                  AND qty > 0
                  $branchCond
                GROUP BY tbl_grn_idtbl_grn, tbl_product_idtbl_product, tbl_company_branch_idtbl_company_branch
            ) AS st ON st.grn_id = gl.grn_id AND st.pid = gl.pid
            LEFT JOIN tbl_product         p  ON p.idtbl_product = gl.pid
            LEFT JOIN tbl_unit            un ON un.idtbl_unit   = p.tbl_unit_idtbl_unit
            LEFT JOIN tbl_company_branch  l  ON l.idtbl_company_branch = st.loc_id
            $orderSql";

    $stmt = $conn->prepare($sql);
    if (!$allBranches) {
        $stmt->bind_param('i', $branchID);
    }
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