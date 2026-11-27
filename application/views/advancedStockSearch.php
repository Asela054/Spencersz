<?php 
include "include/header.php";  
include "include/topnavbar.php"; 
?>

<style>
    .sr-filter-row { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px; }
    .sr-filter-row .fld { flex: 1 1 220px; min-width: 200px; }
    .sr-filter-row .chk { flex: 0 0 auto; display: flex; align-items: center; padding-bottom: 8px; }
    .sr-filter-row .act { flex: 1 1 auto; display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-end; }
    .sr-table-wrap th, .sr-table-wrap td { white-space: nowrap; vertical-align: middle; }
    .sr-table-wrap td.nm { white-space: normal; min-width: 200px; }
    .sr-table-wrap .sr-code { font-size: 11px; color: #8a8f9c; }
</style>

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

                    <!-- Filter bar -->
                    <div class="sr-filter">
                        <div class="sr-filter-row">
                            <div class="fld">
                                <label>Supplier</label>
                                <select id="supplierFilter" class="form-control">
                                    <option value="">All suppliers</option>
                                </select>
                            </div>
                            <div class="fld">
                                <label>Filter results</label>
                                <input type="text" id="filterText" class="form-control"
                                    placeholder="Product, code, batch or warehouse">
                            </div>

                            <?php if (!empty($canviewall)) { ?>
                            <div class="chk">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="allBranches">
                                    <label class="custom-control-label" for="allBranches">Show all branches stock</label>
                                </div>
                            </div>
                            <?php } ?>

                            <div class="act">
                                <button type="button" id="btnSearch" class="btn sr-btn-main"><i class="fas fa-search mr-1"></i> Search</button>
                                <button type="button" id="btnExcel" class="btn btn-light border"><i class="fas fa-file-excel text-success mr-1"></i> Export Excel</button>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.24/jspdf.plugin.autotable.min.js"></script>
<script>
$(document).ready(function() {

    var allRows  = [];
    var viewRows = [];
    var showBranchCol = false;   // true when "all branches" data is loaded

    function money(n) {
        return (parseFloat(n) || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    function qtyFmt(n) {
        return (parseFloat(n) || 0).toLocaleString('en-US', {maximumFractionDigits: 3});
    }
    function esc(s) {
        return $('<div>').text(s == null ? '' : s).html();
    }
    function supplierLabel() {
        return $('#supplierFilter').val() ? $('#supplierFilter option:selected').text() : 'All suppliers';
    }
    function scopeLabel() {
        return showBranchCol ? 'All branches' : 'Current branch';
    }

    /* ---------- load data ---------- */
    function loadData() {
        $('#reportBody').html('<div class="text-center text-muted py-4"><i class="fas fa-circle-notch fa-spin mr-1"></i> Loading stock...</div>');

        var all = $('#allBranches').is(':checked') ? 1 : 0;

        $.ajax({
            url: "<?php echo base_url() ?>scripts/advancereportlist.php",
            type: "POST",
            dataType: "json",
            data: {
                branch_id: '<?php echo (int)($_SESSION['branch_id'] ?? 0); ?>',
                all_branches: all
            },
            success: function(res) {
                allRows = res.data || [];
                showBranchCol = !!all;

                // rebuild supplier options, keep current selection if still present
                var keep = $('#supplierFilter').val();
                var seen = {}, opts = '<option value="">All suppliers</option>';
                allRows.forEach(function(r) {
                    var id = r.supplier_id || 'none';
                    if (!seen[id]) {
                        seen[id] = true;
                        opts += '<option value="' + esc(id) + '">' + esc(r.supplier_name || 'No supplier') + '</option>';
                    }
                });
                $('#supplierFilter').html(opts);
                if (keep && seen[keep]) { $('#supplierFilter').val(keep); }

                render();
            },
            error: function(xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error
                        : (xhr.responseText || xhr.statusText || '').substring(0, 400);
                $('#reportBody').html('<div class="alert alert-danger mt-3"><b>Failed to load stock data.</b><br>' +
                    'HTTP ' + xhr.status + ': ' + esc(msg) + '</div>');
            }
        });
    }

    /* ---------- render table ---------- */
    function render() {
        var q   = $.trim($('#filterText').val()).toLowerCase();
        var sup = $('#supplierFilter').val();

        viewRows = allRows.filter(function(r) {
            if (sup && String(r.supplier_id || 'none') !== sup) return false;
            if (!q) return true;
            return [r.product_name, r.product_code, r.model_no, r.wh_name, r.grn_no]
                .join(' ').toLowerCase().indexOf(q) !== -1;
        });

        var totalQty = 0, totalValue = 0, body = '';

        viewRows.forEach(function(r, i) {
            var qty  = parseFloat(r.qty) || 0;
            var cost = parseFloat(r.cost_price) || 0;
            var gp   = parseFloat(r.grn_unit_price) || 0;
            var line = qty * cost;
            totalQty += qty; totalValue += line;

            body += '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td class="nm">' + esc(r.product_name) + '<div class="sr-code">' + esc(r.product_code) + '</div></td>' +
                '<td>' + esc(r.model_no) + '</td>' +
                '<td>' + esc(r.wh_name) + '</td>' +
                '<td class="text-right">' + qtyFmt(qty) + '</td>' +
                '<td class="text-center"><span class="sr-uom">' + esc(r.unit) + '</span></td>' +
                '<td class="text-right">' + money(gp) + '</td>' +
                '<td class="text-right">' + money(cost) + '</td>' +
                '<td class="text-right">' + money(line) + '</td>' +
            '</tr>';
        });

        if (!viewRows.length) {
            body = '<tr><td colspan="9" class="text-center text-muted py-4">No stock found.</td></tr>';
        }

        $('#reportBody').html(
            '<div class="sr-table-wrap"><div class="table-responsive">' +
            '<table class="table table-sm table-hover"><thead><tr>' +
                '<th>#</th><th>Product</th><th>Model no</th><th>Location</th>' +
                '<th class="text-right">Quantity</th><th class="text-center">UOM</th>' +
                '<th class="text-right">GRN unit price</th><th class="text-right">Cost price</th>' +
                '<th class="text-right">Total</th>' +
            '</tr></thead><tbody>' + body + '</tbody>' +
            '<tfoot><tr style="font-weight:700;background:#f8f9fa;">' +
                '<td colspan="4" class="text-right">Grand total</td>' +
                '<td class="text-right">' + qtyFmt(totalQty) + '</td><td></td><td></td><td></td>' +
                '<td class="text-right">' + money(totalValue) + '</td></tr></tfoot>' +
            '</table></div></div>');

        $('#sumLines').text(viewRows.length);
        $('#sumQty').text(qtyFmt(totalQty));
        $('#sumValue').text('Rs. ' + money(totalValue));
    }

    /* ---------- events ---------- */
    $('#btnSearch').on('click', loadData);
    $('#filterText').on('keyup', render);
    $('#supplierFilter').on('change', render);
    $('#allBranches').on('change', loadData);

    loadData();

    /* ---------- EXPORT EXCEL ---------- */
    $('#btnExcel').on('click', function() {
        if (typeof XLSX === 'undefined') { alert('Excel library is not loaded.'); return; }

        var aoa = [
            ['All Stock Information'],
            ['Supplier: ' + supplierLabel() + '   |   Scope: ' + scopeLabel()],
            [],
            ['#', 'Code', 'Product', 'Model no', 'Supplier', 'Location', 'Qty', 'UOM', 'GRN unit price', 'Cost price', 'Total']
        ];
        var tq = 0, tv = 0;
        viewRows.forEach(function(r, i) {
            var qty = parseFloat(r.qty) || 0, cost = parseFloat(r.cost_price) || 0, gp = parseFloat(r.grn_unit_price) || 0;
            tq += qty; tv += qty * cost;
            aoa.push([i + 1, r.product_code || '', r.product_name || '', r.model_no || '', r.supplier_name || '',
                      r.wh_name || '', qty, r.unit || '', gp, cost, qty * cost]);
        });
        aoa.push(['', '', '', '', '', 'Grand total', tq, '', '', '', tv]);

        var ws = XLSX.utils.aoa_to_sheet(aoa);
        ws['!cols'] = [{wch:5},{wch:14},{wch:32},{wch:20},{wch:24},{wch:16},{wch:10},{wch:8},{wch:14},{wch:12},{wch:14}];
        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'All Stock');
        XLSX.writeFile(wb, 'All_Stock_Information.xlsx');
    });

    /* ---------- PRINT ---------- */
    $('#btnPrint').on('click', function() {
        var w = window.open('', '_blank');
        var h = '<html><head><title>All Stock Information</title><style>' +
            'body{font-family:Arial,sans-serif;font-size:12px;padding:20px}' +
            'h2{margin:0 0 10px}table{width:100%;border-collapse:collapse;margin-top:10px}' +
            'th,td{border:1px solid #ccc;padding:5px 7px;text-align:left}' +
            '.r{text-align:right}tfoot td{font-weight:bold}' +
            '</style></head><body><h2>All Stock Information</h2>' +
            '<div>Supplier: ' + esc(supplierLabel()) + ' | Scope: ' + scopeLabel() +
            ' | Stock lines: ' + $('#sumLines').text() +
            ' | Total quantity: ' + $('#sumQty').text() +
            ' | Total value: ' + $('#sumValue').text() + '</div>' +
            '<table><thead><tr><th>#</th><th>Code</th><th>Product</th><th>Model no</th><th>Location</th>' +
            '<th class="r">Qty</th><th>UOM</th><th class="r">GRN unit price</th><th class="r">Cost price</th><th class="r">Total</th></tr></thead><tbody>';
        var tq = 0, tv = 0;
        viewRows.forEach(function(r, i) {
            var qty = parseFloat(r.qty) || 0, cost = parseFloat(r.cost_price) || 0, gp = parseFloat(r.grn_unit_price) || 0;
            tq += qty; tv += qty * cost;
            h += '<tr><td>' + (i + 1) + '</td><td>' + esc(r.product_code) + '</td><td>' + esc(r.product_name) +
                '</td><td>' + esc(r.model_no) + '</td><td>' + esc(r.wh_name) + '</td><td class="r">' + qtyFmt(qty) +
                '</td><td>' + esc(r.unit) + '</td><td class="r">' + money(gp) + '</td><td class="r">' +
                money(cost) + '</td><td class="r">' + money(qty * cost) + '</td></tr>';
        });
        h += '</tbody><tfoot><tr><td colspan="5" class="r">Grand total</td><td class="r">' + qtyFmt(tq) +
            '</td><td></td><td></td><td></td><td class="r">' + money(tv) + '</td></tr></tfoot></table></body></html>';
        w.document.write(h); w.document.close(); w.focus();
        setTimeout(function() { w.print(); }, 300);
    });

    /* ---------- EXPORT PDF (jsPDF + autoTable) ---------- */
    $('#btnPdf').on('click', function() {
        if (!window.jspdf) { alert('PDF library is not loaded.'); return; }

        var doc = new window.jspdf.jsPDF({ orientation: 'landscape', unit: 'pt', format: 'a4' });
        var pageW = doc.internal.pageSize.getWidth();

        doc.setFontSize(16);
        doc.text('All Stock Information', 40, 40);
        doc.setFontSize(9);
        doc.setTextColor(110);
        doc.text('Generated: ' + new Date().toLocaleString() + '   |   Supplier: ' + supplierLabel() +
                 '   |   Scope: ' + scopeLabel(), 40, 56);
        doc.text('Total stock value: ' + $('#sumValue').text(), pageW - 40, 56, { align: 'right' });
        doc.setTextColor(0);

        var body = [], tq = 0, tv = 0;
        viewRows.forEach(function(r, i) {
            var qty = parseFloat(r.qty) || 0, cost = parseFloat(r.cost_price) || 0, gp = parseFloat(r.grn_unit_price) || 0;
            tq += qty; tv += qty * cost;
            body.push([
                String(i + 1),
                (r.product_code ? r.product_code + ' - ' : '') + (r.product_name || ''),
                r.model_no || '', r.wh_name || '',
                qtyFmt(qty), r.unit || '', money(gp), money(cost), money(qty * cost)
            ]);
        });

        doc.autoTable({
            startY: 72,
            head: [['#', 'Product', 'Model no', 'Location', 'Qty', 'UOM', 'GRN unit price', 'Cost price', 'Total']],
            body: body,
            foot: [['', 'Grand total', '', '', qtyFmt(tq), '', '', '', money(tv)]],
            styles: { fontSize: 8, cellPadding: 3 },
            headStyles: { fillColor: [163, 15, 45] },
            footStyles: { fillColor: [247, 248, 251], textColor: 20, fontStyle: 'bold' },
            columnStyles: { 4: { halign: 'right' }, 6: { halign: 'right' }, 7: { halign: 'right' }, 8: { halign: 'right' } },
            margin: { left: 40, right: 40 }
        });

        doc.save('All_Stock_Information.pdf');
    });
});
</script>

<?php include "include/footer.php"; ?>