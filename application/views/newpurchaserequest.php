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
                            <div class="page-header-icon"><i class="fa fa-shopping-cart"></i></div>
                            <span>New Purchase Order Request</span>   
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2">
                <div class="card">
                    <div class="card-body p-0 p-3">
                        <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-4 col-xl-4">
                                <form id="createorderform" autocomplete="off">
                                    <div class="form-row mb-1">
                                        <div class="col">
                                            <label class="small font-weight-bold text-dark">Company*</label>
                                            <input type="text" id="f_company_name" name="f_company_name"
                                                class="form-control form-control-sm" required readonly>
                                        </div>
                                        <div class="col">
                                            <label class="small font-weight-bold text-dark">Company Branch*</label>
                                            <input type="text" id="f_branch_name" name="f_branch_name"
                                                class="form-control form-control-sm" required readonly>
                                        </div>
                                    </div>
                                    <input type="hidden" name="f_company_id" id="f_company_id">
                                    <input type="hidden" name="f_branch_id" id="f_branch_id">

                                    <div class="form-row mb-1">
                                        <div class="col">
                                            <label class="small font-weight-bold text-dark">Request Date</label>
                                            <input type="date" class="form-control form-control-sm"
                                                name="date" id="date" value="<?php echo date('Y-m-d')?>">
                                        </div>
                                    </div>

                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold text-dark">Product*</label>
                                        <select class="form-control form-control-sm" name="product" id="product"></select>
                                    </div>

                                    <div class="form-row mb-1">
                                        <div class="col">
                                            <label class="small font-weight-bold text-dark">Qty*</label>
                                            <span class="text-danger font-weight-bold" id="stockqty"></span>
                                            <input type="number" min="0" step="any" id="newqty" name="newqty"
                                                class="form-control form-control-sm">
                                        </div>
                                        <div class="col">
                                            <label class="small font-weight-bold text-dark">Unit</label>
                                            <input type="text" id="unit" name="unit"
                                                class="form-control form-control-sm" readonly>
                                        </div>
                                    </div>

                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold text-dark">Comment</label>
                                        <textarea name="comment" id="comment" class="form-control form-control-sm"></textarea>
                                    </div>

                                    <div class="form-group mt-3 text-right">
                                        <button type="button" id="formsubmit" class="btn btn-warning btn-sm font-weight-bold px-4"
                                            <?php if($addcheck==0){echo 'disabled';} ?>>
                                            <i class="fas fa-plus"></i>&nbsp;Add to list
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-8 col-xl-8">
                                <div id="materialmachinetblpart">
                                    <table class="table table-striped table-bordered table-sm small" id="tableorder">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-center">Unit</th>
                                                <th>Comment</th>
                                                <th class="text-right">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <hr>
                                <div class="form-group mt-2">
                                    <button type="button" id="btncreateorder" class="btn btn-primary btn-sm fa-pull-right"
                                        <?php if($addcheck==0){echo 'disabled';} ?>>
                                        <i class="fas fa-save mr-2"></i>&nbsp;Create Request
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container-fluid mt-2 p-0">
                    <div class="card">
                        <div class="card-body p-0 p-3">
                            <div class="row">
                                <div class="col-12">
                                    <div class="scrollbar pb-3" id="style-2">
                                        <table class="table table-bordered table-striped table-sm nowrap" id="dataTable">
                                            <thead>
                                                <tr>
                                                    <th>P-Order Req Number</th>
                                                    <th>Date</th>
                                                    <th>Branch</th>
                                                    <th>Confirm Status</th>
                                                    <th>Check By</th>
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
            </div>
        </main>
        <?php include "include/footerbar.php"; ?>
    </div>
</div>

