<?php
class PorderreqPrintinfo extends CI_Model {

    private $rowsPerPage = 15;   // item rows per A4 page (blank rows pad the table)

    public function Printinvoice($x){
        $recordID = (int)$x;

        /* ---------- header (request + company + branch + checker) ---------- */
        $this->db->select('r.idtbl_porder_req, r.porder_req_no, r.date, r.confirmstatus, r.tbl_company_idtbl_company,
                           c.company AS companyname, c.address1 AS companyaddress, c.mobile AS companymobile,
                           c.phone AS companyphone, c.email AS companyemail,
                           b.branch AS branchname,
                           cu.name AS checkedbyname');
        $this->db->from('tbl_porder_req AS r');
        $this->db->join('tbl_company AS c', 'c.idtbl_company = r.tbl_company_idtbl_company', 'left');
        $this->db->join('tbl_company_branch AS b', 'b.idtbl_company_branch = r.tbl_company_branch_idtbl_company_branch', 'left');
        $this->db->join('tbl_user AS cu', 'cu.idtbl_user = r.check_by', 'left');
        $this->db->where('r.idtbl_porder_req', $recordID);
        $this->db->where_in('r.status', array(1, 2));
        $headerQuery = $this->db->get();

        if ($headerQuery->num_rows() == 0) {
            show_404();
            return;
        }
        $header = $headerQuery->row();

        /* ---------- document number prefix by company ---------- */
        $prefix = 'SP';
        if ($header->tbl_company_idtbl_company == 2) {
            $prefix = 'FT';
        } elseif ($header->tbl_company_idtbl_company == 3) {
            $prefix = 'RM';
        }
        $porNo = $prefix . '/' . $header->porder_req_no;

        /* ---------- detail lines ---------- */
        $this->db->select('d.qty, d.comment, p.product_code, p.product_name, p.model_no, u.unit, u.unit_short');
        $this->db->from('tbl_porder_req_detail AS d');
        $this->db->join('tbl_product AS p', 'p.idtbl_product = d.tbl_product_idtbl_product', 'left');
        $this->db->join('tbl_unit AS u', 'u.idtbl_unit = p.tbl_unit_idtbl_unit', 'left');
        $this->db->where('d.tbl_porder_req_idtbl_porder_req', $recordID);
        $this->db->where('d.status', 1);
        $this->db->order_by('d.idtbl_porder_req_detail', 'ASC');
        $detailQuery = $this->db->get();

        /* ---------- split lines into pages ---------- */
        $pages = array_chunk($detailQuery->result(), $this->rowsPerPage);
        if (empty($pages)) {
            $pages = array(array()); // still print an empty sheet
        }
        $totalPages = count($pages);

        $contact = 'Tel: ' . trim($header->companymobile . ' / ' . $header->companyphone, ' /');
        $body = '';

        foreach ($pages as $index => $lines) {
            $isLast = ($index == $totalPages - 1);

            $p  = $this->lq_head('PURCHASE ORDER REQUEST', $header->companyname, $header->companyaddress, array($contact));
            $p .= $this->lq_boxes(array(
                $this->lq_kv(array(array('POR No', $porNo))),
                $this->lq_kv(array(array('Date', $header->date)))
            ));
            $p .= $this->lq_sp(6);
            $p .= $this->lq_boxes(array(
                $this->lq_kv(array(array('Branch', $header->branchname))),
                $this->lq_kv(array(array('Checked by', $header->checkedbyname)))
            ));
            $p .= $this->lq_sp(8);

            $p .= '<table class="items">
                <thead><tr>
                    <th style="width:5%;">#</th>
                    <th style="width:15%;">Item Code</th>
                    <th style="width:38%;text-align:left;">Description of Goods</th>
                    <th style="width:9%;">Qty</th>
                    <th style="width:9%;">Unit</th>
                    <th style="width:24%;">Comment</th>
                </tr></thead><tbody>';

            $n = $index * $this->rowsPerPage;
            foreach ($lines as $row) {
                $n++;
                $productText = $row->product_name;
                if (!empty($row->model_no)) {
                    $productText .= ' (' . $row->model_no . ')';
                }
                $unit = $row->unit_short ? $row->unit_short : $row->unit;

                $p .= '<tr>
                    <td style="text-align:center;">' . $n . '</td>
                    <td style="text-align:center;">' . $this->lq_e($row->product_code) . '</td>
                    <td>' . $this->lq_e($productText) . '</td>
                    <td style="text-align:center;">' . $this->lq_e((float)$row->qty) . '</td>
                    <td style="text-align:center;">' . $this->lq_e($unit) . '</td>
                    <td>' . $this->lq_e($row->comment) . '</td>
                </tr>';
            }
            $p .= $this->lq_pad_rows(count($lines), $this->rowsPerPage, 6);
            $p .= '</tbody></table>';
            $p .= $this->lq_sp(10);

            if ($isLast) {
                $p .= $this->lq_sign(array(
                    array('Prepared by', '', 'Signature'),
                    array('Checked by', $header->checkedbyname, 'Signature'),
                    array('Authorise Officer', '', 'Signature')
                ));
            } else {
                $p .= '<div class="cont">Continued on next page...</div>';
            }

            $body .= $this->lq_page($p, $isLast, $index + 1, $totalPages);
        }

        $html = $this->lq_doc('Purchase Order Request', $body);

        $this->load->library('pdf');
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->loadHtml($html);
        $this->pdf->render();

        $filename = $this->lq_filename(strtoupper($header->companyname) . ' - PURCHASE ORDER REQUEST - ' . $header->porder_req_no) . '.pdf';
        $this->pdf->stream($filename, array("Attachment" => 0));
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