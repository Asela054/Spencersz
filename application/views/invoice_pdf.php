<?php
/* =====================================================================
   Invoice / Quotation on pre-printed continuous stationery (LQ-310)
   Artwork = 1382 x 1600 px  =  9.5in x 11in  =  684 x 792 pt
   Every x / y / w / h below is in ARTWORK PIXELS measured from the
   top-left corner of the artwork (converted to pt by $k).
   Only DATA is printed - the form design is already on the paper.

   !! The PDF paper size must be 9.5in x 11in (684 x 792 pt):
      $this->pdf->setPaper(array(0, 0, 684, 792));
   ===================================================================== */
$paperW = 684;     // 9.5in in pt
$paperH = 792;     // 11in in pt
$artW   = 1382;    // artwork width in px
$shiftX = -18;     // printer calibration in mm (+ moves right): the LQ-310 printed ~18 mm too far right
$shiftY = 0;       // printer calibration in mm (+ moves down)
$guide  = false;   // true = red outline around every field (alignment test)

// items grid (body of the form: y 626 - 1168)
$rowsPerPage = 11;   // item rows per page (the slot after them holds "Continued...")
$rowTop      = 634;  // first row y
$rowH        = 44;   // row height (name + S/N line fit)

// Non-tax documents (Retail Invoice / Quotation): print unit price & amount as sold (no VAT split)?
$showPricesOnNonTax = true;

$k = $paperW / $artW;

$fm = function ($n) { return number_format((float)$n, 2); };
$e  = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

// one absolutely positioned data field; $html must already be escaped
$at = function ($x, $y, $w, $h, $html, $align = 'left', $bold = false, $size = null) use ($k, $shiftX, $shiftY, $guide) {
    $left = round($x * $k, 2) + $shiftX * 2.8346;
    $top  = round($y * $k, 2) + $shiftY * 2.8346;
    $st = 'position:absolute;left:' . $left . 'pt;top:' . $top . 'pt;width:' . round($w * $k, 2) . 'pt;height:' . round($h * $k, 2) . 'pt;'
        . 'overflow:hidden;text-align:' . $align . ';';
    if ($bold) { $st .= 'font-weight:bold;'; }
    if ($size) { $st .= 'font-size:' . $size . 'pt;line-height:' . round($size * 1.2, 1) . 'pt;'; }
    if ($guide) { $st .= 'outline:0.4pt solid #f00;'; }
    return '<div style="' . $st . '">' . $html . '</div>';
};

// ---------- tax / non-tax ----------
$isTax       = ($title === 'TAX INVOICE');
$showPrices  = ($isTax || $showPricesOnNonTax);
$priceRate   = $isTax ? $rate : 1;                          // non-tax: prices are printed as sold, no VAT split
$supplyShown = $isTax ? $supply : round($inv->net_total, 2);

$discountEx = round($inv->discount / $priceRate, 2);
$grossEx    = round($supplyShown + $discountEx, 2);

// items -> array, then pages
// $itemList = array();
// foreach ($items as $it) { $itemList[] = $it; }
// $pages = array_chunk($itemList, $rowsPerPage);
// if (empty($pages)) { $pages = array(array()); }
// $totalPages = count($pages);
// part warranties saved with the invoice line -> "Compressor 60 mths, Motor 24 mths"
$partsOf = function ($it) {
    $arr = !empty($it->warranty_parts) ? json_decode($it->warranty_parts, true) : array();
    if (!is_array($arr)) { return ''; }
    $t = array();
    foreach ($arr as $w) {
        if (!empty($w['name'])) { $t[] = $w['name'] . ' ' . (int)$w['months'] . ' months'; }
    }
    return implode(', ', $t);
};

// pages: a row with part warranties is taller (one extra 8pt line per ~50 characters)
$lineH        = 19;                       // px of one extra 8pt line
$charsPerLine = 50;                       // 8pt Courier characters per description line
$bodyH        = $rowsPerPage * $rowH;     // height available for the rows (px)

$pages = array(); $cur = array(); $used = 0;
foreach ($items as $it) {
    $ptxt = $partsOf($it);
    $h = $rowH + ($ptxt !== '' ? (int)ceil((strlen($ptxt) + 7) / $charsPerLine) * $lineH : 0);
    if (!empty($cur) && $used + $h > $bodyH) { $pages[] = $cur; $cur = array(); $used = 0; }
    $cur[] = array('it' => $it, 'parts' => $ptxt, 'h' => $h);
    $used += $h;
}
if (!empty($cur)) { $pages[] = $cur; }
if (empty($pages)) { $pages = array(array()); }
$totalPages = count($pages);

// purchaser
$cusAddrParts = array();
if ($customer) {
    foreach (array($customer->address_line1, $customer->address_line2, $customer->city) as $a) {
        $a = trim((string)$a);
        if ($a !== '' && $a !== '-') { $cusAddrParts[] = $a; }
    }
}
$cusAddr = implode(', ', $cusAddrParts);
$cusTin  = ($customer && trim((string)$customer->vat_no) !== '' && trim((string)$customer->vat_no) !== '-') ? $customer->vat_no : '';
$cusTel  = $customer ? $customer->telephone_no : '';

$invDate = date('d-m-Y', strtotime($inv->invoice_date));
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 0; }
    html, body { margin: 0; padding: 0; }
    body {
        font-family: 'Courier', monospace;
        font-size: 9pt;
        line-height: 10.5pt;
        color: #000;
    }