<!-- Modal -->
<div id="purchaseview">
    <div class="modal fade" id="porderviewmodal" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">View Purchase Order Request<span id="pr" class="d-none"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6 small">
                            <label class="small font-weight-bold text-dark mb-1">Date:</label> <span id="viewdate"></span><br>
                            <label class="small font-weight-bold text-dark mb-1">Request No:</label> <span id="porder_number"></span>
                        </div>
                        <div class="col-6 small">
                            <label class="small font-weight-bold text-dark mb-1">Company:</label> <span id="viewcompanyname"></span><br>
                            <label class="small font-weight-bold text-dark mb-1">Branch:</label> <span id="viewbranchname"></span><br>
                            <label class="small font-weight-bold text-dark mb-1">Check By:</label> <span id="viewcheckby"></span>
                        </div>
                    </div>
                    <hr class="border-dark">
                    <div id="viewhtml"></div>
                    <div class="col-12 text-right">
                        <hr>
                        <?php if($approvecheck==1){ ?>
                        <button id="btnapprovereject" class="btn btn-primary btn-sm px-3 mb-2"><i class="fas fa-check mr-2"></i>Approve or Reject</button>
                        <?php } ?>
                        <input type="hidden" name="requestid" id="requestid">
                        <?php if($checkstatus==1){ ?>
                        <button id="btncheck" class="btn btn-success btn-sm px-3 mb-2"><i class="fas fa-user-check mr-2"></i>Check By</button>
                        <?php } ?>
                    </div>
                    <div class="col-12 text-center"><div id="alertdiv"></div></div>
                    <div class="col-12 text-center"><div id="checkalertdiv"></div></div>
                </div>
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

    var addcheck    = '<?php echo $addcheck; ?>';
    var editcheck   = '<?php echo $editcheck; ?>';
    var statuscheck = '<?php echo $statuscheck; ?>';
    var deletecheck = '<?php echo $deletecheck; ?>';

    /* ---------------- Product search (select2) ---------------- */
    $('#product').select2({
        width: '100%',
        placeholder: 'Search by code, name, model or barcode',
        minimumInputLength: 1,
        allowClear: true,
        ajax: {
            url: '<?php echo base_url() ?>Newpurchaserequest/GetProducts',
            type: 'POST',
            dataType: 'json',
            delay: 250,
            data: function(params) { return { query: params.term || '' }; },
            processResults: function(data) { return { results: data }; }
        }
    });

    // product chosen -> fill unit + load stock
    $('#product').on('select2:select', function(e) {
        var d = e.params.data;
        $('#unit').val(d.unit || '');
        $('#stockqty').html('');

        $.ajax({
            type: "POST",
            data: { recordID: d.id },
            url: '<?php echo base_url() ?>Newpurchaserequest/Getstockqty',
            success: function(result) {
                var obj = JSON.parse(result);
                $('#stockqty').html('(Stock: ' + obj.qty + ')');
            }
        });
    });

    $('#product').on('select2:clear', function() {
        $('#unit').val('');
        $('#stockqty').html('');
    });

    /* ---------------- Add item to list ---------------- */
    $('#formsubmit').click(function() {
        var productData = $('#product').select2('data')[0];
        var qty = parseFloat($('#newqty').val());

        if (!productData || !productData.id) {
            Swal.fire({ icon: 'warning', title: 'Select a product' });
            return;
        }
        if (isNaN(qty) || qty <= 0) {
            Swal.fire({ icon: 'warning', title: 'Enter a valid quantity' });
            return;
        }

        // prevent duplicates
        var exists = false;
        $('#tableorder tbody tr').each(function() {
            if ($(this).data('id') == productData.id) { exists = true; return false; }
        });
        if (exists) {
            Swal.fire({ icon: 'warning', title: 'This product is already in the list' });
            return;
        }

        var comment = $('#comment').val();

        var $tr = $('<tr class="pointer"></tr>')
            .data('id', productData.id)
            .data('qty', qty)
            .data('comment', comment);

        $tr.append($('<td></td>').text(productData.text));
        $tr.append($('<td class="text-center"></td>').text(qty));
        $tr.append($('<td class="text-center"></td>').text($('#unit').val()));
        $tr.append($('<td></td>').text(comment));
        $tr.append('<td><button type="button" class="btn btn-danger btn-sm float-right btnremoverow"><i class="fas fa-trash-alt"></i></button></td>');

        $('#tableorder tbody').append($tr);

        $('#product').val(null).trigger('change');
        $('#unit').val('');
        $('#stockqty').html('');
        $('#newqty').val('');
        $('#comment').val('');
        $('#product').select2('open');
    });

    // remove a row
    $('#tableorder').on('click', '.btnremoverow', function() {
        if (confirm("Are you sure, You want to remove this product ? ")) {
            $(this).closest('tr').remove();
        }
    });

    /* ---------------- Create request ---------------- */
    $('#btncreateorder').click(function() {

        if ($('#tableorder tbody tr').length === 0) {
            Swal.fire({ icon: 'warning', title: 'No Data', text: 'Please add items before creating a request.' });
            return;
        }

        var tableData = [];
        $('#tableorder tbody tr').each(function() {
            tableData.push({
                product_id: $(this).data('id'),
                qty:        $(this).data('qty'),
                comment:    $(this).data('comment')
            });
        });

        var requestData = {
            tableData: tableData,
            date: $('#date').val()
        };

        var $btn = $('#btncreateorder');
        $btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin mr-2"></i> Creating Request');

        Swal.fire({
            title: '',
            html: '<div class="div-spinner"><div class="custom-loader"></div></div>',
            allowOutsideClick: false,
            showConfirmButton: false,
            backdrop: 'rgba(255, 255, 255, 0.5)',
            customClass: { popup: 'fullscreen-swal' },
            didOpen: () => {
                document.body.style.overflow = 'hidden';

                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url() ?>Newpurchaserequest/Newpurchaserequestinsertupdate",
                    data: requestData,
                    success: function(result) {
                        Swal.close();
                        document.body.style.overflow = 'auto';

                        var response = JSON.parse(result);
                        if (response.status == 1) {
                            actionreload(response.action);
                            setTimeout(() => window.location.reload(), 2000);
                        } else {
                            action(response.action);
                            $btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i>&nbsp;Create Request');
                        }
                    },
                    error: function() {
                        Swal.close();
                        document.body.style.overflow = 'auto';
                        $btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i>&nbsp;Create Request');
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong. Please try again later.' });
                    }
                });
            }
        });
    });

    /* ---------------- Request list ---------------- */
    $('#dataTable').DataTable({
        "destroy": true,
        "processing": true,
        "serverSide": true,
        dom: "<'row'<'col-sm-5'B><'col-sm-2'l><'col-sm-5'f>>" + "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        responsive: true,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
        "buttons": [
            { extend: 'csv', className: 'btn btn-success btn-sm', title: 'New Purchase Order Request Information', text: '<i class="fas fa-file-csv mr-2"></i> CSV' },
            { extend: 'pdf', className: 'btn btn-danger btn-sm', title: 'New Purchase Order Request Information', text: '<i class="fas fa-file-pdf mr-2"></i> PDF' },
            {
                extend: 'print',
                title: 'New Purchase Order Request Information',
                className: 'btn btn-primary btn-sm',
                text: '<i class="fas fa-print mr-2"></i> Print',
                customize: function(win) {
                    $(win.document.body).find('table').addClass('compact').css('font-size', 'inherit');
                }
            }
        ],
        ajax: {
            url: "<?php echo base_url() ?>scripts/newpurchaserequestlist.php",
            type: "POST",
            "data": function(d) {
                return $.extend({}, d, { "company_id": '<?php echo ($_SESSION['company_id']); ?>' });
            }
        },
        "order": [[0, "desc"]],
        "columns": [
            { "data": "porder_req_no" },
            { "data": "date" },
            { "data": "branch" },
            { "data": "confirmstatus_display", "className": '' },
            { "data": "name" },
            {
                "targets": -1,
                "className": 'text-right',
                "data": null,
                "orderable": false,
                "render": function(data, type, full) {
                    var button = '';

                    button += '<a href="<?php echo base_url() ?>Newpurchaserequest/Printinvoice/' + full['idtbl_porder_req'] +
                        '" target="_blank" data-toggle="tooltip" data-placement="bottom" title="Print Request" class="btn btn-danger btn-sm mr-1 ';
                    if (editcheck != 1) { button += 'd-none'; }
                    button += '"><i class="fas fa-file-pdf mr-2"></i></a>';

                    button += '<button data-toggle="tooltip" data-placement="bottom" title="View Request" class="btn btn-dark btn-sm btnview mr-1" id="' +
                        full['idtbl_porder_req'] + '" aproval_id="' + full['confirmstatus'] +
                        '" check_status="' + full['check_by'] + '"><i class="fas fa-eye"></i></button>';

                    if (full['porderconfirm'] == 1 && statuscheck == 1) {
                        button += '<button data-toggle="tooltip" data-placement="bottom" title="Active" class="btn btn-success btn-sm mr-1"><i class="fas fa-check"></i></button>';
                    }

                    return button;
                }
            }
        ],
        drawCallback: function() {
            $('[data-toggle="tooltip"]').tooltip();
        }
    });

    /* ---------------- View request modal ---------------- */
    $('#dataTable tbody').on('click', '.btnview', function() {
        var id = $(this).attr('id');
        var approvestatus = $(this).attr('aproval_id');
        var checkstatus = $(this).attr('check_status');

        $('#requestid').val(id);

        $.ajax({
            type: "POST",
            data: { recordID: id },
            url: '<?php echo base_url() ?>Newpurchaserequest/Purchaseorderview',
            success: function(result) {
                $('#porderviewmodal').modal('show');
                $('#viewhtml').html(result);

                if (approvestatus > 0) {
                    $('#btnapprovereject').addClass('d-none').prop('disabled', true);
                    if (approvestatus == 1) {
                        $('#alertdiv').html('<div class="alert alert-success" role="alert"><i class="fas fa-check-circle mr-2"></i> Request approved</div>');
                    } else if (approvestatus == 2) {
                        $('#alertdiv').html('<div class="alert alert-danger" role="alert"><i class="fas fa-times-circle mr-2"></i> Request rejected</div>');
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
                    $('#checkalertdiv').html('<div class="alert alert-secondary" role="alert"><i class="fas fa-check-circle mr-2"></i> Request checked</div>');
                }
            }
        });

        $.ajax({
            type: "POST",
            data: { recordID: id },
            url: '<?php echo base_url() ?>Newpurchaserequest/porderviewheader',
            success: function(result) {
                var obj = JSON.parse(result);
                $('#porder_number').text(obj.porder_no);
                $('#viewcompanyname').text(obj.companyname);
                $('#viewbranchname').text(obj.branchname);
            }
        });
    });

    // reset modal state (bound once)
    $('#porderviewmodal').on('hidden.bs.modal', function() {
        $('#alertdiv').html('');
        $('#checkalertdiv').html('');
        $('#btnapprovereject').removeClass('d-none').prop('disabled', false);
        $('#btncheck').removeClass('d-none').prop('disabled', false);
    });

    $('#btnapprovereject').click(function() {
        Swal.fire({
            title: "Do you want to approve this Request?",
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: "Approve",
            denyButtonText: "Reject"
        }).then((result) => {
            if (result.isConfirmed) { approvejob(1); }
            else if (result.isDenied) { approvejob(2); }
        });
    });

    $('#btncheck').click(function() {
        Swal.fire({
            title: "Do you want to check this Request?",
            showCancelButton: true,
            confirmButtonText: "Check"
        }).then((result) => {
            if (result.isConfirmed) { checkjob(1); }
        });
    });
});

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
        placement: { from: "top", align: "center" },
        offset: 100,
        spacing: 10,
        z_index: 1031,
        delay: 5000,
        timer: 1000,
        url_target: '_blank',
        mouse_over: null,
        animate: { enter: 'animated fadeInDown', exit: 'animated fadeOutUp' },
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

