<?php
/*
 * Data endpoint for the Reservation reports (JSON).
 * POST: report = reservation
 *       view   = list | stock
 *
 * tbl_reservation.reserve_status : 1 = Open, 2 = Converted, 3 = Cancelled
 * tbl_reservation.status         : 1 = active record (3 = deleted)
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
    $cust   = (int)($_POST['customer_id'] ?? 0);
    $pid    = (int)($_POST['product_id'] ?? 0);
    $branch = (int)($_POST['branch_id'] ?? 0);

    switch ($report) {

    case 'reservation':
        $view = ($_POST['view'] ?? '') === 'stock' ? 'stock' : 'list';

        /* ---- RESERVED STOCK (open reservations only, as of today) ---- */
        if ($view === 'stock') {
            $w = "r.reserve_status = 1 AND r.status = 1 AND d.status = 1";
            $sb = "";
            if ($branch > 0) { $w .= " AND r.tbl_company_branch_idtbl_company_branch = $branch"; $sb = " AND s.tbl_company_branch_idtbl_company_branch = $branch"; }
            if ($pid > 0)    $w .= " AND d.tbl_product_idtbl_product = $pid";

            $rows = fetch($conn, "SELECT p.idtbl_product, p.product_code, p.product_name, c.category,
                        COUNT(DISTINCT r.idtbl_reservation) AS reservations,
                        SUM(d.qty) AS reserved, SUM(d.line_net_total) AS value, MIN(r.expiry_date) AS next_expiry,
                        (SELECT COALESCE(SUM(s.qty), 0) FROM tbl_stock s
                          WHERE s.tbl_product_idtbl_product = p.idtbl_product AND s.status = 1 AND s.stock_status = 1 $sb) AS in_stock
                 FROM tbl_reservation_detail d
                 JOIN tbl_reservation r ON r.idtbl_reservation = d.tbl_reservation_idtbl_reservation
                 JOIN tbl_product p ON p.idtbl_product = d.tbl_product_idtbl_product
                 LEFT JOIN tbl_category c ON c.idtbl_category = p.tbl_category_idtbl_category
                 WHERE $w
                 GROUP BY p.idtbl_product, p.product_code, p.product_name, c.category
                 ORDER BY reserved DESC");
            echo json_encode(['data' => $rows, 'view' => 'stock']);
            break;
        }

        /* ---- RESERVATION LIST ---- */
        $filter = $_POST['rstatus'] ?? '';   // open | expired | converted | cancelled
        $w = "r.status = 1 AND r.reservation_date BETWEEN '$from' AND '$to'";
        if ($cust > 0)   $w .= " AND r.tbl_customer_idtbl_customer = $cust";
        if ($branch > 0) $w .= " AND r.tbl_company_branch_idtbl_company_branch = $branch";
        if ($pid > 0)    $w .= " AND EXISTS (SELECT 1 FROM tbl_reservation_detail x
                                              WHERE x.tbl_reservation_idtbl_reservation = r.idtbl_reservation
                                                AND x.status = 1 AND x.tbl_product_idtbl_product = $pid)";

        $rows = fetch($conn, "SELECT r.idtbl_reservation, r.reservation_no, r.reservation_date, r.expiry_date,
                    r.total_qty, r.total_amount, r.advance_amount, r.reserve_status, r.remark,
                    c.customer, cb.branch, i.invoice_no, i.invoice_date,
                    DATEDIFF(r.expiry_date, CURDATE()) AS days_left,
                    (SELECT GROUP_CONCAT(CONCAT(p.product_name, ' x', ROUND(d.qty, 2)) ORDER BY d.idtbl_reservation_detail SEPARATOR ', ')
                       FROM tbl_reservation_detail d JOIN tbl_product p ON p.idtbl_product = d.tbl_product_idtbl_product
                      WHERE d.tbl_reservation_idtbl_reservation = r.idtbl_reservation AND d.status = 1) AS items
             FROM tbl_reservation r
             JOIN tbl_customer c ON c.idtbl_customer = r.tbl_customer_idtbl_customer
             LEFT JOIN tbl_company_branch cb ON cb.idtbl_company_branch = r.tbl_company_branch_idtbl_company_branch
             LEFT JOIN tbl_invoice i ON i.idtbl_invoice = r.tbl_invoice_idtbl_invoice
             WHERE $w
             ORDER BY r.reservation_date DESC, r.idtbl_reservation DESC");

        $out = [];
        foreach ($rows as $r) {
            $s = (int)$r['reserve_status'];
            if ($s === 2)      $r['rstate'] = 'converted';
            elseif ($s === 3)  $r['rstate'] = 'cancelled';
            else               $r['rstate'] = ((int)$r['days_left'] < 0) ? 'expired' : 'open';
            if ($filter !== '' && $filter !== $r['rstate']) continue;
            $out[] = $r;
        }
        echo json_encode(['data' => $out, 'view' => 'list']);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown report']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}