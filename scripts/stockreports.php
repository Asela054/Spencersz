<?php
/*
 * Data endpoint for the 6 Stock Reports (JSON).
 * POST: report = current | card | reorder | transfer | dead | valuation
 */
require('config.php');

header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function vdate($v, $def) { return preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$v) ? $v : $def; }
function fetch($conn, $sql) { $o = []; $r = $conn->query($sql); while ($x = $r->fetch_assoc()) { $o[] = $x; } return $o; }

try {
    $conn = new mysqli($db_host, $db_username, $db_password, $db_name);
    $conn->set_charset('utf8mb4');

    $report = $_POST['report'] ?? '';
    $branch = (int)($_POST['branch_id'] ?? 0);          // 0 = all branches / warehouses
    $from   = vdate($_POST['from'] ?? '', date('Y-m-01'));
    $to     = vdate($_POST['to'] ?? '', date('Y-m-d'));
    $bs     = $branch > 0 ? " AND s.tbl_company_branch_idtbl_company_branch = $branch" : "";

    $prodJoins = "LEFT JOIN tbl_category c ON c.idtbl_category = p.tbl_category_idtbl_category
                  LEFT JOIN tbl_brand b ON b.idtbl_brand = p.tbl_brand_idtbl_brand
                  LEFT JOIN tbl_unit u ON u.idtbl_unit = p.tbl_unit_idtbl_unit";
    $out = [];

    switch ($report) {

    /* 7. CURRENT STOCK ------------------------------------------------ */
    case 'current':
        $split = !empty($_POST['split']);
        $bcols = $split ? "cb.branch AS branch, cb.type AS btype" : "'' AS branch, 0 AS btype";
        $bgrp  = $split ? ", cb.idtbl_company_branch, cb.branch, cb.type" : "";
        $out = fetch($conn, "SELECT p.idtbl_product, p.product_code, p.product_name, p.reorder_level,
                    c.category, b.brand, u.unit, $bcols,
                    SUM(s.qty) AS qty, SUM(s.qty * s.costunitprice) AS value
             FROM tbl_stock s
             JOIN tbl_product p ON p.idtbl_product = s.tbl_product_idtbl_product
             $prodJoins
             JOIN tbl_company_branch cb ON cb.idtbl_company_branch = s.tbl_company_branch_idtbl_company_branch
             WHERE s.status IN (1,2) AND s.qty > 0 $bs
             GROUP BY p.idtbl_product, p.product_code, p.product_name, p.reorder_level, c.category, b.brand, u.unit $bgrp
             ORDER BY p.product_name");
        echo json_encode(['data' => $out, 'split' => $split]);
        break;

    /* 8. STOCK CARD --------------------------------------------------- */
    case 'card':
        $pid = (int)($_POST['product_id'] ?? 0);
        if ($pid < 1) { echo json_encode(['data' => [], 'opening' => 0]); break; }

        $bm = [];
        foreach (fetch($conn, "SELECT idtbl_company_branch id, branch FROM tbl_company_branch") as $b) { $bm[$b['id']] = $b['branch']; }

        $bf = $branch > 0 ? " AND m.b = $branch" : "";
        $sql = "SELECT m.* FROM (
            SELECT g.grndate AS d, 'GRN' AS t, g.grn_no AS ref, g.tbl_company_branch_idtbl_company_branch AS b,
                   gd.qty AS qin, 0 AS qout, g.insertdatetime AS ts
              FROM tbl_grndetail gd JOIN tbl_grn g ON g.idtbl_grn = gd.tbl_grn_idtbl_grn
             WHERE gd.tbl_product_idtbl_product = $pid AND g.status = 1 AND g.approvestatus = 1
            UNION ALL
            SELECT DATE(r.insertdatetime), 'GRN Return', CONCAT('GRN-RET #', r.idtbl_grn_return),
                   r.tbl_company_branch_idtbl_company_branch, 0, rd.return_qty, r.insertdatetime
              FROM tbl_grn_return_detail rd JOIN tbl_grn_return r ON r.idtbl_grn_return = rd.tbl_grn_return_idtbl_grn_return
             WHERE rd.tbl_product_idtbl_product = $pid AND r.status = 1 AND r.approvestatus = 1
            UNION ALL
            SELECT i.invoice_date, 'Sale', i.invoice_no, i.tbl_company_branch_idtbl_company_branch, 0, d.qty, i.insertdatetime
              FROM tbl_invoice_detail d JOIN tbl_invoice i ON i.idtbl_invoice = d.tbl_invoice_idtbl_invoice
             WHERE d.tbl_product_idtbl_product = $pid AND i.status = 1 AND d.status = 1
            UNION ALL
            SELECT cn.creditnote_date, 'Sale Return', CONVERT(cn.creditnote_no USING utf8mb4),
                   i.tbl_company_branch_idtbl_company_branch, cd.qty, 0, cn.insertdatetime
              FROM tbl_credit_note_detail cd
              JOIN tbl_credit_note cn ON cn.idtbl_credit_note = cd.tbl_credit_note_idtbl_credit_note
              JOIN tbl_invoice i ON i.idtbl_invoice = cn.tbl_invoice_idtbl_invoice
             WHERE cd.tbl_product_idtbl_product = $pid AND cn.status = 1 AND cn.approve_status = 1 AND cd.restock = 1
            UNION ALL
            SELECT t.transfer_date, 'Transfer Out', CONVERT(t.transfer_no USING utf8mb4), t.from_branch_id, 0, td.qty, t.insertdatetime
              FROM tbl_stock_transfer_detail td JOIN tbl_stock_transfer t ON t.idtbl_stock_transfer = td.tbl_stock_transfer_idtbl_stock_transfer
             WHERE td.tbl_product_idtbl_product = $pid AND t.status = 1 AND t.transfer_status >= 3
            UNION ALL
            SELECT t.transfer_date, 'Transfer In', CONVERT(t.transfer_no USING utf8mb4), t.to_branch_id, td.qty, 0, t.updatedatetime
              FROM tbl_stock_transfer_detail td JOIN tbl_stock_transfer t ON t.idtbl_stock_transfer = td.tbl_stock_transfer_idtbl_stock_transfer
             WHERE td.tbl_product_idtbl_product = $pid AND t.status = 1 AND t.transfer_status = 6
          ) m WHERE m.d <= '$to' $bf ORDER BY m.d, m.ts";

        $opening = 0; $rows = [];
        foreach (fetch($conn, $sql) as $r) {
            if ($r['d'] < $from) { $opening += $r['qin'] - $r['qout']; continue; }
            $r['branch'] = $bm[$r['b']] ?? '';
            $rows[] = $r;
        }
        $pinfo = fetch($conn, "SELECT product_code, product_name FROM tbl_product WHERE idtbl_product = $pid");
        echo json_encode(['data' => $rows, 'opening' => $opening, 'product' => $pinfo[0] ?? null]);
        break;

    /* 9. LOW STOCK & REORDER ------------------------------------------ */
    case 'reorder':
        $out = fetch($conn, "SELECT p.idtbl_product, p.product_code, p.product_name, p.reorder_level, p.cost_price,
                    c.category, b.brand, u.unit, COALESCE(st.qty, 0) AS qty
             FROM tbl_product p $prodJoins
             LEFT JOIN (SELECT s.tbl_product_idtbl_product AS pid, SUM(s.qty) AS qty
                          FROM tbl_stock s WHERE s.status IN (1,2) $bs GROUP BY s.tbl_product_idtbl_product) st
                    ON st.pid = p.idtbl_product
             WHERE p.status = 1 AND COALESCE(st.qty, 0) <= p.reorder_level
             ORDER BY COALESCE(st.qty, 0) ASC, p.product_name");
        echo json_encode(['data' => $out]);
        break;

    /* 10. STOCK TRANSFERS --------------------------------------------- */
    case 'transfer':
        $tt = (int)($_POST['ttype'] ?? 0);
        $ts = (int)($_POST['tstatus'] ?? 0);
        $w  = "t.status = 1 AND t.transfer_date BETWEEN '$from' AND '$to'";
        if ($tt > 0) $w .= " AND t.transfer_type = $tt";
        if ($ts > 0) $w .= " AND t.transfer_status = $ts";
        if ($branch > 0) $w .= " AND (t.from_branch_id = $branch OR t.to_branch_id = $branch)";
        $out = fetch($conn, "SELECT t.transfer_no, t.transfer_date, t.transfer_type, t.transfer_status, t.total_qty,
                    t.vehicle_no, t.driver_name, fb.branch AS from_branch, tb.branch AS to_branch,
                    (SELECT GROUP_CONCAT(CONCAT(p.product_name, ' x ', td.qty) SEPARATOR '||')
                       FROM tbl_stock_transfer_detail td JOIN tbl_product p ON p.idtbl_product = td.tbl_product_idtbl_product
                      WHERE td.tbl_stock_transfer_idtbl_stock_transfer = t.idtbl_stock_transfer AND td.status = 1) AS items
             FROM tbl_stock_transfer t
             LEFT JOIN tbl_company_branch fb ON fb.idtbl_company_branch = t.from_branch_id
             LEFT JOIN tbl_company_branch tb ON tb.idtbl_company_branch = t.to_branch_id
             WHERE $w ORDER BY t.transfer_date DESC, t.idtbl_stock_transfer DESC");
        echo json_encode(['data' => $out]);
        break;

    /* 11. DEAD / SLOW-MOVING ------------------------------------------ */
    case 'dead':
        $days = max(0, (int)($_POST['days'] ?? 90));
        $bi   = $branch > 0 ? " AND i.tbl_company_branch_idtbl_company_branch = $branch" : "";
        $out = fetch($conn, "SELECT p.idtbl_product, p.product_code, p.product_name, c.category, b.brand, u.unit,
                    SUM(s.qty) AS qty, SUM(s.qty * s.costunitprice) AS value,
                    MIN(g.grndate) AS first_in, ls.last_sale,
                    DATEDIFF(CURDATE(), COALESCE(ls.last_sale, MIN(g.grndate), MIN(DATE(s.insertdatetime)))) AS days
             FROM tbl_stock s
             JOIN tbl_product p ON p.idtbl_product = s.tbl_product_idtbl_product
             $prodJoins
             LEFT JOIN tbl_grn g ON g.idtbl_grn = s.tbl_grn_idtbl_grn
             LEFT JOIN (SELECT d.tbl_product_idtbl_product AS pid, MAX(i.invoice_date) AS last_sale
                          FROM tbl_invoice_detail d JOIN tbl_invoice i ON i.idtbl_invoice = d.tbl_invoice_idtbl_invoice
                         WHERE i.status = 1 AND d.status = 1 $bi GROUP BY d.tbl_product_idtbl_product) ls
                    ON ls.pid = p.idtbl_product
             WHERE s.status IN (1,2) AND s.qty > 0 $bs
             GROUP BY p.idtbl_product, p.product_code, p.product_name, c.category, b.brand, u.unit, ls.last_sale
             HAVING days >= $days
             ORDER BY days DESC, value DESC");
        echo json_encode(['data' => $out, 'days' => $days]);
        break;

    /* 12. VALUATION BY CATEGORY & BRAND ------------------------------- */
    case 'valuation':
        $out = fetch($conn, "SELECT IFNULL(c.category, 'Uncategorised') AS category, IFNULL(b.brand, 'No brand') AS brand,
                    COUNT(DISTINCT p.idtbl_product) AS products, SUM(s.qty) AS qty, SUM(s.qty * s.costunitprice) AS value
             FROM tbl_stock s
             JOIN tbl_product p ON p.idtbl_product = s.tbl_product_idtbl_product
             $prodJoins
             WHERE s.status IN (1,2) AND s.qty > 0 $bs
             GROUP BY c.category, b.brand ORDER BY c.category, value DESC");
        echo json_encode(['data' => $out]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown report']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}