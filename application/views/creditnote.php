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
                            <div class="page-header-icon"><i class="fas fa-undo"></i></div>
                            <span>Credit Note</span>
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
                                    Credit Note</button>
                                <hr>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="scrollbar pb-3" id="style-2">
                                    <table class="table table-bordered table-striped table-sm nowrap" id="dataTable">
                                        <thead>
                                            <tr>
                                                <th>Credit Note No</th>
                                                <th>Date</th>
                                                <th>Invoice No</th>
                                                <th>Customer</th>
                                                <th>Status</th>
                                                <th class="text-right">Total</th>
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
                <h5 class="modal-title" id="staticBackdropLabel">Create Credit Note</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <form id="createcnform" autocomplete="off" onsubmit="return false;">
                            <div class="form-row mb-3">
                                <div class="col-3">
                                    <label class="small font-weight-bold text-dark">Credit Note Date*</label>
                                    <input type="date" class="form-control form-control-sm" id="cndate"
                                        value="<?php echo date('Y-m-d') ?>" readonly>
                                </div>
                                <div class="col-9">
                                    <label class="small font-weight-bold text-dark">Invoice No*</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="cnInvoiceNo" class="form-control form-control-sm"
                                            placeholder="e.g. 26OCT_BR03_00001">
                                        <div class="input-group-append">
                                            <button type="button" id="btncnsearch" class="btn btn-warning btn-sm font-weight-bold px-3">
                                                <i class="fas fa-search"></i>&nbsp;Search</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="cninvinfo" class="small border rounded p-2 mt-2 mb-3 bg-light d-none"></div>
                        </form>
                    </div>
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="scrollbar pb-3" id="style-3">
                            <table class="table table-striped table-bordered table-sm small" id="tablecn">
                                <thead>
                                    <tr>
                                        <th nowrap style="width:25px;"></th>
                                        <th nowrap>Item Name</th>
                                        <th nowrap class="text-center">Sold</th>
                                        <th nowrap class="text-center">Returned</th>
                                        <th nowrap style="width:80px;" class="text-center">Return Qty</th>
                                        <th nowrap class="text-right">Unit Price</th>
                                        <th nowrap class="text-center">Back to stock</th>
                                        <th nowrap>Reason</th>
                                        <th nowrap class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col text-right">
                                <div class="small">Subtotal (excl. VAT): <b id="divcnsub">Rs. 0.00</b></div>
                                <div class="small">VAT: <b id="divcnvat">Rs. 0.00</b></div>
                                <h6 class="font-weight-600" id="divcntotal" style="margin-top: 10px;"> Rs. 0.00</h6>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group mt-2">
                            <button type="button" id="btncreatecn" class="btn btn-primary btn-sm fa-pull-right"
                                <?php if($addcheck==0){echo 'disabled';} ?>><i class="fas fa-save"></i>&nbsp;Create
                                Credit Note</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Modal -->
<div id="creditnoteview">
    <div class="modal fade" id="cnviewmodal" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">View Credit Note</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="viewhtml"></div>
                    <div class="row">
                        <div class="col-12 text-right">
                            <hr>
                            <?php if($approvecheck==1){ ?>
                            <div id="approvalControls" class="d-none">
                                <button id="btnapprovereject" class="btn btn-primary btn-sm px-3 mb-2">
                                    <i class="fas fa-check mr-2"></i>Approve or Reject
                                </button>
                            </div>
                            <?php } ?>
                            <input type="hidden" id="cnid">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "include/footerscripts.php"; ?>

