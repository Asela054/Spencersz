<?php
/* =====================================================================
   Pre-printed form, 9.5 x 11 inch continuous paper, Epson LQ-310.
   Only the DATA is printed here: the boxes, headings and logo are already on the paper.
   Positions come from the artwork (1382 x 1600 px = 684 x 792 pt).
   ===================================================================== */
$fm = function ($n) { return number_format((float)$n, 2); };
$e  = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

$discountEx = round($inv->discount / $rate, 2);
$grossEx    = round($supply + $discountEx, 2);

$S     = 0.495;     // pt per px of the artwork
$MONO  = 5.418;     // width of one character (pt): DejaVu Sans Mono, 9pt
$LH    = 12;        // height of one item line (pt)
$offX  = isset($offX) ? (float)$offX : 0;
$offY  = isset($offY) ? (float)$offY : 0;
$guide = !empty($guide);

$py = function ($px) use ($S) { return $px * $S; };                      // artwork px -> pt (no offset)
$N  = function ($x1, $x2) use ($S, $MONO) {                              // characters that fit between x1 and x2
    return max(1, (int)floor((($x2 - $x1 - 10) * $S - 2) / $MONO));
};
$put = function ($x1, $x2, $y, $h, $text, $align = 'left', $css = '') use ($S, $offX, $offY, $e) {
    $l = round(($x1 + 5) * $S + $offX, 2);
    $w = round(($x2 - $x1 - 10) * $S, 2);
    return '<div class="t" style="left:' . $l . 'pt; top:' . round($y + $offY, 2) . 'pt; width:' . $w . 'pt; height:' . $h
         . 'pt; line-height:' . $h . 'pt; text-align:' . $align . ';' . $css . '">' . $e($text) . '</div>';
};
$clip = function ($s, $n) {
    $s = (string)$s;
    return (mb_strlen($s) > $n) ? mb_substr($s, 0, $n - 1) . '.' : $s;
};
$wrap = function ($s, $n) {
    $s = trim(preg_replace('/\s+/', ' ', (string)$s));
    $out = [];
    while (mb_strlen($s) > $n) {
        $cut = mb_strrpos(mb_substr($s, 0, $n + 1), ' ');
        if ($cut === false || $cut < 1) { $cut = $n; }
        $out[] = rtrim(mb_substr($s, 0, $cut));
        $s = ltrim(mb_substr($s, $cut));
    }
    $out[] = $s;
    return $out;
};

// ----- column positions (px of the artwork) -----
$colCode = [109, 247]; $colModel = [247, 362]; $colDesc = [362, 816];
$colQty  = [816, 911]; $colUnit  = [911, 1058]; $colTot  = [1058, 1273];

$descN  = $N($colDesc[0], $colDesc[1]);
$codeN  = $N($colCode[0], $colCode[1]);
$bodyTop = $py(466) + 3;       // first item line (pt)
$bodyBot = $py(1342);          // bottom of the table body (pt)

// ----- item lines: one block per item (an item is never split between two sheets) -----
$blocks = [];
foreach ($items as $it) {
    $dl = $wrap($it->product_name, $descN);
    $ex = [];
    if ($it->serialno)         { $ex[] = 'S/N: ' . $it->serialno; }
    if ($it->warranty_months)  { $ex[] = 'Warranty: ' . (int)$it->warranty_months . ' months'; }
    if ($ex) {
        $joined = implode(' | ', $ex);
        $dl = array_merge($dl, (mb_strlen($joined) <= $descN) ? [$joined] : $ex);
    }
    $b = [];
    foreach ($dl as $k => $txt) {
        $b[] = ($k === 0)
            ? [$clip($it->product_code, $codeN), '', $txt, (string)(int)$it->qty, $fm($it->unitprice / $rate), $fm($it->line_total / $rate)]
            : ['', '', $txt, '', '', ''];
    }
    $blocks[] = $b;
}

