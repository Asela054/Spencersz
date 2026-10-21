<?php
include "include/header.php";
include "include/topnavbar.php";

$tabs = [
	'reservation' => ['Reservationreport', 'Reservations', 'fa-bookmark', 'Item Reservation Report', 'Open, converted and cancelled reservations with the linked invoice, and stock that is reserved and not available for sale.'],
];
$cur = $tabs[$report];
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
							<div class="page-header-icon"><i class="fas <?php echo $cur[2]; ?>"></i></div>
							<span><?php echo $cur[3]; ?></span>
						</h1>
						<div class="page-header-subtitle text-muted"><?php echo $cur[4]; ?></div>
					</div>
				</div>
			</div>

			<div class="container-fluid mt-3 px-3 sx">
				<div class="sx-tabs">
					<?php foreach ($tabs as $k => $t) {
						if ($k != $report && !in_array($t[0], $permissionallowed)) continue; ?>
						<a class="sx-tab <?php echo $k == $report ? 'on' : ''; ?>" href="<?php echo base_url() . $t[0]; ?>"><i class="fas <?php echo $t[2]; ?>"></i><?php echo $t[1]; ?></a>
					<?php } ?>
				</div>

				<div class="sx-panel">
					<div class="sx-filter">
						<div class="row align-items-end">
							<div class="col-lg-2 col-md-4 mb-2">
								<label>View</label>
								<select id="fView" class="form-control"><option value="list">Reservations</option><option value="stock">Reserved stock</option></select>
							</div>
							<div class="col-lg-2 col-md-3 col-6 mb-2 f-list"><label>From</label><input type="date" id="fFrom" class="form-control" value="<?php echo date('Y-m-01'); ?>"></div>
							<div class="col-lg-2 col-md-3 col-6 mb-2 f-list"><label>To</label><input type="date" id="fTo" class="form-control" value="<?php echo date('Y-m-d'); ?>"></div>

							<div class="col-lg-3 col-md-4 mb-2 f-list">
								<label>Customer</label>
								<select id="fCustomer" class="form-control"><option value="0">All customers</option>
									<?php foreach ($customers as $c) { echo '<option value="' . $c->idtbl_customer . '">' . htmlspecialchars($c->customer) . '</option>'; } ?>
								</select>
							</div>

							<div class="col-lg-2 col-md-4 mb-2 f-list">
								<label>Status</label>
								<select id="fRstatus" class="form-control"><option value="">All</option><option value="open">Open</option><option value="expired">Open - expired</option><option value="converted">Converted</option><option value="cancelled">Cancelled</option></select>
							</div>

							<div class="col-lg-3 col-md-4 mb-2">
								<label>Product</label>
								<select id="fProduct" class="form-control"><option value="0">All products</option>
									<?php foreach ($products as $p) { echo '<option value="' . $p->idtbl_product . '">' . htmlspecialchars($p->product_code . ' - ' . $p->product_name) . '</option>'; } ?>
								</select>
							</div>

							<div class="col-lg-2 col-md-4 mb-2">
								<label>Branch</label>
								<select id="fBranch" class="form-control"><option value="0">All branches</option>
									<?php foreach ($branches as $b) { echo '<option value="' . $b->idtbl_company_branch . '">' . htmlspecialchars($b->branch) . '</option>'; } ?>
								</select>
							</div>

							<div class="col mb-2 text-md-right">
								<button type="button" id="btnRun" class="btn sx-go"><i class="fas fa-sync-alt mr-1"></i> Run</button>
							</div>
						</div>
					</div>

					<div class="sx-kpis" id="kpis"></div>
					<div class="sx-tools">
						<span class="hint" id="hint"></span>
						<span>
							<button type="button" id="btnCsv" class="btn btn-light border btn-sm"><i class="fas fa-file-csv text-success mr-1"></i> CSV</button>
							<button type="button" id="btnPdf" class="btn btn-light border btn-sm"><i class="fas fa-file-pdf text-danger mr-1"></i> PDF</button>
							<button type="button" id="btnPrint" class="btn btn-light border btn-sm"><i class="fas fa-print mr-1"></i> Print</button>
						</span>
					</div>
					<div class="sx-wrap"><div id="reportBody"><div class="sx-load">Loading report...</div></div></div>
				</div>
			</div>
		</main>
		<?php include "include/footerbar.php"; ?>
	</div>
</div>

