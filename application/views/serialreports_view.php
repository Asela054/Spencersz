<?php
include "include/header.php";
include "include/topnavbar.php";

$endpoint = base_url() . 'scripts/serialreports.php';
$titles = [
	'history' => ['Serial Number History', 'Full life of one serial: GRN, transfers, sale, returns and warranty.'],
	'stock'   => ['Serial-wise Stock', 'Which serials are in stock, and at which location.'],
	'sold'    => ['Sold Serial Lookup', 'Find the customer and invoice for a serial (useful for warranty claims).']
];
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
							<div class="page-header-icon"><i class="fas fa-barcode"></i></div>
							<span><?php echo $titles[$report][0]; ?></span>
						</h1>
						<div class="page-header-subtitle text-muted"><?php echo $titles[$report][1]; ?></div>
					</div>
				</div>
			</div>

			<div class="container-fluid mt-4">

				<!-- ===================== FILTER BAR ===================== -->
				<div class="card mb-4">
					<div class="card-body">

						<?php if ($report == 'history') { ?>
						<form id="frmSearch" class="form-inline" autocomplete="off">
							<label class="mr-2 font-weight-bold" for="q">Serial number</label>
							<input type="text" id="q" class="form-control mr-2" style="min-width:320px" placeholder="Scan or type serial number" autofocus>
							<button type="submit" class="btn btn-primary btn-sm mr-2"><i class="fas fa-search mr-1"></i>Search</button>
							<button type="button" id="btnClear" class="btn btn-light btn-sm border">Clear</button>
						</form>

						<?php } elseif ($report == 'stock') { ?>
						<form id="frmSearch" class="form-row align-items-end" autocomplete="off">
							<div class="col-md-2 mb-2">
								<label class="small mb-1">Location</label>
								<select id="branch_id" class="form-control form-control-sm">
									<option value="0">All locations</option>
									<?php foreach ($branches as $b) { ?>
									<option value="<?php echo $b->idtbl_company_branch; ?>"><?php echo htmlspecialchars($b->branch) . ' (' . ($b->type == 1 ? 'Showroom' : 'Warehouse') . ')'; ?></option>
									<?php } ?>
								</select>
							</div>
							<div class="col-md-3 mb-2">
								<label class="small mb-1">Product</label>
								<select id="product_id" class="form-control form-control-sm">
									<option value="0">All products</option>
									<?php foreach ($products as $p) { ?>
									<option value="<?php echo $p->idtbl_product; ?>"><?php echo htmlspecialchars($p->product_code . ' - ' . $p->product_name); ?></option>
									<?php } ?>
								</select>
							</div>
							<div class="col-md-2 mb-2">
								<label class="small mb-1">Status</label>
								<select id="status" class="form-control form-control-sm">
									<option value="1" selected>In stock</option>
									<option value="2">Sold</option>
									<option value="6">In transit</option>
									<option value="3">Returned to supplier</option>
									<option value="4">Damaged</option>
									<option value="5">Returned by customer</option>
									<option value="0">All statuses</option>
								</select>
							</div>
							<div class="col-md-3 mb-2">
								<label class="small mb-1">Search</label>
								<input type="text" id="q" class="form-control form-control-sm" placeholder="Serial, product name or code">
							</div>
							<div class="col-md-2 mb-2">
								<button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search mr-1"></i>Show</button>
								<button type="button" id="btnClear" class="btn btn-light btn-sm border">Reset</button>
							</div>
						</form>

						<?php } else { ?>
						<form id="frmSearch" class="form-inline" autocomplete="off">
							<label class="mr-2 font-weight-bold">Search by</label>
							<select id="by" class="form-control mr-2">
								<option value="serial">Serial number</option>
								<option value="invoice">Invoice number</option>
								<option value="customer">Customer (name / phone / NIC)</option>
							</select>
							<input type="text" id="q" class="form-control mr-2" style="min-width:300px" placeholder="Type to search" autofocus>
							<button type="submit" class="btn btn-primary btn-sm mr-2"><i class="fas fa-search mr-1"></i>Search</button>
							<button type="button" id="btnClear" class="btn btn-light btn-sm border">Clear</button>
						</form>
						<?php } ?>

					</div>
				</div>

				<!-- ===================== RESULT AREA ===================== -->
				<div id="resultArea">
					<div class="alert alert-light border text-center text-muted">
						<?php echo $report == 'stock' ? 'Loading...' : 'Enter a search above to begin.'; ?>
					</div>
				</div>

			</div>
		</main>
		<?php include "include/footerbar.php"; ?>
	</div>
