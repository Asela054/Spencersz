<?php
class DashboardInfo extends CI_Model{

    public function DashMaterialInfo(){
        $company = $_SESSION['company_id'];

        $sql = "SELECT COUNT(`idtbl_print_stock`) AS stockcount 
                FROM `tbl_print_stock` 
                LEFT JOIN `tbl_print_material_info` 
                ON `tbl_print_material_info`.`idtbl_print_material_info` = `tbl_print_stock`.`tbl_print_material_info_idtbl_print_material_info`
                WHERE `tbl_print_stock`.`status` = 1 
                AND `tbl_print_stock`.`tbl_print_material_info_idtbl_print_material_info`
                AND `tbl_print_stock`.`tbl_company_idtbl_company` = ?";
        $materialinfo = $this->db->query($sql, array($company));
        return $materialinfo;
    }

    public function DashZeroStockInfo(){
        $company = $_SESSION['company_id'];

        $sql = "SELECT COUNT(`idtbl_print_stock`) AS stockcount 
            FROM `tbl_print_stock` 
            LEFT JOIN `tbl_print_material_info`
            ON `tbl_print_material_info`.`idtbl_print_material_info`= `tbl_print_stock`.`tbl_print_material_info_idtbl_print_material_info`
            WHERE `tbl_print_stock`.`status` = 1
            AND `tbl_print_stock`.`qty` = 0 
            AND `tbl_print_stock`.`tbl_print_material_info_idtbl_print_material_info` 
            AND `tbl_print_stock`.`tbl_company_idtbl_company` = ?";
        $zerostockinfo=$this->db->query($sql, array($company));
        return $zerostockinfo;
    }

    public function DashLowStockInfo(){
        $company = $_SESSION['company_id'];

        $sql="SELECT COUNT(`idtbl_print_stock`) AS stockcount 
        	  FROM `tbl_print_stock` LEFT JOIN `tbl_print_material_info` 
        	  ON `tbl_print_material_info`.`idtbl_print_material_info`= `tbl_print_stock`.`tbl_print_material_info_idtbl_print_material_info`
        	  WHERE `tbl_print_stock`.`status` = 1 
        	  AND `tbl_print_stock`.`tbl_print_material_info_idtbl_print_material_info` <> 0 
        	  AND `tbl_print_stock`.`qty` < `tbl_print_material_info`.`reorderlevel`
        	  AND `tbl_print_stock`.`tbl_company_idtbl_company` = ?";
        $lowstockinfo = $this->db->query($sql, array($company));
        return $lowstockinfo;
    }

    // Today's total approved sales, EXCLUDING VAT (single number, for the stat card)
    public function DashTodaySalesTotal(){
        $company = $_SESSION['company_id'];

        $sql = "SELECT COALESCE(SUM(`subtotal`), 0) AS salestotal
                FROM `tbl_print_invoice`
                WHERE `status` = 1 AND `approvestatus` = 1
                AND `tbl_company_idtbl_company` = ?
                AND DATE(`date`) = CURDATE()";
        $todaysales = $this->db->query($sql, array($company));
        return $todaysales;
    }

