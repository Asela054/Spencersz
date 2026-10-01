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
            <div class="page-header page-header-light bg-white shadow">
                <div class="container-fluid">
                    <div class="page-header-content py-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i class="fas fa-truck"></i></div>
                            <span>Purchase Order</span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2">
                <div class="card">
                    <div class="card-body p-0 p-2">
                        <div class="row">
                            <div class="col-12 text-right">
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                    data-target="#staticBackdrop"
                                    <?php if($addcheck==0){echo 'disabled';} ?>><i class="fas fa-plus mr-2"></i>Create
                                    Purchase Order</button>
                                <hr>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="scrollbar pb-3" id="style-2">
                                    <table class="table table-bordered table-striped table-sm nowrap" id="dataTable">
                                        <thead>
                                            <tr>
                                                <th>PO No</th>
                                                <th>Date</th>
                                                <th>Order Type</th>
                                                <th>Supplier</th>
                                                <th>Atten. To</th>
                                                <th>Confirm Status</th>
                                                <th>Approved By</th>
                                                <th>GRN Issue Status</th>
                                                <th>Total</th>
                                                <th class="text-right">Actions</th>
                                            </tr>
                                        </thead>
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
<!-- Modal -->
<div class="modal fade" id="staticBackdrop" data-backdrop="static" data-keyboard="false" tabindex="-1"
	aria-labelledby="staticBackdropLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="staticBackdropLabel">Create Purchase Order</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12 col-md-12 col-lg-5 col-xl-5">
						<form id="createorderform" autocomplete="off">
							<div class="form-group mb-1">
								<label class="small font-weight-bold text-dark">Order Date*</label>
								<input type="date" class="form-control form-control-sm" placeholder="" name="orderdate"
									id="orderdate" value="<?php echo date('Y-m-d')?>" required>
							</div>
							<div class="form-row mb-1">
								<div class="col">
									<label class="small font-weight-bold text-dark">PO Request</label>
									<select class="form-control form-control-sm selecter2 px-0" name="porderrequest"
										id="porderrequest">
										<option value="">Select</option>
										<?php foreach($porderlist->result() as $rowporderlist){ ?>
										<option value="<?php echo $rowporderlist->idtbl_print_porder_req ?>">
											<?php echo $rowporderlist->porder_req_no ?></option>
										<?php } ?>
									</select>

								</div>
								<div class="col">
									<div class="form-group mb-1">
										<label class="small font-weight-bold text-dark">PO Request Type*</label>
                                        <input type="text" class="form-control form-control-sm" placeholder="" name="requestordertype" id="requestordertype" required readonly>
									</div>
								</div>
							</div>
							<div class="form-group mb-1">
								<label class="small font-weight-bold text-dark d-none">Company*</label>
								<input type="text" id="f_company_name" name="f_company_name"
									class="form-control form-control-sm d-none" required readonly>
							</div>
							<div class="form-group mb-1">
								<label class="small font-weight-bold text-dark d-none">Company Branch*</label>
								<input type="text" id="f_branch_name" name="f_branch_name"
									class="form-control form-control-sm d-none" required readonly>
							</div>
							<input type="hidden" name="f_company_id" id="f_company_id">
							<input type="hidden" name="f_branch_id" id="f_branch_id">

							<div id="supplierFields">
								<div class="form-group mb-1">
									<label class="small font-weight-bold text-dark">Supplier*</label>
									<select class="form-control form-control-sm" name="supplier" id="supplier">
										<option value="">Select</option>
									</select>
								</div>
							</div>
							<div id="contactPersonFields">
								<div class="form-group mb-1">
									<label class="small font-weight-bold text-dark">Contact Person</label>
									<select class="form-control form-control-sm" name="contactperson" id="contactperson">
										<option value="">Select</option>
										<?php foreach($contactpersonlist->result() as $rowcontactpersonlist){ ?>
										<option value="<?php echo $rowcontactpersonlist->idtbl_po_contact_person ?>">
											<?php echo $rowcontactpersonlist->contact_person ?><?php if(!empty($rowcontactpersonlist->designation)){ echo ' - '.$rowcontactpersonlist->designation; } ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
                            <div class="form-group mb-1">
									<div class="form-group mb-1">
										<label class="small font-weight-bold text-dark">PO Type*</label>
										<select class="form-control form-control-sm" name="ordertype" id="ordertype" required>
											<option value="">Select</option>
											<?php foreach($ordertypelist->result() as $rowordertypelist){ ?>
											<option value="<?php echo $rowordertypelist->idtbl_material_group ?>">
												<?php echo $rowordertypelist->group ?></option>
											<?php } ?>
										</select>
									</div>
							</div>
							<div class="form-row mb-1">
								<div class="col" id="productFields">
									<div class="form-group mb-1">
										<label class="small font-weight-bold text-dark">Spare Parts / Service / Material
											/ Machine *</label>
										<select class="form-control form-control-sm selecter2 px-0" name="product" id="product">
											<option value=""></option>
										</select>
									</div>
								</div>
							</div>
							<div class="form-row mb-1">
								<div class="col" id="newQtyFields">
									<label class="small font-weight-bold text-dark">Qty*</label>
									<input type="text" id="newqty" name="newqty" class="form-control form-control-sm" required>
								</div>
								<div class="col">
									<label class="small font-weight-bold text-dark">UOM*</label>
									<select class="form-control form-control-sm" name="uom"
										id="uom" required>
										<option value="">Select</option>
										<?php foreach($measurelist->result() as $rowmeasurelist){ ?>
										<option value="<?php echo $rowmeasurelist->idtbl_mesurements ?>">
											<?php echo $rowmeasurelist->measure_type ?></option>
										<?php } ?>
									</select>
								</div>
                                <div class="col">
                                    <label class="small font-weight-bold text-dark">Convert Qty</label>
                                    <div class="input-group">
                                        <input type="text" id="piecesper_qty" name="piecesper_qty"
                                            class="form-control form-control-sm" value="0" readonly>
                                        <input type="text" id="piecesper_qty_uom" name="piecesper_qty_uom"
                                            class="form-control form-control-sm" readonly>
                                    </div>
                                </div>
							</div>

							<div class="form-row mb-1">
                                    <label class="small font-weight-bold text-dark">Unit Price</label>
                                    <input type="text" id="unitprice" name="unitprice" class="form-control form-control-sm"
                                        value="0" step="any">
							</div>

							<div class="form-row mb-1">
								<div class="col">
									<label class="small font-weight-bold text-dark" hidden>Vat (%)</label>
									<input type="text" id="vat" name="vat" class="form-control form-control-sm" value="0"
										hidden>
								</div>

								<div class="col">
									<label class="small font-weight-bold text-dark" hidden>Discount</label>
									<input type="text" id="discount" name="discount" class="form-control form-control-sm"
										value="0" hidden>
								</div>
							</div>


							<div class="form-group mb-1">
								<label class="small font-weight-bold text-dark">Comment</label>
								<textarea name="comment" id="comment" class="form-control form-control-sm"></textarea>
							</div>
							<div class="form-group mt-3 text-right">
								<button type="button" id="formsubmit" class="btn btn-warning font-weight-bold btn-sm px-4"
									<?php if($addcheck==0){echo 'disabled';} ?>><i class="fas fa-plus"></i>&nbsp;Add
									to
									list</button>
								<input name="submitBtn" type="submit" value="Save" id="submitBtn" class="d-none">
							</div>
							<input type="hidden" name="refillprice" id="refillprice" value="">
						</form>
					</div>
					<div class="col-sm-12 col-md-12 col-lg-7 col-xl-7">
							<div class="scrollbar pb-3" id="style-3">
								<table class="table table-striped table-bordered table-sm small" id="tableorder">
									<thead>
										<tr>
                                            <th id="thServiceItem" class="d-none">Service Item</th>
											<th>Item Name</th>
											<th class="d-none">ProductID</th>
											<th class="text-center">Qty</th>
											<th class="text-center">Uom</th>
											<th class="text-right">Unit Price</th>
                                            <th class="text-right">Price</th>
											<th class="d-none">HideTotal</th>
											<th class="text-right">Total</th>

										</tr>
									</thead>
									<tbody></tbody>
								</table>
							</div>
						<div class="row">
							<div class="col text-right">
								<h6 class="font-weight-600" id="divgrosstotal" style="margin-top: 10px;"> Rs. 0.00</h6>

							</div>
							<input type="hidden" id="hidegrosstotalorder" value="0">
						</div>
						<hr>
						<div class="form-group">
							<label class="small font-weight-bold text-dark">Remark</label>
							<textarea name="remark" id="remark" class="form-control form-control-sm"></textarea>
						</div>
						<div class="form-group mt-2">
							<button type="button" id="btncreateorder" class="btn btn-primary btn-sm fa-pull-right"><i
									class="fas fa-save"></i>&nbsp;Create
								Purchase Order</button>
						</div>
                        <div class="row mt-5 col-12">
                        	<div class="form-row mb-1">
                        		<div class="col-12">
                        			<div class="form-group mb-1">
                        				<label class="small font-weight-bold text-dark">Requestion</label>
                        				<ul id="requestitem" class="list-group">
                        				</ul>
                        			</div>
                        		</div>
                        	</div>
                        </div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="porderEditmodal" data-backdrop="static" data-keyboard="false" tabindex="-1"
	aria-labelledby="staticBackdropLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="staticBackdropLabel">Edit Purchase Order</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12 col-md-12 col-lg-5 col-xl-5">
						<form id="editcreateorderform" autocomplete="off">
							<div class="form-group mb-1">
                            <input type="hidden" class="form-control form-control-sm" name="hiddenporderid"
                            id="hiddenporderid" required>
                            <input type="hidden" class="form-control form-control-sm" name="hiddenporderreqid"
                            id="hiddenporderreqid" required>
								<label class="small font-weight-bold text-dark">Order Date*</label>
								<input type="date" class="form-control form-control-sm" placeholder="" name="editorderdate"
									id="editorderdate" value="<?php echo date('Y-m-d')?>" required>
							</div>
							<div class="form-group mb-1">
								<label class="small font-weight-bold text-dark d-none">Company*</label>
								<input type="text" id="f_company_name" name="f_company_name"
									class="form-control form-control-sm d-none" required readonly>
							</div>
							<div class="form-group mb-1">
								<label class="small font-weight-bold text-dark d-none">Company Branch*</label>
								<input type="text" id="f_branch_name" name="f_branch_name"
									class="form-control form-control-sm d-none" required readonly>
							</div>
							<input type="hidden" name="f_company_id" id="f_company_id">
							<input type="hidden" name="f_branch_id" id="f_branch_id">

							<div id="supplierFields">
								<div class="form-group mb-1">
									<label class="small font-weight-bold text-dark">Supplier*</label>
									<select class="form-control form-control-sm" name="editsupplier" id="editsupplier">
										<option value="">Select</option>
										 <?php foreach($supplierlist->result() as $rowsupplierlist){ ?>
										<option value="<?php echo $rowsupplierlist->idtbl_supplier ?>">
											<?php echo $rowsupplierlist->suppliername ?></option>
										<?php } ?> 
									</select>
								</div>
							</div>
							<div id="editContactPersonFields">
								<div class="form-group mb-1">
									<label class="small font-weight-bold text-dark">Contact Person</label>
									<select class="form-control form-control-sm" name="editcontactperson" id="editcontactperson">
										<option value="">Select</option>
										<?php foreach($contactpersonlist->result() as $rowcontactpersonlist){ ?>
										<option value="<?php echo $rowcontactpersonlist->idtbl_po_contact_person ?>">
											<?php echo $rowcontactpersonlist->contact_person ?><?php if(!empty($rowcontactpersonlist->designation)){ echo ' - '.$rowcontactpersonlist->designation; } ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
                            <div class="form-group mb-1">
									<div class="form-group mb-1">
										<label class="small font-weight-bold text-dark">PO Type*</label>
										<select class="form-control form-control-sm" name="editordertype" id="editordertype" required>
											<option value="">Select</option>
											<?php foreach($ordertypelist->result() as $rowordertypelist){ ?>
											<option value="<?php echo $rowordertypelist->idtbl_material_group ?>">
												<?php echo $rowordertypelist->group ?></option>
											<?php } ?>
										</select>
									</div>
							</div>
							<div class="form-row mb-1">
								<div class="col" id="productFields">
									<div class="form-group mb-1">
										<label class="small font-weight-bold text-dark">Spare Parts / Service / Material
											/ Machine *</label>
										<select class="form-control form-control-sm selecter2 px-0" name="editproduct" id="editproduct">
											<option value="">Select</option>
										</select>
									</div>
								</div>
							</div>
							<div class="form-row mb-1">
								<div class="col" id="editnewQtyFields">
									<label class="small font-weight-bold text-dark">Qty*</label>
									<input type="text" id="editnewqty" name="editnewqty" class="form-control form-control-sm" required>
								</div>
								<div class="col">
									<label class="small font-weight-bold text-dark">UOM*</label>
									<select class="form-control form-control-sm" name="edituom"
										id="edituom" required>
										<option value="">Select</option>
										<?php foreach($measurelist->result() as $rowmeasurelist){ ?>
										<option value="<?php echo $rowmeasurelist->idtbl_mesurements ?>">
											<?php echo $rowmeasurelist->measure_type ?></option>
										<?php } ?>
									</select>
								</div>
                                <div class="col">
                                    <label class="small font-weight-bold text-dark">Convert Qty</label>
                                    <div class="input-group">
                                        <input type="text" id="editpiecesper_qty" name="editpiecesper_qty"
                                            class="form-control form-control-sm" value="0" readonly>
                                        <input type="text" id="editpiecesper_qty_uom" name="editpiecesper_qty_uom"
                                            class="form-control form-control-sm" readonly>
                                    </div>
                                </div>
							</div>

							<div class="form-row mb-1">
                                    <label class="small font-weight-bold text-dark">Unit Price</label>
                                    <input type="text" id="editunitprice" name="editunitprice" class="form-control form-control-sm"
                                        value="0" step="any">
							</div>

							<div class="form-row mb-1">
								<div class="col">
									<label class="small font-weight-bold text-dark" hidden>Vat (%)</label>
									<input type="text" id="editvat" name="editvat" class="form-control form-control-sm" value="0"
										hidden>
								</div>

								<div class="col">
									<label class="small font-weight-bold text-dark" hidden>Discount</label>
									<input type="text" id="editdiscount" name="editdiscount" class="form-control form-control-sm"
										value="0" hidden>
								</div>
							</div>


							<div class="form-group mb-1">
								<label class="small font-weight-bold text-dark">Comment</label>
								<textarea name="editcomment" id="editcomment" class="form-control form-control-sm"></textarea>
							</div>
							<div class="form-group mt-3 text-right">
								<button type="button" id="editformsubmit" class="btn btn-primary btn-sm px-4"
									<?php if($addcheck==0){echo 'disabled';} ?>><i class="fas fa-plus"></i>&nbsp;Add
									to
									list</button>
								<input name="editsubmitBtn" type="submit" value="Save" id="editsubmitBtn" class="d-none">
							</div>
						</form>
					</div>
					<div class="col-sm-12 col-md-12 col-lg-7 col-xl-7">
							<div class="scrollbar pb-3" id="style-3">
                                <table class="table table-striped table-bordered table-sm small" id="edittableorder">
                                    <thead>
                                        <tr>
                                            <th id="editThServiceItem" class="d-none">Service Item</th>
                                            <th>Item Name</th>
                                            <th class="d-none">ProductID</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-center">Uom</th>
                                            <th class="text-right">Unit Price</th>
                                            <th class="text-right">Price</th>
                                            <th class="d-none">HideTotal</th>
                                            <th class="text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
							</div>
						<div class="row">
							<div class="col text-right">
								<h6 class="font-weight-600" id="editdivgrosstotal" style="margin-top: 10px;"> Rs. 0.00</h6>

							</div>
							<input type="hidden" id="edithidegrosstotalorder" value="0">
						</div>
						<hr>
						<div class="form-group">
							<label class="small font-weight-bold text-dark">Remark</label>
							<textarea name="editremark" id="editremark" class="form-control form-control-sm"></textarea>
						</div>
						<div class="form-group mt-2">
							<button type="button" id="editbtncreateorder" class="btn btn-outline-primary btn-sm fa-pull-right"><i
									class="fas fa-save"></i>&nbsp;Update
								Purchase Order</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Modal -->
<div id="purchaseview">
	<div class="modal fade" id="porderviewmodal" data-backdrop="static" data-keyboard="false" tabindex="-1"
		aria-labelledby="staticBackdropLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-xl">

			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="staticBackdropLabel">View Purchase Order</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="row">

						<div class="col-12">
							<p style="margin-bottom: 2px;" class="text-left"><span id="pordersuppliername"></span>
							</P>
							<p style="margin-bottom: 2px;" class="text-left"><span id="pordersuppliercontact"></span>
							</p>
							<p style="margin-bottom: 2px;" class="text-left"><span id="porderaddress1"></span>
							</p>
							<p style="margin-bottom: 2px;" class="text-left"><span id="porderaddress2"></span>
							</p>
							<p style="margin-bottom: 2px;" class="text-left"><span id="pordercity"></span></p>
							<p style="margin-bottom: 2px;" class="text-left"><span id="porderstate"></span></p>
							<p style="margin-bottom: 2px;" class="text-left"><span id="pordercontactperson"></span></p>
						</div>
					</div>
					<div id="viewhtml"></div>
                    <div class="col-12 text-right">
                        <hr>
                        <?php if($approvecheck==1){ ?>
                            <div id="approvalControls" class="d-none">

                                <div class="custom-control custom-checkbox d-inline-block mr-3 align-middle">
                                    <input type="checkbox"
                                        class="custom-control-input"
                                        id="cpstatuscheck"
                                        name="cpstatuscheck">

                                    <label class="custom-control-label" for="cpstatuscheck">
                                        Include Contact No
                                    </label>
                                </div>

                                <button id="btnapprovereject"
                                        class="btn btn-primary btn-sm px-3 mb-2">
                                    <i class="fas fa-check mr-2"></i>Approve or Reject
                                </button>

                            </div>
                        <?php } ?>
                        <input type="hidden" name="porderid" id="porderid">
                        <input type="hidden" id="reqestid" name="reqestid">
                        <?php if($checkstatus==1){ ?>
                        <button id="btncheck" class="btn btn-success btn-sm px-3 mb-2"><i class="fas fa-user-check mr-2"></i>Check By</button>
                        <?php } ?>
                    </div>
                    <div class="col-12 text-center">
                        <div id="alertdiv"></div>
                    </div> 
                    <div class="col-12 text-center">
                        <div id="checkalertdiv"></div>
                    </div>

				</div>
			</div>
			<input type="hidden" class="form-control form-control-sm" name="tableId" id="tableId" required readonly>

		</div>
	</div>
</div>

<?php include "include/footerscripts.php"; ?>

<script>
$(document).ready(function() {

        $('#f_company_id').val('<?php echo ($_SESSION['company_id']); ?>');
        $('#f_company_name').val('<?php echo ($_SESSION['companyname']); ?>');
        $('#f_branch_id').val('<?php echo ($_SESSION['branch_id']); ?>');
        $('#f_branch_name').val('<?php echo ($_SESSION['branchname']); ?>');
});
</script>

<script>
$(document).ready(function() {

    $('#porderrequest').select2({
        dropdownParent: $('#staticBackdrop'),
        width: '100%',
    });
    $('#location').select2({
        dropdownParent: $('#staticBackdrop'),
        width: '100%',
    });

    $('#contactperson').select2({
        dropdownParent: $('#staticBackdrop'),
        width: '100%',
    });
    $('#editcontactperson').select2({
        dropdownParent: $('#porderEditmodal'),
        width: '100%',
    });
    
    $("#product").select2({
		dropdownParent: $('#staticBackdrop'),
		width: '100%',
		ajax: {
			url: "<?php echo base_url() ?>Purchaseorder/GetProductList",
			type: "post",
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					searchTerm: params.term,
                    ordertype: $('#ordertype').val()
				};
			},
			processResults: function (response) {
				return {
					results: response
				};
			},
			cache: true
		}
	});

    $("#editproduct").select2({
		dropdownParent: $('#porderEditmodal'),
		width: '100%',
		ajax: {
			url: "<?php echo base_url() ?>Purchaseorder/GetProductList",
			type: "post",
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					searchTerm: params.term,  // search term
                    ordertype: $('#editordertype').val()
				};
			},
			processResults: function (response) {
				return {
					results: response
				};
			},
			cache: true
		}
	});

    $('#supplier').select2({
        dropdownParent: $('#staticBackdrop'),
        width: '100%',
    });


    var addcheck = '<?php echo $addcheck; ?>';
    var editcheck = '<?php echo $editcheck; ?>';
    var statuscheck = '<?php echo $statuscheck; ?>';
    var deletecheck = '<?php echo $deletecheck; ?>';


    $('#printporder').click(function() {

        printJS({
            printable: 'purchaseview',
            type: 'html',
            css: 'assets/css/styles.css',
            header: 'Purchase Order',
            onPrintSuccess: function() {
                var printButton = document.getElementById('printporder');
                printButton.style.display = 'none';
            }
        });
    });


    $('#dataTable').DataTable({
        "destroy": true,
        "processing": true,
        "serverSide": true,
        dom: "<'row'<'col-sm-5'B><'col-sm-2'l><'col-sm-5'f>>" + "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        responsive: true,
        lengthMenu: [
            [10, 25, 50, -1],
            [10, 25, 50, 'All'],
        ],
        "buttons": [{
                extend: 'csv',
                className: 'btn btn-success btn-sm',
                title: 'Purchase Order Information',
                text: '<i class="fas fa-file-csv mr-2"></i> CSV',
            },
            {
                extend: 'pdf',
                className: 'btn btn-danger btn-sm',
                title: 'Purchase Order Information',
                text: '<i class="fas fa-file-pdf mr-2"></i> PDF',
            },
            {
                extend: 'print',
                title: 'Purchase Order Information',
                className: 'btn btn-primary btn-sm',
                text: '<i class="fas fa-print mr-2"></i> Print',
                customize: function(win) {
                    $(win.document.body).find('table')
                        .addClass('compact')
                        .css('font-size', 'inherit');
                },
            },
        ],
        ajax: {
            url: "<?php echo base_url() ?>scripts/purchaseorderlist.php",
            type: "POST", // you can use GET
            "data": function (d) {
						return $.extend({}, d, {
							"company_id": '<?php echo ($_SESSION['company_id']); ?>',
						});
					}
        },
        "order": [
            [0, "desc"]
        ],
        "columns": [
            {
                "data": "porder_no"
            },
            {
                "data": "orderdate"
            },
            {
                "data": "group"
            },
            {
                "data": "suppliername"
            },
            {
                "data": "contact_person"
            },
            {
                "targets": -1,
                "className": '',
                "data": "confirmstatus_display",
                "render": function(data, type, row) {
                    return data;
                }
            },
            {
                "data": "name"
            },
            {
                "targets": -1,
                "className": '',
                "data": "grnconfirm_display",
                "render": function(data, type, row) {
                    return data;
                }
            }, 
            {
                "targets": -1,
                "className": 'text-right',
                "data": null,
                "render": function(data, type, full) {
                    return addCommas(parseFloat(full['nettotal']).toFixed(2));
                }
            },
            {
                "targets": -1,
                "className": 'text-right',
                "data": null,
                "render": function(data, type, full) {
                    var button = '';
                    if (statuscheck == 1){
                    button += '<button type="button" data-toggle="tooltip" data-placement="bottom" title="Manual Complete" data-url="Purchaseorder/POmanualconfirm/' + full['idtbl_print_porder'] + '"  data-actiontype="6" class="btn btn-warning btn-sm mr-1 btntableaction"><i class="fas fa-clipboard-check"></i></button>';
                    }
                    button += '<button data-toggle="tooltip" data-placement="bottom" title="Edit" class="btn btn-primary btn-sm btnEdit mr-1 ';
                    if (editcheck != 1) {
                        button += 'd-none';
                    }
                    button += '" id="' + full['idtbl_print_porder'] + '"><i class="fas fa-pen"></i></button>';

                    // PDF/Print button — only visible once the PO is approved (confirmstatus == 1)
                    button += '<a href="<?php echo base_url() ?>Purchaseorder/Printinvoice/' +
                        full['idtbl_print_porder'] +
                        '" target="_blank" data-toggle="tooltip" data-placement="bottom" title="Print PO" class="btn btn-danger btn-sm mr-1 ';
                    if (editcheck != 1 || full['confirmstatus'] != 1) {
                        button += 'd-none';
                    }
                    button += '"><i class="fas fa-file-pdf"></i></a>';

                    button += '<button data-toggle="tooltip" data-placement="bottom" title="View PO" class="btn btn-dark btn-sm btnview mr-1" id="' + full[
                            'idtbl_print_porder'] + '" porder_no="' + full[
                            'porder_no'] + '" aproval_id="' + full[
                            'confirmstatus'] + '" check_status="' + full[
                            'check_by'] + '" request_id="' + full[
                            'tbl_print_porder_req_idtbl_print_porder_req'] +
                        '"><i class="fas fa-eye"></i></button>';

                    return button;
                }
            }
        ],
        drawCallback: function(settings) {
            $('[data-toggle="tooltip"]').tooltip();
        }
    });

    $('#dataTable tbody').on('click', '.btnEdit', function () {
    	Swal.fire({
    		title: "Are you sure?",
    		text: "You want to edit this?",
    		icon: "warning",
    		showCancelButton: true,
    		confirmButtonColor: "#3085d6",
    		cancelButtonColor: "#d33",
    		confirmButtonText: "Yes, edit it!"
    	}).then((result) => {
    		if (result.isConfirmed) {
    			console.log("User confirmed the edit");

    			$('#porderEditmodal').modal('show');

    			var id = $(this).attr('id');
    			$.ajax({
    				type: "POST",
    				data: {
    					recordID: id
    				},
    				url: '<?php echo base_url() ?>Purchaseorder/Purchaseorderedit',
    				success: function (result) {
    					try {
    						var obj = JSON.parse(result);
    						console.log(obj);

    						$('#hiddenporderid').val(obj.id);
    						$('#hiddenporderreqid').val(obj.requestid);
    						$('#editorderdate').val(obj.orderdate);
    						$('#editsupplier').val(obj.supplier);
                            $('#editcontactperson').val(obj.contactperson).trigger('change');
                            $('#editordertype').val(obj.type);
                            $('#editremark').val(obj.remark || '');
                            toggleEditServiceColumn();
                            var ordertype = parseInt(obj.type, 10) || 0;

    						$('#edittableorder > tbody').empty();

    						if (obj.items && Array.isArray(obj.items)) {
    							obj.items.forEach(function (item) {

                                    var productID  = item.materialID;
                                    var product    = item.material;
                                    var comment    = item.comment;
                                    var uom        = item.measure;
                                    var uomID      = item.measureID;
                                    var unitprice  = parseFloat(item.unitprice) || 0;
                                    var netprice   = parseFloat(item.netprice) || 0;
                                    var price      = parseFloat(item.packetprice) || 0;   // NEW
                                    var pieces     = item.pieces;
                                    var newqty     = parseFloat(item.qty) || 0;
                                    var showtotal  = addCommas(netprice.toFixed(2));

                                    var row = '<tr class="pointer">';

                                    if (ordertype == 4) {
                                        row += '<td>' + (product || '') + '</td>';
                                        row += '<td>' + (comment || '') + '</td>';
                                    } else {
                                        row += '<td class="d-none"></td>';
                                        row += '<td>' + (product || '') + '</td>';
                                    }

                                    row += '<td class="d-none">' + productID + '</td>';
                                    row += '<td class="text-center">' + newqty + '</td>';
                                    row += '<td class="text-center">' + uom + '</td>';
                                    row += '<td class="d-none">' + uomID + '</td>';
                                    row += '<td class="text-right">' + unitprice + '</td>';
                                    row += '<td class="text-right">' + price + '</td>';        // NEW
                                    row += '<td class="edittotal d-none">' + netprice + '</td>';
                                    row += '<td class="text-right">' + showtotal + '</td>';
                                    row += '<td class="text-right d-none">' + pieces + '</td>';
                                    row += '</tr>';

    								$('#edittableorder > tbody:last').append(row);

    								var sum = 0;
    								$(".edittotal").each(function () {
    									sum += parseFloat($(this).text());
    								});

    								var showsum = addCommas(parseFloat(sum).toFixed(2));
    								$('#editdivgrosstotal').html('Rs. ' + showsum);
    								$('#edithidegrosstotalorder').val(sum);
    								$('#editproduct').focus();
    							});
    						} else {
    							console.error('Error: obj.items is undefined or not an array.');
    						}

    					} catch (e) {
    						console.error('Error parsing JSON:', e);
    					}
    				},
    				error: function (xhr, status, error) {
    					console.error('AJAX request error:', error);
    				}
    			});
    		} else {
    			console.log("User canceled the edit");
    		}
    	});
    });

    $('#dataTable tbody').on('click', '.btnview', function() {
        var id = $(this).attr('id');
        $('#porderid').val(id);
        var porderno = $(this).attr('porder_no');
        $('#reqestid').val($(this).attr('request_id'));
        $('#procode').html(porderno);

        var approvestatus = $(this).attr('aproval_id');
        var checkstatus = $(this).attr('check_status');

        $.ajax({
            type: "POST",
            data: {
                recordID: id
            },
            url: '<?php echo base_url() ?>Purchaseorder/Purchaseorderview',
            success: function(result) {

                $('#porderviewmodal').modal('show');
                $('#viewhtml').html(result);
                
                $('#approvalControls').addClass('d-none');
                $('#btnapprovereject').prop('disabled', true);
                $('#cpstatuscheck').prop('checked', false);

                if (approvestatus > 0) {
                    $('#approvalControls').addClass('d-none');
                    $('#btnapprovereject').prop('disabled', true);

                    if (approvestatus == 1) {

                        $('#alertdiv').html(
                            '<div class="alert alert-success" role="alert">' +
                            '<i class="fas fa-check-circle mr-2"></i>' +
                            ' Purchase Order approved' +
                            '</div>'
                        );

                    } else if (approvestatus == 2) {

                        $('#alertdiv').html(
                            '<div class="alert alert-danger" role="alert">' +
                            '<i class="fas fa-times-circle mr-2"></i>' +
                            ' Purchase Order rejected' +
                            '</div>'
                        );
                    }

                } else if (checkstatus > 0) {

                    $('#approvalControls').removeClass('d-none');
                    $('#btnapprovereject').prop('disabled', false);

                } else {
                    $('#approvalControls').addClass('d-none');
                    $('#btnapprovereject').prop('disabled', true);
                }
                if (checkstatus > 0) {

                    $('#btncheck').addClass('d-none').prop('disabled', true);

                    if (checkstatus == 1) {
                        $('#checkalertdiv').html(
                            '<div class="alert alert-secondary" role="alert">' +
                            '<i class="fas fa-check-circle mr-2"></i>' +
                            ' Purchase Order checked' +
                            '</div>'
                        );
                    }

                } else {
                    $('#btncheck').removeClass('d-none').prop('disabled', false);
                }
            }
        });

        $('#porderviewmodal').on('hidden.bs.modal', function (event) {
            $('#alertdiv').html('');
            $('#checkalertdiv').html('');

            $('#approvalControls').addClass('d-none');
            $('#btnapprovereject').prop('disabled', false);
            $('#cpstatuscheck').prop('checked', false);

            $('#btncheck').removeClass('d-none').prop('disabled', false);
        });

        $.ajax({
            type: "POST",
            data: {
                recordID: id
                // status_id: statusid
            },
            url: '<?php echo base_url() ?>Purchaseorder/porderviewheader',
            success: function(result) {
                // alert(result);
                var obj = JSON.parse(result);
                $('#porderdate').text(obj.orderdate);

                $('#pordersuppliername').text(obj.suppliername);
                $('#pordersuppliercontact').text(obj.suppliercontact);
                $('#porderaddress1').text(obj.address1);
                $('#porderaddress2').text(obj.address2);
                $('#pordercity').text(obj.city);
                $('#porderstate').text(obj.state);

                $('#viewcompanyname').text(obj.companyname);
                $('#viewbranchname').text(obj.branchname);

                if (obj.contactpersonname) {
                    var contactLine = 'Contact Person: ' + obj.contactpersonname;
                    if (obj.contactpersondesignation) {
                        contactLine += ' (' + obj.contactpersondesignation + ')';
                    }
                    $('#pordercontactperson').text(contactLine);
                } else {
                    $('#pordercontactperson').text('');
                }
            }
        });
    });

    $('#btnapprovereject').click(function(){
        Swal.fire({
            title: "Do you want to approve this Purchase Order?",
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: "Approve",
            denyButtonText: `Reject`
        }).then((result) => {
            if (result.isConfirmed) {
                var confirmnot = 1;
                approvejob(confirmnot);
            } else if (result.isDenied) {
                var confirmnot = 2;
                approvejob(confirmnot);
            } 
        });
    });

    $('#btncheck').click(function(){
        Swal.fire({
            title: "Do you want to check this PO?",
            showDenyButton: true,
            showCancelButton: false,
            confirmButtonText: "Check",
        }).then((result) => {
            if (result.isConfirmed) {
                var confirmnot = 1;
                checkjob(confirmnot);
            } 
        });
    });

    $("#supplier").select2({
        dropdownParent: $('#staticBackdrop'),
        width: '100%',
        ajax: {
            url: "<?php echo base_url() ?>Purchaseorder/Getsupplierlist",
            type: "post",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    searchTerm: params.term 
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });

    $('#ordertype').on('change', function () {
        toggleServiceColumn();
    });
    $('#editordertype').on('change', function () {
        toggleEditServiceColumn();
    });
    $("#formsubmit").click(function () {

    	if (!$("#createorderform")[0].checkValidity()) {
    		$("#submitBtn").click();
    	} else {

    		var productID = $('#product').val();
    		var comment = $('#comment').val();
    		var product = $("#product option:selected").text();
    		var unitprice = parseFloat($('#unitprice').val());
    		var vat = parseFloat($('#vat').val());
    		var discount = parseFloat($('#discount').val());
    		var newqty = parseFloat($('#newqty').val());
    		var uomID = $('#uom').val();
    		var pieces = parseFloat($('#piecesper_qty').val());
    		var uom = $("#uom option:selected").text();
    		var ordertype = $('#ordertype').val();

    		var newtotal, newprice;

    		if (pieces !== 0) {
    			newtotal = unitprice * pieces;
    			newprice = (unitprice * pieces) / newqty;
    		} else {
    			newtotal = unitprice * newqty;
    			newprice = 0;
    		}

    		var vatamount = ((newtotal - discount) / 100) * vat;
    		var finaltotal = (newtotal + vatamount) - discount;

    		var total = parseFloat(newtotal);
    		var finaltot = parseFloat(finaltotal);

    		var showtotal = addCommas(total.toFixed(2));
    		var showfinaltot = addCommas(finaltot.toFixed(2));

    		var row = '<tr class="pointer">';

    		if (ordertype == 4) {
    			row += '<td>' + product + '</td>';
    			row += '<td>' + comment + '</td>';
    		} else {
    			row += '<td class="d-none"></td>';
    			row += '<td>' + product + '</td>';
    		}

    		row += '<td class="d-none">' + productID + '</td>';
    		row += '<td class="text-center">' + newqty + '</td>';
    		row += '<td class="text-center">' + uom + '</td>';
    		row += '<td class="d-none">' + uomID + '</td>';
    		row += '<td class="text-right">' + unitprice + '</td>';
    		row += '<td class="text-right">' + newprice + '</td>';
    		row += '<td class="total d-none">' + total + '</td>';
    		row += '<td class="text-right">' + showtotal + '</td>';
    		row += '<td class="text-right d-none">' + pieces + '</td>';
    		row += '</tr>';

    		$('#tableorder tbody').append(row);

    		// 🔄 RESET FIELDS
    		$('#product').val('').trigger('change');
    		$('#unitprice').val('0');
    		$('#saleprice').val('');
    		$('#comment').val('');
    		$('#uom').val('');
    		$('#newqty').val('0');
    		$('#discount').val('0');
    		$('#piecesper_qty').val('0');
    		$('#piecesper_qty_uom').val('');
    		$('#porderrequest').prop('readonly', true).css('pointer-events', 'none');

    		// 🔢 CALCULATIONS
    		var sum = 0;
    		$(".total").each(function () {
    			sum += parseFloat($(this).text());
    		});

    		var showgrosstot = addCommas(sum.toFixed(2));

    		$('#divgrosstotal').html(
    			'<strong style="background-color: yellow;">Final Price</strong> &nbsp;&nbsp;<strong>Rs. ' +
    			showgrosstot + '</strong>'
    		);

    		$('#hidegrosstotalorder').val(sum);

    		$('#product').focus();
    	}
    });
    $("#editformsubmit").click(function() {
        if (!$("#editcreateorderform")[0].checkValidity()) {
            // If the form is invalid, submit it. The form won't actually submit;
            // this will just cause the browser to display the native HTML5 error messages.
            $("#editsubmitBtn").click();
        } else {

            var productID = $('#editproduct').val();
            var comment = $('#editcomment').val();
            var product = $("#editproduct option:selected").text();
            var unitprice = parseFloat($('#editunitprice').val());
            var vat = parseFloat($('#editvat').val());
            var discount = parseFloat($('#editdiscount').val());
            var newqty = parseFloat($('#editnewqty').val());
            var uomID = $('#edituom').val();
            var pieces = parseFloat($('#editpiecesper_qty').val());
            var uom = $("#edituom option:selected").text();
            var ordertype = $('#editordertype').val();
            var newtotal;
            var newprice;
            if (pieces !== 0) {
                newtotal = unitprice * pieces;
                newprice = unitprice * pieces / newqty;
            } else {
                newtotal = unitprice * newqty;
                newprice = 0;
            }
            var vatamount = parseFloat(((newtotal - discount) / 100) * vat);
            var finaltotal = parseFloat((newtotal + vatamount) - discount);

            var totdiscount = parseFloat(discount);
            var totvat = parseFloat(vatamount);
            var total = parseFloat(newtotal);
            var finaltot = parseFloat(finaltotal);
            var showfinaltot = addCommas(parseFloat(finaltot).toFixed(2));
            var showtotal = addCommas(parseFloat(total).toFixed(2));
            var showtotdiscount = addCommas(parseFloat(totdiscount).toFixed(2));
            var showtotvat = addCommas(parseFloat(totvat).toFixed(2));

            // Same 10-cell layout as the item-loading block above, so both
            // "loaded" rows and "newly added" rows line up under the same headers.
            var row = '<tr class="pointer">';

            if (ordertype == 4) {
                row += '<td>' + product + '</td>';
                row += '<td>' + comment + '</td>';
            } else {
                row += '<td class="d-none"></td>';
                row += '<td>' + product + '</td>';
            }

            row += '<td class="d-none">' + productID + '</td>';
            row += '<td class="text-center">' + newqty + '</td>';
            row += '<td class="text-center">' + uom + '</td>';
            row += '<td class="d-none">' + uomID + '</td>';
            row += '<td class="text-right">' + unitprice.toFixed(2) + '</td>';
            row += '<td class="text-right">' + newprice.toFixed(2) + '</td>';   // NEW
            row += '<td class="edittotal d-none">' + total + '</td>';
            row += '<td class="text-right">' + showtotal + '</td>';
            row += '<td class="text-right d-none">' + pieces + '</td>';
            row += '</tr>';

            // If we're editing an existing row, replace it in place instead of appending a new one
            if (editingRow) {
                editingRow.replaceWith(row);
                editingRow = null;
            } else {
                $('#edittableorder > tbody:last').append(row);
            }

            $('#edittableorder tr').removeClass('table-warning');

            $('#editproduct').val('').trigger('change');
            $('#editunitprice').val('');
            $('#editsaleprice').val('');
            $('#editcomment').val('');
            $('#edituom').val('');
            $('#editnewqty').val('0');
            $('#editdiscount').val('0');
            $('#editpiecesper_qty').val('0');
            $('#editpiecesper_qty_uom').val('');
            $('#editporderrequest').prop('readonly', true).css('pointer-events', 'none');


            var sum = 0;
            $(".edittotal").each(function() {
                sum += parseFloat($(this).text());
            });

            var showgrosstot = addCommas(parseFloat(sum).toFixed(2));

            $('#editdivgrosstotal').html(
                '<strong style="background-color: yellow;">Final Price</strong> &nbsp; &nbsp;<strong>Rs.<strong> <strong>' +
                showgrosstot);
            $('#edithidegrosstotalorder').val(sum);
            $('#editproduct').focus();


            var sum = 0;
            $(".total_vat").each(function() {
                sum += parseFloat($(this).text());
            });

            var showtotvat = addCommas(parseFloat(sum).toFixed(2));

            $('#divtotalvat').html('Vat Total &nbsp; &nbsp; Rs.' + showtotvat);
            $('#hidevatlorder').val(sum);
            $('#product').focus();

            var sum = 0;
            $(".total_discount").each(function() {
                sum += parseFloat($(this).text());
            });

            var showtotdiscount = addCommas(parseFloat(sum).toFixed(2));

            $('#divtotaldiscount').html('Discount &nbsp; &nbsp; Rs.' + showtotdiscount);
            $('#hidediscountlorder').val(sum);
            $('#product').focus();

            var sum = 0;
            $(".final_total").each(function() {
                sum += parseFloat($(this).text());
            });

            var showsum = addCommas(parseFloat(sum).toFixed(2));

            $('#divtotal').html(
                '<strong style="background-color: yellow;">Final Price</strong> &nbsp; &nbsp;<strong>Rs.<strong> <strong>' +
                showsum + '</strong>');
            $('#hidetotalorder').val(sum);
            $('#product').focus();
        }
    });

    $('#tableorder').on('click', 'tr', function() {
        var r = confirm("Are you sure, You want to remove this product ? ");
        if (r == true) {
            $(this).closest('tr').remove();

            var sum = 0;
            $(".final_total").each(function() {
                sum += parseFloat($(this).text());
            });

            var showsum = addCommas(parseFloat(sum).toFixed(2));

            $('#divtotal').html('Rs. ' + showsum);
            $('#hidetotalorder').val(sum);
            $('#product').focus();
        }
    });
    // Top-level vars — declare once, alongside your other vars like `var tempgrntype;`
    var editingRow = null; // no longer needed for replace-in-place, but kept for compatibility
    var suppressEditProductChange = false; // prevents the AJAX price-fetch firing on programmatic set

    // Row click — populates the edit form fields AND removes the row from the table
    $('#edittableorder').on('click', 'tr', function () {
        var $row = $(this);
        var cells = $row.find('td');
        var r = confirm("Are you sure, you want to remove this product?");
        if (r) {
            // Cell order: [0] ServiceItem [1] ItemName/Comment [2] ProductID [3] Qty
            // [4] Uom [5] UomID [6] UnitPrice [7] Price [8] edittotal [9] Total [10] pieces
            var ordertype = $('#editordertype').val();

            var productID   = $(cells[2]).text().trim();
            var productName = (ordertype == 4) ? $(cells[0]).text().trim() : $(cells[1]).text().trim();
            var qty         = $(cells[3]).text().trim();
            var uomID       = $(cells[5]).text().trim();
            var unitprice   = $(cells[6]).text().trim();
            var comment     = (ordertype == 4) ? $(cells[1]).text().trim() : '';

            // Select2's options only exist for products already searched — inject one if missing
            if ($('#editproduct').find('option[value="' + productID + '"]').length === 0) {
                var opt = new Option(productName, productID, true, true);
                $('#editproduct').append(opt);
            }

            suppressEditProductChange = true;
            $('#editproduct').val(productID).trigger('change');
            suppressEditProductChange = false;

            $('#editnewqty').val(qty);
            $('#editunitprice').val(unitprice);
            $('#editcomment').val(comment);
            $('#edituom').val(uomID);

            // Fetch the actual conversion unit (e.g. "Sheet") for this product + uom,
            // same call the normal #edituom change handler uses — the table row itself
            // never stores this label, so it has to be looked up, not read off a cell.
            if (uomID && productID) {
                $.ajax({
                    type: "POST",
                    url: 'Purchaseorder/Getpiecesforqty',
                    data: {
                        recordID: uomID,
                        productId: productID,
                        qty: qty
                    },
                    success: function (result) {
                        var obj = JSON.parse(result);
                        $('#editpiecesper_qty').val(obj.piecesper_qty);
                        $('#editpiecesper_qty_uom').val(obj.measure_type);
                    }
                });
            } else {
                $('#editpiecesper_qty').val(0);
                $('#editpiecesper_qty_uom').val('');
            }

            // Remove the row from the table now that its data has been pulled into the form
            $row.remove();

            // Recalculate the running total after removal
            var sum = 0;
            $(".edittotal").each(function () {
                sum += parseFloat($(this).text());
            });
            var showsum = addCommas(parseFloat(sum).toFixed(2));
            $('#editdivgrosstotal').html('Rs. ' + showsum);
            $('#edithidegrosstotalorder').val(sum);

            editingRow = null; // row no longer exists, so "Add to list" will always append fresh
        }
    });

    $('#btncreateorder').click(function () {
        // disable button while processing
        $('#btncreateorder').prop('disabled', true).html(
            '<i class="fas fa-circle-notch fa-spin mr-2"></i> Creating Order...'
        );

        // build table data
        var jsonObj = [];
        $("#tableorder tbody tr").each(function () {
            var item = {};
            $(this).find('td').each(function (col_idx) {
                item["col_" + (col_idx + 1)] = $(this).text();
            });
            jsonObj.push(item);
        });

        // if no rows
        if (jsonObj.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "No Data",
                text: "Please add items before creating an order.",
            });
            $('#btncreateorder').prop('disabled', false).html("Create Order");
            return;
        }

        // collect form data
        var orderData = {
            tableData: jsonObj,
            orderdate: $('#orderdate').val(),
            ordertype: $('#ordertype').val(),
            duedate: $('#duedate').val(),
            total: $('#hidetotalorder').val(),
            discounttotal: $('#hidediscountlorder').val(),
            vatamounttotal: $('#hidevatlorder').val(),
            grosstotal: $('#hidegrosstotalorder').val(),
            remark: $('#remark').val(),
            supplier: $('#supplier').val(),
            contactperson: $('#contactperson').val(),
            company_id: $('#f_company_id').val(),
            branch_id: $('#f_branch_id').val(),
            porderrequest: $('#porderrequest').val()
        };

        Swal.fire({
            title: "",
            html: '<div class="div-spinner"><div class="custom-loader"></div></div>',
            allowOutsideClick: false,
            showConfirmButton: false,
            backdrop: "rgba(255, 255, 255, 0.5)",
            customClass: {
                popup: "fullscreen-swal"
            },
            didOpen: () => {
                document.body.style.overflow = "hidden";

                $.ajax({
                    type: "POST",
                    url: "Purchaseorder/Purchaseorderinsertupdate",
                    data: orderData,
                    success: function (result) {
                        Swal.close();
                        document.body.style.overflow = 'auto';

                        var obj = JSON.parse(result);

                        if (obj.status == 1) {
                            actionreload(obj.action);
                        } else {
                            action(obj.action);
                        }
                    },
                    error: function () {
                        Swal.close();
                        document.body.style.overflow = 'auto';

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Something went wrong. Please try again later.'
                        });
                    }
                });
            },
        });
    });


    $('#editbtncreateorder').click(function () {
        $('#editbtncreateorder').prop('disabled', true).html(
            '<i class="fas fa-circle-notch fa-spin mr-2"></i> Update Order'
        );

        var jsonObj = [];
        $("#edittableorder tbody tr").each(function () {
            var item = {};
            $(this).find('td').each(function (col_idx) {
                item["col_" + (col_idx + 1)] = $(this).text();
            });
            jsonObj.push(item);
        });

        // If no rows in table
        if (jsonObj.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "No Data",
                text: "Please add items before updating an order.",
            });
            $('#editbtncreateorder').prop('disabled', false).html("Update Order");
            return;
        }

        var orderData = {
            tableData: jsonObj,
            orderdate: $('#editorderdate').val(),
            ordertype: $('#editordertype').val(),
            duedate: $('#editduedate').val(),
            total: $('#edithidetotalorder').val(),
            discounttotal: $('#edithidediscountlorder').val(),
            vatamounttotal: $('#edithidevatlorder').val(),
            grosstotal: $('#edithidegrosstotalorder').val(),
            remark: $('#editremark').val(),
            supplier: $('#editsupplier').val(),
            contactperson: $('#editcontactperson').val(),
            company_id: $('#f_company_id').val(),
            branch_id: $('#f_branch_id').val(),
            porderID: $('#hiddenporderid').val(),
            porderreqID: $('#hiddenporderreqid').val()
        };

        $.ajax({
            type: "POST",
            url: "Purchaseorder/Purchaseorderupdate",
            data: orderData,
                success: function (result) {
                    $('#staticBackdrop').modal('hide');

                    var obj = JSON.parse(result);

                    if (obj.status == 1) {
                        actionreload(obj.action);
                    } else {
                        action(obj.action);
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong. Please try again later.'
                    });
                }
        });
    });


    $('#uom').change(function() {
        let uomID = $(this).val(); 
        let productId = $('#product').val();
        let qty = $('#newqty').val();

        $.ajax({
            type: "POST",
            url: 'Purchaseorder/Getpiecesforqty',
            data: {
                recordID: uomID,
                productId: productId,
                qty: qty
            },
            success: function(result) {
                var obj = JSON.parse(result);
                $('#piecesper_qty').val(obj.piecesper_qty);
                $('#piecesper_qty_uom').val(obj.measure_type);
            }
        });
    });

    $('#edituom').change(function () {
        let uomID = $(this).val();
        let productId = $('#editproduct').val();
        let qty = $('#editnewqty').val();

        $.ajax({
            type: "POST",
            url: 'Purchaseorder/Getpiecesforqty',
            data: {
                recordID: uomID,
                productId: productId,
                qty: qty
            },
            success: function (result) {
                var obj = JSON.parse(result);
                $('#editpiecesper_qty').val(obj.piecesper_qty);
                $('#editpiecesper_qty_uom').val(obj.measure_type);
            }
        });
    });

    var tempgrntype;
    var editingRow = null;                  
    var suppressEditProductChange = false; 

    $('#porderrequest').change(function () {
        var porderID = $(this).val();

        $.ajax({
            type: "POST",
            data: {
                recordID: porderID
            },
            url: 'Purchaseorder/Getporderreqdetails',
            success: function (response) {
                var result = JSON.parse(response);
                $('#requestitem').empty();

                if (result.length > 0) {
                    $.each(result, function (index, item) {
                        var listItem = '<li class="list-group-item bg-warning-soft">';

                        listItem += '<strong>' + item.requestname + '</strong> - ';
                        listItem += item.qty + ' ' + item.measure_type;

                        if (item.comment && item.comment !== "") {
                            listItem += ' <em>(' + item.comment + ')</em>';
                        }

                        // Show last two GRN price/date/qty rows (only present for "Exist" materials)
                        if (item.grnhistory && item.grnhistory.length > 0) {
                            listItem += '<table class="table table-sm table-bordered mb-0 mt-2 bg-white">';
                            listItem += '<thead><tr>' +
                                '<th class="small py-1">GRN Date</th>' +
                                '<th class="small py-1 text-right">Qty</th>' +
                                '<th class="small py-1 text-right">Unit Price</th>' +
                                '</tr></thead><tbody>';

                            $.each(item.grnhistory, function (i, grn) {
                                listItem += '<tr>' +
                                    '<td class="small py-1">' + grn.grndate + '</td>' +
                                    '<td class="small py-1 text-right">' + grn.qty + '</td>' +
                                    '<td class="small py-1 text-right">' + parseFloat(grn.unitprice).toFixed(2) + '</td>' +
                                    '</tr>';
                            });

                            listItem += '</tbody></table>';
                        }

                        listItem += '</li>';

                        $('#requestitem').append(listItem);

                        if (index === 0) {
                            $('#requestordertype').val(item.order_type);
                        }
                    });
                }
            },
        });
    });

    $('#product').change(function () {
    	var productID = $(this).val();
    	var supplier = $('#supplier').val();

    		$.ajax({
    			type: "POST",
    			url: 'Purchaseorder/Getproductinfoaccoproduct',
    			data: {
    				recordID: productID,
    				supplier: supplier 			
                },
    			success: function (result) {
    				var obj = JSON.parse(result);
    				$('#unitprice').val(obj.unitprice);
    			}
    		});
    });

    $('#editproduct').change(function () {
        if (suppressEditProductChange) {
            return;
        }
        var productID = $(this).val();
        var ordertype = parseInt($('#editordertype').val(), 10);
        var supplier = $('#editsupplier').val();

        if (ordertype === 4) {
            $('#editunitprice').val('');
            return;
        }

        $.ajax({
            type: "POST",
            url: 'Purchaseorder/Getproductinfoaccoproduct',
            data: {
                recordID: productID,
                supplier: supplier
            },
            success: function (result) {
                var obj = JSON.parse(result);
                $('#editunitprice').val(obj.unitprice || 0);
            }
        });
    });

});



