<?php
$fm = function ($n) { return number_format((float)$n, 2); };
$e  = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$kv = function ($label, $value, $h = 0) use ($e) {
    return '<tr><td class="lbl">' . $label . '</td><td class="sep">:</td>'
         . '<td class="val"' . ($h ? ' style="height:' . $h . 'px"' : '') . '>' . $e($value) . '</td></tr>';
};
$discountEx = round($inv->discount / $rate, 2);
$grossEx    = round($supply + $discountEx, 2);
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
    body {
        margin: 0;
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 10px;
        color: #000;
        background: #fff;
    }

    table { border-collapse: collapse; }
    .mb { margin-bottom: 6px; }
    .box { border: 1px solid #000; padding: 4px 8px; vertical-align: top; }
    .gap { width: 2%; }
    .lbl { font-weight: bold; width: 34%; padding: 2px 0; }
    .sep { width: 4%; font-weight: bold; }
    .val { padding: 2px 0; }
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

        <!-- Title -->
        <table width="100%" class="mb">
            <tr>
                <td style="text-align:center;">
                    <div style="font-size:18px; font-weight:bold; padding:3px 0; border-top:2px solid #000; border-bottom:2px solid #000; letter-spacing:2px;"><?= $title ?></div>
                </td>
            </tr>
        </table>

        <!-- Company (text only, replaces header image) -->
        <table width="100%" class="mb">
            <tr>
                <td style="text-align:center;">
                    <div style="font-size:13px; font-weight:bold;"><?= $e($company['name']) ?></div>
                    <div><?= $e($company['address']) ?></div>
                    <div>Tel: <?= $e($company['tel']) ?></div>
                </td>
            </tr>
        </table>

        <!-- Date / Invoice no -->
        <table width="100%" class="mb">
            <tr>
                <td class="box" style="width:49%;"><table width="100%"><?= $kv('Date of invoice', date('d-m-Y', strtotime($inv->invoice_date))) ?></table></td>
                <td class="gap"></td>
                <td class="box" style="width:49%;"><table width="100%"><?= $kv('Invoice No.', $inv->invoice_no) ?></table></td>
            </tr>
        </table>

        <!-- Supplier / Purchaser -->
        <table width="100%" class="mb">
            <tr>
                <td class="box" style="width:49%;">
                    <table width="100%">
                        <?= $kv("Supplier's TIN", $company['tin']) ?>
                        <?= $kv("Supplier's Name", $company['name']) ?>
                        <?= $kv('Address', $company['address'], 34) ?>
                        <?= $kv('Telephone No.', $company['tel']) ?>
                    </table>
                </td>
                <td class="gap"></td>
                <td class="box" style="width:49%;">
                    <table width="100%">
                        <?= $kv("Purchaser's TIN", $customer ? $customer->vat_no : '') ?>
                        <?= $kv("Purchaser's Name", $inv->customer_name) ?>
                        <?= $kv('Address', $customer ? $customer->address_line1.' '.$customer->address_line2.' '.$customer->city : '', 34) ?>
                        <?= $kv('Telephone No.', $customer ? $customer->telephone_no : '') ?>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Supply -->
        <table width="100%" class="mb">
            <tr>
                <td class="box" style="width:49%;"><table width="100%"><?= $kv('Date of Supply', date('d-m-Y', strtotime($inv->invoice_date))) ?></table></td>
                <td class="gap"></td>
                <td class="box" style="width:49%;"><table width="100%"><?= $kv('Place of Supply', $company['place']) ?></table></td>
            </tr>
        </table>

        <table width="100%" class="mb">
            <tr><td class="box"><table width="100%"><?= $kv('Reference', $inv->remark) ?></table></td></tr>
        </table>

        <!-- Items -->
        <table width="100%" class="items mb">
            <tr>
                <th style="width:11%;">Item<br>Code</th>
                <th style="width:41%;">Description of Goods</th>
                <th style="width:10%;">Quantity</th>
                <th style="width:17%;">Unit Price</th>
                <th style="width:21%;">Amount<br>Excluding VAT<br>(Rs.)</th>
            </tr>
            <?php $n = 0; foreach ($items as $it): $n++; ?>
            <tr>
                <td class="ctr"><?= $e($it->product_code) ?></td>
                <td>
                    <?= $e($it->product_name) ?>
                    <?php if ($it->serialno): ?><br><span style="font-size:9px;">S/N: <?= $e($it->serialno) ?></span><?php endif; ?>
                    <?php if ($it->warranty_months): ?><span style="font-size:9px;"> | Warranty: <?= (int)$it->warranty_months ?> months</span><?php endif; ?>
                </td>
                <td class="ctr"><?= (int)$it->qty ?></td>
                <td class="num"><?= $fm($it->unitprice / $rate) ?></td>
                <td class="num"><?= $fm($it->line_total / $rate) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php for (; $n < 7; $n++): ?>
            <tr><td style="height:15px;">&nbsp;</td><td></td><td></td><td></td><td></td></tr>
            <?php endfor; ?>

            <?php if ($discountEx > 0): ?>
            <tr><td colspan="4">Sub Total (excluding VAT)</td><td class="num"><?= $fm($grossEx) ?></td></tr>
            <tr><td colspan="4">Less: Discount (excluding VAT)</td><td class="num"><?= $fm($discountEx) ?></td></tr>
            <?php endif; ?>
            <tr><td colspan="4">Total Value of Supply</td><td class="num"><?= $fm($supply) ?></td></tr>
            <tr><td colspan="4">VAT Amount (Total Value of Supply @ <?= (float)$inv->vat_rate ?>%)</td><td class="num"><?= $fm($inv->vatamount) ?></td></tr>
            <tr><td colspan="4" style="font-weight:bold; font-size:11px;">Total Amount / consideration including VAT</td>
                <td class="num" style="font-weight:bold; font-size:11px;"><?= $fm($inv->net_total) ?></td></tr>
        </table>

        <!-- Words / payment -->
        <table width="100%" class="items mb">
            <tr><td style="width:22%; font-weight:bold;">Total Amount in words</td><td style="width:2%; font-weight:bold;">:</td><td><?= $e($words) ?></td></tr>
            <?php if ($title === 'QUOTATION'): ?>
            <tr><td style="font-weight:bold;">Validity</td><td style="font-weight:bold;">:</td>
                <td>This quotation is valid for 14 days (until <?= date('d-m-Y', strtotime($inv->invoice_date . ' +14 days')) ?>)</td></tr>
            <?php else: ?>
            <tr><td style="font-weight:bold;">Mode of Payment</td><td style="font-weight:bold;">:</td><td><?= $e($modes) ?></td></tr>
            <?php endif; ?>
        </table>

        <!-- Signatures -->
        <table width="100%">
            <tr>
                <td class="box" style="width:49%; height:75px;">
                    <div style="font-weight:bold;">Customer Acknowledgment</div>
                    <div style="margin-top:38px; border-top:1px solid #000; text-align:center; font-size:9px; padding-top:2px;">Signature</div>
                </td>
                <td class="gap"></td>
                <td class="box" style="width:49%; height:75px;">
                    <div style="font-weight:bold;">Security Check Signature</div>
                    <div style="margin-top:38px; border-top:1px solid #000; text-align:center; font-size:9px; padding-top:2px;">Signature</div>
                </td>
            </tr>
        </table>

    </div>
</body>
</html>