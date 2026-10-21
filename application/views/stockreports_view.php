<?php
include "include/header.php";
include "include/topnavbar.php";

$tabs = [
	'current'   => ['Stocklistreport',      'Current stock', 'fa-boxes',               'Current Stock Report',           'Quantity and value at cost for every product, by branch or warehouse.'],
	'card'      => ['Stockcard',            'Stock card',    'fa-stream',              'Stock Movement / Stock Card',   'Every receipt, sale, return and transfer for one product.'],
	'reorder'   => ['Reorderstockreport',   'Low stock',     'fa-exclamation-triangle','Low Stock & Reorder Report',    'Products at or below their reorder level.'],
	'transfer'  => ['Stocktransferreport',  'Transfers',     'fa-exchange-alt',        'Stock Transfer Report',         'Movements between showrooms and warehouses, with live status.'],
	'dead'      => ['Deadstockreport',      'Slow moving',   'fa-hourglass-half',      'Dead / Slow-Moving Stock',      'Stock on hand that has not sold for a set number of days.'],
	'valuation' => ['Stockvaluationreport', 'Valuation',     'fa-coins',               'Stock Valuation by Category & Brand', 'Stock value at cost grouped by category and brand.'],
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

				<!-- Report switcher -->
				<div class="sx-tabs">
					<?php foreach ($tabs as $k => $t) {
						if ($k != $report && !in_array($t[0], $permissionallowed)) continue; ?>
						<a class="sx-tab <?php echo $k == $report ? 'on' : ''; ?>" href="<?php echo base_url() . $t[0]; ?>">
							<i class="fas <?php echo $t[2]; ?>"></i><?php echo $t[1]; ?>
						</a>
					<?php } ?>
				</div>

				<div class="sx-panel">

					<!-- Filters -->
					<div class="sx-filter">
						<div class="row align-items-end">
							<div class="col-lg-3 col-md-4 mb-2">
								<label>Branch / warehouse</label>
								<select id="fBranch" class="form-control">
									<option value="0">All branches &amp; warehouses</option>
									<?php
									$grp = [1 => 'Showrooms', 2 => 'Warehouses'];
									foreach ($grp as $type => $label) {
										echo '<optgroup label="' . $label . '">';
										foreach ($branches as $b) {
											if ((int)$b->type !== $type) continue;
											echo '<option value="' . $b->idtbl_company_branch . '"' . ($defBranch == $b->idtbl_company_branch ? ' selected' : '') . '>' . htmlspecialchars($b->branch) . '</option>';
										}
										echo '</optgroup>';
									} ?>
								</select>
							</div>

							<?php if ($report == 'card') { ?>
							<div class="col-lg-3 col-md-4 mb-2">
								<label>Product</label>
								<select id="fProduct" class="form-control">
									<option value="">Select a product</option>
									<?php foreach ($products as $p) { echo '<option value="' . $p->idtbl_product . '">' . htmlspecialchars($p->product_code . ' - ' . $p->product_name) . '</option>'; } ?>
								</select>
							</div>
							<?php } ?>

							<?php if ($report == 'card' || $report == 'transfer') { ?>
							<div class="col-lg-2 col-md-2 col-6 mb-2"><label>From</label><input type="date" id="fFrom" class="form-control" value="<?php echo date('Y-m-01'); ?>"></div>
							<div class="col-lg-2 col-md-2 col-6 mb-2"><label>To</label><input type="date" id="fTo" class="form-control" value="<?php echo date('Y-m-d'); ?>"></div>
							<?php } ?>

							<?php if ($report == 'transfer') { ?>
							<div class="col-lg-2 col-md-3 mb-2">
								<label>Transfer type</label>
								<select id="fType" class="form-control">
									<option value="0">All types</option><option value="1">Showroom to showroom</option><option value="2">Warehouse to warehouse</option>
									<option value="3">Showroom to warehouse</option><option value="4">Warehouse to showroom</option>
								</select>
							</div>
							<div class="col-lg-2 col-md-3 mb-2">
								<label>Status</label>
								<select id="fStatus" class="form-control">
									<option value="0">All statuses</option><option value="1">Created</option><option value="2">Loaded</option>
									<option value="3">Dispatched</option><option value="4">Lorry arrived</option><option value="5">Unloaded</option><option value="6">Added to stock</option>
								</select>
							</div>
							<?php } ?>

							<?php if ($report == 'dead') { ?>
							<div class="col-lg-2 col-md-3 mb-2"><label>No sales for at least (days)</label><input type="number" min="0" id="fDays" class="form-control" value="90"></div>
							<?php } ?>

							<?php if ($report == 'current') { ?>
							<div class="col-lg-3 col-md-4 mb-2">
								<div class="custom-control custom-checkbox mt-2">
									<input type="checkbox" class="custom-control-input" id="fSplit">
									<label class="custom-control-label" for="fSplit" style="margin:0;font-size:13px;color:#1e293b">Show each branch on its own line</label>
								</div>
							</div>
							<?php } ?>

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
<script>
$(function () {
	var REPORT = '<?php echo $report; ?>';
	var TITLE  = '<?php echo addslashes($cur[3]); ?>';
	var SCRIPT = "<?php echo base_url(); ?>scripts/stockreports.php";

	/* ---------- helpers ---------- */
	var esc = function (s) { return $('<div>').text(s == null ? '' : s).html(); };
	var num = function (n) { n = parseFloat(n) || 0; return n.toLocaleString('en-US', {maximumFractionDigits: 2}); };
	var money = function (n) { return (parseFloat(n) || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}); };
	var badge = function (cls, t) { return '<span class="sx-b ' + cls + '">' + esc(t) + '</span>'; };
	var bar = function (pct, cls) { pct = Math.max(0, Math.min(100, pct)); return '<div class="sx-bar ' + (cls || '') + '"><i style="width:' + pct + '%"></i></div>'; };
	var kpi = function (icon, val, label, cls) { return '<div class="sx-kpi ' + (cls || '') + '"><i class="fas ' + icon + '"></i><div><b>' + val + '</b><span>' + label + '</span></div></div>'; };
	var empty = function (msg, icon) { return '<div class="sx-empty"><i class="fas ' + (icon || 'fa-inbox') + '"></i>' + msg + '</div>'; };
	var table = function (head, body, foot) {
		return '<div class="sx-scroll"><table class="sx-t" id="rpt"><thead><tr>' + head + '</tr></thead><tbody>' + body + '</tbody>' +
			(foot ? '<tfoot>' + foot + '</tfoot>' : '') + '</table></div>';
	};
	var th = function (cols) { return cols.map(function (c) { var a = c.charAt(0) == '>' ? ' class="r"' : (c.charAt(0) == '^' ? ' class="c"' : ''); return '<th' + a + '>' + esc(c.replace(/^[>^]/, '')) + '</th>'; }).join(''); };
	var prod = function (r) { return esc(r.product_name) + '<div class="sx-code">' + esc(r.product_code) + '</div>'; };
	var sum = function (rows, k) { return rows.reduce(function (a, r) { return a + (parseFloat(r[k]) || 0); }, 0); };

	/* ---------- renderers ---------- */
	var R = {};

	R.current = function (d) {
		var rows = d.data, tq = sum(rows, 'qty'), tv = sum(rows, 'value'), low = 0, seen = {};
		rows.forEach(function (r) { seen[r.idtbl_product] = 1; if (+r.reorder_level > 0 && +r.qty <= +r.reorder_level) low++; });
		var body = rows.map(function (r, i) {
			var flag = (+r.reorder_level > 0 && +r.qty <= +r.reorder_level) ? ' ' + badge('warn', 'Low') : '';
			return '<tr><td>' + (i + 1) + '</td><td>' + prod(r) + flag + '</td><td>' + esc(r.category) + '</td><td>' + esc(r.brand) + '</td>' +
				(d.split ? '<td>' + esc(r.branch) + '</td>' : '') +
				'<td class="r">' + num(r.qty) + '</td><td class="c">' + esc(r.unit) + '</td>' +
				'<td class="r">' + money(r.qty > 0 ? r.value / r.qty : 0) + '</td><td class="r">' + money(r.value) + '</td>' +
				'<td style="width:110px">' + bar(tv ? r.value / tv * 100 : 0) + '</td></tr>';
		}).join('');
		var cols = ['#', 'Product', 'Category', 'Brand'].concat(d.split ? ['Branch'] : [], ['>Qty', '^UOM', '>Avg cost', '>Stock value', 'Share']);
		var span = cols.length - 5;
		return {
			kpis: kpi('fa-box', Object.keys(seen).length, 'Products in stock') + kpi('fa-cubes', num(tq), 'Units on hand', 'info') +
				kpi('fa-coins', 'Rs. ' + money(tv), 'Stock value at cost') + kpi('fa-exclamation-circle', low, 'At or below reorder level', low ? 'warn' : ''),
			html: rows.length ? table(th(cols), body, '<tr><td colspan="' + span + '" class="r">Grand total</td><td class="r">' + num(tq) + '</td><td></td><td></td><td class="r">' + money(tv) + '</td><td></td></tr>') : empty('No stock found for this selection.')
		};
	};

	R.card = function (d) {
		if (!d.product) return {kpis: '', html: empty('Choose a product and a date range, then run the report.', 'fa-search')};
		var bal = +d.opening, tin = 0, tout = 0;
		var tone = {'GRN': 'ok', 'Sale Return': 'ok', 'Transfer In': 'info', 'Transfer Out': 'warn', 'Sale': 'bad', 'GRN Return': 'bad'};
		var body = '<tr class="grp"><td colspan="6">Opening balance</td><td class="r">' + num(bal) + '</td></tr>' + d.data.map(function (r) {
			tin += +r.qin; tout += +r.qout; bal += (+r.qin) - (+r.qout);
			return '<tr><td>' + esc(r.d) + '</td><td>' + badge(tone[r.t] || 'mute', r.t) + '</td><td>' + esc(r.ref) + '</td><td>' + esc(r.branch) + '</td>' +
				'<td class="r">' + (+r.qin ? num(r.qin) : '') + '</td><td class="r">' + (+r.qout ? num(r.qout) : '') + '</td><td class="r"><b>' + num(bal) + '</b></td></tr>';
		}).join('');
		return {
			kpis: kpi('fa-flag-checkered', num(d.opening), 'Opening balance', 'info') + kpi('fa-arrow-down', num(tin), 'Total in') +
				kpi('fa-arrow-up', num(tout), 'Total out', 'warn') + kpi('fa-balance-scale', num(bal), 'Closing balance'),
			html: table(th(['Date', 'Type', 'Reference', 'Branch', '>In', '>Out', '>Balance']), body,
				'<tr><td colspan="4" class="r">' + esc(d.product.product_name) + ' - totals</td><td class="r">' + num(tin) + '</td><td class="r">' + num(tout) + '</td><td class="r">' + num(bal) + '</td></tr>') +
				(d.data.length ? '' : empty('No movements in this period.'))
		};
	};

	R.reorder = function (d) {
		var rows = d.data, out = 0, crit = 0, short = 0, cost = 0;
		var body = rows.map(function (r, i) {
			var q = +r.qty, lv = +r.reorder_level, gap = Math.max(0, lv - q), st, cls;
			if (q <= 0) { st = 'Out of stock'; cls = 'bad'; out++; } else if (lv && q <= lv / 2) { st = 'Critical'; cls = 'bad'; crit++; } else { st = 'Low'; cls = 'warn'; }
			short += gap; cost += gap * (+r.cost_price);
			return '<tr><td>' + (i + 1) + '</td><td>' + prod(r) + '</td><td>' + esc(r.category) + '</td><td>' + esc(r.brand) + '</td>' +
				'<td class="r">' + num(q) + '</td><td class="r">' + num(lv) + '</td><td class="r"><b>' + num(gap) + '</b></td>' +
				'<td class="r">' + money(gap * (+r.cost_price)) + '</td><td style="width:100px">' + bar(lv ? q / lv * 100 : 0, cls) + '</td><td>' + badge(cls, st) + '</td></tr>';
		}).join('');
		return {
			kpis: kpi('fa-exclamation-triangle', rows.length, 'Need restocking', 'warn') + kpi('fa-ban', out, 'Out of stock', 'bad') +
				kpi('fa-fire', crit, 'Below half of reorder level', 'bad') + kpi('fa-coins', 'Rs. ' + money(cost), 'Cost to reach reorder level'),
			html: rows.length ? table(th(['#', 'Product', 'Category', 'Brand', '>On hand', '>Reorder level', '>Shortfall', '>Cost of shortfall', 'Level', 'Status']), body,
				'<tr><td colspan="6" class="r">Total</td><td class="r">' + num(short) + '</td><td class="r">' + money(cost) + '</td><td colspan="2"></td></tr>') : empty('Every product is above its reorder level.', 'fa-check-circle')
		};
	};

	R.transfer = function (d) {
		var rows = d.data, types = {1: 'Showroom to showroom', 2: 'Warehouse to warehouse', 3: 'Showroom to warehouse', 4: 'Warehouse to showroom'};
		var sts = {1: ['mute', 'Created'], 2: ['mute', 'Loaded'], 3: ['info', 'Dispatched'], 4: ['info', 'Lorry arrived'], 5: ['warn', 'Unloaded'], 6: ['ok', 'Added to stock']};
		var transit = 0, done = 0;
		var body = rows.map(function (r, i) {
			var s = sts[r.transfer_status] || ['mute', '-'];
			if (+r.transfer_status >= 3 && +r.transfer_status < 6) transit++; if (+r.transfer_status == 6) done++;
			var items = (r.items || '').split('||').filter(Boolean).map(function (x) { return '<li>' + esc(x) + '</li>'; }).join('');
			return '<tr><td>' + (i + 1) + '</td><td><b>' + esc(r.transfer_no) + '</b></td><td>' + esc(r.transfer_date) + '</td>' +
				'<td>' + esc(r.from_branch) + ' <i class="fas fa-long-arrow-alt-right text-muted mx-1"></i> ' + esc(r.to_branch) + '<div class="sx-code">' + esc(types[r.transfer_type]) + '</div></td>' +
				'<td><ul class="sx-items">' + items + '</ul></td><td class="r">' + num(r.total_qty) + '</td>' +
				'<td>' + esc(r.vehicle_no) + '<div class="sx-code">' + esc(r.driver_name) + '</div></td><td>' + badge(s[0], s[1]) + '</td></tr>';
		}).join('');
		return {
			kpis: kpi('fa-exchange-alt', rows.length, 'Transfers') + kpi('fa-cubes', num(sum(rows, 'total_qty')), 'Units moved', 'info') +
				kpi('fa-truck', transit, 'On the road or unloading', transit ? 'warn' : '') + kpi('fa-check-circle', done, 'Added to stock'),
			html: rows.length ? table(th(['#', 'Transfer', 'Date', 'Route', 'Items', '>Qty', 'Vehicle', 'Status']), body) : empty('No transfers match these filters.')
		};
	};

	R.dead = function (d) {
		var rows = d.data, never = 0;
		var body = rows.map(function (r, i) {
			var dd = +r.days, cls = dd >= 365 ? 'bad' : (dd >= 180 ? 'warn' : 'info');
			if (!r.last_sale) never++;
			return '<tr><td>' + (i + 1) + '</td><td>' + prod(r) + '</td><td>' + esc(r.category) + '</td><td>' + esc(r.brand) + '</td>' +
				'<td class="r">' + num(r.qty) + '</td><td class="r">' + money(r.value) + '</td><td>' + esc(r.first_in || '-') + '</td>' +
				'<td>' + (r.last_sale ? esc(r.last_sale) : badge('mute', 'Never sold')) + '</td><td class="r"><b>' + num(dd) + '</b></td><td>' + badge(cls, dd >= 365 ? 'Dead' : (dd >= 180 ? 'Very slow' : 'Slow')) + '</td></tr>';
		}).join('');
		return {
			kpis: kpi('fa-hourglass-half', rows.length, 'Items with no sales in ' + d.days + '+ days', 'warn') + kpi('fa-cubes', num(sum(rows, 'qty')), 'Units sitting idle', 'info') +
				kpi('fa-coins', 'Rs. ' + money(sum(rows, 'value')), 'Cash tied up at cost', 'bad') + kpi('fa-ban', never, 'Never sold'),
			html: rows.length ? table(th(['#', 'Product', 'Category', 'Brand', '>Qty', '>Stock value', 'First received', 'Last sale', '>Days idle', 'Class']), body,
				'<tr><td colspan="4" class="r">Total</td><td class="r">' + num(sum(rows, 'qty')) + '</td><td class="r">' + money(sum(rows, 'value')) + '</td><td colspan="4"></td></tr>') : empty('Nothing has been idle that long.', 'fa-check-circle')
		};
	};

	R.valuation = function (d) {
		var rows = d.data, tv = sum(rows, 'value'), tq = sum(rows, 'qty'), cats = {}, order = [], brands = {};
		rows.forEach(function (r) {
			if (!cats[r.category]) { cats[r.category] = {q: 0, v: 0, p: 0, rows: []}; order.push(r.category); }
			var c = cats[r.category]; c.q += +r.qty; c.v += +r.value; c.p += +r.products; c.rows.push(r); brands[r.brand] = 1;
		});
		order.sort(function (a, b) { return cats[b].v - cats[a].v; });
		var body = order.map(function (k) {
			var c = cats[k];
			return '<tr class="grp"><td>' + esc(k) + '</td><td class="r">' + c.p + '</td><td class="r">' + num(c.q) + '</td><td class="r">' + money(c.v) + '</td><td class="r">' + (tv ? (c.v / tv * 100).toFixed(1) : 0) + '%</td><td style="width:140px">' + bar(tv ? c.v / tv * 100 : 0) + '</td></tr>' +
				c.rows.map(function (r) {
					return '<tr class="sub"><td>' + esc(r.brand) + '</td><td class="r">' + r.products + '</td><td class="r">' + num(r.qty) + '</td><td class="r">' + money(r.value) + '</td><td class="r">' + (tv ? (r.value / tv * 100).toFixed(1) : 0) + '%</td><td>' + bar(tv ? r.value / tv * 100 : 0) + '</td></tr>';
				}).join('');
		}).join('');
		var top = order[0];
		return {
			kpis: kpi('fa-coins', 'Rs. ' + money(tv), 'Total stock value at cost') + kpi('fa-cubes', num(tq), 'Units on hand', 'info') +
				kpi('fa-layer-group', order.length, 'Categories') + kpi('fa-trophy', top ? esc(top) : '-', 'Largest category by value', 'warn'),
			html: rows.length ? table(th(['Category / Brand', '>Products', '>Qty', '>Value at cost', '>Share', '']), body,
				'<tr><td>Grand total</td><td></td><td class="r">' + num(tq) + '</td><td class="r">' + money(tv) + '</td><td class="r">100%</td><td></td></tr>') : empty('No stock found.')
		};
	};

	/* ---------- load ---------- */
	function params() {
		return {
			report: REPORT, branch_id: $('#fBranch').val(), from: $('#fFrom').val(), to: $('#fTo').val(),
			product_id: $('#fProduct').val(), days: $('#fDays').val(), ttype: $('#fType').val(), tstatus: $('#fStatus').val(),
			split: $('#fSplit').is(':checked') ? 1 : 0
		};
	}
	function run() {
		$('#reportBody').html('<div class="sx-load"><i class="fas fa-circle-notch fa-spin mr-2"></i>Loading report...</div>');
		$.ajax({url: SCRIPT, type: 'POST', dataType: 'json', data: params(),
			success: function (res) {
				var o = R[REPORT](res);
				$('#kpis').html(o.kpis);
				$('#reportBody').html(o.html);
				$('#hint').text($('#fBranch option:selected').text() + ' - generated ' + new Date().toLocaleString());
			},
			error: function (xhr) {
				var m = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : (xhr.responseText || xhr.statusText || '').substring(0, 300);
				$('#kpis').empty();
				$('#reportBody').html('<div class="alert alert-danger"><b>The report could not be loaded.</b> HTTP ' + xhr.status + ': ' + esc(m) + '</div>');
			}});
	}
	$('#btnRun').on('click', run);
	$('#fBranch,#fFrom,#fTo,#fType,#fStatus,#fSplit,#fProduct').on('change', run);
	$('#fDays').on('keyup', function (e) { if (e.keyCode == 13) run(); });
	if ($.fn.select2 && $('#fProduct').length) { $('#fProduct').select2({width: '100%'}); }
	run();

	/* ---------- export (works from whatever table is on screen) ---------- */
	function cells(tr) { return $(tr).children().map(function () { return $.trim($(this).text().replace(/\s+/g, ' ')); }).get(); }
	function fileName(ext) { return TITLE.replace(/[^a-z0-9]+/gi, '_') + '_' + new Date().toISOString().slice(0, 10) + '.' + ext; }

	$('#btnCsv').on('click', function () {
		var lines = [];
		$('#rpt tr').each(function () { lines.push(cells(this).map(function (c) { return '"' + c.replace(/"/g, '""') + '"'; }).join(',')); });
		if (!lines.length) return;
		var a = document.createElement('a');
		a.href = URL.createObjectURL(new Blob(['\ufeff' + lines.join('\r\n')], {type: 'text/csv;charset=utf-8'}));
		a.download = fileName('csv'); a.click();
	});

	$('#btnPrint').on('click', function () {
		if (!$('#rpt').length) return;
		var w = window.open('', '_blank');
		w.document.write('<html><head><title>' + TITLE + '</title><style>body{font-family:Arial,sans-serif;font-size:11px;padding:18px;color:#111}h2{margin:0 0 4px}' +
			'table{width:100%;border-collapse:collapse;margin-top:10px}th,td{border:1px solid #ccc;padding:4px 6px;text-align:left}th{background:#f1f5f9}' +
			'.r{text-align:right}.c{text-align:center}.sx-code{color:#666;font-size:10px}.sx-items{margin:0;padding-left:12px}.sx-bar{display:none}</style></head><body><h2>' + TITLE + '</h2><div>' +
			esc($('#hint').text()) + '</div>' + $('#rpt')[0].outerHTML + '</body></html>');
		w.document.close(); w.focus(); setTimeout(function () { w.print(); }, 300);
	});

	$('#btnPdf').on('click', function () {
		if (!$('#rpt').length) return;
		if (typeof pdfMake === 'undefined') { alert('pdfMake is not loaded on this page.'); return; }
		var body = [], width = 0;
		$('#rpt tr').each(function () {
			var row = [], isHead = $(this).closest('thead').length > 0, isFoot = $(this).closest('tfoot').length > 0;
			$(this).children().each(function () {
				var cs = parseInt($(this).attr('colspan')) || 1;
				var cell = {text: $.trim($(this).text().replace(/\s+/g, ' ')), bold: isHead || isFoot || $(this).parent().hasClass('grp'), fillColor: isHead ? '#e2e8f0' : null};
				if ($(this).hasClass('r')) cell.alignment = 'right';
				if (cs > 1) cell.colSpan = cs;
				row.push(cell); for (var i = 1; i < cs; i++) row.push({});
			});
			width = Math.max(width, row.length); body.push(row);
		});
		pdfMake.createPdf({
			pageOrientation: 'landscape', pageMargins: [24, 28, 24, 28], defaultStyle: {fontSize: 7.5},
			content: [{text: TITLE, fontSize: 14, bold: true, margin: [0, 0, 0, 3]}, {text: $('#hint').text(), color: '#64748b', margin: [0, 0, 0, 8]},
				{table: {headerRows: 1, widths: Array(width).fill('auto'), body: body}, layout: 'lightHorizontalLines'}]
		}).download(fileName('pdf'));
	});
});
</script>

<?php include "include/footer.php"; ?>