<script>
$(document).ready(function() {

    var cnInvoice = null, cnLines = [];
    var createBtnHtml = '<i class="fas fa-save"></i>&nbsp;Create Credit Note';

    function cnEsc(s) { return $('<div>').text(s == null ? '' : s).html(); }
    function cnFmt(n) { return addCommas(parseFloat(n || 0).toFixed(2)); }

    // ---------------- list ----------------
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
                title: 'Credit Note Information',
                text: '<i class="fas fa-file-csv mr-2"></i> CSV',
            },
            {
                extend: 'pdf',
                className: 'btn btn-danger btn-sm',
                title: 'Credit Note Information',
                text: '<i class="fas fa-file-pdf mr-2"></i> PDF',
            },
            {
                extend: 'print',
                title: 'Credit Note Information',
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
            url: "<?php echo base_url() ?>scripts/creditnotelist.php",
            type: "POST"
        },
        "order": [
            [1, "desc"]
        ],
        "columns": [
            { "data": "creditnote_no" },
            { "data": "creditnote_date" },
            { "data": "invoice_no" },
            { "data": "customer_name" },
            {
                "data": "settle_status",
                "render": function(data, type, row) {
                    if (row['approve_status'] == 0) return '<span class="badge badge-secondary">Pending Approval</span>';
                    if (row['approve_status'] == 2) return '<span class="badge badge-danger">Rejected</span>';
                    if (data == 1) return '<span class="badge badge-success">Used</span> <small class="text-muted">' + cnEsc(row['settle_invoice_no']) + '</small>';
                    return '<span class="badge badge-info">Approved</span>';
                }
            },
            {
                "className": 'text-right',
                "data": "totalamount",
                "render": function(data) {
                    return cnFmt(data);
                }
            },
            {
                "className": 'text-right',
                "orderable": false,
                "data": null,
                "render": function(data, type, full) {
                    var button = '';
                    button += '<button data-toggle="tooltip" data-placement="bottom" title="View Credit Note" class="btn btn-dark btn-sm btnview mr-1" id="' +
                        full['idtbl_credit_note'] + '" approve_status="' + full['approve_status'] + '"><i class="fas fa-eye"></i></button>';
                    button += '<a href="<?php echo base_url() ?>Creditnote/Printcreditnote/' + full['idtbl_credit_note'] +
                        '" target="_blank" data-toggle="tooltip" data-placement="bottom" title="Print Credit Note" class="btn btn-danger btn-sm mr-1 ' +
                        (full['approve_status'] == 1 ? '' : 'd-none') + '"><i class="fas fa-file-pdf"></i></a>';
                    return button;
                }
            }
        ],
        drawCallback: function(settings) {
            $('[data-toggle="tooltip"]').tooltip();
        }
    });

    // ---------------- view ----------------
    $('#dataTable tbody').on('click', '.btnview', function() {
        var id = $(this).attr('id');
        var st = $(this).attr('approve_status');
        $('#cnid').val(id);
        $.ajax({
            type: "POST",
            data: { recordID: id },
            url: '<?php echo base_url() ?>Creditnote/Creditnoteview',
            success: function(result) {
                $('#viewhtml').html(result);
                $('#approvalControls').toggleClass('d-none', st != 0);   // only pending notes can be approved
                $('#cnviewmodal').modal('show');
            }
        });
    });

    // ---------------- create: search the invoice ----------------
    function cnSearch() {
        var no = $('#cnInvoiceNo').val().trim();
        if (!no) {
            Swal.fire({ icon: 'warning', title: 'Invoice required', text: 'Enter the invoice number.' });
            return;
        }
        $.ajax({
            type: "POST",
            data: { invoice_no: no },
            url: '<?php echo base_url() ?>Creditnote/Searchinvoice',
            success: function(result) {console.log(result);
            
                var obj = JSON.parse(result);
                if (obj.status != 1) {
                    cnReset(false);
                    action(obj.action);
                    return;
                }
                cnInvoice = obj.invoice;
                cnLines = obj.lines;
                cnRender();
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong. Please try again later.' });
            }
        });
    }

    function cnRender() {
        $('#cninvinfo').removeClass('d-none').html(
            '<div><b>Date:</b> ' + cnEsc(cnInvoice.invoice_date) + '</div>' +
            '<div><b>Customer:</b> ' + cnEsc(cnInvoice.customer_name || 'Walk-in') + '</div>' +
            '<div><b>Invoice total:</b> Rs. ' + cnFmt(cnInvoice.net_total) + '</div>' +
            (cnInvoice.has_customer ? '' : '<div class="text-danger mt-1">This invoice has no customer record, so the credit note cannot be applied to a new invoice automatically.</div>')
        );

        var tb = $('#tablecn tbody').empty();
        cnLines.forEach(function(l, i) {
            var left = parseInt(l.returnable_qty), serial = !!l.serialno, off = left <= 0;
            tb.append(
                '<tr class="pointer' + (off ? ' text-muted' : '') + '" data-i="' + i + '">' +
                '<td class="text-center"><input type="checkbox" class="cn-pick"' + (off ? ' disabled' : '') + '></td>' +
                '<td>' + cnEsc(l.product_name) + (serial ? '<br><small class="text-muted">S/N: ' + cnEsc(l.serialno) + '</small>' : '') + '</td>' +
                '<td class="text-center">' + l.qty + '</td>' +
                '<td class="text-center">' + l.returned_qty + '</td>' +
                '<td><input type="number" class="form-control form-control-sm cn-qty" min="1" max="' + left + '" value="' + (off ? 0 : 1) + '"' + (serial || off ? ' readonly' : '') + '></td>' +
                '<td class="text-right">' + cnFmt(l.paid_unit) + '</td>' +
                '<td class="text-center">' + (serial ? '<small class="text-muted">returned status</small>' : '<input type="checkbox" class="cn-restock" checked>') + '</td>' +
                '<td><input type="text" class="form-control form-control-sm cn-remark" maxlength="250" placeholder="Reason"></td>' +
                '<td class="text-right cn-amt">0.00</td></tr>'
            );
        });
        cnRecalc();
    }

    function cnRecalc() {
        var total = 0;
        $('#tablecn tbody tr').each(function() {
            var l = cnLines[$(this).data('i')], left = parseInt(l.returnable_qty);
            var q = parseInt($(this).find('.cn-qty').val()) || 0;
            q = Math.max(0, Math.min(q, left));
            var amt = $(this).find('.cn-pick').is(':checked') ? Math.round(q * l.paid_unit * 100) / 100 : 0;
            $(this).find('.cn-amt').text(cnFmt(amt));
            total += amt;
        });
        var rate = 1 + (cnInvoice ? cnInvoice.vat_rate : 18) / 100;
        var vat = Math.round((total - total / rate) * 100) / 100;
        $('#divcnsub').text('Rs. ' + cnFmt(total - vat));
        $('#divcnvat').text('Rs. ' + cnFmt(vat));
        $('#divcntotal').html('<strong style="background-color: yellow;">Credit Total</strong> &nbsp;&nbsp;<strong>Rs. ' + cnFmt(total) + '</strong>');
    }

    function cnReset(clearInput) {
        cnInvoice = null;
        cnLines = [];
        if (clearInput) $('#cnInvoiceNo').val('');
        $('#cninvinfo').addClass('d-none').empty();
        $('#tablecn tbody').empty();
        cnRecalc();
    }

    $('#btncnsearch').click(cnSearch);
    $('#cnInvoiceNo').on('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); cnSearch(); }
    });
    $('#tablecn tbody').on('change input', '.cn-pick, .cn-qty', cnRecalc);
    $('#staticBackdrop').on('hidden.bs.modal', function() { cnReset(true); });

    // ---------------- create: save ----------------
    $('#btncreatecn').click(function() {
        if (!cnInvoice) {
            Swal.fire({ icon: "warning", title: "Invoice required", text: "Search and select an invoice first." });
            return;
        }

        var items = [];
        $('#tablecn tbody tr').each(function() {
            if (!$(this).find('.cn-pick').is(':checked')) return;
            var l = cnLines[$(this).data('i')];
            items.push({
                detailId: l.idtbl_invoice_detail,
                qty: parseInt($(this).find('.cn-qty').val()) || 0,
                restock: $(this).find('.cn-restock').is(':checked') ? 1 : 0,
                remark: $(this).find('.cn-remark').val()
            });
        });

        if (items.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "No Data",
                text: "Please select at least one item to return.",
            });
            return;
        }

        $('#btncreatecn').prop('disabled', true).html(
            '<i class="fas fa-circle-notch fa-spin mr-2"></i> Creating Credit Note...'
        );

        var cnData = { creditnote: JSON.stringify({ invoiceId: cnInvoice.id, items: items }) };

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
                    url: "<?php echo base_url() ?>Creditnote/Insertcreditnote",
                    data: cnData,
                    success: function(result) {
                        Swal.close();
                        document.body.style.overflow = 'auto';

                        var obj = JSON.parse(result);

                        if (obj.status == 1) {
                            actionreload(obj.action);
                        } else {
                            $('#btncreatecn').prop('disabled', false).html(createBtnHtml);
                            action(obj.action);
                        }
                    },
                    error: function() {
                        Swal.close();
                        document.body.style.overflow = 'auto';
                        $('#btncreatecn').prop('disabled', false).html(createBtnHtml);

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

    $('#btnapprovereject').click(function () {
        Swal.fire({
            title: "Do you want to approve this Credit Note?",
            text: "Stock will be updated after approval.",
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: "Approve",
            denyButtonText: `Reject`
        }).then((result) => {
            if (result.isConfirmed) {
                cnApprove(1, '');
            } else if (result.isDenied) {
                Swal.fire({
                    title: 'Reject reason',
                    input: 'text',
                    showCancelButton: true,
                    confirmButtonText: 'Reject',
                    inputValidator: (v) => !v && 'Please enter a reason'
                }).then((r) => {
                    if (r.isConfirmed) cnApprove(2, r.value);
                });
            }
        });
    });

    function cnApprove(confirmnot, remark) {
        Swal.fire({
            title: '',
            html: '<div class="div-spinner"><div class="custom-loader"></div></div>',
            allowOutsideClick: false,
            showConfirmButton: false,
            backdrop: `rgba(255, 255, 255, 0.5)`,
            customClass: { popup: 'fullscreen-swal' },
            didOpen: () => {
                document.body.style.overflow = 'hidden';
                $.ajax({
                    type: "POST",
                    data: { cnid: $('#cnid').val(), confirmnot: confirmnot, remark: remark },
                    url: '<?php echo base_url() ?>Creditnote/Creditnotestatus',
                    success: function (result) {
                        Swal.close();
                        document.body.style.overflow = 'auto';
                        var obj = JSON.parse(result);
                        if (obj.status == 1) { actionreload(obj.action); } else { action(obj.action); }
                    },
                    error: function () {
                        Swal.close();
                        document.body.style.overflow = 'auto';
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong. Please try again later.' });
                    }
                });
            }
        });
    }
});
</script>
<?php include "include/footer.php"; ?>