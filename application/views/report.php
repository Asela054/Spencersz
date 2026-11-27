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
							<div class="page-header-icon"><i class="fas fa-file-alt"></i></div>
							<span>Stock Category Wise Report</span>
						</h1>
					</div>
				</div>
			</div>
			<div class="container-fluid mt-2 p-0 p-2 sr">
				<div class="card">
					<div class="card-body">

						<form id="searchStock" autocomplete="off">
							<div class="sr-filter">
								<div class="grow">
									<label for="category">Category</label>
									<select class="form-control form-control-sm selecter2 px-0" name="category" id="category" required>
										<option value="">Select a category</option>
										<option value="0" selected>All categories</option>
										<?php foreach ($getcategory->result() as $rowgetcategory) { ?>
										<option value="<?php echo $rowgetcategory->idtbl_category ?>"><?php echo $rowgetcategory->category ?></option>
										<?php } ?>
									</select>
								</div>
								<div class="grow">
									<label for="supplier">Supplier</label>
									<select class="form-control form-control-sm selecter2 px-0" name="supplier" id="supplier">
										<option value="0" selected>All suppliers</option>
										<?php foreach ($getsupplier->result() as $rowgetsupplier) { ?>
										<option value="<?php echo $rowgetsupplier->idtbl_supplier ?>"><?php echo $rowgetsupplier->suppliername ?></option>
										<?php } ?>
									</select>
								</div>
								<div class="grow">
									<label for="quicksearch">Filter results</label>
									<input type="text" id="quicksearch" class="form-control form-control-sm" placeholder="Product, code, batch or warehouse" disabled>
								</div>

								<?php if (!empty($canviewall)) { ?>
								<div class="grow" style="display:flex;align-items:center">
									<div class="custom-control custom-checkbox">
										<input type="checkbox" class="custom-control-input" id="allBranches">
										<label class="custom-control-label" for="allBranches">Show all branches stock</label>
									</div>
								</div>
								<?php } ?>

								<div class="sr-actions">
									<button type="submit" id="submitBtnStock" class="btn btn-brand btn-sm px-4" <?php if(isset($addcheck) && $addcheck==0){echo 'disabled';} ?>>
										<i class="fas fa-search mr-1"></i>Search
									</button>
									<button type="button" id="btnExcel" class="btn btn-ghost btn-sm px-3" onclick="exportToExcel()" disabled>
										<i class="fas fa-file-excel mr-1 text-success"></i>Export Excel
									</button>
									<button type="button" id="btnPdf" class="btn btn-ghost btn-sm px-3" onclick="exportToPDF()" disabled>
										<i class="fas fa-file-pdf mr-1 text-danger"></i>Export PDF
									</button>
									<button type="button" id="btnPrint" class="btn btn-ghost btn-sm px-3" onclick="window.print()" disabled>
										<i class="fas fa-print mr-1"></i>Print
									</button>
								</div>
							</div>
						</form>

						<div id="summary"></div>
						<div id="mainTable" class="mt-3">
							<div class="sr-empty">
								<div><i class="fas fa-boxes"></i></div>
								Choose a category and select Search to see current stock.
							</div>
						</div>

					</div>
				</div>
			</div>
		</main>
		<?php include "include/footerbar.php"; ?>
	</div>
</div>
<?php include "include/footerscripts.php"; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.24/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
var reportGroups = {};   // { categoryName: [rows] }
var reportTotals = {};
var loadedAllBranches = false;

