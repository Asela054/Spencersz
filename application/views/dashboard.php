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
            <div class="container-fluid p-3">

				<!-- Page header -->
				<!-- <div class="ds-page-header mb-4">
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
				</div> -->

			</div>
        </main>
        <?php include "include/footerbar.php"; ?>
    </div>
</div>

<?php include "include/footerscripts.php"; ?>
<?php include "include/footer.php"; ?>