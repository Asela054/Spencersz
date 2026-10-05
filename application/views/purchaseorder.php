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
                                                <th>Supplier</th>
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
<!-- Create Modal -->
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
							<div class="form-group mb-1">
								<label class="small font-weight-bold text-dark">PO Request</label>
								<select class="form-control form-control-sm selecter2 px-0" name="porderrequest"
									id="porderrequest">
									<option value="">Select</option>
									<?php foreach($porderlist->result() as $rowporderlist){ ?>
									<option value="<?php echo $rowporderlist->idtbl_porder_req ?>">
										<?php echo $rowporderlist->porder_req_no ?></option>
									<?php } ?>
								</select>
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
									<select class="form-control form-control-sm" name="supplier" id="supplier" required>
										<option value="">Select</option>
									</select>
								</div>
							</div>
							<div class="form-row mb-1">
								<div class="col" id="productFields">
									<div class="form-group mb-1">
										<label class="small font-weight-bold text-dark">Product *</label>
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
									<label class="small font-weight-bold text-dark">Unit</label>
									<input type="text" id="uom" name="uom" class="form-control form-control-sm" readonly>
								</div>
							</div>

							<div class="form-row mb-1">
								<div class="col">
									<label class="small font-weight-bold text-dark">Unit Price</label>
									<input type="text" id="unitprice" name="unitprice" class="form-control form-control-sm"
										value="0" step="any">
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
						</form>
					</div>
					<div class="col-sm-12 col-md-12 col-lg-7 col-xl-7">
							<div class="scrollbar pb-3" id="style-3">
								<table class="table table-striped table-bordered table-sm small" id="tableorder">
									<thead>
										<tr>
											<th>Item Name</th>
											<th class="d-none">ProductID</th>
											<th class="text-center">Qty</th>
											<th class="text-center">Unit</th>
											<th class="text-right">Unit Price</th>
											<th>Comment</th>
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

<!-- Edit Modal -->
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
									id="hiddenporderreqid">
								<label class="small font-weight-bold text-dark">Order Date*</label>
								<input type="date" class="form-control form-control-sm" placeholder="" name="editorderdate"
									id="editorderdate" value="<?php echo date('Y-m-d')?>" required>
							</div>
							<div id="editSupplierFields">
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
							<div class="form-row mb-1">
								<div class="col" id="editProductFields">
									<div class="form-group mb-1">
										<label class="small font-weight-bold text-dark">Product *</label>
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
									<label class="small font-weight-bold text-dark">Unit</label>
									<input type="text" id="edituom" name="edituom" class="form-control form-control-sm" readonly>
								</div>
							</div>

							<div class="form-row mb-1">
								<div class="col">
									<label class="small font-weight-bold text-dark">Unit Price</label>
									<input type="text" id="editunitprice" name="editunitprice" class="form-control form-control-sm"
										value="0" step="any">
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
                                            <th>Item Name</th>
                                            <th class="d-none">ProductID</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-center">Unit</th>
                                            <th class="text-right">Unit Price</th>
                                            <th>Comment</th>
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