    // Current calendar month's total approved sales, EXCLUDING VAT (single number, for the stat card)
    public function DashMonthSalesTotal(){
        $company = $_SESSION['company_id'];

        $sql = "SELECT COALESCE(SUM(`subtotal`), 0) AS salestotal
                FROM `tbl_print_invoice`
                WHERE `status` = 1 AND `approvestatus` = 1
                AND `tbl_company_idtbl_company` = ?
                AND DATE_FORMAT(`date`, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
        $monthsales = $this->db->query($sql, array($company));
        return $monthsales;
    }

    public function DashLastFiveInfo(){
        $company = $_SESSION['company_id'];

        $sql="SELECT m.materialname, g.date, g.qty, g.unitprice, g.total 
        	  FROM tbl_print_grndetail g LEFT JOIN tbl_print_grn gd ON gd.idtbl_print_grn=g.tbl_print_grn_idtbl_print_grn INNER JOIN tbl_print_material_info m 
        	  ON g.tbl_print_material_info_idtbl_print_material_info = m.idtbl_print_material_info 
        	  WHERE g.status = 1 AND gd.approvestatus=1 AND gd.tbl_company_idtbl_company=$company AND g.tbl_print_material_info_idtbl_print_material_info <> 0 ORDER BY g.date DESC LIMIT 5";
        $resultdate = $this->db->query($sql);
        return $resultdate;
    }

    public function DashTopFiveInfo(){
        $company = $_SESSION['company_id'];

        $sql="SELECT m.materialname, g.date, g.qty, g.unitprice, g.total FROM tbl_print_grndetail g LEFT JOIN tbl_print_grn gd ON gd.idtbl_print_grn=g.tbl_print_grn_idtbl_print_grn INNER JOIN tbl_print_material_info m ON g.tbl_print_material_info_idtbl_print_material_info = m.idtbl_print_material_info WHERE g.status = 1 AND gd.approvestatus=1 AND gd.tbl_company_idtbl_company=$company AND g.tbl_print_material_info_idtbl_print_material_info <> 0 ORDER BY g.qty DESC LIMIT 5 ";
        $resultqty = $this->db->query($sql);
        return $resultqty;
    }

    public function DashNonMoveInfo(){
        $company = $_SESSION['company_id'];

        $sql="SELECT m.materialname, g.grndate, g.qty, g.unitprice, g.total FROM tbl_print_stock g INNER JOIN tbl_print_material_info m ON g.tbl_print_material_info_idtbl_print_material_info = m.idtbl_print_material_info WHERE g.status = 1 AND g.tbl_company_idtbl_company=$company AND g.tbl_print_material_info_idtbl_print_material_info <> 0 AND (g.updatedatetime IS NULL OR g.updatedatetime > g.grndate) <> 0 ORDER BY g.qty DESC LIMIT 5";
        $resultnonmove = $this->db->query($sql);
        return $resultnonmove;
    }

    /* =========================================================
       CHART DATA — SALES (tbl_print_invoice)
       Figures are EXCLUDING VAT (based on `subtotal`, not `total`).
       Each method returns ['labels' => [...], 'data' => [...]]
       ready to json_encode() straight into Chart.js.

       $endDate  / $endMonth let the dashboard's date pickers ask for a
       window ending on an arbitrary day/month instead of always "today".
       Both the SQL filter and the PHP gap-filling loop use the same
       anchor so the two never disagree.
       ========================================================= */

    // Daily sales total (excl. VAT) for $days days ending on $endDate (default: today), gaps filled with 0
    public function DashDailySales($days = 7, $endDate = null){
        $company = $_SESSION['company_id'];
        $endDate = $this->_normalizeDate($endDate);

        $sql = "SELECT DATE(`date`) AS thedate, SUM(`subtotal`) AS total
                FROM `tbl_print_invoice`
                WHERE `status` = 1 AND `approvestatus` = 1
                AND `tbl_company_idtbl_company` = ?
                AND DATE(`date`) BETWEEN DATE_SUB(?, INTERVAL ? DAY) AND ?
                GROUP BY DATE(`date`)
                ORDER BY thedate ASC";
        $rows = $this->db->query($sql, array($company, $endDate, $days - 1, $endDate))->result();

        return $this->_fillDailySeries($rows, $days, 'thedate', 'total', $endDate);
    }

    // Monthly sales total (excl. VAT) for $months months ending on $endMonth (default: current month), gaps filled with 0
    public function DashMonthlySales($months = 12, $endMonth = null){
        $company  = $_SESSION['company_id'];
        $endMonth = $this->_normalizeMonth($endMonth);
        $endOfEndMonth = date('Y-m-t', strtotime($endMonth . '-01'));

        $sql = "SELECT DATE_FORMAT(`date`, '%Y-%m') AS themonth, SUM(`subtotal`) AS total
                FROM `tbl_print_invoice`
                WHERE `status` = 1 AND `approvestatus` = 1
                AND `tbl_company_idtbl_company` = ?
                AND `date` >= DATE_SUB(?, INTERVAL ? MONTH)
                AND `date` <= ?
                GROUP BY DATE_FORMAT(`date`, '%Y-%m')
                ORDER BY themonth ASC";
        $rows = $this->db->query($sql, array($company, $endOfEndMonth, $months - 1, $endOfEndMonth))->result();

        return $this->_fillMonthlySeries($rows, $months, 'themonth', 'total', $endMonth);
    }

    /* ---------- helpers ---------- */

    // Accepts 'Y-m-d' or null; falls back to today; rejects garbage input
    private function _normalizeDate($date){
        if (empty($date) || !strtotime($date)){
            return date('Y-m-d');
        }
        return date('Y-m-d', strtotime($date));
    }

    // Accepts 'Y-m' or null; falls back to current month; rejects garbage input
    private function _normalizeMonth($month){
        if (empty($month) || !strtotime($month . '-01')){
            return date('Y-m');
        }
        return date('Y-m', strtotime($month . '-01'));
    }

    private function _fillDailySeries($rows, $days, $keyField, $valField, $endDate){
        $map = array();
        foreach ($rows as $r){
            $map[$r->$keyField] = (float) $r->$valField;
        }

        $labels = array();
        $data   = array();
        for ($i = $days - 1; $i >= 0; $i--){
            $d = date('Y-m-d', strtotime("$endDate -$i day"));
            $labels[] = date('d M', strtotime($d));
            $data[]   = isset($map[$d]) ? $map[$d] : 0;
        }

        return array('labels' => $labels, 'data' => $data);
    }

    private function _fillMonthlySeries($rows, $months, $keyField, $valField, $endMonth){
        $map = array();
        foreach ($rows as $r){
            $map[$r->$keyField] = (float) $r->$valField;
        }

        $labels = array();
        $data   = array();
        for ($i = $months - 1; $i >= 0; $i--){
            $m = date('Y-m', strtotime($endMonth . "-01 -$i month"));
            $labels[] = date('M Y', strtotime($m . '-01'));
            $data[]   = isset($map[$m]) ? $map[$m] : 0;
        }

        return array('labels' => $labels, 'data' => $data);
    }
}