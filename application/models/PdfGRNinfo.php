<?php
class PdfGRNinfo extends CI_Model {

    /* =====================================================================
       GRN on pre-printed continuous stationery (LQ-310)
       Artwork = 1382 x 1600 px  =  9.5in x 11in  =  684 x 792 pt
       Every x / y / w / h used by the GRN print is in ARTWORK PIXELS measured
       from the top-left corner of the artwork (converted to pt by px()).
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

    // GRN items grid (body of the form: y 466 - 1342)
    private $rowsPerPage = 17;    // GRN item rows per page
    private $rowTop      = 476;   // first row y
    private $rowH        = 44;    // row height (2 text lines fit)
    private $totalsY     = 1236;  // first totals line (3 lines, 22 px apart) / "continued" line is the 3rd

    private $voucherRowsPerPage = 9;   // voucher item rows per page (leaves room for the importation split)

    /* ------------------------------------------------------------------
     * GOODS RECEIVED NOTE  (data only, pre-printed continuous paper)
     * ------------------------------------------------------------------ */
    public function pdfgrnget($x) {
        $recordID = $x;

        $this->db->select("
            *,
            COALESCE(tbl_grn.idtbl_grn, 0) AS idtbl_grn,
            COALESCE(tbl_grn.subtotalcost, 0) AS grn_subtotal,
            COALESCE(tbl_grn.totalcost, 0) AS grn_total,
            COALESCE(tbl_grn.vatamountcost, 0) AS vatamount,
            COALESCE(tbl_grn.discount, 0) AS discount,
            COALESCE(tbl_grn.remark, '') AS remark,
            COALESCE(tbl_grndetail.qty, 0) AS qty,
            COALESCE(tbl_grndetail.costunitprice, 0) AS costunitprice,
            COALESCE(tbl_category.idtbl_category, 0) AS idtbl_category,
            COALESCE(tbl_grndetail.comment, '') AS comment,
            tbl_grndetail.tbl_product_idtbl_product AS grn_product_id,

            (
                SELECT COALESCE(pd.qty, 0)
                FROM tbl_porder_detail pd
                WHERE pd.status = 1
                AND pd.tbl_porder_idtbl_porder = tbl_grn.tbl_porder_idtbl_porder
                AND pd.tbl_product_idtbl_product = tbl_grndetail.tbl_product_idtbl_product
                ORDER BY
                    ABS(pd.qty - tbl_grndetail.qty) ASC,
                    pd.idtbl_porder_detail ASC
                LIMIT 1
            ) AS ordered_qty,

            (
                SELECT COALESCE(SUM(gd.qty), 0)
                FROM tbl_grn g
                INNER JOIN tbl_grndetail gd
                    ON g.idtbl_grn = gd.tbl_grn_idtbl_grn
                WHERE g.tbl_porder_idtbl_porder =
                    tbl_grn.tbl_porder_idtbl_porder
                AND gd.tbl_product_idtbl_product =
                    tbl_grndetail.tbl_product_idtbl_product
                AND g.status = 1
                AND gd.status = 1
                AND g.idtbl_grn < tbl_grn.idtbl_grn
            ) AS prev_qty

        ", false);
        $this->db->from('tbl_grn');
        $this->db->join('tbl_grndetail', 'tbl_grn.idtbl_grn = tbl_grndetail.tbl_grn_idtbl_grn', 'left');
        $this->db->join('tbl_product', 'tbl_grndetail.tbl_product_idtbl_product = tbl_product.idtbl_product', 'left');
        $this->db->join('tbl_supplier', 'tbl_grn.tbl_supplier_idtbl_supplier = tbl_supplier.idtbl_supplier', 'left');
        $this->db->join('tbl_category', 'tbl_product.tbl_category_idtbl_category = tbl_category.idtbl_category', 'left');
        $this->db->join('tbl_unit', 'tbl_product.tbl_unit_idtbl_unit = tbl_unit.idtbl_unit', 'left');
        $this->db->join('tbl_porder', 'tbl_grn.tbl_porder_idtbl_porder = tbl_porder.idtbl_porder', 'left');
        $this->db->where('tbl_grn.idtbl_grn', $recordID);
        $query = $this->db->get();

        if ($query->num_rows() == 0) {
            show_404();
            return;
        }

        $q = $query->row();

        $remark    = trim((string)$q->remark);
        $totalSum  = $q->grn_subtotal;
        $grn_total = $q->grn_total;
        $grn_vat   = $q->vatamount;

        $this->db->select('tbl_company.company AS companyname,tbl_company.address1 As companyaddress,tbl_company.mobile AS companymobile, tbl_company.phone companyphone,tbl_company.email AS companyemail, tbl_company_branch.branch AS branchname');
        $this->db->from('tbl_grn');
        $this->db->join('tbl_company', 'tbl_company.idtbl_company = tbl_grn.tbl_company_idtbl_company', 'left');
        $this->db->join('tbl_company_branch', 'tbl_company_branch.idtbl_company_branch = tbl_grn.tbl_company_branch_idtbl_company_branch', 'left');
        $this->db->where('tbl_grn.idtbl_grn', $recordID);
        $companydetails = $this->db->get();
        $co = $companydetails->row();

        $grnNo = $q->grn_no;

        $supplierAddr = array($q->delivery_address_line1, $q->delivery_address_line2, $q->delivery_city, $q->delivery_state);
        $receivedAt   = (trim((string)$co->branchname) !== '') ? $co->branchname : $co->companyname;

        // left box: supplier
        $leftBox = $this->lq_e('Received from :') . '<br><b>' . $this->lq_e($q->suppliername) . '</b>';
        $addrHtml = $this->lq_lines($supplierAddr);
        if ($addrHtml !== '') {
            $leftBox .= '<br>' . $addrHtml;
        }

        // right box: GRN details (label / value pairs)
        $details = array(
            array('GRN Date',    $q->grndate),
            array('Invoice No',  $q->invoicenum),
            array('Batch No',    $q->batchno),
            array('Received at', $receivedAt)
        );

        $pages = array_chunk($query->result(), $this->rowsPerPage);
        if (empty($pages)) {
            $pages = array(array());
        }
        $totalPages = count($pages);
        $body = '';

        foreach ($pages as $index => $lines) {
            $isLast = ($index == $totalPages - 1);

            // ---------- top right boxes ----------
            $p  = $this->at(1093, 125, 170, 24, $this->lq_e($grnNo));                       // No.      (GRN No)
            $p .= $this->at(1093, 177, 170, 24, ($index + 1) . ' of ' . $totalPages);       // Page No.
            $p .= $this->at(1093, 229, 170, 24, $this->lq_e($q->porder_no));                // No.      (PO No)

            // ---------- title box ----------
            $p .= $this->at(536, 219, 311, 26, 'GOODS RECEIVED NOTE', 'center', true, 10);

            // ---------- two big boxes ----------
            $p .= $this->at(125, 281, 545, 128, $leftBox);
            foreach ($details as $i => $d) {
                $y = 281 + $i * 24;
                $p .= $this->at(712, $y, 165, 22, $this->lq_e($d[0]) . ' :');
                $p .= $this->at(885, $y, 380, 22, $this->lq_e($d[1]), 'left', true);
            }

            // ---------- items grid ----------
            foreach ($lines as $i => $r) {
                $y = $this->rowTop + $i * $this->rowH;

                $desc  = ($r->idtbl_category == 4) ? $r->comment : $r->product_name;
                $price = !empty($r->unitprice) ? $r->unitprice : $r->costunitprice;

                $p .= $this->at(119,  $y, 124, $this->rowH, $this->lq_e($r->product_code));                         // Stock Code
                $p .= $this->at(257,  $y, 100, $this->rowH, $this->lq_e($r->model_no));                             // Model No.
                $p .= $this->at(372,  $y, 438, $this->rowH, $this->lq_e($desc));                                    // Description
                $p .= $this->at(816,  $y, 95,  $this->rowH, $this->lq_e((float)$r->qty), 'center');                 // Qty.
                $p .= $this->at(911,  $y, 137, $this->rowH, number_format((float)$price, 2), 'right');              // Unit Price
                $p .= $this->at(1058, $y, 205, $this->rowH, number_format((float)$r->total, 2), 'right');           // Total
            }

            // ---------- totals / continued (bottom of the grid) ----------
            if ($isLast) {
                // $p .= $this->at(911, $this->totalsY,      137, 22, 'Sub Total', 'right');
                // $p .= $this->at(1058, $this->totalsY,     205, 22, number_format((float)$totalSum, 2), 'right');
                // $p .= $this->at(911, $this->totalsY + 22, 137, 22, 'VAT', 'right');
                // $p .= $this->at(1058, $this->totalsY + 22, 205, 22, number_format((float)$grn_vat, 2), 'right');
                $p .= $this->at(911, $this->totalsY + 44, 137, 22, 'Total', 'right', true);
                $p .= $this->at(1058, $this->totalsY + 44, 205, 22, number_format((float)$grn_total, 2), 'right', true);

                // ---------- wide box under the grid ----------
                if ($remark !== '') {
                    $p .= $this->at(125, 1360, 1135, 42, 'Remarks : ' . $this->lq_e($remark));
                }
            } else {
                $p .= $this->at(372, $this->totalsY + 44, 438, 22, 'Continued on next page...', 'right', true);
            }

            $body .= $this->grn_page($p, $isLast);
        }

        $html = $this->grn_doc('Goods Received Note - ' . $grnNo, $body);

        $this->load->library('pdf');
        $this->pdf->setPaper(array(0, 0, $this->paperW, $this->paperH));
        $this->pdf->loadHtml($html);
        $this->pdf->render();
        $this->pdf->stream($this->lq_filename('Goods Received Note - ' . $grnNo) . '.pdf', array("Attachment" => 0));
    }

    /* ------------------------------------------------------------------
     * GOOD RECEIVE VOUCHER  (A4 portrait, black and white)
     * ------------------------------------------------------------------ */
    public function VoucherPdf($x){
        $recordID = $x;

        $sql = "SELECT *, `tbl_grn`.`subtotal` AS `grnsubtotal` FROM `tbl_grn_vouchar_import_cost`
        LEFT JOIN `tbl_grn` ON `tbl_grn`.`idtbl_grn` = `tbl_grn_vouchar_import_cost`.`tbl_grn_idtbl_grn`
        LEFT JOIN `tbl_supplier` ON `tbl_supplier`.`idtbl_supplier` = `tbl_grn`.`tbl_supplier_idtbl_supplier`
        LEFT JOIN `tbl_porder` ON `tbl_porder`.`idtbl_porder` = `tbl_grn`.`tbl_porder_idtbl_porder`
        WHERE `idtbl_grn_vouchar_import_cost` = ?";
        $respond = $this->db->query($sql, array($recordID));

        if ($respond->num_rows() == 0) {
            show_404();
            return;
        }

        $v = $respond->row(0);
        $grnID = $v->idtbl_grn;

        $this->db->select('tbl_grn_vouchar_import_cost.*, tbl_company.company AS companyname,tbl_company.address1 As companyaddress,tbl_company.mobile AS companymobile, tbl_company.phone companyphone,tbl_company.email AS companyemail, tbl_company_branch.branch AS branchname');
        $this->db->from('tbl_grn_vouchar_import_cost');
        $this->db->join('tbl_company', 'tbl_company.idtbl_company = tbl_grn_vouchar_import_cost.tbl_company_idtbl_company', 'left');
        $this->db->join('tbl_company_branch', 'tbl_company_branch.idtbl_company_branch = tbl_grn_vouchar_import_cost.tbl_company_branch_idtbl_company_branch', 'left');
        $this->db->where('tbl_grn_vouchar_import_cost.idtbl_grn_vouchar_import_cost', $recordID);
        $companydetails = $this->db->get();
        $co = $companydetails->row();

        $this->db->select('*, COALESCE(tbl_grn.idtbl_grn, 0) AS idtbl_grn, COALESCE(tbl_grn.total, 0) AS grn_total, COALESCE(tbl_grn.discount, 0) AS discount, COALESCE(tbl_grndetail.qty, 0) AS qty, COALESCE(tbl_grndetail.costunitprice, 0) AS costunitprice');
        $this->db->from('tbl_grn');
        $this->db->join('tbl_grndetail', 'tbl_grn.idtbl_grn = tbl_grndetail.tbl_grn_idtbl_grn', 'left');
        $this->db->join('tbl_product', 'tbl_grndetail.tbl_product_idtbl_product = tbl_product.idtbl_product', 'left');
        $this->db->join('tbl_supplier', 'tbl_grn.tbl_supplier_idtbl_supplier = tbl_supplier.idtbl_supplier', 'left');
        $this->db->join('tbl_unit', 'tbl_product.tbl_unit_idtbl_unit = tbl_unit.idtbl_unit', 'left');
        $this->db->where('tbl_grn.idtbl_grn', $grnID);
        $respondgrn = $this->db->get();

        $sql2 = "SELECT
        `tbl_grn_vouchar_import_cost_detail`.*,
        `tbl_import_cost_types`.`cost_type`
        FROM `tbl_grn_vouchar_import_cost_detail`
        LEFT JOIN `tbl_import_cost_types` ON `tbl_import_cost_types`.`idtbl_import_cost_types` = `tbl_grn_vouchar_import_cost_detail`.`tbl_import_cost_types_idtbl_import_cost_types`
        WHERE
        `tbl_grn_vouchar_import_cost_detail`.`status` = ?
        AND `tbl_grn_vouchar_import_cost_detail`.`tbl_grn_vouchar_import_cost_idtbl_grn_vouchar_import_cost` = ?";
        $respond2 = $this->db->query($sql2, array(1, $recordID));

        $coTel = trim($co->companymobile . ' / ' . $co->companyphone, ' /');
        $supplierAddr = array($v->address_line1, $v->address_line2, $v->city);

        $pages = array_chunk($respondgrn->result(), $this->voucherRowsPerPage);
        if (empty($pages)) {
            $pages = array(array());
        }
        $totalPages = count($pages);
        $body = '';

        foreach ($pages as $index => $lines) {
            $isLast = ($index == $totalPages - 1);

            $p  = $this->lq_head('GOOD RECEIVE VOUCHER', $co->companyname, $co->companyaddress,
                          array('Tel: ' . $coTel, 'E-Mail: ' . $co->companyemail));

            $p .= $this->lq_boxes(array(
                $this->lq_kv(array(array('Order No', $v->porder_no), array('Date', $v->grndate))),
                $this->lq_kv(array(array('Supplier Invoice', $v->invoicenum), array('Our Reference', $v->invoiceno)))
            ));
            $p .= $this->lq_sp(6);
            $p .= $this->lq_boxes(array(
                $this->lq_kv(array(array('Supplier', $v->suppliername), array('Address', $supplierAddr))),
                $this->lq_kv(array(array('Company', $co->companyname), array('Address', $co->companyaddress), array('Telephone No.', $coTel)))
            ));
            $p .= $this->lq_sp(8);

            $p .= '<table class="items">
                <thead><tr>
                    <th style="width:11%;">Item Code</th>
                    <th style="width:22%;text-align:left;">Description of Goods</th>
                    <th style="width:8%;">Ordered</th>
                    <th style="width:6%;">Prev</th>
                    <th style="width:8%;">Qty</th>
                    <th style="width:6%;">Unit</th>
                    <th style="width:12%;">Price (Ex)</th>
                    <th style="width:7%;">Disc %</th>
                    <th style="width:9%;">Tax</th>
                    <th style="width:11%;">Total (Inc)</th>
                </tr></thead><tbody>';

            foreach ($lines as $g) {
                $p .= '<tr style="font-size:9px;">
                    <td>' . $this->lq_e($g->product_code) . '</td>
                    <td>' . $this->lq_e($g->product_name) . '</td>
                    <td style="text-align:center;">' . $this->lq_e($g->qty) . '</td>
                    <td style="text-align:center;">0.00</td>
                    <td style="text-align:center;font-weight:bold;">' . $this->lq_e($g->qty) . '</td>
                    <td style="text-align:center;">' . $this->lq_e($g->unit) . '</td>
                    <td style="text-align:right;">' . number_format((float)$g->costunitprice, 2) . '</td>
                    <td style="text-align:center;">' . $this->lq_e($g->unit_discount) . '</td>
                    <td style="text-align:right;">' . number_format((float)$g->costunitprice, 2) . '</td>
                    <td style="text-align:right;">' . number_format((float)$g->total, 2) . '</td>
                </tr>';
            }
            $p .= $this->lq_pad_rows(count($lines), $this->voucherRowsPerPage, 10);
            $p .= '</tbody></table>';

            if ($isLast) {
                /* ---------- importation split ---------- */
                if ($respond2->num_rows() > 0) {
                    $p .= $this->lq_sp(8);
                    $p .= '<div class="section">Importation Split - Additional Cost Allocation</div>';
                    $p .= '<table class="items"><thead><tr>
                            <th style="width:6%;">#</th>
                            <th style="width:24%;text-align:left;">Cost Type</th>
                            <th style="width:30%;text-align:left;">Description</th>
                            <th style="width:14%;">Amount (Excl)</th>
                            <th style="width:12%;">Tax Amount</th>
                            <th style="width:14%;">Total</th>
                        </tr></thead><tbody>';

                    $totalcostamount = 0;
                    $j = 0;
                    foreach ($respond2->result() as $rowlist) {
                        $j++;
                        $amount = (float)$rowlist->cost_amount;
                        $totalcostamount += $amount;
                        $desc = trim((string)$rowlist->comment) !== '' ? $rowlist->comment : $rowlist->cost_type;

                        $p .= '<tr>
                            <td style="text-align:center;">' . $j . '</td>
                            <td>' . $this->lq_e($rowlist->cost_type) . '</td>
                            <td>' . $this->lq_e($desc) . '</td>
                            <td style="text-align:right;">' . number_format($amount, 2) . '</td>
                            <td style="text-align:right;">0.00</td>
                            <td style="text-align:right;">' . number_format($amount, 2) . '</td>
                        </tr>';
                    }
                    $p .= '<tr>
                            <td colspan="3" style="font-weight:bold;">Importation Split Totals</td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($totalcostamount, 2) . '</td>
                            <td style="text-align:right;font-weight:bold;">0.00</td>
                            <td>&nbsp;</td>
                        </tr></tbody></table>';
                }

                $p .= $this->lq_sp(8);
                $p .= $this->lq_totals(array(
                    array('Total (Excl)', number_format((float)$v->grnsubtotal, 2)),
                    array('Tax', number_format((float)$v->vatamount, 2)),
                    array('Discount', number_format((float)$v->discount, 2)),
                    array('Total (Incl)', number_format((float)$v->total, 2), true)
                ), 16);

                $p .= $this->lq_sp(8);
                $p .= $this->lq_sign(array(
                    array('Received by', '', 'Signature'),
                    array('Date', '', ''),
                    array('Signed', '', 'Signature')
                ));
            } else {
                $p .= '<div class="cont">Continued on next page...</div>';
            }

            $body .= $this->lq_page($p, $isLast, $index + 1, $totalPages);
        }

        $html = $this->lq_doc('Good Receive Voucher', $body);

        $this->load->library('pdf');
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->loadHtml($html);
        $this->pdf->render();
        $this->pdf->stream($this->lq_filename('Good Receive Voucher - ' . $recordID) . '.pdf', array("Attachment" => 0));
    }

    /* ================= data-only print helpers (GRN on pre-printed stationery) ================= */

    // artwork px -> pt
    private function px($v){
        return round($v * $this->paperW / $this->artW, 2);
    }

    // one absolutely positioned data field; $html must already be escaped
    private function at($x, $y, $w, $h, $html, $align = 'left', $bold = false, $size = null){
        $left = $this->px($x) + $this->shiftX * 2.8346;
        $top  = $this->px($y) + $this->shiftY * 2.8346;
        $st = 'position:absolute;left:' . $left . 'pt;top:' . $top . 'pt;width:' . $this->px($w) . 'pt;height:' . $this->px($h) . 'pt;'
            . 'overflow:hidden;text-align:' . $align . ';';
        if ($bold) { $st .= 'font-weight:bold;'; }
        if ($size) { $st .= 'font-size:' . $size . 'pt;'; }
        if ($this->guide) { $st .= 'outline:0.4pt solid #f00;'; }
        return '<div style="' . $st . '">' . $html . '</div>';
    }

    // several text lines, empty ones skipped
    private function lq_lines($arr){
        $out = array();
        foreach ($arr as $line) {
            $line = trim((string)$line);
            if ($line !== '') { $out[] = $this->lq_e($line); }
        }
        return implode('<br>', $out);
    }

    private function grn_page($inner, $isLast){
        return '<div style="position:relative;width:' . $this->paperW . 'pt;height:' . ($this->paperH - 6) . 'pt;'
            . ($isLast ? '' : 'page-break-after:always;') . '">' . $inner . '</div>';
    }

    private function grn_doc($title, $body){
        $css = '@page { margin: 0; } html, body { margin: 0; padding: 0; } '
             . 'body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: ' . $this->fontSize . 'pt; '
             . 'line-height: ' . $this->lineHeight . 'pt; color: #000; }';
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . $this->lq_e($title) . '</title><style>'
            . $css . '</style></head><body>' . $body . '</body></html>';
    }

    /* ================= LQ-310 print design helpers (black and white, A4 portrait) - used by the voucher ================= */

    private function lq_e($v){
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }

    private function lq_val($v){
        if (is_array($v)) {
            $lines = array();
            foreach ($v as $line) {
                $line = trim((string)$line);
                if ($line !== '') { $lines[] = $this->lq_e($line); }
            }
            return empty($lines) ? '&nbsp;' : implode('<br>', $lines);
        }
        $v = trim((string)$v);
        return $v === '' ? '&nbsp;' : $this->lq_e($v);
    }

    private function lq_css(){
        return '
@page { size: A4 portrait; margin: 8mm; }
body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 10px; color: #000; margin: 0; line-height: 1.3; }
table { border-collapse: collapse; width: 100%; }
.title { border-top: 2px solid #000; border-bottom: 2px solid #000; text-align: center; font-size: 20px; font-weight: bold; letter-spacing: 3px; padding: 5px 0; }
.co { text-align: center; padding: 8px 0; line-height: 1.45; }
.coname { font-size: 14px; font-weight: bold; }
table.outer { border-collapse: separate; border-spacing: 0; table-layout: fixed; width: 100%; }
td.half { border: 1px solid #000; padding: 3px 6px; vertical-align: top; }
table.kv { table-layout: fixed; }
table.kv td { border: none; padding: 2px 0; vertical-align: top; }
table.kv td.k { width: 34%; font-weight: bold; }
table.kv td.c { width: 4%; }
table.items { table-layout: fixed; }
table.items th { border: 1px solid #000; padding: 4px; font-size: 10px; font-weight: bold; }
table.items td { border: 1px solid #000; padding: 2px 5px; font-size: 10px; height: 19px; vertical-align: middle; }
table.items tr { page-break-inside: avoid; }
table.tot { table-layout: fixed; }
table.tot td { border: 1px solid #000; padding: 3px 6px; font-size: 10px; }
table.tot td.n { text-align: right; }
table.tot tr.b td { font-weight: bold; font-size: 11px; }
.sigline { border-top: 1px solid #000; text-align: center; font-size: 9px; padding-top: 2px; }
.sp { font-size: 1px; line-height: 1px; }
.pg { text-align: right; font-size: 9px; margin-top: 4px; }
.cont { text-align: right; font-size: 10px; font-weight: bold; margin-top: 6px; }
.section { font-size: 11px; font-weight: bold; margin: 0 0 4px 0; }';
    }

    private function lq_head($title, $companyName, $address, $contactLines = array()){
        $h  = '<div class="title">' . $this->lq_e($title) . '</div>';
        $h .= '<div class="co"><div class="coname">' . $this->lq_e($companyName) . '</div>';
        if (trim((string)$address) !== '') { $h .= '<div>' . $this->lq_e($address) . '</div>'; }
        foreach ($contactLines as $l) {
            if (trim((string)$l) !== '') { $h .= '<div>' . $this->lq_e($l) . '</div>'; }
        }
        return $h . '</div>';
    }

    private function lq_kv($pairs){
        $h = '<table class="kv">';
        foreach ($pairs as $p) {
            $h .= '<tr><td class="k">' . $this->lq_e($p[0]) . '</td><td class="c">:</td><td>' . $this->lq_val($p[1]) . '</td></tr>';
        }
        return $h . '</table>';
    }

    private function lq_boxes($cells){
        $n   = count($cells);
        $gap = 2;
        $w   = (100 - $gap * ($n - 1)) / $n;
        $h   = '<table class="outer"><tr>';
        foreach ($cells as $i => $c) {
            if ($i > 0) { $h .= '<td style="width:' . $gap . '%;"></td>'; }
            $h .= '<td class="half" style="width:' . $w . '%;">' . $c . '</td>';
        }
        return $h . '</tr></table>';
    }

    private function lq_sp($px = 6){
        return '<div class="sp" style="height:' . (int)$px . 'px;">&nbsp;</div>';
    }

    private function lq_pad_rows($used, $perPage, $cols){
        $h = '';
        for ($k = $used; $k < $perPage; $k++) {
            $h .= '<tr>' . str_repeat('<td>&nbsp;</td>', $cols) . '</tr>';
        }
        return $h;
    }

    private function lq_totals($rows, $amountWidth = 22){
        $h = '<table class="tot">';
        foreach ($rows as $r) {
            $bold = !empty($r[2]);
            $h .= '<tr' . ($bold ? ' class="b"' : '') . '><td style="width:' . (100 - $amountWidth) . '%;">' . $this->lq_e($r[0]) . '</td>'
                . '<td class="n" style="width:' . $amountWidth . '%;">' . $this->lq_e($r[1]) . '</td></tr>';
        }
        return $h . '</table>';
    }

    private function lq_sign($items){
        $cells = array();
        foreach ($items as $it) {
            $name    = isset($it[1]) ? trim((string)$it[1]) : '';
            $caption = isset($it[2]) ? $it[2] : 'Signature';
            $cells[] = '<b>' . $this->lq_e($it[0]) . '</b>'
                . '<div style="height:38px;text-align:center;padding-top:22px;">' . ($name !== '' ? $this->lq_e($name) : '&nbsp;') . '</div>'
                . '<div class="sigline">' . ($caption !== '' ? $this->lq_e($caption) : '&nbsp;') . '</div>';
        }
        return $this->lq_boxes($cells);
    }

    private function lq_page($inner, $isLast, $pageNo, $total){
        return '<div' . ($isLast ? '' : ' style="page-break-after:always;"') . '>' . $inner
            . '<div class="pg">Page ' . (int)$pageNo . ' of ' . (int)$total . '</div></div>';
    }

    private function lq_doc($title, $body){
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . $this->lq_e($title) . '</title><style>'
            . $this->lq_css() . '</style></head><body>' . $body . '</body></html>';
    }

    private function lq_filename($s){
        return trim(preg_replace('/[^A-Za-z0-9 _.\-]+/', '-', $s));
    }
}