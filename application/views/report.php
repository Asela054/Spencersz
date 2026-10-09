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
									<label for="quicksearch">Filter results</label>
									<input type="text" id="quicksearch" class="form-control form-control-sm" placeholder="Product, code, batch or warehouse" disabled>
								</div>
								<div class="sr-actions">
									<button type="submit" id="submitBtnStock" class="btn btn-brand btn-sm px-4" <?php if($addcheck==0){echo 'disabled';} ?>>
										<i class="fas fa-search mr-1"></i>Search
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

<script>
var reportGroups = {};   // { categoryName: [rows] }
var reportTotals = {};

function fmt(n) {
    return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function fmtQty(n) {
    return n.toLocaleString('en-US', { maximumFractionDigits: 3 });
}
function esc(s) {
    return $('<div>').text(s == null ? '' : s).html();
}

$(document).ready(function () {

    $("#searchStock").submit(function (event) {
        event.preventDefault();

        $('#mainTable').html('<div class="sr-empty"><i class="fas fa-circle-notch fa-spin"></i><div>Loading stock...</div></div>');
        $('#summary').empty();

        $.ajax({
            type: "POST",
            url: "<?php echo base_url(); ?>Report/stockReport",
            data: { category: $("#category").val() },
            success: function (result) {
                var dataArray = JSON.parse(result);
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
            error: function () {
                $('#mainTable').html('<div class="sr-empty"><i class="fas fa-exclamation-triangle"></i><div>Could not load the report. Please try again.</div></div>');
            }
        });
    });

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

    $('#quicksearch, #btnPdf, #btnPrint').prop('disabled', !hasData);

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
            '<td><div>' + esc(item.materialname) + '</div><div class="code">' + esc(item.product_code) + '</div></td>' +
            '<td>' + esc(item.batchno) + '</td>' +
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
                    '<th>Product</th><th>Batch no</th><th>Warehouse</th>' +
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

function exportToPDF() {
    var names = Object.keys(reportGroups).sort();
    if (!names.length) { return; }

    var jsPDF = window.jspdf.jsPDF;
    var doc = new jsPDF({ orientation: 'landscape', unit: 'pt', format: 'a4' });
    var pageW = doc.internal.pageSize.getWidth();

    doc.setFontSize(16);
    doc.text('Stock Category Wise Report', 40, 40);
    doc.setFontSize(9);
    doc.setTextColor(110);
    doc.text('Generated: ' + new Date().toLocaleString(), 40, 56);
    doc.text('Total stock value: Rs. ' + fmt(reportTotals.value), pageW - 40, 56, { align: 'right' });
    doc.setTextColor(0);

    var y = 72;
    names.forEach(function (name) {
        var items = reportGroups[name], gq = 0, gt = 0, body = [];
        items.forEach(function (it) {
            var q = parseFloat(it.qty) || 0, p = parseFloat(it.unitprice) || 0;
            gq += q; gt += q * p;
            body.push([
                (it.product_code ? it.product_code + ' - ' : '') + (it.materialname || ''),
                it.batchno || '', it.wh_name || '', fmtQty(q), it.measure_type || '', fmt(p), fmt(q * p)
            ]);
        });

        doc.autoTable({
            startY: y,
            head: [[{ content: name, colSpan: 7, styles: { halign: 'left', fillColor: [163, 15, 45] } }],
                   ['Product', 'Batch no', 'Warehouse', 'Quantity', 'UOM', 'Cost price', 'Total']],
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.24/jspdf.plugin.autotable.min.js"></script>

<?php include "include/footer.php"; ?>