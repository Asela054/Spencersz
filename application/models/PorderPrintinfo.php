<?php
class PorderPrintinfo extends CI_Model{

    private $logoPath    = 'images/logo.jpeg';
    private $faviconPath = 'images/favicon.jpeg';

    private function imgData($relativePath){
        $full = FCPATH . ltrim($relativePath, '/');
        if (!is_file($full)) {
            return '';
        }
        $ext  = strtolower(pathinfo($full, PATHINFO_EXTENSION));
        $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($full));
    }

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

        $dots = '...................................';
        $preparedByName   = !empty($co->preparedByName)   ? htmlspecialchars($co->preparedByName)   : $dots;
        $authorizedByName = !empty($co->authorizedByName) ? htmlspecialchars($co->authorizedByName) : $dots;
        $contactNo = $dots;

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

        $remarkFeild = trim((string)$po->remark);
        $supplierId  = (int)$po->tbl_supplier_idtbl_supplier;

        // Supplier address lines
        $addr = array();
        foreach (array($po->address_line1, $po->address_line2, $po->city) as $part) {
            $part = trim((string)$part);
            if ($part !== '') { $addr[] = htmlspecialchars($part); }
        }
        $tp = (strlen((string)$po->telephone_no) >= 9) ? htmlspecialchars($po->telephone_no) : '';

        $logo    = $this->imgData($this->logoPath);
        $favicon = $this->imgData($this->faviconPath);

        $red     = '#c8102e';
        $darkred = '#8a0c20';

        $html = '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Purchase Order - ' . htmlspecialchars((string)$po->porder_no) . '</title>
