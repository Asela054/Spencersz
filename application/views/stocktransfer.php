<?php 
include "include/header.php";  
include "include/topnavbar.php"; 
?>
<style id="notecss">
    .tn{font-family:"Segoe UI",Arial,sans-serif;color:#212529;font-size:13px;}
    .tn-head{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #212529;padding-bottom:10px;margin-bottom:14px;}
    .tn-co{font-size:20px;font-weight:700;}
    .tn-title{font-size:18px;font-weight:700;text-align:right;}
    .tn-grid{display:flex;gap:14px;margin-bottom:14px;}
    .tn-box{flex:1;border:1px solid #ced4da;border-radius:4px;padding:9px 12px;}
    .tn-box small{display:block;color:#6c757d;}
    .tn table{width:100%;border-collapse:collapse;margin-bottom:14px;}
    .tn th,.tn td{border:1px solid #ced4da;padding:6px 8px;vertical-align:top;text-align:left;}
    .tn th{background:#f1f3f5;}
    .tn-sign{display:flex;gap:24px;margin-top:50px;}
    .tn-sign div{flex:1;border-top:1px solid #212529;padding-top:4px;text-align:center;font-size:12px;}
</style>
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
                            <div class="page-header-icon"><i class="fas fa-exchange-alt"></i></div>
                            <span>Stock Transfer</span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2">

                <!-- Summary cards -->
                <div class="row" id="summaryrow"></div>

                <!-- Filters -->
                <div class="card mb-3">
                    <div class="card-body py-3">
                        <div class="form-row align-items-end">
                            <div class="col-12 col-md-3 mb-2">
                                <label class="small font-weight-bold">Transfer Type</label>
                                <select id="f_type" class="form-control form-control-sm">
                                    <option value="">All</option>
                                    <option value="1">Showroom to Showroom</option>
                                    <option value="2">Warehouse to Warehouse</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3 mb-2">
                                <label class="small font-weight-bold">Status</label>
                                <select id="f_status" class="form-control form-control-sm">
                                    <option value="">All</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 mb-2 text-md-right">
                                <button type="button" id="btnreset" class="btn btn-light btn-sm border px-3 mr-1"><i class="fas fa-undo mr-1"></i>Reset</button>
                                <button type="button" id="btnnew" class="btn btn-primary btn-sm px-4"><i class="fas fa-plus mr-1"></i>New Transfer</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="card">
                    <div class="card-body p-0 p-2">
                        <div class="scrollbar pb-3" id="style-2">
                            <table class="table table-bordered table-striped table-sm nowrap w-100" id="tbltransfer">
                                <thead>
                                    <tr>
                                        <th>Transfer No</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>From / To</th>
                                        <th>Vehicle</th>
                                        <th class="text-right">Qty</th>
                                        <th>Status</th>
                                        <th class="text-right">Actions</th>
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

<!-- New transfer modal -->
<div class="modal fade" id="newmodal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-exchange-alt mr-2 text-primary"></i>New Stock Transfer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="small font-weight-bold d-block">Transfer Type*</label>
                    <label class="st-typeopt"><input type="radio" name="ntype" value="1" checked><i class="fas fa-store mr-1 text-muted"></i>Showroom to Showroom</label>
                    <label class="st-typeopt"><input type="radio" name="ntype" value="2"><i class="fas fa-warehouse mr-1 text-muted"></i>Warehouse to Warehouse</label>
                </div>
                <div class="form-row">
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold">From*</label>
                        <select id="n_from" class="form-control form-control-sm"></select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold">To*</label>
                        <select id="n_to" class="form-control form-control-sm"></select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold">Date*</label>
                        <input type="date" id="n_date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold">Vehicle No*</label>
                        <input type="text" id="n_vehicle" class="form-control form-control-sm" placeholder="e.g. WP CAB-4521">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold">Driver*</label>
                        <input type="text" id="n_driver" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold">Driver Contact</label>
                        <input type="text" id="n_contact" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-9 mb-2">
                        <label class="small font-weight-bold">Remark</label>
                        <input type="text" id="n_remark" class="form-control form-control-sm">
                    </div>
                </div>

                <div class="border rounded p-3 mt-2">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Product*</label>
                            <select id="n_product" class="form-control form-control-sm"></select>
                            <div class="small text-muted mt-2" id="n_avail"></div>
                            <button type="button" id="btnadditem" class="btn btn-success btn-sm mt-3 px-3"><i class="fas fa-plus mr-1"></i>Add to transfer</button>
                        </div>
                        <div class="col-md-8 mb-2">
                            <div class="d-flex justify-content-between">
                                <label class="small font-weight-bold">Serial numbers*</label>
                                <a href="#" id="n_selall" class="small">Select all</a>
                            </div>
                            <div class="st-picklist" id="n_serials"></div>
                        </div>
                    </div>
                    <div class="table-responsive mt-2">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="bg-light">
                                <tr><th>Product</th><th class="text-right" style="width:70px">Qty</th><th>Serial numbers</th><th style="width:50px"></th></tr>
                            </thead>
                            <tbody id="n_items"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="mr-auto small text-muted"><i class="fas fa-info-circle mr-1"></i>A transfer note is created automatically when the transfer is saved.</div>
                <button type="button" class="btn btn-light btn-sm border px-3" data-dismiss="modal">Cancel</button>
                <button type="button" id="btnsave" class="btn btn-primary btn-sm px-4"><i class="far fa-save mr-1"></i>Save Transfer</button>
            </div>
        </div>
    </div>
</div>

<!-- Tracking modal -->
<div class="modal fade" id="trackmodal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-route mr-2 text-primary"></i><span id="trackTitle"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="trackbody"></div>
            <div class="modal-footer">
                <button type="button" id="btntracknote" class="btn btn-light btn-sm border px-3 mr-auto"><i class="fas fa-print mr-1"></i>Transfer Note</button>
                <button type="button" class="btn btn-primary btn-sm px-4" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Transfer note modal -->
<div class="modal fade" id="notemodal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt mr-2 text-primary"></i>Transfer Note</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="notebody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm border px-3" data-dismiss="modal">Close</button>
                <button type="button" id="btnprintnote" class="btn btn-primary btn-sm px-4"><i class="fas fa-print mr-1"></i>Print</button>
            </div>
        </div>
    </div>
</div>

<?php include "include/footerscripts.php"; ?>
<script>
    $(document).ready(function() {
        /* ============ sample data (preview only, nothing is saved) ============ */
        var USER = 'Super Administrator';

        var BRANCHES = [
            { id: 1, type: 1, name: 'Colombo Showroom' },
            { id: 2, type: 1, name: 'Kandy Showroom' },
            { id: 3, type: 1, name: 'Galle Showroom' },
            { id: 4, type: 2, name: 'Central Warehouse' },
            { id: 5, type: 2, name: 'Kandy Warehouse' },
            { id: 6, type: 2, name: 'Southern Warehouse' }
        ];

        var PRODUCTS = [
            { id: 1, code: 'VAC001', name: 'TCL Cordless Vacuum Cleaner', serials: ['9366856396','2572373278','528247527','7713094425','1048836291','6620175534','3098417752','8841265093'] },
            { id: 2, code: 'TV055',  name: '55" Smart LED TV', serials: ['TV55-240801','TV55-240802','TV55-240803','TV55-240804','TV55-240805','TV55-240806','TV55-240807','TV55-240808'] },
            { id: 3, code: 'WM070',  name: 'Front Load Washing Machine 7kg', serials: ['WM7-110231','WM7-110232','WM7-110233','WM7-110234','WM7-110235','WM7-110236'] },
            { id: 4, code: 'RF250',  name: 'Inverter Refrigerator 250L', serials: ['RF25-70011','RF25-70012','RF25-70013','RF25-70014','RF25-70015','RF25-70016'] },
            { id: 5, code: 'AC120',  name: 'Split Air Conditioner 12000 BTU', serials: ['AC12-30551','AC12-30552','AC12-30553','AC12-30554','AC12-30555'] }
        ];

        var STEPS = [
            { key: 1, short: 'Created',        card: 'Created',    label: 'Transfer Created',         icon: 'fa-file-alt',        color: '#6c757d', by: 'from' },
            { key: 2, short: 'Loaded',         card: 'Loaded',     label: 'Loaded to Lorry',          icon: 'fa-dolly',           color: '#fd7e14', by: 'from', action: 'Mark as Loaded',     effect: 'Goods are loaded onto the lorry at the sending location.' },
            { key: 3, short: 'Dispatched',     card: 'In Transit', label: 'Dispatched from Location', icon: 'fa-truck-moving',    color: '#007bff', by: 'from', action: 'Approve & Dispatch', effect: 'The sending location approves the transfer and the lorry leaves. Stock is reduced at the sending location and the serial numbers move to In Transit.' },
            { key: 4, short: 'Lorry Arrived',  card: 'Arrived',    label: 'Lorry Arrived',            icon: 'fa-map-marker-alt',  color: '#17a2b8', by: 'to',   action: 'Mark Lorry Arrived', effect: 'The receiving location confirms the lorry has arrived.' },
            { key: 5, short: 'Unloaded',       card: 'Unloaded',   label: 'Unloaded',                 icon: 'fa-dolly-flatbed',   color: '#6f42c1', by: 'to',   action: 'Mark as Unloaded',   effect: 'Goods are unloaded and checked at the receiving location.' },
            { key: 6, short: 'Added to Stock', card: 'Completed',  label: 'Added to Stock',           icon: 'fa-check-circle',    color: '#28a745', by: 'to',   action: 'Add to Stock',       effect: 'A new stock record is created for the receiving location and the serial numbers now belong to it.' }
        ];

        function seedlog(day, status) {
            var times = ['08:30', '09:10', '10:05', '13:40', '14:15', '15:00'];
            var log = [];
            for (var i = 1; i <= status; i++) { log.push({ step: i, at: day + ' ' + times[i - 1], user: USER }); }
            return log;
        }

        var transfers = [
            { id: 'TRF-0001', date: '2026-10-05', type: 2, from: 4, to: 5, vehicle: 'WP LA-7788',  driver: 'Nuwan Silva',      contact: '0771234501', remark: 'Festival season stock', items: [{ pid: 2, serials: ['TV55-240801','TV55-240802','TV55-240803'] }], status: 6, log: seedlog('2026-10-05', 6) },
            { id: 'TRF-0002', date: '2026-10-08', type: 1, from: 1, to: 3, vehicle: 'SP KB-1234',  driver: 'Ruwan Jayasinghe', contact: '0719876502', remark: '', items: [{ pid: 1, serials: ['9366856396','2572373278'] }], status: 4, log: seedlog('2026-10-08', 4) },
            { id: 'TRF-0003', date: '2026-10-09', type: 1, from: 2, to: 1, vehicle: 'CP BCD-4410', driver: 'Saman Kumara',     contact: '0752345603', remark: 'Display units', items: [{ pid: 3, serials: ['WM7-110231','WM7-110232'] }], status: 3, log: seedlog('2026-10-09', 3) },
            { id: 'TRF-0004', date: '2026-10-09', type: 2, from: 4, to: 6, vehicle: 'WP CAB-4521', driver: 'Kasun Perera',     contact: '0763456704', remark: '', items: [{ pid: 4, serials: ['RF25-70011','RF25-70012','RF25-70013','RF25-70014'] }, { pid: 5, serials: ['AC12-30551','AC12-30552'] }], status: 2, log: seedlog('2026-10-09', 2) },
            { id: 'TRF-0005', date: '2026-10-07', type: 2, from: 5, to: 4, vehicle: 'CP LB-9087',  driver: 'Dinesh Fernando',  contact: '0774567805', remark: 'Return of excess stock', items: [{ pid: 2, serials: ['TV55-240804','TV55-240805'] }], status: 5, log: seedlog('2026-10-07', 5) },
            { id: 'TRF-0006', date: '2026-10-09', type: 1, from: 3, to: 1, vehicle: 'SP CAA-2210', driver: 'Chamara Silva',    contact: '0705678906', remark: '', items: [{ pid: 1, serials: ['7713094425'] }], status: 1, log: seedlog('2026-10-09', 1) }
        ];

        var table = null;
        var draft = [];
        var curid = '';
        var curnote = '';

        /* ============ helpers ============ */
        function esc(s) { return $('<div>').text(s === null || s === undefined ? '' : s).html(); }
        function pad(n) { return n < 10 ? '0' + n : '' + n; }
        function nowstr() { var d = new Date(); return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes()); }
        function find(id) { for (var i = 0; i < transfers.length; i++) { if (transfers[i].id === id) { return transfers[i]; } } return null; }
        function branch(id) { for (var i = 0; i < BRANCHES.length; i++) { if (BRANCHES[i].id === parseInt(id, 10)) { return BRANCHES[i]; } } return { name: '-', type: 0 }; }
        function product(id) { for (var i = 0; i < PRODUCTS.length; i++) { if (PRODUCTS[i].id === parseInt(id, 10)) { return PRODUCTS[i]; } } return { code: '', name: '-', serials: [] }; }
        function qty(t) { var n = 0; $.each(t.items, function(i, it) { n += it.serials.length; }); return n; }
        function tnno(t) { return 'TN-' + t.id.split('-')[1]; }
        function logfor(t, key) { for (var i = 0; i < t.log.length; i++) { if (t.log[i].step === key) { return t.log[i]; } } return null; }

        function typeTag(type) {
            var c = type === 1 ? '#6610f2' : '#795548';
            return '<span class="st-type" style="color:' + c + ';border-color:' + c + '"><i class="fas ' + (type === 1 ? 'fa-store' : 'fa-warehouse') + ' mr-1"></i>' + (type === 1 ? 'Showroom' : 'Warehouse') + '</span>';
        }
        function statusBadge(n) {
            var s = STEPS[n - 1];
            return '<span class="st-badge" style="background:' + s.color + '"><i class="fas ' + s.icon + ' mr-1"></i>' + s.short + '</span>';
        }
        function mini(n) {
            var c = STEPS[n - 1].color, h = '<div class="st-mini">';
            for (var i = 1; i <= 6; i++) { h += '<span' + (i <= n ? ' style="background:' + c + '"' : '') + '></span>'; }
            return h + '</div>';
        }
        function notify(msg, kind) {
            var icon = kind === 'err' ? 'fa-times-circle text-danger' : (kind === 'warn' ? 'fa-exclamation-triangle text-warning' : 'fa-check-circle text-success');
            var $n = $('<div class="st-toast ' + (kind || '') + '"><i class="fas ' + icon + ' mr-2"></i>' + esc(msg) + '</div>').appendTo('body');
            setTimeout(function() { $n.addClass('show'); }, 10);
            setTimeout(function() { $n.removeClass('show'); setTimeout(function() { $n.remove(); }, 300); }, 3200);
        }
        function lockedSerials() {
            var used = [];
            $.each(transfers, function(i, t) { if (t.status < 6) { $.each(t.items, function(k, it) { used = used.concat(it.serials); }); } });
            $.each(draft, function(i, it) { used = used.concat(it.serials); });
            return used;
        }

        /* ============ summary cards ============ */
        function card(label, num, color, icon, key) {
            var active = (key !== '' && String($('#f_status').val()) === String(key)) ? ' active' : '';
            return '<div class="col-6 col-md-4 col-xl mb-3"><div class="card st-stat' + active + '" data-status="' + key + '" style="background:linear-gradient(135deg,' + color + ',' + color + 'bb)">' +
                   '<div class="card-body py-3"><div class="num">' + num + '</div><div class="lbl">' + label + '</div><i class="fas ' + icon + ' ico"></i></div></div></div>';
        }
        function renderSummary() {
            var counts = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0 };
            $.each(transfers, function(i, t) { counts[t.status]++; });
            var h = card('All transfers', transfers.length, '#343a40', 'fa-list-alt', '');
            $.each(STEPS, function(i, s) { h += card(s.card, counts[s.key], s.color, s.icon, s.key); });
            $('#summaryrow').html(h);
        }

        /* ============ list table ============ */
        function filtered() {
            var ty = $('#f_type').val(), st = $('#f_status').val();
            return $.grep(transfers, function(t) { return (!ty || String(t.type) === ty) && (!st || String(t.status) === st); });
        }
        function drawTable() { table.clear().rows.add(filtered()).draw(false); }
        function refresh() { renderSummary(); drawTable(); }

        $.each(STEPS, function(i, s) { $('#f_status').append('<option value="' + s.key + '">' + s.short + '</option>'); });

        table = $('#tbltransfer').DataTable({
            "destroy": true,
            data: [],
            dom: "<'row'<'col-sm-5'B><'col-sm-2'l><'col-sm-5'f>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            responsive: true,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'All'],
            ],
            "buttons": [
                { extend: 'csv', className: 'btn btn-success btn-sm', title: 'Stock Transfers', text: '<i class="fas fa-file-csv mr-2"></i> CSV', },
                { extend: 'pdf', className: 'btn btn-danger btn-sm', title: 'Stock Transfers', orientation: 'landscape', text: '<i class="fas fa-file-pdf mr-2"></i> PDF', },
                { 
                    extend: 'print', 
                    title: 'Stock Transfers',
                    className: 'btn btn-primary btn-sm', 
                    text: '<i class="fas fa-print mr-2"></i> Print',
                    customize: function ( win ) {
                        $(win.document.body).find( 'table' )
                            .addClass( 'compact' )
                            .css( 'font-size', 'inherit' );
                    }, 
                },
            ],
            "order": [[ 0, "desc" ]],
            "columns": [
                {
                    "data": "id",
                    "render": function(d, t) {
                        return t === 'display' ? '<a href="#" class="btntrack font-weight-bold" data-id="' + d + '">' + d + '</a>' : d;
                    }
                },
                { "data": "date" },
                {
                    "data": "type",
                    "render": function(d, t) {
                        return t === 'display' ? typeTag(d) : (d === 1 ? 'Showroom' : 'Warehouse');
                    }
                },
                {
                    "data": null,
                    "orderable": false,
                    "render": function(d, t, r) {
                        var f = branch(r.from).name, to = branch(r.to).name;
                        if (t !== 'display') { return f + ' to ' + to; }
                        return '<span class="font-weight-bold">' + esc(f) + '</span><i class="fas fa-long-arrow-alt-right mx-2 text-muted"></i><span class="font-weight-bold">' + esc(to) + '</span>';
                    }
                },
                {
                    "data": null,
                    "orderable": false,
                    "render": function(d, t, r) {
                        if (t !== 'display') { return r.vehicle + ' / ' + r.driver; }
                        return '<div class="font-weight-bold"><i class="fas fa-truck mr-1 text-muted"></i>' + esc(r.vehicle) + '</div><small class="text-muted">' + esc(r.driver) + '</small>';
                    }
                },
                {
                    "data": null,
                    "className": 'text-right',
                    "orderable": false,
                    "render": function(d, t, r) { return qty(r); }
                },
                {
                    "data": "status",
                    "render": function(d, t) {
                        return t === 'display' ? statusBadge(d) + mini(d) : STEPS[d - 1].short;
                    }
                },
                {
                    "data": null,
                    "className": 'text-right',
                    "orderable": false,
                    "render": function(d, t, r) {
                        var b = '<button class="btn btn-primary btn-sm btntrack mr-1" data-id="' + r.id + '" data-toggle="tooltip" title="Track"><i class="fas fa-route"></i></button>';
                        b += '<button class="btn btn-light btn-sm border btnnote mr-1" data-id="' + r.id + '" data-toggle="tooltip" title="Transfer note"><i class="fas fa-print"></i></button>';
                        if (r.status < 6) {
                            var n = STEPS[r.status];
                            b += '<button class="btn btn-sm btnnext text-white" style="background:' + n.color + '" data-id="' + r.id + '"><i class="fas ' + n.icon + ' mr-1"></i>' + n.action + '</button>';
                        } else {
                            b += '<span class="text-success small font-weight-bold"><i class="fas fa-check mr-1"></i>Completed</span>';
                        }
                        return b;
                    }
                }
            ],
            drawCallback: function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });

        /* ============ tracking modal ============ */
        function logText(t, step) {
            var f = branch(t.from).name, to = branch(t.to).name, q = qty(t);
            if (step === 1) { return 'Transfer created and transfer note ' + tnno(t) + ' generated.'; }
            if (step === 2) { return 'Goods loaded onto lorry ' + t.vehicle + ' at ' + f + '.'; }
            if (step === 3) { return 'Approved by ' + f + '. Stock reduced by ' + q + ' and serial numbers set to In Transit.'; }
            if (step === 4) { return 'Lorry arrived at ' + to + '.'; }
            if (step === 5) { return 'Goods unloaded and checked at ' + to + '.'; }
            return 'Added to stock at ' + to + '. Stock increased by ' + q + ' and serial numbers now belong to ' + to + '.';
        }
        function stepper(t) {
            var h = '<div class="st-stepper">';
            $.each(STEPS, function(i, s) {
                var done = t.status >= s.key, lg = logfor(t, s.key);
                h += '<div class="st-step' + (done ? ' done' : '') + (t.status === s.key ? ' current' : '') + '">' +
                     '<div class="st-dot" style="' + (done ? 'background:' + s.color + ';border-color:' + s.color : '') + '"><i class="fas ' + s.icon + '"></i></div>' +
                     '<div class="st-lbl">' + s.label + '</div>' +
                     '<div class="st-when">' + (lg ? esc(lg.at.substr(5)) : 'Pending') + '</div></div>';
            });
            return h + '</div>';
        }
        function actionPanel(t) {
            if (t.status >= 6) {
                return '<div class="alert alert-success mb-3"><i class="fas fa-check-circle mr-2"></i>This transfer is complete. The goods are now in stock at <b>' + esc(branch(t.to).name) + '</b>.</div>';
            }
            var n = STEPS[t.status];
            var who = n.by === 'from' ? branch(t.from).name : branch(t.to).name;
            return '<div class="st-next mb-3 d-flex justify-content-between align-items-center flex-wrap">' +
                   '<div class="mr-3"><div class="font-weight-bold"><i class="fas ' + n.icon + ' mr-2" style="color:' + n.color + '"></i>Next step: ' + n.label + '</div>' +
                   '<div class="small text-muted">' + n.effect + '</div>' +
                   '<div class="small mt-1"><i class="fas fa-user-check mr-1 text-muted"></i>Done by: <b>' + esc(who) + '</b></div></div>' +
                   '<button type="button" class="btn btn-sm text-white px-4 btnnext" style="background:' + n.color + '" data-id="' + t.id + '"><i class="fas ' + n.icon + ' mr-1"></i>' + n.action + '</button></div>';
        }
        function serialState(t) {
            if (t.status < 3) { return { l: 'In stock at ' + branch(t.from).name, c: '#28a745' }; }
            if (t.status < 6) { return { l: 'In transit', c: '#007bff' }; }
            return { l: 'In stock at ' + branch(t.to).name, c: '#28a745' };
        }
        function itemsTab(t) {
            var st = serialState(t), h = '<div class="table-responsive"><table class="table table-bordered table-sm mb-0"><thead class="bg-light"><tr><th>Product</th><th class="text-right">Qty</th><th>Serial numbers</th><th>Serial status</th></tr></thead><tbody>';
            $.each(t.items, function(i, it) {
                var p = product(it.pid), chips = '';
                $.each(it.serials, function(k, s) { chips += '<span class="st-chip">' + esc(s) + '</span>'; });
                h += '<tr><td><div class="font-weight-bold">' + esc(p.name) + '</div><small class="text-muted">' + esc(p.code) + '</small></td><td class="text-right">' + it.serials.length + '</td><td>' + chips + '</td>' +
                     '<td><span class="st-badge" style="background:' + st.c + '">' + esc(st.l) + '</span></td></tr>';
            });
            return h + '</tbody></table></div>';
        }
        function stockTab(t) {
            var f = esc(branch(t.from).name), to = esc(branch(t.to).name);
            var h = '<div class="table-responsive"><table class="table table-bordered table-sm mb-2"><thead class="bg-light"><tr><th>Product</th><th class="text-right">Qty</th><th>' + f + ' (sending)</th><th>' + to + ' (receiving)</th></tr></thead><tbody>';
            $.each(t.items, function(i, it) {
                var p = product(it.pid), q = it.serials.length;
                h += '<tr><td>' + esc(p.name) + '</td><td class="text-right">' + q + '</td>' +
                     '<td>' + (t.status >= 3 ? '<span class="text-danger font-weight-bold">-' + q + '</span> deducted' : '<span class="text-muted">-' + q + ' on dispatch</span>') + '</td>' +
                     '<td>' + (t.status >= 6 ? '<span class="text-success font-weight-bold">+' + q + '</span> added to stock' : '<span class="text-muted">+' + q + ' when added to stock</span>') + '</td></tr>';
            });
            h += '</tbody></table></div>';
            h += '<div class="small text-muted"><i class="fas fa-barcode mr-1"></i>Every step also writes a serial number movement record (movement type: Transfer). When the goods are added to stock, each serial number moves to <b>' + to + '</b>.</div>';
            return h;
        }
        function logTab(t) {
            var h = '<div class="st-flow">';
            for (var i = t.log.length - 1; i >= 0; i--) {
                var e = t.log[i], s = STEPS[e.step - 1];
                h += '<div class="it" style="--c:' + s.color + '"><div class="font-weight-bold" style="color:' + s.color + '">' + s.label + '</div>' +
                     '<div class="small">' + esc(logText(t, e.step)) + '</div>' +
                     '<div class="small text-muted"><i class="far fa-clock mr-1"></i>' + esc(e.at) + '<i class="far fa-user-circle ml-3 mr-1"></i>' + esc(e.user) + '</div></div>';
            }
            return h + '</div>';
        }
        function renderTrack(id) {
            var t = find(id);
            if (!t) { return; }
            curid = id;
            $('#trackTitle').html(esc(t.id) + ' <span class="ml-2">' + statusBadge(t.status) + '</span>');
            var f = branch(t.from), to = branch(t.to);
            var h = '<div class="st-route">' +
                    '<div class="pt"><small>From</small><b>' + esc(f.name) + '</b><div>' + typeTag(f.type) + '</div></div>' +
                    '<div class="mid"><i class="fas fa-truck fa-lg"></i><div class="small mt-1">' + esc(t.vehicle) + ' &middot; ' + esc(t.driver) + (t.contact ? ' &middot; ' + esc(t.contact) : '') + '</div><div class="ln"></div></div>' +
                    '<div class="pt text-right"><small>To</small><b>' + esc(to.name) + '</b><div>' + typeTag(to.type) + '</div></div></div>' +
                    stepper(t) + actionPanel(t) +
                    '<ul class="nav nav-tabs" role="tablist">' +
                    '<li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tabitems" role="tab">Items & serial numbers</a></li>' +
                    '<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tabstock" role="tab">Stock impact</a></li>' +
                    '<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tablog" role="tab">Activity log</a></li></ul>' +
                    '<div class="tab-content pt-3">' +
                    '<div class="tab-pane fade show active" id="tabitems" role="tabpanel">' + itemsTab(t) + '</div>' +
                    '<div class="tab-pane fade" id="tabstock" role="tabpanel">' + stockTab(t) + '</div>' +
                    '<div class="tab-pane fade" id="tablog" role="tabpanel">' + logTab(t) + '</div></div>';
            $('#trackbody').html(h);
        }
        function opentrack(id) { renderTrack(id); $('#trackmodal').modal('show'); }

        function advance(id) {
            var t = find(id);
            if (!t || t.status >= 6) { return; }
            var n = STEPS[t.status];
            if (!confirm('Confirm: ' + n.action + ' for ' + t.id + '?')) { return; }
            t.status = n.key;
            t.log.push({ step: n.key, at: nowstr(), user: USER });
            refresh();
            if ($('#trackmodal').hasClass('show')) { renderTrack(t.id); }
            notify(t.id + ' updated: ' + n.label, 'ok');
        }

        /* ============ transfer note ============ */
        function noteHtml(t) {
            var f = branch(t.from), to = branch(t.to), rows = '', i = 0, total = 0;
            $.each(t.items, function(k, it) {
                var p = product(it.pid);
                i++; total += it.serials.length;
                rows += '<tr><td>' + i + '</td><td>' + esc(p.code) + '</td><td>' + esc(p.name) + '</td><td style="text-align:center">' + it.serials.length + '</td><td>' + esc(it.serials.join(', ')) + '</td></tr>';
            });
            return '<div class="tn">' +
                '<div class="tn-head"><div><div class="tn-co">Spencersz (Pvt) Ltd</div><div>Stock Transfer Note</div></div>' +
                '<div class="tn-title">' + esc(tnno(t)) + '<div style="font-size:12px;font-weight:400">Date: ' + esc(t.date) + '</div></div></div>' +
                '<div class="tn-grid">' +
                '<div class="tn-box"><small>From (' + (f.type === 1 ? 'Showroom' : 'Warehouse') + ')</small><b>' + esc(f.name) + '</b></div>' +
                '<div class="tn-box"><small>To (' + (to.type === 1 ? 'Showroom' : 'Warehouse') + ')</small><b>' + esc(to.name) + '</b></div>' +
                '<div class="tn-box"><small>Vehicle / Driver</small><b>' + esc(t.vehicle) + '</b><br>' + esc(t.driver) + (t.contact ? ' (' + esc(t.contact) + ')' : '') + '</div></div>' +
                '<table><thead><tr><th style="width:36px">#</th><th style="width:90px">Code</th><th>Product</th><th style="width:60px">Qty</th><th>Serial numbers</th></tr></thead><tbody>' + rows +
                '<tr><td colspan="3" style="text-align:right"><b>Total</b></td><td style="text-align:center"><b>' + total + '</b></td><td></td></tr></tbody></table>' +
                (t.remark ? '<div style="margin-bottom:10px"><b>Remark:</b> ' + esc(t.remark) + '</div>' : '') +
                '<div class="tn-sign"><div>Prepared by</div><div>Approved by (sending)</div><div>Driver</div><div>Received by (receiving)</div></div></div>';
        }
        function opennote(id) {
            var t = find(id);
            if (!t) { return; }
            curnote = id;
            $('#notebody').html(noteHtml(t));
            $('#notemodal').modal('show');
        }

        /* ============ new transfer ============ */
        function fillBranches() {
            var type = parseInt($('input[name=ntype]:checked').val(), 10), h = '<option value="">Select</option>';
            $.each(BRANCHES, function(i, b) { if (b.type === type) { h += '<option value="' + b.id + '">' + esc(b.name) + '</option>'; } });
            $('#n_from').html(h);
            $('#n_to').html(h);
        }
        function showSerials() {
            var pid = $('#n_product').val();
            if (!pid) {
                $('#n_serials').html('<div class="text-muted small p-2">Select a product to see its available serial numbers.</div>');
                $('#n_avail').text('');
                return;
            }
            var p = product(pid), locked = lockedSerials(), h = '', n = 0;
            $.each(p.serials, function(i, s) {
                if ($.inArray(s, locked) > -1) { return; }
                n++;
                h += '<label class="st-pick"><input type="checkbox" value="' + esc(s) + '"><span>' + esc(s) + '</span></label>';
            });
            $('#n_serials').html(n ? h : '<div class="text-muted small p-2">No free serial numbers for this product.</div>');
            $('#n_avail').text(n + ' serial number(s) available');
        }
        function drawDraft() {
            var h = '', total = 0;
            if (!draft.length) {
                h = '<tr><td colspan="4" class="text-center text-muted py-3">No items added yet.</td></tr>';
            }
            $.each(draft, function(i, it) {
                var p = product(it.pid), chips = '';
                total += it.serials.length;
                $.each(it.serials, function(k, s) { chips += '<span class="st-chip">' + esc(s) + '</span>'; });
                h += '<tr><td><div class="font-weight-bold">' + esc(p.name) + '</div><small class="text-muted">' + esc(p.code) + '</small></td><td class="text-right">' + it.serials.length + '</td><td>' + chips + '</td>' +
                     '<td class="text-center"><button type="button" class="btn btn-danger btn-sm btnremove" data-index="' + i + '"><i class="fas fa-trash-alt"></i></button></td></tr>';
            });
            if (draft.length) { h += '<tr class="bg-light"><td class="text-right font-weight-bold">Total</td><td class="text-right font-weight-bold">' + total + '</td><td colspan="2"></td></tr>'; }
            $('#n_items').html(h);
        }
        function resetNew() {
            draft = [];
            $('input[name=ntype][value=1]').prop('checked', true);
            fillBranches();
            $('#n_date').val(nowstr().substr(0, 10));
            $('#n_vehicle, #n_driver, #n_contact, #n_remark').val('');
            var ph = '<option value="">Select</option>';
            $.each(PRODUCTS, function(i, p) { ph += '<option value="' + p.id + '">' + esc(p.code + ' - ' + p.name) + '</option>'; });
            $('#n_product').html(ph);
            showSerials();
            drawDraft();
        }

        $('#btnnew').on('click', function() { resetNew(); $('#newmodal').modal('show'); });
        $('input[name=ntype]').on('change', fillBranches);
        $('#n_product').on('change', showSerials);
        $('#n_selall').on('click', function(e) { e.preventDefault(); $('#n_serials input[type=checkbox]').prop('checked', true); });

        $('#btnadditem').on('click', function() {
            var pid = parseInt($('#n_product').val(), 10), picked = [];
            if (!pid) { notify('Select a product first.', 'warn'); return; }
            $('#n_serials input:checked').each(function() { picked.push($(this).val()); });
            if (!picked.length) { notify('Tick at least one serial number.', 'warn'); return; }
            var found = false;
            $.each(draft, function(i, it) { if (it.pid === pid) { it.serials = it.serials.concat(picked); found = true; } });
            if (!found) { draft.push({ pid: pid, serials: picked }); }
            drawDraft();
            showSerials();
        });
        $('#n_items').on('click', '.btnremove', function() {
            draft.splice(parseInt($(this).attr('data-index'), 10), 1);
            drawDraft();
            showSerials();
        });

        $('#btnsave').on('click', function() {
            var from = parseInt($('#n_from').val(), 10), to = parseInt($('#n_to').val(), 10);
            if (!from || !to) { notify('Select the from and to locations.', 'warn'); return; }
            if (from === to) { notify('From and To locations must be different.', 'warn'); return; }
            if (!$('#n_date').val() || !$.trim($('#n_vehicle').val()) || !$.trim($('#n_driver').val())) { notify('Enter the date, vehicle number and driver.', 'warn'); return; }
            if (!draft.length) { notify('Add at least one item to the transfer.', 'warn'); return; }

            var no = transfers.length + 1;
            var t = {
                id: 'TRF-' + ('0000' + no).slice(-4),
                date: $('#n_date').val(),
                type: parseInt($('input[name=ntype]:checked').val(), 10),
                from: from, to: to,
                vehicle: $.trim($('#n_vehicle').val()), driver: $.trim($('#n_driver').val()), contact: $.trim($('#n_contact').val()), remark: $.trim($('#n_remark').val()),
                items: draft, status: 1,
                log: [{ step: 1, at: nowstr(), user: USER }]
            };
            transfers.push(t);
            draft = [];
            refresh();
            $('#newmodal').one('hidden.bs.modal', function() {
                notify(t.id + ' saved. Transfer note created.', 'ok');
                opennote(t.id);
            }).modal('hide');
        });

        /* ============ events ============ */
        $('#tbltransfer tbody').on('click', '.btntrack', function(e) { e.preventDefault(); $('[data-toggle="tooltip"]').tooltip('hide'); opentrack($(this).attr('data-id')); });
        $('#tbltransfer tbody').on('click', '.btnnote', function() { $('[data-toggle="tooltip"]').tooltip('hide'); opennote($(this).attr('data-id')); });
        $('#tbltransfer tbody').on('click', '.btnnext', function() { advance($(this).attr('data-id')); });
        $('#trackbody').on('click', '.btnnext', function() { advance($(this).attr('data-id')); });

        $('#btntracknote').on('click', function() {
            var id = curid;
            $('#trackmodal').one('hidden.bs.modal', function() { opennote(id); }).modal('hide');
        });

        $('#btnprintnote').on('click', function() {
            var t = find(curnote);
            if (!t) { return; }
            var w = window.open('', '_blank', 'width=900,height=800');
            if (!w) { notify('Allow pop-ups to print the transfer note.', 'warn'); return; }
            w.document.write('<html><head><title>' + esc(tnno(t)) + '</title><style>body{margin:24px}' + $('#notecss').html() + '</style></head><body>' + noteHtml(t) + '</body></html>');
            w.document.close();
            w.focus();
            setTimeout(function() { w.print(); }, 300);
        });

        $('#summaryrow').on('click', '.st-stat', function() {
            var k = String($(this).attr('data-status'));
            $('#f_status').val(k === '' || String($('#f_status').val()) === k ? '' : k);
            refresh();
        });
        $('#f_type, #f_status').on('change', refresh);
        $('#btnreset').on('click', function() { $('#f_type, #f_status').val(''); refresh(); });

        refresh();
    });
</script>
<?php include "include/footer.php"; ?>