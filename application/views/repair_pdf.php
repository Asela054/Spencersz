<?php
$fm  = function ($n) { return number_format((float)$n, 2); };
$e   = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$isW = ((int)$job->repair_type === 1);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 10mm 12mm; }
    * { color: #000; background: none; }
    body { margin: 0; font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; }
    table { border-collapse: collapse; width: 100%; }
    .mb { margin-bottom: 8px; }
    .box { border: 1.5px solid #000; padding: 5px 8px; vertical-align: top; }
    .gap { width: 2%; }
    .lbl { font-weight: bold; }
    .items td, .items th { border: 1.5px solid #000; padding: 4px 6px; }
    .items th { font-weight: bold; text-align: left; }
    .items tr { page-break-inside: avoid; }
    .num { text-align: right; }
    .ctr { text-align: center; }
</style>
</head>
<body>

    <!-- header -->
    <div style="text-align:center; border-bottom: 4px double #000; padding-bottom: 6px; margin-bottom: 8px;">
        <div style="font-size: 20pt; font-weight: bold;"><?= $e(strtoupper($company['name'])) ?></div>
        <div style="font-size: 11pt; font-weight: bold;"><?= $e($company['address']) ?></div>
        <div style="font-size: 10pt;">Tel: <?= $e($company['tel']) ?></div>
    </div>

    <!-- title -->
    <div style="text-align:center; border: 2px solid #000; padding: 5px 0; margin-bottom: 8px;">
        <div style="font-size: 16pt; font-weight: bold;">REPAIR JOB CARD</div>
        <div style="font-size: 12pt; font-weight: bold;"><?= $isW ? 'WARRANTY REPAIR' : 'OVER-WARRANTY REPAIR' ?></div>
    </div>

    <!-- job / customer -->
    <table class="mb">
        <tr>
            <td class="box" style="width:49%;">
                <span class="lbl">Job No :</span> <b style="font-size: 12pt;"><?= $e($job->job_no) ?></b><br>
                <span class="lbl">Received :</span> <?= $e(date('d-m-Y', strtotime($job->job_date))) ?><br>
                <span class="lbl">Promised :</span> <?= $job->promised_date ? $e(date('d-m-Y', strtotime($job->promised_date))) : '-' ?>
            </td>
            <td class="gap"></td>
            <td class="box" style="width:49%;">
                <span class="lbl">Customer :</span> <?= $e($job->customer_name) ?><br>
                <span class="lbl">Phone :</span> <?= $e($job->customer_phone) ?>
            </td>
        </tr>
    </table>

    <!-- product / warranty -->
    <table class="mb">
        <tr>
            <td class="box">
                <span class="lbl">Product :</span> <?= $e($job->product_name) ?><br>
                <span class="lbl">Serial / IMEI :</span> <b><?= $e($job->serialno ?: '-') ?></b><br>
                <span class="lbl">Sale invoice :</span> <?= $e($job->sale_invoice_no ?: '-') ?><br>
                <span class="lbl">Warranty :</span>
                <?php if ($job->warranty_status == 1): ?>IN WARRANTY until <?= $e(date('d-m-Y', strtotime($job->warranty_end))) ?>
                <?php elseif ($job->warranty_status == 2): ?>WARRANTY EXPIRED on <?= $e(date('d-m-Y', strtotime($job->warranty_end))) ?>
                <?php else: ?>No sale record<?php endif; ?>
                <?php if ($job->not_covered_reason): ?><br><span class="lbl">Not covered :</span> <?= $e($job->not_covered_reason) ?><?php endif; ?>
            </td>
        </tr>
    </table>

    <!-- fault -->
    <table class="mb">
        <tr>
            <td class="box" style="height: 60px;">
                <span class="lbl">Fault reported :</span> <?= $e($job->fault) ?><br>
                <span class="lbl">Accessories received :</span> <?= $e($job->accessories ?: '-') ?><br>
                <span class="lbl">Condition of the item :</span> <?= $e($job->condition_note ?: '-') ?>
            </td>
        </tr>
    </table>

    <!-- charges -->
    <?php if (!$isW && (float)$job->total_charge > 0): ?>
    <table class="items mb">
        <tr>
            <th style="width:52%;">Part / Service</th>
            <th style="width:10%;" class="ctr">Qty</th>
            <th style="width:18%;" class="num">Unit Price</th>
            <th style="width:20%;" class="num">Amount</th>
        </tr>
        <?php foreach ($parts as $p): ?>
        <tr>
            <td><?= $e($p->description) ?></td>
            <td class="ctr"><?= (int)$p->qty ?></td>
            <td class="num"><?= $fm($p->unitprice) ?></td>
            <td class="num"><?= $fm($p->total) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if ((float)$job->labour_charge > 0): ?>
        <tr><td colspan="3">Labour / service charge</td><td class="num"><?= $fm($job->labour_charge) ?></td></tr>
        <?php endif; ?>
        <tr><td colspan="3">VAT included in the total</td><td class="num"><?= $fm($job->vatamount) ?></td></tr>
        <tr>
            <td colspan="3" style="font-weight:bold; font-size: 12pt;">TOTAL (including VAT)</td>
            <td class="num" style="font-weight:bold; font-size: 12pt;"><?= $fm($job->total_charge) ?></td>
        </tr>
    </table>
    <?php elseif ($isW): ?>
    <table class="mb">
        <tr><td class="box ctr" style="font-weight:bold; font-size: 11pt;">WARRANTY REPAIR - NO CHARGE TO THE CUSTOMER</td></tr>
    </table>
    <?php endif; ?>

    <!-- terms (CHANGE: write your own terms) -->
    <table class="mb">
        <tr>
            <td class="box" style="font-size: 8.5pt;">
                <b>Terms:</b> 1. Please bring this card to collect the item.
                2. We are not responsible for data on the device; please back it up before repair.
                3. Items not collected within 30 days after we inform you may be disposed of.
                4. Warranty does not cover physical or liquid damage.
            </td>
        </tr>
    </table>

    <!-- signatures -->
    <table>
        <tr>
            <td class="box" style="width:49%; height: 70px;">
                <div class="lbl">Customer signature</div>
                <div style="margin-top: 36px; border-top: 1.5px solid #000; text-align:center; font-size: 9pt; padding-top: 2px;">Signature</div>
            </td>
            <td class="gap"></td>
            <td class="box" style="width:49%; height: 70px;">
                <div class="lbl">Received by</div>
                <div style="margin-top: 36px; border-top: 1.5px solid #000; text-align:center; font-size: 9pt; padding-top: 2px;">Signature</div>
            </td>
        </tr>
    </table>

</body>
</html>