function runStatusRequest(url, confirmnot) {
    Swal.fire({
        title: '',
        html: '<div class="div-spinner"><div class="custom-loader"></div></div>',
        allowOutsideClick: false,
        showConfirmButton: false,
        backdrop: 'rgba(255, 255, 255, 0.5)',
        customClass: { popup: 'fullscreen-swal' },
        didOpen: () => {
            document.body.style.overflow = 'hidden';

            $.ajax({
                type: "POST",
                data: { requestid: $('#requestid').val(), confirmnot: confirmnot },
                url: url,
                success: function(result) {
                    Swal.close();
                    document.body.style.overflow = 'auto';
                    var obj = JSON.parse(result);
                    if (obj.status == 1) { actionreload(obj.action); }
                    else { action(obj.action); }
                },
                error: function() {
                    Swal.close();
                    document.body.style.overflow = 'auto';
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong. Please try again later.' });
                }
            });
        }
    });
}

function approvejob(confirmnot) {
    runStatusRequest('<?php echo base_url() ?>Newpurchaserequest/Newpurchaserequeststatus', confirmnot);
}

function checkjob(confirmnot) {
    runStatusRequest('<?php echo base_url() ?>Newpurchaserequest/Newpurchaserequestcheckstatus', confirmnot);
}
</script>
<?php include "include/footer.php"; ?>