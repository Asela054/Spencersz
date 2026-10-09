<?php 
include "include/header.php";  
?>
<style>
    :root {
      --spencersz-primary: #c8102e;
      --spencersz-primary-dark: #a60c25;
      --spencersz-primary-light: #fdf2f4;
      --spencersz-bg: #f8f9fa;
      --spencersz-card-bg: #ffffff;
      --spencersz-border: #e2e8f0;
      --spencersz-text: #1e293b;
      --spencersz-text-muted: #64748b;
      --sidebar-width: 260px;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      background-color: var(--spencersz-bg);
      color: var(--spencersz-text);
      font-size: 0.8125rem; /* Compact 13px baseline for high UI density */
      overflow-x: hidden;
    }

    .app-wrapper {
      display: flex;
      min-height: 100vh;
      padding: 0.75rem;
      gap: 0.75rem;
    }

    /* Floating Sidebar Styling */
    .app-sidebar {
      width: var(--sidebar-width);
      flex-shrink: 0;
      background: #ffffff;
      border-radius: 1rem;
      border: 1px solid var(--spencersz-border);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      box-shadow: 0 4px 20px -2px rgba(0,0,0,0.03);
      padding: 1rem 0.75rem;
      transition: all 0.3s ease;
      z-index: 1020;
    }

    .brand-box {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.5rem 0.75rem;
      margin-bottom: 1.25rem;
    }

    .brand-icon {
      width: 38px;
      height: 38px;
      background-color: var(--spencersz-primary);
      color: white;
      border-radius: 0.6rem;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
    }

    .nav-section-title {
      font-size: 0.65rem;
      font-weight: 700;
      color: #94a3b8;
      letter-spacing: 0.08em;
      padding: 0.5rem 0.75rem 0.25rem;
      text-transform: uppercase;
    }

    .sidebar-menu {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-direction: column;
      gap: 0.2rem;
    }

    .sidebar-menu .nav-link {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.6rem 0.75rem;
      color: #475569;
      font-weight: 500;
      border-radius: 0.6rem;
      transition: all 0.15s ease-in-out;
      text-decoration: none;
    }

    .sidebar-menu .nav-link:hover {
      background-color: var(--spencersz-primary-light);
      color: var(--spencersz-primary);
    }

    .sidebar-menu .nav-link.active {
      background-color: var(--spencersz-primary-light);
      color: var(--spencersz-primary);
      font-weight: 600;
    }

    .sidebar-menu .nav-link i {
      width: 20px;
      text-align: center;
      margin-right: 0.6rem;
      font-size: 0.95rem;
    }

    .sidebar-footer {
      background-color: #f8fafc;
      border-radius: 0.75rem;
      padding: 0.6rem 0.75rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border: 1px solid #f1f5f9;
    }

    .avatar-circle {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background-color: var(--spencersz-primary);
      color: white;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.75rem;
    }

    /* Main Container & Top Nav Styling */
    .app-main {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
      min-width: 0;
    }

    .top-navbar {
      background: #ffffff;
      border-radius: 0.85rem;
      border: 1px solid var(--spencersz-border);
      padding: 0.6rem 1.25rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }

    /* Header Crimson Banner */
    .page-banner-card {
      background: linear-gradient(135deg, var(--spencersz-primary) 0%, var(--spencersz-primary-dark) 100%);
      color: white;
      border-radius: 0.85rem;
      padding: 1rem 1.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 4px 15px rgba(200, 16, 46, 0.2);
    }

    .page-banner-card .banner-icon {
      width: 44px;
      height: 44px;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 0.6rem;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
    }

    /* Cards & Compact Input Elements */
    .card-compact {
      background: #ffffff;
      border: 1px solid var(--spencersz-border);
      border-radius: 0.85rem;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
      margin-bottom: 0.75rem;
      overflow: hidden;
    }

    .card-compact .card-header-compact {
      padding: 0.65rem 1rem;
      background: #ffffff;
      border-bottom: 1px solid #f1f5f9;
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-weight: 700;
      font-size: 0.8125rem;
      color: var(--spencersz-text);
    }

    .card-compact .card-body-compact {
      padding: 0.85rem 1rem;
    }

    .form-control, .form-select {
      font-size: 0.8125rem;
      padding: 0.42rem 0.65rem;
      border-radius: 0.5rem;
      border-color: #cbd5e1;
    }

    .form-control:focus, .form-select:focus {
      border-color: var(--spencersz-primary);
      box-shadow: 0 0 0 0.2rem rgba(200, 16, 46, 0.15);
    }

    /* Specialized Input Highlights */
    .imei-input-box {
      background-color: #fffbeb !important;
      border-color: #fcd34d !important;
      color: #78350f !important;
      font-weight: 600;
    }

    .imei-input-box:focus {
      border-color: #f59e0b !important;
      box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.2) !important;
    }

    /* Compact Data Tables */
    .table-compact {
      font-size: 0.8125rem;
      margin-bottom: 0;
    }

    .table-compact th {
      background-color: #f8fafc;
      color: #64748b;
      font-weight: 700;
      text-transform: uppercase;
      font-size: 0.6875rem;
      letter-spacing: 0.03em;
      padding: 0.6rem 0.75rem;
      border-bottom: 1px solid var(--spencersz-border);
    }

    .table-compact td {
      padding: 0.6rem 0.75rem;
      vertical-align: middle;
      border-bottom: 1px solid #f1f5f9;
    }

    .table-container {
      max-height: 300px;
      overflow-y: auto;
    }

    /* Payment Summary Block Styling */
    .totals-dark-card {
      background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
      color: #ffffff;
      border-radius: 0.75rem;
      padding: 1rem;
      box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
    }

    .totals-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 0.4rem;
      font-size: 0.8125rem;
      color: #94a3b8;
    }

    .totals-row.grand-total {
      margin-top: 0.5rem;
      padding-top: 0.5rem;
      border-top: 1px solid #334155;
      color: #ffffff;
    }

    /* Buttons & Badges */
    .btn-spencersz {
      background-color: var(--spencersz-primary);
      color: #ffffff;
      border: 1px solid var(--spencersz-primary);
      font-weight: 600;
      border-radius: 0.5rem;
      padding: 0.45rem 0.85rem;
      transition: all 0.15s ease-in-out;
    }

    .btn-spencersz:hover, .btn-spencersz:focus {
      background-color: var(--spencersz-primary-dark);
      color: #ffffff;
      border-color: var(--spencersz-primary-dark);
    }

    .btn-outline-spencersz {
      color: var(--spencersz-primary);
      border-color: var(--spencersz-primary);
      font-weight: 600;
      background: transparent;
      border-radius: 0.5rem;
    }

    .btn-outline-spencersz:hover {
      background-color: var(--spencersz-primary);
      color: #ffffff;
    }

    .shortcut-badge {
      background: rgba(255, 255, 255, 0.2);
      font-size: 0.65rem;
      padding: 0.1rem 0.35rem;
      border-radius: 0.25rem;
      margin-left: 0.3rem;
    }

    /* Search Autocomplete Suggestions */
    .search-autocomplete-box {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: white;
      border: 1px solid var(--spencersz-border);
      border-radius: 0.5rem;
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
      z-index: 1050;
      display: none;
      max-height: 220px;
      overflow-y: auto;
    }

    .autocomplete-item {
      padding: 0.5rem 0.75rem;
      cursor: pointer;
      border-bottom: 1px solid #f1f5f9;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .autocomplete-item:hover {
      background-color: var(--spencersz-primary-light);
    }

    /* Print styling rules */
    @media print {
      body * {
        visibility: hidden;
      }
      #printableInvoiceArea, #printableInvoiceArea * {
        visibility: visible;
      }
      #printableInvoiceArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
      }
    }