<!-- View Modal -->
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
							<p style="margin-bottom: 2px;" class="text-left"><span id="pordersuppliername"></span></p>
							<p style="margin-bottom: 2px;" class="text-left"><span id="pordersuppliercontact"></span></p>
							<p style="margin-bottom: 2px;" class="text-left"><span id="porderaddress1"></span></p>
							<p style="margin-bottom: 2px;" class="text-left"><span id="porderaddress2"></span></p>
							<p style="margin-bottom: 2px;" class="text-left"><span id="pordercity"></span></p>
							<p style="margin-bottom: 2px;" class="text-left"><span id="porderstate"></span></p>
						</div>
					</div>
					<div id="viewhtml"></div>
                    <div class="col-12 text-right">
                        <hr>
                        <?php if($approvecheck==1){ ?>
                            <div id="approvalControls" class="d-none">
                                <div class="custom-control custom-checkbox d-inline-block mr-3 align-middle">
                                    <input type="checkbox" class="custom-control-input" id="cpstatuscheck" name="cpstatuscheck">
                                    <label class="custom-control-label" for="cpstatuscheck">Include Contact No</label>
                                </div>
                                <button id="btnapprovereject" class="btn btn-primary btn-sm px-3 mb-2">
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

    $("#product").select2({
		dropdownParent: $('#staticBackdrop'),
		width: '100%',
		ajax: {
			url: "<?php echo base_url() ?>Purchaseorder/GetProductList",
			type: "post",
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return { searchTerm: params.term };
			},
			processResults: function (response) {
				return { results: response };
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
				return { searchTerm: params.term };
			},
			processResults: function (response) {
				return { results: response };
			},
			cache: true
		}
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
                return { searchTerm: params.term };
            },
            processResults: function (response) {
                return { results: response };
            },
            cache: true
        }
    });

    var addcheck = '<?php echo $addcheck; ?>';
    var editcheck = '<?php echo $editcheck; ?>';
    var statuscheck = '<?php echo $statuscheck; ?>';
    var deletecheck = '<?php echo $deletecheck; ?>';

    var editingRow = null;
    var suppressEditProductChange = false;

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
            type: "POST",
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
            { "data": "porder_no" },
            { "data": "orderdate" },
            { "data": "suppliername" },
            {
                "targets": -1,
                "className": '',
                "data": "confirmstatus_display",
                "render": function(data, type, row) {
                    return data;
                }
            },
            { "data": "name" },
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
                    button += '<button type="button" data-toggle="tooltip" data-placement="bottom" title="Manual Complete" data-url="Purchaseorder/POmanualconfirm/' + full['idtbl_porder'] + '"  data-actiontype="6" class="btn btn-warning btn-sm mr-1 btntableaction"><i class="fas fa-clipboard-check"></i></button>';
                    }
                    button += '<button data-toggle="tooltip" data-placement="bottom" title="Edit" class="btn btn-primary btn-sm btnEdit mr-1 ';
                    if (editcheck != 1) {
                        button += 'd-none';
                    }
                    button += '" id="' + full['idtbl_porder'] + '"><i class="fas fa-pen"></i></button>';

                    button += '<a href="<?php echo base_url() ?>Purchaseorder/Printinvoice/' +
                        full['idtbl_porder'] +
                        '" target="_blank" data-toggle="tooltip" data-placement="bottom" title="Print PO" class="btn btn-danger btn-sm mr-1 ';
                    if (editcheck != 1 || full['confirmstatus'] != 1) {
                        button += 'd-none';
                    }
                    button += '"><i class="fas fa-file-pdf"></i></a>';

                    button += '<button data-toggle="tooltip" data-placement="bottom" title="View PO" class="btn btn-dark btn-sm btnview mr-1" id="' + full[
                            'idtbl_porder'] + '" porder_no="' + full[
                            'porder_no'] + '" aproval_id="' + full[
                            'confirmstatus'] + '" check_status="' + full[
                            'check_by'] + '" request_id="' + full[
                            'tbl_porder_req_idtbl_porder_req'] +
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
    	var id = $(this).attr('id');
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

    			$('#porderEditmodal').modal('show');

    			$.ajax({
    				type: "POST",
    				data: { recordID: id },
    				url: '<?php echo base_url() ?>Purchaseorder/Purchaseorderedit',
    				success: function (result) {
    					try {
    						var obj = JSON.parse(result);

    						$('#hiddenporderid').val(obj.id);
    						$('#hiddenporderreqid').val(obj.requestid);
    						$('#editorderdate').val(obj.orderdate);
    						$('#editsupplier').val(obj.supplier);
                            $('#editremark').val(obj.remark || '');

    						$('#edittableorder > tbody').empty();

    						if (obj.items && Array.isArray(obj.items)) {
    							obj.items.forEach(function (item) {
                                    var unitprice = parseFloat(item.unitprice) || 0;
                                    var netprice  = parseFloat(item.netprice) || 0;
                                    var newqty    = parseFloat(item.qty) || 0;
                                    var showtotal = addCommas(netprice.toFixed(2));

                                    var row = '<tr class="pointer">';
                                    row += '<td>' + esc(item.material) + '</td>';
                                    row += '<td class="d-none">' + item.materialID + '</td>';
                                    row += '<td class="text-center">' + newqty + '</td>';
                                    row += '<td class="text-center">' + esc(item.unit) + '</td>';
                                    row += '<td class="text-right">' + unitprice.toFixed(2) + '</td>';
                                    row += '<td>' + esc(item.comment) + '</td>';
                                    row += '<td class="edittotal d-none">' + netprice + '</td>';
                                    row += '<td class="text-right">' + showtotal + '</td>';
                                    row += '</tr>';

    								$('#edittableorder > tbody:last').append(row);
    							});
    							recalcTotal('.edittotal', '#editdivgrosstotal', '#edithidegrosstotalorder');
    							$('#editproduct').focus();
    						}
    					} catch (e) {
    						console.error('Error parsing JSON:', e);
    					}
    				},
    				error: function (xhr, status, error) {
    					console.error('AJAX request error:', error);
    				}
    			});
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
            data: { recordID: id },
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

                    $('#checkalertdiv').html(
                        '<div class="alert alert-secondary" role="alert">' +
                        '<i class="fas fa-check-circle mr-2"></i>' +
                        ' Purchase Order checked' +
                        '</div>'
                    );
                } else {
                    $('#btncheck').removeClass('d-none').prop('disabled', false);
                }
            }
        });

        $('#porderviewmodal').off('hidden.bs.modal').on('hidden.bs.modal', function (event) {
            $('#alertdiv').html('');
            $('#checkalertdiv').html('');

            $('#approvalControls').addClass('d-none');
            $('#btnapprovereject').prop('disabled', false);
            $('#cpstatuscheck').prop('checked', false);

            $('#btncheck').removeClass('d-none').prop('disabled', false);
        });

        $.ajax({
            type: "POST",
            data: { recordID: id },
            url: '<?php echo base_url() ?>Purchaseorder/porderviewheader',
            success: function(result) {
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
                approvejob(1);
            } else if (result.isDenied) {
                approvejob(2);
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
                checkjob(1);
            } 
        });
    });

    $("#formsubmit").click(function () {

    	if (!$("#createorderform")[0].checkValidity()) {
    		$("#submitBtn").click();
    	} else {

    		var productID = $('#product').val();
    		var comment = $('#comment').val();
    		var product = $("#product option:selected").text();
    		var unitprice = parseFloat($('#unitprice').val());
    		var newqty = parseFloat($('#newqty').val());
    		var unit = $('#uom').val();

    		if (!productID || isNaN(newqty) || newqty <= 0 || isNaN(unitprice)) {
    			Swal.fire({ icon: 'warning', title: 'Missing data', text: 'Select a product and enter a valid qty and unit price.' });
    			return;
    		}

    		var total = unitprice * newqty;
    		var showtotal = addCommas(total.toFixed(2));

    		var row = '<tr class="pointer">';
    		row += '<td>' + esc(product) + '</td>';
    		row += '<td class="d-none">' + productID + '</td>';
    		row += '<td class="text-center">' + newqty + '</td>';
    		row += '<td class="text-center">' + esc(unit) + '</td>';
    		row += '<td class="text-right">' + unitprice.toFixed(2) + '</td>';
    		row += '<td>' + esc(comment) + '</td>';
    		row += '<td class="total d-none">' + total + '</td>';
    		row += '<td class="text-right">' + showtotal + '</td>';
    		row += '</tr>';

    		$('#tableorder tbody').append(row);

    		$('#product').val('').trigger('change');
    		$('#unitprice').val('0');
    		$('#comment').val('');
    		$('#uom').val('');
    		$('#newqty').val('');
    		$('#porderrequest').prop('readonly', true).css('pointer-events', 'none');

    		recalcTotal('.total', '#divgrosstotal', '#hidegrosstotalorder', true);
    		$('#product').focus();
    	}
    });

    $("#editformsubmit").click(function() {
        if (!$("#editcreateorderform")[0].checkValidity()) {
            $("#editsubmitBtn").click();
        } else {

            var productID = $('#editproduct').val();
            var comment = $('#editcomment').val();
            var product = $("#editproduct option:selected").text();
            var unitprice = parseFloat($('#editunitprice').val());
            var newqty = parseFloat($('#editnewqty').val());
            var unit = $('#edituom').val();

            if (!productID || isNaN(newqty) || newqty <= 0 || isNaN(unitprice)) {
                Swal.fire({ icon: 'warning', title: 'Missing data', text: 'Select a product and enter a valid qty and unit price.' });
                return;
            }

            var total = unitprice * newqty;
            var showtotal = addCommas(total.toFixed(2));

            var row = '<tr class="pointer">';
            row += '<td>' + esc(product) + '</td>';
            row += '<td class="d-none">' + productID + '</td>';
            row += '<td class="text-center">' + newqty + '</td>';
            row += '<td class="text-center">' + esc(unit) + '</td>';
            row += '<td class="text-right">' + unitprice.toFixed(2) + '</td>';
            row += '<td>' + esc(comment) + '</td>';
            row += '<td class="edittotal d-none">' + total + '</td>';
            row += '<td class="text-right">' + showtotal + '</td>';
            row += '</tr>';

            $('#edittableorder > tbody:last').append(row);

            $('#editproduct').val('').trigger('change');
            $('#editunitprice').val('0');
            $('#editcomment').val('');
            $('#edituom').val('');
            $('#editnewqty').val('');

            recalcTotal('.edittotal', '#editdivgrosstotal', '#edithidegrosstotalorder', true);
            $('#editproduct').focus();
        }
    });

    $('#tableorder').on('click', 'tr', function() {
        if ($(this).closest('thead').length) { return; }
        var r = confirm("Are you sure, You want to remove this product ? ");
        if (r == true) {
            $(this).closest('tr').remove();
            recalcTotal('.total', '#divgrosstotal', '#hidegrosstotalorder', true);
            $('#product').focus();
        }
    });

    // Click an existing row in the edit table: move it back into the form
    $('#edittableorder').on('click', 'tr', function () {
        if ($(this).closest('thead').length) { return; }
        var $row = $(this);
        var cells = $row.find('td');
        var r = confirm("Are you sure, you want to remove this product?");
        if (r) {
            // [0] name [1] productID [2] qty [3] unit [4] unitprice [5] comment [6] hidden total [7] total
            var productID   = $(cells[1]).text().trim();
            var productName = $(cells[0]).text().trim();
            var qty         = $(cells[2]).text().trim();
            var unit        = $(cells[3]).text().trim();
            var unitprice   = $(cells[4]).text().trim();
            var comment     = $(cells[5]).text().trim();

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
            $('#edituom').val(unit);

            $row.remove();
            recalcTotal('.edittotal', '#editdivgrosstotal', '#edithidegrosstotalorder');

            editingRow = null;
        }
    });

    $('#btncreateorder').click(function () {
        var jsonObj = [];
        $("#tableorder tbody tr").each(function () {
            var item = {};
            $(this).find('td').each(function (col_idx) {
                item["col_" + (col_idx + 1)] = $(this).text();
            });
            jsonObj.push(item);
        });

        if (jsonObj.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "No Data",
                text: "Please add items before creating an order.",
            });
            return;
        }

        if (!$('#supplier').val()) {
            Swal.fire({ icon: "warning", title: "Supplier required", text: "Please select a supplier." });
            return;
        }

        $('#btncreateorder').prop('disabled', true).html(
            '<i class="fas fa-circle-notch fa-spin mr-2"></i> Creating Order...'
        );

        var orderData = {
            tableData: jsonObj,
            orderdate: $('#orderdate').val(),
            grosstotal: $('#hidegrosstotalorder').val(),
            remark: $('#remark').val(),
            supplier: $('#supplier').val(),
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
                            $('#btncreateorder').prop('disabled', false).html('<i class="fas fa-save"></i>&nbsp;Create Purchase Order');
                            action(obj.action);
                        }
                    },
                    error: function () {
                        Swal.close();
                        document.body.style.overflow = 'auto';
                        $('#btncreateorder').prop('disabled', false).html('<i class="fas fa-save"></i>&nbsp;Create Purchase Order');

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
        var jsonObj = [];
        $("#edittableorder tbody tr").each(function () {
            var item = {};
            $(this).find('td').each(function (col_idx) {
                item["col_" + (col_idx + 1)] = $(this).text();
            });
            jsonObj.push(item);
        });

        if (jsonObj.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "No Data",
                text: "Please add items before updating an order.",
            });
            return;
        }

        $('#editbtncreateorder').prop('disabled', true).html(
            '<i class="fas fa-circle-notch fa-spin mr-2"></i> Update Order'
        );

        var orderData = {
            tableData: jsonObj,
            orderdate: $('#editorderdate').val(),
            grosstotal: $('#edithidegrosstotalorder').val(),
            remark: $('#editremark').val(),
            supplier: $('#editsupplier').val(),
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
                $('#porderEditmodal').modal('hide');

                var obj = JSON.parse(result);

                if (obj.status == 1) {
                    actionreload(obj.action);
                } else {
                    $('#editbtncreateorder').prop('disabled', false).html('<i class="fas fa-save"></i>&nbsp;Update Purchase Order');
                    action(obj.action);
                }
            },
            error: function () {
                $('#editbtncreateorder').prop('disabled', false).html('<i class="fas fa-save"></i>&nbsp;Update Purchase Order');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Something went wrong. Please try again later.'
                });
            }
        });
    });

    $('#porderrequest').change(function () {
        var porderID = $(this).val();

        $.ajax({
            type: "POST",
            data: { recordID: porderID },
            url: 'Purchaseorder/Getporderreqdetails',
            success: function (response) {
                var result = JSON.parse(response);
                $('#requestitem').empty();

                if (result.length > 0) {
                    $.each(result, function (index, item) {
                        var listItem = '<li class="list-group-item bg-warning-soft">';

                        listItem += '<strong>' + esc(item.requestname) + '</strong> - ';
                        listItem += item.qty + ' ' + esc(item.measure_type);

                        if (item.comment && item.comment !== "") {
                            listItem += ' <em>(' + esc(item.comment) + ')</em>';
                        }

                        // Last two GRN price/date/qty rows for this product
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
                    });
                }
            },
        });
    });

    $('#product').change(function () {
    	var productID = $(this).val();
    	var supplier = $('#supplier').val();
    	if (!productID) { return; }

    	$.ajax({
    		type: "POST",
    		url: 'Purchaseorder/Getproductprice',
    		data: {
    			recordID: productID,
    			supplier: supplier
    		},
    		success: function (result) {
    			var obj = JSON.parse(result);
    			$('#unitprice').val(obj.unitprice);
    			$('#uom').val(obj.unit);
    		}
    	});
    });

    $('#editproduct').change(function () {
        if (suppressEditProductChange) {
            return;
        }
        var productID = $(this).val();
        var supplier = $('#editsupplier').val();
        if (!productID) { return; }

        $.ajax({
            type: "POST",
            url: 'Purchaseorder/Getproductprice',
            data: {
                recordID: productID,
                supplier: supplier
            },
            success: function (result) {
                var obj = JSON.parse(result);
                $('#editunitprice').val(obj.unitprice || 0);
                $('#edituom').val(obj.unit);
            }
        });
    });

});

