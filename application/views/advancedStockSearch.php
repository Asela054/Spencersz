<?php 
include "include/header.php";  
include "include/topnavbar.php"; 
?>

<div id="layoutSidenav">
    <div id="layoutSidenav_nav">
        <?php include "include/menubar.php"; ?>
    </div>
    <div id="layoutSidenav_content">
        <main>
            <div class="page-header page-header-light bg-white shadow">
                <div class="container-fluid">
                    <div class="page-header-content py-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i class="fas fa-truck"></i></div>
                            <span>All Stock View</span>
                        </h1>
                    </div>
                </div>
            </div>

            <div class="container-fluid mt-2 p-2">
                <div class="sr-card p-3">

                    <!-- Filter bar (no dropdowns) -->
                    <div class="sr-filter">
                        <div class="row align-items-end">
                            <div class="col-md-7 mb-2 mb-md-0">
                                <label>Filter results</label>
                                <input type="text" id="filterText" class="form-control"
                                    placeholder="Product, code, batch or warehouse">
                            </div>
                            <div class="col-md-5 text-md-right">
                                <button type="button" id="btnSearch" class="btn sr-btn-main"><i class="fas fa-search mr-1"></i> Search</button>
                                <button type="button" id="btnPdf" class="btn btn-light border"><i class="fas fa-file-pdf text-danger mr-1"></i> Export PDF</button>
                                <button type="button" id="btnPrint" class="btn btn-light border"><i class="fas fa-print mr-1"></i> Print</button>
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="sr-summary">
                        <div><div class="num" id="sumLines">0</div><div class="lbl">Stock lines</div></div>
                        <div><div class="num" id="sumQty">0</div><div class="lbl">Total quantity</div></div>
                        <div class="total"><div class="num" id="sumValue">Rs. 0.00</div><div class="lbl">Total stock value</div></div>
                    </div>

                    <!-- Stock table -->
                    <div id="reportBody"></div>
                </div>
            </div>
        </main>
        <?php include "include/footerbar.php"; ?>
    </div>
</div>

