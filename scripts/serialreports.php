<?php
/*
 * Data endpoint for the 3 Serial Number reports (JSON).
 * POST: report = history | stock | sold
 */
require('config.php');

header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function fetch($conn, $sql) { $o = []; $r = $conn->query($sql); while ($x = $r->fetch_assoc()) { $o[] = $x; } return $o; }

try {
    $conn = new mysqli($db_host, $db_username, $db_password, $db_name);
    $conn->set_charset('utf8mb4');
    $report = $_POST['report'] ?? '';
    $q  = trim($_POST['q'] ?? '');
    $qe = $conn->real_escape_string($q);

    switch ($report) {

    /* 13. SERIAL NUMBER HISTORY --------------------------------------- */
    case 'history':
        if ($q === '' && empty($_POST['serial_id'])) { echo json_encode(['mode' => 'idle']); break; }
        $sid = (int)($_POST['serial_id'] ?? 0);
        $row = null;

        if ($sid < 1) {
            $m = fetch($conn, "SELECT ps.idtbl_product_serial AS id, ps.serialno, ps.stock_status, p.product_name, p.product_code
                  FROM tbl_product_serial ps JOIN tbl_product p ON p.idtbl_product = ps.tbl_product_idtbl_product
                 WHERE ps.serialno LIKE '%$qe%' ORDER BY (ps.serialno = '$qe') DESC, ps.serialno LIMIT 25");
            if (count($m) > 1 && $m[0]['serialno'] !== $q) { echo json_encode(['mode' => 'pick', 'matches' => $m]); break; }
            if (count($m) >= 1) { $sid = (int)$m[0]['id']; }
        }
        if ($sid > 0) {
            $info = fetch($conn, "SELECT ps.idtbl_product_serial AS id, ps.serialno, ps.stock_status, ps.costunitprice, ps.batchno,
                        p.product_code, p.product_name, p.warranty_months AS pwarranty, c.category, b.brand,
                        g.grn_no, g.grndate, s.suppliername, cb.branch AS location
                   FROM tbl_product_serial ps
                   JOIN tbl_product p ON p.idtbl_product = ps.tbl_product_idtbl_product
                   LEFT JOIN tbl_category c ON c.idtbl_category = p.tbl_category_idtbl_category
                   LEFT JOIN tbl_brand b ON b.idtbl_brand = p.tbl_brand_idtbl_brand
                   LEFT JOIN tbl_grn g ON g.idtbl_grn = ps.tbl_grn_idtbl_grn
                   LEFT JOIN tbl_supplier s ON s.idtbl_supplier = g.tbl_supplier_idtbl_supplier
                   LEFT JOIN tbl_company_branch cb ON cb.idtbl_company_branch = ps.tbl_company_branch_idtbl_company_branch
                  WHERE ps.idtbl_product_serial = $sid");
            $row = $info[0] ?? null;
        }
        $sn = $row ? $conn->real_escape_string($row['serialno']) : $qe;
        $events = [];

        if ($row) {
            $labels = [1 => ['Received into stock', 'ok'], 2 => ['Issued', 'info'], 3 => ['Returned by customer', 'warn'], 4 => ['Returned to supplier', 'bad'], 5 => ['Marked damaged', 'bad']];
            foreach (fetch($conn, "SELECT m.insertdatetime AS ts, m.movement_type, m.from_status, m.to_status, m.doc_type, m.doc_id, m.remark,
                        cb.branch, g.grn_no, t.transfer_no, u.name AS by_user
                   FROM tbl_serial_movement m
                   LEFT JOIN tbl_company_branch cb ON cb.idtbl_company_branch = m.branch_id
                   LEFT JOIN tbl_grn g ON m.doc_type = 'GRN' AND g.idtbl_grn = m.doc_id
                   LEFT JOIN tbl_stock_transfer t ON m.doc_type = 'TRANSFER' AND t.idtbl_stock_transfer = m.doc_id
                   LEFT JOIN tbl_user u ON u.idtbl_user = m.tbl_user_idtbl_user
                  WHERE m.tbl_product_serial_idtbl_product_serial = $sid
                    AND m.doc_type NOT IN ('INVOICE', 'CREDIT_NOTE', 'CN')
                  ORDER BY m.idtbl_serial_movement") as $m) {
                if ($m['movement_type'] == 6) { $l = $m['from_status'] == 1 ? ['Transfer dispatched', 'warn'] : ['Transfer received', 'info']; }
                else { $l = $labels[$m['movement_type']] ?? ['Movement', 'mute']; }
                $ref = $m['grn_no'] ?: ($m['transfer_no'] ?: ($m['doc_type'] === 'GRN_RETURN' ? 'GRN-RET #' . $m['doc_id'] : $m['doc_type'] . ' #' . $m['doc_id']));
                $events[] = ['key' => $m['ts'], 'when' => $m['ts'], 'event' => $l[0], 'tone' => $l[1], 'ref' => $ref, 'location' => $m['branch'],
                             'from' => $m['from_status'], 'to' => $m['to_status'], 'detail' => trim(($m['remark'] ?? '') . ($m['by_user'] ? ' (by ' . $m['by_user'] . ')' : ''))];
            }
        }

        $warranty = null;
        foreach (fetch($conn, "SELECT i.invoice_no, i.invoice_date, i.insertdatetime AS ts, i.customer_name, c.customer, c.telephone_no,
                    d.warranty_months, d.unitprice, cb.branch
               FROM tbl_invoice_detail d
               JOIN tbl_invoice i ON i.idtbl_invoice = d.tbl_invoice_idtbl_invoice
               LEFT JOIN tbl_customer c ON c.idtbl_customer = i.tbl_customer_idtbl_customer
               LEFT JOIN tbl_company_branch cb ON cb.idtbl_company_branch = i.tbl_company_branch_idtbl_company_branch
              WHERE d.serialno = '$sn' AND i.status = 1 AND d.status = 1 ORDER BY i.invoice_date, i.idtbl_invoice") as $s) {
            $cust = $s['customer_name'] !== '' && $s['customer_name'] !== null ? $s['customer_name'] : $s['customer'];
            $wm = (int)$s['warranty_months'];
            $end = $wm > 0 ? date('Y-m-d', strtotime($s['invoice_date'] . " +$wm months")) : null;
            $events[] = ['key' => $s['invoice_date'] . ' ' . substr($s['ts'], 11), 'when' => $s['invoice_date'], 'event' => 'Sold to customer', 'tone' => 'info',
                         'ref' => $s['invoice_no'], 'location' => $s['branch'], 'from' => null, 'to' => 2,
                         'detail' => $cust . ($s['telephone_no'] ? ' - ' . $s['telephone_no'] : '') . ' | Rs. ' . number_format($s['unitprice'], 2) . ' | Warranty ' . $wm . ' months'];
            $warranty = ['sold_on' => $s['invoice_date'], 'months' => $wm, 'ends' => $end, 'customer' => $cust, 'phone' => $s['telephone_no'], 'invoice' => $s['invoice_no'],
                         'left' => $end ? (int)floor((strtotime($end) - strtotime(date('Y-m-d'))) / 86400) : null];
        }

        foreach (fetch($conn, "SELECT cn.creditnote_no, cn.creditnote_date, cn.insertdatetime AS ts, cn.approve_status, cd.remark, cd.restock
               FROM tbl_credit_note_detail cd JOIN tbl_credit_note cn ON cn.idtbl_credit_note = cd.tbl_credit_note_idtbl_credit_note
              WHERE cd.serialno = '$sn' AND cn.status = 1") as $c) {
            $events[] = ['key' => $c['creditnote_date'] . ' ' . substr($c['ts'], 11), 'when' => $c['creditnote_date'], 'event' => 'Customer return (credit note)', 'tone' => 'warn',
                         'ref' => $c['creditnote_no'], 'location' => null, 'from' => 2, 'to' => $c['restock'] ? 5 : null,
                         'detail' => trim($c['remark'] . ' | ' . ($c['restock'] ? 'Restocked' : 'Not restocked') . ' | ' . ($c['approve_status'] ? 'Approved' : 'Awaiting approval'), ' |')];
        }

        if (!$row && !$events) { echo json_encode(['mode' => 'none']); break; }
        usort($events, function ($a, $b) { return strcmp($a['key'], $b['key']); });
        echo json_encode(['mode' => 'history', 'info' => $row, 'events' => $events, 'warranty' => $warranty, 'serialno' => $row['serialno'] ?? $q]);
        break;

    /* 14. SERIAL-WISE STOCK ------------------------------------------- */
    case 'stock':
        $branch = (int)($_POST['branch_id'] ?? 0);
        $pid    = (int)($_POST['product_id'] ?? 0);
        $st     = (int)($_POST['status'] ?? 1);
        $w = "ps.status IN (1,2)";
        if ($st > 0)     $w .= " AND ps.stock_status = $st";
        if ($branch > 0) $w .= " AND ps.tbl_company_branch_idtbl_company_branch = $branch";
        if ($pid > 0)    $w .= " AND ps.tbl_product_idtbl_product = $pid";
        if ($q !== '')   $w .= " AND (ps.serialno LIKE '%$qe%' OR p.product_name LIKE '%$qe%' OR p.product_code LIKE '%$qe%')";
        $out = fetch($conn, "SELECT ps.idtbl_product_serial AS id, ps.serialno, ps.stock_status, ps.costunitprice, ps.batchno, ps.insertdatetime,
                    p.product_code, p.product_name, c.category, b.brand, g.grn_no, g.grndate,
                    cb.branch, cb.type AS btype,
                    DATEDIFF(CURDATE(), COALESCE(g.grndate, DATE(ps.insertdatetime))) AS age
             FROM tbl_product_serial ps
             JOIN tbl_product p ON p.idtbl_product = ps.tbl_product_idtbl_product
             LEFT JOIN tbl_category c ON c.idtbl_category = p.tbl_category_idtbl_category
             LEFT JOIN tbl_brand b ON b.idtbl_brand = p.tbl_brand_idtbl_brand
             LEFT JOIN tbl_grn g ON g.idtbl_grn = ps.tbl_grn_idtbl_grn
             LEFT JOIN tbl_company_branch cb ON cb.idtbl_company_branch = ps.tbl_company_branch_idtbl_company_branch
             WHERE $w ORDER BY cb.branch, p.product_name, ps.serialno LIMIT 5000");
        echo json_encode(['data' => $out, 'capped' => count($out) >= 5000]);
        break;

    /* 15. SOLD SERIAL LOOKUP ------------------------------------------ */
    case 'sold':
        if ($q === '') { echo json_encode(['mode' => 'idle']); break; }
        $by = $_POST['by'] ?? 'serial';
        $cond = $by === 'invoice' ? "i.invoice_no LIKE '%$qe%'"
              : ($by === 'customer' ? "(i.customer_name LIKE '%$qe%' OR c.customer LIKE '%$qe%' OR c.telephone_no LIKE '%$qe%' OR i.customer_nic LIKE '%$qe%')"
              : "d.serialno LIKE '%$qe%'");
        $out = fetch($conn, "SELECT d.serialno, d.product_code, d.product_name, d.warranty_months, d.unitprice,
                    i.invoice_no, i.invoice_date, i.net_total, i.payment_status, i.customer_nic,
                    COALESCE(NULLIF(i.customer_name, ''), c.customer) AS customer, c.telephone_no, c.address_line1, c.city,
                    cb.branch,
                    DATE_ADD(i.invoice_date, INTERVAL d.warranty_months MONTH) AS w_end,
                    DATEDIFF(DATE_ADD(i.invoice_date, INTERVAL d.warranty_months MONTH), CURDATE()) AS w_left,
                    (SELECT COUNT(*) FROM tbl_credit_note_detail cd JOIN tbl_credit_note cn ON cn.idtbl_credit_note = cd.tbl_credit_note_idtbl_credit_note
                      WHERE cd.serialno = d.serialno AND cn.tbl_invoice_idtbl_invoice = i.idtbl_invoice AND cn.status = 1) AS returned
             FROM tbl_invoice_detail d
             JOIN tbl_invoice i ON i.idtbl_invoice = d.tbl_invoice_idtbl_invoice
             LEFT JOIN tbl_customer c ON c.idtbl_customer = i.tbl_customer_idtbl_customer
             LEFT JOIN tbl_company_branch cb ON cb.idtbl_company_branch = i.tbl_company_branch_idtbl_company_branch
             WHERE i.status = 1 AND d.status = 1 AND d.serialno <> '' AND $cond
             ORDER BY i.invoice_date DESC, d.serialno LIMIT 200");
        echo json_encode(['mode' => 'list', 'data' => $out]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown report']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
