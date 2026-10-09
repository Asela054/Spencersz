<?php
include "include/header.php";
include "include/topnavbar.php";

// ---- small view helpers ----
function ds_money($n){ return 'Rs. ' . number_format((float) $n, 2); }
function ds_int($n){ return number_format((float) $n, 0); }
function ds_e($s){ return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

$csrf_on   = $this->config->item('csrf_protection');
$csrf_name = $this->security->get_csrf_token_name();
$csrf_hash = $this->security->get_csrf_hash();

$change = $cards['sales_change'];
?>
<style>
	:root{
		--ds-primary:#C8102E;
		--ds-primary-dark:#9E0C24;
		--ds-maroon:#7A0F22;
		--ds-teal:#159b8f;
		--ds-teal-soft:#e4f6f4;
		--ds-amber:#e8850a;
		--ds-amber-soft:#fff2e0;
		--ds-red-soft:#fde8eb;
		--ds-ink:#1e2233;
		--ds-muted:#7d8296;
		--ds-line:#f1e7e9;
		--ds-bg:#faf3f4;
		--ds-radius:14px;
	}
	body{ background:var(--ds-bg); color:var(--ds-ink); }

	/* ---------- header ---------- */
	.ds-page-header{
		background:linear-gradient(120deg,var(--ds-primary) 0%,var(--ds-maroon) 100%);
		border-radius:var(--ds-radius);
		box-shadow:0 8px 24px rgba(200,16,46,.18);
		padding:1.25rem 1.5rem;
		display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.75rem;
	}
	.ds-page-header .page-header-title{ color:#fff; margin:0; font-weight:700; font-size:1.45rem; }
	.ds-page-header .page-header-icon{
		background:rgba(255,255,255,.18); color:#fff; border-radius:12px; width:42px; height:42px;
		display:inline-flex; align-items:center; justify-content:center; margin-right:.7rem;
	}
	.ds-page-header .ds-sub{ color:rgba(255,255,255,.85); font-size:.85rem; margin-top:.2rem; }
	.ds-page-header .ds-today{ color:#fff; text-align:right; font-size:.85rem; opacity:.95; }
	.ds-page-header .ds-today strong{ display:block; font-size:1.05rem; }

	/* ---------- hero card (the one bold element) ---------- */
	.ds-hero{
		background:linear-gradient(150deg,var(--ds-primary) 0%,var(--ds-maroon) 100%);
		color:#fff; border-radius:var(--ds-radius); padding:1.5rem;
		box-shadow:0 14px 30px rgba(200,16,46,.25); height:100%;
		display:flex; flex-direction:column; justify-content:space-between; min-height:250px;
	}
	.ds-hero .ds-hero-label{ font-size:.9rem; opacity:.9; font-weight:600; }
	.ds-hero .ds-hero-value{ font-size:2.1rem; font-weight:800; line-height:1.1; margin:.4rem 0 .5rem; word-break:break-word; }
	.ds-hero .ds-trend{
		display:inline-block; font-size:.78rem; font-weight:700; padding:.25rem .65rem; border-radius:20px;
		background:rgba(255,255,255,.2);
	}
	.ds-hero .ds-hero-foot{
		display:flex; gap:1.5rem; border-top:1px solid rgba(255,255,255,.22); padding-top:.9rem; margin-top:1rem;
	}
	.ds-hero .ds-hero-foot div span{ display:block; font-size:.74rem; opacity:.8; }
	.ds-hero .ds-hero-foot div strong{ font-size:1.02rem; }

	/* ---------- stat cards ---------- */
	.ds-stat{
		background:#fff; border-radius:var(--ds-radius); padding:1.05rem 1.15rem; height:100%;
		box-shadow:0 6px 18px rgba(30,34,51,.06); display:flex; gap:.9rem; align-items:flex-start;
	}
	.ds-chip{
		flex:0 0 42px; width:42px; height:42px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:1.05rem;
	}
	.chip-red{ background:var(--ds-red-soft); color:var(--ds-primary); }
	.chip-teal{ background:var(--ds-teal-soft); color:var(--ds-teal); }
	.chip-amber{ background:var(--ds-amber-soft); color:var(--ds-amber); }
	.ds-stat .ds-label{ font-size:.8rem; color:var(--ds-muted); font-weight:600; }
	.ds-stat .ds-value{ font-size:1.3rem; font-weight:800; line-height:1.25; word-break:break-word; }
	.ds-stat .ds-note{ font-size:.74rem; color:var(--ds-muted); margin-top:.1rem; }
	.ds-stat .ds-warn{ color:var(--ds-primary); font-weight:700; }

	/* ---------- count strip ---------- */
	.ds-strip{
		background:#fff; border-radius:var(--ds-radius); box-shadow:0 6px 18px rgba(30,34,51,.06);
		display:flex; flex-wrap:wrap;
	}
	.ds-strip .ds-strip-item{
		flex:1 1 150px; padding:.9rem 1.2rem; border-right:1px solid var(--ds-line);
	}
	.ds-strip .ds-strip-item:last-child{ border-right:none; }
	.ds-strip .ds-strip-item span{ display:block; font-size:.78rem; color:var(--ds-muted); font-weight:600; }
	.ds-strip .ds-strip-item strong{ font-size:1.25rem; }
	@media (max-width:575px){ .ds-strip .ds-strip-item{ border-right:none; border-bottom:1px solid var(--ds-line); } }

	/* ---------- panels ---------- */
	.ds-panel{ background:#fff; border-radius:var(--ds-radius); box-shadow:0 6px 18px rgba(30,34,51,.06); height:100%; }
	.ds-panel .ds-panel-header{
		display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.5rem;
		padding:1rem 1.25rem .4rem;
	}
	.ds-panel .ds-panel-title{ font-weight:700; font-size:.98rem; margin:0; }
	.ds-panel .ds-panel-title small{ display:block; color:var(--ds-muted); font-weight:400; font-size:.76rem; margin-top:2px; }
	.ds-panel .ds-panel-body{ padding:.4rem 1.15rem 1.15rem; }
	.ds-chart-wrap{ position:relative; height:260px; }
	.ds-chart-wrap.tall{ height:290px; }
	.ds-empty{
		display:none; align-items:center; justify-content:center; height:100%; text-align:center;
		color:var(--ds-muted); font-size:.85rem; padding:0 1rem;
	}

	/* ---------- filters ---------- */
	.ds-filter{ display:flex; align-items:center; gap:.4rem; flex-wrap:wrap; }
	.ds-filter input, .ds-filter select{
		border:1px solid #eddfe2; border-radius:9px; padding:.28rem .5rem; font-size:.8rem; background:#fdfafa; color:var(--ds-ink);
	}
	.ds-filter input:focus, .ds-filter select:focus{ outline:2px solid rgba(200,16,46,.35); border-color:var(--ds-primary); }
	.ds-filter a{ font-size:.76rem; color:var(--ds-muted); text-decoration:underline; cursor:pointer; }

	/* ---------- tables ---------- */
	.ds-table .table{ margin-bottom:0; }
	.ds-table .table thead th{
		border-top:none; border-bottom:1px solid var(--ds-line); color:var(--ds-muted);
		font-size:.76rem; font-weight:700; white-space:nowrap;
	}
	.ds-table .table td{ vertical-align:middle; font-size:.84rem; border-color:#f5eef0; }
	.ds-table .num{ text-align:right; white-space:nowrap; }
	.ds-table .sub{ display:block; color:var(--ds-muted); font-size:.72rem; }
	.ds-pill{ font-size:.7rem; font-weight:700; padding:.2rem .55rem; border-radius:20px; display:inline-block; }
	.pill-red{ background:var(--ds-red-soft); color:var(--ds-primary); }
	.pill-amber{ background:var(--ds-amber-soft); color:var(--ds-amber); }
	.pill-teal{ background:var(--ds-teal-soft); color:var(--ds-teal); }
	.ds-none{ text-align:center; color:var(--ds-muted); padding:1.6rem .5rem !important; }

	@media (prefers-reduced-motion:no-preference){
		.ds-stat{ transition:box-shadow .18s ease; }
		.ds-stat:hover{ box-shadow:0 10px 24px rgba(30,34,51,.12); }
	}
</style>

<div id="layoutSidenav">
    <div id="layoutSidenav_nav">
        <?php include "include/menubar.php"; ?>
    </div>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid p-3">

				<!-- Page header -->
				<div class="ds-page-header mb-4">
					<div>
						<h1 class="page-header-title d-flex align-items-center">
							<span class="page-header-icon"><i class="fas fa-desktop"></i></span>
							<span>Dashboard</span>
						</h1>
						<div class="ds-sub">
							<?php echo ds_e(isset($_SESSION['companyname']) ? $_SESSION['companyname'] : ''); ?>
							<?php if (!empty($_SESSION['branchname'])) echo ' - ' . ds_e($_SESSION['branchname']); ?>
						</div>
					</div>
					<div class="ds-today">
						<strong><?php echo date('l, d F Y'); ?></strong>
						Welcome back, <?php echo ds_e(isset($_SESSION['name']) ? $_SESSION['name'] : ''); ?>
					</div>
				</div>

				<!-- Hero + stat cards -->
				<div class="row mb-3">
					<div class="col-xl-4 col-lg-5 mb-3 mb-lg-0">
						<div class="ds-hero">
							<div>
								<div class="ds-hero-label">Sales this month (excl. VAT)</div>
								<div class="ds-hero-value"><?php echo ds_money($cards['sales_month']); ?></div>
								<?php if ($change === null){ ?>
									<span class="ds-trend">No sales last month to compare</span>
								<?php } else { ?>
									<span class="ds-trend">
										<i class="fas fa-arrow-<?php echo $change >= 0 ? 'up' : 'down'; ?>"></i>
										<?php echo abs($change); ?>% vs last month
									</span>
								<?php } ?>
							</div>
							<div class="ds-hero-foot">
								<div><span>Sales today</span><strong><?php echo ds_money($cards['sales_today']); ?></strong></div>
								<div><span>Invoices this month</span><strong><?php echo ds_int($cards['invoices_month']); ?></strong></div>
							</div>
						</div>
					</div>

					<div class="col-xl-8 col-lg-7">
						<div class="row">
							<div class="col-md-6 col-xl-4 mb-3">
								<div class="ds-stat">
									<div class="ds-chip chip-teal"><i class="fas fa-chart-line"></i></div>
									<div>
										<div class="ds-label">Gross profit this month</div>
										<div class="ds-value"><?php echo ds_money($cards['profit_month']); ?></div>
										<div class="ds-note">Selling price minus cost</div>
									</div>
								</div>
							</div>
							<div class="col-md-6 col-xl-4 mb-3">
								<div class="ds-stat">
									<div class="ds-chip chip-amber"><i class="fas fa-truck-loading"></i></div>
									<div>
										<div class="ds-label">Purchases this month</div>
										<div class="ds-value"><?php echo ds_money($cards['purchase_month']); ?></div>
										<div class="ds-note"><?php echo ds_int($cards['grn_month']); ?> approved GRNs</div>
									</div>
								</div>
							</div>
							<div class="col-md-6 col-xl-4 mb-3">
								<div class="ds-stat">
									<div class="ds-chip chip-red"><i class="fas fa-coins"></i></div>
									<div>
										<div class="ds-label">Stock value (at cost)</div>
										<div class="ds-value"><?php echo ds_money($cards['stock_value']); ?></div>
										<div class="ds-note">On hand across all batches</div>
									</div>
								</div>
							</div>
							<div class="col-md-6 col-xl-4 mb-3 mb-xl-0">
								<div class="ds-stat">
									<div class="ds-chip chip-teal"><i class="fas fa-boxes"></i></div>
									<div>
										<div class="ds-label">Units in stock</div>
										<div class="ds-value"><?php echo ds_int($cards['stock_units']); ?></div>
										<div class="ds-note">Across <?php echo ds_int($cards['products']); ?> active products</div>
									</div>
								</div>
							</div>
							<div class="col-md-6 col-xl-4 mb-3 mb-xl-0">
								<div class="ds-stat">
									<div class="ds-chip chip-red"><i class="fas fa-exclamation-triangle"></i></div>
									<div>
										<div class="ds-label">Needs restocking</div>
										<div class="ds-value"><?php echo ds_int($cards['low_stock'] + $cards['out_stock']); ?></div>
										<div class="ds-note">
											<span class="ds-warn"><?php echo ds_int($cards['out_stock']); ?></span> out of stock,
											<span class="ds-warn"><?php echo ds_int($cards['low_stock']); ?></span> running low
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-6 col-xl-4">
								<div class="ds-stat">
									<div class="ds-chip chip-amber"><i class="fas fa-file-invoice"></i></div>
									<div>
										<div class="ds-label">Purchase orders awaiting GRN</div>
										<div class="ds-value"><?php echo ds_int($cards['pending_po']); ?></div>
										<div class="ds-note">Goods not yet received</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Count strip -->
				<div class="ds-strip mb-4">
					<div class="ds-strip-item"><span>Active products</span><strong><?php echo ds_int($cards['products']); ?></strong></div>
					<div class="ds-strip-item"><span>Customers</span><strong><?php echo ds_int($cards['customers']); ?></strong></div>
					<div class="ds-strip-item"><span>Suppliers</span><strong><?php echo ds_int($cards['suppliers']); ?></strong></div>
					<div class="ds-strip-item"><span>Serial-tracked units in stock</span><strong><?php echo ds_int($cards['serials_in_stock']); ?></strong></div>
				</div>

				<!-- Daily + stock by category -->
				<div class="row mb-3">
					<div class="col-xl-8 mb-3 mb-xl-0">
						<div class="ds-panel">
							<div class="ds-panel-header">
								<h6 class="ds-panel-title">Daily sales and purchases<small>Excluding VAT</small></h6>
								<div class="ds-filter">
									<label for="dailyEnd" class="mb-0 small text-muted">Up to</label>
									<input type="date" id="dailyEnd" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">
									<select id="dailyDays" aria-label="Number of days">
										<option value="7">7 days</option>
										<option value="14">14 days</option>
										<option value="30">30 days</option>
									</select>
									<a id="dailyReset">Reset</a>
								</div>
							</div>
							<div class="ds-panel-body">
								<div class="ds-chart-wrap tall"><canvas id="chartDaily"></canvas></div>
							</div>
						</div>
					</div>
					<div class="col-xl-4">
						<div class="ds-panel">
							<div class="ds-panel-header">
								<h6 class="ds-panel-title">Stock value by category<small>At cost</small></h6>
							</div>
							<div class="ds-panel-body">
								<div class="ds-chart-wrap tall">
									<canvas id="chartCategory"></canvas>
									<div class="ds-empty" id="emptyCategory">No stock on hand yet. Approve a GRN to see it here.</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Monthly + serials -->
				<div class="row mb-3">
					<div class="col-xl-8 mb-3 mb-xl-0">
						<div class="ds-panel">
							<div class="ds-panel-header">
								<h6 class="ds-panel-title">Monthly sales and purchases<small>Last 12 months, excluding VAT</small></h6>
								<div class="ds-filter">
									<label for="monthlyEnd" class="mb-0 small text-muted">Up to</label>
									<input type="month" id="monthlyEnd" value="<?php echo date('Y-m'); ?>" max="<?php echo date('Y-m'); ?>">
									<a id="monthlyReset">Reset</a>
								</div>
							</div>
							<div class="ds-panel-body">
								<div class="ds-chart-wrap"><canvas id="chartMonthly"></canvas></div>
							</div>
						</div>
					</div>
					<div class="col-xl-4">
						<div class="ds-panel">
							<div class="ds-panel-header">
								<h6 class="ds-panel-title">Serial number status<small>Every tracked unit</small></h6>
							</div>
							<div class="ds-panel-body">
								<div class="ds-chart-wrap">
									<canvas id="chartSerial"></canvas>
									<div class="ds-empty" id="emptySerial">No serial numbers recorded yet.</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Top products + brand -->
				<div class="row mb-3">
					<div class="col-xl-6 mb-3 mb-xl-0">
						<div class="ds-panel">
							<div class="ds-panel-header">
								<h6 class="ds-panel-title">Top selling products<small>By revenue, last 30 days</small></h6>
							</div>
							<div class="ds-panel-body">
								<div class="ds-chart-wrap">
									<canvas id="chartTop"></canvas>
									<div class="ds-empty" id="emptyTop">No invoices in the last 30 days.</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-6">
						<div class="ds-panel">
							<div class="ds-panel-header">
								<h6 class="ds-panel-title">Stock value by brand<small>At cost</small></h6>
							</div>
							<div class="ds-panel-body">
								<div class="ds-chart-wrap">
									<canvas id="chartBrand"></canvas>
									<div class="ds-empty" id="emptyBrand">No stock on hand yet.</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Tables -->
				<div class="row mb-3">
					<div class="col-xl-6 mb-3 mb-xl-0">
						<div class="ds-panel ds-table">
							<div class="ds-panel-header">
								<h6 class="ds-panel-title">Low and out of stock<small>At or below the reorder level</small></h6>
							</div>
							<div class="ds-panel-body">
								<div class="table-responsive">
									<table class="table">
										<thead><tr><th>Product</th><th>Category</th><th class="num">In stock</th><th class="num">Reorder at</th><th>Status</th></tr></thead>
										<tbody>
										<?php if (empty($lowstock)){ ?>
											<tr><td colspan="5" class="ds-none">All products are above their reorder level.</td></tr>
										<?php } else { foreach ($lowstock as $r){ ?>
											<tr>
												<td><?php echo ds_e($r->product_name); ?><span class="sub"><?php echo ds_e($r->product_code); ?></span></td>
												<td><?php echo ds_e($r->category); ?></td>
												<td class="num"><?php echo ds_int($r->qty); ?></td>
												<td class="num"><?php echo ds_int($r->reorder_level); ?></td>
												<td><?php if ($r->qty <= 0){ ?><span class="ds-pill pill-red">Out of stock</span><?php } else { ?><span class="ds-pill pill-amber">Low</span><?php } ?></td>
											</tr>
										<?php } } ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-6">
						<div class="ds-panel ds-table">
							<div class="ds-panel-header">
								<h6 class="ds-panel-title">Recent invoices<small>Latest 6</small></h6>
							</div>
							<div class="ds-panel-body">
								<div class="table-responsive">
									<table class="table">
										<thead><tr><th>Invoice</th><th>Date</th><th>Customer</th><th class="num">Total</th></tr></thead>
										<tbody>
										<?php if (empty($recentinv)){ ?>
											<tr><td colspan="4" class="ds-none">No invoices yet.</td></tr>
										<?php } else { foreach ($recentinv as $r){ ?>
											<tr>
												<td><?php echo ds_e($r->invoice_no); ?></td>
												<td><?php echo date('d M Y', strtotime($r->invoice_date)); ?></td>
												<td><?php echo ds_e($r->customer); ?></td>
												<td class="num"><?php echo ds_money($r->total); ?></td>
											</tr>
										<?php } } ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row mb-3">
					<div class="col-xl-6 mb-3 mb-xl-0">
						<div class="ds-panel ds-table">
							<div class="ds-panel-header">
								<h6 class="ds-panel-title">Recent goods received<small>Latest 6 GRNs</small></h6>
							</div>
							<div class="ds-panel-body">
								<div class="table-responsive">
									<table class="table">
										<thead><tr><th>GRN</th><th>Date</th><th>Supplier</th><th class="num">Total</th><th>Status</th></tr></thead>
										<tbody>
										<?php if (empty($recentgrn)){ ?>
											<tr><td colspan="5" class="ds-none">No GRNs yet.</td></tr>
										<?php } else { foreach ($recentgrn as $r){ ?>
											<tr>
												<td><?php echo ds_e($r->grn_no); ?></td>
												<td><?php echo date('d M Y', strtotime($r->grndate)); ?></td>
												<td><?php echo ds_e($r->suppliername); ?></td>
												<td class="num"><?php echo ds_money($r->totalcost); ?></td>
												<td><?php if ($r->approvestatus == 1){ ?><span class="ds-pill pill-teal">Approved</span><?php } else { ?><span class="ds-pill pill-amber">Pending</span><?php } ?></td>
											</tr>
										<?php } } ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-6">
						<div class="ds-panel ds-table">
							<div class="ds-panel-header">
								<h6 class="ds-panel-title">Recent GRN returns<small>Goods sent back to suppliers</small></h6>
							</div>
							<div class="ds-panel-body">
								<div class="table-responsive">
									<table class="table">
										<thead><tr><th>GRN</th><th>Date</th><th>Supplier</th><th class="num">Value</th><th>Status</th></tr></thead>
										<tbody>
										<?php if (empty($recentret)){ ?>
											<tr><td colspan="5" class="ds-none">No returns recorded.</td></tr>
										<?php } else { foreach ($recentret as $r){ ?>
											<tr>
												<td><?php echo ds_e($r->grn_no); ?></td>
												<td><?php echo date('d M Y', strtotime($r->insertdatetime)); ?></td>
												<td><?php echo ds_e($r->suppliername); ?></td>
												<td class="num"><?php echo ds_money($r->totalpayment); ?></td>
												<td><?php if ($r->approvestatus == 1){ ?><span class="ds-pill pill-teal">Approved</span><?php } else { ?><span class="ds-pill pill-amber">Pending</span><?php } ?></td>
											</tr>
										<?php } } ?>
										</tbody>
									</table>
								</div>
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

<!-- Chart.js 4 (loaded last so it is the version this page uses) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
	var RED = '#C8102E', MAROON = '#7A0F22', TEAL = '#159b8f';
	var PALETTE = ['#C8102E','#7A0F22','#e8850a','#159b8f','#ef476f','#5b6078','#f4a261','#8d99ae'];
	var SERIAL_COLORS = {'In stock':'#159b8f','Sold':'#C8102E','Returned to supplier':'#e8850a','Damaged':'#7A0F22','Returned by customer':'#5b6078'};

	var initial = {
		daily:    <?php echo $daily; ?>,
		monthly:  <?php echo $monthly; ?>,
		category: <?php echo $bycategory; ?>,
		brand:    <?php echo $bybrand; ?>,
		top:      <?php echo $topproducts; ?>,
		serial:   <?php echo $serials; ?>
	};

	var CSRF = <?php echo $csrf_on ? json_encode(array('name' => $csrf_name, 'hash' => $csrf_hash)) : 'null'; ?>;

	function money(v){ return 'Rs. ' + Number(v).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}); }
	function compact(v){
		var a = Math.abs(v);
		if (a >= 1e6) return (v/1e6).toFixed(1).replace(/\.0$/,'') + 'M';
		if (a >= 1e3) return (v/1e3).toFixed(1).replace(/\.0$/,'') + 'k';
		return v;
	}
	function total(arr){ return (arr || []).reduce(function(a,b){ return a + Number(b); }, 0); }

	Chart.defaults.font.family = "'Nunito','Segoe UI',system-ui,sans-serif";
	Chart.defaults.color = '#7d8296';
	Chart.defaults.plugins.legend.labels.usePointStyle = true;
	Chart.defaults.plugins.legend.labels.boxWidth = 8;

	var moneyTooltip = { callbacks: { label: function(c){
		var name = c.dataset.label ? c.dataset.label + ': ' : (c.label ? c.label + ': ' : '');
		var v = (c.parsed && c.parsed.x !== undefined && c.chart.options.indexAxis === 'y') ? c.parsed.x : (c.parsed.y !== undefined ? c.parsed.y : c.parsed);
		return ' ' + name + money(v);
	}}};
	var yAxis = { beginAtZero:true, grid:{color:'#f3eaec'}, border:{display:false}, ticks:{ callback:function(v){ return compact(v); } } };
	var xAxis = { grid:{display:false}, border:{display:false} };

	/* shows the message instead of an all-zero chart */
	function toggleEmpty(canvasId, emptyId, hasData){
		document.getElementById(canvasId).style.display = hasData ? '' : 'none';
		var e = document.getElementById(emptyId);
		if (e) e.style.display = hasData ? 'none' : 'flex';
	}

	/* ---------- sales vs purchases (bar pair) ---------- */
	function pairData(d){
		return {
			labels: d.labels,
			datasets: [
				{ label:'Sales',     data:d.sales,     backgroundColor:RED,  borderRadius:5, maxBarThickness:22 },
				{ label:'Purchases', data:d.purchases, backgroundColor:TEAL, borderRadius:5, maxBarThickness:22 }
			]
		};
	}
	function pairChart(id, d){
		return new Chart(document.getElementById(id), {
			type:'bar', data:pairData(d),
			options:{ responsive:true, maintainAspectRatio:false, interaction:{mode:'index', intersect:false},
				plugins:{ legend:{position:'top', align:'end'}, tooltip:moneyTooltip },
				scales:{ x:xAxis, y:yAxis } }
		});
	}
	var dailyChart   = pairChart('chartDaily',   initial.daily);
	var monthlyChart = pairChart('chartMonthly', initial.monthly);

	function refresh(chart, d){
		chart.data = pairData(d);
		chart.update();
	}

	/* ---------- doughnuts ---------- */
	new Chart(document.getElementById('chartCategory'), {
		type:'doughnut',
		data:{ labels:initial.category.labels, datasets:[{ data:initial.category.data, backgroundColor:PALETTE, borderWidth:2, borderColor:'#fff' }] },
		options:{ responsive:true, maintainAspectRatio:false, cutout:'62%',
			plugins:{ legend:{position:'bottom'}, tooltip:moneyTooltip } }
	});
	toggleEmpty('chartCategory', 'emptyCategory', total(initial.category.data) > 0);

	new Chart(document.getElementById('chartSerial'), {
		type:'doughnut',
		data:{ labels:initial.serial.labels,
			datasets:[{ data:initial.serial.data,
				backgroundColor:initial.serial.labels.map(function(l){ return SERIAL_COLORS[l] || '#8d99ae'; }),
				borderWidth:2, borderColor:'#fff' }] },
		options:{ responsive:true, maintainAspectRatio:false, cutout:'62%',
			plugins:{ legend:{position:'bottom'},
				tooltip:{ callbacks:{ label:function(c){ return ' ' + c.label + ': ' + c.parsed + ' units'; } } } } }
	});
	toggleEmpty('chartSerial', 'emptySerial', total(initial.serial.data) > 0);

	/* ---------- horizontal bars ---------- */
	function hbar(id, emptyId, d, color){
		new Chart(document.getElementById(id), {
			type:'bar',
			data:{ labels:d.labels, datasets:[{ label:'Value', data:d.data, backgroundColor:color, borderRadius:6, maxBarThickness:26 }] },
			options:{ indexAxis:'y', responsive:true, maintainAspectRatio:false,
				plugins:{ legend:{display:false}, tooltip:moneyTooltip },
				scales:{ x:yAxis, y:{ grid:{display:false}, border:{display:false} } } }
		});
		toggleEmpty(id, emptyId, total(d.data) > 0);
	}
	hbar('chartTop',   'emptyTop',   initial.top,   RED);
	hbar('chartBrand', 'emptyBrand', initial.brand, MAROON);

	/* ---------- date pickers -> AJAX ---------- */
	function post(url, params){
		var body = new URLSearchParams(params);
		if (CSRF){ body.append(CSRF.name, CSRF.hash); }
		return fetch(url, { method:'POST', body:body, credentials:'same-origin',
			headers:{'X-Requested-With':'XMLHttpRequest'} })
			.then(function(r){ return r.json(); });
	}

	var base = "<?php echo base_url('Welcome/'); ?>";
	var today = "<?php echo date('Y-m-d'); ?>", thisMonth = "<?php echo date('Y-m'); ?>";
	var dailyEnd = document.getElementById('dailyEnd'), dailyDays = document.getElementById('dailyDays'), monthlyEnd = document.getElementById('monthlyEnd');

	function loadDaily(){
		if (!dailyEnd.value) return;
		post(base + 'DailyChartData', { enddate:dailyEnd.value, days:dailyDays.value })
			.then(function(d){ refresh(dailyChart, d); });
	}
	function loadMonthly(){
		if (!monthlyEnd.value) return;
		post(base + 'MonthlyChartData', { endmonth:monthlyEnd.value })
			.then(function(d){ refresh(monthlyChart, d); });
	}
	dailyEnd.addEventListener('change', loadDaily);
	dailyDays.addEventListener('change', loadDaily);
	monthlyEnd.addEventListener('change', loadMonthly);

	document.getElementById('dailyReset').addEventListener('click', function(){
		dailyEnd.value = today; dailyDays.value = '7'; refresh(dailyChart, initial.daily);
	});
	document.getElementById('monthlyReset').addEventListener('click', function(){
		monthlyEnd.value = thisMonth; refresh(monthlyChart, initial.monthly);
	});
})();
</script>
<?php include "include/footer.php"; ?>