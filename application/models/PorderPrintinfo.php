<?php
class PorderPrintinfo extends CI_Model{

    /* =====================================================================
       Pre-printed continuous stationery (LQ-310)
       Sheet = 9.5in x 11in = 24.13cm x 27.94cm = 684 x 792 pt
       Field positions are in ARTWORK PIXELS (artwork = 1382 x 1600 px), measured
       from the top-left corner of the sheet. The items-grid COLUMNS are set in cm
       ($colX0 / $colW below, taken from the pen measurements on the real form).
       Only DATA is printed - the form design is already on the paper.

       PRINT SETTINGS (important): paper size 9.5 x 11 in, margins None,
       scale 100% ("Actual size", NOT "Fit to printable area"), Pages = All.

       CALIBRATION (use when the printer shrinks / moves the page):
         ?ruler=1   cm ruler on the top and left edge. Measure it with a real ruler.
                    10cm must be 10cm. If 10cm prints as 8.7cm  ->  sx = 10 / 8.7 = 1.15
         ?guide=1   red outline round every field
         ?sx=1.15   horizontal enlargement   (same as $kx)
         ?sy=1.20   vertical enlargement     (same as $ky)
         ?dx=-4     move left 4mm   (+ right)   (same as $shiftX)
         ?dy=-6     move up 6mm     (+ down)    (same as $shiftY)
       When the numbers are right, write them into $kx $ky $shiftX $shiftY below.
       ===================================================================== */
    private $paperW = 684;     // 9.5in in pt
    private $paperH = 792;     // 11in in pt
    private $artW   = 1382;    // artwork width in px

    // calibration (physical paper units)
    private $kx     = 1.0;     // >1 enlarges the PDF to cancel a printer that shrinks the page
    private $ky     = 1.0;
    private $shiftX = 0;       // mm, + moves right
    private $shiftY = 0;       // mm, + moves down
    private $guide  = false;   // true = red outline around every field (or add ?guide=1)
    private $ruler  = false;   // true = cm ruler on the top / left edge (or add ?ruler=1)

    private $fontSize   = 9;    // pt
    private $lineHeight = 10.5; // pt

    // items grid columns (cm): x of the first column line from the left edge of the sheet, then the width of each column
    // order: Seq. No | Stock Code | Description | Qty | Unit | Unit Price | Value
    private $colX0  = 1.75;
    private $colW   = array(2.3, 3.2, 6.5, 1.9, 1.9, 2.6, 2.3);
    private $colPad = 0.12;     // cm of space kept inside every column

    // items grid rows (body of the form: y 535 - 1416 px)
    private $rowsPerPage = 18;
    private $rowTop      = 545;   // first row y
    private $rowH        = 44;    // row height (2 text lines fit)
    private $totalY      = 1372;  // total / "continued" line, below the last row

