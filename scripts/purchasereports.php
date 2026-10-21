<?php
/*
 * Data endpoint for the 3 Purchasing reports (JSON).
 * POST: report = purchase | postatus | grnret
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
    $from   = vdate($_POST['from'] ?? '', date('Y-m-01'));
    $to     = vdate($_POST['to'] ?? '', date('Y-m-d'));
    $sup    = (int)($_POST['supplier_id'] ?? 0);
    $pid    = (int)($_POST['product_id'] ?? 0);
    $branch = (int)($_POST['branch_id'] ?? 0);

    switch ($report) {

    /* 16. PURCHASE REPORT (approved GRNs, value excludes VAT) --------- */
        case 'purchase':
        $group = in_array($_POST['group'] ?? '', ['supplier', 'product', 'month', 'detail']) ? $_POST['group'] : 'supplier';
        $w = "g.status = 1 AND g.approvestatus = 1 AND gd.status = 1 AND g.grndate BETWEEN '$from' AND '$to'";
        if ($sup > 0)    $w .= " AND g.tbl_supplier_idtbl_supplier = $sup";
        if ($pid > 0)    $w .= " AND gd.tbl_product_idtbl_product = $pid";
        if ($branch > 0) $w .= " AND g.tbl_company_branch_idtbl_company_branch = $branch";
        $from_sql = "FROM tbl_grndetail gd
             JOIN tbl_grn g ON g.idtbl_grn = gd.tbl_grn_idtbl_grn
             JOIN tbl_supplier s ON s.idtbl_supplier = g.tbl_supplier_idtbl_supplier
             JOIN tbl_product p ON p.idtbl_product = gd.tbl_product_idtbl_product
             LEFT JOIN tbl_category c ON c.idtbl_category = p.tbl_category_idtbl_category
             WHERE $w";

        $totals = fetch($conn, "SELECT COUNT(DISTINCT g.idtbl_grn) AS grns, COUNT(DISTINCT s.idtbl_supplier) AS suppliers,
                    COALESCE(SUM(gd.qty),0) AS qty, COALESCE(SUM(gd.total),0) AS value $from_sql")[0];

        $items = [];
        switch ($group) {
        case 'product':
            $sql = "SELECT p.idtbl_product, p.product_code, p.product_name, c.category, COUNT(DISTINCT s.idtbl_supplier) AS suppliers,
                           SUM(gd.qty) AS qty, SUM(gd.total) AS value, MAX(g.grndate) AS last_in
                    $from_sql GROUP BY p.idtbl_product, p.product_code, p.product_name, c.category ORDER BY value DESC";
            /* price-wise: one line per unit price */
            $items = fetch($conn, "SELECT p.idtbl_product AS k, gd.unitprice, COUNT(DISTINCT s.idtbl_supplier) AS suppliers,
                           COUNT(DISTINCT g.idtbl_grn) AS grns, SUM(gd.qty) AS qty, SUM(gd.total) AS value, MAX(g.grndate) AS last_in
                    $from_sql GROUP BY p.idtbl_product, gd.unitprice ORDER BY p.idtbl_product, gd.unitprice DESC");
            break;
        case 'month':
            $sql = "SELECT DATE_FORMAT(g.grndate, '%Y-%m') AS ym, COUNT(DISTINCT g.idtbl_grn) AS grns, SUM(gd.qty) AS qty, SUM(gd.total) AS value
                    $from_sql GROUP BY DATE_FORMAT(g.grndate, '%Y-%m') ORDER BY ym";
            $items = fetch($conn, "SELECT DATE_FORMAT(g.grndate, '%Y-%m') AS k, p.product_code, p.product_name, gd.unitprice,
                           COUNT(DISTINCT g.idtbl_grn) AS grns, SUM(gd.qty) AS qty, SUM(gd.total) AS value
                    $from_sql GROUP BY DATE_FORMAT(g.grndate, '%Y-%m'), p.idtbl_product, p.product_code, p.product_name, gd.unitprice
                    ORDER BY k, value DESC");
            break;
        case 'detail':
            $sql = "SELECT g.grndate, g.grn_no, g.invoicenum, s.suppliername, p.product_code, p.product_name, gd.qty, gd.unitprice, gd.total AS value
                    $from_sql ORDER BY g.grndate DESC, g.idtbl_grn DESC LIMIT 5000";
            break;
        default:
            $sql = "SELECT s.idtbl_supplier, s.suppliername, COUNT(DISTINCT g.idtbl_grn) AS grns, SUM(gd.qty) AS qty, SUM(gd.total) AS value
                    $from_sql GROUP BY s.idtbl_supplier, s.suppliername ORDER BY value DESC";
            $items = fetch($conn, "SELECT s.idtbl_supplier AS k, p.product_code, p.product_name, gd.unitprice,
                           COUNT(DISTINCT g.idtbl_grn) AS grns, SUM(gd.qty) AS qty, SUM(gd.total) AS value
                    $from_sql GROUP BY s.idtbl_supplier, p.idtbl_product, p.product_code, p.product_name, gd.unitprice
                    ORDER BY s.idtbl_supplier, value DESC");
        }
        echo json_encode(['data' => fetch($conn, $sql), 'items' => $items, 'totals' => $totals, 'group' => $group]);
        break;

    /* 17. PO STATUS ---------------------------------------------------- */
    case 'postatus':
        $filter = $_POST['pstatus'] ?? '';
        $w = "po.status = 1 AND po.orderdate BETWEEN '$from' AND '$to'";
        if ($sup > 0)    $w .= " AND po.tbl_supplier_idtbl_supplier = $sup";
        if ($branch > 0) $w .= " AND po.tbl_company_branch_idtbl_company_branch = $branch";
        $rows = fetch($conn, "SELECT po.idtbl_porder, po.porder_no, po.orderdate, po.nettotal, po.confirmstatus, s.suppliername, cb.branch,
                    SUM(d.qty) AS ordered, SUM(LEAST(d.qty, COALESCE(r.qty, 0))) AS received,
                    DATEDIFF(CURDATE(), po.orderdate) AS age_days
             FROM tbl_porder po
             JOIN tbl_supplier s ON s.idtbl_supplier = po.tbl_supplier_idtbl_supplier
             LEFT JOIN tbl_company_branch cb ON cb.idtbl_company_branch = po.tbl_company_branch_idtbl_company_branch
             JOIN tbl_porder_detail d ON d.tbl_porder_idtbl_porder = po.idtbl_porder AND d.status = 1
             LEFT JOIN (SELECT g.tbl_porder_idtbl_porder AS poid, gd.tbl_product_idtbl_product AS pid, SUM(gd.qty) AS qty
                          FROM tbl_grn g JOIN tbl_grndetail gd ON gd.tbl_grn_idtbl_grn = g.idtbl_grn
                         WHERE g.status = 1 AND g.approvestatus = 1 AND gd.status = 1
                         GROUP BY g.tbl_porder_idtbl_porder, gd.tbl_product_idtbl_product) r
                    ON r.poid = po.idtbl_porder AND r.pid = d.tbl_product_idtbl_product
             WHERE $w
             GROUP BY po.idtbl_porder, po.porder_no, po.orderdate, po.nettotal, po.confirmstatus, s.suppliername, cb.branch
             ORDER BY po.orderdate DESC, po.idtbl_porder DESC");
        $out = [];
        foreach ($rows as $r) {
            if (!(int)$r['confirmstatus'])            $r['pstatus'] = 'pending';
            elseif ((float)$r['received'] <= 0)       $r['pstatus'] = 'open';
            elseif ((float)$r['received'] < (float)$r['ordered']) $r['pstatus'] = 'partial';
            else                                      $r['pstatus'] = 'received';
            if ($filter === 'outstanding' && $r['pstatus'] === 'received') continue;
            if ($filter !== '' && $filter !== 'outstanding' && $filter !== $r['pstatus']) continue;
            $out[] = $r;
        }
        echo json_encode(['data' => $out]);
        break;

    /* 18. GRN & GRN RETURN --------------------------------------------- */
    case 'grnret':
        $view = ($_POST['view'] ?? '') === 'docs' ? 'docs' : 'summary';
        $gs = $sup > 0 ? " AND g.tbl_supplier_idtbl_supplier = $sup" : "";
        $rs = $sup > 0 ? " AND r.tbl_supplier_idtbl_supplier = $sup" : "";
        if ($branch > 0) { $gs .= " AND g.tbl_company_branch_idtbl_company_branch = $branch"; $rs .= " AND r.tbl_company_branch_idtbl_company_branch = $branch"; }

        if ($view === 'docs') {
            $out = fetch($conn, "SELECT x.* FROM (
                  SELECT g.grndate AS d, 'GRN' AS doctype, g.grn_no AS docno, s.suppliername, g.invoicenum AS refno, g.total AS amount, g.remark AS note, g.insertdatetime AS ts
                    FROM tbl_grn g JOIN tbl_supplier s ON s.idtbl_supplier = g.tbl_supplier_idtbl_supplier
                   WHERE g.status = 1 AND g.approvestatus = 1 AND g.grndate BETWEEN '$from' AND '$to' $gs
                  UNION ALL
                  SELECT DATE(r.insertdatetime), 'GRN Return', CONCAT('GRN-RET #', r.idtbl_grn_return), s.suppliername, g.grn_no, r.totalpayment, r.remark, r.insertdatetime
                    FROM tbl_grn_return r JOIN tbl_supplier s ON s.idtbl_supplier = r.tbl_supplier_idtbl_supplier
                    LEFT JOIN tbl_grn g ON g.idtbl_grn = r.grn_no
                   WHERE r.status = 1 AND r.approvestatus = 1 AND DATE(r.insertdatetime) BETWEEN '$from' AND '$to' $rs
                ) x ORDER BY x.d DESC, x.ts DESC");
        } else {
            $out = fetch($conn, "SELECT s.idtbl_supplier, s.suppliername, COALESCE(a.cnt,0) AS grns, COALESCE(a.total,0) AS grn_total,
                        COALESCE(b.cnt,0) AS rets, COALESCE(b.total,0) AS ret_total
                 FROM tbl_supplier s
                 LEFT JOIN (SELECT g.tbl_supplier_idtbl_supplier AS sid, COUNT(*) AS cnt, SUM(g.total) AS total FROM tbl_grn g
                             WHERE g.status = 1 AND g.approvestatus = 1 AND g.grndate BETWEEN '$from' AND '$to' $gs GROUP BY g.tbl_supplier_idtbl_supplier) a ON a.sid = s.idtbl_supplier
                 LEFT JOIN (SELECT r.tbl_supplier_idtbl_supplier AS sid, COUNT(*) AS cnt, SUM(r.totalpayment) AS total FROM tbl_grn_return r
                             WHERE r.status = 1 AND r.approvestatus = 1 AND DATE(r.insertdatetime) BETWEEN '$from' AND '$to' $rs GROUP BY r.tbl_supplier_idtbl_supplier) b ON b.sid = s.idtbl_supplier
                WHERE a.cnt IS NOT NULL OR b.cnt IS NOT NULL ORDER BY grn_total DESC");
        }
        echo json_encode(['data' => $out, 'view' => $view]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown report']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