<?php include "include/footerscripts.php"; ?>
<script>var TITLE = '<?php echo addslashes($cur[3]); ?>';</script>
<script>
/* ---------- Helpers ---------- */
function ajaxFail(xhr, status, err) {
	var msg = 'Request failed (' + xhr.status + ')';
	try { var j = JSON.parse(xhr.responseText); if (j.error) msg = j.error; } catch (e) {
		msg += '<pre style="text-align:left;white-space:pre-wrap">' + (xhr.responseText || err || '').toString().substring(0, 1500).replace(/</g, '&lt;') + '</pre>';
	}
	$('#reportBody').html('<div class="sx-load text-danger">' + msg + '</div>');
	console.error(xhr.status, xhr.responseText);
}
function esc(s) { return $('<div>').text(s == null ? '' : s).html(); }
function num(n) { return (+n || 0).toLocaleString('en-US', {maximumFractionDigits: 2}); }
function money(n) { return (+n || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
function sum(rows, k) { return rows.reduce(function (a, r) { return a + (+r[k] || 0); }, 0); }
function bar(p, cls) { p = Math.max(0, Math.min(100, p)); return '<div class="sx-bar ' + (cls || '') + '"><span style="width:' + p + '%"></span></div><small>' + p.toFixed(0) + '%</small>'; }
function badge(c, t) { return '<span class="sx-badge ' + c + '">' + esc(t) + '</span>'; }
function kpi(icon, val, label, cls) { return '<div class="sx-kpi ' + (cls || '') + '"><i class="fas ' + icon + '"></i><div><b>' + val + '</b><span>' + label + '</span></div></div>'; }
function th(cols) { return '<tr>' + cols.map(function (c) { return c.charAt(0) == '>' ? '<th class="r">' + c.substr(1) + '</th>' : '<th>' + c + '</th>'; }).join('') + '</tr>'; }
function table(head, body, foot) { return '<table class="table sx-table"><thead>' + head + '</thead><tbody>' + body + '</tbody>' + (foot ? '<tfoot>' + foot + '</tfoot>' : '') + '</table>'; }
function empty(m) { return '<div class="sx-load">' + m + '</div>'; }

$(function () {
	var REPORT = '<?php echo $report; ?>';
	var SCRIPT = "<?php echo base_url(); ?>scripts/reservationreports.php";
	var R = {};

	/* 20. Item reservation report */
	R.reservation = function (d) {
		var rows = d.data;

		/* ----- reserved stock (open reservations) ----- */
		if (d.view == 'stock') {
			var body = rows.map(function (r, i) {
				var avail = (+r.in_stock) - (+r.reserved);
				var availCell = avail < 0 ? badge('bad', num(avail)) : '<b>' + num(avail) + '</b>';
				return '<tr><td>' + (i + 1) + '</td><td>' + esc(r.product_name) + '<div class="sx-code">' + esc(r.product_code) + '</div></td><td>' + esc(r.category) + '</td>' +
					'<td class="r">' + num(r.reservations) + '</td><td class="r"><b>' + num(r.reserved) + '</b></td><td class="r">' + num(r.in_stock) + '</td><td class="r">' + availCell + '</td>' +
					'<td>' + esc(r.next_expiry) + '</td><td class="r">' + money(r.value) + '</td></tr>';
			}).join('');
			var short = rows.filter(function (r) { return (+r.in_stock) - (+r.reserved) < 0; }).length;
			return {
				kpis: kpi('fa-bookmark', num(rows.length), 'Products reserved') + kpi('fa-lock', num(sum(rows, 'reserved')), 'Units held back from sale', 'warn') +
					kpi('fa-coins', 'Rs. ' + money(sum(rows, 'value')), 'Reserved value (incl. VAT)', 'info') + kpi('fa-exclamation-triangle', short, 'Over-reserved products', short ? 'bad' : ''),
				html: rows.length ? table(th(['#', 'Product', 'Category', '>Open reservations', '>Reserved qty', '>In stock', '>Available for sale', 'Earliest expiry', '>Reserved value']), body,
					'<tr><td colspan="3" class="r">Total</td><td class="r">' + num(sum(rows, 'reservations')) + '</td><td class="r">' + num(sum(rows, 'reserved')) + '</td><td class="r">' + num(sum(rows, 'in_stock')) + '</td><td></td><td></td><td class="r">' + money(sum(rows, 'value')) + '</td></tr>') : empty('No open reservations are holding stock.')
			};
		}

		/* ----- reservation list ----- */
		var S = {open: ['info', 'Open'], expired: ['bad', 'Open - expired'], converted: ['ok', 'Converted'], cancelled: ['mute', 'Cancelled']};
		var cnt = {open: 0, expired: 0, converted: 0, cancelled: 0}, held = 0, adv = 0, tq = 0, tt = 0, ta = 0;
		var b = rows.map(function (r, i) {
			cnt[r.rstate]++;
			var live = (r.rstate == 'open' || r.rstate == 'expired');
			var bal = (+r.total_amount) - (+r.advance_amount);
			if (live) { held += +r.total_qty; adv += +r.advance_amount; }
			tq += +r.total_qty; tt += +r.total_amount; ta += +r.advance_amount;
			var exp = esc(r.expiry_date);
			if (r.rstate == 'open')    exp += ' <small class="text-muted">(' + (+r.days_left === 0 ? 'today' : r.days_left + ' d left') + ')</small>';
			if (r.rstate == 'expired') exp += ' ' + badge('bad', Math.abs(r.days_left) + ' d overdue');
			var inv = r.invoice_no ? '<b>' + esc(r.invoice_no) + '</b><div class="sx-code">' + esc(r.invoice_date) + '</div>' : (r.rstate == 'converted' ? '<span class="text-muted">not linked</span>' : '');
			var s = S[r.rstate];
			return '<tr><td>' + (i + 1) + '</td><td><b>' + esc(r.reservation_no) + '</b></td><td>' + esc(r.reservation_date) + '</td><td>' + exp + '</td><td>' + esc(r.customer) + '</td><td>' + esc(r.branch) + '</td>' +
				'<td>' + esc(r.items) + '</td><td class="r">' + num(r.total_qty) + '</td><td class="r">' + money(r.total_amount) + '</td><td class="r">' + money(r.advance_amount) + '</td>' +
				'<td class="r">' + (live ? money(bal) : '') + '</td><td>' + inv + '</td><td>' + badge(s[0], s[1]) + '</td></tr>';
		}).join('');
		return {
			kpis: kpi('fa-bookmark', rows.length, 'Reservations') + kpi('fa-hourglass-half', cnt.open + cnt.expired, 'Open', (cnt.open + cnt.expired) ? 'info' : '') +
				kpi('fa-exclamation-circle', cnt.expired, 'Open but expired', cnt.expired ? 'bad' : '') + kpi('fa-check-circle', cnt.converted, 'Converted to invoice') +
				kpi('fa-ban', cnt.cancelled, 'Cancelled', cnt.cancelled ? 'warn' : '') + kpi('fa-lock', num(held), 'Units held (not for sale)', held ? 'warn' : '') +
				kpi('fa-hand-holding-usd', 'Rs. ' + money(adv), 'Advance held on open', 'info'),
			html: rows.length ? table(th(['#', 'Reservation', 'Date', 'Expiry', 'Customer', 'Branch', 'Items', '>Qty', '>Total (incl. VAT)', '>Advance', '>Balance due', 'Invoice', 'Status']), b,
				'<tr><td colspan="7" class="r">Total</td><td class="r">' + num(tq) + '</td><td class="r">' + money(tt) + '</td><td class="r">' + money(ta) + '</td><td colspan="3"></td></tr>') : empty('No reservations match these filters.')
		};
	};

	/* ---------- Run report ---------- */
	function toggleView() {
		var stock = $('#fView').val() == 'stock';
		$('.f-list').css({opacity: stock ? .4 : 1}).find('input,select').prop('disabled', stock);
	}
	function run() {
		toggleView();
		$('#reportBody').html('<div class="sx-load"><i class="fas fa-circle-notch fa-spin mr-2"></i>Loading report...</div>');
		var stock = $('#fView').val() == 'stock';
		$.ajax({url: SCRIPT, type: 'POST', dataType: 'json',
			data: {report: REPORT, view: $('#fView').val(), from: $('#fFrom').val(), to: $('#fTo').val(), customer_id: $('#fCustomer').val(),
				rstatus: $('#fRstatus').val(), product_id: $('#fProduct').val(), branch_id: $('#fBranch').val()},
			success: function (res) {
				if (res && res.error) { $('#reportBody').html('<div class="sx-load text-danger">' + esc(res.error) + '</div>'); return; }
				var o = R[REPORT](res);
				$('#kpis').html(o.kpis); $('#reportBody').html(o.html);
				$('#hint').text(stock ? 'Open reservations as of today - generated ' + new Date().toLocaleString()
					: $('#fFrom').val() + ' to ' + $('#fTo').val() + ' - generated ' + new Date().toLocaleString());
			}, error: ajaxFail});
	}
	$('#btnRun').on('click', run);
	$('#fView,#fFrom,#fTo,#fCustomer,#fRstatus,#fProduct,#fBranch').on('change', run);

	/* ---------- Export helpers ---------- */
	function getTable() {
		var t = document.querySelector('#reportBody table');
		if (!t) { alert('Run the report first. There is no data to export.'); return null; }
		return t;
	}
	function viewName() { return $('#fView').val() == 'stock' ? 'Reserved stock' : 'Reservations'; }
	function filterText() {
		var s = 'View: ' + viewName();
		if ($('#fView').val() != 'stock') s += ' | ' + $('#fFrom').val() + ' to ' + $('#fTo').val() + ' | Customer: ' + $('#fCustomer option:selected').text() + ' | Status: ' + $('#fRstatus option:selected').text();
		return s + ' | Branch: ' + $('#fBranch option:selected').text();
	}
	function fileName(ext) {
		return TITLE.replace(/[^a-z0-9]+/gi, '_') + '_' + viewName().replace(/\s+/g, '_') + '_' + new Date().toISOString().substring(0, 10) + '.' + ext;
	}

	/* CSV */
	$('#btnCsv').on('click', function () {
		var t = getTable(); if (!t) return;
		var lines = [];
		$(t).find('tr').each(function () {
			var cells = [];
			$(this).children('th,td').each(function () {
				var txt = $(this).text().replace(/\s+/g, ' ').trim().replace(/"/g, '""');
				cells.push('"' + txt + '"');
				var span = +$(this).attr('colspan') || 1;
				for (var i = 1; i < span; i++) cells.push('""');
			});
			lines.push(cells.join(','));
		});
		var blob = new Blob(['\ufeff' + lines.join('\r\n')], {type: 'text/csv;charset=utf-8;'});
		var a = document.createElement('a');
		a.href = URL.createObjectURL(blob);
		a.download = fileName('csv');
		document.body.appendChild(a); a.click(); document.body.removeChild(a);
	});

	/* Print */
	function printHtml() {
		var t = getTable(); if (!t) return;
		var f = document.createElement('iframe');
		f.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0';
		document.body.appendChild(f);
		var d = f.contentWindow.document;
		d.open();
		d.write('<html><head><title>' + TITLE + '</title><style>' +
			'body{font-family:Arial,sans-serif;font-size:11px;color:#000;margin:15px}' +
			'h2{margin:0 0 4px}p{margin:0 0 10px;color:#555}' +
			'table{width:100%;border-collapse:collapse}' +
			'th,td{border:1px solid #999;padding:4px 6px;text-align:left}' +
			'th{background:#eee}.r{text-align:right}' +
			'tfoot td{font-weight:bold;background:#f5f5f5}' +
			'.sx-code{font-size:9px;color:#666}' +
			'@page{size:landscape;margin:10mm}' +
			'</style></head><body><h2>Spencersz (Pvt) Ltd - ' + TITLE + '</h2><p>' + esc(filterText()) +
			'<br>Generated: ' + new Date().toLocaleString() + '</p>' + t.outerHTML + '</body></html>');
		d.close();
		var done = false;
		function go() {
			if (done) return; done = true;
			f.contentWindow.focus(); f.contentWindow.print();
			setTimeout(function () { if (f.parentNode) document.body.removeChild(f); }, 1500);
		}
		f.onload = go;
		setTimeout(go, 300);
	}
	$('#btnPrint').on('click', printHtml);

	/* PDF (jsPDF + autotable, loaded on first click) */
	function loadScript(src, cb) {
		var s = document.createElement('script');
		s.src = src; s.onload = cb;
		s.onerror = function () { alert('Could not load the PDF library. Opening the print dialog instead; choose "Save as PDF".'); printHtml(); };
		document.head.appendChild(s);
	}
	function makePdf() {
		var t = getTable(); if (!t) return;
		var doc = new window.jspdf.jsPDF({orientation: 'landscape', unit: 'pt', format: 'a4'});
		doc.setFontSize(14); doc.text('Spencersz (Pvt) Ltd - ' + TITLE, 30, 32);
		doc.setFontSize(9);  doc.text(filterText(), 30, 47);
		doc.text('Generated: ' + new Date().toLocaleString(), 30, 59);
		doc.autoTable({
			html: t, startY: 70,
			styles: {fontSize: 8, cellPadding: 3},
			headStyles: {fillColor: [60, 60, 60]},
			footStyles: {fillColor: [235, 235, 235], textColor: 0, fontStyle: 'bold'}
		});
		doc.save(fileName('pdf'));
	}
	$('#btnPdf').on('click', function () {
		if (!getTable()) return;
		if (window.jspdf && window.jspdf.jsPDF && window.jspdf.jsPDF.API.autoTable) return makePdf();
		loadScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', function () {
			loadScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js', makePdf);
		});
	});

	run();
});
</script>

<?php include "include/footer.php"; ?>