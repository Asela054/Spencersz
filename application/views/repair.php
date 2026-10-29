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
                            <div class="page-header-icon"><i class="fas fa-tools"></i></div>
                            <span>Repair Jobs (Warranty &amp; Over-warranty)</span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2">
                <div class="card">
                    <div class="card-body p-0 p-2">
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <select id="fStatus" class="form-control form-control-sm">
                                    <option value="open">Open jobs</option>
                                    <option value="">All jobs</option>
                                    <option value="1">Received</option>
                                    <option value="2">Awaiting customer approval</option>
                                    <option value="3">In repair</option>
                                    <option value="4">Ready for delivery</option>
                                    <option value="5">Delivered</option>
                                    <option value="6">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-8 text-right">
                                <button type="button" id="btnNewJob" class="btn btn-primary btn-sm"
                                    <?php if($addcheck==0){echo 'disabled';} ?>><i class="fas fa-plus mr-2"></i>New Repair Job</button>
                            </div>
                        </div>
                        <hr class="mt-1">
                        <div class="row">
                            <div class="col-12">
                                <div class="scrollbar pb-3" id="style-2">
                                    <table class="table table-bordered table-striped table-sm nowrap" id="dataTable">
                                        <thead>
                                            <tr>
                                                <th>Job No</th>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Product</th>
                                                <th>Serial</th>
                                                <th>Customer</th>
                                                <th>Phone</th>
                                                <th>Warranty</th>
                                                <th>Status</th>
                                                <th class="text-right">Charge</th>
                                                <th class="text-right">Actions</th>
                                                <th class="d-none">ID</th>
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

<!-- New job -->
<div class="modal fade" id="jobmodal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-tools mr-2 text-primary"></i>New Repair Job</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-5">
                        <label class="small font-weight-bold text-dark mb-1">Serial / IMEI*</label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="rSerial" class="form-control form-control-sm" placeholder="Scan or type the serial number" autocomplete="off">
                            <div class="input-group-append">
                                <button type="button" id="btnRSearch" class="btn btn-warning btn-sm font-weight-bold px-3"><i class="fas fa-search"></i>&nbsp;Check</button>
                            </div>
                        </div>
                        <div id="rInfo" class="small border rounded p-2 mt-2 bg-light d-none"></div>
                        <div id="rManualBox" class="mt-2 d-none">
                            <label class="small font-weight-bold text-dark mb-1">Product name*</label>
                            <input type="text" id="rProduct" class="form-control form-control-sm" maxlength="200">
                        </div>
                        <div id="rCoverBox" class="mt-2 d-none">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="rNotCovered">
                                <label class="custom-control-label small" for="rNotCovered">Warranty does not cover this fault (physical / liquid damage etc.)</label>
                            </div>
                            <input type="text" id="rNotCoveredWhy" class="form-control form-control-sm mt-1 d-none" maxlength="250" placeholder="Reason*">
                        </div>
                        <div class="mt-2 small font-weight-bold">Repair type: <span id="rTypeBadge" class="text-muted">-</span></div>
                    </div>
                    <div class="col-lg-7">
                        <div class="form-row">
                            <div class="form-group col-md-7 mb-1">
                                <label class="small font-weight-bold text-dark mb-1">Customer name*</label>
                                <input type="text" id="rName" class="form-control form-control-sm" maxlength="150">
                            </div>
                            <div class="form-group col-md-5 mb-1">
                                <label class="small font-weight-bold text-dark mb-1">Phone*</label>
                                <input type="text" id="rPhone" class="form-control form-control-sm" maxlength="20">
                            </div>
                        </div>
                        <div class="form-group mb-1">
                            <label class="small font-weight-bold text-dark mb-1">Fault description*</label>
                            <textarea id="rFault" class="form-control form-control-sm" rows="3" maxlength="500"></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6 mb-1">
                                <label class="small font-weight-bold text-dark mb-1">Accessories received</label>
                                <input type="text" id="rAcc" class="form-control form-control-sm" maxlength="250" placeholder="charger, box, sim tray ...">
                            </div>
                            <div class="form-group col-md-6 mb-1">
                                <label class="small font-weight-bold text-dark mb-1">Condition of the item</label>
                                <input type="text" id="rCond" class="form-control form-control-sm" maxlength="250" placeholder="scratches, cracks ...">
                            </div>
                        </div>
                        <div class="form-group mb-1">
                            <label class="small font-weight-bold text-dark mb-1">Promised date</label>
                            <input type="date" id="rPromised" class="form-control form-control-sm">
                        </div>
                        <hr>
                        <button type="button" id="btnCreateJob" class="btn btn-primary btn-sm fa-pull-right"
                            <?php if($addcheck==0){echo 'disabled';} ?>><i class="fas fa-save"></i>&nbsp;Create Job</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Manage job -->