// ----- totals block (printed at the bottom of the table body of the LAST sheet) -----
$tot = [];
$tot[] = ['Unit Price and Total are excluding VAT', '', false];
if ($discountEx > 0) {
    $tot[] = ['Sub Total (excluding VAT)', $fm($grossEx), false];
    $tot[] = ['Less: Discount (excluding VAT)', $fm($discountEx), false];
}
$tot[] = ['Total Value of Supply', $fm($supply), false];
$tot[] = ['VAT Amount @ ' . (float)$inv->vat_rate . '%', $fm($inv->vatamount), false];
$tot[] = ['TOTAL INCLUDING VAT', $fm($inv->net_total), true];
$nTot   = count($tot);
$totTop = $bodyBot - 4 - $nTot * $LH;

// ----- split the lines into sheets -----
$capMid  = 33;                                                   // lines on a sheet that is followed by another sheet
$capLast = (int)floor(($totTop - $bodyTop - 6) / $LH);           // lines on the last sheet (the totals need room)
$pages = []; $cur = []; $used = 0;
foreach ($blocks as $b) {
    $n = count($b);
    if ($used > 0 && $used + $n > $capMid) { $pages[] = $cur; $cur = []; $used = 0; }
    foreach ($b as $ln) { $cur[] = $ln; }
    $used += $n;
}
$pages[] = $cur;
if ($used > $capLast) { $pages[] = []; }                         // no room for the totals: they go on their own sheet
$totalPages = count($pages);

// ----- header boxes -----
$label = function ($l) { return str_pad($l, 17) . ': '; };
$vN    = $N(109, 686) - 19;                                      // characters left for the value

$addr = trim(trim($customer ? $customer->address_line1 : '') . ' ' . trim($customer ? $customer->address_line2 : '') . ' ' . trim($customer ? $customer->city : ''));
$aw   = $wrap($addr, $vN);

$leftLines = [
    $label("Purchaser's TIN")  . $clip($customer ? $customer->vat_no : '', $vN),
    $label("Purchaser's Name") . $clip($inv->customer_name, $vN),
    $label('Address')          . (isset($aw[0]) ? $aw[0] : ''),
    str_repeat(' ', 19)        . (isset($aw[1]) ? $aw[1] : ''),
    $label('Telephone No.')    . $clip($customer ? $customer->telephone_no : '', $vN),
];

$rightLines = [
    $label('Date of Invoice')  . date('d-m-Y', strtotime($inv->invoice_date)),
    $label('Date of Supply')   . date('d-m-Y', strtotime($inv->invoice_date)),
    $label('Place of Supply')  . $clip($company['place'], $vN),
    $label("Supplier's TIN")   . $clip($company['tin'], $vN),
    $label('Reference')        . $clip(trim((string)$inv->remark), $vN),
    ($title === 'QUOTATION')
        ? $label('Valid until') . date('d-m-Y', strtotime($inv->invoice_date . ' +14 days'))
        : $label('Mode of Payment') . $clip(str_replace('  |  ', ', ', $modes), $vN),
];

$wordsLines = $wrap('Amount in words: ' . $words, $N(109, 1273));

// ----- alignment guide (only with ?guide=1): the outline of the pre-printed form, NOT shifted by x / y -----
$rects = [[1083,113,1273,158], [1083,165,1273,210], [1083,217,1273,262], [536,207,847,252], [109,269,686,413],
          [696,269,1273,413], [109,421,1273,461], [109,466,1273,1342], [109,1351,1273,1403]];
