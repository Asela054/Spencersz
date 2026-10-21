<?php
$fm = function ($n) { return number_format((float)$n, 2); };
$e  = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    /* LQ-310 (dot matrix, A4): black only, no images, no fills.
       Increase margin-top if you print on pre-printed letterhead stationery. */
    @page {
        margin-top: 30px;
        margin-bottom: 50px;
        margin-left: 30px;
        margin-right: 30px;
    }
    body { margin: 0; font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #000; background: #fff; }

    /* Repeating text footer on every page */
    .page-footer {
        position: fixed;
        bottom: -38px;
        left: 0;
        right: 0;
        height: 20px;
        text-align: center;
        font-size: 9px;
        border-top: 1px solid #000;
        padding-top: 3px;
    }

    table { border-collapse: collapse; }
    .mb { margin-bottom: 6px; }
    .box { border: 1px solid #000; padding: 5px 8px; vertical-align: top; }
    .gap { width: 2%; }
    .items td, .items th { border: 1px solid #000; padding: 3px 5px; }
    .items th { font-size: 10px; font-weight: bold; }
    .items tr { page-break-inside: avoid; }
    .num { text-align: right; }
    .ctr { text-align: center; }
</style>
</head>
<body>

<!-- Repeating Footer (every page) -->
<div class="page-footer">&copy; Bartack by Erav Technology - 071 657 8718 / 070 330 0061 - 65</div>

<div class="wrap">

    <!-- Company (text only, no logo) -->
    <table width="100%" class="mb">
        <tr>
            <td style="text-align:center;">
                <div style="font-size:20px; font-weight:bold;"><?= $e(strtoupper($company['name'])) ?></div>
                <div style="font-size:11px; font-weight:bold;"><?= $e($company['address']) ?></div>
                <div>TIN: <?= $e($company['tin']) ?> &nbsp;|&nbsp; Tel: <?= $e($company['tel']) ?></div>
            </td>
        </tr>
    </table>

    <!-- Title -->
    <div style="font-size:18px; font-weight:bold; padding:3px 0; text-align:center; letter-spacing:2px; border-top:2px solid #000; border-bottom:2px solid #000;" class="mb">CREDIT NOTE</div>

    <table width="100%" class="mb">
        <tr>
            <td class="box" style="width:49%;">
                <b>Credit Note No:</b> <?= $e($cn->creditnote_no) ?><br>
                <b>Date:</b> <?= $e(date('d-m-Y', strtotime($cn->creditnote_date))) ?>
            </td>
            <td class="gap"></td>
            <td class="box" style="width:49%;">
                <b>Original Tax Invoice No:</b> <?= $e($cn->invoice_no) ?><br>
                <b>Invoice Date:</b> <?= $e(date('d-m-Y', strtotime($cn->invoice_date))) ?>
            </td>
        </tr>
    </table>

    <table width="100%" class="mb">
        <tr>
            <td class="box">
                <b>Customer:</b> <?= $e($cn->customer_name) ?> &nbsp;&nbsp; <b>NIC / TIN:</b> <?= $e($cn->customer_nic) ?>
            </td>
        </tr>
    </table>

    <table width="100%" class="items mb">
        <tr>
            <th style="width:12%;">Item Code</th>
            <th style="width:38%;">Description of Goods Returned</th>
            <th style="width:8%;">Qty</th>
            <th style="width:18%;">Unit Price (excl. VAT)</th>
            <th style="width:24%;">Amount excl. VAT (Rs.)</th>
        </tr>
        <?php foreach ($items as $it): ?>
        <tr>
            <td class="ctr"><?= $e($it->product_code) ?></td>
            <td>
                <?= $e($it->product_name) ?>
                <?php if ($it->serialno): ?><br><span style="font-size:9px;">S/N: <?= $e($it->serialno) ?></span><?php endif; ?>
                <?php if ($it->remark): ?><br><span style="font-size:9px;">Reason: <?= $e($it->remark) ?></span><?php endif; ?>
            </td>
            <td class="ctr"><?= (int)$it->qty ?></td>
            <td class="num"><?= $fm($it->unitprice / $rate) ?></td>
            <td class="num"><?= $fm($it->total / $rate) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr><td colspan="4">Total Value (excluding VAT)</td><td class="num"><?= $fm($cn->subtotal) ?></td></tr>
        <tr><td colspan="4">VAT Amount @ <?= (float)$cn->vat_rate ?>%</td><td class="num"><?= $fm($cn->vatamount) ?></td></tr>
        <tr><td colspan="4" style="font-weight:bold; font-size:11px;">Total Credit including VAT</td>
            <td class="num" style="font-weight:bold; font-size:11px;"><?= $fm($cn->totalamount) ?></td></tr>
    </table>

    <table width="100%">
        <tr>
            <td class="box" style="width:49%; height:70px;">
                <div style="font-weight:bold;">Customer Acknowledgment</div>
                <div style="margin-top:36px; border-top:1px solid #000; text-align:center; font-size:9px; padding-top:2px;">Signature</div>
            </td>
            <td class="gap"></td>
            <td class="box" style="width:49%; height:70px;">
                <div style="font-weight:bold;">Authorized Signature</div>
                <div style="margin-top:36px; border-top:1px solid #000; text-align:center; font-size:9px; padding-top:2px;">Signature</div>
            </td>
        </tr>
    </table>
</div>
</body>
</html>