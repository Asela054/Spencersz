<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Barcode - <?php echo htmlspecialchars($product->product_code); ?></title>
<style>
    @page { size: 34mm 25mm; margin: 0; }
    html, body { margin: 0; padding: 0; }
    .label {
        width: 34mm;
        height: 25mm;
        box-sizing: border-box;
        padding: 1mm 1.5mm;
        overflow: hidden;
        page-break-after: always;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        font-family: Arial, sans-serif;
    }
    .label:last-child { page-break-after: auto; }
    .name {
        width: 100%;
        font-size: 7pt;
        font-weight: bold;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .barcode { max-width: 100%; max-height: 13mm; }
    .price { font-size: 8pt; font-weight: bold; }
</style>
</head>
<body>
<?php for($i = 0; $i < $qty; $i++){ ?>
    <div class="label">
        <div class="name"><?php echo htmlspecialchars($product->product_name); ?></div>
        <svg class="barcode"></svg>
        <div class="price">Rs. <?php echo number_format($product->selling_price, 2); ?></div>
    </div>
<?php } ?>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
    JsBarcode(".barcode", <?php echo json_encode((string)$value); ?>, {
        format: "CODE128",
        width: 1.4,
        height: 38,
        displayValue: true,
        fontSize: 11,
        margin: 0
    });
    window.onload = function(){ setTimeout(function(){ window.print(); }, 300); };
</script>
</body>
</html>