    public function Printinvoice($x){
        $this->lq_flags();
        $recordID = (int)$x;

        $respond = $this->db->query(
            "SELECT `tbl_porder`.*, `tbl_supplier`.`suppliername`, `tbl_supplier`.`address_line1`,
                    `tbl_supplier`.`address_line2`, `tbl_supplier`.`city`, `tbl_supplier`.`telephone_no`
             FROM `tbl_porder`
             LEFT JOIN `tbl_supplier` ON `tbl_supplier`.`idtbl_supplier` = `tbl_porder`.`tbl_supplier_idtbl_supplier`
             WHERE `tbl_porder`.`idtbl_porder` = ? AND `tbl_porder`.`status` IN (1,2)",
            array($recordID)
        );

        if ($respond->num_rows() == 0) {
            show_404();
            return;
        }

        $po = $respond->row(0);

        $this->db->select('tbl_company.company AS companyname, tbl_company.address1 AS companyaddress, tbl_company.mobile AS companymobile,
                           tbl_company.phone AS companyphone, tbl_company.email AS companyemail,
                           tbl_company_branch.branch AS branchname,
                           tbl_company_branch.address1 AS branchaddress1, tbl_company_branch.address2 AS branchaddress2,
                           tbl_company_branch.mobile AS branchmobile, tbl_company_branch.phone AS branchphone');
        $this->db->from('tbl_porder');
        $this->db->join('tbl_company', 'tbl_company.idtbl_company = tbl_porder.tbl_company_idtbl_company', 'left');
        $this->db->join('tbl_company_branch', 'tbl_company_branch.idtbl_company_branch = tbl_porder.tbl_company_branch_idtbl_company_branch', 'left');
        $this->db->where('tbl_porder.idtbl_porder', $recordID);
        $companydetails = $this->db->get();
        $co = $companydetails->row();

        $net = (float)$po->nettotal;

        $respond2 = $this->db->query(
            "SELECT `tbl_porder_detail`.`qty`, `tbl_porder_detail`.`unitprice`, `tbl_porder_detail`.`netprice`,
                    `tbl_porder_detail`.`comment`,
                    `tbl_product`.`product_code`, `tbl_product`.`product_name`,
                    COALESCE(NULLIF(`tbl_unit`.`unit_short`, ''), `tbl_unit`.`unit`) AS `measure_type`
             FROM `tbl_porder_detail`
             LEFT JOIN `tbl_product` ON `tbl_product`.`idtbl_product` = `tbl_porder_detail`.`tbl_product_idtbl_product`
             LEFT JOIN `tbl_unit` ON `tbl_unit`.`idtbl_unit` = `tbl_product`.`tbl_unit_idtbl_unit`
             WHERE `tbl_porder_detail`.`status` = 1
             AND `tbl_porder_detail`.`tbl_porder_idtbl_porder` = ?
             ORDER BY `tbl_porder_detail`.`idtbl_porder_detail` ASC",
            array($recordID)
        );

        $remark     = trim((string)$po->remark);
        $supplierId = (int)$po->tbl_supplier_idtbl_supplier;

        // ---- "To Messers" : supplier (supplier 65 = one-time supplier, name is typed in the remark)
        $toLines = array($po->suppliername);
        if ($supplierId == 65 && $remark !== '') {
            $toLines[] = $remark;
        }
        $toLines[] = $po->address_line1;
        $toLines[] = $po->address_line2;
        $toLines[] = $po->city;
        if (strlen((string)$po->telephone_no) >= 9) {
            $toLines[] = 'Tel: ' . $po->telephone_no;
        }

        // ---- "Delivery" : receiving branch (falls back to the company address)
        $brTel = trim($co->branchmobile . ' / ' . $co->branchphone, ' /');
        $coTel = trim($co->companymobile . ' / ' . $co->companyphone, ' /');

        $delLines = array($co->branchname);
        $brAddr = array(trim((string)$co->branchaddress1), trim((string)$co->branchaddress2));
        if ($brAddr[0] === '' && $brAddr[1] === '') {
            $delLines[] = $co->companyaddress;
        } else {
            $delLines[] = $brAddr[0];
            $delLines[] = $brAddr[1];
        }
        $delTel = ($brTel !== '') ? $brTel : $coTel;
        if ($delTel !== '') {
            $delLines[] = 'Tel: ' . $delTel;
        }

        // ---- optional fields (left blank for hand writing if the column does not exist)
        $onOrBefore   = $this->lq_pick($po, array('deliverydate', 'delivery_date', 'requireddate', 'required_date', 'duedate', 'due_date'));
        $creditPeriod = $this->lq_pick($po, array('creditperiod', 'credit_period', 'creditdays', 'credit_days'));

        $pages = array_chunk($respond2->result(), $this->rowsPerPage);
        if (empty($pages)) {
            $pages = array(array());
        }
        $totalPages = count($pages);
        $body = '';

        foreach ($pages as $index => $lines) {
            $isLast = ($index == $totalPages - 1);

            $p = '';
            if ($this->ruler && $index == 0) { $p .= $this->lq_ruler(); }

            // ---------- header boxes (single-line boxes are centred vertically) ----------
            $p .= $this->at(1082, 103, 196, 42, $this->lq_e($po->porder_no), 'left', false, true);   // No.
            $p .= $this->at(1082, 155, 196, 41, $this->lq_e($po->orderdate), 'left', false, true);   // Date
            $p .= $this->at(115, 245, 380, 118, $this->lq_lines($delLines, true));                   // Delivery
            $p .= $this->at(910, 245, 369, 118, $this->lq_lines($toLines, true));                    // To Messers
            $p .= $this->at(275, 369, 229, 46, $this->lq_e($onOrBefore), 'left', false, true);       // ON or BEFORE
            $p .= $this->at(1071, 369, 223, 46, $this->lq_e($creditPeriod), 'left', false, true);    // Credit Period

            // ---------- items grid ----------
            $n = $index * $this->rowsPerPage;
            foreach ($lines as $i => $r) {
                $n++;
                $y = $this->rowTop + $i * $this->rowH;

                $desc = (string)$r->product_name;
                if (trim((string)$r->comment) !== '') {
                    $desc .= ' - ' . $r->comment;
                }

                $p .= $this->cell(0, $y, $n, 'center');                                          // Seq. No.
                $p .= $this->cell(1, $y, $this->lq_e($r->product_code));                         // Stock Code
                $p .= $this->cell(2, $y, $this->lq_e($desc));                                    // Description
                $p .= $this->cell(3, $y, $this->lq_e($r->qty), 'center');                        // Qty
                $p .= $this->cell(4, $y, $this->lq_e($r->measure_type), 'center');               // Unit
                $p .= $this->cell(5, $y, number_format((float)$r->unitprice, 2), 'right');       // Unit Price
                $p .= $this->cell(6, $y, number_format((float)$r->netprice, 2), 'right');        // Value
            }

            // ---------- total / continued ----------
            if ($isLast) {
                $p .= $this->cell(5, $this->totalY, 'TOTAL', 'right', true, 28);
                $p .= $this->cell(6, $this->totalY, number_format($net, 2), 'right', true, 28);
            } else {
                $p .= $this->cell(2, $this->totalY, 'Continued on next page...', 'right', true, 28, 6);
            }

            // ---------- bottom-left (free area left of the Director box) ----------
            if ($isLast && $supplierId != 65 && $remark !== '') {
                $p .= $this->at(105, 1432, 790, 44, 'Remark : ' . $this->lq_e($remark));
            }
            if ($totalPages > 1) {
                $p .= $this->at(105, 1546, 300, 22, 'Page ' . ($index + 1) . ' of ' . $totalPages);
            }

            $body .= $this->lq_page($p, $isLast);
        }

        $html = $this->lq_doc('Purchase Order - ' . $po->porder_no, $body);

        $this->load->library('pdf');
        $this->pdf->setPaper(array(0, 0, $this->paperW * $this->kx, $this->paperH * $this->ky));
        $this->pdf->loadHtml($html);
        $this->pdf->render();
        $this->lq_noscale();
        $this->pdf->stream($this->lq_filename(strtoupper($co->companyname) . ' - PURCHASE ORDER - ' . $po->porder_no) . '.pdf', array("Attachment" => 0));
    }

    /* ================= data-only print helpers (continuous stationery) ================= */

    // ?guide ?ruler ?sx ?sy ?dx ?dy test / calibration switches
    private function lq_flags(){
        if ($this->input->get('guide')) { $this->guide = true; }
        if ($this->input->get('ruler')) { $this->ruler = true; }
        $v = $this->input->get('sx'); if (is_numeric($v) && $v > 0.5 && $v < 2) { $this->kx = (float)$v; }
        $v = $this->input->get('sy'); if (is_numeric($v) && $v > 0.5 && $v < 2) { $this->ky = (float)$v; }
        $v = $this->input->get('dx'); if (is_numeric($v) && abs($v) < 100) { $this->shiftX = (float)$v; }
        $v = $this->input->get('dy'); if (is_numeric($v) && abs($v) < 100) { $this->shiftY = (float)$v; }
    }

    // ask the PDF viewer to print at 100% (no "Fit to page"); best effort, depends on the Dompdf version
    private function lq_noscale(){
        $c = method_exists($this->pdf, 'getCanvas') ? $this->pdf->getCanvas() : $this->pdf->get_canvas();
        if (method_exists($c, 'get_cpdf') && method_exists($c->get_cpdf(), 'addViewerPreference')) {
            $c->get_cpdf()->addViewerPreference('PrintScaling', 'None');
        }
    }

    // cm ruler along the top and left (print with ?ruler=1 and measure it: 10cm must be 10cm)
    private function lq_ruler(){
        $cm  = 28.3465;
        $out = '';
        for ($i = 0; $i <= 24; $i++) {
            $x   = round(($i * $cm + $this->shiftX * 2.8346) * $this->kx, 2);
            $len = ($i % 5 == 0) ? 16 : 8;
            $out .= '<div style="position:absolute;left:' . $x . 'pt;top:8pt;width:0.7pt;height:' . $len . 'pt;background:#000;"></div>';
            if ($i % 5 == 0) {
                $out .= '<div style="position:absolute;left:' . ($x + 2) . 'pt;top:25pt;font-size:7pt;line-height:8pt;">' . $i . 'cm</div>';
            }
        }
        for ($j = 0; $j <= 27; $j++) {
            $y   = round(($j * $cm + $this->shiftY * 2.8346) * $this->ky, 2);
            $len = ($j % 5 == 0) ? 16 : 8;
            $out .= '<div style="position:absolute;left:30pt;top:' . $y . 'pt;width:' . $len . 'pt;height:0.7pt;background:#000;"></div>';
            if ($j % 5 == 0) {
                $out .= '<div style="position:absolute;left:48pt;top:' . ($y - 4) . 'pt;font-size:7pt;line-height:8pt;">' . $j . 'cm</div>';
            }
        }
        return $out;
    }

    // cm -> artwork px
    private function cm($v){
        return $v * $this->artW / 24.13;
    }

    // artwork px -> pt
    private function px($v){
        return round($v * $this->paperW / $this->artW, 2);
    }

    // one cell of the items grid: columns $i .. $to (widths come from $colW, in cm)
    private function cell($i, $y, $html, $align = 'left', $bold = false, $h = null, $to = null){
        $to   = ($to === null) ? $i : $to;
        $left = $this->colX0 + array_sum(array_slice($this->colW, 0, $i)) + $this->colPad;
        $w    = array_sum(array_slice($this->colW, $i, $to - $i + 1)) - 2 * $this->colPad;
        return $this->at($this->cm($left), $y, $this->cm($w), ($h === null ? $this->rowH : $h), $html, $align, $bold);
    }

    // one absolutely positioned data field; $html must already be escaped; $vc = centre a single line vertically in the box
    private function at($x, $y, $w, $h, $html, $align = 'left', $bold = false, $vc = false){
        $left = round(($this->px($x) + $this->shiftX * 2.8346) * $this->kx, 2);
        $top  = round(($this->px($y) + $this->shiftY * 2.8346) * $this->ky, 2);
        $wpt  = round($this->px($w) * $this->kx, 2);
        $hpt  = round($this->px($h) * $this->ky, 2);
        $st = 'position:absolute;left:' . $left . 'pt;top:' . $top . 'pt;width:' . $wpt . 'pt;height:' . $hpt . 'pt;'
            . 'overflow:hidden;text-align:' . $align . ';';
        if ($bold) { $st .= 'font-weight:bold;'; }
        if ($vc)   { $st .= 'line-height:' . $hpt . 'pt;'; }
        if ($this->guide) { $st .= 'outline:0.4pt solid #f00;'; }
        return '<div style="' . $st . '">' . $html . '</div>';
    }

    private function lq_e($v){
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }

    // several text lines, empty ones skipped, first line optionally bold
    private function lq_lines($arr, $boldFirst = false){
        $out = array();
        foreach ($arr as $line) {
            $line = trim((string)$line);
            if ($line !== '') { $out[] = $this->lq_e($line); }
        }
        if ($boldFirst && !empty($out)) {
            $out[0] = '<b>' . $out[0] . '</b>';
        }
        return implode('<br>', $out);
    }

    // first non-empty column found on the row ('' when none exists)
    private function lq_pick($row, $names){
        foreach ($names as $n) {
            if (isset($row->$n)) {
                $v = trim((string)$row->$n);
                if ($v !== '' && strpos($v, '0000-00-00') !== 0) { return $v; }
            }
        }
        return '';
    }

    private function lq_page($inner, $isLast){
        return '<div style="position:relative;width:' . round($this->paperW * $this->kx, 2) . 'pt;height:' . round(($this->paperH - 6) * $this->ky, 2) . 'pt;'
            . ($isLast ? '' : 'page-break-after:always;') . '">' . $inner . '</div>';
    }

    private function lq_doc($title, $body){
        $css = '@page { margin: 0; } html, body { margin: 0; padding: 0; } '
             . 'body { font-family: "Courier", monospace; font-size: ' . round($this->fontSize * $this->kx, 2) . 'pt; '
             . 'line-height: ' . round($this->lineHeight * $this->ky, 2) . 'pt; color: #000; }';
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . $this->lq_e($title) . '</title><style>'
            . $css . '</style></head><body>' . $body . '</body></html>';
    }

    private function lq_filename($s){
        return trim(preg_replace('/[^A-Za-z0-9 _.\-]+/', '-', $s));
    }
}