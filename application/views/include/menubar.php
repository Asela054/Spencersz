<?php 
$controllermenu=$this->router->fetch_class();
$functionmenu=uri_string();
$functionmenu2=$this->router->fetch_method();

$menuprivilegearray = $menuaccess;
$permissionallowed = array();
$addcheck = 0;
$editcheck = 0;
$statuscheck = 0;
$deletecheck = 0;
$approvecheck = 0;
$checkstatus = 0;
$accountstatus = 0;

foreach($menuprivilegearray as $row){
    if($row->module==$functionmenu2){
        if($row->permission_type==1){$addcheck=1;}
        if($row->permission_type==2){$editcheck=1;}
        if($row->permission_type==3){$statuscheck=1;}
        if($row->permission_type==4){$deletecheck=1;}
        if($row->permission_type==5){$approvecheck=1;}
        if($row->permission_type==6){$checkstatus=1;}
		if($row->permission_type==7){$accountstatus=1;}
    }

    if($row->module==$functionmenu){
        if($row->permission_type==1){$addcheck=1;}
        if($row->permission_type==2){$editcheck=1;}
        if($row->permission_type==3){$statuscheck=1;}
        if($row->permission_type==4){$deletecheck=1;}
        if($row->permission_type==5){$approvecheck=1;}
        if($row->permission_type==6){$checkstatus=1;}
		if($row->permission_type==7){$accountstatus=1;}
    }
    
    array_push($permissionallowed, $row->module);
}

$permissionallowed = array_unique($permissionallowed);

$companynameshow = $_SESSION['companyname'];
$comsplit = explode('(', $companynameshow);
$comshowline1 = $comsplit[0];
if(!empty($comsplit[1])): $comshowline2 = '('.$comsplit[1];else: $comshowline2 = '';endif;
?>
<textarea class="d-none" id="actiontext"><?php if($this->session->flashdata('msg')) {echo $this->session->flashdata('msg');} ?></textarea>

