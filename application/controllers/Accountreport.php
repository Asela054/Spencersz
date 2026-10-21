<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Accountreport extends CI_Controller
{
    // CHANGE: the column of tbl_user that holds the user's name
    private $userNameCol = 'name';

    // CHANGE: the column of tbl_supplier that holds the supplier's name
    private $supplierNameCol = 'supplier';

    private $notice = '';

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

        $this->load->view('accountreport', $result);
    }

    public function Getreport()
    {
        $old_debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;

        try {
            $f = [
                'company' => (int)$_SESSION['company_id'],
                'from'    => $this->_date($this->input->post('from')),
                'to'      => $this->_date($this->input->post('to')),
                'branch'  => (int)$this->input->post('branch'),      // 0 = all branches
                'cashier' => (int)$this->input->post('cashier'),     // 0 = all cashiers
                'mode'    => (string)$this->input->post('mode'),
            ];
            if ($f['from'] > $f['to']) throw new RuntimeException('The From date is after the To date');

            switch ((int)$this->input->post('report')) {
                case 21: $out = $this->_r_vat($f);        break;
                case 22: $out = $this->_r_collection($f); break;
                default: throw new RuntimeException('Unknown report');
            }

            echo json_encode(['status' => 1, 'variant' => $out[0], 'rows' => $out[1], 'notice' => $this->notice]);

        } catch (RuntimeException $e) {
            $this->_jsonResponse(0, 'fas fa-exclamation-triangle', $e->getMessage(), 'danger');
        } catch (Throwable $e) {
            log_message('error', 'Accountreport failed: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            $this->_jsonResponse(0, 'fas fa-exclamation-triangle', 'Could not load the report. Please try again.', 'danger');
        } finally {
            $this->db->db_debug = $old_debug;
        }
    }

    // ================================================================ 21. VAT report
    private function _r_vat($f)
    {
        $mode = in_array($f['mode'], ['month', 'day', 'invoice', 'purchase'], true) ? $f['mode'] : 'month';
        if ($mode === 'invoice')  return ['invoice',  $this->_vat_invoices($f)];
        if ($mode === 'purchase') return ['purchase', $this->_vat_purchases($f)];
        return [$mode, $this->_vat_summary($f, $mode)];
    }

    // output VAT (sales) - credit note VAT = net output;  net output - input VAT (purchases) = VAT payable
    private function _vat_summary($f, $mode)
    {
        $fmt = ($mode === 'day') ? '%Y-%m-%d' : '%Y-%m';
        $blank = ['taxable_sales' => 0, 'output_vat' => 0, 'cn_vat' => 0, 'input_vat' => 0];
        $rows = [];

        // 1. sales (tax invoices + hire purchase, no quotations)
        list($w, $b) = $this->_filters($f, 'i', true, false);
        foreach ($this->_q(
            "SELECT DATE_FORMAT(i.invoice_date, '$fmt') AS p, SUM(i.net_total - i.vatamount) AS taxable, SUM(i.vatamount) AS vat
               FROM tbl_invoice i WHERE 1 = 1 $w GROUP BY p", $b) as $r) {
            if (!isset($rows[$r['p']])) $rows[$r['p']] = $blank;
            $rows[$r['p']]['taxable_sales'] = (float)$r['taxable'];
            $rows[$r['p']]['output_vat']    = (float)$r['vat'];
        }

        // 2. approved credit notes reduce the output VAT
        list($cw, $cb) = $this->_cn_filters($f);
        foreach ($this->_q(
            "SELECT DATE_FORMAT(cn.creditnote_date, '$fmt') AS p, SUM(cn.vatamount) AS vat
               FROM tbl_credit_note cn JOIN tbl_invoice i ON i.idtbl_invoice = cn.tbl_invoice_idtbl_invoice
              WHERE $cw GROUP BY p", $cb) as $r) {
            if (!isset($rows[$r['p']])) $rows[$r['p']] = $blank;
            $rows[$r['p']]['cn_vat'] = (float)$r['vat'];
        }

        // 3. input VAT (purchases)
        foreach ($this->_input_vat($f, $fmt) as $p => $vat) {
            if (!isset($rows[$p])) $rows[$p] = $blank;
            $rows[$p]['input_vat'] = $vat;
        }

        ksort($rows);
        $out = [];
        foreach ($rows as $p => $r) {
            $net = round($r['output_vat'] - $r['cn_vat'], 2);
            $out[] = [
                'period'        => $p,
                'taxable_sales' => round($r['taxable_sales'], 2),
                'output_vat'    => round($r['output_vat'], 2),
                'cn_vat'        => round($r['cn_vat'], 2),
                'net_output'    => $net,
                'input_vat'     => round($r['input_vat'], 2),
                'vat_payable'   => round($net - $r['input_vat'], 2),
            ];
        }
        return $out;
    }

    // every invoice and credit note with its VAT (credit notes are negative), for the VAT return
    private function _vat_invoices($f)
    {
        list($w, $b)   = $this->_filters($f, 'i', true, false);
        list($cw, $cb) = $this->_cn_filters($f);

        return $this->_q(
            "SELECT 'Invoice' AS doc_type, i.invoice_no AS doc_no, i.invoice_date AS doc_date, i.customer_name,
                    c.vat_no AS customer_vat, (i.net_total - i.vatamount) AS taxable, i.vatamount AS vat, i.net_total AS total
               FROM tbl_invoice i
               LEFT JOIN tbl_customer c ON c.idtbl_customer = i.tbl_customer_idtbl_customer
              WHERE 1 = 1 $w
             UNION ALL
             SELECT 'Credit Note', cn.creditnote_no, cn.creditnote_date, i.customer_name,
                    c.vat_no, -cn.subtotal, -cn.vatamount, -cn.totalamount
               FROM tbl_credit_note cn
               JOIN tbl_invoice i ON i.idtbl_invoice = cn.tbl_invoice_idtbl_invoice
               LEFT JOIN tbl_customer c ON c.idtbl_customer = i.tbl_customer_idtbl_customer
              WHERE $cw
              ORDER BY doc_date, doc_no", array_merge($b, $cb));
    }

    // input VAT per period: approved, active GRNs
    private function _input_vat($f, $fmt)
    {
        list($w, $b) = $this->_grn_filters($f);
        $res = [];
        foreach ($this->_q(
            "SELECT DATE_FORMAT(g.grndate, '$fmt') AS p, SUM(g.vatamount) AS vat
            FROM tbl_grn g WHERE $w GROUP BY p", $b) as $r) {
            $res[$r['p']] = (float)$r['vat'];
        }
        return $res;
    }

    // every approved GRN with its VAT (to check against the supplier invoices)
    private function _vat_purchases($f)
    {
        list($w, $b) = $this->_grn_filters($f);

        $sql = "SELECT g.grn_no, g.grndate, g.invoicenum, g.vat_type, %s AS supplier,
                    g.subtotal, g.discount, g.vatamount, g.total
                FROM tbl_grn g %s
                WHERE $w
                ORDER BY g.grndate, g.idtbl_grn";

        $q = $this->db->query(
            sprintf($sql, "s.`{$this->supplierNameCol}`", "LEFT JOIN tbl_supplier s ON s.idtbl_supplier = g.tbl_supplier_idtbl_supplier"), $b);

        if ($q === FALSE) {                       // most likely the supplier name column: show the GRNs without it
            $e = $this->db->error();
            log_message('error', 'Accountreport supplier name: ' . $e['code'] . ' ' . $e['message']);
            $this->notice = 'The supplier name could not be read. Please check $supplierNameCol in the Accountreport controller.';
            $q = $this->db->query(sprintf($sql, "''", ""), $b);
            if ($q === FALSE) {
                $e = $this->db->error();
                throw new Exception('SQL error ' . $e['code'] . ': ' . $e['message']);
            }
        }
        return $q->result_array();
    }

    private function _grn_filters($f)
    {
        $sql  = "g.status = 1 AND g.approvestatus = 1 AND g.tbl_company_idtbl_company = ?
                AND DATE(g.grndate) BETWEEN ? AND ?";
        $bind = [$f['company'], $f['from'], $f['to']];
        if ($f['branch'] > 0) {
            $sql .= " AND g.tbl_company_branch_idtbl_company_branch = ?";
            $bind[] = $f['branch'];
        }
        return [$sql, $bind];
    }

    // ================================================================ 22. Payment collection
    private function _r_collection($f)
    {
        $mode = in_array($f['mode'], ['day', 'daycashier', 'detail'], true) ? $f['mode'] : 'day';
        list($w, $b) = $this->_filters($f, 'i', false, true);       // the date comes from the payment, not the invoice
        $b[] = $f['from']; $b[] = $f['to'];
        $u = $this->userNameCol;

        if ($mode === 'detail') {                                    // card / cheque / bank transfer, one line each
            $rows = $this->_q(
                "SELECT DATE(p.insertdatetime) AS day, i.invoice_no, u.`$u` AS cashier, p.paytype, p.bank,
                        p.ref_no AS ref, p.approval_code AS approval, p.cheque_date,
                        IF(p.group_type = 'cheque', IF(p.cheque_status = 0, 'Pending', p.cheque_status), '') AS cheque_status,
                        p.amount
                   FROM tbl_invoice_payment p
                   JOIN tbl_invoice i ON i.idtbl_invoice = p.tbl_invoice_idtbl_invoice
                   LEFT JOIN tbl_user u ON u.idtbl_user = i.tbl_user_idtbl_user
                  WHERE p.status = 1 AND p.group_type IN ('card', 'installment', 'cheque', 'transfer') $w
                    AND DATE(p.insertdatetime) BETWEEN ? AND ?
                  ORDER BY p.insertdatetime, p.idtbl_invoice_payment", $b);
            return ['detail', $rows];
        }

        $byCashier = ($mode === 'daycashier');
        $sel = "DATE(p.insertdatetime) AS day" . ($byCashier ? ", u.`$u` AS cashier" : "");
        $grp = "DATE(p.insertdatetime)"        . ($byCashier ? ", i.tbl_user_idtbl_user, u.`$u`" : "");

        $rows = $this->_q(
            "SELECT $sel,
                    COUNT(DISTINCT i.idtbl_invoice) AS invoices,
                    SUM(IF(p.group_type = 'cash', p.amount, 0))                                        AS cash,
                    SUM(IF(p.group_type IN ('card', 'installment'), p.amount, 0))                      AS card,
                    SUM(IF(p.group_type = 'cheque', p.amount, 0))                                      AS cheque,
                    SUM(IF(p.group_type = 'transfer', p.amount, 0))                                    AS transfer,
                    SUM(IF(p.group_type IN ('cash', 'card', 'installment', 'cheque', 'transfer'), p.amount, 0)) AS collected,
                    SUM(IF(p.group_type = 'voucher', p.amount, 0))                                     AS voucher,
                    SUM(IF(p.group_type = 'creditnote', p.amount, 0))                                  AS creditnote
               FROM tbl_invoice_payment p
               JOIN tbl_invoice i ON i.idtbl_invoice = p.tbl_invoice_idtbl_invoice
               LEFT JOIN tbl_user u ON u.idtbl_user = i.tbl_user_idtbl_user
              WHERE p.status = 1 $w AND DATE(p.insertdatetime) BETWEEN ? AND ?
              GROUP BY $grp
              ORDER BY $grp", $b);

        return [$mode, $rows];
    }

    // ================================================================ helpers
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

    // approved credit notes of this company / branch in the date range (the branch is the one of the original invoice)
    private function _cn_filters($f)
    {
        $sql  = "cn.status = 1 AND cn.approve_status = 1 AND i.tbl_company_idtbl_company = ?
                 AND cn.creditnote_date BETWEEN ? AND ?";
        $bind = [$f['company'], $f['from'], $f['to']];
        if ($f['branch'] > 0) {
            $sql .= " AND i.tbl_company_branch_idtbl_company_branch = ?";
            $bind[] = $f['branch'];
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