function deactive_confirm() {
    return confirm("Are you sure you want to deactive this?");
}

function active_confirm() {
    return confirm("Are you sure you want to confirm this purchase order?");
}

function delete_confirm() {
    return confirm("Are you sure you want to remove this?");
}

function addCommas(nStr) {
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}

function action(data) { //alert(data);
    var obj = JSON.parse(data);
    $.notify({
        // options
        icon: obj.icon,
        title: obj.title,
        message: obj.message,
        url: obj.url,
        target: obj.target
    }, {
        // settings
        element: 'body',
        position: null,
        type: obj.type,
        allow_dismiss: true,
        newest_on_top: false,
        showProgressbar: false,
        placement: {
            from: "top",
            align: "center"
        },
        offset: 100,
        spacing: 10,
        z_index: 1031,
        delay: 5000,
        timer: 1000,
        url_target: '_blank',
        mouse_over: null,
        animate: {
            enter: 'animated fadeInDown',
            exit: 'animated fadeOutUp'
        },
        onShow: null,
        onShown: null,
        onClose: null,
        onClosed: null,
        icon_type: 'class',
        template: '<div data-notify="container" class="col-xs-11 col-sm-3 alert alert-{0}" role="alert">' +
            '<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
            '<span data-notify="icon"></span> ' +
            '<span data-notify="title">{1}</span> ' +
            '<span data-notify="message">{2}</span>' +
            '<div class="progress" data-notify="progressbar">' +
            '<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
            '</div>' +
            '<a href="{3}" target="{4}" data-notify="url"></a>' +
            '</div>'
    });
}
</script>
<script>
function approvejob(confirmnot){
    var cpstatus = $('#cpstatuscheck').is(':checked') ? 1 : 0;

    Swal.fire({
        title: '',
        html: '<div class="div-spinner"><div class="custom-loader"></div></div>',
        allowOutsideClick: false,
        showConfirmButton: false, // Hide the OK button
        backdrop: `
            rgba(255, 255, 255, 0.5) 
        `,
        customClass: {
            popup: 'fullscreen-swal'
        },
        didOpen: () => {
            document.body.style.overflow = 'hidden';

            $.ajax({
                type: "POST",
                data: {
                    porderid: $('#porderid').val(),
                    reqestid: $('#reqestid').val(),
                    confirmnot: confirmnot,
                    cpstatus: cpstatus
                },
                url: '<?php echo base_url() ?>Purchaseorder/Purchaseorderstatus',
                success: function(result) {
                    Swal.close();
                    document.body.style.overflow = 'auto';
                    var obj = JSON.parse(result);
                    if(obj.status==1){
                        actionreload(obj.action);
                    }
                    else{
                        action(obj.action);
                    }
                },
                error: function(error) {
                    // Close the SweetAlert on error
                    Swal.close();
                    document.body.style.overflow = 'auto';
                    
                    // Show an error alert
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong. Please try again later.'
                    });
                }
            });
        }
    });
}
function checkjob(confirmnot){
    Swal.fire({
        title: '',
        html: '<div class="div-spinner"><div class="custom-loader"></div></div>',
        allowOutsideClick: false,
        showConfirmButton: false, // Hide the OK button
        backdrop: `
            rgba(255, 255, 255, 0.5) 
        `,
        customClass: {
            popup: 'fullscreen-swal'
        },
        didOpen: () => {
            document.body.style.overflow = 'hidden';

            $.ajax({
                type: "POST",
                data: {
                    requestid: $('#porderid').val(),
                    confirmnot: confirmnot
                },
                url: '<?php echo base_url() ?>Purchaseorder/Purchaseordercheckstatus',
                success: function(result) {
                    Swal.close();
                    document.body.style.overflow = 'auto';
                    var obj = JSON.parse(result);
                    if(obj.status==1){
                        actionreload(obj.action);
                    }
                    else{
                        action(obj.action);
                    }
                },
                error: function(error) {
                    // Close the SweetAlert on error
                    Swal.close();
                    document.body.style.overflow = 'auto';
                    
                    // Show an error alert
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong. Please try again later.'
                    });
                }
            });
        }
    });
}
function toggleServiceColumn() {
    var ordertype = $('#ordertype').val();

    if (ordertype == 4) {
        $('#thServiceItem').removeClass('d-none');

        $('#tableorder tbody tr').each(function () {
            $(this).find('td:eq(0)').removeClass('d-none');
        });

    } else {
        $('#thServiceItem').addClass('d-none');

        $('#tableorder tbody tr').each(function () {
            $(this).find('td:eq(0)').addClass('d-none');
        });
    }
}
function toggleEditServiceColumn() {
    var ordertype = $('#editordertype').val();

    if (ordertype == 4) {
        $('#editThServiceItem').removeClass('d-none');
        $('#edittableorder tbody tr').each(function () {
            $(this).find('td:eq(0)').removeClass('d-none');
        });
    } else {
        $('#editThServiceItem').addClass('d-none');
        $('#edittableorder tbody tr').each(function () {
            $(this).find('td:eq(0)').addClass('d-none');
        });
    }
}
</script>

<?php include "include/footer.php"; ?>