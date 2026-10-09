<?php
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGRNinfo extends CI_Model {

    // Image paths are relative to FCPATH
    private $logoPath    = 'images/logo.jpeg';
    private $faviconPath = 'images/favicon.jpeg';

    private $red  = '#c8102e';
    private $dark = '#2b2b2b';

    /* ------------------------------------------------------------------
     * Shared helpers  (GRN style: white header, info strip, boxed signatures)
     * ------------------------------------------------------------------ */
    private function e($v){
        return htmlspecialchars((string)$v);
    }

    private function imgData($relativePath){
        $full = FCPATH . ltrim($relativePath, '/');
        if (!is_file($full)) {
            return '';
        }
        $ext  = strtolower(pathinfo($full, PATHINFO_EXTENSION));
        $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($full));
    }

    private function addressLines($parts){
        $out = array();
        foreach ($parts as $p) {
            $p = trim((string)$p);
            if ($p !== '') { $out[] = $this->e($p); }
        }
        return $out;
    }

    private function css($wmTop){
        $r = $this->red;
        $d = $this->dark;
        return '
    @page { size: A4; margin: 118px 32px 125px 32px; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #222; margin: 0; }

    header { position: fixed; top: -118px; left: 0; right: 0; height: 100px; }
    footer { position: fixed; bottom: -125px; left: 0; right: 0; height: 115px; }

    .watermark-wrap { position: fixed; top: ' . $wmTop . 'px; left: 0; right: 0; text-align: center; }
    .watermark { width: 250px; opacity: 0.05; }

    .lbl { font-size: 8px; color: #888; text-transform: uppercase; letter-spacing: 1px; }
    .val { font-size: 12px; font-weight: bold; color: ' . $d . '; margin-top: 2px; }

    table.strip { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 4px; }
    table.strip td { background: #f5f5f5; border-top: 3px solid ' . $r . '; padding: 8px 10px; vertical-align: top; }

    .party { border-left: 3px solid ' . $r . '; padding-left: 10px; line-height: 1.55; }
    .party .name { font-size: 13px; font-weight: bold; color: ' . $d . '; }
    .tag { font-size: 9px; font-weight: bold; color: ' . $r . '; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 3px; }

    table.items { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 18px; }
    table.items th { background: #f4f4f4; color: ' . $d . '; font-size: 9px; text-transform: uppercase; letter-spacing: .5px;
                     padding: 8px 5px; border-top: 1px solid #ddd; border-bottom: 2px solid ' . $r . '; }
    table.items td { padding: 7px 5px; border-bottom: 1px solid #e5e5e5; font-size: 11px; vertical-align: top; }
    table.items td.recv { background: #fdeef0; font-weight: bold; text-align: center; }
    table.items thead { display: table-header-group; }
    table.items tr { page-break-inside: avoid; }

    table.totals { border-collapse: collapse; width: 100%; }
    table.totals td { padding: 7px 10px; font-size: 11px; border-bottom: 1px solid #e5e5e5; }
    table.totals tr.grand td { background: ' . $d . '; color: #fff; font-weight: bold; font-size: 13px; border-bottom: none; }

    .note { border: 1px dashed #bbb; padding: 8px 10px; line-height: 1.5; }
    .section-title { margin-top: 20px; font-size: 12px; font-weight: bold; color: ' . $d . ';
                     border-left: 4px solid ' . $r . '; padding-left: 8px; }

    table.sign { width: 100%; border-collapse: separate; border-spacing: 8px 0; table-layout: fixed; }
    table.sign td { border: 1px solid #bbb; height: 62px; vertical-align: top; padding: 5px 8px; }
    .pagenum:before { content: counter(page); }';
    }

    private function headerHtml($title, $badgeLabel, $badgeValue, $companyName){
        $logo = $this->imgData($this->logoPath);
        $left = ($logo !== '')
            ? '<img src="' . $logo . '" style="height:70px;">'
            : '<span style="font-size:18px;font-weight:bold;color:' . $this->red . ';">' . $this->e($companyName) . '</span>';

        return '
<header>
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="width:40%;vertical-align:middle;padding:0;">' . $left . '</td>
            <td style="width:60%;text-align:right;vertical-align:middle;padding:0;">
                <div style="font-size:24px;font-weight:bold;letter-spacing:1px;color:' . $this->dark . ';">' . $this->e($title) . '</div>
                <div style="margin-top:6px;">
                    <span style="background:' . $this->dark . ';color:#fff;font-size:11px;font-weight:bold;padding:4px 12px;letter-spacing:1px;">'
                    . $this->e($badgeLabel) . ' &nbsp;' . $this->e($badgeValue) . '</span>
                </div>
            </td>
        </tr>
    </table>
    <div style="height:3px;background:' . $this->red . ';margin-top:8px;"></div>
    <div style="height:1px;background:#ddd;margin-top:2px;"></div>
</header>';
    }

    private function watermarkHtml(){
        $fav = $this->imgData($this->faviconPath);
        if ($fav === '') {
            return '';
        }
        return '<div class="watermark-wrap"><img class="watermark" src="' . $fav . '"></div>';
    }

    private function signatureFooter($labels){
        $cells = '';
        foreach ($labels as $l) {
            $cells .= '<td><div class="lbl">' . $this->e($l) . '</div>
                <div style="margin-top:34px;font-size:8px;color:#aaa;">Name / Signature / Date</div></td>';
        }
        return '
<footer>
    <table class="sign"><tr>' . $cells . '</tr></table>
    <div style="margin-top:8px;font-size:9px;color:#777;text-align:center;">
        This is a computer-generated document &nbsp;&bull;&nbsp; Page <span class="pagenum"></span>
    </div>
</footer>';
    }

    // $cells = array(array('LABEL','value'), ...)
    private function infoStrip($cells){
        $w = floor(100 / count($cells));
        $html = '<table class="strip"><tr>';
        foreach ($cells as $c) {
            $html .= '<td style="width:' . $w . '%;"><div class="lbl">' . $this->e($c[0]) . '</div><div class="val">' . ($c[1] !== '' ? $this->e($c[1]) : '&nbsp;') . '</div></td>';
        }
        return $html . '</tr></table>';
    }

    private function partyBlock($tag, $name, $lines){
        $h = '<div class="party"><div class="tag">' . $this->e($tag) . '</div><div class="name">' . $name . '</div>';
        foreach ($lines as $l) {
            $h .= '<div>' . $l . '</div>';
        }
        return $h . '</div>';
    }

    /* ------------------------------------------------------------------
     * GOODS RECEIVED NOTE  (A4 landscape)
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

        $remarkFeild = $q->remark;
        $totalSum    = $q->grn_subtotal;
        $grn_total   = $q->grn_total;
        $grn_vat     = $q->vatamount;

        $this->db->select('tbl_company.company AS companyname,tbl_company.address1 As companyaddress,tbl_company.mobile AS companymobile, tbl_company.phone companyphone,tbl_company.email AS companyemail, tbl_company_branch.branch AS branchname');
        $this->db->from('tbl_grn');
        $this->db->join('tbl_company', 'tbl_company.idtbl_company = tbl_grn.tbl_company_idtbl_company', 'left');
        $this->db->join('tbl_company_branch', 'tbl_company_branch.idtbl_company_branch = tbl_grn.tbl_company_branch_idtbl_company_branch', 'left');
        $this->db->where('tbl_grn.idtbl_grn', $recordID);
        $companydetails = $this->db->get();
        $co = $companydetails->row();

        $grnNo = $q->grn_no;

        $supplierAddr = $this->addressLines(array(
            $q->delivery_address_line1,
            $q->delivery_address_line2,
            $q->delivery_city,
            $q->delivery_state
        ));
        $companyLines = array(
            '<span style="text-transform:uppercase;">' . $this->e($co->companyaddress) . '</span>',
            'Phone: ' . $this->e($co->companymobile) . ' / ' . $this->e($co->companyphone),
            'E-Mail: ' . $this->e($co->companyemail)
        );

        $html = '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Goods Received Note - ' . $this->e($grnNo) . '</title>
<style>' . $this->css(135) . '</style>
</head>
<body>';

        $html .= $this->watermarkHtml();
        $html .= $this->headerHtml('GOODS RECEIVED NOTE', 'GRN NO', $grnNo, $co->companyname);
        $html .= $this->signatureFooter(array('Received by', 'Approved by', 'Accountant'));

        // ---------- INFO STRIP ----------
        $html .= $this->infoStrip(array(
            array('GRN No',      $grnNo),
            array('GRN Date',    $q->grndate),
            array('Invoice No',  $q->invoicenum),
            array('PO No',       $q->porder_no),
            array('Batch No',    $q->batchno)
        ));

        // ---------- PARTIES ----------
        $html .= '
<table style="width:100%;border-collapse:collapse;table-layout:fixed;margin-top:16px;">
<tr>
    <td style="width:48%;vertical-align:top;padding:0;">'
        . $this->partyBlock('Received from', $this->e($q->suppliername), $supplierAddr) . '
    </td>
    <td style="width:4%;"></td>
    <td style="width:48%;vertical-align:top;padding:0;">'
        . $this->partyBlock('Received at', $this->e($co->companyname), $companyLines) . '
    </td>
</tr>
</table>';

        // ---------- ITEMS ----------
        $html .= '
<table class="items">
    <thead>
        <tr>
            <th rowspan="2" style="width:4%;">#</th>
            <th rowspan="2" style="width:13%;text-align:left;">Item Code</th>
            <th rowspan="2" style="width:31%;text-align:left;">Item Description</th>
            <th colspan="3">Quantity</th>
            <th rowspan="2" style="width:7%;">Unit</th>
            <th rowspan="2" style="width:11%;text-align:right;">Price</th>
            <th rowspan="2" style="width:12%;text-align:right;">Total</th>
        </tr>
        <tr>
            <th style="width:7%;">Ordered</th>
            <th style="width:7%;">Prev</th>
            <th style="width:8%;">Received</th>
        </tr>
    </thead>
    <tbody>';

        $i = 0;
        foreach ($query->result() as $rowlist) {
            $i++;

            if ($rowlist->idtbl_category == 4) {
                $itemDescription = $rowlist->comment;
            } else {
                $itemDescription = $rowlist->product_name;
                if (!empty($rowlist->product_code)) {
                    $itemDescription .= ' / ' . $rowlist->product_code;
                }
            }
            $price = !empty($rowlist->unitprice) ? $rowlist->unitprice : $rowlist->costunitprice;

            $html .= '<tr>
                <td style="text-align:center;color:#999;">' . $i . '</td>
                <td>' . $this->e($rowlist->product_code) . '</td>
                <td>' . $this->e($itemDescription) . '</td>
                <td style="text-align:center;">' . $this->e((float)$rowlist->ordered_qty) . '</td>
                <td style="text-align:center;">' . $this->e((float)$rowlist->prev_qty) . '</td>
                <td class="recv">' . $this->e((float)$rowlist->qty) . '</td>
                <td style="text-align:center;">' . $this->e($rowlist->unit) . '</td>
                <td style="text-align:right;">' . number_format((float)$price, 2) . '</td>
                <td style="text-align:right;font-weight:bold;">' . number_format((float)$rowlist->total, 2) . '</td>
            </tr>';
        }
        $html .= '</tbody></table>';

        // ---------- REMARK + TOTALS ----------
        $html .= '
<table style="width:100%;border-collapse:collapse;margin-top:14px;page-break-inside:avoid;">
<tr>
    <td style="width:58%;vertical-align:top;padding-right:24px;">'
        . (trim((string)$remarkFeild) !== ''
            ? '<div class="lbl" style="margin-bottom:4px;">Remarks</div><div class="note">' . nl2br($this->e($remarkFeild)) . '</div>'
            : '') . '
    </td>
    <td style="width:42%;vertical-align:top;">
        <table class="totals">
            <tr><td>Total (Excl)</td><td style="text-align:right;">' . number_format((float)$totalSum, 2) . '</td></tr>
            <tr><td>VAT</td><td style="text-align:right;">' . number_format((float)$grn_vat, 2) . '</td></tr>
            <tr class="grand"><td>Total (Incl)</td><td style="text-align:right;">' . number_format((float)$grn_total, 2) . '</td></tr>
        </table>
    </td>
</tr>
</table>';

        $html .= '</body></html>';

        $this->load->library('pdf');

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream("Goods Received Note - " . $recordID . ".pdf", array("Attachment" => 0));
    }

    /* ------------------------------------------------------------------
     * GOOD RECEIVE VOUCHER  (A4 portrait)
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

        $supplierAddr = $this->addressLines(array($v->address_line1, $v->address_line2, $v->city));
        $companyLines = array(
            '<span style="text-transform:uppercase;">' . $this->e($co->companyaddress) . '</span>',
            'Tel: ' . $this->e($co->companymobile) . ' / ' . $this->e($co->companyphone),
            'E-Mail: ' . $this->e($co->companyemail)
        );

        $html = '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Good Receive Voucher</title>
<style>' . $this->css(295) . '
    table.items th { font-size: 8px; padding: 8px 3px; }
    table.items td { font-size: 10px; padding: 6px 3px; }
</style>
</head>
<body>';

        $html .= $this->watermarkHtml();
        $html .= $this->headerHtml('GOOD RECEIVE VOUCHER', 'ORDER NO', $v->porder_no, $co->companyname);
        $html .= $this->signatureFooter(array('Received by', 'Date', 'Signed'));

        // ---------- INFO STRIP ----------
        $html .= $this->infoStrip(array(
            array('Account',          ''),
            array('Date',             $v->grndate),
            array('Order No',         $v->porder_no),
            array('Supplier Invoice', $v->invoicenum),
            array('Our Reference',    $v->invoiceno)
        ));

        // ---------- PARTIES ----------
        $html .= '
<table style="width:100%;border-collapse:collapse;table-layout:fixed;margin-top:16px;">
<tr>
    <td style="width:48%;vertical-align:top;padding:0;">'
        . $this->partyBlock('Supplier', $this->e($v->suppliername), $supplierAddr) . '
    </td>
    <td style="width:4%;"></td>
    <td style="width:48%;vertical-align:top;padding:0;">'
        . $this->partyBlock('Company', $this->e($co->companyname), $companyLines) . '
    </td>
</tr>
</table>';

        // ---------- GRN ITEMS ----------
        $html .= '
<table class="items">
    <thead>
        <tr>
            <th style="width:10%;text-align:left;">Item Code</th>
            <th style="width:23%;text-align:left;">Item Description</th>
            <th style="width:8%;">Ordered</th>
            <th style="width:6%;">Prev</th>
            <th style="width:8%;">Qty</th>
            <th style="width:6%;">Unit</th>
            <th style="width:11%;text-align:right;">Price (Ex)</th>
            <th style="width:7%;">Disc %</th>
            <th style="width:9%;text-align:right;">Tax</th>
            <th style="width:12%;text-align:right;">Total (Inc)</th>
        </tr>
    </thead>
    <tbody>';

        foreach ($respondgrn->result() as $g) {
            $html .= '<tr>
                <td>' . $this->e($g->product_code) . '</td>
                <td>' . $this->e($g->product_name) . '</td>
                <td style="text-align:center;">' . $this->e($g->qty) . '</td>
                <td style="text-align:center;">0.00</td>
                <td class="recv">' . $this->e($g->qty) . '</td>
                <td style="text-align:center;">' . $this->e($g->unit) . '</td>
                <td style="text-align:right;">' . number_format((float)$g->costunitprice, 2) . '</td>
                <td style="text-align:center;">' . $this->e($g->unit_discount) . '</td>
                <td style="text-align:right;">' . number_format((float)$g->costunitprice, 2) . '</td>
                <td style="text-align:right;font-weight:bold;">' . number_format((float)$g->total, 2) . '</td>
            </tr>';
        }
        $html .= '</tbody></table>';

        // ---------- IMPORTATION SPLIT ----------
        if ($respond2->num_rows() > 0) {
            $html .= '
<div class="section-title">Importation Split - Additional Cost Allocation</div>
<table class="items" style="margin-top:8px;">
    <thead>
        <tr>
            <th style="width:6%;">#</th>
            <th style="width:24%;text-align:left;">Cost Type</th>
            <th style="width:30%;text-align:left;">Description</th>
            <th style="width:14%;text-align:right;">Amount (Excl)</th>
            <th style="width:12%;text-align:right;">Tax Amount</th>
            <th style="width:14%;text-align:right;">Total</th>
        </tr>
    </thead>
    <tbody>';

            $totalcostamount = 0;
            $totalcostvatamount = 0;
            $j = 0;
            foreach ($respond2->result() as $rowlist) {
                $j++;
                $amount = (float)$rowlist->cost_amount;
                $totalcostamount += $amount;
                $desc = trim((string)$rowlist->comment) !== '' ? $rowlist->comment : $rowlist->cost_type;

                $html .= '<tr>
                    <td style="text-align:center;color:#999;">' . $j . '</td>
                    <td>' . $this->e($rowlist->cost_type) . '</td>
                    <td>' . $this->e($desc) . '</td>
                    <td style="text-align:right;">' . number_format($amount, 2) . '</td>
                    <td style="text-align:right;">0.00</td>
                    <td style="text-align:right;font-weight:bold;">' . number_format($amount, 2) . '</td>
                </tr>';
            }

            $html .= '</tbody>
    <tfoot>
        <tr>
            <td colspan="3" style="font-weight:bold;padding:7px 5px;background:#f5f5f5;">Importation Split Totals</td>
            <td style="text-align:right;font-weight:bold;background:#f5f5f5;">' . number_format($totalcostamount, 2) . '</td>
            <td style="text-align:right;font-weight:bold;background:#f5f5f5;">' . number_format($totalcostvatamount, 2) . '</td>
            <td style="background:#f5f5f5;">&nbsp;</td>
        </tr>
    </tfoot>
</table>';
        }

        // ---------- TOTALS ----------
        $html .= '
<table style="width:100%;border-collapse:collapse;margin-top:16px;page-break-inside:avoid;">
<tr>
    <td style="width:58%;"></td>
    <td style="width:42%;">
        <table class="totals">
            <tr><td>Total (Excl)</td><td style="text-align:right;">' . number_format((float)$v->grnsubtotal, 2) . '</td></tr>
            <tr><td>Tax</td><td style="text-align:right;">' . number_format((float)$v->vatamount, 2) . '</td></tr>
            <tr><td>Discount</td><td style="text-align:right;">' . number_format((float)$v->discount, 2) . '</td></tr>
            <tr class="grand"><td>Total (Incl)</td><td style="text-align:right;">' . number_format((float)$v->total, 2) . '</td></tr>
        </table>
    </td>
</tr>
</table>';

        $html .= '</body></html>';

        $this->load->library('pdf');
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->loadHtml($html);
        $this->pdf->render();
        $this->pdf->stream("Good Receive Voucher - " . $recordID . ".pdf", array("Attachment" => 0));
    }
}