<?php
/**
 * DashboardInfo model
 * Works against: tbl_product, tbl_stock, tbl_grn, tbl_grn_return, tbl_invoice,
 * tbl_invoice_detail, tbl_product_serial, tbl_porder, tbl_customer, tbl_supplier,
 * tbl_category, tbl_brand.
 *
 * All sales / purchase figures are EXCLUDING VAT  (total - vatamount).
 * Everything is filtered by the logged-in company ($_SESSION['company_id']).
 */
class DashboardInfo extends CI_Model{

    private function _co(){
        return (int) $_SESSION['company_id'];
    }

    // single scalar helper
    private function _val($sql, $binds = array()){
        $row = $this->db->query($sql, $binds)->row();
        return $row ? (float) $row->v : 0;
    }

    /* =========================================================
       STAT CARDS
       ========================================================= */
    public function Cards(){
        $c = $this->_co();
        $r = array();

        // ---- Sales (excl. VAT) ----
        $salesSql = "SELECT COALESCE(SUM(`total` - `vatamount`),0) AS v FROM `tbl_invoice`
                     WHERE `status` = 1 AND `tbl_company_idtbl_company` = ? ";
        $r['sales_today']      = $this->_val($salesSql . "AND `invoice_date` = CURDATE()", array($c));
        $r['sales_month']      = $this->_val($salesSql . "AND DATE_FORMAT(`invoice_date`,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')", array($c));
        $r['sales_prev_month'] = $this->_val($salesSql . "AND DATE_FORMAT(`invoice_date`,'%Y-%m') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH),'%Y-%m')", array($c));
        $r['invoices_month']   = $this->_val("SELECT COUNT(*) AS v FROM `tbl_invoice`
                     WHERE `status` = 1 AND `tbl_company_idtbl_company` = ?
                     AND DATE_FORMAT(`invoice_date`,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')", array($c));

        // ---- Gross profit this month (selling price - cost at time of sale) ----
        $r['profit_month'] = $this->_val("SELECT COALESCE(SUM(d.`qty` * (d.`unitprice` - d.`costunitprice`)),0) AS v
                     FROM `tbl_invoice_detail` d
                     INNER JOIN `tbl_invoice` i ON i.`idtbl_invoice` = d.`tbl_invoice_idtbl_invoice`
                     WHERE d.`status` = 1 AND i.`status` = 1 AND i.`tbl_company_idtbl_company` = ?
                     AND DATE_FORMAT(i.`invoice_date`,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')", array($c));

        // ---- Purchases (approved GRNs, excl. VAT) ----
        $r['purchase_month'] = $this->_val("SELECT COALESCE(SUM(`totalcost` - `vatamountcost`),0) AS v FROM `tbl_grn`
                     WHERE `status` = 1 AND `approvestatus` = 1 AND `tbl_company_idtbl_company` = ?
                     AND DATE_FORMAT(`grndate`,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')", array($c));
        $r['grn_month'] = $this->_val("SELECT COUNT(*) AS v FROM `tbl_grn`
                     WHERE `status` = 1 AND `approvestatus` = 1 AND `tbl_company_idtbl_company` = ?
                     AND DATE_FORMAT(`grndate`,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')", array($c));

        // ---- Stock ----
        $r['stock_units'] = $this->_val("SELECT COALESCE(SUM(`qty`),0) AS v FROM `tbl_stock`
                     WHERE `status` = 1 AND `tbl_company_idtbl_company` = ?", array($c));
        $r['stock_value'] = $this->_val("SELECT COALESCE(SUM(`qty` * `costunitprice`),0) AS v FROM `tbl_stock`
                     WHERE `status` = 1 AND `tbl_company_idtbl_company` = ?", array($c));

        // ---- Low / out of stock (per product, summed over all batches) ----
        $perProduct = "SELECT p.`idtbl_product`, p.`reorder_level`, COALESCE(s.`q`,0) AS q
                       FROM `tbl_product` p
                       LEFT JOIN (SELECT `tbl_product_idtbl_product` AS pid, SUM(`qty`) AS q
                                  FROM `tbl_stock` WHERE `status` = 1 AND `tbl_company_idtbl_company` = ?
                                  GROUP BY `tbl_product_idtbl_product`) s ON s.pid = p.`idtbl_product`
                       WHERE p.`status` = 1";
        $r['low_stock'] = $this->_val("SELECT COUNT(*) AS v FROM ($perProduct) x WHERE x.q > 0 AND x.q <= x.`reorder_level`", array($c));
        $r['out_stock'] = $this->_val("SELECT COUNT(*) AS v FROM ($perProduct) x WHERE x.q <= 0", array($c));

        // ---- Counts ----
        $r['products']  = $this->_val("SELECT COUNT(*) AS v FROM `tbl_product` WHERE `status` = 1");
        $r['customers'] = $this->_val("SELECT COUNT(*) AS v FROM `tbl_customer` WHERE `status` = 1 AND `tbl_company_idtbl_company` = ?", array($c));
        $r['suppliers'] = $this->_val("SELECT COUNT(*) AS v FROM `tbl_supplier` WHERE `status` = 1 AND `tbl_company_idtbl_company` = ?", array($c));
        $r['serials_in_stock'] = $this->_val("SELECT COUNT(*) AS v FROM `tbl_product_serial` WHERE `status` = 1 AND `stock_status` = 1");
        $r['pending_po'] = $this->_val("SELECT COUNT(*) AS v FROM `tbl_porder`
                     WHERE `status` = 1 AND `grnconfirm` = 0 AND `tbl_company_idtbl_company` = ?", array($c));

        // month-on-month change in %, null when there is no previous month to compare to
        $r['sales_change'] = ($r['sales_prev_month'] > 0)
            ? round((($r['sales_month'] - $r['sales_prev_month']) / $r['sales_prev_month']) * 100, 1)
            : null;

        return $r;
    }

    /* =========================================================
       TABLES
       ========================================================= */

    // Products at or below reorder level (or empty), lowest first
    public function LowStockTable($limit = 8){
        $c = $this->_co();
        $sql = "SELECT p.`product_code`, p.`product_name`, cat.`category`, p.`reorder_level`, COALESCE(s.`q`,0) AS qty
                FROM `tbl_product` p
                LEFT JOIN `tbl_category` cat ON cat.`idtbl_category` = p.`tbl_category_idtbl_category`
                LEFT JOIN (SELECT `tbl_product_idtbl_product` AS pid, SUM(`qty`) AS q
                           FROM `tbl_stock` WHERE `status` = 1 AND `tbl_company_idtbl_company` = ?
                           GROUP BY `tbl_product_idtbl_product`) s ON s.pid = p.`idtbl_product`
                WHERE p.`status` = 1 AND COALESCE(s.`q`,0) <= p.`reorder_level`
                ORDER BY qty ASC, p.`product_name` ASC
                LIMIT " . (int) $limit;
        return $this->db->query($sql, array($c))->result();
    }

    public function RecentGrn($limit = 6){
        $c = $this->_co();
        $sql = "SELECT g.`grn_no`, g.`grndate`, g.`totalcost`, g.`approvestatus`, s.`suppliername`
                FROM `tbl_grn` g
                LEFT JOIN `tbl_supplier` s ON s.`idtbl_supplier` = g.`tbl_supplier_idtbl_supplier`
                WHERE g.`status` = 1 AND g.`tbl_company_idtbl_company` = ?
                ORDER BY g.`grndate` DESC, g.`idtbl_grn` DESC
                LIMIT " . (int) $limit;
        return $this->db->query($sql, array($c))->result();
    }

    public function RecentInvoices($limit = 6){
        $c = $this->_co();
        $sql = "SELECT i.`invoice_no`, i.`invoice_date`, i.`total`, cu.`customer`
                FROM `tbl_invoice` i
                LEFT JOIN `tbl_customer` cu ON cu.`idtbl_customer` = i.`tbl_customer_idtbl_customer`
                WHERE i.`status` = 1 AND i.`tbl_company_idtbl_company` = ?
                ORDER BY i.`invoice_date` DESC, i.`idtbl_invoice` DESC
                LIMIT " . (int) $limit;
        return $this->db->query($sql, array($c))->result();
    }

    public function RecentGrnReturns($limit = 6){
        $c = $this->_co();
        $sql = "SELECT r.`idtbl_grn_return`, r.`insertdatetime`, r.`totalpayment`, r.`approvestatus`,
                       g.`grn_no`, s.`suppliername`
                FROM `tbl_grn_return` r
                LEFT JOIN `tbl_grn` g ON g.`idtbl_grn` = r.`grn_no`
                LEFT JOIN `tbl_supplier` s ON s.`idtbl_supplier` = r.`tbl_supplier_idtbl_supplier`
                WHERE r.`status` = 1 AND r.`tbl_company_idtbl_company` = ?
                ORDER BY r.`insertdatetime` DESC
                LIMIT " . (int) $limit;
        return $this->db->query($sql, array($c))->result();
    }

    /* =========================================================
       CHART DATA  (each returns arrays ready for json_encode)
       ========================================================= */

    // Top selling products by revenue, last 30 days
    public function TopProducts($limit = 5){
        $c = $this->_co();
        $sql = "SELECT p.`product_name` AS label, SUM(d.`qty`) AS units, SUM(d.`total`) AS revenue
                FROM `tbl_invoice_detail` d
                INNER JOIN `tbl_invoice` i ON i.`idtbl_invoice` = d.`tbl_invoice_idtbl_invoice`
                INNER JOIN `tbl_product` p ON p.`idtbl_product` = d.`tbl_product_idtbl_product`
                WHERE d.`status` = 1 AND i.`status` = 1 AND i.`tbl_company_idtbl_company` = ?
                AND i.`invoice_date` >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY p.`idtbl_product`
                ORDER BY revenue DESC
                LIMIT " . (int) $limit;
        return $this->_labelSeries($this->db->query($sql, array($c))->result(), 'revenue', 'units');
    }

    // Stock value (at cost) by category
    public function StockByCategory(){
        $c = $this->_co();
        $sql = "SELECT COALESCE(cat.`category`,'Uncategorised') AS label,
                       SUM(s.`qty` * s.`costunitprice`) AS value, SUM(s.`qty`) AS units
                FROM `tbl_stock` s
                INNER JOIN `tbl_product` p ON p.`idtbl_product` = s.`tbl_product_idtbl_product`
                LEFT JOIN `tbl_category` cat ON cat.`idtbl_category` = p.`tbl_category_idtbl_category`
                WHERE s.`status` = 1 AND s.`qty` > 0 AND s.`tbl_company_idtbl_company` = ?
                GROUP BY cat.`idtbl_category`
                ORDER BY value DESC";
        return $this->_labelSeries($this->db->query($sql, array($c))->result(), 'value', 'units');
    }

    // Stock value (at cost) by brand
    public function StockByBrand(){
        $c = $this->_co();
        $sql = "SELECT COALESCE(b.`brand`,'No brand') AS label,
                       SUM(s.`qty` * s.`costunitprice`) AS value, SUM(s.`qty`) AS units
                FROM `tbl_stock` s
                INNER JOIN `tbl_product` p ON p.`idtbl_product` = s.`tbl_product_idtbl_product`
                LEFT JOIN `tbl_brand` b ON b.`idtbl_brand` = p.`tbl_brand_idtbl_brand`
                WHERE s.`status` = 1 AND s.`qty` > 0 AND s.`tbl_company_idtbl_company` = ?
                GROUP BY b.`idtbl_brand`
                ORDER BY value DESC";
        return $this->_labelSeries($this->db->query($sql, array($c))->result(), 'value', 'units');
    }

    // Serial numbers grouped by stock_status
    public function SerialStatus(){
        $names = array(1 => 'In stock', 2 => 'Sold', 3 => 'Returned to supplier', 4 => 'Damaged', 5 => 'Returned by customer');
        $rows = $this->db->query("SELECT `stock_status` AS s, COUNT(*) AS n FROM `tbl_product_serial`
                                  WHERE `status` = 1 GROUP BY `stock_status`")->result();
        $map = array();
        foreach ($rows as $r){ $map[(int) $r->s] = (int) $r->n; }

        $labels = array(); $data = array();
        foreach ($names as $k => $name){
            if (isset($map[$k])){ $labels[] = $name; $data[] = $map[$k]; }
        }
        return array('labels' => $labels, 'data' => $data);
    }

    // Sales vs purchases per day for $days days ending on $endDate
    public function DailySeries($days = 7, $endDate = null){
        $c = $this->_co();
        $days    = max(1, min(60, (int) $days));
        $endDate = $this->_normalizeDate($endDate);

        $sales = $this->db->query("SELECT `invoice_date` AS d, SUM(`total` - `vatamount`) AS v FROM `tbl_invoice`
                WHERE `status` = 1 AND `tbl_company_idtbl_company` = ?
                AND `invoice_date` BETWEEN DATE_SUB(?, INTERVAL ? DAY) AND ?
                GROUP BY `invoice_date`", array($c, $endDate, $days - 1, $endDate))->result();

        $purch = $this->db->query("SELECT `grndate` AS d, SUM(`totalcost` - `vatamountcost`) AS v FROM `tbl_grn`
                WHERE `status` = 1 AND `approvestatus` = 1 AND `tbl_company_idtbl_company` = ?
                AND `grndate` BETWEEN DATE_SUB(?, INTERVAL ? DAY) AND ?
                GROUP BY `grndate`", array($c, $endDate, $days - 1, $endDate))->result();

        $sm = $this->_map($sales); $pm = $this->_map($purch);
        $labels = array(); $s = array(); $p = array();
        for ($i = $days - 1; $i >= 0; $i--){
            $d = date('Y-m-d', strtotime("$endDate -$i day"));
            $labels[] = date('d M', strtotime($d));
            $s[] = isset($sm[$d]) ? $sm[$d] : 0;
            $p[] = isset($pm[$d]) ? $pm[$d] : 0;
        }
        return array('labels' => $labels, 'sales' => $s, 'purchases' => $p);
    }

    // Sales vs purchases per month for $months months ending on $endMonth
    public function MonthlySeries($months = 12, $endMonth = null){
        $c = $this->_co();
        $months   = max(1, min(24, (int) $months));
        $endMonth = $this->_normalizeMonth($endMonth);
        $endOfEnd = date('Y-m-t', strtotime($endMonth . '-01'));

        $sales = $this->db->query("SELECT DATE_FORMAT(`invoice_date`,'%Y-%m') AS d, SUM(`total` - `vatamount`) AS v FROM `tbl_invoice`
                WHERE `status` = 1 AND `tbl_company_idtbl_company` = ?
                AND `invoice_date` > DATE_SUB(?, INTERVAL ? MONTH) AND `invoice_date` <= ?
                GROUP BY DATE_FORMAT(`invoice_date`,'%Y-%m')", array($c, $endOfEnd, $months, $endOfEnd))->result();

        $purch = $this->db->query("SELECT DATE_FORMAT(`grndate`,'%Y-%m') AS d, SUM(`totalcost` - `vatamountcost`) AS v FROM `tbl_grn`
                WHERE `status` = 1 AND `approvestatus` = 1 AND `tbl_company_idtbl_company` = ?
                AND `grndate` > DATE_SUB(?, INTERVAL ? MONTH) AND `grndate` <= ?
                GROUP BY DATE_FORMAT(`grndate`,'%Y-%m')", array($c, $endOfEnd, $months, $endOfEnd))->result();

        $sm = $this->_map($sales); $pm = $this->_map($purch);
        $labels = array(); $s = array(); $p = array();
        for ($i = $months - 1; $i >= 0; $i--){
            $m = date('Y-m', strtotime($endMonth . "-01 -$i month"));
            $labels[] = date('M Y', strtotime($m . '-01'));
            $s[] = isset($sm[$m]) ? $sm[$m] : 0;
            $p[] = isset($pm[$m]) ? $pm[$m] : 0;
        }
        return array('labels' => $labels, 'sales' => $s, 'purchases' => $p);
    }

    /* ---------- helpers ---------- */

    private function _map($rows){
        $map = array();
        foreach ($rows as $r){ $map[$r->d] = (float) $r->v; }
        return $map;
    }

    private function _labelSeries($rows, $valField, $extraField){
        $labels = array(); $data = array(); $extra = array();
        foreach ($rows as $r){
            $labels[] = $r->label;
            $data[]   = (float) $r->$valField;
            $extra[]  = (float) $r->$extraField;
        }
        return array('labels' => $labels, 'data' => $data, 'extra' => $extra);
    }

    private function _normalizeDate($date){
        if (empty($date) || !strtotime($date)){ return date('Y-m-d'); }
        return date('Y-m-d', strtotime($date));
    }

    private function _normalizeMonth($month){
        if (empty($month) || !strtotime($month . '-01')){ return date('Y-m'); }
        return date('Y-m', strtotime($month . '-01'));
    }
}