</style>
<?php
include "include/topnavbar.php"; 
?>
<div id="layoutSidenav">
    <div id="layoutSidenav_nav">
        <?php include "include/menubar.php"; ?>
    </div>
    <div id="layoutSidenav_content">
        <main>
            <div class="page-header page-header-light bg-white shadow">
                <div class="container-fluid d-flex align-items-center">
                    <div class="page-header-content py-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i class="fas fa-file-invoice"></i></div>
                            <span>Direct Sale / Showroom Invoicing</span>
                        </h1>
                        <span class="text-muted small"><?php echo $_SESSION['companyname']; ?> | <?php echo $_SESSION['branchname']; ?></span>
                    </div>
                    <div class="d-flex ml-auto gap-2">
                        <button class="btn btn-sm btn-light text-dark mr-2" onclick="focusBarcode()"><i class="fas fa-keyboard text-danger mr-1"></i> Hotkeys [F2/F3/F10]</button>
                        <button class="btn btn-sm btn-outline-light" onclick="clearInvoiceCart()"><i class="fas fa-undo mr-1"></i> Reset Cart [Esc]</button>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2">
                <div class="row g-2">
                    <div class="col-lg-7">
                        <!-- 1. Customer & Invoice Info Card -->
                        <div class="card-compact">
                            <div class="card-header-compact">
                                <span><i class="fas fa-id-card me-r text-danger"></i> Customer & Invoice Info</span>
                                <button class="btn btn-spencersz btn-sm py-0 px-2" style="font-size: 0.72rem;"
                                    data-bs-toggle="modal" data-bs-target="#newCustomerModal">
                                    <i class="fas fa-user-plus mr-1"></i> New Customer
                                </button>
                            </div>
                            <div class="card-body-compact">
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label mb-1 text-muted fw-semibold">NIC / Mobile Search [F2]</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="custSearchInput" class="form-control" value="921840291V"
                                                placeholder="Type NIC or Phone..." oninput="lookupCustomer()">
                                            <button class="btn btn-outline-secondary" type="button"
                                                onclick="lookupCustomer()"><i class="fa-solid fa-magnifying-glass"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label mb-1 text-muted fw-semibold">Customer Name</label>
                                        <input type="text" id="custNameInput"
                                            class="form-control form-control-sm bg-light fw-bold text-dark"
                                            value="K. M. Dinesh Perera" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label mb-1 text-muted fw-semibold">Invoice Type</label>
                                        <select class="form-select form-select-sm fw-bold" id="invoiceTypeSelect">
                                            <option value="Retail Tax Invoice (VAT)" selected>Retail Tax Invoice (VAT)</option>
                                            <option value="Hire Purchase (HP)">Hire Purchase (HP Scheme)</option>
                                            <option value="Corporate Quotation">Corporate Quotation</option>
                                        </select>
                                    </div>
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
<script>
$(document).ready(function() {
    $('#sidebarToggle').click();
});
</script>
<?php include "include/footer.php"; ?>