<?php include "include/footerscripts.php"; ?>
<script>
$(document).ready(function() {

    var allRows = [];
    var viewRows = []; // currently displayed (filtered) rows, used by PDF/Print
    

    function money(n) {
        return (parseFloat(n) || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    function esc(s) {
        return $('<div>').text(s == null ? '' : s).html();
    }

    // Load data once
    $.ajax({
        url: "<?php echo base_url() ?>scripts/advancereportlist.php",
        type: "POST",
        dataType: "json",
        data: {company_id: '<?php echo (int)$_SESSION['company_id']; ?>'},
        success: function(res) {
            allRows = res.data || [];
            render();
        },
        error: function(xhr) {
            var msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error
                    : (xhr.responseText || xhr.statusText || '').substring(0, 400);
            $('#reportBody').html('<div class="alert alert-danger mt-3"><b>Failed to load stock data.</b><br>' +
                'HTTP ' + xhr.status + ': ' + esc(msg) + '</div>');
        }
    });

    function render() {
        var q = $.trim($('#filterText').val()).toLowerCase();

        viewRows = allRows.filter(function(r) {
            if (!q) return true;
            return [r.product_name, r.product_code, r.batchno, r.wh_name]
                .join(' ').toLowerCase().indexOf(q) !== -1;
        });

        var totalQty = 0, totalValue = 0, body = '';

        viewRows.forEach(function(r, i) {
            var qty = parseFloat(r.qty) || 0;
            var cost = parseFloat(r.costunitprice) || 0;
            var sale = parseFloat(r.saleprice) || 0;
            var line = qty * cost;
            totalQty += qty; totalValue += line;

            body += '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + esc(r.product_name) + '<div class="sr-code">' + esc(r.product_code) + '</div></td>' +
                '<td>' + esc(r.batchno) + '</td>' +
                '<td>' + esc(r.wh_name) + '</td>' +
                '<td class="text-right">' + qty + '</td>' +
                '<td class="text-center"><span class="sr-uom">' + esc(r.unit) + '</span></td>' +
                '<td class="text-right">' + money(cost) + '</td>' +
                '<td class="text-right">' + money(sale) + '</td>' +
                '<td class="text-right">' + money(line) + '</td>' +
            '</tr>';
        });

        if (!viewRows.length) {
            body = '<tr><td colspan="9" class="text-center text-muted py-4">No stock found.</td></tr>';
        }

        $('#reportBody').html(
            '<div class="sr-table-wrap"><div class="table-responsive">' +
            '<table class="table table-sm"><thead><tr>' +
                '<th>#</th><th>Product</th><th>Batch no</th><th>Warehouse</th>' +
                '<th class="text-right">Quantity</th><th class="text-center">UOM</th>' +
                '<th class="text-right">Cost price</th><th class="text-right">Sale price</th>' +
                '<th class="text-right">Total</th>' +
            '</tr></thead><tbody>' + body + '</tbody>' +
            '<tfoot><tr class="font-weight-bold" style="font-weight:700;background:#f8f9fa;">' +
                '<td colspan="4" class="text-right" style="font-weight:700;">Grand total</td>' +
                '<td class="text-right" style="font-weight:700;">' + totalQty + '</td><td></td><td></td><td></td>' +
                '<td class="text-right" style="font-weight:700;">' + money(totalValue) + '</td></tr></tfoot>'  +
            '</table></div></div>');

        $('#sumLines').text(viewRows.length);
        $('#sumQty').text(totalQty);
        $('#sumValue').text('Rs. ' + money(totalValue));
    }

    $('#btnSearch').on('click', render);
    $('#filterText').on('keyup', render);

    // PRINT
    $('#btnPrint').on('click', function() {
        var w = window.open('', '_blank');
        var h = '<html><head><title>All Stock Information</title><style>' +
            'body{font-family:Arial,sans-serif;font-size:12px;padding:20px}' +
            'h2{margin:0 0 10px}table{width:100%;border-collapse:collapse;margin-top:10px}' +
            'th,td{border:1px solid #ccc;padding:5px 7px;text-align:left}' +
            '.r{text-align:right}tfoot td{font-weight:bold}' +
            '</style></head><body><h2>All Stock Information</h2>' +
            '<div>Stock lines: ' + $('#sumLines').text() + ' | Total quantity: ' + $('#sumQty').text() +
            ' | Total value: ' + $('#sumValue').text() + '</div>' +
            '<table><thead><tr><th>#</th><th>Code</th><th>Product</th><th>Batch no</th><th>Warehouse</th>' +
            '<th class="r">Qty</th><th>UOM</th><th class="r">Cost price</th><th class="r">Sale price</th><th class="r">Total</th></tr></thead><tbody>';
        var tq = 0, tv = 0;
        viewRows.forEach(function(r, i) {
            var qty = parseFloat(r.qty) || 0, cost = parseFloat(r.costunitprice) || 0, sale = parseFloat(r.saleprice) || 0;
            tq += qty; tv += qty * cost;
            h += '<tr><td>' + (i + 1) + '</td><td>' + esc(r.product_code) + '</td><td>' + esc(r.product_name) +
                '</td><td>' + esc(r.batchno) + '</td><td>' + esc(r.wh_name) + '</td><td class="r">' + qty +
                '</td><td>' + esc(r.unit) + '</td><td class="r">' + money(cost) + '</td><td class="r">' +
                money(sale) + '</td><td class="r">' + money(qty * cost) + '</td></tr>';
        });
        h += '</tbody><tfoot><tr><td colspan="5" class="r">Grand total</td><td class="r">' + tq +
            '</td><td></td><td></td><td></td><td class="r">' + money(tv) + '</td></tr></tfoot></table></body></html>';
        w.document.write(h); w.document.close(); w.focus();
        setTimeout(function() { w.print(); }, 300);
    });

    // EXPORT PDF (uses pdfMake, already loaded by DataTables PDF button)
    $('#btnPdf').on('click', function() {
        if (typeof pdfMake === 'undefined') { alert('pdfMake is not loaded on this page.'); return; }
        var R = function(t, bold) { return {text: String(t), alignment: 'right', bold: !!bold}; };
        var body = [[
            {text: '#', bold: true}, {text: 'Code', bold: true}, {text: 'Product', bold: true},
            {text: 'Batch no', bold: true}, {text: 'Warehouse', bold: true}, R('Qty', true),
            {text: 'UOM', bold: true}, R('Cost price', true), R('Sale price', true), R('Total', true)
        ]];
        var tq = 0, tv = 0;
        viewRows.forEach(function(r, i) {
            var qty = parseFloat(r.qty) || 0, cost = parseFloat(r.costunitprice) || 0, sale = parseFloat(r.saleprice) || 0;
            tq += qty; tv += qty * cost;
            body.push([String(i + 1), r.product_code || '', r.product_name || '', r.batchno || '', r.wh_name || '',
                R(qty), r.unit || '', R(money(cost)), R(money(sale)), R(money(qty * cost))]);
        });
        body.push([{text: 'Grand total', colSpan: 5, alignment: 'right', bold: true}, {}, {}, {}, {},
            R(tq, true), '', '', '', R(money(tv), true)]);

        pdfMake.createPdf({
            pageOrientation: 'landscape',
            defaultStyle: {fontSize: 8},
            content: [
                {text: 'All Stock Information', fontSize: 14, bold: true, margin: [0, 0, 0, 4]},
                {text: 'Stock lines: ' + $('#sumLines').text() + '   Total quantity: ' + $('#sumQty').text() +
                       '   Total value: ' + $('#sumValue').text(), margin: [0, 0, 0, 8]},
                {table: {headerRows: 1, widths: ['auto', 'auto', '*', 'auto', 'auto', 'auto', 'auto', 'auto', 'auto', 'auto'], body: body},
                 layout: 'lightHorizontalLines'}
            ]
        }).download('All_Stock_Information.pdf');
    });
});
</script>

<?php include "include/footer.php"; ?>