function fmt(n) {
    return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function fmtQty(n) {
    return n.toLocaleString('en-US', { maximumFractionDigits: 3 });
}
function esc(s) {
    return $('<div>').text(s == null ? '' : s).html();
}
function supplierLabel() {
    var v = $('#supplier').val();
    return (v && v !== '0') ? $('#supplier option:selected').text() : 'All suppliers';
}
function scopeLabel() {
    return loadedAllBranches ? 'All branches' : 'Current branch';
}

$(document).ready(function () {

    $("#searchStock").submit(function (event) {
        event.preventDefault();

        $('#mainTable').html('<div class="sr-empty"><i class="fas fa-circle-notch fa-spin"></i><div>Loading stock...</div></div>');
        $('#summary').empty();

        var all = $('#allBranches').is(':checked') ? 1 : 0;

        $.ajax({
            type: "POST",
            url: "<?php echo base_url(); ?>Report/stockReport",
            dataType: "text",
            data: {
                category: $("#category").val(),
                supplier: $("#supplier").val(),
                all_branches: all
            },
            success: function (result) {
                var dataArray;
                try {
                    dataArray = JSON.parse(result);
                } catch (e) {
                    $('#mainTable').html('<div class="sr-empty"><i class="fas fa-exclamation-triangle"></i>' +
                        '<div>Server returned an invalid response:<br><small>' + esc(result.substring(0, 400)) + '</small></div></div>');
                    return;
                }

                loadedAllBranches = !!all;
                reportGroups = {};
                reportTotals = { qty: 0, value: 0, lines: dataArray.length };

                dataArray.forEach(function (item) {
                    var name = item.group || 'No category';
                    (reportGroups[name] = reportGroups[name] || []).push(item);
                    var q = parseFloat(item.qty) || 0;
                    reportTotals.qty += q;
                    reportTotals.value += q * (parseFloat(item.unitprice) || 0);
                });

                $('#quicksearch').val('');
                renderReport();
            },
            error: function (xhr) {
                $('#mainTable').html('<div class="sr-empty"><i class="fas fa-exclamation-triangle"></i>' +
                    '<div>Could not load the report (HTTP ' + xhr.status + ').<br><small>' +
                    esc((xhr.responseText || xhr.statusText || '').substring(0, 400)) + '</small></div></div>');
            }
        });
    });

    // reload when the all-branches box is toggled
    $('#allBranches').on('change', function () { $('#searchStock').submit(); });

    $('#quicksearch').on('input', function () {
        var term = $.trim($(this).val()).toLowerCase();
        $('#mainTable .sr-group').each(function () {
            var visible = 0;
            $(this).find('tbody tr').each(function () {
                var match = term === '' || $(this).text().toLowerCase().indexOf(term) > -1;
                $(this).toggle(match);
                if (match) visible++;
            });
            $(this).toggle(visible > 0);
            if (term !== '' && visible > 0) $(this).removeClass('collapsed');
        });
    });

    $('#mainTable').on('click', '.sr-group-head', function () {
        $(this).closest('.sr-group').toggleClass('collapsed');
    });

    $("#searchStock").submit();   // load all on open
});

function renderReport() {
    var names = Object.keys(reportGroups).sort();
    var hasData = names.length > 0;

    $('#quicksearch, #btnExcel, #btnPdf, #btnPrint').prop('disabled', !hasData);

    if (!hasData) {
        $('#summary').empty();
        $('#mainTable').html('<div class="sr-empty"><i class="fas fa-box-open"></i><div>No stock found for this selection.</div></div>');
        return;
    }

    $('#summary').html(
        '<div class="sr-summary">' +
            '<div class="sr-stat"><div class="v">' + names.length + '</div><div class="l">Categories</div></div>' +
            '<div class="sr-stat"><div class="v">' + reportTotals.lines + '</div><div class="l">Stock lines</div></div>' +
            '<div class="sr-stat"><div class="v">' + fmtQty(reportTotals.qty) + '</div><div class="l">Total quantity</div></div>' +
            '<div class="sr-stat"><div class="v">Rs. ' + fmt(reportTotals.value) + '</div><div class="l">Total stock value</div></div>' +
        '</div>'
    );

    var html = '';
    names.forEach(function (name) { html += generateTable(name, reportGroups[name]); });
    $('#mainTable').html(html);
}

function generateTable(type, items) {
    var groupQty = 0, groupTotal = 0, rows = '';

    items.forEach(function (item) {
        var qty = parseFloat(item.qty) || 0;
        var price = parseFloat(item.unitprice) || 0;
        var total = qty * price;
        groupQty += qty;
        groupTotal += total;

        rows += '<tr>' +
            '<td><div>' + esc(item.product_name) + '</div><div class="code">' + esc(item.product_code) + '</div></td>' +
            '<td>' + esc(item.model_no) + '</td>' +
            '<td>' + esc(item.wh_name) + '</td>' +
            '<td class="num">' + fmtQty(qty) + '</td>' +
            '<td class="ctr"><span class="pill">' + esc(item.measure_type) + '</span></td>' +
            '<td class="num">' + fmt(price) + '</td>' +
            '<td class="num">' + fmt(total) + '</td>' +
        '</tr>';
    });

    return '' +
    '<div class="sr-group">' +
        '<div class="sr-group-head">' +
            '<h5><i class="fas fa-chevron-down chev"></i>' + esc(type) + '</h5>' +
            '<div class="meta">' + items.length + ' items &nbsp;|&nbsp; Value <strong>Rs. ' + fmt(groupTotal) + '</strong></div>' +
        '</div>' +
        '<div class="sr-table-wrap">' +
            '<table class="sr-table">' +
                '<thead><tr>' +
                    '<th>Product</th><th>Model no</th><th>Warehouse</th>' +
                    '<th class="num">Quantity</th><th class="ctr">UOM</th>' +
                    '<th class="num">Cost price</th><th class="num">Total</th>' +
                '</tr></thead>' +
                '<tbody>' + rows + '</tbody>' +
                '<tfoot><tr>' +
                    '<td colspan="3" class="num">Category total</td>' +
                    '<td class="num">' + fmtQty(groupQty) + '</td>' +
                    '<td></td><td></td>' +
                    '<td class="num">' + fmt(groupTotal) + '</td>' +
                '</tr></tfoot>' +
            '</table>' +
        '</div>' +
    '</div>';
}

function exportToExcel() {
    var names = Object.keys(reportGroups).sort();
    if (!names.length) { return; }
    if (typeof XLSX === 'undefined') { alert('Excel library is not loaded.'); return; }

    var aoa = [
        ['Stock Category Wise Report'],
        ['Supplier: ' + supplierLabel() + '   |   Scope: ' + scopeLabel()],
        [],
        ['Category', 'Product code', 'Product', 'Supplier', 'Model no', 'Warehouse', 'Quantity', 'UOM', 'Cost price', 'Total']
    ];
    var tq = 0, tv = 0;

    names.forEach(function (name) {
        var gq = 0, gt = 0;
        reportGroups[name].forEach(function (it) {
            var q = parseFloat(it.qty) || 0, p = parseFloat(it.unitprice) || 0;
            gq += q; gt += q * p;
            aoa.push([name, it.product_code || '', it.product_name || '', it.supplier_name || '',
                      it.model_no || '', it.wh_name || '', q, it.measure_type || '', p, q * p]);
        });
        aoa.push([name + ' total', '', '', '', '', '', gq, '', '', gt]);
        tq += gq; tv += gt;
    });
    aoa.push(['Grand total', '', '', '', '', '', tq, '', '', tv]);

    var ws = XLSX.utils.aoa_to_sheet(aoa);
    ws['!cols'] = [{wch:22},{wch:14},{wch:32},{wch:24},{wch:14},{wch:16},{wch:10},{wch:8},{wch:12},{wch:14}];
    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Stock by Category');
    XLSX.writeFile(wb, 'stock_category_report.xlsx');
}

function exportToPDF() {
    var names = Object.keys(reportGroups).sort();
    if (!names.length) { return; }
    if (!window.jspdf) { alert('PDF library is not loaded.'); return; }

    var jsPDF = window.jspdf.jsPDF;
    var doc = new jsPDF({ orientation: 'landscape', unit: 'pt', format: 'a4' });
    var pageW = doc.internal.pageSize.getWidth();

    doc.setFontSize(16);
    doc.text('Stock Category Wise Report', 40, 40);
    doc.setFontSize(9);
    doc.setTextColor(110);
    doc.text('Generated: ' + new Date().toLocaleString() + '   |   Supplier: ' + supplierLabel() +
             '   |   Scope: ' + scopeLabel(), 40, 56);
    doc.text('Total stock value: Rs. ' + fmt(reportTotals.value), pageW - 40, 56, { align: 'right' });
    doc.setTextColor(0);

    var y = 72;
    names.forEach(function (name) {
        var items = reportGroups[name], gq = 0, gt = 0, body = [];
        items.forEach(function (it) {
            var q = parseFloat(it.qty) || 0, p = parseFloat(it.unitprice) || 0;
            gq += q; gt += q * p;
            body.push([
                (it.product_code ? it.product_code + ' - ' : '') + (it.product_name || ''),
                it.model_no || '', it.wh_name || '', fmtQty(q), it.measure_type || '', fmt(p), fmt(q * p)
            ]);
        });

        doc.autoTable({
            startY: y,
            head: [[{ content: name, colSpan: 7, styles: { halign: 'left', fillColor: [163, 15, 45], textColor: 255 } }],
                   ['Product', 'Model no', 'Warehouse', 'Quantity', 'UOM', 'Cost price', 'Total']],
            body: body,
            foot: [['Category total', '', '', fmtQty(gq), '', '', fmt(gt)]],
            styles: { fontSize: 8.5, cellPadding: 4 },
            headStyles: { fillColor: [235, 237, 243], textColor: 40 },
            footStyles: { fillColor: [247, 248, 251], textColor: 20, fontStyle: 'bold' },
            columnStyles: { 3: { halign: 'right' }, 5: { halign: 'right' }, 6: { halign: 'right' } },
            margin: { left: 40, right: 40 }
        });
        y = doc.lastAutoTable.finalY + 18;
    });

    doc.save('stock_category_report.pdf');
}
</script>

<?php include "include/footer.php"; ?>