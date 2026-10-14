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
		font-size: 0.8125rem;
		/* Compact 13px baseline for high UI density */
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
		box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.03);
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
		box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
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

	.form-control,
	.form-select {
		font-size: 0.8125rem;
		padding: 0.42rem 0.65rem;
		border-radius: 0.5rem;
		border-color: #cbd5e1;
	}

	.form-control:focus,
	.form-select:focus {
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
		box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
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
		/* padding: 0.45rem 0.85rem; */
		transition: all 0.15s ease-in-out;
	}

	.btn-spencersz:hover,
	.btn-spencersz:focus {
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
		box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
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

		#printableInvoiceArea,
		#printableInvoiceArea * {
			visibility: visible;
		}

		#printableInvoiceArea {
			position: absolute;
			left: 0;
			top: 0;
			width: 100%;
		}
	}

	.card-compact.allow-overflow {
		overflow: visible;
		position: relative;
		z-index: 1030;
	}

	.search-autocomplete-box {
		margin-top: 2px;
	}

	.bg-spencersz-primary {
		background-color: var(--spencersz-primary) !important;
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
						<span class="text-muted small"><?php echo $_SESSION['companyname']; ?> |
							<?php echo $_SESSION['branchname']; ?></span>
					</div>
					<div class="d-flex ml-auto gap-2">
						<button class="btn btn-sm btn-light text-dark mr-2" onclick="focusBarcode()"><i
								class="fas fa-keyboard text-danger mr-1"></i> Hotkeys [F2/F3/F10]</button>
						<button class="btn btn-sm btn-outline-light" onclick="clearInvoiceCart()"><i
								class="fas fa-undo mr-1"></i> Reset Cart [Esc]</button>
					</div>
				</div>
			</div>
			<div class="container-fluid mt-2 p-0 p-2">
				<div class="row g-2">
					<div class="col-lg-7">
						<!-- LEFT COLUMN (Customer, Product Search, Cart) -->
						<!-- 1. Customer & Invoice Info Card -->
						<div class="card-compact">
							<div class="card-header-compact">
								<span><i class="fas fa-id-card mr-r text-danger"></i> Customer & Invoice Info</span>
								<button class="btn btn-spencersz btn-sm" style="font-size: 0.72rem;"
									data-toggle="modal" data-target="#newCustomerModal">
									<i class="fas fa-user-plus mr-1"></i> New Customer
								</button>
							</div>
							<div class="card-body-compact">
								<div class="row g-2">
									<div class="col-md-4">
										<label class="form-label mb-1 text-muted fw-semibold">NIC / Mobile Search
											[F2]</label>
										<div class="input-group input-group-sm">
											<input type="text" id="custSearchInput" class="form-control"
												value="" placeholder="Type NIC or Phone...">
											<div class="input-group-append">
												<button class="btn btn-spencersz" id="searchCustomer" type="button"><i class="fas fa-search"></i></button>
											</div>
										</div>
									</div>
									<div class="col-md-4">
										<label class="form-label mb-1 text-muted fw-semibold">Customer Name</label>
										<input type="text" id="custNameInput"
											class="form-control form-control-sm bg-light fw-bold text-dark"
											value="" placeholder="Customer Name" readonly>
									</div>
									<div class="col-md-4">
										<label class="form-label mb-1 text-muted fw-semibold">Invoice Type</label>
										<select class="form-control form-control-sm fw-bold" id="invoiceTypeSelect">
											<option value="Retail Tax Invoice (VAT)" selected>Retail Tax Invoice (VAT)
											</option>
											<option value="Hire Purchase (HP)">Hire Purchase (HP Scheme)</option>
											<option value="Corporate Quotation">Corporate Quotation</option>
										</select>
									</div>
                                    <input type="hidden" id="custIDInput" value="">
								</div>
							</div>
						</div>
						<!-- 2. Product Search & Serial Input Card -->
						<div class="card-compact allow-overflow">
							<div class="card-body-compact">
								<div class="row g-2">
									<div class="col-md-6 position-relative">
										<label class="form-label mb-1 text-muted fw-semibold"><i
												class="fas fa-barcode mr-1 text-danger"></i> Barcode / SKU Search
											[F3]</label>
										<div class="input-group input-group-sm">
											<input type="text" id="barcodeInput" class="form-control border-danger"
												placeholder="Scan barcode or search model..."
												oninput="handleLiveSearch(this.value)"
												onkeydown="handleBarcodeKeyDown(event)">
											<div class="input-group-append">
												<button class="btn btn-spencersz" type="button"
													onclick="openCatalogModal()"><i class="fas fa-list"></i></button>
											</div>
										</div>

										<!-- Autocomplete Dropdown Box -->
										<div class="search-autocomplete-box" id="autocompleteBox"></div>
									</div>

									<div class="col-md-6 position-relative">
                                        <label class="form-label mb-1 text-warning-emphasis fw-bold"><i
                                                class="fas fa-qrcode mr-1"></i> Serial No / IMEI Capture
                                            [Mandatory]</label>
                                        <input type="text" id="imeiInput"
                                            class="form-control form-control-sm imei-input-box"
                                            placeholder="Select a product first..."
                                            autocomplete="off"
                                            oninput="showSerialList(this.value)"
                                            onkeydown="handleImeiAttach(event)">
                                        <div class="search-autocomplete-box" id="serialBox"></div>
                                    </div>
								</div>
							</div>
						</div>

						<!-- 3. Cart Items Table Card -->
						<div class="card-compact">
							<div class="card-header-compact">
								<span><i class="fas fa-dolly-flatbed mr-1 text-danger"></i> Line Items in Cart</span>
								<span class="badge bg-danger text-light" id="cartCountBadge">2 Items</span>
							</div>
							<div class="table-container">
								<table class="table table-hover table-compact align-middle">
									<thead>
										<tr>
											<th style="width: 5%;">#</th>
											<th style="width: 42%;">Item Description & Serial</th>
											<th style="width: 15%;">Warranty</th>
											<th style="width: 13%;" class="text-end">Unit Price</th>
											<th style="width: 10%;" class="text-center">Qty</th>
											<th style="width: 15%;" class="text-end">Total (LKR)</th>
											<th style="width: 5%;"></th>
										</tr>
									</thead>
									<tbody id="cartTableBody">
										<!-- Dynamically Rendered -->
									</tbody>
								</table>
							</div>
							<div class="p-2 bg-light border-top d-flex justify-content-between align-items-center text-muted"
								style="font-size: 0.72rem;">
								<span><i class="fas fa-lightbulb text-warning mr-1"></i> Click quantity buttons to edit
									line amounts.</span>
								<span class="fw-bold">All Prices Include 18% VAT</span>
							</div>
						</div>
					</div>
					<div class="col-lg-5">
						<!-- RIGHT COLUMN (Fulfillment, Totals, Split Payment) -->

						<!-- Fulfillment Option -->
						<div class="card-compact">
							<div class="card-body-compact py-2 d-flex justify-content-between align-items-center">
								<span class="fw-bold text-muted"><i class="fas fa-truck-loading mr-1 text-danger"></i>
									Fulfillment:</span>
								<div class="d-flex gap-3">
									<div class="form-check form-check-inline mb-0">
										<input class="form-check-input" type="radio" name="fulfillmentRadio"
											id="pickupRadio" value="Store Pickup" checked>
										<label class="form-check-label fw-semibold" for="pickupRadio">Store
											Pickup</label>
									</div>
									<div class="form-check form-check-inline mb-0">
										<input class="form-check-input" type="radio" name="fulfillmentRadio"
											id="deliveryRadio" value="Showroom Delivery">
										<label class="form-check-label fw-semibold" for="deliveryRadio">Showroom
											Delivery</label>
									</div>
								</div>
							</div>
						</div>

						<!-- Totals Dark Crimson Card -->
						<div class="totals-dark-card mb-2">
							<div class="totals-row">
								<span>Subtotal Gross:</span>
								<span class="fw-semibold font-monospace" id="subtotalDisplay">LKR 0.00</span>
							</div>
							<div class="totals-row">
								<span>Showroom Promo Discount:</span>
								<div class="d-flex align-items-center gap-1">
									<span class="text-danger">- LKR</span>
									<input type="number" id="discountInput"
										class="form-control form-control-sm text-end p-0 px-1 border-0 bg-dark text-white font-monospace"
										style="width: 80px;" value="10000" oninput="recalculateInvoice()">
								</div>
							</div>
							<div class="totals-row">
								<span>VAT Component (18% Included):</span>
								<span class="font-monospace" id="vatDisplay">LKR 0.00</span>
							</div>
							<div class="totals-row grand-total align-items-center">
								<span class="fw-bold text-warning fs-6">NET TOTAL DUE:</span>
								<span class="fw-black fs-4 text-warning font-monospace" id="netTotalDisplay">LKR
									0.00</span>
							</div>
						</div>

						<!-- Multi-Pay Breakdown Module -->
						<div class="card-compact">
							<div class="card-header-compact">
								<span><i class="fas fa-wallet mr-1 text-danger"></i> Payment Lines Breakdown</span>
								<button class="btn btn-link text-danger p-0 fw-bold text-decoration-none"
									style="font-size: 0.72rem;" onclick="addPaymentRow()">
									<i class="fas fa-plus mr-1"></i> Add Mode
								</button>
							</div>
							<div class="card-body-compact">
                                <div id="paymentLinesContainer"></div>

                                <!-- Payment Balance Summary -->
                                <div class="p-2 rounded bg-light border fw-bold mt-2" style="font-size: 0.75rem;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-success">Total Entered: <span id="totalEnteredDisplay">LKR 0.00</span></span>
                                        <span class="text-muted" id="balanceRemainingDisplay">Balance: LKR 0.00</span>
                                    </div>
                                    <div id="changeDueDisplay" class="text-primary mt-1 text-end" style="display:none;"></div>
                                </div>
                            </div>
						</div>

						<!-- Quick Action Hotkeys & Submit -->
						<div class="d-flex flex-column gap-2 mt-2">
							<div class="row g-2 mb-2">
								<div class="col-4">
									<button class="btn btn-light border w-100 py-2 fw-bold text-dark text-center"
										style="font-size: 0.75rem;" onclick="holdInvoiceDraft()">
										[F6] Hold Bill
										<small class="d-block text-muted fw-normal" style="font-size: 0.65rem;">Save
											Draft</small>
									</button>
								</div>
								<div class="col-4">
									<button
										class="btn btn-warning-subtle border border-warning w-100 py-2 fw-bold text-amber-900 text-center"
										style="font-size: 0.75rem;" onclick="printQuotation()">
										[F8] Quotation
										<small class="d-block text-warning-emphasis fw-normal"
											style="font-size: 0.65rem;">Print Estimate</small>
									</button>
								</div>
								<div class="col-4">
									<button
										class="btn btn-danger-subtle border border-danger w-100 py-2 fw-bold text-danger text-center"
										style="font-size: 0.75rem;" onclick="clearInvoiceCart()">
										[Esc] Cancel
										<small class="d-block text-danger fw-normal" style="font-size: 0.65rem;">Void
											Bill</small>
									</button>
								</div>
							</div>

							<button
								class="btn btn-spencersz btn-lg w-100 py-3 fw-bold shadow text-uppercase tracking-wider"
								style="font-size: 0.95rem;" onclick="completeTransaction()">
								<i class="fas fa-print mr-2"></i> COMPLETE SALE & PRINT INVOICE <span
									class="shortcut-badge">F10</span>
							</button>
						</div>

					</div>
				</div>
			</div>
		</main>
		<?php include "include/footerbar.php"; ?>
	</div>
</div>
<!-- MODAL 1: NEW CUSTOMER REGISTRATION -->
<div class="modal fade" id="newCustomerModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header bg-spencersz-primary text-white py-2">
				<h6 class="modal-title fw-bold text-light"><i class="fas fa-user-plus me-2"></i>New Customer
					Registration</h6>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"
						class="text-light">&times;</span></button>
			</div>
			<div class="modal-body p-3">
				<form id="newCustForm">
					<div class="mb-2">
						<label class="form-label small fw-semibold mb-1">Full Customer Name *</label>
						<input type="text" id="regCustName" name="regCustName" class="form-control form-control-sm" required placeholder="e.g. Kasun M. Perera">
					</div>
					<div class="row g-2 mb-2">
						<div class="col-6">
							<label class="form-label small fw-semibold mb-1">NIC / Passport Number *</label>
							<input type="text" id="regCustNIC" name="regCustNIC" class="form-control form-control-sm" required placeholder="e.g. 921840291V">
						</div>
						<div class="col-6">
							<label class="form-label small fw-semibold mb-1">Mobile Number *</label>
							<input type="text" id="regCustMobile" name="regCustMobile" class="form-control form-control-sm" required placeholder="e.g. 0771234567">
						</div>
					</div>
					<div class="row g-2 mb-2">
						<div class="col-6">
							<label class="form-label small fw-semibold mb-1">Address 1 *</label>
							<input type="text" id="regCustAddress1" name="regCustAddress1" class="form-control form-control-sm" required placeholder="No. 45, Galle Road">
						</div>
						<div class="col-6">
							<label class="form-label small fw-semibold mb-1">Address 2</label>
							<input type="text" id="regCustAddress2" name="regCustAddress2" class="form-control form-control-sm" placeholder="e.g. Colombo 03">
						</div>
					</div>
					<div class="row g-2 mb-3">
						<div class="col-6">
							<label class="form-label small fw-semibold mb-1">City</label>
							<input type="text" id="regCustCity" name="regCustCity" class="form-control form-control-sm" placeholder="Colombo 03">
						</div>
						<div class="col-6">
							<label class="form-label small fw-semibold mb-1">VAT Information</label>
							<input type="text" id="regCustVAT" name="regCustVAT" class="form-control form-control-sm" placeholder="e.g. VAT Number">
						</div>
					</div>
					<div class="d-flex justify-content-end gap-2 border-top pt-2">
						<button type="button" id="btnsubmitcustomer" class="btn btn-spencersz btn-sm">Save & Select Customer</button>
						<input type="submit" class="d-none" id="hidecustomerSubmitBtn">
						<input type="reset" class="d-none" id="hidecustomerResetBtn">
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<!-- MODAL 2: INVENTORY CATALOG MODAL -->
<div class="modal fade" id="catalogModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header bg-danger text-white py-2">
				<h6 class="modal-title fw-bold text-light"><i class="fas fa-list mr-2"></i>Product Catalog Inventory</h6>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" class="text-light">&times;</span></button>
			</div>
			<div class="modal-body p-3">
				<input type="text" id="catalogSearchFilter" class="form-control mb-3"
					placeholder="Filter by product name or SKU..." oninput="renderCatalogModalTable()">
				<div class="table-responsive">
					<table class="table table-hover table-striped table-sm align-middle nowrap" id="dataTableCatalog">
						<thead>
							<tr>
								<th>SKU</th>
								<th>Product Title</th>
								<th>Warranty</th>
								<th>Price (LKR)</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody id="catalogModalTableBody">
							<!-- Dynamically Populated -->
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- MODAL 3: INVOICE RECEIPT PREVIEW -->
<div class="modal fade" id="receiptPreviewModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header bg-dark py-2">
				<h6 class="modal-title fw-bold text-white"><i class="fas fa-receipt mr-2"></i>Spencersz Tax Invoice Preview</h6>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" class="text-light">&times;</span></button>
			</div>
			<div class="modal-body p-4" id="printableInvoiceArea">
				<!-- Receipt Document Header -->
				<div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
					<div>
						<h4 class="fw-bold text-danger mb-0">SPENCERSZ (PVT) LTD</h4>
						<small class="text-muted d-block">124, Main Street, Colombo 11, Sri Lanka</small>
						<small class="text-muted">Tel: +94 11 234 5678 | VAT Reg No: 114589201-7000</small>
					</div>
					<div class="text-end">
						<h5 class="fw-bold mb-0" id="previewInvoiceNo">INV-2026-9041</h5>
						<small class="text-muted d-block" id="previewDate">Date: 08 Oct 2026</small>
						<span class="badge bg-danger" id="previewTypeBadge">RETAIL TAX INVOICE</span>
					</div>
				</div>

				<!-- Customer Info -->
				<div class="row mb-3 small">
					<div class="col-6">
						<strong>Customer Info:</strong>
						<div id="previewCustName" class="fw-bold">K. M. Dinesh Perera</div>
						<div id="previewCustNIC">NIC: 921840291V</div>
						<div>Fulfillment: <span id="previewFulfillment">Store Pickup</span></div>
					</div>
					<div class="col-6 text-end">
						<strong>Terminal Info:</strong>
						<div>Terminal #01 (Main Showroom)</div>
						<div>Cashier: Super Administrator</div>
					</div>
				</div>

				<!-- Items Table -->
				<table class="table table-bordered table-sm small align-middle mb-3">
					<thead class="table-light">
						<tr>
							<th>#</th>
							<th>Item Description & Serial / IMEI</th>
							<th>Qty</th>
							<th>Unit Price</th>
							<th class="text-end">Total (LKR)</th>
						</tr>
					</thead>
					<tbody id="previewTableBody">
						<!-- Rendered -->
					</tbody>
				</table>

				<!-- Financial Calculation Totals -->
				<div class="row justify-content-end mb-3">
					<div class="col-6">
						<table class="table table-sm table-borderless small mb-0">
							<tr>
								<td>Gross Total:</td>
								<td class="text-end" id="previewSubtotal">0.00</td>
							</tr>
							<tr>
								<td>Discount Applied:</td>
								<td class="text-end" id="previewDiscount">0.00</td>
							</tr>
							<tr>
								<td>VAT Component (18%):</td>
								<td class="text-end" id="previewVat">0.00</td>
							</tr>
							<tr class="border-top fw-bold fs-6">
								<td>NET TOTAL DUE:</td>
								<td class="text-end text-danger" id="previewNetTotal">0.00</td>
							</tr>
						</table>
					</div>
				</div>

                <!-- Payment details -->
                <div class="mb-3" id="previewPaymentArea">
                    <strong class="small">Payment Details:</strong>
                    <table class="table table-bordered table-sm small mb-1">
                        <tbody id="previewPaymentBody"></tbody>
                    </table>
                    <div class="d-flex justify-content-between small fw-bold">
                        <span>Balance Due: LKR <span id="previewBalance">0.00</span></span>
                        <span id="previewChangeRow">Change Returned: LKR <span id="previewChange">0.00</span></span>
                    </div>
                </div>

				<div class="border-top pt-2 text-center text-muted small">
					<p class="mb-0 fw-semibold">Thank you for shopping with Spencersz (Pvt) Ltd!</p>
				</div>
			</div>
			<div class="modal-footer py-2">
				<button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
				<button type="button" id="btnSaveInvoice" class="btn btn-spencersz btn-sm" onclick="saveInvoice()">
                    <i class="fas fa-save me-1"></i> Save & Print
                </button>
			</div>
		</div>
	</div>
</div>
<?php include "include/footerscripts.php"; ?>
<script>
	$(document).ready(function () {
		$('#sidebarToggle').click();
		$('#btnsubmitcustomer').click(function () {
			if (!$("#newCustForm")[0].checkValidity()) {
				// If the form is invalid, submit it. The form won't actually submit;
				// this will just cause the browser to display the native HTML5 error messages.
				$("#hidecustomerSubmitBtn").click();
			} else {
                const formData = $('#newCustForm').serialize();
                let custName = $('#regCustName').val();
                let regCustNIC = $('#regCustNIC').val();

                console.log($('#newCustForm').serialize());

                $.ajax({
                    url: '<?php echo base_url(); ?>Invoice/SubmitNewCustomer',
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        var obj = JSON.parse(response);
                        if (obj.status == 1) {
                            $('#custSearchInput').val(regCustNIC);
                            $('#custNameInput').val(custName);

                            action(obj.action);
                            $('#newCustomerModal').modal('hide');
                            $('#hidecustomerResetBtn').click();
                        }
                        else{
                            action(obj.action);
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Something went wrong. Please try again later.'
                        });
                    }
                });
			}
		});
        $('#custSearchInput').keydown(function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                $('#searchCustomer').click();
            }
        });
        $('#searchCustomer').click(function(){
            var input = $('#custSearchInput')[0];
            var searchValue = input.value.trim();
            if (searchValue === "") {
                input.setCustomValidity('Please enter the NIC number or mobile number');
                input.reportValidity();   // shows the message bubble now
                return;                   // stop here, don't run the search
            }

            input.setCustomValidity('');

            $.ajax({
                url: '<?php echo base_url(); ?>Invoice/SearchCustomer',
                type: 'POST',
                data: { searchValue: searchValue },
                success: function (response) {
                    var obj = JSON.parse(response);
                    if (obj.status == 1) {
                        $('#custNameInput').val(obj.customer_name);
                        $('#custSearchInput').val(obj.customer_nic);
                        $('#custIDInput').val(obj.customer_id);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Customer Not Found',
                            text: 'No customer found with the provided NIC or mobile number.'
                        });
                        $('#custNameInput').val('');
                    }
                },
                error: function (xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong while searching for the customer. Please try again later.'
                    });
                }
            });
        });
        
        loadProductlist();
	});
