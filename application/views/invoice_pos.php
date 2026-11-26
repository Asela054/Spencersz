<?php
/* POS bill for the Xprinter XP-420B (label printer used as a receipt printer).
   $paperW = roll width in mm (80, or 100 for a 100 mm roll; the printer's maximum is 108 mm). */
$pageMode = 'auto';        // 'auto' = use the paper size chosen in the printer driver (recommended for this printer)
                        // 'fixed' = ask for an exact page size from CSS (some drivers ignore it)
$paperW = 80;
$side   = 3;                       // left / right margin in mm
$billW  = $paperW - 2 * $side;     // printable width of the bill

$isTax = ($title === 'TAX INVOICE');
$pr    = $isTax ? $rate : 1;       // tax invoice: prices without VAT (same rule as the A4 invoice)

$fm = function ($n) { return number_format((float)$n, 2); };
$e  = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

$discountEx  = round($inv->discount / $pr, 2);
$supplyShown = $isTax ? $supply : round($inv->net_total, 2);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>POS Bill <?= $e($inv->invoice_no) ?></title>
<style>
    @page { margin: 0; }                         /* the page size is set by the script below (exact bill height) */
    html, body { margin: 0; padding: 0; background: #fff; height: auto; margin-top: 10px; }
    body { width: <?= $paperW ?>mm; font-family: Arial, Helvetica, sans-serif; font-size: 11px; line-height: 1.3; color: #000; }
    #bill { width: <?= $billW ?>mm; margin: 0 0 0 <?= $side ?>mm; padding: 1mm 0 0 0; }   /* starts at the top-left corner */
    .c { text-align: center; } .r { text-align: right; } .b { font-weight: bold; }
    .row { display: flex; justify-content: space-between; gap: 4px; }
    .sep { border-top: 1px dashed #000; margin: 3px 0; }
    .big { font-size: 14px; }
    .small { font-size: 9px; }
    .noprint { margin: 6px; }
    @media print { .noprint { display: none; } }
</style>
</head>
<body>
<button class="noprint" onclick="window.print()">Print</button>

<div id="bill">
    <div class="c b big"><?= $e($company['name']) ?></div>
    <div class="c"><?= $e($company['address']) ?></div>
    <div class="c">Tel: <?= $e($company['tel']) ?></div>
    <?php if ($isTax) { ?><div class="c">TIN: <?= $e($company['tin']) ?></div><?php } ?>

    <div class="sep"></div>
    <div class="c b"><?= $e($title) ?></div>
    <div class="row"><span>No: <b><?= $e($inv->invoice_no) ?></b></span></div>
    <div class="row"><span>Date: <?= $e(date('d-m-Y', strtotime($inv->invoice_date)) . ' ' . date('H:i', strtotime($inv->insertdatetime))) ?></span><span><?= $cashier !== '' ? 'Cashier: ' . $e($cashier) : '' ?></span></div>
    <?php if ($inv->customer_name) { ?><div>Customer: <?= $e($inv->customer_name) ?></div><?php } ?>
    <?php if ($customer && trim((string)$customer->vat_no) !== '' && trim((string)$customer->vat_no) !== '-') { ?><div>Customer TIN: <?= $e($customer->vat_no) ?></div><?php } ?>

    <div class="sep"></div>
    <div class="row b"><span>Item</span><span><?= $isTax ? 'Amount (excl. VAT)' : 'Amount' ?></span></div>
    <div class="sep"></div>

    <?php foreach ($items as $it) { ?>
        <div class="b"><?= $e($it->product_name) ?></div>
        <?php if ($it->serialno) { ?><div class="small">S/N: <?= $e($it->serialno) ?></div><?php } ?>
        <?php if (!empty($it->is_preorder) && (int)$it->delivery_status === 2) { ?><div class="small">Delivery: <?= $e(date('d-m-Y', strtotime($it->delivery_date))) ?></div><?php } ?>
        <div class="row">
            <span><?= (int)$it->qty ?> x <?= $fm($it->unitprice / $pr) ?></span>
            <span><?= $fm($it->line_total / $pr) ?></span>
        </div>
    <?php } ?>

    <div class="sep"></div>
    <?php if ($discountEx > 0) { ?>
        <div class="row"><span>Sub total</span><span><?= $fm($supplyShown + $discountEx) ?></span></div>
        <div class="row"><span>Discount</span><span>- <?= $fm($discountEx) ?></span></div>
    <?php } ?>
    <?php if ($isTax) { ?>
        <div class="row"><span>Total value of supply</span><span><?= $fm($supply) ?></span></div>
        <div class="row"><span>VAT @ <?= (float)$inv->vat_rate ?>%</span><span><?= $fm($inv->vatamount) ?></span></div>
    <?php } ?>
    <div class="row b big"><span>TOTAL</span><span><?= $fm($inv->net_total) ?></span></div>

    <div class="sep"></div>
    <?php foreach ($pays as $p) { if ((float)$p->amount > 0) { ?>
        <div class="row"><span><?= $e($p->paytype) ?></span><span><?= $fm($p->amount) ?></span></div>
    <?php } } ?>
    <?php if ($inv->change_given > 0) { ?>
        <div class="row"><span>Change</span><span><?= $fm($inv->change_given) ?></span></div>
    <?php } ?>
    <?php if ($inv->balance_due > 0) { ?>
        <div class="row b"><span>Balance due</span><span><?= $fm($inv->balance_due) ?></span></div>
    <?php } ?>

    <div class="sep"></div>
    <div class="c">Thank you for shopping with us!</div>
</div>

<script>
window.addEventListener('load', function () {
    <?php if ($pageMode === 'fixed') { ?>
    // exact page height = bill height
    var el = document.getElementById('bill');
    var mm = Math.ceil(el.getBoundingClientRect().height * 25.4 / 96) + 3;
    var s = document.createElement('style');
    s.textContent = '@page { size: <?= $paperW ?>mm ' + mm + 'mm; margin: 0; }';
    document.head.appendChild(s);
    <?php } ?>

    // opened by the POS Bill button (?print=1): print at once and close the window afterwards
    if (location.search.indexOf('print=1') !== -1) {
        window.onafterprint = function () { window.close(); };
        setTimeout(function () { window.print(); }, 300);
    }
});
</script>
</body>
</html>