<nav class="sidenav shadow-right sidenav-light">
	<div class="sidenav-toggle-btn" id="sidenavToggleBtn">
		<span class="icon-collapse"><i data-feather="chevron-left"></i></span>
		<span class="icon-expand"><i data-feather="chevron-right"></i></span>
	</div>
	<div class="sidenav-brand">
		<div class="brand-icon-box"><i data-feather="tv"></i></div>
		<div class="sidenav-brand-text">
			<div class="brand-name"><?php echo $comshowline1; ?></div>
			<div class="brand-sub"><?php echo $comshowline2; ?></div>
		</div>
	</div>
	<div class="sidenav-divider"></div>
	<div class="sidenav-menu">
		<div class="nav accordion" id="accordionSidenav">
			<div class="sidenav-menu-heading">Core</div>
			<a class="nav-link" href="<?php echo base_url().'Welcome/Dashboard'; ?>">
				<div class="nav-link-icon"><i class="fas fa-desktop"></i></div>
				<span>Dashboard</span>
			</a>

			<!-- Product Menu New Added -->
			<?php if(in_array("Product", $permissionallowed) || in_array("Category", $permissionallowed) || in_array("Brand", $permissionallowed) || in_array("Unit", $permissionallowed) || in_array("Productserial", $permissionallowed)){ ?>
			<a class="nav-link collapsed" href="javascript:void(0);" data-toggle="collapse"
				data-target="#collapseproduct" aria-expanded="false" aria-controls="collapseproduct">
				<div class="nav-link-icon"><i class="fas fa-boxes"></i></div>
				<span>Product</span>
				<div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
			</a>
			<div class="collapse <?php if($functionmenu=="Product" || $functionmenu=="Category" || $functionmenu=="Brand" || $functionmenu=="Unit" || $functionmenu=="Productserial"){echo 'show';} ?>"
				id="collapseproduct" data-parent="#accordionSidenav">
				<nav class="sidenav-menu-nested nav accordion">
					<?php if(in_array("Category", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'Category'; ?>">Category</a>
					<?php } if(in_array("Brand", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'Brand'; ?>">Brand</a>
					<?php } if(in_array("Unit", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'Unit'; ?>">Unit</a>
					<?php } if(in_array("Product", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'Product'; ?>">Product</a>
					<?php } if(in_array("Productserial", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'Productserial'; ?>">Product Serial</a>
					<?php } ?>
				</nav>
			</div>
			<?php } ?>

			<!-- Supplier Menu New Added -->
			<?php if(in_array("Supplier", $permissionallowed) || in_array("Suppliertype", $permissionallowed)){ ?>
			<a class="nav-link collapsed" href="javascript:void(0);" data-toggle="collapse"
				data-target="#collapsesupplier" aria-expanded="false" aria-controls="collapsesupplier">
				<div class="nav-link-icon"><i class="fas fa-users"></i></div>
				<span>Supplier</span>
				<div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
			</a>
			<div class="collapse <?php if($functionmenu=="Supplier" || $functionmenu=="Suppliertype"){echo 'show';} ?>"
				id="collapsesupplier" data-parent="#accordionSidenav">
				<nav class="sidenav-menu-nested nav accordion">
					<?php if(in_array("Suppliertype", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'Suppliertype'; ?>">Supplier
						Type</a>
					<?php } if(in_array("Supplier", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'Supplier'; ?>">New
						Supplier</a>
					<?php } ?>
				</nav>
			</div>
			<?php } ?>

			<!-- Purchase Order Menu New Added -->
			<?php if(in_array("Newpurchaserequest", $permissionallowed) || in_array("Purchaseorder", $permissionallowed) || in_array("ServicePurchaseOrder", $permissionallowed)){ ?>
			<a class="nav-link collapsed" href="javascript:void(0);" data-toggle="collapse"
				data-target="#collapseporder" aria-expanded="false" aria-controls="collapseporder">
				<div class="nav-link-icon"><i class="fas fa-truck"></i></div>
				<span>Purchase Order</span>
				<div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
			</a>
			<div class="collapse <?php if($functionmenu=="Newpurchaserequest" || $functionmenu=="Purchaseorder" || $functionmenu=="ServicePurchaseOrder"){echo 'show';} ?>"
				id="collapseporder" data-parent="#accordionSidenav">
				<nav class="sidenav-menu-nested nav accordion">
					<?php if(in_array("Newpurchaserequest", $permissionallowed)){ ?>
					<a class="nav-link"
						href="<?php echo base_url().'Newpurchaserequest'; ?>">New Purchase Request</a>
					<?php } if(in_array("Purchaseorder", $permissionallowed)){ ?>
					<a class="nav-link"
						href="<?php echo base_url().'Purchaseorder'; ?>">Purchase Order</a>
					<?php } if(in_array("ServicePurchaseOrder", $permissionallowed)){ ?>
					<!-- <a class="nav-link"
						href="<?php echo base_url().'ServicePurchaseOrder'; ?>">Service Purchase Order</a> -->
					<?php } ?>
				</nav>
			</div>
			<?php } ?>

			<!-- GRN Menu New Added -->
			<?php if(in_array("Goodreceive", $permissionallowed) || in_array("GRNVoucher", $permissionallowed) || in_array("Goodreceivereturn", $permissionallowed)){ ?>
			<a class="nav-link collapsed" href="javascript:void(0);" data-toggle="collapse"
				data-target="#collapsestores" aria-expanded="false" aria-controls="collapsestores">
				<div class="nav-link-icon"><i class="fa fa-cart-arrow-down"></i></div>
				<span>GRN Section</span>
				<div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
			</a>
			<div class="collapse <?php if($functionmenu=="Goodreceive" || $functionmenu=="GRNVoucher" || $functionmenu=="Goodreceivereturn"){echo 'show';} ?>"
				id="collapsestores" data-parent="#accordionSidenav">
				<nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPages">
					<?php if(in_array("Goodreceive", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'Goodreceive'; ?>">Good
						Receive Note</a>
					<?php } if(in_array("GRNVoucher", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'GRNVoucher'; ?>">Good
						Receive Note Voucher</a>
					<?php } if(in_array("Goodreceivereturn", $permissionallowed)){ ?>
					<a class="nav-link"
						href="<?php echo base_url().'Goodreceivereturn'; ?>">Good Receive Note Return</a>
					<?php } ?>
				</nav>
			</div>
			<?php } ?>

			<!-- Stock Menu New Added -->
			<?php if(in_array("Report", $permissionallowed) || in_array("Allstockview", $permissionallowed) || in_array("IssueMaterials", $permissionallowed)){ ?>
			<a class="nav-link collapsed" href="javascript:void(0);" data-toggle="collapse"
				data-target="#collapsStock" aria-expanded="false" aria-controls="collapsStock">
				<div class="nav-link-icon"><i class="fa fa-warehouse"></i></div><span>Stock Management</span>
				<div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
			</a>
			<div class="collapse <?php if($functionmenu=="Report" || $functionmenu=="Allstockview" || $functionmenu=="IssueMaterials"){echo 'show';} ?>"
				id="collapsStock" data-parent="#accordionSidenav">
				<nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPages">
					<?php if(in_array("Report", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'Report'; ?>">Stock
						Category Wise</a>
					<?php } if(in_array("Allstockview", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'Allstockview'; ?>">All
						Stock View</a>
					<!-- <?php } if(in_array("IssueMaterials", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'IssueMaterials'; ?>">Issue
						Materials</a> -->
					<?php } ?>
				</nav>
			</div>
			<?php } ?>

			<!-- Stock Transfer Menu New Added -->
			<?php if(in_array("Stocktransfer", $permissionallowed)){ ?>
			<a class="nav-link" href="<?php echo base_url().'Stocktransfer'; ?>">
				<div class="nav-link-icon"><i class="fas fa-exchange-alt"></i></div>
				<span>Stock Transfer</span>
			</a>
			<?php } ?>

			<!-- Customer Menu New Added -->
			<?php if(in_array("Customer", $permissionallowed)){ ?>
			<a class="nav-link" href="<?php echo base_url().'Customer'; ?>">
				<div class="nav-link-icon"><i class="fas fa-user"></i></div>
				<span>Customer</span>
			</a>
			<?php } ?>

			<!-- Item Reservation Menu New Added -->
			<?php if(in_array("Reservation", $permissionallowed)){ ?>
			<a class="nav-link" href="<?php echo base_url().'Reservation'; ?>">
				<div class="nav-link-icon"><i class="fas fa-calendar-check"></i></div>
				<span>Item Reservation</span>
			</a>
			<?php } ?>

			<!-- Invoice Menu New Added -->
			<?php if(in_array("Dispatchnote", $permissionallowed) || in_array("Invoice", $permissionallowed) || in_array("Deletedinvoice", $permissionallowed) || in_array("Canceledinvoice", $permissionallowed) || in_array("Creditnote", $permissionallowed) || in_array("Giftvoucher", $permissionallowed) || in_array("Invoicelist", $permissionallowed)){ ?>
			<a class="nav-link collapsed" href="javascript:void(0);" data-toggle="collapse"
				data-target="#collapsInvoice" aria-expanded="false" aria-controls="collapsInvoice">
				<div class="nav-link-icon"><i class="fa fa-file-invoice"></i></div><span>Invoice</span>
				<div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
			</a>
			<div class="collapse <?php if($functionmenu=="Dispatchnote" || $functionmenu=="Invoice" || $functionmenu=="Deletedinvoice" || $functionmenu=="Canceledinvoice" || $functionmenu=="Creditnote" || $functionmenu=="Giftvoucher" || $functionmenu2=="Invoicelist"){echo 'show';} ?>"
				id="collapsInvoice" data-parent="#accordionSidenav">
				<nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPages">
					<?php if(in_array("Dispatchnote", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'Dispatchnote'; ?>">Dispatch
						Note</a>
					<?php } if(in_array("Invoice", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'Invoice'; ?>">Invoice </a>
					<?php } if(in_array("Invoicelist", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'Invoice/Invoicelist'; ?>">Invoice List</a>
					<?php } if(in_array("Creditnote", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'Creditnote'; ?>">Credit
						Note</a>
					<?php } if(in_array("Giftvoucher", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'Giftvoucher'; ?>">Gift
						Voucher</a>
					<?php } ?>
				</nav>
			</div>
			<?php } ?>

			<!-- Invoice Menu Fair Trading Added -->
			<?php if(in_array("DirectDispatchnote", $permissionallowed) || in_array("DirectInvoice", $permissionallowed)){ ?>
			<a class="nav-link collapsed" href="javascript:void(0);" data-toggle="collapse"
				data-target="#collapsDirectInvoice" aria-expanded="false" aria-controls="collapsDirectInvoice">
				<div class="nav-link-icon"><i class="fa fa-file-invoice"></i></div><span>Direct Sale</span>
				<div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
			</a>
			<div class="collapse <?php if($functionmenu=="DirectDispatchnote" || $functionmenu=="DirectInvoice"){echo 'show';} ?>"
				id="collapsDirectInvoice" data-parent="#accordionSidenav">
				<nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPages">
					<?php if(in_array("DirectDispatchnote", $permissionallowed)){ ?>
					<a class="nav-link"
						href="<?php echo base_url().'DirectDispatchnote'; ?>">Direct Dispatch Note</a>
					<?php } if(in_array("DirectInvoice", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'DirectInvoice'; ?>">Direct
						Invoice </a>
					<?php } ?>
				</nav>
			</div>
			<?php } ?>


			<!-- Report Menu New Added -->
			<?php
			$stockRptPages = ['Stocklistreport','Stockcard','Reorderstockreport','Stocktransferreport','Deadstockreport','Stockvaluationreport'];
			$stockRptEntry = '';
			foreach ($stockRptPages as $p) {
				if (in_array($p, $permissionallowed)) { $stockRptEntry = $p; break; }
			}
			if ($stockRptEntry !== '') { ?>
			<a class="nav-link <?php echo in_array($functionmenu, $stockRptPages) ? 'active' : ''; ?>" href="<?php echo base_url() . $stockRptEntry; ?>">
				<div class="nav-link-icon"><i class="fas fa-chart-bar"></i></div><span>Stock Reports</span>
			</a>
			<?php } ?>

			<?php if(in_array("Serialhistory",$permissionallowed) || in_array("Serialstock",$permissionallowed) || in_array("Soldserial",$permissionallowed)){ ?>
			<a class="nav-link collapsed" href="javascript:void(0);" data-toggle="collapse" data-target="#collapseSerialRpt" aria-expanded="false" aria-controls="collapseSerialRpt">
				<div class="nav-link-icon"><i class="fas fa-barcode"></i></div><span>Serial Reports</span>
				<div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
			</a>
			<div class="collapse <?php if(in_array($functionmenu,['Serialhistory','Serialstock','Soldserial'])){echo 'show';} ?>" id="collapseSerialRpt" data-parent="#accordionSidenav">
				<nav class="sidenav-menu-nested nav accordion">
					<?php if(in_array("Serialhistory",$permissionallowed)){ ?><a class="nav-link" href="<?php echo base_url().'Serialhistory'; ?>">Serial History</a><?php } ?>
					<?php if(in_array("Serialstock",$permissionallowed)){ ?><a class="nav-link" href="<?php echo base_url().'Serialstock'; ?>">Serial-wise Stock</a><?php } ?>
					<?php if(in_array("Soldserial",$permissionallowed)){ ?><a class="nav-link" href="<?php echo base_url().'Soldserial'; ?>">Sold Serial Lookup</a><?php } ?>
				</nav>
			</div>
			<?php } ?>

			<?php
			$purchRptPages = ['Purchasereport','Postatusreport','Grnreturnreport'];
			$purchRptEntry = '';
			foreach ($purchRptPages as $p) {
				if (in_array($p, $permissionallowed)) { $purchRptEntry = $p; break; }
			}
			if ($purchRptEntry !== '') { ?>
			<a class="nav-link <?php echo in_array($functionmenu, $purchRptPages) ? 'active' : ''; ?>" href="<?php echo base_url() . $purchRptEntry; ?>">
				<div class="nav-link-icon"><i class="fas fa-shopping-basket"></i></div><span>Purchasing Reports</span>
			</a>
			<?php } ?>

			<!-- Reservationreport -->
			<?php if(in_array("Reservationreport", $permissionallowed)){ ?>
			<a class="nav-link" href="<?php echo base_url().'Reservationreport'; ?>">
				<div class="nav-link-icon"><i class="fas fa-calendar-check"></i></div>
				<span>Reservation Report</span>
			</a>
			<?php } ?>

            <!-- Sales Report -->
			<?php if(in_array("Salesreport", $permissionallowed)){ ?>
			<a class="nav-link" href="<?php echo base_url().'Salesreport'; ?>">
				<div class="nav-link-icon"><i class="fas fa-cash-register"></i></div>
				<span>Sales Report</span>
			</a>
			<?php } ?>

			<!-- Tax Control Menu New Added -->
			<?php if(in_array("Taxcontrol", $permissionallowed)){ ?>
			<a class="nav-link" href="<?php echo base_url().'Taxcontrol'; ?>">
				<div class="nav-link-icon"><i class="fas fa-euro-sign"></i></div>
				<span>Tax Control</span>
			</a>
			<?php } ?>

			<!-- Serial Number Movement Menu New Added -->
			<?php if(in_array("Serialmovement", $permissionallowed)){ ?>
			<a class="nav-link" href="<?php echo base_url().'Serialmovement'; ?>">
				<div class="nav-link-icon"><i class="fas fa-barcode"></i></div>
				<span>Serial Number Movement</span>
			</a>
			<?php } ?>

			<!-- Company Menu New Added -->
			<?php if(in_array("Company", $permissionallowed) || in_array("Companybranch", $permissionallowed)){ ?>
			<a class="nav-link collapsed" href="javascript:void(0);" data-toggle="collapse"
				data-target="#collapseCompany" aria-expanded="false" aria-controls="collapseCompany">
				<div class="nav-link-icon"><i class="fas fa-building"></i></div>
				<span>Company</span>
				<div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
			</a>
			<div class="collapse <?php if($functionmenu=="Company" || $functionmenu=="Companybranch"){echo 'show';} ?>"
				id="collapseCompany" data-parent="#accordionSidenav">
				<nav class="sidenav-menu-nested nav accordion">
					<?php if(in_array("Company", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'Company'; ?>">Company</a>
					<?php } if(in_array("Companybranch", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'Companybranch'; ?>">New
						Company Branch</a>
					<?php } ?>
				</nav>
			</div>
			<?php } ?>


			<!-- User Account Menu New Added -->
			<?php if(in_array("Useraccount", $permissionallowed) || in_array("Usertype", $permissionallowed) || in_array("Userprivilege", $permissionallowed) || in_array("Userpermissions", $permissionallowed) || in_array("Userroles", $permissionallowed)){ ?>
			<a class="nav-link collapsed" href="javascript:void(0);" data-toggle="collapse"
				data-target="#collapseUser" aria-expanded="false" aria-controls="collapseUser">
				<div class="nav-link-icon"><i class="fas fa-user"></i></div>
				<span>User Account</span>
				<div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
			</a>
			<div class="collapse <?php if($functionmenu2=="Useraccount" || $functionmenu2=="Usertype" || $functionmenu2=="Userprivilege" || $functionmenu2=="Userpermissions" || $functionmenu2=="Userroles"){echo 'show';} ?>"
				id="collapseUser" data-parent="#accordionSidenav">
				<nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPages">
					<?php if(in_array("Useraccount", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'User/Useraccount'; ?>">User
						Account</a>
					<?php } if(in_array("Usertype", $permissionallowed)){ ?>
					<a class="nav-link"
						href="<?php echo base_url().'User/Usertype'; ?>">Type</a>
					<?php } if(in_array("Userprivilege", $permissionallowed)){ ?>
					<a class="nav-link"
						href="<?php echo base_url().'User/Userprivilege'; ?>">Privilege</a>
					<?php } if(in_array("Userpermissions", $permissionallowed)){ ?>
					<a class="nav-link"
						href="<?php echo base_url().'User/Userpermissions'; ?>">User Permissions</a>
					<?php } if(in_array("Userroles", $permissionallowed)){ ?>
					<a class="nav-link" href="<?php echo base_url().'User/Userroles'; ?>">User
						Roles</a>
					<?php } ?>
				</nav>
			</div>
			<?php } ?>
		</div>
	</div>

	<div class="sidenav-footer">
		<div class="avatar-circle">
			<?php
			$string = ucfirst($_SESSION['name']);

			$initials = implode('', array_map(function ($w) {
				return isset($w[0]) ? $w[0] : '';
			}, explode(' ', $string)));

			echo strtoupper($initials); // Output: LASER
			?>
		</div>
		<div class="sidenav-footer-content">
			<div class="sidenav-footer-subtitle">Logged in as:</div>
			<div class="sidenav-footer-title"><?php echo $_SESSION['typename']; ?></div>
		</div>
		<a href="<?php echo base_url() ?>Welcome/Logout" class="sidenav-footer-logout" title="Logout"><i data-feather="log-out"></i></a>
	</div>
</nav>