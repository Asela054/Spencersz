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
                            <span>Good Receive Note</span>
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
                                    data-target="#staticBackdrop" onclick="getVat();"
                                    <?php if($addcheck==0){echo 'disabled';} ?>><i class="fas fa-plus mr-2"></i>Create
                                    Good Receive Note</button>
                                <hr>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="scrollbar pb-3" id="style-2">
                                    <table class="table table-bordered table-striped table-sm nowrap" id="dataTable">
                                        <thead>
                                            <tr>
                                                <th>GRN No</th>
                                                <th>GRN Date</th>
                                                <th>GRN Type</th>
                                                <th>Batch No</th>
                                                <th>Supplier</th>
                                                <th>Total</th>
                                                <th>Porder No</th>
                                                <th>Approved Status</th>
                                                <th>Approved By</th>
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
                <h5 class="modal-title" id="staticBackdropLabel">Create Good Receive Note</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-4 col-xl-4">
                        <form id="createorderform" autocomplete="off">
                            <div class="form-row mb-1">
                                <div class="col-6">
                                    <label class="small font-weight-bold text-dark">Order Date*</label>
                                    <input type="date" class="form-control form-control-sm" placeholder=""
                                        name="grndate" id="grndate" onchange="getVat();"
                                        value="<?php echo date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-6">
                                    <label class="small font-weight-bold text-dark">Purchase Order*</label>
                                    <select class="form-control form-control-sm selecter2 px-0" name="porder" id="porder" required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row mb-1">
                            <div class="col-6">
                                    <label class="small font-weight-bold text-dark">Supplier*</label>
                                    <select class="form-control form-control-sm selecter2 px-0" name="supplier"
                                        id="supplier" required readonly>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="small font-weight-bold text-dark">GRN Type*</label>
                                    <select class="form-control form-control-sm" name="grntype" id="grntype" required>
                                        <option value="">Select</option>
                                        <?php foreach($ordertypelist->result() as $rowordertypelist){ ?>
                                        <option value="<?php echo $rowordertypelist->idtbl_material_group ?>">
                                            <?php echo $rowordertypelist->group ?></option>
                                        <?php } ?>
                                    </select>
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

                            <div class="form-group mb-1 d-none">
                                <label class="small font-weight-bold text-dark">Location*</label>
                                <select class="form-control form-control-sm" name="location" id="location" required>
                                    <option value="">Select</option>
                                    <?php foreach($locationlist->result() as $rowlocationlist){ ?>
                                    <option value="<?php echo $rowlocationlist->idtbl_location ?>" selected>
                                        <?php echo $rowlocationlist->location ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group mb-1">
                                <label class="small font-weight-bold text-dark">Warehouse*</label>
                                <select class="form-control form-control-sm" name="warehouse" id="warehouse" required>
                                    <option value="">Select</option>
                                    <?php foreach($warehouselist->result() as $rowwarehouselist){ ?>
                                    <option value="<?php echo $rowwarehouselist->idtbl_warehouse ?>">
                                        <?php echo $rowwarehouselist->wh_name ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group mb-1">
                                <label class="small font-weight-bold text-dark">Material*</label>
                                <select class="form-control form-control-sm selecter2 px-0" name="product"
                                    id="product" required>
                                    <option value="">Select</option>
                                </select>
                            </div>

                            <div class="form-group mb-1" id="servicematerialDiv" style="display: none;">
                                <label class="small font-weight-bold text-dark">Service Material</label>
                                <select class="form-control form-control-sm" name="servicematerial" id="servicematerial">
                                    <option value="">Select</option>
                                </select>
                            </div>

                            <div class="form-group mb-1">
                                <label class="small font-weight-bold text-dark">UOM*</label>
                                <select class="form-control form-control-sm" style="pointer-events: none;"
                                    name="uom" id="uom">
                                    <option value="">Select</option>
                                    <?php foreach($measurelist->result() as $rowmeasurelist){ ?>
                                    <option value="<?php echo $rowmeasurelist->idtbl_mesurements ?>">
                                        <?php echo $rowmeasurelist->measure_type ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-row mb-1">
                                <div class="col">
                                    <label class="small font-weight-bold text-dark" hidden>MF Date*</label>
                                    <input type="date" id="mfdate" name="mfdate" class="form-control form-control-sm"
                                        value="<?php echo date('Y-m-d') ?>" required hidden>
                                </div>
                            </div>

                            <div class="form-row mb-1">
                                <div class="col">
                                    <label class="small font-weight-bold text-dark">Qty*</label>
                                    <label class="small font-weight-bold text-danger" id="qtylabel"></label>
                                    <input type="text" id="newqty" name="newqty" class="form-control form-control-sm"
                                        required >
                                </div>
                                <div class="col">
            						<label class="small font-weight-bold text-dark">Pieces (Sheets)</label>
            						<input type="text" id="piecesper_qty" name="piecesper_qty"
            							class="form-control form-control-sm" value="0" readonly>
            					</div>
                            </div>

                            <div class="form-row mb-1">
                                <div class="col">
                                    <label class="small font-weight-bold text-dark">Unit Price</label>
                                    <input type="text" id="unitprice" name="unitprice"
                                        class="form-control form-control-sm"
                                        value="0">
                                </div>
                                <div class="col">
                                    <label class="small font-weight-bold text-dark">Discount</label>
                                    <input type="text" id="unitdiscount" name="unitdiscount"
                                        class="form-control form-control-sm"
                                        value="0">
                                </div>
                            </div>

                            <input type="hidden" id="porderdetailsid" name="porderdetailsid"
                            class="form-control form-control-sm" />
                            
                            <div class="form-group mb-1">
                                <label class="small font-weight-bold text-dark">Comment</label>
                                <textarea name="comment" id="comment" class="form-control form-control-sm"
                                   ></textarea>
                            </div>
                            <div class="form-group mb-1">
                                <label class="small font-weight-bold text-dark">Batch No</label>
                                <input type="text" id="batchno" name="batchno" class="form-control form-control-sm"
                                    required readonly>
                            </div>
                            <div class="form-row mb-1">
                                <div class="col">
                                <label class="small font-weight-bold text-dark">Vat Type*</label>
                                <select class="form-control form-control-sm" name="vat_type" id="vat_type" required>
                                    <option value="">Select Vat Type</option>
                                    <option value="1">VAT Seperated</option>
                                    <option value="2" selected>Non VAT</option>
                                </select>
                                </div>
                                <div class="col">
                                    <label class="small font-weight-bold text-dark">Invoice No*</label>
                                    <input type="text" id="invoice" name="invoice" class="form-control form-control-sm"
                                        required>
                                </div>
                            </div>
                            <div class="form-group mb-1">

                            </div>
                            <div class="form-group mt-3 text-right">
                                <button type="button" id="formsubmit" class="btn btn-warning btn-sm font-weight-bold px-4"
                                    <?php if($addcheck==0){echo 'disabled';} ?>><i class="fas fa-plus"></i>&nbsp;Add to
                                    list</button>
                                <input name="submitBtn" type="submit" value="Save" id="submitBtn" class="d-none">
                            </div>
                            <input type="hidden" name="refillprice" id="refillprice" value="">
                        </form>
                    </div>
                    <div class="col-sm-12 col-md-12 col-lg-8 col-xl-8">
                        <div class="scrollbar pb-3" id="style-3">
                            <table class="table table-striped table-bordered table-sm small" id="tableorder">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Comment</th>
                                        <th class="d-none">ProductID</th>
                                        <th>Unitprice</th>
                                        <th class="d-none">Saleprice</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-center">Uom</th>
                                        <th>Price</th>
                                        <th class="d-none">HideTotal</th>
                                        <th class="text-right">Discount</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <div class="row">
                            <div class="col text-right">
                                <h4 class="font-weight-600" id="divtotal">Rs. 0.00</h4>
                            </div>
                            <input type="hidden" id="hidetotalorder" value="0">
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <label class="small font-weight-bold text-dark">Discount*</label>
                                <input type="text" class="form-control form-control-sm" id="discount" value="0"
                                    onkeyup="finaltotalcalculate();" required>
                            </div>
                            <div class="col-6">
                                <label class="small font-weight-bold text-dark">Sub Total </label>
                                <input type="number" step="any" name="hiddenfulltotal"
                                    class="form-control form-control-sm" id="hiddenfulltotal" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-3">
                                <label class="small font-weight-bold text-dark">Vat (%)*</label>
                                <input type="number" id="vat" name="vat" class="form-control form-control-sm" value="0"
                                    onkeyup="finaltotalcalculate();" required>
                            </div>
                            <div class="col-3">
                                                <label class="small font-weight-bold text-dark">Vat Amount*</label>
                                                <input type="number" id="vatamount" name="vatamount"
                                                    class="form-control form-control-sm" value="0" required readonly>
                                            </div>

                            <div class="col-6">
                                <label class="small font-weight-bold text-dark"><b>Total Payment</b></label>
                                <input type="number" step="any" name="modeltotalpayment"
                                    class="form-control form-control-sm small font-weight-bold text-dark"
                                    id="modeltotalpayment" readonly>
                            </div>
                        </div>

                        <hr>
                        <div class="form-group">
                            <label class="small font-weight-bold text-dark">Remark</label>
                            <textarea name="remark" id="remark" class="form-control form-control-sm"></textarea>
                        </div>
                        <div class="form-group mt-2">
                            <button type="button" id="btncreateorder"
                                class="btn btn-primary btn-sm fa-pull-right"><i
                                    class="fas fa-save"></i>&nbsp;Create
                                Good Receive Note</button>
                        </div>
                        <div class="row mt-5 col-12">
                        	<div class="form-row mb-1">
                        		<div class="col-12">
                        			<div class="form-group mb-1">
                        				<label class="small font-weight-bold text-dark">PO Details</label>
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

<div class="modal fade" id="viewmodal" data-backdrop="static" data-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">View Good Recieve Note</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="GRNView">

                <div id="viewhtml"></div>

            </div>
            <div class="modal-footer">
                <div class="col-12 text-right">
                    <hr>
                <?php if($approvecheck==1){ ?>
                <button id="btnapprovereject" class="btn btn-primary btn-sm px-3 mb-2"><i class="fas fa-check mr-2"></i>Approve or Reject</button>
                <?php } ?>
                <?php if($checkstatus==1){ ?>
                <button id="btncheck" class="btn btn-success btn-sm px-3 mb-2"><i class="fas fa-user-check mr-2"></i>Check By</button>
                <?php } ?>
                    <input type="hidden" name="grnid" id="grnid">
                </div>
                <div class="col-12 text-center">
                    <div id="alertdiv"></div>
                </div>
                <div class="col-12 text-center">
                    <div id="checkalertdiv"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal Add Remarks -->
<div class="modal fade" id="updatevattypemodal" data-backdrop="static" data-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="staticBackdropLabel"><i class="fas fa-marker"></i> Change VAT Type</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <form id="addvattypeform" autocomplete="off">
                            <div class="form-group mb-1">
                                <input type="hidden" class="form-control form-control-sm" id="hiddengrnid" name="hiddengrnid">
                            </div>
                            <div class="form-group mb-1">
                                <label class="small font-weight-bold text-dark">Vat Type*</label>
                                <select class="form-control form-control-sm" name="vattype" id="vattype" required>
                                    <option value="">Select Vat Type</option>
                                    <option value="1">VAT</option>
                                    <option value="2">Non VAT</option>
                                </select>
                            </div>
                            <div class="form-group mt-2 text-right">
                                <button type="button" id="submitBtnVatType" class="btn btn-primary btn-sm px-4"><i class="far fa-save"></i>&nbsp;Update</button>
                                <input type="submit" class="d-none" id="hidesubmitvattype" value="">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editgrnmodal" data-backdrop="static" data-keyboard="false" tabindex="-1"
    aria-labelledby="editgrnmodalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit GRN Prices</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col-3">
                        <label class="small font-weight-bold text-dark">GRN No</label>
                        <input type="text" class="form-control form-control-sm" id="editgrnno" readonly>
                    </div>
                    <div class="col-3">
                        <label class="small font-weight-bold text-dark">GRN Date</label>
                        <input type="text" class="form-control form-control-sm" id="editgrndate" readonly>
                    </div>
                    <div class="col-3">
                        <label class="small font-weight-bold text-dark">Supplier</label>
                        <input type="text" class="form-control form-control-sm" id="editsupplier" readonly>
                    </div>
                    <div class="col-3">
                        <label class="small font-weight-bold text-dark">Invoice No</label>
                        <input type="text" class="form-control form-control-sm" id="editinvoice" readonly>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm" id="tableeditgrn">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Uom</th>
                                <th class="text-right" width="150">Unit Price</th>
                                <th class="text-right">Discount</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="row mt-3">
                    <div class="col-5 offset-7">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-right font-weight-bold">Discount</td>
                                <td class="text-right" id="editdiscountdisplay">0.00</td>
                            </tr>
                            <tr>
                                <td class="text-right font-weight-bold">Sub Total</td>
                                <td class="text-right" id="editsubtotaldisplay">0.00</td>
                            </tr>
                            <tr id="editvatrow">
                                <td class="text-right font-weight-bold">Vat (<span id="editvatpercent">0</span>%)</td>
                                <td class="text-right" id="editvatamountdisplay">0.00</td>
                            </tr>
                            <tr>
                                <td class="text-right font-weight-bold"><strong>Total Payment</strong></td>
                                <td class="text-right"><strong id="edittotalpaymentdisplay">0.00</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <input type="hidden" id="editgrnid">
                <input type="hidden" id="editheaderdiscount">
                <input type="hidden" id="editheadervat">
                <input type="hidden" id="editheadervattype">
            </div>
            <div class="modal-footer">
                <button type="button" id="btnsaveeditgrn" class="btn btn-primary btn-sm"><i
                        class="fas fa-save"></i>&nbsp;Update Prices</button>
            </div>
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
    $('#printgrn').click(function() {
        printJS({
            printable: 'GRNView',
            type: 'html',
            css: 'assets/css/styles.css'
        });
    });
});

$(document).ready(function() {

    $('#supplier').select2({
        dropdownParent: $('#staticBackdrop'),
        width: '100%',
    });
    $('#porder').select2({
        dropdownParent: $('#staticBackdrop'),
        width: '100%',
    });
    $('#grntype').change(function() {
        toggleServiceMaterial();
    });
    

    $('#porder').select2({
        dropdownParent: $('#staticBackdrop'),
        placeholder: "Select Purchase Order",
        width: '100%',
        ajax: {
            url: '<?= base_url("Goodreceive/getPorderList") ?>',
            type: 'post',
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

    function toggleServiceMaterial() {
        if ($('#grntype').val() == '4') {
            $('#servicematerialDiv').show();
        } else {
            $('#servicematerialDiv').hide();
            $('#servicematerial').val('').trigger('change');
        }
    }

    var addcheck = '<?php echo $addcheck; ?>';
    var editcheck = '<?php echo $editcheck; ?>';
    var statuscheck = '<?php echo $statuscheck; ?>';
    var deletecheck = '<?php echo $deletecheck; ?>';

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
                title: 'Good Receive Note Information',
                text: '<i class="fas fa-file-csv mr-2"></i> CSV',
            },
            {
                extend: 'pdf',
                className: 'btn btn-danger btn-sm',
                title: 'Good Receive Note Information',
                text: '<i class="fas fa-file-pdf mr-2"></i> PDF',
            },
            {
                extend: 'print',
                title: 'Good Receive Note Information',
                className: 'btn btn-primary btn-sm',
                text: '<i class="fas fa-print mr-2"></i> Print',
                customize: function(win) {
                    $(win.document.body).find('table')
                        .addClass('compact')
                        .css('font-size', 'inherit');
                },
            },
            // 'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        ajax: {
            url: "<?php echo base_url() ?>scripts/goodreceivelist.php",
            type: "POST", // you can use GET
            "data": function(d) {
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
            	"data": "grn_no"
            },
            {
                "data": "grndate"
            },
            {
                "data": "group"
            },
            {
                "data": "batchno"
            },
            {
                "data": "suppliername"
            },
            {
                "targets": -1,
                "className": 'text-right',
                "data": null,
                "render": function(data, type, full) {
                    return addCommas(parseFloat(full['totalcost']).toFixed(2));
                }
            },
            {
                "data": "porder_no"
            },
                        {
                "targets": -1,
                "className": '',
                "data": "approvestatus_display",
                "render": function(data, type, row) {
                    return data;
                }
            }, 
                        {
                "data": "name"
            },
            {
                "targets": -1,
                "className": 'text-right',
                "data": null,
                "render": function(data, type, full) {

                    var button = '';

                    if (editcheck == 1) {

                        button += '<div class="btn-group" role="group">';
                        button += '<button class="btn btn-yellow btn-sm btnUpdatevattype mr-1" ' +
                            'id="' + full['idtbl_print_grn'] + '" ' +
                            'data-toggle="tooltip" data-placement="bottom" ' +
                            'title="Update VAT Type">' +
                            '<i class="fas fa-marker"></i>' +
                            '</button>';
                        button += '<button data-toggle="tooltip" data-placement="bottom" ' +
                            'title="Edit Prices" ' +
                            'class="btn btn-primary btn-sm btnEditGRN mr-1" ' +
                            'id="' + full['idtbl_print_grn'] + '">' +
                            '<i class="fas fa-edit"></i>' +
                            '</button>';

                        button += '</div>';
                    }

                            button += '<a href="<?php echo base_url() ?>Goodreceive/pdfgrnget/' +
                            full['idtbl_print_grn'] +
                            '" target="_blank" ' +
                            'data-toggle="tooltip" data-placement="bottom" ' +
                            'title="Print GRN" ' +
                            'class="btn btn-secondary btn-sm mr-1">' +
                            '<i class="fas fa-file-pdf mr-2"></i>' +
                            '</a>';

                    button += '<button data-toggle="tooltip" data-placement="bottom" ' +
                        'title="View GRN" ' +
                        'class="btn btn-dark btn-sm btnview mr-1" ' +
                        'id="' + full['idtbl_print_grn'] + '" ' +
                        'aproval_id="' + full['approvestatus'] + '" ' +
                        'check_status="' + full['check_by'] + '" ' +
                        'grn_no="' + full['grn_no'] + '">' +
                        '<i class="fas fa-eye"></i>' +
                        '</button>';

                    return button;
                }
            }
        ],
        drawCallback: function(settings) {
            $('[data-toggle="tooltip"]').tooltip();
        }
    });
    $('#dataTable tbody').on('click', '.btnUpdatevattype', function () {
        var id = $(this).attr('id');
        $("#hiddengrnid").val(id);
        $.ajax({
            type: "POST",
            data: {
                recordID: id
            },
            url: 'Goodreceive/Getvattype',
            success: function (result) {
                $('#updatevattypemodal').modal('show');

                if (result) {
                    var data = JSON.parse(result);
                    $('#vattype').val(data.vat_type);
                }
            }
        });
    });
    $('#submitBtnVatType').click(function () {
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

                if (!$("#addvattypeform")[0].checkValidity()) {
                    $("#hidesubmitvattype").click();
                } else {
                    var vattype = $('#vattype').val();
                    var hiddenID = $('#hiddengrnid').val();

                    $.ajax({
                        type: "POST",
                        data: {
                            vattype: vattype,
                            hiddenID: hiddenID

                        },
                        url: '<?php echo base_url() ?>Goodreceive/Goodreceivevattype',
                        success: function (result) {
                            Swal.close();
                            document.body.style.overflow = 'auto';

                            var obj = JSON.parse(result);
                            var action = JSON.parse(obj.action);

                            if (obj.status == 1) {

                                Swal.fire({
                                    icon: action.type,
                                    title: action.message,
                                    showConfirmButton: false,
                                    timer: 3000
                                });

                                setTimeout(function () {
                                    location.reload();
                                }, 3000);

                            } else {

                                Swal.fire({
                                    icon: action.type,
                                    title: action.message,
                                    showConfirmButton: false,
                                    timer: 3000
                                });
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
            }
        });
    });
    $('#porder').change(function () {
        var porderID = $(this).val();

        $.ajax({
            type: "POST",
            data: {
                recordID: porderID
            },
            url: 'Goodreceive/Getporderdetails',

            success: function (response) {
                var result = JSON.parse(response);
                $('#requestitem').empty();

                if (result.length > 0) {

                    $.each(result, function (index, item) {

                        var listItem = '<li class="list-group-item bg-warning-soft">';
                        listItem += '<strong>' + item.materialname + '</strong><br>';
                        listItem += 'Qty: ' + item.qty + ' ' + item.measure_type;
                        if (item.pieces) {
                            listItem += ' | Pieces: ' + item.pieces;
                        }
                        listItem += '</li>';

                        $('#requestitem').append(listItem);
                        $('#remark').val(item.remark);
                    });

                } else {
                    $('#requestitem').append('<li class="list-group-item">No items found</li>');
                }
            }
        });
    });
    $('#dataTable tbody').on('click', '.btnview', function () {
    	var id = $(this).attr('id');
    	var grnno = $(this).attr('grn_no');
    	$('#grncode').html(grnno);
    	$('#grnid').val(id);

    	var approvestatus = $(this).attr('aproval_id');
    	var checkstatus = $(this).attr('check_status');

    	$.ajax({
    		type: "POST",
    		data: {
    			recordID: id
    		},
    		url: '<?php echo base_url() ?>Goodreceive/Goodreceiveview',
    		success: function (result) { //alert(result);
    			$('#viewmodal').modal('show');
    			$('#viewhtml').html(result.html);
    			$('#viewcompanyname').text(result.details.companyname);
    			$('#viewbranchname').text(result.details.branchname);
    			if (approvestatus > 0) {
    				$('#btnapprovereject').addClass('d-none').prop('disabled', true);
    				if (approvestatus == 1) {
    					$('#alertdiv').html('<div class="alert alert-success" role="alert"><i class="fas fa-check-circle mr-2"></i> GRN approved</div>');
    				} else if (approvestatus == 2) {
    					$('#alertdiv').html('<div class="alert alert-danger" role="alert"><i class="fas fa-times-circle mr-2"></i> GRN rejected</div>');
    				}
    			} else {
    				if (checkstatus == 0) {
    					$('#btnapprovereject').addClass('d-none').prop('disabled', true);
    				} else {
    					$('#btnapprovereject').removeClass('d-none').prop('disabled', false);
    					$('#btncheck').addClass('d-none').prop('disabled', true);
    				}
    			}

    			if (checkstatus > 0) {
    				$('#btncheck').addClass('d-none').prop('disabled', true);
    				if (checkstatus == 1) {
    					$('#checkalertdiv').html('<div class="alert alert-secondary" role="alert"><i class="fas fa-check-circle mr-2"></i> GRN checked</div>');
    				}
    			}
    		}
    	});

    	$('#viewmodal').on('hidden.bs.modal', function (event) {
    		$('#alertdiv').html('');
    		$('#checkalertdiv').html('');
    		$('#btnapprovereject').removeClass('d-none').prop('disabled', false);
    		$('#btncheck').removeClass('d-none').prop('disabled', false);
    	});
    });

    $('#btnapprovereject').click(function(){
        Swal.fire({
            title: "Do you want to approve this Good Receive Note?",
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

    $('#btncheck').click(function () {
        Swal.fire({
            title: "Do you want to check this GRN?",
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

        $('#dataTable tbody').on('click', '.btnEditGRN', function () {
            var id = $(this).attr('id');

            $.ajax({
                type: "POST",
                data: { recordID: id },
                url: '<?php echo base_url() ?>Goodreceive/Geteditgrn',
                success: function (result) {
                    var obj = result;        
                    var header = obj.header;
                    var details = obj.details;

                    $('#editgrnid').val(header.idtbl_print_grn);
                    $('#editgrnno').val(header.grn_no);
                    $('#editgrndate').val(header.grndate);
                    $('#editsupplier').val(obj.suppliername);
                    $('#editinvoice').val(header.invoicenum);
                    $('#editheaderdiscount').val(header.discount);
                    $('#editheadervat').val(header.vat);
                    $('#editheadervattype').val(header.vat_type);
                    $('#editvatpercent').text(header.vat);

                    var tbody = $('#tableeditgrn tbody');
                    tbody.empty();

                    $.each(details, function (i, item) {
                        var productName;
                        if (header.tbl_material_group_idtbl_material_group == 4) {
                            productName = item.comment;
                        } else {
                            productName = item.materialname
                                ? (item.materialname + (item.materialinfocode ? ' / ' + item.materialinfocode : ''))
                                : item.comment;
                        }

                        var hasPieces = (item.pieces !== null && item.pieces !== '' && parseFloat(item.pieces) > 0);
                        var qtyDisplay = hasPieces ? item.pieces : item.qty;

                        var uomDisplay = hasPieces
                            ? (item.convert_uom_name ? item.convert_uom_name : (item.main_uom_name ? item.main_uom_name : ''))
                            : (item.main_uom_name ? item.main_uom_name : '');

                        var discount = parseFloat(item.unit_discount) || 0;
                        var rowTotal = (parseFloat(item.unitprice) * parseFloat(qtyDisplay)) - discount;

                        var row = '<tr>';
                        row += '<td>' + productName + '</td>';
                        row += '<td class="text-center">' + qtyDisplay + '</td>';
                        row += '<td class="text-center">' + uomDisplay + '</td>';
                        row += '<td class="text-right"><input type="text" class="form-control form-control-sm text-right edit-unitprice" ' +
                            'data-detailid="' + item.idtbl_print_grndetail + '" data-qty="' + item.qty + '" data-pieces="' + item.pieces +
                            '" data-discount="' + discount + '" value="' + parseFloat(item.unitprice) + '"></td>';
                        row += '<td class="text-right">' + discount.toFixed(2) + '</td>';
                        row += '<td class="text-right row-total">' + rowTotal.toFixed(2) + '</td>';
                        row += '</tr>';

                        tbody.append(row);
                    });

                    calculateEditGRNTotals();
                    $('#editgrnmodal').modal('show');
                }
            });
        });

        $(document).on('input', '.edit-unitprice', function () {
            var row = $(this).closest('tr');
            var unitprice = parseFloat($(this).val()) || 0;
            var qty = parseFloat($(this).data('qty')) || 0;
            var pieces = parseFloat($(this).data('pieces')) || 0;
            var discount = parseFloat($(this).data('discount')) || 0;

            var finalQty = pieces > 0 ? pieces : qty;
            var total = (unitprice * finalQty) - discount;

            row.find('.row-total').text(total.toFixed(2));
            calculateEditGRNTotals();
        });

        function calculateEditGRNTotals() {
            var sum = 0;

            $('#tableeditgrn tbody .row-total').each(function () {
                sum += parseFloat($(this).text()) || 0;
            });

            var headerDiscount = parseFloat($('#editheaderdiscount').val()) || 0;
            var vat = parseFloat($('#editheadervat').val()) || 0;
            var vatType = $('#editheadervattype').val();

            var subTotal = sum - headerDiscount;
            var vatAmount = 0;
            var finalTotal = subTotal;

            var companyId = <?php echo (int)$_SESSION['company_id']; ?>;

            if (companyId != 3 && vatType == 1) {
                vatAmount = (subTotal * vat) / 100;
                finalTotal = subTotal + vatAmount;
                $('#editvatrow').show();
            } else {
                vatAmount = 0;
                finalTotal = subTotal;
                $('#editvatrow').hide();
            }

            $('#editdiscountdisplay').text(addCommas(headerDiscount.toFixed(2)));
            $('#editsubtotaldisplay').text(addCommas(subTotal.toFixed(2)));
            $('#editvatamountdisplay').text(addCommas(vatAmount.toFixed(2)));
            $('#edittotalpaymentdisplay').text(addCommas(finalTotal.toFixed(2)));
        }

        $('#btnsaveeditgrn').click(function () {
            var jsonObj = [];
            var valid = true;

            $('#tableeditgrn tbody tr').each(function () {
                var input = $(this).find('.edit-unitprice');
                var unitprice = input.val();

                if (unitprice === '' || isNaN(unitprice)) {
                    valid = false;
                }

                jsonObj.push({
                    detailid: input.data('detailid'),
                    unitprice: unitprice,
                    qty: input.data('qty'),
                    pieces: input.data('pieces'),
                    discount: input.data('discount')
                });
            });

            if (!valid) {
                Swal.fire({ icon: 'warning', title: 'Invalid Input', text: 'Please enter valid unit prices for all items.' });
                return;
            }

            Swal.fire({
                title: '',
                html: '<div class="div-spinner"><div class="custom-loader"></div></div>',
                allowOutsideClick: false,
                showConfirmButton: false,
                backdrop: "rgba(255, 255, 255, 0.5)",
                customClass: { popup: "fullscreen-swal" },
                didOpen: () => {
                    document.body.style.overflow = "hidden";

                    $.ajax({
                        type: "POST",
                        data: {
                            grnID: $('#editgrnid').val(),
                            tableData: jsonObj,
                            discount: $('#editheaderdiscount').val(),
                            vat: $('#editheadervat').val(),
                            vat_type: $('#editheadervattype').val()
                        },
                        url: '<?php echo base_url() ?>Goodreceive/Goodreceiveeditupdate',
                        success: function (result) {
                            Swal.close();
                            document.body.style.overflow = 'auto';
                            var obj = JSON.parse(result);

                            if (obj.status == 1) {
                                $('#editgrnmodal').modal('hide');
                                actionreload(obj.action);
                            } else {
                                action(obj.action);
                            }
                        },
                        error: function () {
                            Swal.close();
                            document.body.style.overflow = 'auto';
                            Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong. Please try again later.' });
                        }
                    });
                }
            });
        });

    $("#formsubmit").click(function() {
        if (!$("#createorderform")[0].checkValidity()) {
            // If the form is invalid, submit it. The form won't actually submit;
            // this will just cause the browser to display the native HTML5 error messages.
            $("#submitBtn").click();
        } else {
            var productID = $('#product').val();
            var comment = $('#comment').val();
            var product = $("#product option:selected").text();
            var unitprice = parseFloat($('#unitprice').val());
            var newqty = parseFloat($('#newqty').val());
            var pieces = parseFloat($('#piecesper_qty').val());
            var discount = $('#unitdiscount').val();
            var uomID = $('#uom').val();
            var uom = $("#uom option:selected").text();
            var expdate = $('#expdate').val();
            var porderdetailsid = parseFloat($('#porderdetailsid').val());

            var newtotal;
            var newprice;
            if (pieces !== 0) {
                newtotal = (unitprice * pieces) - discount;
                newprice = (unitprice * pieces / newqty)  - discount;
            } else {
                newtotal = (unitprice * newqty) - discount;
                newprice = 0;
            }

            var total = parseFloat(newtotal);
            var showtotal = addCommas(parseFloat(total).toFixed(2));

            $('#tableorder > tbody:last').append('<tr class="pointer"><td>' + product + '</td><td>' +
                comment + '</td><td class="d-none">' + productID +
                '</td><td class="text-center">' + unitprice + '</td><td class="text-center">' +
                newqty +
                '</td><td class="text-center">' + uom +
                '</td><td class="text-center">' + parseFloat(newprice).toFixed(2) +
                '</td><td class="text-center">' + discount + '</td><td class="d-none">' + uomID +
                '</td><td class="total d-none">' + total + '</td><td class="text-right">' +
                showtotal +
                '</td><td name="inquerydetailsid" class="d-none">' + porderdetailsid +
                    '</td><td name="inquerydetailsid" class="d-none">' + pieces +
                    '</td><td><button type="button" onclick= "productDelete(this);" id="btnDeleterow" class=" btn btn-danger btn-sm float-right"><i class="fas fa-trash-alt"></i></button></td> </tr>'
            );

            $('#product').val('').trigger('change');
            $('#unitprice').val('0');
            $('#uom').val('');
            $('#comment').val('');
            $('#unitdiscount').val('0');
            $('#newqty').val('');
            $('#piecesper_qty').val('');
            $('#qtylabel').text('0');
            $('#porder').prop('readonly', true).css('pointer-events', 'none');


            var sum = 0;
            $(".total").each(function() {
                sum += parseFloat($(this).text());
            });

            var showsum = addCommas(parseFloat(sum).toFixed(2));

            $('#divtotal').html('<strong style="background-color: yellow;"> Rs. <strong>' + showsum);

            $('#hidetotalorder').val(sum);
            $('#product').focus();
        }

        finaltotalcalculate();

    });

    $(document).on("keyup", "#discount", function(event) {
        var checkdiscount = parseFloat($("#discount").val());
        if (!checkdiscount == "") {
            finaltotalcalculate();
        } else {

        }

    });

    $(document).on("keyup", "#vat", function(event) {
        var checkvat = parseFloat($("#vat").val());
        if (!checkvat == "") {
            finaltotalcalculate();
        } else {

        }

    });


    $('#tableorder').on('click', 'tr', function () {
    	var r = confirm("Are you sure you want to remove this product?");
    	if (r == true) {
    		$(this).closest('tr').remove();

    		var sum = 0;
    		$(".total").each(function () {
    			sum += parseFloat($(this).text());
    		});

    		var showsum = addCommas(parseFloat(sum).toFixed(2));

    		$('#divtotal').html('Rs. ' + showsum);
    		$('#hidetotalorder').val(sum);

    		finaltotalcalculate();
    		$('#product').focus();
    	}
    });


    $('#tblcost').on('click', 'tr', function() {
        var r = confirm("Are you sure, You want to remove this cost? ");
        if (r == true) {
            $(this).closest('tr').remove();

            var sum = 0;
            $(".totalamount").each(function() {
                sum += parseFloat($(this).text());
            });

            var showsum = addCommas(parseFloat(sum).toFixed(2));

            $('#labelcosttotal').html('Rs. ' + showsum);
            $('#totalcost').val(sum);
        }
    });

    $('#btncreateorder').click(function() { //alert('IN');
        $('#btncreateorder').prop('disabled', true).html(
            '<i class="fas fa-circle-notch fa-spin mr-2"></i> Create Good Receive Note')
        var tbody = $("#tableorder tbody");

        if (tbody.children().length > 0) {
            jsonObj = [];
            $("#tableorder tbody tr").each(function() {
                item = {}
                $(this).find('td').each(function(col_idx) {
                    item["col_" + (col_idx + 1)] = $(this).text();
                });
                jsonObj.push(item);
            });
            // console.log(jsonObj);

            var grndate = $('#grndate').val();
            var remark = $('#remark').val();
            var total = $('#modeltotalpayment').val();
            var vatamount = $('#vatamount').val();
            var location = $('#location').val();
            var warehouse = $('#warehouse').val();
            var porder = $('#porder').val();
            var batchno = $('#batchno').val();
            var supplier = $('#supplier').val();
            var invoice = $('#invoice').val();
            var vat_type = $('#vat_type').val();
            var grntype = $('#grntype').val();
            var discount = $('#discount').val();
            var vat = $('#vat').val();
            var subtotal = $('#hiddenfulltotal').val();
            var branch_id = $('#f_branch_id').val();
        	var company_id = $('#f_company_id').val();

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
                    data: {
                        tableData: jsonObj,
                        grndate: grndate,
                        total: total,
                        remark: remark,
                        vatamount: vatamount,
                        location: location,
                        warehouse: warehouse,
                        porder: porder,
                        invoice: invoice,
                        subtotal: subtotal,
                        batchno: batchno,
                        supplier: supplier,
                        grntype: grntype,
                        discount: discount,
                        vat: vat,
                        company_id: company_id,
                        branch_id: branch_id,
                        vat_type: vat_type
                    },
                    url: 'Goodreceive/Goodreceiveinsertupdate',
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
        }

    });

    var tempsupplier;
    var tempgrntype;
    $('#porder').change(function() {
        var porderID = $(this).val();

        $.ajax({
            type: "POST",
            data: {
                recordID: porderID
            },
            url: 'Goodreceive/Getcompanyaccoporder',
            success: function(result) { //alert(result);
                $('#company_id').val(result).css('pointer-events', 'none');
            }
        });

        $.ajax({
            type: "POST",
            data: {
                recordID: porderID
            },
            url: 'Goodreceive/Getbranchaccoporder',
            success: function(result) { //alert(result);
                $('#branch_id').val(result).css('pointer-events', 'none');
            }
        });


        function getSupplier() {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    type: "POST",
                    data: {
                        recordID: porderID
                    },
                    url: 'Goodreceive/Getsupplieraccoporder',
                    success: function(result) {
                        $('#supplier').val(result).css('pointer-events', 'none');

                        tempsupplier = result;
                        resolve();
                    },
                    error: reject
                });
            });
        }

        function getGrntype() {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    type: "POST",
                    data: {
                        recordID: porderID
                    },
                    url: 'Goodreceive/Getpordertpeaccoporder',
                    success: function(result) {
                        $('#grntype').val(result).css('pointer-events', 'none');

                        tempgrntype = result;
                        getitems(porderID, result);
                        toggleServiceMaterial();
                        resolve();
                    },
                    error: reject
                });
            });
        }


        getSupplier()
            .then(getGrntype)
            .then(function() {

                getbatchno(tempsupplier, tempgrntype);
            })
            .catch(function(error) {
                console.error("An error occurred:", error);
            });

    });

    $('#porder').change(function () {
    	var porderID = $(this).val();

    	$.ajax({
    		type: "POST",
    		data: {
    			recordID: porderID
    		},
    		url: 'Goodreceive/Getsupplier',
    		success: function (result) {
    			$('#supplier').empty();

    			if (result) {
    				var data = JSON.parse(result);
    				if (data.id) {
    					$('#supplier').append('<option value="' + data.id + '">' + data.name + '</option>');
    				}
    			}
    		},
    	});
    });

    $('#product').change(function () {
        var productID = $(this).val();
        var porderID = $('#porder').val();

        $.ajax({
            type: "POST",
            url: 'Goodreceive/Getservicematerials',
            data: {
                recordID: productID,
                porderID: porderID
            },
            success: function (result) {
                $('#servicematerial').empty();
                $('#servicematerial').append('<option value="">Select</option>');

                var data = JSON.parse(result);

                if (data.length > 0) {
                    $.each(data, function (index, item) {
                        var $opt = $('<option></option>')
                            .val(item.comment)
                            .text(item.comment)
                            .data('recordid', item.idtbl_print_porder_detail);

                        $('#servicematerial').append($opt);
                    });
                }
            }
        });
    });

    $('#servicematerial').change(function () {
        var comment  = $(this).val();
        var detailid = $('#servicematerial option:selected').data('recordid');

        $('#comment').val(comment);
        $('#uom').prop('disabled', false).css('pointer-events', 'auto');

        if (!detailid) {
            $('#newqty').val('');
            $('#unitprice').val('0');
            $('#piecesper_qty').val('0');
            $('#uom').val('');
            return;
        }

        $.ajax({
            type: "POST",
            url: 'Goodreceive/Getservicematerialsprices',
            data: {
                recordID: detailid
            },
            success: function (result) {
                var data = JSON.parse(result);

                if (data.length > 0) {
                    $('#newqty').val(data[0].qty);
                    $('#unitprice').val(data[0].unitprice);
                    $('#piecesper_qty').val(0);
                    $('#uom').val(data[0].tbl_measurements_idtbl_measurements).trigger('change');
                } else {
                    $('#newqty').val('');
                    $('#unitprice').val('0');
                    $('#uom').val('');
                    $('#piecesper_qty').val('0');
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load quantity and price.'
                });
            }
        });
    });


    function getbatchno(supplierID, typeID) {

        $.ajax({
            type: "POST",
            data: {
                recordID: supplierID
            },
            url: 'Goodreceive/Getbatchnoaccosupplier',
            success: function(result) {
                // alert(result);
                $('#batchno').val(result);
            }
        });
    }

    function getitems(porderID) {
            $.ajax({
                type: "POST",
                data: {
                    recordID: porderID
                },
                url: 'Goodreceive/Getproductaccoporder',
                success: function(result) { //alert(result);
                    var obj = JSON.parse(result);
                    var html1 = '';
                    html1 += '<option value="">Select</option>';
                    $.each(obj, function(i, item) {
                        html1 += '<option value="' + obj[i]
                            .idtbl_print_material_info +
                            '">';
                        html1 += obj[i].materialname + ' / ' + obj[i]
                            .materialinfocode;
                        html1 += '</option>';
                    });
                    $('#product').empty().append(html1);
                }
            });
    };
    $('#product').change(function () {
        var productID = $(this).val();
        var grn_id = $('#porder').val();
        var grntype = $('#grntype').val(); // GRN type

        $.ajax({
            type: "POST",
            url: 'Goodreceive/Getproductinfoaccoproduct',
            data: {
                recordID: productID,
                grn_id: grn_id
            },
            success: function (result) {
                try {
                    var obj = JSON.parse(result);
                    if (grntype != 4) {
                    var actualQty = parseFloat(obj.actual_qty) || 0;
                    var pieces = parseFloat(obj.pieces) || 0;
                    var qty = parseFloat(obj.qty) || 0;
                    var conversion_qty = parseFloat(obj.conversion_rate) || 1;
                    var newqty = 0,
                        qtyLabel = 0;

                    if (pieces > 0) {
                        var remainingPieces = pieces - actualQty;
                        newqty = remainingPieces / conversion_qty;
                        qtyLabel = newqty;

                        if (actualQty <= 0) {
                            $('#newqty').val(newqty);
                        } else {
                            $('#newqty').val('');
                        }
                    } else {
                        newqty = qty - actualQty;
                        qtyLabel = newqty;
                        $('#newqty').val(newqty);
                    }

                        $('#newqty').data('original-qty', qty);
                        $('#newqty').data('original-pieces', pieces);
                        $('#qtylabel').html(qtyLabel);
                        $('#uom').val(obj.uom || '');
                        $('#unitprice').val(obj.unitprice || '');
                        $('#piecesper_qty').val(pieces);
                        /* comment intentionally left alone for normal materials */
                        $('#porderdetailsid').val(obj.detailsid || '');

                    } else {
                        $('#uom, #unitprice, #piecesper_qty, #porderdetailsid').val('');
                        $('#qtylabel').html('');
                    }

                    $('#piecesper_qty')
                        .closest('.form-group')
                        .toggle(pieces > 0);

                } catch (e) {
                    console.error("Error parsing response:", e);
                    alert("Error processing product information.");
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
                alert("Error loading product information. Please try again.");
            }
        });
    });

    $('#newqty').on('input', function () {
        var newQty = parseFloat($(this).val()) || 0;
        var originalQty = parseFloat($(this).data('original-qty')) || 1;
        var originalPieces = parseFloat($(this).data('original-pieces')) || 0;

        var newPieces = Math.round((newQty / originalQty) * originalPieces);
        $('#piecesper_qty').val(newPieces);
    });

    $('#dataTable tbody').on('click', '.btnLabel', function() {
        var id = $(this).attr('id');
        $('#lablemodal').modal('show');

        $.ajax({
            type: "POST",
            data: {
                recordID: id
            },
            url: '<?php echo base_url() ?>Goodreceive/Getmateriallistaccogrn',
            success: function(result) { //alert(result);
                var obj = JSON.parse(result);
                var html1 = '';
                html1 += '<option value="">Select</option>';
                $.each(obj, function(i, item) {
                    html1 += '<option value="' + obj[i]
                        .idtbl_print_material_info +
                        '">';
                    html1 += obj[i].materialname + ' / ' + obj[i]
                        .materialinfocode;
                    html1 += '</option>';
                });
                $('#materiallist').empty().append(html1);
            }
        });
    });

    $('#btncreatelable').click(function() {
        if (!$("#formlable")[0].checkValidity()) {
            // If the form is invalid, submit it. The form won't actually submit;
            // this will just cause the browser to display the native HTML5 error messages.
            $("#hidesubmitbtn").click();
        } else {
            let mname = $('#mname').val();
            let mcode = $('#mcode').val();
            let grnno = $('#grnno').val();
            let pono = $('#pono').val();
            let mfdate = $('#lmfdate').val();
            let expdate = $('#lexpdate').val();
            let batchno = $('#lbatchno').val();

            var link = '<?php echo base_url() ?>Goodreceive/Createlabel/' + mname + '/' +
                mcode + '/' +
                grnno + '/' + pono + '/' + mfdate + '/' + expdate + '/' + batchno;
            window.open(link, '_blank');
            $('#hideresetbtn').click();
            $('#lablemodal').modal('hide');
        }
    });
});

function deactive_confirm() {
    return confirm("Are you sure you want to deactive this?");
}

function active_confirm() {
    return confirm("Are you sure you want to approve this good receive note?");
}

function delete_confirm() {
    return confirm("Are you sure you want to reject this good receive note?");
}

function finaltotalcalculate() {
    var vat = parseFloat($("#vat").val()) || 0;
    var discount = parseFloat($("#discount").val()) || 0;
    var total = parseFloat($("#hidetotalorder").val()) || 0;
    var vatType = $("#vat_type").val();

    if (isNaN(discount)) {
        discount = 0;
        $("#discount").val(0);
    }

    if (isNaN(vat)) {
        vat = 0;
        $("#vat").val(0);
    }

    var subTotal = total - discount;
    $('#hiddenfulltotal').val(subTotal.toFixed(2));

    var vatAmount = 0;
    var finalTotal;

    if (vatType === "1") { 
        vatAmount = (subTotal * vat) / 100;
        $('#vatamount').val(vatAmount.toFixed(2));
        finalTotal = subTotal + vatAmount;
    } else { 
        $('#vatamount').val("0.00");
        finalTotal = subTotal;
    }

    $('#modeltotalpayment').val(finalTotal.toFixed(2));
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
function getVat() {
    var currentDate = $('#grndate').val();

    $.ajax({
        type: "POST",
        data: {
            currentDate: currentDate,
        },
        url: 'Goodreceive/Getvatpresentage',
        success: function(result) { //alert(result);
            var obj = JSON.parse(result);

            $('#vat').val(obj);
        }
    });
}
function approvejob(confirmnot){
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
                    grnid: $('#grnid').val(),
                    confirmnot: confirmnot
                },
                url: '<?php echo base_url() ?>Goodreceive/Approvestatus',
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
                    requestid: $('#grnid').val(),
                    confirmnot: confirmnot
                },
                url: '<?php echo base_url() ?>Goodreceive/Goodreceivecheckstatus',
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
</script>
<?php include "include/footer.php"; ?>