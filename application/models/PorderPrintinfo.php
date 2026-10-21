<?php
class PorderPrintinfo extends CI_Model{

    private $rowsPerPage = 14;   // item rows per A4 page (blank rows pad the table)

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
        $company_id = $po->tbl_company_idtbl_company;

        $this->db->select('tbl_company.company AS companyname, tbl_company.address1 AS companyaddress, tbl_company.mobile AS companymobile,
                           tbl_company.phone AS companyphone, tbl_company.email AS companyemail,
                           tbl_company_branch.branch AS branchname,
                           preparer.name AS preparedByName,
                           approver.name AS authorizedByName');
        $this->db->from('tbl_porder');
        $this->db->join('tbl_company', 'tbl_company.idtbl_company = tbl_porder.tbl_company_idtbl_company', 'left');
        $this->db->join('tbl_company_branch', 'tbl_company_branch.idtbl_company_branch = tbl_porder.tbl_company_branch_idtbl_company_branch', 'left');
        $this->db->join('tbl_user AS preparer', 'preparer.idtbl_user = tbl_porder.tbl_user_idtbl_user', 'left');
        $this->db->join('tbl_user AS approver', 'approver.idtbl_user = tbl_porder.approve_by', 'left');
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

        // Supplier name (supplier 65 = one-time supplier, name is typed in the remark)
        $supName = array($po->suppliername);
        if ($supplierId == 65 && $remark !== '') {
            $supName[] = $remark;
        }
        $supAddr = array($po->address_line1, $po->address_line2, $po->city);
        $tp = (strlen((string)$po->telephone_no) >= 9) ? $po->telephone_no : '';

        $coTel = trim($co->companymobile . ' / ' . $co->companyphone, ' /');

        $pages = array_chunk($respond2->result(), $this->rowsPerPage);
        if (empty($pages)) {
            $pages = array(array());
        }
        $totalPages = count($pages);
        $body = '';

        foreach ($pages as $index => $lines) {
            $isLast = ($index == $totalPages - 1);

            $p  = $this->lq_head('PURCHASE ORDER', $co->companyname, $co->companyaddress,
                          array('Tel: ' . $coTel, 'E-Mail: ' . $co->companyemail));

            $p .= $this->lq_boxes(array(
                $this->lq_kv(array(array('PO No', $po->porder_no))),
                $this->lq_kv(array(array('Date', $po->orderdate)))
            ));
            $p .= $this->lq_sp(6);

            $rightPairs = array(
                array('Purchaser Name', $co->companyname),
                array('Address', $co->companyaddress),
                array('Telephone No.', $coTel)
            );
            if ($company_id == 1) {
                $rightPairs[] = array('Our VAT No', '103305667-7000');
            }
            $p .= $this->lq_boxes(array(
                $this->lq_kv(array(
                    array('Supplier Name', $supName),
                    array('Address', $supAddr),
                    array('Telephone No.', $tp),
                    array('Attn', '....................................')
                )),
                $this->lq_kv($rightPairs)
            ));
            $p .= $this->lq_sp(8);

            $p .= '<table class="items">
                <thead><tr>
                    <th style="width:5%;">#</th>
                    <th style="width:14%;">Item Code</th>
                    <th style="width:35%;text-align:left;">Description of Goods</th>
                    <th style="width:8%;">Qty</th>
                    <th style="width:8%;">UOM</th>
                    <th style="width:14%;">Unit Price</th>
                    <th style="width:16%;">Amount (Rs.)</th>
                </tr></thead><tbody>';

            $n = $index * $this->rowsPerPage;
            foreach ($lines as $r) {
                $n++;
                $desc = (string)$r->product_name;
                if (trim((string)$r->comment) !== '') {
                    $desc .= ' - ' . $r->comment;
                }
                $p .= '<tr>
                    <td style="text-align:center;">' . $n . '</td>
                    <td style="text-align:center;">' . $this->lq_e($r->product_code) . '</td>
                    <td>' . $this->lq_e($desc) . '</td>
                    <td style="text-align:center;">' . $this->lq_e($r->qty) . '</td>
                    <td style="text-align:center;">' . $this->lq_e($r->measure_type) . '</td>
                    <td style="text-align:right;">' . number_format((float)$r->unitprice, 2) . '</td>
                    <td style="text-align:right;">' . number_format((float)$r->netprice, 2) . '</td>
                </tr>';
            }
            $p .= $this->lq_pad_rows(count($lines), $this->rowsPerPage, 7);
            $p .= '</tbody></table>';

            if ($isLast) {
                $p .= $this->lq_totals(array(
                    array('Total (Excl)', number_format($net, 2)),
                    array('Tax', ''),
                    array('Total (Incl)', number_format($net, 2), true)
                ), 16);

                if ($supplierId != 65 && $remark !== '') {
                    $p .= $this->lq_sp(6);
                    $p .= $this->lq_boxes(array($this->lq_kv(array(array('Remark', $remark)))));
                }

                $p .= $this->lq_sp(8);
                $p .= $this->lq_sign(array(
                    array('Prepared by', $co->preparedByName, 'Signature'),
                    array('Authorized by', $co->authorizedByName, 'Signature'),
                    array('Contact No', '', '')
                ));
            } else {
                $p .= '<div class="cont">Continued on next page...</div>';
            }

            $body .= $this->lq_page($p, $isLast, $index + 1, $totalPages);
        }

        $html = $this->lq_doc('Purchase Order - ' . $po->porder_no, $body);

        $this->load->library('pdf');
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->loadHtml($html);
        $this->pdf->render();
        $this->pdf->stream($this->lq_filename(strtoupper($co->companyname) . ' - PURCHASE ORDER - ' . $po->porder_no) . '.pdf', array("Attachment" => 0));
    }

    /* ================= LQ-310 print design helpers (black and white, A4 portrait) ================= */

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