<div class="modal fade" id="repairmodal" data-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-tools mr-2 text-primary"></i>Repair Job</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="repairbody"></div>
        </div>
    </div>
</div>

<!-- Job card PDF -->
<div class="modal fade" id="pdfmodal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark py-2">
                <h6 class="modal-title text-white"><i class="fas fa-file-pdf mr-2"></i>Job Card</h6>
                <button type="button" class="close" data-dismiss="modal"><span class="text-light">&times;</span></button>
            </div>
            <div class="modal-body p-0"><iframe id="pdfframe" style="width:100%; height:78vh; border:0;"></iframe></div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('pdfframe').contentWindow.print();"><i class="fas fa-print"></i> Print</button>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include "include/footerscripts.php"; ?>

<script>
$(document).ready(function() {

    var BASE = '<?php echo base_url() ?>';
    var VAT = 18;
    var TECHS = <?php echo json_encode($technicians) ?>;
    var PAYTYPES = <?php echo json_encode($paytypes) ?>;

    var TYPE = { 1: ['Warranty Repair', 'success'], 2: ['Over-warranty Repair', 'warning'] };
    var STATUS = { 1: ['Received', 'secondary'], 2: ['Awaiting Customer Approval', 'info'], 3: ['In Repair', 'primary'],
                   4: ['Ready for Delivery', 'success'], 5: ['Delivered', 'dark'], 6: ['Cancelled', 'danger'] };

    function rEsc(s) {
        return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                                         .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
    function money(n) { return (parseFloat(n) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function badge(map, k) { var m = map[k] || ['-', 'light']; return '<span class="badge badge-' + m[1] + '">' + m[0] + '</span>'; }

    // ================= list =================
    var table = $('#dataTable').DataTable({
        "destroy": true,
        "processing": true,
        "serverSide": true,
        dom: "<'row'<'col-sm-5'B><'col-sm-2'l><'col-sm-5'f>>" + "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        responsive: true,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
        "buttons": [
            { extend: 'csv', className: 'btn btn-success btn-sm', title: 'Repair Jobs', text: '<i class="fas fa-file-csv mr-2"></i> CSV',
              exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9] } },
            { extend: 'pdf', className: 'btn btn-danger btn-sm', title: 'Repair Jobs', text: '<i class="fas fa-file-pdf mr-2"></i> PDF',
              exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9] } },
            { extend: 'print', className: 'btn btn-primary btn-sm', title: 'Repair Jobs', text: '<i class="fas fa-print mr-2"></i> Print',
              exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9] },
              customize: function(win) { $(win.document.body).find('table').addClass('compact').css('font-size', 'inherit'); } }
        ],
        ajax: {
            url: BASE + 'scripts/repairlist.php',
            type: 'POST',
            data: function(d) { d.job_status = $('#fStatus').val(); }
        },
        "order": [[11, "desc"]],
        "columns": [
            { "data": "job_no" },
            { "data": "job_date" },
            { "data": "repair_type", "render": function(d) { return badge(TYPE, d); } },
            { "data": "product_name" },
            { "data": "serialno" },
            { "data": "customer_name" },
            { "data": "customer_phone" },
            { "data": "warranty_status", "render": function(d, t, row) {
                if (d == 1) return '<span class="text-success">In warranty until ' + rEsc(row['warranty_end']) + '</span>';
                if (d == 2) return '<span class="text-danger">Expired ' + rEsc(row['warranty_end']) + '</span>';
                return '<span class="text-muted">No sale record</span>';
            } },
            { "data": "job_status", "render": function(d) { return badge(STATUS, d); } },
            { "className": 'text-right', "data": "total_charge", "render": function(d, t, row) {
                return row['repair_type'] == 1 ? '<span class="text-muted">Free</span>' : money(d);
            } },
            { "className": 'text-right', "orderable": false, "searchable": false, "data": null, "render": function(d, t, full) {
                return '<button data-toggle="tooltip" title="Open / Manage" class="btn btn-dark btn-sm btnmanage mr-1" id="' + full['idtbl_repair_job'] + '"><i class="fas fa-tools"></i></button>'
                     + '<button data-toggle="tooltip" title="Print Job Card" class="btn btn-danger btn-sm btnprint" id="' + full['idtbl_repair_job'] + '"><i class="fas fa-file-pdf"></i></button>';
            } },
            { "data": "idtbl_repair_job", "visible": false, "searchable": false }
        ],
        drawCallback: function() { $('[data-toggle="tooltip"]').tooltip(); }
    });

    $('#fStatus').change(function() { table.ajax.reload(); });
    $('#dataTable tbody').on('click', '.btnmanage', function() { openJob($(this).attr('id')); });
    $('#dataTable tbody').on('click', '.btnprint', function() { showPdf($(this).attr('id')); });

    // ================= job card PDF =================
    function showPdf(id) {
        $('#pdfframe').attr('src', BASE + 'Repair/Printrepair/' + id);
        $('#pdfmodal').modal('show');
    }
    $('#pdfmodal').on('hidden.bs.modal', function() { $('#pdfframe').attr('src', 'about:blank'); });

    // ================= new job =================
    var rItem = null;      // the sold item that was found (null = manual / not checked)
    var rChecked = false;

    $('#btnNewJob').click(function() {
        rItem = null; rChecked = false;
        $('#jobmodal input[type=text], #jobmodal input[type=date], #jobmodal textarea').val('');
        $('#rNotCovered').prop('checked', false);
        $('#rInfo, #rManualBox, #rCoverBox, #rNotCoveredWhy').addClass('d-none');
        $('#rTypeBadge').html('-');
        $('#jobmodal').modal('show');
    });

    function rType() {
        if (!rChecked) { $('#rTypeBadge').html('-'); return; }
        var t = (rItem && rItem.in_warranty && !$('#rNotCovered').is(':checked')) ? 1 : 2;
        $('#rTypeBadge').html(badge(TYPE, t));
    }

    function rSearch() {
        var sn = $('#rSerial').val().trim();
        if (!sn) return;
        $.ajax({
            type: 'POST', dataType: 'json', url: BASE + 'Repair/Searchserial', data: { serial: sn },
            success: function(res) {
                if (res.status != 1) { action(res.action); return; }
                rChecked = true;
                rItem = res.found ? res.item : null;
                rShowInfo(res);
            },
            error: function() { Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong. Please try again later.' }); }
        });
    }

    function rShowInfo(res) {
        var h = '';
        $('#rCoverBox').addClass('d-none');
        $('#rNotCovered').prop('checked', false);
        $('#rNotCoveredWhy').val('').addClass('d-none');

        if (res.open_job) h += '<div class="text-danger font-weight-bold mb-1">An open job already exists for this serial: ' + rEsc(res.open_job) + '</div>';

        if (res.found) {
            var it = res.item;
            $('#rManualBox').addClass('d-none');
            h += '<div><b>' + rEsc(it.product_name) + '</b></div>'
               + '<div>Invoice: ' + rEsc(it.invoice_no) + ' (' + rEsc(it.invoice_date) + ')</div>'
               + '<div>Customer: ' + rEsc(it.customer_name || 'Walk-in') + '</div>'
               + '<div>Warranty: ' + it.warranty_months + ' months &nbsp;|&nbsp; ends ' + rEsc(it.warranty_end) + '</div>';
            if (it.returned_on) {
                h += '<div class="text-danger font-weight-bold">This item was returned on credit note ' + rEsc(it.returned_on) + '.</div>';
            } else if (it.in_warranty) {
                h += '<div class="text-success font-weight-bold">IN WARRANTY (' + it.days_left + ' days left)</div>';
                $('#rCoverBox').removeClass('d-none');
            } else if (parseInt(it.warranty_months) > 0) {
                h += '<div class="text-danger font-weight-bold">WARRANTY EXPIRED (' + Math.abs(it.days_left) + ' days ago)</div>';
            } else {
                h += '<div class="text-danger font-weight-bold">NO WARRANTY on this item</div>';
            }
            if (res.repairs > 0) h += '<div class="text-muted">Earlier repairs of this serial: ' + res.repairs + '</div>';
            if (!$('#rName').val()) $('#rName').val(it.customer_name || '');
            if (!$('#rPhone').val()) $('#rPhone').val(it.phone || '');
        } else {
            $('#rManualBox').removeClass('d-none');
            h += '<div class="text-danger">No sale record was found for this serial number. It can still be taken as an over-warranty repair.</div>';
        }
        $('#rInfo').removeClass('d-none').html(h);
        rType();
    }

    $('#btnRSearch').click(rSearch);
    $('#rSerial').on('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); rSearch(); } })
                 .on('input', function() { rChecked = false; rItem = null; $('#rInfo, #rManualBox, #rCoverBox').addClass('d-none'); rType(); });
    $('#rNotCovered').change(function() { $('#rNotCoveredWhy').toggleClass('d-none', !this.checked); rType(); });

    $('#btnCreateJob').click(function() {
        if (!rChecked) { Swal.fire({ icon: 'warning', title: 'Check the serial number first' }); return; }
        if ($('#rNotCovered').is(':checked') && !$('#rNotCoveredWhy').val().trim()) {
            Swal.fire({ icon: 'warning', title: 'Enter the reason why the warranty does not cover this fault' });
            return;
        }
        var job = {
            detailId: rItem ? rItem.detail_id : null,
            serial: $('#rSerial').val().trim(),
            productName: $('#rProduct').val(),
            notCovered: $('#rNotCovered').is(':checked') ? $('#rNotCoveredWhy').val() : '',
            customerName: $('#rName').val(), customerPhone: $('#rPhone').val(),
            fault: $('#rFault').val(), accessories: $('#rAcc').val(), condition: $('#rCond').val(),
            promised: $('#rPromised').val()
        };

        var $btn = $('#btnCreateJob').prop('disabled', true);
        $.ajax({
            type: 'POST', dataType: 'json', url: BASE + 'Repair/Insertrepair', data: { job: JSON.stringify(job) },
            success: function(res) {
                $btn.prop('disabled', false);
                if (res.status != 1) { action(res.action); return; }
                table.ajax.reload(null, false);
                $('#jobmodal').one('hidden.bs.modal', function() { showPdf(res.job_id); }).modal('hide');   // print the job card
            },
            error: function() {
                $btn.prop('disabled', false);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong. Please try again later.' });
            }
        });
    });

    // ================= manage job =================
    var cur = null;

    function openJob(id) {
        $.ajax({
            type: 'POST', dataType: 'json', url: BASE + 'Repair/Getrepair', data: { id: id },
            success: function(res) {
                if (res.status != 1) { action(res.action); return; }
                cur = res;
                renderJob();
                $('#repairmodal').modal('show');
            }
        });
    }

    function partRow(p, editable) {
        p = p || { description: '', qty: 1, unitprice: '', total: 0 };
        if (!editable) {
            return '<tr><td>' + rEsc(p.description) + '</td><td class="text-center">' + p.qty + '</td><td class="text-right">' + money(p.unitprice) + '</td><td class="text-right">' + money(p.total) + '</td></tr>';
        }
        return '<tr class="pt"><td><input type="text" class="form-control form-control-sm pt-desc" maxlength="200" value="' + rEsc(p.description) + '"></td>'
             + '<td style="width:80px"><input type="number" min="1" class="form-control form-control-sm pt-qty" value="' + p.qty + '"></td>'
             + '<td style="width:120px"><input type="number" min="0" step="0.01" class="form-control form-control-sm pt-price" value="' + (p.unitprice === '' ? '' : parseFloat(p.unitprice)) + '"></td>'
             + '<td class="text-right pt-total" style="width:110px">0.00</td>'
             + '<td style="width:40px"><button type="button" class="btn btn-sm btn-outline-danger pt-del"><i class="fas fa-times"></i></button></td></tr>';
    }

    function renderJob() {
        var j = cur.job, st = parseInt(j.job_status), ty = parseInt(j.repair_type);
        var editable = (st === 1 || st === 3);
        var h = '';

        h += '<div class="d-flex flex-wrap align-items-center mb-2"><h5 class="mb-0 mr-3">' + rEsc(j.job_no) + '</h5>'
           + badge(TYPE, ty) + '<span class="mx-1"></span>' + badge(STATUS, st) + '</div>';

        var wtxt = j.warranty_status == 1 ? 'In warranty until ' + rEsc(j.warranty_end)
                 : j.warranty_status == 2 ? 'Warranty expired on ' + rEsc(j.warranty_end) : 'No sale record';
        h += '<div class="row small mb-2">'
           + '<div class="col-md-6"><div><b>Product:</b> ' + rEsc(j.product_name) + '</div>'
           + '<div><b>Serial:</b> ' + rEsc(j.serialno || '-') + '</div>'
           + '<div><b>Sale invoice:</b> ' + rEsc(j.sale_invoice_no || '-') + '</div>'
           + '<div><b>Warranty:</b> ' + wtxt + (j.not_covered_reason ? ' <span class="text-danger">(not covered: ' + rEsc(j.not_covered_reason) + ')</span>' : '') + '</div>'
           + '<div><b>Received:</b> ' + rEsc(j.job_date) + (j.promised_date ? ' &nbsp;|&nbsp; <b>Promised:</b> ' + rEsc(j.promised_date) : '') + '</div></div>'
           + '<div class="col-md-6"><div><b>Customer:</b> ' + rEsc(j.customer_name) + ' &nbsp; ' + rEsc(j.customer_phone) + '</div>'
           + '<div><b>Fault:</b> ' + rEsc(j.fault) + '</div>'
           + '<div><b>Accessories:</b> ' + rEsc(j.accessories || '-') + '</div>'
           + '<div><b>Condition:</b> ' + rEsc(j.condition_note || '-') + '</div>'
           + '<div><b>Technician:</b> ' + rEsc(j.technician_name || '-') + '</div></div></div>';

        // charges / parts
        h += '<div class="card mb-2"><div class="card-header py-1 small font-weight-bold">'
           + (ty === 1 ? 'Parts used (warranty repair, no charge to the customer)' : 'Charges (VAT inclusive)') + '</div><div class="card-body p-2">';
        h += '<table class="table table-sm table-bordered small mb-1" id="mParts"' + (editable ? ' data-edit="1"' : '') + '><thead><tr>'
           + '<th>Part / Service</th><th>Qty</th><th class="text-right">Unit Price</th><th class="text-right">Total</th>' + (editable ? '<th></th>' : '') + '</tr></thead><tbody>';
        $.each(cur.parts, function(i, p) { h += partRow(p, editable); });
        if (editable && !cur.parts.length) h += partRow(null, true);
        h += '</tbody></table>';
        if (editable) h += '<button type="button" class="btn btn-sm btn-outline-secondary" id="btnAddPart"><i class="fas fa-plus"></i> Add part</button>';
        if (ty === 2) {
            h += '<div class="row mt-2"><div class="col-md-5 offset-md-7 small">'
               + '<div class="d-flex justify-content-between align-items-center mb-1"><span>Labour / service charge</span>'
               + (editable ? '<input type="number" min="0" step="0.01" id="mLabour" class="form-control form-control-sm ml-2" style="width:120px" value="' + parseFloat(j.labour_charge) + '">'
                           : '<b>' + money(j.labour_charge) + '</b>') + '</div>'
               + '<div class="d-flex justify-content-between"><span>Parts</span><b id="mPartsTotal">' + money(j.parts_total) + '</b></div>'
               + '<div class="d-flex justify-content-between"><span>VAT included</span><b id="mVat">' + money(j.vatamount) + '</b></div>'
               + '<div class="d-flex justify-content-between h6 mt-1"><span>Total</span><b id="mTotal">' + money(j.total_charge) + '</b></div>'
               + (st === 5 ? '<div class="d-flex justify-content-between"><span>Paid (' + rEsc(j.paytype_name || '') + ')</span><b>' + money(j.paid_amount) + '</b></div>' : '')
               + '</div></div>';
        }
        h += '</div></div>';

        // actions
        var tech = '<select id="mTech" class="form-control form-control-sm d-inline-block mr-1" style="width:210px"><option value="">Technician (optional)</option>';
        $.each(TECHS, function(i, t) { tech += '<option value="' + t.id + '">' + rEsc(t.name) + '</option>'; });
        tech += '</select>';

        h += '<div class="mb-2" id="mActions">';
        if (st === 1 && ty === 1) h += tech + '<button class="btn btn-primary btn-sm mr-1" data-act="start"><i class="fas fa-play"></i> Start repair</button>';
        if (st === 1 && ty === 2) h += '<button class="btn btn-info btn-sm mr-1" data-act="estimate"><i class="fas fa-paper-plane"></i> Save &amp; send estimate</button>';
        if (st === 2) h += tech + '<button class="btn btn-success btn-sm mr-1" data-act="approve"><i class="fas fa-check"></i> Customer approved - start repair</button>'
                         + '<button class="btn btn-outline-danger btn-sm mr-1" data-act="reject"><i class="fas fa-times"></i> Customer declined</button>';
        if (st === 3) h += '<button class="btn btn-success btn-sm mr-1" data-act="ready"><i class="fas fa-box-open"></i> Mark as ready</button>';
        if (editable) h += '<button class="btn btn-outline-primary btn-sm mr-1" data-act="charges"><i class="fas fa-save"></i> Save charges</button>';
        if (st === 4) {
            h += '<div class="border rounded p-2 mb-2 bg-light"><div class="form-row">';
            if (ty === 2 && parseFloat(j.total_charge) > 0) {
                h += '<div class="col-md-3"><label class="small mb-0">Payment method*</label><select id="mPaytype" class="form-control form-control-sm"><option value="">Select</option>';
                $.each(PAYTYPES, function(i, p) { h += '<option value="' + p.id + '">' + rEsc(p.paytype) + '</option>'; });
                h += '</select></div><div class="col-md-3"><label class="small mb-0">Amount received* (total ' + money(j.total_charge) + ')</label>'
                   + '<input type="number" step="0.01" id="mPaid" class="form-control form-control-sm" value="' + parseFloat(j.total_charge) + '"></div>';
            }
            h += '<div class="col-md-3"><label class="small mb-0">Collected by</label><input type="text" id="mCollector" class="form-control form-control-sm" maxlength="150" value="' + rEsc(j.customer_name) + '"></div>'
               + '<div class="col-md-3 d-flex align-items-end"><button class="btn btn-dark btn-sm" data-act="deliver"><i class="fas fa-handshake"></i> Deliver to customer</button></div>'
               + '</div></div>';
        }
        if (st >= 1 && st <= 4) h += '<button class="btn btn-outline-danger btn-sm mr-1" data-act="cancel"><i class="fas fa-ban"></i> Cancel job</button>';
        h += '<button class="btn btn-danger btn-sm" data-act="print"><i class="fas fa-file-pdf"></i> Job card</button></div>';

        // history
        h += '<div class="small"><b>History</b><ul class="list-unstyled mb-0">';
        $.each(cur.log, function(i, l) {
            h += '<li class="border-bottom py-1"><span class="text-muted">' + rEsc(l.insertdatetime) + '</span> &nbsp;'
               + badge(STATUS, l.to_status) + ' ' + rEsc(l.remark || '') + ' <span class="text-muted">- ' + rEsc(l.by_user || '') + '</span></li>';
        });
        h += '</ul></div>';

        $('#repairbody').html(h);
        if (editable) recalcCharges();
    }

    function recalcCharges() {
        var parts = 0;
        $('#mParts tbody tr.pt').each(function() {
            var t = (parseFloat($(this).find('.pt-qty').val()) || 0) * (parseFloat($(this).find('.pt-price').val()) || 0);
            $(this).find('.pt-total').text(money(t));
            parts += t;
        });
        if (cur && parseInt(cur.job.repair_type) === 2) {
            var total = parts + (parseFloat($('#mLabour').val()) || 0);
            $('#mPartsTotal').text(money(parts));
            $('#mTotal').text(money(total));
            $('#mVat').text(money(total - total / (1 + VAT / 100)));
        }
    }

    function collectCharges() {
        var parts = [];
        $('#mParts tbody tr.pt').each(function() {
            parts.push({ desc: $(this).find('.pt-desc').val(), qty: $(this).find('.pt-qty').val(), price: $(this).find('.pt-price').val() });
        });
        return { labour: $('#mLabour').length ? $('#mLabour').val() : 0, parts: JSON.stringify(parts) };
    }

    function doAct(act, extra) {
        var id = cur.job.idtbl_repair_job;
        var data = $.extend({ job_id: id, act: act }, extra || {});
        if ($('#mParts').attr('data-edit') === '1' && ['charges', 'estimate', 'start', 'ready'].indexOf(act) >= 0) {
            var c = collectCharges();
            data.labour = c.labour;
            data.parts = c.parts;
        }
        $.ajax({
            type: 'POST', dataType: 'json', url: BASE + 'Repair/Repairaction', data: data,
            success: function(res) {
                action(res.action);
                if (res.status == 1) { table.ajax.reload(null, false); openJob(id); }
            },
            error: function() { Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong. Please try again later.' }); }
        });
    }

    $('#repairbody')
        .on('input', '.pt-qty, .pt-price, #mLabour', recalcCharges)
        .on('click', '#btnAddPart', function() { $('#mParts tbody').append(partRow(null, true)); })
        .on('click', '.pt-del', function() { $(this).closest('tr').remove(); recalcCharges(); })
        .on('click', '[data-act]', function() {
            var a = $(this).data('act');
            if (a === 'print') {
                var id = cur.job.idtbl_repair_job;
                $('#repairmodal').one('hidden.bs.modal', function() { showPdf(id); }).modal('hide');
            } else if (a === 'cancel') {
                Swal.fire({ title: 'Cancel this job?', input: 'text', inputPlaceholder: 'Reason*', showCancelButton: true,
                            confirmButtonText: 'Cancel job', inputValidator: function(v) { return !v && 'Please enter the reason'; } })
                    .then(function(r) { if (!r.dismiss) doAct('cancel', { remark: r.value }); });
            } else if (a === 'reject') {
                Swal.fire({ title: 'The customer declined the estimate?', showCancelButton: true, confirmButtonText: 'Yes, close the job' })
                    .then(function(r) { if (!r.dismiss) doAct('reject'); });
            } else if (a === 'deliver') {
                doAct('deliver', { paytype: $('#mPaytype').val(), paid: $('#mPaid').val(), collector: $('#mCollector').val() });
            } else if (a === 'start' || a === 'approve') {
                doAct(a, { technician: $('#mTech').val() });
            } else {
                doAct(a);
            }
        });
});
</script>
<?php include "include/footer.php"; ?>