function esc(s) {
    return $('<div>').text(s == null ? '' : s).html();
}

// Sum the hidden total cells and refresh the grand total display
function recalcTotal(cellSelector, displaySelector, hiddenSelector, styled) {
    var sum = 0;
    $(cellSelector).each(function () {
        sum += parseFloat($(this).text()) || 0;
    });
    var showsum = addCommas(parseFloat(sum).toFixed(2));
    if (styled) {
        $(displaySelector).html('<strong style="background-color: yellow;">Final Price</strong> &nbsp;&nbsp;<strong>Rs. ' + showsum + '</strong>');
    } else {
        $(displaySelector).html('Rs. ' + showsum);
    }
    $(hiddenSelector).val(sum);
}

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

function action(data) {
    var obj = JSON.parse(data);
    $.notify({
        icon: obj.icon,
        title: obj.title,
        message: obj.message,
        url: obj.url,
        target: obj.target
    }, {
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
        showConfirmButton: false,
        backdrop: `rgba(255, 255, 255, 0.5)`,
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
                    Swal.close();
                    document.body.style.overflow = 'auto';
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
        showConfirmButton: false,
        backdrop: `rgba(255, 255, 255, 0.5)`,
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
                    Swal.close();
                    document.body.style.overflow = 'auto';
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
</script>

<?php include "include/footer.php"; ?>