</script>
<script>
	// Database Mock
	const productCatalog = [];
	const PAYMENT_MODES = [];

    function loadProductlist() {
        $.ajax({
            url: '<?php echo base_url(); ?>Invoice/Getproductdetails',
            type: 'GET',
            success: function (response) {
                var obj = JSON.parse(response);
                if (obj.status == 1) {
                    productCatalog.length = 0; // Clear existing catalog
                    obj.products.forEach(function (product) {
                        productCatalog.push({
                            id: product.idtbl_product,
                            sku: product.product_code,
                            name: product.product_name,
                            price: parseFloat(product.selling_price),
                            warranty: product.warranty_months + ' Months',
                            warrantymonth: product.warranty_months,
                            serials: product.serials || []
                        });
                    });
                } else {
                    console.error('Failed to load product list:', obj.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error loading product list:', error);
            }
        });
    }

    function loadPaytypelist() {
        $.ajax({
            url: '<?php echo base_url(); ?>Invoice/Getpaymenttypelist',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.status == 1) {
                    PAYMENT_MODES.length = 0; // Clear existing array
                    
                    // Temporary object to group items by group name
                    var groupedMap = {};
                    
                    response.data.forEach(function (row) {
                        var groupName = row.paygroup;
                        var groupType = row.group_type;
                        var payTypeName = row.paytype;
                        
                        if (!groupedMap[groupName]) {
                            groupedMap[groupName] = {
                                group: groupName,
                                type: groupType,
                                items: []
                            };
                        }
                        
                        // Push the item name into the group's items array
                        groupedMap[groupName].items.push(payTypeName);
                    });
                    
                    // Convert the grouped object back into an array
                    for (var key in groupedMap) {
                        PAYMENT_MODES.push(groupedMap[key]);
                    }
                    
                    resetPayments();
                    // console.log('Recreated PAYMENT_MODES:', PAYMENT_MODES);
                } else {
                    console.error('Failed to load payment type list');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error loading payment type list:', error);
            }
        });
    }

	// Active Cart State
	let cart = [];
    let pendingProduct = null; // product waiting for a serial

	document.addEventListener("DOMContentLoaded", function () {
		renderCartTable();
        loadPaytypelist();
        // addPaymentRow('Cash');
		setupKeyboardHotkeys();
	});

    document.addEventListener("click", function (e) {
        if (!e.target.closest("#serialBox, #imeiInput")) document.getElementById("serialBox").style.display = "none";
        if (!e.target.closest("#autocompleteBox, #barcodeInput")) document.getElementById("autocompleteBox").style.display = "none";
    });

    document.getElementById("receiptPreviewModal").addEventListener("hidden.bs.modal", function () {
        document.getElementById("invoiceTypeSelect").value = "Retail Tax Invoice (VAT)";
    });

	// Cart Rendering & Operations
	function renderCartTable() {
		const tbody = document.getElementById("cartTableBody");
		tbody.innerHTML = "";

		if (cart.length === 0) {
			tbody.innerHTML =
				`<tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-basket-shopping fs-3 mb-2 d-block"></i>Invoice cart is empty. Scan barcode or SKU.</td></tr>`;
			document.getElementById("cartCountBadge").innerText = "0 Items";
			recalculateInvoice();
			return;
		}

		let totalQtyCount = 0;

		cart.forEach((item, index) => {
			totalQtyCount += item.qty;
			const row = document.createElement("tr");
			row.innerHTML = `
                <td class="text-muted">${index + 1}</td>
                <td>
                <div class="fw-bold text-dark">${item.name}</div>
                <div class="text-warning-emphasis font-monospace" style="font-size: 0.72rem;"><i class="fas fa-microchip me-1"></i>${item.imei || "PENDING SCAN"}</div>
                </td>
                <td><span class="badge bg-success text-light" style="font-size: 0.65rem;">${item.warranty}</span></td>
                <td class="text-end font-monospace">${item.price.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td class="text-center">
                <div class="input-group input-group-sm mx-auto" style="width: 80px;">
                    <div class="input-group-prepend">
                    <button class="btn btn-light p-0 px-1" onclick="changeQty(${index}, -1)">-</button>
                    </div>
                    <input type="text" class="form-control text-center p-0 fw-bold border-light" value="${item.qty}" readonly>
                    <div class="input-group-append">
                    <button class="btn btn-light p-0 px-1" onclick="changeQty(${index}, 1)">+</button>
                    </div>
                </div>
                </td>
                <td class="text-end fw-bold font-monospace">${(item.price * item.qty).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td class="text-center">
                <button class="btn btn-sm text-danger p-0" onclick="removeCartItem(${index})"><i class="fas fa-trash"></i></button>
                </td>
                <td class="d-none">${item.id}</td>
                <td class="d-none">${item.warrantymonth}</td>
                <td class="d-none">${item.price}</td>
                <td class="d-none">${item.serialno}</td>
            `;
			tbody.appendChild(row);
		});

		document.getElementById("cartCountBadge").innerText = totalQtyCount + " Items";
		recalculateInvoice();
	}

	function changeQty(index, delta) {
        if (cart[index].serialId && delta > 0) return; // one serial = one unit
		cart[index].qty += delta;
		if (cart[index].qty <= 0) {
			cart.splice(index, 1);
		}
		renderCartTable();
	}

	function removeCartItem(index) {
		cart.splice(index, 1);
		renderCartTable();
	}

	// Live Search Autocomplete & Barcode Input
	function handleLiveSearch(val) {
		const box = document.getElementById("autocompleteBox");
		const query = val.toLowerCase().trim();
		if (!query) {
			box.style.display = "none";
			return;
		}

		const matches = productCatalog.filter(p => p.name.toLowerCase().includes(query) || p.sku.toLowerCase().includes(query));
		if (matches.length === 0) {
			box.style.display = "none";
			return;
		}

		box.innerHTML = "";
		matches.forEach(m => {
			const item = document.createElement("div");
			item.className = "autocomplete-item";
			item.innerHTML = `
                <div>
                <div class="fw-bold" style="font-size:0.78rem;">${m.name}</div>
                <small class="text-muted">${m.sku}</small>
                </div>
                <span class="fw-bold text-danger font-monospace" style="font-size:0.78rem;">LKR ${m.price.toLocaleString()}</span>
            `;
			// item.onclick = () => {
			// 	addItemToCart(m);
			// 	box.style.display = "none";
			// 	document.getElementById("barcodeInput").value = "";
			// };
            item.onclick = () => {
                selectProduct(m);
                document.getElementById("barcodeInput").value = "";
            };
			box.appendChild(item);
		});
		box.style.display = "block";
	}

	function handleBarcodeKeyDown(e) {
		if (e.key === 'Enter') {
			e.preventDefault();
			const val = e.target.value.trim().toLowerCase();
			const match = productCatalog.find(p => p.sku.toLowerCase() === val || p.name.toLowerCase().includes(val));
			// if (match) {
			// 	addItemToCart(match);
			// 	e.target.value = "";
			// 	document.getElementById("autocompleteBox").style.display = "none";
			// }
            if (match) {
                selectProduct(match);
                e.target.value = "";
                document.getElementById("autocompleteBox").style.display = "none";
            }
		}
	}

	// function handleImeiAttach(e) {
	// 	if (e.key === 'Enter') {
	// 		e.preventDefault();
	// 		const imeiVal = e.target.value.trim();
	// 		if (cart.length > 0 && imeiVal) {
	// 			cart[cart.length - 1].imei = "S/N: " + imeiVal;
	// 			renderCartTable();
	// 			e.target.value = "";
	// 		}
	// 	}
	// }

	// function addItemToCart(prod) {
	// 	const existing = cart.find(c => c.sku === prod.sku);
	// 	if (existing) {
	// 		existing.qty++;
	// 	} else {
	// 		cart.push({
	// 			...prod,
	// 			qty: 1,
	// 			imei: "S/N: " + Math.floor(10000000 + Math.random() * 90000000)
	// 		});
	// 	}
	// 	renderCartTable();

    //     $('#imeiInput').focus();
	// }

	function openCatalogModal() {
		const modal = new bootstrap.Modal(document.getElementById("catalogModal"));
		renderCatalogModalTable();
		modal.show();
	}

	function renderCatalogModalTable() {
		const filter = (document.getElementById("catalogSearchFilter").value || "").toLowerCase();
		const tbody = document.getElementById("catalogModalTableBody");
		tbody.innerHTML = "";

		productCatalog.filter(p => p.name.toLowerCase().includes(filter) || p.sku.toLowerCase().includes(filter)).forEach(
        p => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td class="fw-bold">${p.sku}</td>
                <td>${p.name}</td>
                <td><span class="badge bg-success text-white border">${p.warranty}</span></td>
                <td class="fw-bold font-monospace">LKR ${p.price.toLocaleString()}</td>
                <td class="text-center">
                <button class="btn btn-spencersz btn-sm" onclick="addItemFromCatalog('${p.sku}')"><i class="fas fa-plus"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
	}

	function addItemFromCatalog(sku) {
		const prod = productCatalog.find(p => p.sku === sku);
		if (prod) {
			// addItemToCart(prod);
            selectProduct(prod);
			const modalEl = document.getElementById("catalogModal");
			const modal = bootstrap.Modal.getInstance(modalEl);
			if (modal) modal.hide();
		}
	}

	// Totals & Split Payments Calculations
	function recalculateInvoice() {
		const grossSubtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
		const discount = parseFloat(document.getElementById("discountInput").value) || 0;
		const netTotal = Math.max(0, grossSubtotal - discount);

		const vatComponent = netTotal - (netTotal / 1.18);

		document.getElementById("subtotalDisplay").innerText = "LKR " + grossSubtotal.toLocaleString('en-US', {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		});
		document.getElementById("vatDisplay").innerText = "LKR " + vatComponent.toLocaleString('en-US', {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		});
		document.getElementById("netTotalDisplay").innerText = "LKR " + netTotal.toLocaleString('en-US', {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		});

		calculateSplitPayments();
	}

	// function addPaymentRow() {
	// 	const container = document.getElementById("paymentLinesContainer");
	// 	const row = document.createElement("div");
	// 	row.className = "row g-2 mb-2 align-items-center payment-line";
	// 	row.innerHTML = `
    //     <div class="col-6">
    //         <select class="form-select form-select-sm fw-semibold pay-mode" onchange="calculateSplitPayments()">
    //         <option value="Cash">Cash</option>
    //         <option value="Commercial Bank Credit Card">Commercial Bank Credit Card</option>
    //         <option value="Sampath 0% Installment (24M)">Sampath 0% Installment (24M)</option>
    //         </select>
    //     </div>
    //     <div class="col-5">
    //         <input type="number" class="form-control form-control-sm text-end font-monospace fw-bold pay-amount" value="0" oninput="calculateSplitPayments()">
    //     </div>
    //     <div class="col-1 p-0 text-center">
    //         <button class="btn btn-sm text-danger p-0" onclick="removePaymentRow(this)"><i class="fas fa-xmark"></i></button>
    //     </div>
    //     `;
	// 	container.appendChild(row);
	// 	calculateSplitPayments();
	// }

	function removePaymentRow(btn) {
		const rows = document.querySelectorAll(".payment-line");
		if (rows.length > 1) {
			btn.closest(".payment-line").remove();
			calculateSplitPayments();
		}
	}

	// function calculateSplitPayments() {
	// 	const netTotalText = document.getElementById("netTotalDisplay").innerText.replace("LKR ", "").replace(/,/g, "");
	// 	const netTotal = parseFloat(netTotalText) || 0;

	// 	let totalEntered = 0;
	// 	document.querySelectorAll(".pay-amount").forEach(inp => {
	// 		totalEntered += parseFloat(inp.value) || 0;
	// 	});

	// 	const balance = netTotal - totalEntered;

	// 	document.getElementById("totalEnteredDisplay").innerText = "LKR " + totalEntered.toLocaleString('en-US', {
	// 		minimumFractionDigits: 2,
	// 		maximumFractionDigits: 2
	// 	});

	// 	const balDisplay = document.getElementById("balanceRemainingDisplay");
	// 	if (balance <= 0) {
	// 		balDisplay.innerText = "Balance: LKR " + Math.abs(balance).toLocaleString('en-US', {
	// 			minimumFractionDigits: 2,
	// 			maximumFractionDigits: 2
	// 		}) + " (Paid)";
	// 		balDisplay.className = "text-success fw-bold";
	// 	} else {
	// 		balDisplay.innerText = "Balance: LKR " + balance.toLocaleString('en-US', {
	// 			minimumFractionDigits: 2,
	// 			maximumFractionDigits: 2
	// 		});
	// 		balDisplay.className = "text-danger fw-bold";
	// 	}
	// }

	function completeTransaction() {
		if (cart.length === 0) {
			alert("Cannot print invoice for an empty cart.");
			return;
		}

        const payErr = validatePayments();
        if (payErr) { alert(payErr); return; }

		document.getElementById("previewCustName").innerText = document.getElementById("custNameInput").value;
		document.getElementById("previewCustNIC").innerText = "NIC: " + document.getElementById("custSearchInput").value;
		document.getElementById("previewTypeBadge").innerText = document.getElementById("invoiceTypeSelect").value
			.toUpperCase();
		document.getElementById("previewFulfillment").innerText = document.querySelector(
			'input[name="fulfillmentRadio"]:checked').value;

		const tbody = document.getElementById("previewTableBody");
		tbody.innerHTML = "";

		let gross = 0;
		cart.forEach((item, index) => {
			const itemTotal = item.price * item.qty;
			gross += itemTotal;
			const tr = document.createElement("tr");
			tr.innerHTML = `
                <td>${index + 1}</td>
                <td>
                <div class="fw-bold">${item.name}</div>
                <small class="text-muted">${item.imei || ""}</small>
                </td>
                <td>${item.qty}</td>
                <td>${item.price.toLocaleString()}</td>
                <td class="text-end">${itemTotal.toLocaleString()}</td>
            `;
			tbody.appendChild(tr);
		});

		const discount = parseFloat(document.getElementById("discountInput").value) || 0;
		const net = Math.max(0, gross - discount);
		const vat = net - (net / 1.18);

		document.getElementById("previewSubtotal").innerText = gross.toLocaleString('en-US', {
			minimumFractionDigits: 2
		});
		document.getElementById("previewDiscount").innerText = discount.toLocaleString('en-US', {
			minimumFractionDigits: 2
		});
		document.getElementById("previewVat").innerText = vat.toLocaleString('en-US', {
			minimumFractionDigits: 2
		});
		document.getElementById("previewNetTotal").innerText = net.toLocaleString('en-US', {
			minimumFractionDigits: 2
		});

        // ---- Payment summary in the receipt ----
        const isQuote = document.getElementById("invoiceTypeSelect").value === "Corporate Quotation";
        document.getElementById("previewPaymentArea").style.display = isQuote ? "none" : "block";

        if (!isQuote) {
            const pb = document.getElementById("previewPaymentBody");
            pb.innerHTML = "";
            let paid = 0, cash = 0;

            collectPayments().forEach(p => {
                paid += p.amount;
                if (p.type === 'cash') cash += p.amount;

                let detail = "";
                if (p.type === 'card') detail = "**** " + p.ref + " | Auth " + p.ref2;
                else if (p.type === 'installment') detail = p.months + " Months | **** " + p.ref + " | Auth " + p.ref2;
                else if (p.type === 'cheque') detail = p.bank + " | No " + p.ref + " | " + p.chequeDate;
                else if (p.type === 'transfer') detail = "Ref " + p.ref;
                else if (p.type === 'voucher') detail = "Voucher " + p.ref;

                const tr = document.createElement("tr");
                const td1 = document.createElement("td");
                td1.textContent = p.mode;
                if (detail) {
                    const sm = document.createElement("small");
                    sm.className = "text-muted d-block";
                    sm.textContent = detail;
                    td1.appendChild(sm);
                }
                const td2 = document.createElement("td");
                td2.className = "text-end";
                td2.textContent = p.amount.toLocaleString('en-US', { minimumFractionDigits: 2 });
                tr.appendChild(td1);
                tr.appendChild(td2);
                pb.appendChild(tr);
            });

            const change = Math.max(0, r2(paid - net));
            const balance = Math.max(0, r2(net - paid));
            document.getElementById("previewChange").innerText = change.toLocaleString('en-US', { minimumFractionDigits: 2 });
            document.getElementById("previewBalance").innerText = balance.toLocaleString('en-US', { minimumFractionDigits: 2 });
            document.getElementById("previewChangeRow").style.display = change > 0 ? "inline" : "none";
        }

		const modal = new bootstrap.Modal(document.getElementById("receiptPreviewModal"));
		modal.show();
	}

    function saveInvoice() {
        const payload = {
            invoiceType: document.getElementById("invoiceTypeSelect").value,
            customerNic: document.getElementById("custSearchInput").value,
            customerName: document.getElementById("custNameInput").value,
            fulfillment: document.querySelector('input[name="fulfillmentRadio"]:checked').value,
            discount: parseFloat(document.getElementById("discountInput").value) || 0,
            items: cart.map(i => ({ id: i.id, serialId: i.serialId || null, qty: i.qty })),
            payments: collectPayments()
        };

        const btn = document.getElementById("btnSaveInvoice");
        btn.disabled = true;

        $.ajax({
            url: '<?php echo base_url(); ?>Invoice/Insertinvoice',
            type: 'POST',
            data: { invoice: JSON.stringify(payload) },
            dataType: 'json',
            success: function (res) {
                if (res.status == 1) {
                    document.getElementById("previewInvoiceNo").innerText = res.invoice_no;
                    document.getElementById("previewDate").innerText = "Date: " +
                        new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

                    window.print();   // blocks until the print dialog closes

                    const m = bootstrap.Modal.getInstance(document.getElementById("receiptPreviewModal"));
                    if (m) m.hide();

                    cart = [];
                    renderCartTable();
                    resetPayments();
                    document.getElementById("invoiceTypeSelect").value = "Retail Tax Invoice (VAT)";
                    loadProductlist();   // sold serials disappear from the list
                } else {
                    alert(res.message || "Could not save the invoice.");
                }
                btn.disabled = false;
            },
            error: function () {
                alert("Server error while saving the invoice.");
                btn.disabled = false;
            }
        });
    }

	function clearInvoiceCart() {
		if (confirm("Clear current invoice transaction?")) {
			cart = [];
			renderCartTable();
		}
	}

	function holdInvoiceDraft() {
		if (cart.length === 0) return;
		alert("Invoice held in Terminal Draft Queue #01.");
		cart = [];
		renderCartTable();
	}

	function printQuotation() {
		if (cart.length === 0) return;
		document.getElementById("invoiceTypeSelect").value = "Corporate Quotation";
		completeTransaction();
	}

	function focusBarcode() {
		document.getElementById("barcodeInput").focus();
	}

	function setupKeyboardHotkeys() {
		document.addEventListener("keydown", function (e) {
			if (e.key === "F2") {
				e.preventDefault();
				document.getElementById("custSearchInput").focus();
			} else if (e.key === "F3") {
				e.preventDefault();
				document.getElementById("barcodeInput").focus();
			} else if (e.key === "F6") {
				e.preventDefault();
				holdInvoiceDraft();
			} else if (e.key === "F8") {
				e.preventDefault();
				printQuotation();
			} else if (e.key === "F10") {
				e.preventDefault();
				completeTransaction();
			} else if (e.key === "Escape") {
				clearInvoiceCart();
			}
		});
	}

    //---------------------- ON 12/10/2026 ----------------------//

    function selectProduct(prod) {
        document.getElementById("autocompleteBox").style.display = "none";

        // Product without serials: add straight to the cart
        if (!prod.serials || prod.serials.length === 0) {
            addItemToCart(prod);
            return;
        }

        pendingProduct = prod;
        const imei = document.getElementById("imeiInput");
        imei.value = "";
        imei.placeholder = "Scan / select serial for: " + prod.name;
        imei.focus();
        showSerialList("");
    }

    function availableSerials(q) {
        if (!pendingProduct) return [];
        q = (q || "").toLowerCase().trim();
        return pendingProduct.serials.filter(s =>
            !cart.some(c => c.serialId == s.idtbl_product_serial) &&
            s.serialno.toLowerCase().includes(q)
        );
    }

    function showSerialList(val) {
        const box = document.getElementById("serialBox");
        if (!pendingProduct) { box.style.display = "none"; return; }

        const list = availableSerials(val);
        if (list.length === 0) { box.style.display = "none"; return; }

        box.innerHTML = "";
        list.forEach(s => {
            const price = parseFloat(s.saleprice) > 0 ? parseFloat(s.saleprice) : pendingProduct.price;
            const item = document.createElement("div");
            item.className = "autocomplete-item";
            item.innerHTML = `
                <div class="fw-bold font-monospace" style="font-size:0.78rem;">${s.serialno}</div>
                <span class="fw-bold text-danger font-monospace" style="font-size:0.78rem;">LKR ${price.toLocaleString()}</span>
            `;
            item.onclick = () => addSerialToCart(pendingProduct, s);
            box.appendChild(item);
        });
        box.style.display = "block";
    }

    function handleImeiAttach(e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();

        const val = e.target.value.trim().toLowerCase();
        if (!val) return;

        if (!pendingProduct) {
            alert("Please select a product first.");
            document.getElementById("barcodeInput").focus();
            return;
        }

        // exact match first, otherwise a single partial match
        let serial = pendingProduct.serials.find(s => s.serialno.toLowerCase() === val);
        if (!serial) {
            const partial = availableSerials(val);
            if (partial.length === 1) serial = partial[0];
        }

        if (!serial) {
            alert("Serial / IMEI not found for this product.");
            return;
        }
        if (cart.some(c => c.serialId == serial.idtbl_product_serial)) {
            alert("This serial is already in the cart.");
            e.target.value = "";
            return;
        }
        addSerialToCart(pendingProduct, serial);
    }

    function addSerialToCart(prod, s) {
        const price = parseFloat(s.saleprice) > 0 ? parseFloat(s.saleprice) : prod.price;

        cart.push({
            id: prod.id,
            sku: prod.sku,
            name: prod.name,
            warranty: prod.warranty,
            warrantymonth: prod.warrantymonth,
            price: price,
            qty: 1,
            serialId: s.idtbl_product_serial,
            serialno: s.serialno,
            costprice: parseFloat(s.costunitprice) || 0,
            imei: "S/N: " + s.serialno,
            serialno: s.serialno
        });

        pendingProduct = null;
        const imei = document.getElementById("imeiInput");
        imei.value = "";
        imei.placeholder = "Select a product first...";
        document.getElementById("serialBox").style.display = "none";

        renderCartTable();
        document.getElementById("barcodeInput").focus();
    }

    // Only for products WITHOUT serials
    function addItemToCart(prod) {
        const existing = cart.find(c => c.sku === prod.sku && !c.serialId);
        if (existing) {
            existing.qty++;
        } else {
            cart.push({ ...prod, qty: 1, imei: "N/A" });
        }
        renderCartTable();
        document.getElementById("barcodeInput").focus();
    }

    // ---------- Payment helpers ----------
    // const PAYMENT_MODES = [
    //     { group: "Cash", type: "cash", items: ["Cash"] },
    //     { group: "Credit / Debit Cards", type: "card", items: [
    //         "Commercial Bank Credit Card", "HNB Credit Card", "Sampath Credit Card", "BOC Credit Card",
    //         "People's Bank Credit Card", "NTB Credit Card", "Seylan Credit Card", "DFCC Credit Card",
    //         "Debit Card (Any Bank)"] },
    //     { group: "0% Installment (Card)", type: "installment", items: [
    //         "Sampath 0% Installment", "Commercial Bank 0% Installment", "HNB 0% Installment",
    //         "BOC 0% Installment", "People's Bank 0% Installment", "NTB 0% Installment",
    //         "Seylan 0% Installment"] },
    //     { group: "Cheque", type: "cheque", items: ["Cheque"] },
    //     { group: "Online / QR / Wallet", type: "transfer", items: [
    //         "Online Bank Transfer", "LANKAQR", "FriMi", "Genie", "eZ Cash"] },
    //     { group: "Gift Voucher", type: "voucher", items: ["Gift Voucher"] }
    // ];

    const r2 = n => Math.round((n + Number.EPSILON) * 100) / 100;
    const fmtLKR = n => "LKR " + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function getNetTotal() {
        const gross = cart.reduce((s, i) => s + i.price * i.qty, 0);
        const disc = parseFloat(document.getElementById("discountInput").value) || 0;
        return Math.max(0, gross - disc);
    }

    function rowAmount(row) {
        const a = parseFloat(row.querySelector('.pay-amount').value) || 0;
        if (row.dataset.type === 'voucher' && row.dataset.voucherOk !== '1') return 0; // unverified voucher doesn't count
        return a;
    }

    function sumPayments(skip) {
        skip = skip || [];
        let t = 0;
        document.querySelectorAll('.payment-line').forEach(r => {
            if (!skip.includes(r)) t += rowAmount(r);
        });
        return t;
    }

    function buildPayModeSelect() {
        const sel = document.createElement('select');
        sel.className = 'form-control form-control-sm fw-semibold pay-mode';
        PAYMENT_MODES.forEach(g => {
            const og = document.createElement('optgroup');
            og.label = g.group;
            g.items.forEach(name => {
                const o = document.createElement('option');
                o.value = name;
                o.textContent = name;
                o.dataset.type = g.type;
                og.appendChild(o);
            });
            sel.appendChild(og);
        });
        return sel;
    }

    function payExtraHtml(type) {
        const inp = 'form-control form-control-sm';
        switch (type) {
            case 'card':
                return `
                    <div class="col-5"><input type="text" class="${inp} pay-ref" maxlength="4" inputmode="numeric" placeholder="Card last 4"></div>
                    <div class="col-7"><input type="text" class="${inp} pay-ref2" placeholder="Approval / Auth code"></div>`;
            case 'installment':
                return `
                    <div class="col-4">
                        <select class="${inp} pay-months">
                            <option value="3">3 M</option><option value="6">6 M</option>
                            <option value="12">12 M</option><option value="18">18 M</option>
                            <option value="24" selected>24 M</option><option value="36">36 M</option>
                        </select>
                    </div>
                    <div class="col-3"><input type="text" class="${inp} pay-ref" maxlength="4" inputmode="numeric" placeholder="Last 4"></div>
                    <div class="col-5"><input type="text" class="${inp} pay-ref2" placeholder="Approval code"></div>`;
            case 'cheque':
                return `
                    <div class="col-6"><input type="text" class="${inp} pay-bank" placeholder="Bank"></div>
                    <div class="col-6"><input type="text" class="${inp} pay-ref" maxlength="6" inputmode="numeric" placeholder="Cheque No"></div>
                    <div class="col-12">
                        <label class="mb-0 text-muted" style="font-size:0.68rem;">Cheque Date (post-dated allowed)</label>
                        <input type="date" class="${inp} pay-date">
                    </div>`;
            case 'transfer':
                return `<div class="col-12"><input type="text" class="${inp} pay-ref" placeholder="Transaction / Reference No"></div>`;
            case 'voucher':
                return `
                    <div class="col-12">
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control pay-ref text-uppercase" placeholder="Scan / enter voucher no"
                                oninput="invalidateVoucher(this)"
                                onkeydown="if(event.key==='Enter'){event.preventDefault();verifyGiftVoucher(this);}">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-spencersz" onclick="verifyGiftVoucher(this)">Verify</button>
                            </div>
                        </div>
                        <small class="pay-voucher-msg"></small>
                    </div>`;
            default:
                return '';
        }
    }

    // ---------- Payment rows ----------
    function addPaymentRow(mode) {
        const container = document.getElementById("paymentLinesContainer");
        const row = document.createElement("div");
        row.className = "payment-line border rounded p-2 mb-2";
        // first row is the "balance filler": it auto-takes whatever is still unpaid
        row.dataset.auto = container.children.length === 0 ? '1' : '0';
        row.innerHTML = `
            <div class="row g-2 align-items-center">
                <div class="col-6 pay-mode-holder"></div>
                <div class="col-5">
                    <input type="number" min="0" step="0.01"
                        class="form-control form-control-sm text-end font-monospace fw-bold pay-amount"
                        value="0" oninput="onPayAmountInput(this)">
                </div>
                <div class="col-1 p-0 text-center">
                    <button type="button" class="btn btn-sm text-danger p-0" onclick="removePaymentRow(this)"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="row g-2 mt-1 pay-extra"></div>`;

        const sel = buildPayModeSelect();
        sel.value = mode || 'Cash';
        sel.addEventListener('change', () => onPayModeChange(sel));
        row.querySelector('.pay-mode-holder').appendChild(sel);
        container.appendChild(row);

        // extra rows start with the remaining due (0 if the filler row already covers it)
        if (row.dataset.auto === '0') {
            row.querySelector('.pay-amount').value = Math.max(0, r2(getNetTotal() - sumPayments())).toFixed(2);
        }
        onPayModeChange(sel);
    }

    function onPayModeChange(sel) {
        const row = sel.closest('.payment-line');
        const type = sel.selectedOptions[0].dataset.type;
        row.dataset.type = type;
        ['voucherOk', 'voucherId', 'voucherBalance', 'voucherCode'].forEach(k => delete row.dataset[k]);
        row.querySelector('.pay-extra').innerHTML = payExtraHtml(type);

        if (type === 'voucher') {
            row.dataset.auto = '0';
            row.querySelector('.pay-amount').value = '0';
            const ref = row.querySelector('.pay-ref');
            if (ref) ref.focus();
        }
        calculateSplitPayments();
    }

    function onPayAmountInput(inp) {
        inp.closest('.payment-line').dataset.auto = '0'; // user typed, stop auto-filling this row
        calculateSplitPayments();
    }

    function resetPayments() {
        document.getElementById("paymentLinesContainer").innerHTML = "";
        addPaymentRow('Cash');
    }

    // ---------- Gift voucher ----------
    function invalidateVoucher(inp) {
        const r = inp.closest('.payment-line');
        r.dataset.voucherOk = '0';
        delete r.dataset.voucherCode;
        r.querySelector('.pay-voucher-msg').textContent = '';
        calculateSplitPayments();
    }

    function verifyGiftVoucher(el) {
        const row = el.closest('.payment-line');
        const input = row.querySelector('.pay-ref');
        const msg = row.querySelector('.pay-voucher-msg');
        const code = input.value.trim().toUpperCase();
        input.value = code;

        const setMsg = (text, ok) => {
            msg.textContent = text;
            msg.className = 'pay-voucher-msg ' + (ok ? 'text-success' : 'text-danger');
        };

        row.dataset.voucherOk = '0';
        if (!code) { setMsg('Enter the voucher number', false); calculateSplitPayments(); return; }

        const dup = [...document.querySelectorAll('.payment-line')]
            .some(r => r !== row && r.dataset.voucherCode === code);
        if (dup) { setMsg('This voucher is already added to the invoice', false); return; }

        $.ajax({
            url: '<?php echo base_url(); ?>Invoice/CheckGiftVoucher',
            type: 'POST',
            data: { vouchercode: code },
            success: function (response) {
                const obj = JSON.parse(response);
                if (obj.status == 1) {
                    const bal = parseFloat(obj.balance) || 0;
                    row.dataset.voucherOk = '1';
                    row.dataset.voucherCode = code;
                    row.dataset.voucherId = obj.idtbl_gift_voucher;
                    row.dataset.voucherBalance = bal;

                    const filler = document.querySelector('.payment-line[data-auto="1"]');
                    const due = Math.max(0, r2(getNetTotal() - sumPayments([row, filler])));
                    row.querySelector('.pay-amount').value = Math.min(bal, due).toFixed(2);

                    setMsg('Valid • Balance ' + fmtLKR(bal) + (obj.expiry_date ? ' • Exp ' + obj.expiry_date : ''), true);
                } else {
                    row.querySelector('.pay-amount').value = '0';
                    setMsg(obj.message || 'Invalid voucher', false);
                }
                calculateSplitPayments();
            },
            error: function () { setMsg('Could not verify voucher. Try again.', false); }
        });
    }

    // ---------- Totals ----------
    function calculateSplitPayments() {
        const net = getNetTotal();
        const rows = document.querySelectorAll('.payment-line');

        // 1. voucher amount can never exceed its balance
        rows.forEach(r => {
            if (r.dataset.type === 'voucher' && r.dataset.voucherOk === '1') {
                const inp = r.querySelector('.pay-amount');
                const bal = parseFloat(r.dataset.voucherBalance) || 0;
                if ((parseFloat(inp.value) || 0) > bal) inp.value = bal.toFixed(2);
            }
        });

        // 2. filler row takes whatever is still unpaid
        const filler = document.querySelector('.payment-line[data-auto="1"]');
        if (filler) {
            const others = sumPayments([filler]);
            filler.querySelector('.pay-amount').value = Math.max(0, r2(net - others)).toFixed(2);
        }

        // 3. totals
        let total = 0, cash = 0;
        rows.forEach(r => {
            const a = rowAmount(r);
            total += a;
            if (r.dataset.type === 'cash') cash += a;
        });
        total = r2(total);
        const diff = r2(net - total);

        document.getElementById("totalEnteredDisplay").innerText = fmtLKR(total);

        const balDisplay = document.getElementById("balanceRemainingDisplay");
        const changeDisplay = document.getElementById("changeDueDisplay");
        changeDisplay.style.display = "none";

        if (diff > 0) {
            balDisplay.innerText = "Balance: " + fmtLKR(diff);
            balDisplay.className = "text-danger fw-bold";
        } else if (diff === 0) {
            balDisplay.innerText = "Balance: LKR 0.00 (Paid)";
            balDisplay.className = "text-success fw-bold";
        } else {
            const over = Math.abs(diff);
            if (over <= r2(cash)) {
                balDisplay.innerText = "Balance: LKR 0.00 (Paid)";
                balDisplay.className = "text-success fw-bold";
                changeDisplay.innerText = "Change to return: " + fmtLKR(over);
                changeDisplay.style.display = "block";
            } else {
                balDisplay.innerText = "Over-payment " + fmtLKR(over) + " (only cash can give change)";
                balDisplay.className = "text-danger fw-bold";
            }
        }
    }

    // ---------- For saving + validation ----------
    function collectPayments() {
        const v = (r, s) => { const e = r.querySelector(s); return e ? e.value.trim() : ''; };
        return [...document.querySelectorAll('.payment-line')]
            .map(r => ({
                mode: v(r, '.pay-mode'),
                type: r.dataset.type,
                amount: rowAmount(r),
                ref: v(r, '.pay-ref'),
                ref2: v(r, '.pay-ref2'),
                bank: v(r, '.pay-bank'),
                months: v(r, '.pay-months'),
                chequeDate: v(r, '.pay-date'),
                voucherId: r.dataset.voucherId || null
            }))
            .filter(p => p.amount > 0);
    }

    function validatePayments() {
        const invType = document.getElementById("invoiceTypeSelect").value;
        if (invType === "Corporate Quotation") return "";   // quotation needs no payment

        const net = getNetTotal();
        let total = 0, cash = 0;

        for (const r of document.querySelectorAll('.payment-line')) {
            const amt = parseFloat(r.querySelector('.pay-amount').value) || 0;
            if (amt <= 0) continue;
            const t = r.dataset.type;
            const mode = r.querySelector('.pay-mode').value;
            const v = s => { const e = r.querySelector(s); return e ? e.value.trim() : ''; };

            if (t === 'card' || t === 'installment') {
                if (!/^\d{4}$/.test(v('.pay-ref'))) return mode + ": enter the last 4 digits of the card.";
                if (!v('.pay-ref2')) return mode + ": enter the approval code.";
            }
            if (t === 'cheque') {
                if (!v('.pay-bank')) return "Cheque: enter the bank.";
                if (!/^\d{6}$/.test(v('.pay-ref'))) return "Cheque: enter the 6-digit cheque number.";
                if (!v('.pay-date')) return "Cheque: select the cheque date.";
            }
            if (t === 'transfer' && !v('.pay-ref')) return mode + ": enter the reference number.";
            if (t === 'voucher' && r.dataset.voucherOk !== '1') return "Gift Voucher: verify the voucher first.";

            total += amt;
            if (t === 'cash') cash += amt;
        }
        total = r2(total);

        if (invType === "Retail Tax Invoice (VAT)" && total < r2(net)) return "Payment is less than the invoice total.";
        if (total > r2(net) && r2(total - net) > r2(cash)) return "Over-payment can only be returned as cash change.";
        return "";
    }
</script>
<?php include "include/footer.php"; ?>