</style>
</head>
<body>
<?php foreach ($pages as $pi => $lines): $isLast = ($pi == $totalPages - 1); ?>
<div style="position:relative;width:<?= $paperW ?>pt;height:1pt;<?= $isLast ? '' : 'page-break-after:always;' ?>">
<?php
    // ---------- title strip (between the two rules) ----------
    echo $at(102, 209, 1178, 30, $e($title), 'center', true, 12);
    if ($totalPages > 1) {
        echo $at(1090, 214, 180, 24, 'Page ' . ($pi + 1) . ' of ' . $totalPages, 'right');
    }

    // ---------- Date of Invoice / Invoice No. ----------
    echo $at(285, 258, 395, 24, $e($invDate));
    echo $at(880, 258, 390, 24, $e($inv->invoice_no), 'left', true);

    // ---------- Purchaser ----------
    echo $at(285, 310, 575, 24, $e($inv->customer_name), 'left', true);
    if ($cusTin !== '') {
        echo $at(890, 310, 380, 24, 'TIN : ' . $e($cusTin));
    }
    echo $at(285, 339, 575, 84, $e($cusAddr));
    echo $at(285, 425, 575, 24, $e($cusTel));

    // ---------- Date of Supply / Delivery Location / Reference ----------
    echo $at(265, 475, 415, 24, $e($invDate));
    echo $at(870, 475, 400, 24, $e($company['place']));
    echo $at(265, 523, 1000, 24, $e($inv->remark));

    // ---------- items grid ----------
    // foreach ($lines as $i => $it) {
    //     $y = $rowTop + $i * $rowH;

    //     $desc = $e($it->product_name);
    //     $extra = array();
    //     if ($it->serialno) { $extra[] = 'S/N: ' . $e($it->serialno); }
    //     if ($it->warranty_months) { $extra[] = 'Warranty: ' . (int)$it->warranty_months . ' months'; }
    //     if (!empty($extra)) {
    //         $desc .= '<div style="font-size:8pt;line-height:9pt;margin:0;padding:0;">' . implode(' | ', $extra) . '</div>';
    //     }

    //     echo $at(104, $y, 93, $rowH, $e($it->product_code), 'center');                 // Item Code
    //     echo $at(207, $y, 515, $rowH + 6, $desc);                                       // Description of Goods
    //     echo $at(730, $y, 97, $rowH, (int)$it->qty, 'center');                          // Quantity
    //     if ($showPrices) {
    //         echo $at(827, $y, 208, $rowH, $fm($it->unitprice / $priceRate), 'right');   // Unit Price
    //         echo $at(1045, $y, 225, $rowH, $fm($it->line_total / $priceRate), 'right'); // Amount Excluding VAT
    //     }
    // }
    // ---------- items grid ----------
    $y = $rowTop;
    foreach ($lines as $row) {
        $it = $row['it'];
        $h  = $row['h'];

        $desc = $e($it->product_name);
        $extra = array();
        if ($it->serialno) { $extra[] = 'S/N: ' . $e($it->serialno); }
        if ($it->warranty_months) { $extra[] = 'Warranty: ' . (int)$it->warranty_months . ' months'; }
        if (!empty($extra)) {
            $desc .= '<div style="font-size:8pt;line-height:9pt;margin:0;padding:0;">' . implode(' | ', $extra) . '</div>';
        }
        if ($row['parts'] !== '') {
            $desc .= '<div style="font-size:8pt;line-height:9pt;margin:0;padding:0;">Parts: ' . $e($row['parts']) . '</div>';
        }

        echo $at(104, $y, 93, $h, $e($it->product_code), 'center');                    // Item Code
        echo $at(207, $y, 515, $h + 6, $desc);                                          // Description of Goods
        echo $at(730, $y, 97, $h, (int)$it->qty, 'center');                             // Quantity
        if ($showPrices) {
            echo $at(827, $y, 208, $h, $fm($it->unitprice / $priceRate), 'right');      // Unit Price
            echo $at(1045, $y, 225, $h, $fm($it->line_total / $priceRate), 'right');    // Amount Excluding VAT
        }
        $y += $h;
    }

        // ---------- totals (rows pre-printed on the form) / continued ----------
    if ($isLast) {
        if ($discountEx > 0 && $showPrices) {
            echo $at(1045, 1174, 225, 24, $fm($grossEx), 'right');                      // Sub Total (excluding VAT)
            echo $at(1045, 1205, 225, 24, $fm($discountEx), 'right');                   // Less: Discount
        }

        // Total Value of Supply: all document types
        // (tax invoice = net_total - VAT, non-tax = net_total because there is no VAT split)
        echo $at(1045, 1236, 225, 24, $fm($supplyShown), 'right');

        if ($isTax) {
            echo $at(423, 1268, 36, 24, (float)$inv->vat_rate, 'center');               // VAT rate in "@ ___ %"
            echo $at(1045, 1268, 225, 24, $fm($inv->vatamount), 'right');               // VAT Amount
        }
        // non-tax: VAT % and VAT Amount are left empty
        echo $at(1045, 1299, 225, 24, $fm($inv->net_total), 'right', true);             // Total Amount incl. VAT

        // ---------- words / payment ----------
        echo $at(330, 1342, 940, 24, $e($words));                                       // Total Amount in words
        if ($title === 'QUOTATION') {
            echo $at(325, 1374, 945, 24, 'This quotation is valid for 14 days (until ' . $e(date('d-m-Y', strtotime($inv->invoice_date . ' +14 days'))) . ')');
        } else {
            echo $at(325, 1374, 945, 24, $e($modes));                                   // Mode of Payment
        }
    } else {
        echo $at(207, $rowTop + $rowsPerPage * $rowH, 828, 24, 'Continued on next page...', 'right', true);
    }
?>
</div>
<?php endforeach; ?>
</body>
</html>