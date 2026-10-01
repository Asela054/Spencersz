<?php
include "include/header.php";
include "include/topnavbar.php";
?>
<style>
	:root{
		/* ---- Red theme (change these to re-theme the dashboard) ---- */
		--ds-primary:#C8102E;        /* main red */
		--ds-primary-dark:#9E0C24;   /* darker red */
		--ds-maroon:#7A0F22;         /* deep maroon */
		--ds-maroon-dark:#560A18;
		--ds-green:#2ec4b6;
		--ds-green-dark:#159b8f;
		--ds-orange:#ff9f1c;
		--ds-orange-dark:#e8850a;
		--ds-coral:#ef476f;          /* lighter red/coral for contrast cards */
		--ds-coral-dark:#d1355a;
		--ds-ink:#1e2233;
		--ds-muted:#8a8fa3;
		--ds-bg:#faf3f4;
		--ds-radius:16px;
	}
	body{ background:var(--ds-bg); }
	.ds-page-header{
		background:linear-gradient(120deg,var(--ds-primary) 0%,var(--ds-maroon) 100%);
		border-radius:var(--ds-radius);
		box-shadow:0 8px 24px rgba(200,16,46,.18);
		padding:1.35rem 1.5rem;
	}
	.ds-page-header .page-header-title{ color:#fff; margin:0; font-weight:700; letter-spacing:.3px; font-size:1.5rem; }
	.ds-page-header .page-header-icon{
		background:rgba(255,255,255,.18);
		color:#fff; border-radius:12px; width:42px; height:42px;
		display:inline-flex; align-items:center; justify-content:center; margin-right:.7rem;
	}
	.ds-page-header .ds-page-header-sub{ color:rgba(255,255,255,.85); font-size:.85rem; margin-top:.25rem; }

	.ds-stat-card{
		border:none; border-radius:var(--ds-radius);
		padding:1.1rem 1.25rem;
		color:#fff; position:relative; overflow:hidden;
		box-shadow:0 10px 22px rgba(30,34,51,.10);
		transition:transform .18s ease, box-shadow .18s ease;
		cursor:pointer; min-height:118px;
	}
	.ds-stat-card:hover{ transform:translateY(-4px); box-shadow:0 16px 28px rgba(30,34,51,.16); }
	.ds-stat-card .ds-icon{
		width:44px; height:44px; border-radius:12px;
		background:rgba(255,255,255,.22);
		display:flex; align-items:center; justify-content:center;
		font-size:1.15rem; margin-bottom:.6rem;
	}
	.ds-stat-card .ds-value{ font-size:1.6rem; font-weight:700; line-height:1; }
	.ds-stat-card .ds-label{ font-size:.78rem; opacity:.92; margin-top:.2rem; font-weight:500; letter-spacing:.2px; }
	.ds-stat-card .ds-blob{
		position:absolute; right:-18px; bottom:-18px; width:90px; height:90px;
		border-radius:50%; background:rgba(255,255,255,.10);
	}
	.card-materials{ background:linear-gradient(135deg,var(--ds-green) 0%,var(--ds-green-dark) 100%); }
	.card-machine{ background:linear-gradient(135deg,var(--ds-coral) 0%,var(--ds-coral-dark) 100%); }
	.card-spares{ background:linear-gradient(135deg,var(--ds-orange) 0%,var(--ds-orange-dark) 100%); }
	.card-zero{ background:linear-gradient(135deg,var(--ds-primary) 0%,var(--ds-primary-dark) 100%); }
	.card-low{ background:linear-gradient(135deg,var(--ds-maroon) 0%,var(--ds-maroon-dark) 100%); }

	.ds-panel{
		background:#fff; border-radius:var(--ds-radius); border:none;
		box-shadow:0 6px 18px rgba(30,34,51,.06);
	}
	.ds-panel .ds-panel-header{
		display:flex; align-items:center; justify-content:space-between;
		padding:1rem 1.25rem .5rem 1.25rem;
	}
	.ds-panel .ds-panel-title{ font-weight:700; color:var(--ds-ink); font-size:.95rem; margin:0; }
	.ds-panel .ds-panel-title small{ display:block; color:var(--ds-muted); font-weight:400; font-size:.72rem; margin-top:2px; }
	.ds-panel .ds-panel-body{ padding:.5rem 1.15rem 1.15rem 1.15rem; }
	.ds-badge-pill{
		font-size:.68rem; font-weight:600; padding:.3rem .6rem; border-radius:20px;
	}
	.ds-badge-sales{ background:rgba(200,16,46,.12); color:var(--ds-primary); }

	.ds-table-panel .table thead th{
		border-top:none; border-bottom:1px solid #f3eaec; color:var(--ds-muted);
		font-size:.72rem; text-transform:uppercase; letter-spacing:.4px; font-weight:700;
	}
	.ds-table-panel .table td{ vertical-align:middle; font-size:.85rem; border-color:#f5eef0; }
	.ds-table-panel .table-striped tbody tr:nth-of-type(odd){ background-color:#fdfafa; }

	.ds-chart-wrap{ position:relative; height:230px; }
	@media (max-width:767px){ .ds-chart-wrap{ height:200px; } }

	.ds-filter-bar{
		background:#fff; border-radius:var(--ds-radius);
		box-shadow:0 6px 18px rgba(30,34,51,.06);
		padding:.85rem 1.1rem; margin-top:1.5rem; margin-bottom:.25rem;
		display:flex; flex-wrap:wrap; align-items:center; gap:1.1rem;
	}
	.ds-filter-group{ display:flex; align-items:center; gap:.5rem; }
	.ds-filter-group label{
		margin:0; font-size:.75rem; font-weight:700; color:var(--ds-muted);
		text-transform:uppercase; letter-spacing:.4px; display:flex; align-items:center; gap:.35rem;
	}
	.ds-filter-group input[type="date"],
	.ds-filter-group input[type="month"]{
		border:1px solid #eddfe2; border-radius:10px; padding:.35rem .6rem;
		font-size:.85rem; color:var(--ds-ink); background:#fdfafa;
	}
	.ds-filter-group input[type="date"]:focus,
	.ds-filter-group input[type="month"]:focus{
		outline:none; border-color:var(--ds-primary);
	}
	.ds-filter-group .btn{
		border-radius:10px; font-size:.8rem; padding:.35rem .9rem; font-weight:600;
	}
	.ds-filter-reset{ font-size:.78rem; color:var(--ds-muted); text-decoration:underline; cursor:pointer; }
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
					<h1 class="page-header-title d-flex align-items-center">
						<span class="page-header-icon"><i class="fas fa-desktop"></i></span>
						<span>Dashboard</span>
					</h1>
				</div>

			</div>
        </main>
        <?php include "include/footerbar.php"; ?>
    </div>
</div>

<?php include "include/footerscripts.php"; ?>
<?php include "include/footer.php"; ?>