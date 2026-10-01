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
                            <span>Good Receive Return Note</span>
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
                                    Good Receive Return Note</button>
                                <hr>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="scrollbar pb-3" id="style-2">
                                    <table class="table table-bordered table-striped table-sm nowrap" id="dataTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Batch No</th>
                                                <th>GRN No</th>
                                                <th>GRN Type</th>
                                                <th>Supplier</th>
                                                <th>Discount</th>
                                                <th>Sub Total</th>
                                                <th>Vat %</th>
                                                <th>Total</th>
                                                <th>Remark</th>
                                                <th>Approved Status</th>
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
                <h5 class="modal-title" id="staticBackdropLabel">Create Good Receive Return Note</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-4 col-xl-4">
                        <form id="createorderform" autocomplete="off">
                            <div class="form-row mb-1">
                                <div class="col">
                                    <label class="small font-weight-bold text-dark">Company</label>
                                    <input type="text" id="f_company_name" name="f_company_name"
                                        class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col">
                                    <label class="small font-weight-bold text-dark">Company Branch</label>
                                    <input type="text" id="f_branch_name" name="f_branch_name"
                                        class="form-control form-control-sm" readonly>
                                </div>
                            </div>
                            <div class="form-row mb-1">
                                <div class="col">
                                    <label class="small font-weight-bold text-dark">Supplier*</label>
                                    <select class="form-control form-control-sm selecter2 px-0" name="supplier"
                                        id="supplier" required>
                                        <option value="">Select</option>
                                        <?php foreach($supplierlist->result() as $rowsupplierlist){ ?>
                                        <option value="<?php echo $rowsupplierlist->idtbl_supplier ?>">
                                            <?php echo $rowsupplierlist->suppliername ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row mb-1">
                                <div class="col">
                                    <label class="small font-weight-bold text-dark">GRN Numbers*</label>
                                    <select class="form-control form-control-sm selecter2 px-0" name="grn_no"
                                        id="grn_no" required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                                <div class="col">
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
                            <div class="form-row mb-1">
                                <div class="col-9">
                                    <label class="small font-weight-bold text-dark">Product*</label>
                                    <select class="form-control form-control-sm selecter2 px-0" name="product"
                                        id="product" required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label class="small font-weight-bold text-dark">Ordered Qty</label>
                                    <input type="text" id="orderedqty" name="orderedqty"
                                        class="form-control form-control-sm" readonly>
                                </div>
                            </div>
                            <div class="form-row mb-1">
                                <div class="col">
                                    <label class="small font-weight-bold text-dark">Return Qty*</label>
                                    <label class="small font-weight-bold text-danger" id="qtylabel"></label>
                                    <input type="text" id="returnqty" name="returnqty"
                                        class="form-control form-control-sm"
                                        <?php if($editcheck==0){echo 'readonly';} ?> required>
                                </div>
                                <div class="col">
                                    <label class="small font-weight-bold text-dark">UOM*</label>
                                    <select class="form-control form-control-sm" style="pointer-events: none;"
                                        name="uom" id="uom" readonly>
                                        <option value="">Select</option>
                                        <?php foreach($measurelist->result() as $rowmeasurelist){ ?>
                                        <option value="<?php echo $rowmeasurelist->idtbl_mesurements ?>">
                                            <?php echo $rowmeasurelist->measure_type ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row mb-1">
                                <div class="col">
                                    <label class="small font-weight-bold text-dark">Unit Price</label>
                                    <input type="text" id="unitprice" name="unitprice"
                                        class="form-control form-control-sm"
                                        <?php if($editcheck==0){echo 'readonly';} ?> value="0">
                                </div>
                                <div class="col">
                                    <label class="small font-weight-bold text-dark">Discount</label>
                                    <input type="text" id="unitdiscount" name="unitdiscount"
                                        class="form-control form-control-sm"
                                        <?php if($editcheck==0){echo 'readonly';} ?> value="0">
                                </div>
                            </div>
                            <div class="form-row mb-1">
                                <div class="col">
                                    <label class="small font-weight-bold text-dark">Comment</label>
                                    <textarea id="comment" name="comment"
                                        class="form-control form-control-sm"></textarea>
                                </div>
                            </div>
                            <div class="form-group mb-1">
                                <label class="small font-weight-bold text-dark">Batch No</label>
                                <input type="text" id="batchno" name="batchno" class="form-control form-control-sm"
                                    required readonly>
                            </div>
                            <div class="form-group mt-3 text-right">
                                <button type="button" id="formsubmit" class="btn btn-primary btn-sm px-4"
                                    <?php if($addcheck==0){echo 'disabled';} ?>><i class="fas fa-plus"></i>&nbsp;Add to
                                    list</button>
                                <input name="submitBtn" type="submit" value="Save" id="submitBtn" class="d-none">
                            </div>
                        </form>
                    </div>
                    <div class="col-sm-12 col-md-12 col-lg-8 col-xl-8">
                        <div class="scrollbar pb-3" id="style-3">
                            <table class="table table-striped table-bordered table-sm small" id="tableorder">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th class="d-none">ProductID</th>
                                        <th class="text-right">Orderd QTY</th>
                                        <th class="text-right">Avalible Stock QTY</th>
                                        <th class="text-right">Return QTY</th>
                                        <th class="text-right">Unit Price</th>
                                        <th class="text-center">UOM</th>
                                        <th class="d-none">UOM ID</th>
                                        <th class="text-right">Discount</th>
                                        <th>Comment</th>
                                        <th class="text-right">Total</th>
                                        <th class="d-none">Total Hide</th>
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
                            <div class="col-6">
                                <label class="small font-weight-bold text-dark">Vat (%)*</label>
                                <input type="number" id="vat" name="vat" class="form-control form-control-sm" value="0"
                                    onkeyup="finaltotalcalculate();" required>
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
                                class="btn btn-outline-primary btn-sm fa-pull-right"><i
                                    class="fas fa-save"></i>&nbsp;Create Good Receive return Note</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewmodal" data-backdrop="static" data-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">View Good Recieve Return Note</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="GRNView">
                <div class="row">
                    <div class="col-6 text-left">
                        <img src="./images/book.jpg" alt="" width="40%" style="margin-top: -20px;">
                    </div>
                    <div class="col-6">
                        <h2 style="margin-bottom: 2px; color: black;font-family: cursive;font-size:20px;font-weight: bold; padding:0;"
                            class="text-right">Good Recieve Return Note<span id="pr"></span>
                        </h2>
                        <p style="margin-bottom: 2px; font-family: cursive;font-size:15px; font-weight: bold; padding-top: 8px;padding:0;"
                            class="text-right">Multi Offset Printers (PVT) LTD <span id="proname"></span>
                        </p>
                        <p style="margin-bottom: 2px; font-family: cursive;font-size:15px; font-weight: bold; padding-top: 8px;padding:0;"
                            class="text-right">MO/GRN-0000<span id="grncode"></span>
                        </P>
                    </div>
                </div>
                <div id="viewhtml"></div>
            </div>
            <div class="modal-footer">
                <button type="button" id="printgrn" class="btn btn-outline-primary btn-sm fa-pull-right"
                    <?php if($addcheck==0){echo 'disabled';} ?>><i class="far fa-save"></i>&nbsp;Print GRN
                    Return</button>
            </div>
        </div>
    </div>
</div>

<?php include "include/footerscripts.php"; ?>
<script>
$(document).ready(function() {
    // Company / Branch come straight from the logged-in user's session - no manual selection needed
    $('#f_company_name').val('<?php echo ($_SESSION['companyname']); ?>');
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
    $('#product').select2({
        dropdownParent: $('#staticBackdrop'),
        width: '100%',
    });

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
        ],
        ajax: {
            url: "<?php echo base_url() ?>scripts/goodreceivereturnlist.php",
            type: "POST",
        },
        "order": [
            [0, "desc"]
        ],
        "columns": [
            {
                "data": null,
                "render": function(data, type, full, meta) {
                    return meta.settings._iRecordsDisplay - meta.row;
                }
            },
            { "data": "batchno" },
            {
                "data": function(row) {
                    return "GRN000" + row.grn_no;
                }
            },
            {
                "data": "group"
            },
            { "data": "suppliername" },
            {
                "targets": -1,
                "className": 'text-right',
                "data": null,
                "render": function(data, type, full) {
                    return addCommas(parseFloat(full['discount']).toFixed(2));
                }
            },
            {
                "targets": -1,
                "className": 'text-right',
                "data": null,
                "render": function(data, type, full) {
                    return addCommas(parseFloat(full['subtotal']).toFixed(2));
                }
            },
            { "data": "vat", "className": 'text-right' },
            {
                "targets": -1,
                "className": 'text-right',
                "data": null,
                "render": function(data, type, full) {
                    return addCommas(parseFloat(full['totalpayment']).toFixed(2));
                }
            },
            { "data": "remark" },
            {
                "targets": -1,
                "className": '',
                "data": null,
                "render": function(data, type, full) {
                    if (full['approvestatus'] == 1) {
                        return '<i class="fas fa-check text-success mr-2"></i>Approved GRN Return';
                    } else {
                        return 'Not Approved GRN Return';
                    }
                }
            },
            {
                "targets": -1,
                "className": 'text-right',
                "data": null,
                "render": function(data, type, full) {
                    var button = '';

                    button += '<button class="btn btn-dark btn-sm btnview mr-1" id="' + full[
                            'idtbl_print_grn_return'] + '" data-grnid="' + full['grn_no'] +
                        '" data-toggle="tooltip" data-placement="top" title="View"><i class="fas fa-eye"></i></button>';
                    if (full['approvestatus'] == 1) {
                        button += '<button class="btn btn-success btn-sm mr-1 ';
                        if (statuscheck != 1) {
                            button += 'd-none';
                        }
                        button += '"><i class="fas fa-check"></i></button>';
                    } else {
                        button +=
                            '<a href="<?php echo base_url() ?>Goodreceivereturn/Goodreceivereturnstatus/' +
                            full['idtbl_print_grn_return'] +
                            '/1" onclick="return active_confirm()" target="_self" class="btn btn-danger btn-sm mr-1 ';
                        if (statuscheck != 1) {
                            button += 'd-none';
                        }
                        button +=
                            '" data-toggle="tooltip" data-placement="top" title="Approved"><i class="fas fa-times"></i></a>';
                    }
                    if (full['approvestatus'] == 0) {
                        button +=
                            '<a href="<?php echo base_url() ?>Goodreceivereturn/Goodreceivereturnstatus/' +
                            full['idtbl_print_grn_return'] +
                            '/3" onclick="return delete_confirm()" target="_self" class="btn btn-danger btn-sm mr-1 ';
                        if (statuscheck != 1) {
                            button += 'd-none';
                        }
                        button +=
                            '" data-toggle="tooltip" data-placement="top" title="Delete"><i class="fas fa-trash-alt"></i></a>';
                    }

                    return button;
                }
            }
        ],
        drawCallback: function(settings) {
            $('[data-toggle="tooltip"]').tooltip();
        }
    });

    // Supplier -> GRN numbers (already scoped by company/branch via session inside the model)
    $('#supplier').change(function() {
        var supplierID = $(this).val();
        $('#grn_no').empty().append('<option value="">Select</option>').trigger('change');
        $.ajax({
            type: "POST",
            data: {
                recordID: supplierID
            },
            url: 'Goodreceivereturn/Getgrnaccsupllier',
            success: function(result) {
                var obj = JSON.parse(result);
                var html1 = '<option value="">Select</option>';
                $.each(obj, function(i, item) {
                    html1 += '<option value="' + obj[i].idtbl_print_grn + '">' +
                        'GRN000' + obj[i].idtbl_print_grn + '</option>';
                });
                $('#grn_no').empty().append(html1);
            }
        });
    });

    // GRN number -> batch/type + product list (products always come from tbl_print_material_info
    // via tbl_print_grndetail, the same way the GRN screen itself lists its products)
    $('#grn_no').change(function() {
        var grnNo = $(this).val();

        $('#batchno').val('').trigger('change');
        $('#grntype').val('').trigger('change');
        $('#product').empty().append('<option value="">Select</option>').trigger('change');

        $.ajax({
            type: "POST",
            data: {
                recordID: grnNo
            },
            url: 'Goodreceivereturn/Getordertypesetgrn',
            success: function(result) {
                var obj = JSON.parse(result);
                $('#batchno').val(obj.batchNo);
                $('#grntype').val(obj.grnType);
                $('#grntype').prop('disabled', true);

                $.ajax({
                    type: "POST",
                    data: {
                        grnNo: grnNo
                    },
                    url: 'Goodreceivereturn/Getproducts',
                    success: function(result) {
                        var obj = JSON.parse(result);
                        var html = '<option value="">Select</option>';
                        $.each(obj, function(i, item) {
                            html += '<option value="' + obj[i].id +
                                '">' + obj[i].name + '</option>';
                        });
                        $('#product').empty().append(html);
                    }
                });
            }
        });
    });

    $('#product').change(function() {
        var productID = $(this).val();
        var batchNo = $('#batchno').val();
        var grnNo = $('#grn_no').val();

        $('#orderedqty').val('');
        $('#qtylabel').html('');
        $('#uom').val('');
        $('#unitprice').val('');
        $('#returnqty').val('');

        $.ajax({
            type: "POST",
            data: {
                productID: productID,
                batchNo: batchNo,
                grnNo: grnNo
            },
            url: 'Goodreceivereturn/Getproductdetails',
            success: function(result) {
                var obj = JSON.parse(result);
                $('#orderedqty').val(obj.orderedQty);
                $('#qtylabel').html(obj.stockQty);
                $('#uom').val(obj.measureType);
                $('#unitprice').val(obj.unitPrice);
            }
        });
    });

    $("#formsubmit").click(function() {
        if (!$("#createorderform")[0].checkValidity()) {
            $("#submitBtn").click();
        } else {
            var product = $("#product option:selected").text();
            var productID = $('#product').val();
            var orderedQty = $('#orderedqty').val();
            var stockQty = $('#qtylabel').text();
            var returnQty = parseFloat($('#returnqty').val());
            var unitPrice = parseFloat($('#unitprice').val());
            var uom = $("#uom option:selected").text();
            var uomID = $('#uom').val();
            var discount = parseFloat($('#unitdiscount').val());
            var comment = $('#comment').val();

            var newtotal = parseFloat((unitPrice * returnQty) - discount);
            var total = parseFloat(newtotal);
            var showtotal = addCommas(parseFloat(total).toFixed(2));

            var productExists = false;
            $('#tableorder tbody tr').each(function() {
                var existingProductID = $(this).find('td.d-none').first().text();
                if (existingProductID === productID) {
                    productExists = true;
                    return false;
                }
            });

            if (productExists) {
                alert("This Product has already been added.");
                return;
            }

            $('#tableorder > tbody:last').append('<tr class="pointer"><td>' + product +
                '</td><td class="d-none">' + productID + '</td><td class="text-right">' +
                orderedQty +
                '</td><td class="text-right">' + stockQty + '</td><td class="text-right">' +
                returnQty +
                '</td><td class="text-right">' + unitPrice + '</td><td class="text-center">' + uom +
                '</td><td class="d-none">' + uomID + '</td><td class="text-right">' + discount +
                '</td><td>' + comment + '<td class="text-right"> ' + showtotal +
                '</td><td class="d-none total"> ' + total +
                '</td><td><button type="button" onclick= "productDelete(this);" id="btnDeleterow" class=" btn btn-danger btn-sm float-right"><i class="fas fa-trash-alt"></i></button></td> </tr>'
            );

            $('#grn_no').prop('disabled', true);

            $('#product').val('').trigger('change');
            $('#orderedqty').val('');
            $('#returnqty').val('');
            $('#qtylabel').empty();
            $('#uom').val('').trigger('change');
            $('#unitprice').val('0');
            $('#unitdiscount').val('0');
            $('#comment').val('');

            var sum = 0;
            $(".total").each(function() {
                sum += parseFloat($(this).text());
            });

            var showsum = addCommas(parseFloat(sum).toFixed(2));

            $('#divtotal').html('Rs. ' + showsum);
            $('#hidetotalorder').val(sum);

            $('#product').focus();
        }
        finaltotalcalculate();
    });

    $('#tableorder').on('click', 'tr.pointer', function() {
        var r = confirm("Are you sure, You want to remove this product ? ");
        if (r == true) {
            $(this).closest('tr').remove();

            var sum = 0;
            $(".total").each(function() {
                sum += parseFloat($(this).text());
            });

            var showsum = addCommas(parseFloat(sum).toFixed(2));

            $('#divtotal').html('Rs. ' + showsum);
            $('#hidetotalorder').val(sum);

            $('#product').focus();

            var tablebody = $('#tableorder tbody tr').length;
            if (tablebody == 0) {
                $('#grn_no').prop('disabled', false);
            }

            finaltotalcalculate();
        }
    });

    $('#discount').change(function() {
        var checkdiscount = parseFloat($("#discount").val());
        if (!checkdiscount == "") {
            finaltotalcalculate();
        }
    });

    $('#vat').change(function() {
        var checkvat = parseFloat($("#vat").val());
        if (!checkvat == "") {
            finaltotalcalculate();
        }
    });

    $('#btncreateorder').click(function() {
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

            var supplier = $('#supplier').val();
            var grnNo = $('#grn_no').val();
            var grnType = $('#grntype').val();
            var batchNo = $('#batchno').val();
            var discount = $('#discount').val();
            var subTotal = $('#hiddenfulltotal').val();
            var vat = $('#vat').val();
            var totalPayment = $('#modeltotalpayment').val();
            var remark = $('#remark').val();

            $.ajax({
                type: "POST",
                data: {
                    tableData: jsonObj,
                    supplier: supplier,
                    grnNo: grnNo,
                    grnType: grnType,
                    batchNo: batchNo,
                    discount: discount,
                    subTotal: subTotal,
                    vat: vat,
                    totalPayment: totalPayment,
                    remark: remark
                },
                url: 'Goodreceivereturn/Goodreceivereturninsertupdate',
                success: function(result) {
                    $('#staticBackdrop').modal('hide');
                    var obj = JSON.parse(result);
                    if (obj.status == 1) {
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    }
                    action(obj.action)
                }
            });
        }
    });

    $('#dataTable tbody').on('click', '.btnview', function() {
        var id = $(this).attr('id');
        var grnid = $(this).data('grnid');
        $('#grncode').html(grnid);
        $.ajax({
            type: "POST",
            data: {
                recordID: id
            },
            url: '<?php echo base_url() ?>Goodreceivereturn/Goodreceivereturnview',
            success: function(result) {
                $('#viewmodal').modal('show');
                $('#viewhtml').html(result);
            }
        });
    });
});

function deactive_confirm() {
    return confirm("Are you sure you want to deactive this?");
}

function active_confirm() {
    return confirm("Are you sure you want to approve this GRN Return?");
}

function delete_confirm() {
    return confirm("Are you sure you want to reject this GRN Return?");
}

function productDelete(elem) {
    $(elem).closest('tr').trigger('click');
}

function finaltotalcalculate() {
    var vat = parseFloat($("#vat").val());
    var discount = parseFloat($("#discount").val());
    var total = parseFloat($("#hidetotalorder").val());

    if (isNaN(discount)) {
        discount = 0;
        $("#discount").val(0);
    }

    if (isNaN(vat)) {
        vat = 0;
        $("#vat").val(0);
    }

    var finalsubtot = total - discount
    $('#hiddenfulltotal').val(finalsubtot.toFixed(2))

    var vatamount = parseFloat((finalsubtot / 100) * vat);
    var finaltotal = finalsubtot + vatamount

    $('#modeltotalpayment').val(finaltotal.toFixed(2));
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
<?php include "include/footer.php"; ?>