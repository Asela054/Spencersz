<?php
class PorderPrintinfo extends CI_Model{

    /* =====================================================================
       Pre-printed continuous stationery (LQ-310)
       Artwork = 1382 x 1600 px  =  9.5in x 11in  =  684 x 792 pt
       Every x / y / w / h below is in ARTWORK PIXELS, measured from the
       top-left corner of the artwork. They are converted to pt automatically.
       Only DATA is printed - the form design is already on the paper.
       ===================================================================== */
    private $paperW = 684;     // 9.5in in pt
    private $paperH = 792;     // 11in in pt
    private $artW   = 1382;    // artwork width in px

    private $shiftX = 0;       // printer calibration in mm (+ moves right)
    private $shiftY = 0;       // printer calibration in mm (+ moves down)
    private $guide  = false;   // true = red outline around every field (alignment test)

    private $fontSize   = 9;    // pt
    private $lineHeight = 10.5; // pt

    // items grid (body of the form: y 535 - 1416)
    private $rowsPerPage = 18;
    private $rowTop      = 545;   // first row y
    private $rowH        = 44;    // row height (2 text lines fit)
    private $totalY      = 1372;  // total / "continued" line, below the last row

    public function Printinvoice($x){
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

            // ---------- header boxes ----------
            $p  = $this->at(1082, 112, 196, 24, $this->lq_e($po->porder_no));      // No.
            $p .= $this->at(1082, 163, 196, 24, $this->lq_e($po->orderdate));      // Date
            $p .= $this->at(115, 245, 380, 118, $this->lq_lines($delLines, true)); // Delivery
            $p .= $this->at(899, 245, 380, 118, $this->lq_lines($toLines, true));  // To Messers
            $p .= $this->at(277, 387, 217, 26, $this->lq_e($onOrBefore));          // ON or BEFORE
            $p .= $this->at(1061, 387, 217, 26, $this->lq_e($creditPeriod));       // Credit Period

            // ---------- items grid ----------
            $n = $index * $this->rowsPerPage;
            foreach ($lines as $i => $r) {
                $n++;
                $y = $this->rowTop + $i * $this->rowH;

                $desc = (string)$r->product_name;
                if (trim((string)$r->comment) !== '') {
                    $desc .= ' - ' . $r->comment;
                }

                $p .= $this->at(97,   $y, 135, $this->rowH, $n, 'center');                                        // Seq. No.
                $p .= $this->at(240,  $y, 170, $this->rowH, $this->lq_e($r->product_code));                       // Stock Code
                $p .= $this->at(424,  $y, 357, $this->rowH, $this->lq_e($desc));                                  // Description
                $p .= $this->at(787,  $y, 110, $this->rowH, $this->lq_e($r->qty), 'center');                      // Qty
                $p .= $this->at(897,  $y, 110, $this->rowH, $this->lq_e($r->measure_type), 'center');             // Unit
                $p .= $this->at(1007, $y, 138, $this->rowH, number_format((float)$r->unitprice, 2), 'right');     // Unit Price
                $p .= $this->at(1155, $y, 123, $this->rowH, number_format((float)$r->netprice, 2), 'right');      // Value
            }

            // ---------- total / continued ----------
            if ($isLast) {
                $p .= $this->at(1007, $this->totalY, 138, 28, 'TOTAL', 'right', true);
                $p .= $this->at(1155, $this->totalY, 123, 28, number_format($net, 2), 'right', true);
            } else {
                $p .= $this->at(416, $this->totalY, 862, 28, 'Continued on next page...', 'right', true);
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
        $this->pdf->setPaper(array(0, 0, $this->paperW, $this->paperH));
        $this->pdf->loadHtml($html);
        $this->pdf->render();
        $this->pdf->stream($this->lq_filename(strtoupper($co->companyname) . ' - PURCHASE ORDER - ' . $po->porder_no) . '.pdf', array("Attachment" => 0));
    }

    /* ================= data-only print helpers (continuous stationery) ================= */

    // artwork px -> pt
    private function px($v){
        return round($v * $this->paperW / $this->artW, 2);
    }

    // one absolutely positioned data field; $html must already be escaped
    private function at($x, $y, $w, $h, $html, $align = 'left', $bold = false){
        $left = $this->px($x) + $this->shiftX * 2.8346;
        $top  = $this->px($y) + $this->shiftY * 2.8346;
        $st = 'position:absolute;left:' . $left . 'pt;top:' . $top . 'pt;width:' . $this->px($w) . 'pt;height:' . $this->px($h) . 'pt;'
            . 'overflow:hidden;text-align:' . $align . ';';
        if ($bold) { $st .= 'font-weight:bold;'; }
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
        return '<div style="position:relative;width:' . $this->paperW . 'pt;height:' . ($this->paperH - 6) . 'pt;'
            . ($isLast ? '' : 'page-break-after:always;') . '">' . $inner . '</div>';
    }

    private function lq_doc($title, $body){
        $css = '@page { margin: 0; } html, body { margin: 0; padding: 0; } '
             . 'body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: ' . $this->fontSize . 'pt; '
             . 'line-height: ' . $this->lineHeight . 'pt; color: #000; }';
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . $this->lq_e($title) . '</title><style>'
            . $css . '</style></head><body>' . $body . '</body></html>';
    }

    private function lq_filename($s){
        return trim(preg_replace('/[^A-Za-z0-9 _.\-]+/', '-', $s));
    }
}