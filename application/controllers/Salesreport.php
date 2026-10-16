<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Salesreport extends CI_Controller
{
    // CHANGE: the column of tbl_user that holds the user's name
    private $userNameCol = 'name';

    // revenue of one invoice line: VAT excluded, the invoice discount shared across its lines
    const REV  = "d.line_total * IF(i.gross_total > 0, i.net_total / i.gross_total, 1) / (1 + i.vat_rate / 100)";
    const COST = "d.costunitprice * d.qty";

    public function __construct()
    {
        parent::__construct();
        $this->load->model("Commeninfo");
    }

    public function index()
    {
        $result['menuaccess']=$this->Commeninfo->Getmenuprivilege();

        $company = (int)$_SESSION['company_id'];
        $u = $this->userNameCol;

        $result['branches'] = $this->db->query(
            "SELECT DISTINCT b.idtbl_company_branch AS id, b.code
               FROM tbl_invoice i
               JOIN tbl_company_branch b ON b.idtbl_company_branch = i.tbl_company_branch_idtbl_company_branch
              WHERE i.tbl_company_idtbl_company = ? ORDER BY b.code", [$company])->result();

        $result['cashiers'] = $this->db->query(
            "SELECT u.idtbl_user AS id, u.`$u` AS name FROM tbl_user u
              WHERE u.idtbl_user IN (SELECT DISTINCT i.tbl_user_idtbl_user FROM tbl_invoice i WHERE i.tbl_company_idtbl_company = ?)
              ORDER BY u.`$u`", [$company])->result();

        $result['customers'] = $this->db->query(
            "SELECT i.tbl_customer_idtbl_customer AS id, MAX(i.customer_name) AS name
               FROM tbl_invoice i
              WHERE i.tbl_company_idtbl_company = ? AND i.tbl_customer_idtbl_customer IS NOT NULL
              GROUP BY i.tbl_customer_idtbl_customer ORDER BY name", [$company])->result();

        $this->load->view('salesreport', $result);
    }

    public function Getreport()
    {
        $old_debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;       // SQL errors come back as exceptions, not an HTML error page

        try {
            $f = [
                'company'  => (int)$_SESSION['company_id'],
                'from'     => $this->_date($this->input->post('from')),
                'to'       => $this->_date($this->input->post('to')),
                'branch'   => (int)$this->input->post('branch'),      // 0 = all branches
                'cashier'  => (int)$this->input->post('cashier'),     // 0 = all cashiers
                'customer' => (int)$this->input->post('customer'),    // 0 = all customers
                'mode'     => (string)$this->input->post('mode'),
            ];
            if ($f['from'] > $f['to']) throw new RuntimeException('The From date is after the To date');

            switch ((int)$this->input->post('report')) {
                case 1:  $out = $this->_r_daily($f);     break;
                case 2:  $out = $this->_r_products($f);  break;
                case 3:  $out = $this->_r_profit($f);    break;
                case 4:  $out = $this->_r_customers($f); break;
                case 5:  $out = $this->_r_credit($f);    break;
                case 6:  $out = $this->_r_returns($f);   break;
                default: throw new RuntimeException('Unknown report');
            }

            echo json_encode(['status' => 1, 'variant' => $out[0], 'rows' => $out[1]]);

        } catch (RuntimeException $e) {
            $this->_jsonResponse(0, 'fas fa-exclamation-triangle', $e->getMessage(), 'danger');
        } catch (Throwable $e) {
            log_message('error', 'Salesreport failed: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            $this->_jsonResponse(0, 'fas fa-exclamation-triangle', 'Could not load the report. Please try again.', 'danger');
        } finally {
            $this->db->db_debug = $old_debug;
        }
    }

    // ---------------------------------------------------------------- 1. Daily sales
    private function _r_daily($f)
    {
        list($w, $b) = $this->_filters($f, 'i', true, true);
        $u = $this->userNameCol;

        $rows = $this->_q(
            "SELECT i.invoice_no, i.invoice_date, i.customer_name, u.`$u` AS cashier,
                    i.gross_total, i.discount, i.vatamount, i.net_total, i.balance_due AS credit,
                    COALESCE(p.cash, 0) AS cash, COALESCE(p.card, 0) AS card, COALESCE(p.cheque, 0) AS cheque,
                    COALESCE(p.transfer, 0) AS transfer, COALESCE(p.voucher, 0) AS voucher,
                    COALESCE(p.creditnote, 0) AS creditnote
               FROM tbl_invoice i
               LEFT JOIN tbl_user u ON u.idtbl_user = i.tbl_user_idtbl_user
               LEFT JOIN (
                    SELECT tbl_invoice_idtbl_invoice AS inv,
                           SUM(IF(group_type = 'cash', amount, 0))                    AS cash,
                           SUM(IF(group_type IN ('card', 'installment'), amount, 0))  AS card,
                           SUM(IF(group_type = 'cheque', amount, 0))                  AS cheque,
                           SUM(IF(group_type = 'transfer', amount, 0))                AS transfer,
                           SUM(IF(group_type = 'voucher', amount, 0))                 AS voucher,
                           SUM(IF(group_type = 'creditnote', amount, 0))              AS creditnote
                      FROM tbl_invoice_payment WHERE status = 1 GROUP BY tbl_invoice_idtbl_invoice
               ) p ON p.inv = i.idtbl_invoice
              WHERE 1 = 1 $w
              ORDER BY i.invoice_date, i.idtbl_invoice", $b);

        return ['main', $rows];
    }

    // ---------------------------------------------------------------- 2. Sales by product / brand / category
    private function _r_products($f)
    {
        list($w, $b) = $this->_filters($f, 'i', true, false);
        $mode = in_array($f['mode'], ['product', 'brand', 'category'], true) ? $f['mode'] : 'product';
        $rev = self::REV; $cost = self::COST;

        if ($mode === 'brand') {
            $join = "JOIN tbl_product p ON p.idtbl_product = d.tbl_product_idtbl_product
                     LEFT JOIN tbl_brand br ON br.idtbl_brand = p.tbl_brand_idtbl_brand";          // CHANGE
            $sel  = "COALESCE(br.brand, 'No brand') AS label";                                     // CHANGE
            $grp  = "COALESCE(br.brand, 'No brand')";
        } elseif ($mode === 'category') {
            $join = "JOIN tbl_product p ON p.idtbl_product = d.tbl_product_idtbl_product
                     LEFT JOIN tbl_category ct ON ct.idtbl_category = p.tbl_category_idtbl_category";   // CHANGE
            $sel  = "COALESCE(ct.category, 'No category') AS label";                                // CHANGE
            $grp  = "COALESCE(ct.category, 'No category')";
        } else {
            $join = "";
            $sel  = "d.product_code, d.product_name";
            $grp  = "d.tbl_product_idtbl_product, d.product_code, d.product_name";
        }

        $rows = $this->_q(
            "SELECT $sel, SUM(d.qty) AS qty, SUM($rev) AS revenue, SUM($cost) AS cost, SUM($rev) - SUM($cost) AS profit
               FROM tbl_invoice i
               JOIN tbl_invoice_detail d ON d.tbl_invoice_idtbl_invoice = i.idtbl_invoice AND d.status = 1
               $join
              WHERE 1 = 1 $w
              GROUP BY $grp
              ORDER BY qty DESC", $b);

        return [$mode, $rows];
    }

    // ---------------------------------------------------------------- 3. Gross profit
    private function _r_profit($f)
    {
        list($w, $b) = $this->_filters($f, 'i', true, false);
        $mode = ($f['mode'] === 'product') ? 'product' : 'invoice';
        $rev = self::REV; $cost = self::COST;

        if ($mode === 'product') {
            $rows = $this->_q(
                "SELECT d.product_code, d.product_name, SUM(d.qty) AS qty, SUM($rev) AS revenue, SUM($cost) AS cost,
                        SUM($rev) - SUM($cost) AS profit,
                        ROUND((SUM($rev) - SUM($cost)) / NULLIF(SUM($rev), 0) * 100, 2) AS margin
                   FROM tbl_invoice i
                   JOIN tbl_invoice_detail d ON d.tbl_invoice_idtbl_invoice = i.idtbl_invoice AND d.status = 1
                  WHERE 1 = 1 $w
                  GROUP BY d.tbl_product_idtbl_product, d.product_code, d.product_name
                  ORDER BY profit DESC", $b);
        } else {
            $rows = $this->_q(
                "SELECT i.invoice_no, i.invoice_date, i.customer_name, SUM($rev) AS revenue, SUM($cost) AS cost,
                        SUM($rev) - SUM($cost) AS profit,
                        ROUND((SUM($rev) - SUM($cost)) / NULLIF(SUM($rev), 0) * 100, 2) AS margin
                   FROM tbl_invoice i
                   JOIN tbl_invoice_detail d ON d.tbl_invoice_idtbl_invoice = i.idtbl_invoice AND d.status = 1
                  WHERE 1 = 1 $w
                  GROUP BY i.idtbl_invoice
                  ORDER BY i.invoice_date, i.idtbl_invoice", $b);
        }
        return [$mode, $rows];
    }

    // ---------------------------------------------------------------- 4. Customer sales and statement
    private function _r_customers($f)
    {
        list($w, $b) = $this->_filters($f, 'i', true, false);

        if ($f['customer'] > 0) {                              // one customer selected = statement
            $rows = $this->_q(
                "SELECT i.invoice_date, i.invoice_no, i.net_total AS total, i.paid_total AS paid, i.balance_due AS balance
                   FROM tbl_invoice i
                  WHERE 1 = 1 $w AND i.tbl_customer_idtbl_customer = ?
                  ORDER BY i.invoice_date, i.idtbl_invoice", array_merge($b, [$f['customer']]));
            $run = 0;
            foreach ($rows as &$r) { $run += (float)$r['balance']; $r['running'] = round($run, 2); }
            unset($r);
            return ['statement', $rows];
        }

        $rows = $this->_q(
            "SELECT COALESCE(i.tbl_customer_idtbl_customer, 0) AS cid, MAX(i.customer_name) AS customer_name,
                    COUNT(*) AS invoices, SUM(i.net_total) AS total, SUM(i.paid_total) AS paid, SUM(i.balance_due) AS balance
               FROM tbl_invoice i
              WHERE 1 = 1 $w
              GROUP BY cid
              ORDER BY total DESC", $b);
        foreach ($rows as &$r) { if ((int)$r['cid'] === 0) $r['customer_name'] = 'Walk-in customers'; }
        unset($r);
        return ['summary', $rows];
    }

    // ---------------------------------------------------------------- 5. Credit (unpaid) with ageing
    private function _r_credit($f)
    {
        list($w, $b) = $this->_filters($f, 'i', false, false);     // as of today, no date range
        $age = "DATEDIFF(CURDATE(), i.invoice_date)";

        $rows = $this->_q(
            "SELECT i.invoice_no, i.invoice_date, i.customer_name, $age AS age,
                    i.net_total, i.paid_total, i.balance_due,
                    IF($age <= 30, i.balance_due, 0)               AS b30,
                    IF($age BETWEEN 31 AND 60, i.balance_due, 0)   AS b60,
                    IF($age BETWEEN 61 AND 90, i.balance_due, 0)   AS b90,
                    IF($age > 90, i.balance_due, 0)                AS b90p
               FROM tbl_invoice i
              WHERE 1 = 1 $w AND i.balance_due > 0
              ORDER BY age DESC", $b);

        return ['main', $rows];
    }

    // ---------------------------------------------------------------- 6. Sales return (approved credit notes only)
    private function _r_returns($f)
    {
        list($w, $b) = $this->_filters($f, 'i', false, false);
        $b[] = $f['from']; $b[] = $f['to'];

        $rows = $this->_q(
            "SELECT cn.creditnote_date, cn.creditnote_no, i.invoice_no, i.customer_name,
                    d.product_name, cd.serialno, cd.qty, cd.total AS amount, cd.remark AS reason,
                    IF(cn.settle_status = 1, CONCAT('Used on ', s.invoice_no), 'Open (credit not used yet)') AS status
               FROM tbl_credit_note_detail cd
               JOIN tbl_credit_note cn ON cn.idtbl_credit_note = cd.tbl_credit_note_idtbl_credit_note
               JOIN tbl_invoice i ON i.idtbl_invoice = cn.tbl_invoice_idtbl_invoice
               LEFT JOIN tbl_invoice_detail d ON d.idtbl_invoice_detail = cd.tbl_invoice_detail_idtbl_invoice_detail
               LEFT JOIN tbl_invoice s ON s.idtbl_invoice = cn.settle_invoice_id
              WHERE cn.status = 1 AND cd.status = 1 AND cn.approve_status = 1 $w
                AND cn.creditnote_date BETWEEN ? AND ?
              ORDER BY cn.creditnote_date, cn.idtbl_credit_note", $b);

        return ['main', $rows];
    }

    // ---------------------------------------------------------------- helpers
    // shared WHERE part for invoices: no quotations, active only, own company, optional dates / branch / cashier
    private function _filters($f, $a = 'i', $withDate = true, $withCashier = false)
    {
        $sql  = " AND $a.status = 1 AND $a.invoice_type <> 'Corporate Quotation' AND $a.tbl_company_idtbl_company = ?";
        $bind = [$f['company']];

        if ($withDate) {
            $sql .= " AND $a.invoice_date BETWEEN ? AND ?";
            $bind[] = $f['from']; $bind[] = $f['to'];
        }
        if ($f['branch'] > 0) {
            $sql .= " AND $a.tbl_company_branch_idtbl_company_branch = ?";
            $bind[] = $f['branch'];
        }
        if ($withCashier && $f['cashier'] > 0) {
            $sql .= " AND $a.tbl_user_idtbl_user = ?";
            $bind[] = $f['cashier'];
        }
        return [$sql, $bind];
    }

    private function _q($sql, $bind = [])
    {
        $q = $this->db->query($sql, $bind);
        if ($q === FALSE) {
            $e = $this->db->error();
            throw new Exception('SQL error ' . $e['code'] . ': ' . $e['message']);
        }
        return $q->result_array();
    }

    private function _date($d)
    {
        $d = (string)$d;
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? $d : date('Y-m-d');
    }

    // ── Private helper ────────────────────────────────────────────────────────────
    private function _jsonResponse($status, $icon, $message, $type, $extra = []) {
        $actionObj          = new stdClass();
        $actionObj->icon    = $icon;
        $actionObj->title   = '';
        $actionObj->message = $message;
        $actionObj->url     = '';
        $actionObj->target  = '_blank';
        $actionObj->type    = $type;

        $obj         = new stdClass();
        $obj->status = $status;
        $obj->action = json_encode($actionObj);

        foreach ($extra as $k => $v) {     // extra fields, e.g. invoice_no
            $obj->$k = $v;
        }

        echo json_encode($obj);
    }
}