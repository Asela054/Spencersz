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
                        <h1 class="page-header-title font-weight-light">
                            <div class="page-header-icon"><i class="fas fa-barcode"></i></div>
                            <span>Serial Number Movement</span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2">

                <!-- Summary cards (filled by JS) -->
                <div class="row" id="summaryrow"></div>

                <!-- Filters -->
                <div class="card sm-filter mb-3">
                    <div class="card-body py-3">
                        <div class="form-row align-items-end">
                            <div class="col-6 col-md-2 mb-2">
                                <label class="small font-weight-bold">From</label>
                                <input type="date" id="f_from" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 col-md-2 mb-2">
                                <label class="small font-weight-bold">To</label>
                                <input type="date" id="f_to" class="form-control form-control-sm">
                            </div>
                            <div class="col-12 col-md-2 mb-2">
                                <label class="small font-weight-bold">Movement Type</label>
                                <select id="f_type" class="form-control form-control-sm">
                                    <option value="">All</option>
                                    <option value="1">Received (GRN)</option>
                                    <option value="2">Issued / Sold</option>
                                    <option value="3">Returned by Customer</option>
                                    <option value="4">Returned to Supplier</option>
                                    <option value="5">Damaged</option>
                                    <option value="6">Transfer</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3 mb-2">
                                <label class="small font-weight-bold">Product</label>
                                <select id="f_product" class="form-control form-control-sm">
                                    <option value="">All Products</option>
                                    <?php foreach($productlist as $rowproduct){ ?>
                                    <option value="<?php echo $rowproduct->idtbl_product ?>">
                                        <?php echo $rowproduct->product_name . ($rowproduct->product_code ? ' / ' . $rowproduct->product_code : '') ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-3 mb-2">
                                <label class="small font-weight-bold">Serial No</label>
                                <input type="text" id="f_serial" class="form-control form-control-sm" placeholder="Search serial number">
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="button" id="btnreset" class="btn btn-light btn-sm border px-3 mr-1"><i class="fas fa-undo mr-1"></i>Reset</button>
                            <button type="button" id="btnsearch" class="btn btn-primary btn-sm px-4"><i class="fas fa-search mr-1"></i>Search</button>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="card sm-card">
                    <div class="card-body p-0 p-2">
                        <div class="scrollbar pb-3" id="style-2">
                            <table class="table table-bordered table-striped table-sm nowrap w-100" id="tblmovement">
                                <thead>
                                    <tr>
                                        <th>Ref</th>
                                        <th>Date / Time</th>
                                        <th>Serial No</th>
                                        <th>Product</th>
                                        <th>Movement</th>
                                        <th>Status Change</th>
                                        <th>Document</th>
                                        <th>User</th>
                                        <th>Remark</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
		</main>
		<?php include "include/footerbar.php"; ?>
    </div>
</div>

<!-- Serial timeline modal -->
<div class="modal fade" id="timelinemodal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-route mr-2 text-primary"></i>Serial Number History</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="tlbody"></div>
        </div>
    </div>
</div>

<?php include "include/footerscripts.php"; ?>
<script>
    $(document).ready(function() {
        var movementtable=null;

        var MOVE = {
            1: { label: 'Received (GRN)',       short: 'Received',        color: '#28a745', icon: 'fa-arrow-circle-down' },
            2: { label: 'Issued / Sold',        short: 'Sold',            color: '#007bff', icon: 'fa-shopping-cart' },
            3: { label: 'Returned by Customer', short: 'Customer Return', color: '#17a2b8', icon: 'fa-undo' },
            4: { label: 'Returned to Supplier', short: 'Supplier Return', color: '#fd7e14', icon: 'fa-truck' },
            5: { label: 'Damaged',              short: 'Damaged',         color: '#dc3545', icon: 'fa-exclamation-triangle' },
            6: { label: 'Transfer',             short: 'Transfer',        color: '#6f42c1', icon: 'fa-exchange-alt' }
        };

        var STATUS = {
            1: 'In Stock', 2: 'Sold', 3: 'Returned by Customer',
            4: 'Returned to Supplier', 5: 'Damaged', 6: 'Transferred'
        };

        var DOCS = {
            GRN:        { label: 'GRN',        color: '#28a745' },
            INVOICE:    { label: 'Invoice',    color: '#007bff' },
            GRN_RETURN: { label: 'GRN Return', color: '#fd7e14' },
            TRANSFER:   { label: 'Transfer',   color: '#6f42c1' }
        };

        function esc(s){
            return $('<div>').text(s === null || s === undefined ? '' : s).html();
        }
        function money(v){
            return parseFloat(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        function splitDate(dt){
            var p = (dt || '').split(' ');
            return { d: p[0] || '', t: (p[1] || '').substring(0, 5) };
        }
        function moveBadge(t){
            var m = MOVE[t] || { short: 'Unknown', color: '#6c757d', icon: 'fa-question' };
            return '<span class="sm-badge" style="background:' + m.color + '"><i class="fas ' + m.icon + ' mr-1"></i>' + m.short + '</span>';
        }
        function statusPill(s){
            if (s === null || s === undefined || s === '') {
                return '<span class="sm-pill sm-pill-new">New</span>';
            }
            var c = MOVE[s] ? MOVE[s].color : '#6c757d';
            var label = STATUS[s] || ('Status ' + s);
            return '<span class="sm-pill" style="color:' + c + ';border-color:' + c + ';background:' + c + '1a">' + esc(label) + '</span>';
        }
        function statusChange(r){
            return statusPill(r.from_status) + '<i class="fas fa-long-arrow-alt-right sm-arrow"></i>' + statusPill(r.to_status);
        }
        function docHtml(r){
            var d = DOCS[r.doc_type] || { label: r.doc_type, color: '#6c757d' };
            var no = (r.doc_type === 'GRN' && r.grn_no) ? r.grn_no : '#' + r.doc_id;
            return '<span class="sm-doc" style="color:' + d.color + ';border-color:' + d.color + '">' + esc(d.label) + '</span> ' +
                   '<span class="small font-weight-bold">' + esc(no) + '</span>';
        }

        /* ---------------- summary cards ---------------- */
        function statCard(label, num, color, icon, type){
            var active = (type && String($('#f_type').val()) === String(type)) ? ' active' : '';
            return '<div class="col-6 col-md-3 mb-3"><div class="card sm-stat' + active + '" data-type="' + (type || '') + '" ' +
                   'style="background:linear-gradient(135deg,' + color + ',' + color + 'bb)">' +
                   '<div class="card-body py-3"><div class="num">' + Number(num).toLocaleString() + '</div>' +
                   '<div class="lbl">' + label + '</div><i class="fas ' + icon + ' ico"></i></div></div></div>';
        }
        function renderSummary(s){
            var h = statCard('Total Movements', s.total, '#343a40', 'fa-list-alt', '') +
                    statCard('Unique Serials', s.serials, '#4e54c8', 'fa-barcode', '');
            $.each(MOVE, function(k, m){
                h += statCard(m.label, s.types[k] || 0, m.color, m.icon, k);
            });
            $('#summaryrow').html(h);
        }
        function filterdata(){
            return {
                from: $('#f_from').val(),
                to: $('#f_to').val(),
                type: $('#f_type').val(),
                product: $('#f_product').val(),
                serial: $('#f_serial').val()
            };
        }
        function loadsummary(){
            $.ajax({
                type: "POST",
                data: filterdata(),
                url: '<?php echo base_url() ?>Serialmovement/Getsummary',
                success: function(result) { //alert(result);
                    renderSummary(JSON.parse(result));
                }
            });
        }
        function loaddata(){
            movementtable.ajax.reload();
            loadsummary();
        }

        /* ---------------- movement table ---------------- */
        movementtable=$('#tblmovement').DataTable({
            "destroy": true,
            "processing": true,
            "serverSide": true,
            dom: "<'row'<'col-sm-5'B><'col-sm-2'l><'col-sm-5'f>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            responsive: true,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'All'],
            ],
            "buttons": [
                { extend: 'csv', className: 'btn btn-success btn-sm', title: 'Serial Number Movement', text: '<i class="fas fa-file-csv mr-2"></i> CSV', },
                { extend: 'pdf', className: 'btn btn-danger btn-sm', title: 'Serial Number Movement', orientation: 'landscape', text: '<i class="fas fa-file-pdf mr-2"></i> PDF', },
                { 
                    extend: 'print', 
                    title: 'Serial Number Movement',
                    className: 'btn btn-primary btn-sm', 
                    text: '<i class="fas fa-print mr-2"></i> Print',
                    customize: function ( win ) {
                        $(win.document.body).find( 'table' )
                            .addClass( 'compact' )
                            .css( 'font-size', 'inherit' );
                    }, 
                },
            ],
            ajax: {
                url: "<?php echo base_url() ?>scripts/serialmovementlist.php",
                type: "POST", // you can use GET
                data: function(d) {
                    d.from = $('#f_from').val();
                    d.to = $('#f_to').val();
                    d.type = $('#f_type').val();
                    d.product = $('#f_product').val();
                    d.serial = $('#f_serial').val();
                }
            },
            "order": [[ 0, "desc" ]],
            "columns": [
                {
                    "data": "idtbl_serial_movement",
                    "render": function(data) {
                        return '<span class="text-muted">#' + data + '</span>';
                    }
                },
                {
                    "data": "insertdatetime",
                    "render": function(data) {
                        var x = splitDate(data);
                        return '<div class="font-weight-bold">' + esc(x.d) + '</div><small class="text-muted">' + esc(x.t) + '</small>';
                    }
                },
                {
                    "data": "serialno",
                    "render": function(data, type, full) {
                        return '<span class="sm-serial btnserial" data-id="' + full['idtbl_product_serial'] + '" title="View history"><i class="fas fa-barcode mr-1"></i>' + esc(data) + '</span>';
                    }
                },
                {
                    "data": "product_name",
                    "render": function(data, type, full) {
                        return '<div class="font-weight-bold">' + esc(data) + '</div>' + (full['product_code'] ? '<small class="text-muted">' + esc(full['product_code']) + '</small>' : '');
                    }
                },
                {
                    "data": "movement_type",
                    "render": function(data) {
                        return moveBadge(data);
                    }
                },
                {
                    "data": null,
                    "orderable": false,
                    "render": function(data, type, full) {
                        return statusChange(full);
                    }
                },
                {
                    "data": "doc_type",
                    "render": function(data, type, full) {
                        return docHtml(full);
                    }
                },
                {
                    "data": "name",
                    "defaultContent": "",
                    "render": function(data) {
                        return data ? '<i class="far fa-user-circle text-muted mr-1"></i>' + esc(data) : '<span class="text-muted">&mdash;</span>';
                    }
                },
                {
                    "data": "remark",
                    "defaultContent": "",
                    "render": function(data) {
                        return '<span class="small">' + esc(data) + '</span>';
                    }
                }
            ]
        });

        /* ---------------- timeline modal ---------------- */
        function renderTimeline(res){
            var s = res.serial;
            if (!s) {
                $('#tlbody').html('<div class="text-center text-muted py-5">Serial number not found.</div>');
                return;
            }

            var head = '<div class="sm-head"><div class="d-flex justify-content-between align-items-start flex-wrap">' +
                '<div><div class="sn"><i class="fas fa-barcode mr-2"></i>' + esc(s.serialno) + '</div>' +
                '<div class="mt-1">' + esc(s.product_name) + (s.product_code ? ' <span style="opacity:.7">/ ' + esc(s.product_code) + '</span>' : '') + '</div></div>' +
                '<div class="mt-1">' + statusPill(s.stock_status).replace('background:', 'background:#fff;border-color:transparent; color:#000;') + '</div></div>' +
                '<div class="row meta mt-3">' +
                '<div class="col-6 col-md-3"><small>GRN No</small><span>' + esc(s.grn_no || '-') + '</span></div>' +
                '<div class="col-6 col-md-3"><small>Cost Price</small><span>Rs. ' + money(s.costunitprice) + '</span></div>' +
                '<div class="col-6 col-md-3"><small>Warranty End</small><span>' + esc(s.warranty_end && s.warranty_end !== '0000-00-00' ? s.warranty_end : '-') + '</span></div>' +
                '<div class="col-6 col-md-3"><small>Total Movements</small><span>' + res.events.length + '</span></div>' +
                '</div></div>';

            var tl = '<div class="sm-timeline">';
            if (res.events.length === 0) {
                tl = '<div class="text-center text-muted py-4">No movements recorded for this serial.</div>';
            } else {
                $.each(res.events, function(i, e) {
                    var m = MOVE[e.movement_type] || { label: 'Movement', color: '#6c757d', icon: 'fa-circle' };
                    var x = splitDate(e.insertdatetime);
                    var no = (e.doc_type === 'GRN' && e.grn_no) ? e.grn_no : '#' + e.doc_id;
                    tl += '<div class="sm-tl-item">' +
                        '<div class="sm-tl-dot" style="background:' + m.color + '"><i class="fas ' + m.icon + '"></i></div>' +
                        '<div class="sm-tl-card" style="border-left-color:' + m.color + '">' +
                        '<div class="d-flex justify-content-between flex-wrap"><span class="ttl" style="color:' + m.color + '">' + m.label + '</span>' +
                        '<span class="when"><i class="far fa-clock mr-1"></i>' + esc(x.d) + ' ' + esc(x.t) + '</span></div>' +
                        '<div class="row-meta">' +
                        '<span>' + statusChange(e) + '</span>' +
                        '<span><i class="far fa-file-alt mr-1 text-muted"></i>' + esc((DOCS[e.doc_type] ? DOCS[e.doc_type].label : e.doc_type)) + ' ' + esc(no) + '</span>' +
                        (e.username ? '<span><i class="far fa-user-circle mr-1 text-muted"></i>' + esc(e.username) + '</span>' : '') +
                        '</div>' +
                        (e.remark ? '<div class="small text-muted mt-1"><i class="far fa-comment-dots mr-1"></i>' + esc(e.remark) + '</div>' : '') +
                        '</div></div>';
                });
                tl += '</div>';
            }

            $('#tlbody').html(head + tl);
        }

        function opentimeline(id){
            $('#tlbody').html('<div class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
            $('#timelinemodal').modal('show');

            $.ajax({
                type: "POST",
                data: {
                    serialID: id
                },
                url: '<?php echo base_url() ?>Serialmovement/Getserialtimeline',
                success: function(result) { //alert(result);
                    renderTimeline(JSON.parse(result));
                },
                error: function() {
                    $('#tlbody').html('<div class="text-center text-danger py-5">Unable to load the history.</div>');
                }
            });
        }

        /* ---------------- events ---------------- */
        if($.fn.select2){ $('#f_product').select2({ width: '100%' }); }

        $('#btnsearch').on('click', loaddata);

        $('#f_serial').on('keypress', function(e) {
            if (e.which === 13) { loaddata(); }
        });

        $('#btnreset').on('click', function() {
            $('#f_from, #f_to, #f_type, #f_serial').val('');
            $('#f_product').val('').trigger('change');
            loaddata();
        });

        // click a summary card to filter by that movement type (click again to clear)
        $('#summaryrow').on('click', '.sm-stat', function() {
            var t = String($(this).attr('data-type'));
            if (!t) { return; }
            $('#f_type').val(String($('#f_type').val()) === t ? '' : t);
            loaddata();
        });

        $('#f_type').on('change', loaddata);

        // click a serial number to open its timeline
        $('#tblmovement tbody').on('click', '.btnserial', function() {
            opentimeline($(this).data('id'));
        });

        loadsummary();
    });
</script>
<?php include "include/footer.php"; ?>