<style>
    @page { size: A4 portrait; margin: 130px 32px 120px 32px; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #222; margin: 0; }

    header { position: fixed; top: -130px; left: -32px; right: -32px; height: 100px; }
    footer { position: fixed; bottom: -120px; left: 0; right: 0; height: 110px; }

    .watermark-wrap { position: fixed; top: 290px; left: 0; right: 0; text-align: center; }
    .watermark { width: 250px; opacity: 0.06; }

    .box-title { background: ' . $red . '; color: #fff; font-weight: bold; font-size: 10px;
                 letter-spacing: 1px; padding: 5px 10px; text-transform: uppercase; }
    .box-body  { border: 1px solid #e3c4ca; border-top: none; padding: 8px 10px; line-height: 1.55; }

    table.items { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 16px; }
    table.items th { background: ' . $darkred . '; color: #fff; font-size: 10px; text-transform: uppercase;
                     letter-spacing: .5px; padding: 8px 6px; border: 1px solid ' . $darkred . '; }
    table.items td { padding: 7px 6px; border-bottom: 1px solid #ead5d9; font-size: 11px; vertical-align: top; }
    table.items tr.alt td { background: #fbf1f3; }
    table.items thead { display: table-header-group; }
    table.items tr { page-break-inside: avoid; }

    table.totals { border-collapse: collapse; width: 100%; }
    table.totals td { padding: 7px 10px; font-size: 11px; }
    .grand td { background: ' . $red . '; color: #fff; font-weight: bold; font-size: 13px; }

    .pagenum:before { content: counter(page); }
    .sig-line { border-top: 1px solid #555; padding-top: 4px; font-size: 10px; text-align: center; color: #444; }
</style>
</head>
<body>';

        if ($favicon !== '') {
            $html .= '<div class="watermark-wrap"><img class="watermark" src="' . $favicon . '"></div>';
        }

        // ---------- HEADER (repeats on every page) ----------
        $html .= '
<header>
    <table style="width:100%;border-collapse:collapse;background:' . $red . ';">
        <tr>
            <td style="width:55%;padding:0;">'
            . ($logo !== '' ? '<img src="' . $logo . '" style="height:100px;">' : '<span style="color:#fff;font-size:20px;font-weight:bold;padding:20px;">' . htmlspecialchars((string)$co->companyname) . '</span>') .
            '</td>
            <td style="width:45%;text-align:right;padding:0 38px 0 0;color:#fff;vertical-align:middle;">
                <div style="font-size:24px;font-weight:bold;letter-spacing:2px;">PURCHASE ORDER</div>
                <div style="font-size:12px;margin-top:4px;">No: ' . htmlspecialchars((string)$po->porder_no) . '</div>
            </td>
        </tr>
    </table>
    <div style="height:5px;background:' . $darkred . ';"></div>
</header>';

        // ---------- FOOTER (repeats on every page) ----------
        $html .= '<footer>';
        if ($supplierId != 65 && $remarkFeild !== '') {
            $html .= '<div style="font-size:10px;font-weight:bold;margin-bottom:6px;color:' . $darkred . ';">Remark: ' . htmlspecialchars($remarkFeild) . '</div>';
        }
        $html .= '
    <table style="width:100%;border-collapse:collapse;table-layout:fixed;">
        <tr>
            <td style="width:30%;padding:0 10px;"><div style="height:22px;text-align:center;font-size:10px;">' . $preparedByName . '</div><div class="sig-line">Prepared by</div></td>
            <td style="width:30%;padding:0 10px;"><div style="height:22px;text-align:center;font-size:10px;">' . $authorizedByName . '</div><div class="sig-line">Authorized by</div></td>
            <td style="width:30%;padding:0 10px;"><div style="height:22px;text-align:center;font-size:10px;">' . $contactNo . '</div><div class="sig-line">Contact No</div></td>
        </tr>
    </table>
    <div style="margin-top:8px;border-top:2px solid ' . $red . ';padding-top:5px;font-size:9px;color:#666;text-align:center;">
        This is a computer-generated document. No signature is required. &nbsp;|&nbsp; Page <span class="pagenum"></span>
    </div>
</footer>';

        // ---------- INFO BOXES ----------
        $html .= '
<table style="width:100%;border-collapse:collapse;table-layout:fixed;">
<tr>
    <td style="width:49%;vertical-align:top;padding:0;">
        <div class="box-title">Supplier</div>
        <div class="box-body">
            <div style="font-size:13px;font-weight:bold;">' . htmlspecialchars((string)$po->suppliername) . '</div>';
        if ($supplierId == 65 && $remarkFeild !== '') {
            $html .= '<div style="font-weight:bold;">' . htmlspecialchars($remarkFeild) . '</div>';
        }
        foreach ($addr as $line) {
            $html .= '<div>' . $line . '</div>';
        }
        if ($tp !== '') {
            $html .= '<div>Tel: ' . $tp . '</div>';
        }
        $html .= '<div style="margin-top:6px;">Attn: ..............................................</div>
        </div>
    </td>
    <td style="width:2%;"></td>
    <td style="width:49%;vertical-align:top;padding:0;">
        <div class="box-title">Order Details</div>
        <div class="box-body">
            <div style="font-size:12px;font-weight:bold;text-transform:uppercase;">' . htmlspecialchars((string)$co->companyname) . '</div>
            <div style="text-transform:uppercase;">' . htmlspecialchars((string)$co->companyaddress) . '</div>
            <div>Phone: ' . htmlspecialchars((string)$co->companymobile) . ' / ' . htmlspecialchars((string)$co->companyphone) . '</div>
            <div>E-Mail: ' . htmlspecialchars((string)$co->companyemail) . '</div>
            <div style="margin-top:4px;"><b>PO No:</b> ' . htmlspecialchars((string)$po->porder_no) . '</div>
            <div><b>Date:</b> ' . htmlspecialchars((string)$po->orderdate) . '</div>'
            . ($company_id == 1 ? '<div><b>Our VAT No:</b> 103305667-7000</div>' : '') . '
        </div>
    </td>
</tr>
</table>';

        // ---------- ITEMS TABLE ----------
        $html .= '
<table class="items">
    <thead>
        <tr>
            <th style="width:6%;">#</th>
            <th style="width:13%;">Code</th>
            <th style="width:33%;text-align:left;">Item Description</th>
            <th style="width:9%;">Qty</th>
            <th style="width:9%;">UOM</th>
            <th style="width:15%;text-align:right;">Unit Price</th>
            <th style="width:15%;text-align:right;">Total</th>
        </tr>
    </thead>
    <tbody>';

        $i = 0;
        foreach ($respond2->result() as $rowlist) {
            $i++;
            $desc = (string)$rowlist->product_name;
            if (trim((string)$rowlist->comment) !== '') {
                $desc .= ' - ' . $rowlist->comment;
            }
            $html .= '<tr class="' . ($i % 2 == 0 ? 'alt' : '') . '">
                <td style="text-align:center;color:#888;">' . $i . '</td>
                <td style="text-align:center;">' . htmlspecialchars((string)$rowlist->product_code) . '</td>
                <td>' . htmlspecialchars($desc) . '</td>
                <td style="text-align:center;">' . htmlspecialchars((string)$rowlist->qty) . '</td>
                <td style="text-align:center;">' . htmlspecialchars((string)$rowlist->measure_type) . '</td>
                <td style="text-align:right;">' . number_format((float)$rowlist->unitprice, 2) . '</td>
                <td style="text-align:right;font-weight:bold;">' . number_format((float)$rowlist->netprice, 2) . '</td>
            </tr>';
        }

        $html .= '</tbody></table>';

        // ---------- TOTALS (once, after last item) ----------
        $html .= '
<table style="width:100%;border-collapse:collapse;margin-top:14px;page-break-inside:avoid;">
<tr>
    <td style="width:58%;"></td>
    <td style="width:42%;">
        <table class="totals">
            <tr><td style="border-bottom:1px solid #ead5d9;">Total (Excl)</td><td style="text-align:right;border-bottom:1px solid #ead5d9;">' . number_format($net, 2) . '</td></tr>
            <tr><td style="border-bottom:1px solid #ead5d9;">Tax</td><td style="text-align:right;border-bottom:1px solid #ead5d9;">&nbsp;</td></tr>
            <tr class="grand"><td>Total (Incl)</td><td style="text-align:right;">' . number_format($net, 2) . '</td></tr>
        </table>
    </td>
</tr>
</table>';

        $html .= '</body></html>';

        $this->load->library('pdf');
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->loadHtml($html);
        $this->pdf->render();
        $this->pdf->stream("MULTI OFFSET PRINTERS-PURCHASE ORDER- " . $recordID . ".pdf", array("Attachment" => 0));
    }

}