</div>

<?php include "include/footerscripts.php"; ?>
<script>
$(function () {
	var ENDPOINT = <?php echo json_encode($endpoint); ?>;
	var REPORT   = <?php echo json_encode($report); ?>;
	var HIST_URL = <?php echo json_encode(base_url() . 'Serialhistory'); ?>;
	var $area    = $('#resultArea');

	/* ---------- helpers ---------- */
	function esc(s) {
		return (s === null || s === undefined) ? '' : String(s)
			.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
	}
	function money(n) {
		return Number(n || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
	}
	function alertBox(type, msg) {
		$area.html('<div class="alert alert-' + type + '">' + msg + '</div>');
	}
	var STATUS = {
		1: ['In stock', 'success'], 2: ['Sold', 'primary'], 3: ['Returned to supplier', 'secondary'],
		4: ['Damaged', 'danger'], 5: ['Returned by customer', 'warning'], 6: ['In transit', 'info']
	};
	function statusBadge(s) {
		var x = STATUS[s];
		return x ? '<span class="badge badge-' + x[1] + '">' + x[0] + '</span>' : '<span class="badge badge-light">-</span>';
	}
	var TONE = {ok: 'success', info: 'primary', warn: 'warning', bad: 'danger', mute: 'secondary'};

	function warrantyBadge(left, months) {
		if (!months || parseInt(months) === 0) return '<span class="badge badge-light border">No warranty</span>';
		if (left === null || left === undefined) return '-';
		left = parseInt(left);
		if (left < 0) return '<span class="badge badge-danger">Expired ' + Math.abs(left) + ' days ago</span>';
		if (left <= 30) return '<span class="badge badge-warning">' + left + ' days left</span>';
		return '<span class="badge badge-success">' + left + ' days left</span>';
	}

	function post(data, done) {
		data.report = REPORT;
		$area.html('<div class="text-center p-5 text-muted"><i class="fas fa-spinner fa-spin mr-2"></i>Loading...</div>');
		$.ajax({
			url: ENDPOINT, type: 'POST', data: data, dataType: 'json',
			success: function (res) {
				if (res && res.error) { alertBox('danger', 'Error: ' + esc(res.error)); return; }
				done(res);
			},
			error: function (xhr) {
				var m = 'Request failed.';
				try { m = JSON.parse(xhr.responseText).error || m; } catch (e) {}
				alertBox('danger', '<b>The report could not be loaded.</b> HTTP ' + xhr.status + ': ' + esc(m));
			}
		});
	}

	function initTable(sel, opts) {
		if ($.fn.DataTable) {
			if ($.fn.DataTable.isDataTable(sel)) { $(sel).DataTable().destroy(); }
			$(sel).DataTable($.extend({pageLength: 25, order: []}, opts || {}));
		}
	}

	/* =============== 13. SERIAL HISTORY =============== */
	function loadHistory(serialId) {
		var d = {q: $('#q').val()};
		if (serialId) d.serial_id = serialId;
		post(d, function (res) {
			if (res.mode === 'idle') { alertBox('light border text-center text-muted', 'Enter a serial number to search.'); return; }
			if (res.mode === 'none') { alertBox('warning', 'No record found for this serial number.'); return; }

			if (res.mode === 'pick') {
				var p = '<div class="card"><div class="card-header">Several serials match - pick one</div><div class="list-group list-group-flush">';
				$.each(res.matches, function (n, m) {
					p += '<a href="#" class="list-group-item list-group-item-action pick" data-id="' + m.id + '">' +
						'<strong>' + esc(m.serialno) + '</strong> &nbsp; ' + esc(m.product_code) + ' - ' + esc(m.product_name) +
						' <span class="float-right">' + statusBadge(m.stock_status) + '</span></a>';
				});
				$area.html(p + '</div></div>');
				return;
			}

			var i = res.info || {}, w = res.warranty, h = '';

			if (res.info) {
				h += '<div class="row">';
				h += '<div class="col-lg-7 mb-4"><div class="card h-100"><div class="card-header">Serial details</div><div class="card-body"><table class="table table-sm table-borderless mb-0">' +
					'<tr><th width="35%">Serial No</th><td><strong>' + esc(i.serialno) + '</strong> ' + statusBadge(i.stock_status) + '</td></tr>' +
					'<tr><th>Product</th><td>' + esc(i.product_code) + ' - ' + esc(i.product_name) + '</td></tr>' +
					'<tr><th>Category / Brand</th><td>' + esc(i.category) + ' / ' + esc(i.brand) + '</td></tr>' +
					'<tr><th>Current location</th><td>' + esc(i.location) + '</td></tr>' +
					'<tr><th>GRN</th><td>' + esc(i.grn_no) + ' (' + esc(i.grndate) + ')</td></tr>' +
					'<tr><th>Supplier</th><td>' + esc(i.suppliername) + '</td></tr>' +
					'<tr><th>Batch</th><td>' + esc(i.batchno) + '</td></tr>' +
					'<tr><th>Cost price</th><td>Rs. ' + money(i.costunitprice) + '</td></tr>' +
					'</table></div></div></div>';

				h += '<div class="col-lg-5 mb-4"><div class="card h-100"><div class="card-header">Warranty</div><div class="card-body">';
				if (w) {
					h += '<table class="table table-sm table-borderless mb-0">' +
						'<tr><th width="40%">Sold on</th><td>' + esc(w.sold_on) + '</td></tr>' +
						'<tr><th>Invoice</th><td>' + esc(w.invoice) + '</td></tr>' +
						'<tr><th>Customer</th><td>' + esc(w.customer) + '</td></tr>' +
						'<tr><th>Phone</th><td>' + esc(w.phone) + '</td></tr>' +
						'<tr><th>Warranty</th><td>' + esc(w.months) + ' months</td></tr>' +
						'<tr><th>Ends on</th><td>' + esc(w.ends || '-') + '</td></tr>' +
						'<tr><th>Status</th><td>' + warrantyBadge(w.left, w.months) + '</td></tr></table>';
				} else {
					h += '<span class="text-muted">This serial has not been sold yet.</span>';
				}
				h += '</div></div></div></div>';
			}

			h += '<div class="card"><div class="card-header">Movement history</div><div class="card-body"><div class="table-responsive">' +
				'<table class="table table-bordered table-sm table-hover" id="tblEvents"><thead class="thead-light"><tr>' +
				'<th>#</th><th>Date / time</th><th>Event</th><th>Reference</th><th>Location</th><th>Status change</th><th>Details</th>' +
				'</tr></thead><tbody>';
			$.each(res.events || [], function (n, e) {
				var chg = '';
				if (e.from || e.to) {
					chg = (e.from ? statusBadge(parseInt(e.from)) : '<span class="text-muted">new</span>') + ' &rarr; ' + (e.to ? statusBadge(parseInt(e.to)) : '-');
				}
				h += '<tr><td>' + (n + 1) + '</td><td>' + esc(e.when) + '</td>' +
					'<td><span class="badge badge-' + (TONE[e.tone] || 'secondary') + '">' + esc(e.event) + '</span></td>' +
					'<td>' + esc(e.ref) + '</td><td>' + esc(e.location) + '</td><td>' + chg + '</td><td>' + esc(e.detail) + '</td></tr>';
			});
			h += '</tbody></table></div></div></div>';
			$area.html(h);
		});
	}

	/* =============== 14. SERIAL-WISE STOCK =============== */
	function loadStock() {
		post({
			q: $('#q').val(), branch_id: $('#branch_id').val(),
			product_id: $('#product_id').val(), status: $('#status').val()
		}, function (res) {
			var rows = res.data || [];
			if (!rows.length) { alertBox('warning', 'No serials found for the selected filters.'); return; }

			var byBranch = {}, totalCost = 0;
			$.each(rows, function (i, r) {
				var k = r.branch || 'Unassigned';
				byBranch[k] = (byBranch[k] || 0) + 1;
				totalCost += parseFloat(r.costunitprice || 0);
			});

			var h = '';
			if (res.capped) h += '<div class="alert alert-warning">Showing the first 5,000 serials. Narrow the filters to see the rest.</div>';

			h += '<div class="row mb-3">' +
				'<div class="col-md-3"><div class="card border-left-primary"><div class="card-body py-2"><div class="small text-muted">Serials</div><div class="h4 mb-0">' + rows.length + '</div></div></div></div>' +
				'<div class="col-md-3"><div class="card"><div class="card-body py-2"><div class="small text-muted">Total cost value</div><div class="h4 mb-0">Rs. ' + money(totalCost) + '</div></div></div></div>' +
				'<div class="col-md-6"><div class="card"><div class="card-body py-2"><div class="small text-muted">By location</div>';
			$.each(byBranch, function (k, v) { h += '<span class="badge badge-light border mr-1">' + esc(k) + ': <strong>' + v + '</strong></span>'; });
			h += '</div></div></div></div>';

			h += '<div class="card"><div class="card-body"><div class="table-responsive">' +
				'<table class="table table-bordered table-sm table-hover" id="tblStock"><thead class="thead-light"><tr>' +
				'<th>Serial No</th><th>Code</th><th>Product</th><th>Category</th><th>Brand</th><th>Location</th>' +
				'<th>GRN</th><th>GRN date</th><th>Batch</th><th class="text-right">Cost</th><th class="text-right">Age (days)</th><th>Status</th>' +
				'</tr></thead><tbody>';
			$.each(rows, function (i, r) {
				var age = parseInt(r.age || 0);
				var ageCls = age > 180 ? 'text-danger font-weight-bold' : (age > 90 ? 'text-warning' : '');
				h += '<tr><td><a href="' + HIST_URL + '" class="hist" data-serial="' + esc(r.serialno) + '">' + esc(r.serialno) + '</a></td>' +
					'<td>' + esc(r.product_code) + '</td><td>' + esc(r.product_name) + '</td><td>' + esc(r.category) + '</td><td>' + esc(r.brand) + '</td>' +
					'<td>' + esc(r.branch) + '</td><td>' + esc(r.grn_no) + '</td><td>' + esc(r.grndate) + '</td><td>' + esc(r.batchno) + '</td>' +
					'<td class="text-right" data-order="' + esc(r.costunitprice) + '">' + money(r.costunitprice) + '</td>' +
					'<td class="text-right ' + ageCls + '">' + age + '</td><td>' + statusBadge(parseInt(r.stock_status)) + '</td></tr>';
			});
			h += '</tbody></table></div></div></div>';
			$area.html(h);
			initTable('#tblStock');
		});
	}

	/* =============== 15. SOLD SERIAL LOOKUP =============== */
	function loadSold() {
		post({q: $('#q').val(), by: $('#by').val()}, function (res) {
			if (res.mode === 'idle') { alertBox('light border text-center text-muted', 'Enter something to search.'); return; }
			var rows = res.data || [];
			if (!rows.length) { alertBox('warning', 'No sold serials match your search.'); return; }

			var h = '';
			if (rows.length >= 200) h += '<div class="alert alert-warning">Showing the latest 200 matches. Refine your search to narrow the list.</div>';
			h += '<div class="card"><div class="card-body"><div class="table-responsive">' +
				'<table class="table table-bordered table-sm table-hover" id="tblSold"><thead class="thead-light"><tr>' +
				'<th>Serial No</th><th>Product</th><th>Invoice</th><th>Date</th><th>Customer</th><th>Phone</th><th>Address</th>' +
				'<th>Branch</th><th class="text-right">Price</th><th>Warranty</th><th>Ends</th><th>Status</th>' +
				'</tr></thead><tbody>';
			$.each(rows, function (i, r) {
				var pay = {0: ['Unpaid', 'danger'], 1: ['Partial', 'warning'], 2: ['Paid', 'success']}[r.payment_status] || ['-', 'light'];
				var addr = $.grep([r.address_line1, r.city], function (v) { return v; }).join(', ');
				var state = parseInt(r.returned) > 0
					? '<span class="badge badge-warning">Returned</span>'
					: warrantyBadge(r.w_left, r.warranty_months);
				h += '<tr><td><a href="' + HIST_URL + '" class="hist" data-serial="' + esc(r.serialno) + '"><strong>' + esc(r.serialno) + '</strong></a></td>' +
					'<td>' + esc(r.product_code) + '<br><small class="text-muted">' + esc(r.product_name) + '</small></td>' +
					'<td>' + esc(r.invoice_no) + '<br><span class="badge badge-' + pay[1] + '">' + pay[0] + '</span></td>' +
					'<td>' + esc(r.invoice_date) + '</td>' +
					'<td>' + esc(r.customer) + (r.customer_nic ? '<br><small class="text-muted">' + esc(r.customer_nic) + '</small>' : '') + '</td>' +
					'<td>' + esc(r.telephone_no) + '</td><td>' + esc(addr) + '</td><td>' + esc(r.branch) + '</td>' +
					'<td class="text-right" data-order="' + esc(r.unitprice) + '">' + money(r.unitprice) + '</td>' +
					'<td>' + esc(r.warranty_months) + ' mo</td>' +
					'<td>' + (parseInt(r.warranty_months) > 0 ? esc(r.w_end) : '-') + '</td><td>' + state + '</td></tr>';
			});
			h += '</tbody></table></div></div></div>';
			$area.html(h);
			initTable('#tblSold');
		});
	}

	/* ---------- wiring ---------- */
	function run(serialId) {
		if (REPORT === 'history') loadHistory(serialId);
		else if (REPORT === 'stock') loadStock();
		else loadSold();
	}

	$('#frmSearch').on('submit', function (e) { e.preventDefault(); run(); });

	$('#btnClear').on('click', function () {
		$('#frmSearch')[0].reset();
		if (REPORT === 'stock') { run(); }
		else { $area.html('<div class="alert alert-light border text-center text-muted">Enter a search above to begin.</div>'); $('#q').focus(); }
	});

	// pick a serial from the "several matches" list
	$area.on('click', '.pick', function (e) { e.preventDefault(); loadHistory($(this).data('id')); });

	// serial links in stock / sold reports jump to the history page for that serial
	$area.on('click', '.hist', function (e) {
		e.preventDefault();
		window.location.href = $(this).attr('href') + '?serial=' + encodeURIComponent($(this).data('serial'));
	});

	// history page: auto-run when opened from a link (?serial=XXXX)
	if (REPORT === 'history') {
		var qs = new URLSearchParams(window.location.search).get('serial');
		if (qs) { $('#q').val(qs); run(); }
	}

	// stock report loads immediately with the default "In stock" filter
	if (REPORT === 'stock') run();
});
</script>

<?php include "include/footer.php"; ?>