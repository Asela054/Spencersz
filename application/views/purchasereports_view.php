<?php
include "include/header.php";
include "include/topnavbar.php";

$tabs = [
	'purchase' => ['Purchasereport',   'Purchases',        'fa-shopping-basket',   'Purchase Report',          'What was bought, from whom and when, based on approved GRNs.'],
	'postatus' => ['Postatusreport',   'PO status',        'fa-clipboard-list',    'PO Status Report',         'Open, partly received and fully received purchase orders.'],
	'grnret'   => ['Grnreturnreport',  'GRN & returns',    'fa-undo-alt',          'GRN & GRN Return Report',  'Goods received and returned to each supplier.'],
];
$cur = $tabs[$report];
$defBranch = (int)(isset($_SESSION['branch_id']) ? $_SESSION['branch_id'] : 0);
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
							<div class="col-lg-2 col-md-3 col-6 mb-2"><label>From</label><input type="date" id="fFrom" class="form-control" value="<?php echo date('Y-m-01'); ?>"></div>
							<div class="col-lg-2 col-md-3 col-6 mb-2"><label>To</label><input type="date" id="fTo" class="form-control" value="<?php echo date('Y-m-d'); ?>"></div>

							<div class="col-lg-3 col-md-4 mb-2">
								<label>Supplier</label>
								<select id="fSupplier" class="form-control"><option value="0">All suppliers</option>
									<?php foreach ($suppliers as $s) { echo '<option value="' . $s->idtbl_supplier . '">' . htmlspecialchars($s->suppliername) . '</option>'; } ?>
								</select>
							</div>

							<?php if ($report == 'purchase') { ?>
							<div class="col-lg-3 col-md-4 mb-2">
								<label>Product</label>
								<select id="fProduct" class="form-control"><option value="0">All products</option>
									<?php foreach ($products as $p) { echo '<option value="' . $p->idtbl_product . '">' . htmlspecialchars($p->product_code . ' - ' . $p->product_name) . '</option>'; } ?>
								</select>
							</div>
							<div class="col-lg-2 col-md-4 mb-2">
								<label>Group by</label>
								<select id="fGroup" class="form-control"><option value="supplier">Supplier</option><option value="product">Product</option><option value="month">Month</option><option value="detail">Every line</option></select>
							</div>
							<?php } ?>

							<?php if ($report == 'postatus') { ?>
							<div class="col-lg-2 col-md-4 mb-2">
								<label>Order status</label>
								<select id="fPstatus" class="form-control"><option value="">All</option><option value="pending">Pending approval</option><option value="open">Awaiting GRN</option><option value="partial">Partly received</option><option value="received">Fully received</option><option value="outstanding">Not fully received</option></select>
							</div>
							<?php } ?>

							<?php if ($report == 'grnret') { ?>
							<div class="col-lg-2 col-md-4 mb-2">
								<label>View</label>
								<select id="fView" class="form-control"><option value="summary">By supplier</option><option value="docs">Every document</option></select>
							</div>
							<?php } ?>

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
							<button type="button" id="btnToggle" class="btn btn-light border btn-sm"><i class="fas fa-layer-group mr-1"></i> Expand / collapse</button>
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
<style>
#reportBody .sub-row td{background:#fafcff;font-size:12px;color:#475569}
#reportBody .sx-ind{padding-left:34px!important}
.sx-tg{cursor:pointer;color:#64748b}
.sx-tg i{display:inline-block;transition:transform .15s}
.sx-tg:not(.open) i{transform:rotate(-90deg)}
</style>
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
	var SCRIPT = "<?php echo base_url(); ?>scripts/purchasereports.php";
	var R = {};

	/* 16. purchase report */
	R.purchase = function (d) {
		var rows = d.data, t = d.totals, g = d.group, tv = +t.value || 0, head, body, foot, items = {};
		(d.items || []).forEach(function (it) { (items[it.k] = items[it.k] || []).push(it); });
		var chev = function (k) { return '<span class="sx-tg open" data-k="' + esc(k) + '"><i class="fas fa-chevron-down mr-1"></i></span>'; };
		var itemName = function (it) { return esc(it.product_name) + '<div class="sx-code">' + esc(it.product_code) + '</div>'; };

		if (g == 'supplier') {
			head = ['#', 'Supplier / item', '>GRNs', '>Qty', '>Unit price', '>Purchases (ex VAT)', 'Share'];
			body = rows.map(function (r, i) {
				var k = r.idtbl_supplier;
				return '<tr class="grp"><td>' + (i + 1) + '</td><td>' + chev(k) + esc(r.suppliername) + '</td><td class="r">' + num(r.grns) + '</td><td class="r">' + num(r.qty) + '</td><td></td><td class="r">' + money(r.value) + '</td><td style="width:140px">' + bar(tv ? r.value / tv * 100 : 0) + '</td></tr>' +
					(items[k] || []).map(function (it) {
						return '<tr class="sub-row" data-p="' + esc(k) + '"><td></td><td class="sx-ind">' + itemName(it) + '</td><td class="r">' + num(it.grns) + '</td><td class="r">' + num(it.qty) + '</td><td class="r">' + money(it.unitprice) + '</td><td class="r">' + money(it.value) + '</td><td></td></tr>';
					}).join('');
			}).join('');
			foot = '<tr><td colspan="2" class="r">Total</td><td class="r">' + num(t.grns) + '</td><td class="r">' + num(t.qty) + '</td><td></td><td class="r">' + money(tv) + '</td><td></td></tr>';
		} else if (g == 'product') {
			head = ['#', 'Product / price', 'Category', '>Suppliers', '>Qty', '>Unit price', '>Purchases (ex VAT)', 'Last received', 'Share'];
			body = rows.map(function (r, i) {
				var k = r.idtbl_product;
				return '<tr class="grp"><td>' + (i + 1) + '</td><td>' + chev(k) + esc(r.product_name) + '<div class="sx-code">' + esc(r.product_code) + '</div></td><td>' + esc(r.category) + '</td><td class="r">' + num(r.suppliers) + '</td><td class="r">' + num(r.qty) + '</td><td class="r">avg ' + money(r.qty > 0 ? r.value / r.qty : 0) + '</td><td class="r">' + money(r.value) + '</td><td>' + esc(r.last_in) + '</td><td style="width:120px">' + bar(tv ? r.value / tv * 100 : 0) + '</td></tr>' +
					(items[k] || []).map(function (it) {
						return '<tr class="sub-row" data-p="' + esc(k) + '"><td></td><td class="sx-ind">Bought at this price (' + num(it.grns) + ' GRN)</td><td></td><td class="r">' + num(it.suppliers) + '</td><td class="r">' + num(it.qty) + '</td><td class="r">' + money(it.unitprice) + '</td><td class="r">' + money(it.value) + '</td><td>' + esc(it.last_in) + '</td><td></td></tr>';
					}).join('');
			}).join('');
			foot = '<tr><td colspan="4" class="r">Total</td><td class="r">' + num(t.qty) + '</td><td></td><td class="r">' + money(tv) + '</td><td colspan="2"></td></tr>';
		} else if (g == 'month') {
			head = ['Month / item', '>GRNs', '>Qty', '>Unit price', '>Purchases (ex VAT)', 'Share'];
			body = rows.map(function (r) {
				var k = r.ym;
				return '<tr class="grp"><td>' + chev(k) + esc(r.ym) + '</td><td class="r">' + num(r.grns) + '</td><td class="r">' + num(r.qty) + '</td><td></td><td class="r">' + money(r.value) + '</td><td style="width:160px">' + bar(tv ? r.value / tv * 100 : 0) + '</td></tr>' +
					(items[k] || []).map(function (it) {
						return '<tr class="sub-row" data-p="' + esc(k) + '"><td class="sx-ind">' + itemName(it) + '</td><td class="r">' + num(it.grns) + '</td><td class="r">' + num(it.qty) + '</td><td class="r">' + money(it.unitprice) + '</td><td class="r">' + money(it.value) + '</td><td></td></tr>';
					}).join('');
			}).join('');
			foot = '<tr><td class="r">Total</td><td class="r">' + num(t.grns) + '</td><td class="r">' + num(t.qty) + '</td><td></td><td class="r">' + money(tv) + '</td><td></td></tr>';
		} else {
			head = ['Date', 'GRN', 'Supplier invoice', 'Supplier', 'Product', '>Qty', '>Unit cost', '>Value (ex VAT)'];
			body = rows.map(function (r) { return '<tr><td>' + esc(r.grndate) + '</td><td>' + esc(r.grn_no) + '</td><td>' + esc(r.invoicenum) + '</td><td>' + esc(r.suppliername) + '</td><td>' + esc(r.product_name) + '<div class="sx-code">' + esc(r.product_code) + '</div></td><td class="r">' + num(r.qty) + '</td><td class="r">' + money(r.unitprice) + '</td><td class="r">' + money(r.value) + '</td></tr>'; }).join('');
			foot = '<tr><td colspan="5" class="r">Total</td><td class="r">' + num(t.qty) + '</td><td></td><td class="r">' + money(tv) + '</td></tr>';
		}
		return {
			kpis: kpi('fa-file-invoice', num(t.grns), 'Approved GRNs') + kpi('fa-cubes', num(t.qty), 'Units received', 'info') + kpi('fa-coins', 'Rs. ' + money(tv), 'Purchases (ex VAT)') + kpi('fa-truck-loading', num(t.suppliers), 'Suppliers', 'warn'),
			html: rows.length ? table(th(head), body, foot) : empty('No approved GRNs match these filters.')
		};
	};

	/* 17. PO status */
	R.postatus = function (d) {
		var rows = d.data, S = {pending: ['mute', 'Pending approval'], open: ['warn', 'Awaiting GRN'], partial: ['info', 'Partly received'], received: ['ok', 'Fully received']};
		var cnt = {pending: 0, open: 0, partial: 0, received: 0};
		var body = rows.map(function (r, i) {
			cnt[r.pstatus]++;
			var out = Math.max(0, r.ordered - r.received), pct = r.ordered > 0 ? r.received / r.ordered * 100 : 0, s = S[r.pstatus];
			var age = r.pstatus == 'received' ? '' : (+r.age_days > 30 ? badge('bad', r.age_days + ' d') : r.age_days + ' d');
			return '<tr><td>' + (i + 1) + '</td><td><b>' + esc(r.porder_no) + '</b></td><td>' + esc(r.orderdate) + '</td><td>' + esc(r.suppliername) + '</td><td>' + esc(r.branch) + '</td>' +
				'<td class="r">' + num(r.ordered) + '</td><td class="r">' + num(r.received) + '</td><td class="r"><b>' + num(out) + '</b></td><td style="width:100px">' + bar(pct, r.pstatus == 'received' ? '' : 'info') + '</td>' +
				'<td class="r">' + money(r.nettotal) + '</td><td class="r">' + age + '</td><td>' + badge(s[0], s[1]) + '</td></tr>';
		}).join('');
		var outQty = rows.reduce(function (a, r) { return a + Math.max(0, r.ordered - r.received); }, 0);
		return {
			kpis: kpi('fa-clipboard-list', rows.length, 'Purchase orders') + kpi('fa-hourglass-start', cnt.open, 'Awaiting GRN', cnt.open ? 'warn' : '') + kpi('fa-adjust', cnt.partial, 'Partly received', 'info') +
				kpi('fa-check-circle', cnt.received, 'Fully received') + kpi('fa-boxes', num(outQty), 'Units still to arrive', outQty ? 'warn' : ''),
			html: rows.length ? table(th(['#', 'PO', 'Date', 'Supplier', 'Branch', '>Ordered', '>Received', '>Outstanding', 'Progress', '>PO value', '>Age', 'Status']), body,
				'<tr><td colspan="5" class="r">Total</td><td class="r">' + num(sum(rows, 'ordered')) + '</td><td class="r">' + num(sum(rows, 'received')) + '</td><td class="r">' + num(outQty) + '</td><td></td><td class="r">' + money(sum(rows, 'nettotal')) + '</td><td colspan="2"></td></tr>') : empty('No purchase orders match these filters.')
		};
	};

	/* 18. GRN & GRN return */
	R.grnret = function (d) {
		var rows = d.data, gt, rt;
		if (d.view == 'docs') {
			var body = rows.map(function (r) {
				var ret = r.doctype == 'GRN Return';
				return '<tr><td>' + esc(r.d) + '</td><td>' + badge(ret ? 'bad' : 'ok', r.doctype) + '</td><td><b>' + esc(r.docno) + '</b></td><td>' + esc(r.suppliername) + '</td><td>' + esc(r.refno) + '</td>' +
					'<td class="r">' + (ret ? '' : money(r.amount)) + '</td><td class="r">' + (ret ? money(r.amount) : '') + '</td><td>' + esc(r.note) + '</td></tr>';
			}).join('');
			var g = 0, x = 0; rows.forEach(function (r) { if (r.doctype == 'GRN Return') x += +r.amount; else g += +r.amount; });
			return {
				kpis: kpi('fa-arrow-down', 'Rs. ' + money(g), 'Goods received (incl. VAT)') + kpi('fa-undo-alt', 'Rs. ' + money(x), 'Returned to suppliers', 'bad') + kpi('fa-balance-scale', 'Rs. ' + money(g - x), 'Net received', 'info') + kpi('fa-percentage', (g ? (x / g * 100).toFixed(1) : '0.0') + '%', 'Return rate', x ? 'warn' : ''),
				html: rows.length ? table(th(['Date', 'Type', 'Document', 'Supplier', 'Against / supplier invoice', '>Received', '>Returned', 'Note']), body,
					'<tr><td colspan="5" class="r">Total</td><td class="r">' + money(g) + '</td><td class="r">' + money(x) + '</td><td></td></tr>') : empty('No GRNs or returns in this period.')
			};
		}
		gt = sum(rows, 'grn_total'); rt = sum(rows, 'ret_total');
		var b2 = rows.map(function (r, i) {
			var rate = +r.grn_total ? r.ret_total / r.grn_total * 100 : 0;
			return '<tr><td>' + (i + 1) + '</td><td>' + esc(r.suppliername) + '</td><td class="r">' + num(r.grns) + '</td><td class="r">' + money(r.grn_total) + '</td><td class="r">' + num(r.rets) + '</td><td class="r">' + money(r.ret_total) + '</td>' +
				'<td class="r"><b>' + money(r.grn_total - r.ret_total) + '</b></td><td class="r">' + (rate > 10 ? badge('bad', rate.toFixed(1) + '%') : (rate > 0 ? badge('warn', rate.toFixed(1) + '%') : '0%')) + '</td></tr>';
		}).join('');
		return {
			kpis: kpi('fa-file-invoice', num(sum(rows, 'grns')), 'GRNs') + kpi('fa-arrow-down', 'Rs. ' + money(gt), 'Goods received (incl. VAT)') + kpi('fa-undo-alt', 'Rs. ' + money(rt), 'Returned to suppliers', 'bad') + kpi('fa-percentage', (gt ? (rt / gt * 100).toFixed(1) : '0.0') + '%', 'Return rate', rt ? 'warn' : ''),
			html: rows.length ? table(th(['#', 'Supplier', '>GRNs', '>Received', '>Returns', '>Returned', '>Net received', '>Return rate']), b2,
				'<tr><td colspan="2" class="r">Total</td><td class="r">' + num(sum(rows, 'grns')) + '</td><td class="r">' + money(gt) + '</td><td class="r">' + num(sum(rows, 'rets')) + '</td><td class="r">' + money(rt) + '</td><td class="r">' + money(gt - rt) + '</td><td></td></tr>') : empty('No GRNs or returns in this period.')
		};
	};

	/* ---------- Run report ---------- */
	function run() {
		$('#reportBody').html('<div class="sx-load"><i class="fas fa-circle-notch fa-spin mr-2"></i>Loading report...</div>');
		$.ajax({url: SCRIPT, type: 'POST', dataType: 'json',
			data: {report: REPORT, from: $('#fFrom').val(), to: $('#fTo').val(), supplier_id: $('#fSupplier').val(), product_id: $('#fProduct').val(), branch_id: $('#fBranch').val(),
				group: $('#fGroup').val(), pstatus: $('#fPstatus').val(), view: $('#fView').val()},
			success: function (res) {
				if (res && res.error) { $('#reportBody').html('<div class="sx-load text-danger">' + esc(res.error) + '</div>'); return; }
				var o = R[REPORT](res);
				$('#kpis').html(o.kpis); $('#reportBody').html(o.html);
				$('#hint').text($('#fFrom').val() + ' to ' + $('#fTo').val() + ' - generated ' + new Date().toLocaleString());
			}, error: ajaxFail});
	}
	$('#btnRun').on('click', run);
	$('#fFrom,#fTo,#fSupplier,#fProduct,#fBranch,#fGroup,#fPstatus,#fView').on('change', run);

		/* expand / collapse item rows */
	$(document).on('click', '.sx-tg', function () {
		var k = String($(this).attr('data-k')), open = $(this).hasClass('open');
		$(this).toggleClass('open', !open);
		$('#reportBody tr.sub-row').filter(function () { return $(this).attr('data-p') === k; }).toggle(!open);
	});
	$('#btnToggle').on('click', function () {
		var anyOpen = $('#reportBody .sx-tg.open').length > 0;
		$('#reportBody .sx-tg').toggleClass('open', !anyOpen);
		$('#reportBody tr.sub-row').toggle(!anyOpen);
	});

	/* ---------- Export helpers ---------- */
	function getTable() {
		var t = document.querySelector('#reportBody table');
		if (!t) { alert('Run the report first. There is no data to export.'); return null; }
		return t;
	}
	function filterText() {
		return $('#fFrom').val() + ' to ' + $('#fTo').val() +
			' | Supplier: ' + $('#fSupplier option:selected').text() +
			' | Branch: ' + $('#fBranch option:selected').text();
	}
	function fileName(ext) {
		return TITLE.replace(/[^a-z0-9]+/gi, '_') + '_' + $('#fFrom').val() + '_' + $('#fTo').val() + '.' + ext;
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