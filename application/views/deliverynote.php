<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Delivery Note</title>
<style>
    body{font-family:Arial,sans-serif;font-size:13px;margin:20px;color:#000}
    h2{margin:0 0 4px} table{width:100%;border-collapse:collapse;margin-top:10px}
    th,td{border:1px solid #444;padding:5px 7px;text-align:left} th{background:#eee}
    .meta td{border:0;padding:2px 0}
    .sign{margin-top:60px;display:flex;justify-content:space-between}
    .sign div{width:30%;border-top:1px solid #000;text-align:center;padding-top:4px}
    @media print{.noprint{display:none}}
</style>
</head>
<body>
<button class="noprint" onclick="window.print()">Print</button>
<h2>DELIVERY NOTE</h2>
<div>Spencersz Lanka (Pvt) Ltd, 295, Negombo Road, Wattala</div>
<table class="meta">
    <tr><td width="50%"><b>Invoice No:</b> <?php echo htmlspecialchars($inv->invoice_no); ?></td><td><b>Delivered date:</b> <?php echo htmlspecialchars($date); ?></td></tr>
    <tr><td><b>Customer:</b> <?php echo htmlspecialchars($inv->customer_name); ?></td><td><b>Invoice date:</b> <?php echo htmlspecialchars($inv->invoice_date); ?></td></tr>
    <tr><td colspan="2"><b>Address / Tel:</b> <?php echo htmlspecialchars(trim($inv->address_line1.' '.$inv->address_line2.' '.$inv->city.'  '.$inv->telephone_no)); ?></td></tr>
</table>
<table>
    <thead><tr><th>#</th><th>Item Code</th><th>Description</th><th>Qty</th><th>Serial No</th><th>Warranty</th></tr></thead>
    <tbody>
    <?php foreach($rows as $i => $r){ ?>
        <tr>
            <td><?php echo $i+1; ?></td>
            <td><?php echo htmlspecialchars($r->product_code); ?></td>
            <td><?php echo htmlspecialchars($r->product_name); ?></td>
            <td><?php echo (int)$r->qty; ?></td>
            <td><?php echo htmlspecialchars((string)$r->serialno); ?></td>
            <td><?php echo $r->warranty_months ? (int)$r->warranty_months.' months (from '.htmlspecialchars($date).')' : '-'; ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<div class="sign"><div>Delivered by</div><div>Received by (name / signature)</div><div>Date</div></div>
</body>
</html>