$vlines = [247, 362, 816, 911, 1058];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 0; }
    html, body { margin: 0; padding: 0; height: auto; font-family: 'Courier', monospace; font-size: 9pt; color: #000;  }
    .sheet { position: relative; width: 684pt; height: 780pt; overflow: hidden; }
    .t { position: absolute; white-space: pre; overflow: hidden; font-size: 9pt; }
    .g { position: absolute; border: 0.5pt solid #888; }
</style>
</head>
<body>
<?php foreach ($pages as $pi => $lines): $isLast = ($pi === $totalPages - 1); ?>
<div class="sheet" style="<?= $pi > 0 ? 'page-break-before: always;' : '' ?>">

    <?php if ($guide): ?>
        <?php foreach ($rects as $r): ?>
        <div class="g" style="left:<?= round($r[0]*$S, 2) ?>pt; top:<?= round($r[1]*$S, 2) ?>pt; width:<?= round(($r[2]-$r[0])*$S, 2) ?>pt; height:<?= round(($r[3]-$r[1])*$S, 2) ?>pt;"></div>
        <?php endforeach; ?>
        <?php foreach ($vlines as $vx): ?>
        <div class="g" style="left:<?= round($vx*$S, 2) ?>pt; top:<?= round(421*$S, 2) ?>pt; width:0; height:<?= round((1342-421)*$S, 2) ?>pt; border-width:0 0 0 0.5pt;"></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- title, invoice no, page no -->
    <?= $put(536, 847, $py(207), $py(252) - $py(207), $title, 'center', 'font-size:13pt; font-weight:bold;') ?>
    <?= $put(1083, 1273, $py(113), $py(158) - $py(113), $inv->invoice_no, 'center', 'font-size:8.5pt; font-weight:bold;') ?>
    <?= $put(1083, 1273, $py(165), $py(210) - $py(165), ($pi + 1) . ' / ' . $totalPages, 'center') ?>

    <!-- purchaser box and details box -->
    <?php foreach ($leftLines as $i => $txt): ?>
        <?= $put(109, 686, $py(269) + 3 + $i * 11, 11, $txt) ?>
    <?php endforeach; ?>
    <?php foreach ($rightLines as $i => $txt): ?>
        <?= $put(696, 1273, $py(269) + 3 + $i * 11, 11, $txt) ?>
    <?php endforeach; ?>

    <!-- item lines -->
    <?php foreach ($lines as $i => $ln): $y = $bodyTop + $i * $LH; ?>
        <?php if ($ln[0] !== ''): ?><?= $put($colCode[0],  $colCode[1],  $y, $LH, $ln[0]) ?><?php endif; ?>
        <?php if ($ln[2] !== ''): ?><?= $put($colDesc[0],  $colDesc[1],  $y, $LH, $ln[2]) ?><?php endif; ?>
        <?php if ($ln[3] !== ''): ?><?= $put($colQty[0],   $colQty[1],   $y, $LH, $ln[3], 'center') ?><?php endif; ?>
        <?php if ($ln[4] !== ''): ?><?= $put($colUnit[0],  $colUnit[1],  $y, $LH, $ln[4], 'right') ?><?php endif; ?>
        <?php if ($ln[5] !== ''): ?><?= $put($colTot[0],   $colTot[1],   $y, $LH, $ln[5], 'right') ?><?php endif; ?>
    <?php endforeach; ?>

    <?php if ($isLast): ?>
        <!-- totals -->
        <?php foreach ($tot as $i => $r): $y = $totTop + $i * $LH; $css = $r[2] ? 'font-weight:bold;' : ''; ?>
            <?= $put(362, 1058, $y, $LH, $r[0], 'right', $css) ?>
            <?php if ($r[1] !== ''): ?><?= $put(1058, 1273, $y, $LH, $r[1], 'right', $css) ?><?php endif; ?>
        <?php endforeach; ?>

        <!-- amount in words (the single box under the table) -->
        <?php for ($k = 0; $k < 2; $k++): ?>
            <?php if (isset($wordsLines[$k])): ?><?= $put(109, 1273, $py(1351) + 1 + $k * 12, 12, $wordsLines[$k]) ?><?php endif; ?>
        <?php endfor; ?>
    <?php else: ?>
        <?= $put(362, 1273, $bodyTop + $capMid * $LH, $LH, 'Continued on the next sheet ...', 'right') ?>
    <?php endif; ?>

</div>
<?php endforeach; ?>
</body>
</html>