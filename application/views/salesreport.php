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
                            <div class="page-header-icon"><i class="fas fa-chart-line"></i></div>
                            <span>Sales Reports</span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2 sx">
                <div class="sx-tabs" id="reportTabs">
                    <a href="#" class="sx-tab on" data-report="1"><i class="far fa-calendar-alt"></i> Daily Sales</a>
                    <a href="#" class="sx-tab" data-report="2"><i class="fas fa-list"></i> Product / Brand / Category</a>
                    <a href="#" class="sx-tab" data-report="3"><i class="fas fa-euro-sign"></i> Gross Profit</a>
                    <a href="#" class="sx-tab" data-report="4"><i class="fas fa-shopping-basket"></i> Customer Sales &amp; Statement</a>
                    <a href="#" class="sx-tab" data-report="5"><i class="fas fa-wallet"></i> Credit Ageing</a>
                    <a href="#" class="sx-tab" data-report="6"><i class="fas fa-exchange-alt"></i> Sales Return</a>
                </div>
                <div class="sx-panel">
                    <div class="sx-filter">
                        <div class="form-row align-items-end mb-2">
                            <div class="col-auto f-item" data-f="dates">
                                <label class="small font-weight-bold text-dark mb-1">From</label>
                                <input type="date" id="fFrom" class="form-control form-control-sm" value="<?php echo date('Y-m-01') ?>">
                            </div>
                            <div class="col-auto f-item" data-f="dates">
                                <label class="small font-weight-bold text-dark mb-1">To</label>
                                <input type="date" id="fTo" class="form-control form-control-sm" value="<?php echo date('Y-m-d') ?>">
                            </div>
                            <div class="col-auto f-item" data-f="branch">
                                <label class="small font-weight-bold text-dark mb-1">Branch</label>
                                <select id="fBranch" class="form-control form-control-sm">
                                    <option value="0">All branches</option>
                                    <?php foreach ($branches as $b) { ?>
                                    <option value="<?php echo (int)$b->id ?>" <?php echo ($b->id == $_SESSION['branch_id']) ? 'selected' : '' ?>><?php echo html_escape($b->code) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-auto f-item" data-f="cashier">
                                <label class="small font-weight-bold text-dark mb-1">Cashier</label>
                                <select id="fCashier" class="form-control form-control-sm">
                                    <option value="0">All cashiers</option>
                                    <?php foreach ($cashiers as $c) { ?>
                                    <option value="<?php echo (int)$c->id ?>"><?php echo html_escape($c->name) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-auto f-item" data-f="mode">
                                <label class="small font-weight-bold text-dark mb-1">View by</label>
                                <select id="fMode" class="form-control form-control-sm"></select>
                            </div>
                            <div class="col-auto f-item" data-f="customer">
                                <label class="small font-weight-bold text-dark mb-1">Customer (statement)</label>
                                <select id="fCustomer" class="form-control form-control-sm">
                                    <option value="0">All customers (summary)</option>
                                    <?php foreach ($customers as $c) { ?>
                                    <option value="<?php echo (int)$c->id ?>"><?php echo html_escape($c->name) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="button" id="btnGenerate" class="btn btn-primary btn-sm"><i class="fas fa-sync-alt mr-1"></i>Generate</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="scrollbar pb-3" id="style-2">
                                    <table class="table table-bordered table-striped table-sm nowrap" id="reportTable" style="width:100%"></table>
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

<?php include "include/footerscripts.php"; ?>

<script>
$(document).ready(function() {

    function fmt(n) {
        return (parseFloat(n) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // ----- column builders -----
    var T = $.fn.dataTable.render.text();
    function txt(k, t) { return { data: k, title: t, render: T }; }
    function num(k, t, sum) {
        return { data: k, title: t, className: 'text-right', sum: !!sum,
                 render: function(d, type) { return (type === 'display' || type === 'filter') ? fmt(d) : d; } };
    }
    function cnt(k, t, sum) { return { data: k, title: t, className: 'text-right', sum: !!sum, isInt: true }; }
    function pct(k, t) {
        return { data: k, title: t, className: 'text-right',
                 render: function(d, type) { return (type === 'display') ? (d == null ? '-' : fmt(d) + ' %') : d; } };
    }

    // ----- the six reports -----
    var R = {
        1: { title: 'Daily Sales Report', show: ['dates', 'branch', 'cashier'],
             cols: { main: [
                txt('invoice_no', 'Invoice No'), txt('invoice_date', 'Date'), txt('customer_name', 'Customer'), txt('cashier', 'Cashier'),
                num('gross_total', 'Gross', 1), num('discount', 'Discount', 1), num('vatamount', 'VAT', 1), num('net_total', 'Net Total', 1),
                num('cash', 'Cash', 1), num('card', 'Card', 1), num('cheque', 'Cheque', 1), num('transfer', 'Transfer', 1),
                num('voucher', 'Voucher', 1), num('creditnote', 'Credit Note', 1), num('credit', 'Credit (Unpaid)', 1)
             ] } },

        2: { title: 'Sales by Product / Brand / Category', show: ['dates', 'branch', 'mode'],
             modes: [['product', 'Product'], ['brand', 'Brand'], ['category', 'Category']],
             cols: {
                product:  [txt('product_code', 'Item Code'), txt('product_name', 'Product'), cnt('qty', 'Qty Sold', 1),
                           num('revenue', 'Revenue (excl. VAT)', 1), num('cost', 'Cost', 1), num('profit', 'Gross Profit', 1)],
                brand:    [txt('label', 'Brand'), cnt('qty', 'Qty Sold', 1),
                           num('revenue', 'Revenue (excl. VAT)', 1), num('cost', 'Cost', 1), num('profit', 'Gross Profit', 1)],
                category: [txt('label', 'Category'), cnt('qty', 'Qty Sold', 1),
                           num('revenue', 'Revenue (excl. VAT)', 1), num('cost', 'Cost', 1), num('profit', 'Gross Profit', 1)]
             } },

        3: { title: 'Gross Profit Report', show: ['dates', 'branch', 'mode'],
             modes: [['invoice', 'Per Invoice'], ['product', 'Per Product']],
             cols: {
                invoice: [txt('invoice_no', 'Invoice No'), txt('invoice_date', 'Date'), txt('customer_name', 'Customer'),
                          num('revenue', 'Selling (excl. VAT)', 1), num('cost', 'Cost', 1), num('profit', 'Gross Profit', 1), pct('margin', 'Margin')],
                product: [txt('product_code', 'Item Code'), txt('product_name', 'Product'), cnt('qty', 'Qty', 1),
                          num('revenue', 'Selling (excl. VAT)', 1), num('cost', 'Cost', 1), num('profit', 'Gross Profit', 1), pct('margin', 'Margin')]
             } },

        4: { title: 'Customer Sales & Statement', show: ['dates', 'branch', 'customer'],
             cols: {
                summary:   [txt('customer_name', 'Customer'), cnt('invoices', 'Invoices', 1), num('total', 'Total Purchases', 1),
                            num('paid', 'Paid', 1), num('balance', 'Outstanding', 1)],
                statement: [txt('invoice_date', 'Date'), txt('invoice_no', 'Invoice No'), num('total', 'Invoice Total', 1),
                            num('paid', 'Paid', 1), num('balance', 'Balance Due', 1), num('running', 'Running Outstanding', 0)]
             } },

        5: { title: 'Credit Ageing Report (as of today)', show: ['branch'],
             cols: { main: [
                txt('invoice_no', 'Invoice No'), txt('invoice_date', 'Date'), txt('customer_name', 'Customer'), cnt('age', 'Days', 0),
                num('net_total', 'Invoice Total', 1), num('paid_total', 'Paid', 1), num('balance_due', 'Balance Due', 1),
                num('b30', '0 - 30', 1), num('b60', '31 - 60', 1), num('b90', '61 - 90', 1), num('b90p', 'Over 90', 1)
             ] } },

        6: { title: 'Sales Return Report', show: ['dates', 'branch'],
             cols: { main: [
                txt('creditnote_date', 'Date'), txt('creditnote_no', 'Credit Note'), txt('invoice_no', 'Invoice No'), txt('customer_name', 'Customer'),
                txt('product_name', 'Item'), txt('serialno', 'Serial'), cnt('qty', 'Qty', 1), num('amount', 'Credit Amount', 1),
                txt('reason', 'Reason'), txt('status', 'Status')
             ] } }
    };

    var current = 1;

    function selectReport(n) {
        current = n;
        var cfg = R[n];
        $('#reportTabs .sx-tab').removeClass('on');
        $('#reportTabs .sx-tab[data-report="' + n + '"]').addClass('on');

        $('.f-item').hide();
        cfg.show.forEach(function(k) { $('.f-item[data-f="' + k + '"]').show(); });

        if (cfg.modes) {
            var o = '';
            cfg.modes.forEach(function(m) { o += '<option value="' + m[0] + '">' + m[1] + '</option>'; });
            $('#fMode').html(o);
        }
        loadReport();
    }

    function loadReport() {
        var cfg = R[current];
        $('#btnGenerate').prop('disabled', true);
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo base_url() ?>Salesreport/Getreport',
            data: {
                report: current,
                from: $('#fFrom').val(), to: $('#fTo').val(),
                branch: $('#fBranch').val(), cashier: $('#fCashier').val(),
                mode: $('#fMode').val(), customer: $('#fCustomer').val()
            },
            success: function(res) {
                $('#btnGenerate').prop('disabled', false);
                if (res.status != 1) { action(res.action); return; }
                drawTable(cfg, res.variant, res.rows);
            },
            error: function() {
                $('#btnGenerate').prop('disabled', false);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong. Please try again later.' });
            }
        });
    }

    function drawTable(cfg, variant, rows) {
        var cols = cfg.cols[variant] || cfg.cols[Object.keys(cfg.cols)[0]];

        if ($.fn.DataTable.isDataTable('#reportTable')) { $('#reportTable').DataTable().destroy(); }
        $('#reportTable').empty().append('<tfoot><tr>' + cols.map(function() { return '<th></th>'; }).join('') + '</tr></tfoot>');

        var showDates = cfg.show.indexOf('dates') >= 0;
        var title = cfg.title + (showDates ? ' (' + $('#fFrom').val() + ' to ' + $('#fTo').val() + ')' : '');

        $('#reportTable').DataTable({
            data: rows,
            columns: cols,
            order: [],                      // the server already sorts the rows
            // scrollX: true,
            pageLength: 25,
            dom: "<'row'<'col-sm-5'B><'col-sm-2'l><'col-sm-5'f>>" + "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
            buttons: [
                { extend: 'csv', className: 'btn btn-success btn-sm', title: title, footer: true,
                  text: '<i class="fas fa-file-csv mr-2"></i> CSV' },
                { extend: 'pdf', className: 'btn btn-danger btn-sm', title: title, footer: true,
                  orientation: 'landscape', pageSize: 'A4', text: '<i class="fas fa-file-pdf mr-2"></i> PDF' },
                { extend: 'print', className: 'btn btn-primary btn-sm', title: title, footer: true,
                  text: '<i class="fas fa-print mr-2"></i> Print',
                  customize: function(win) { $(win.document.body).find('table').addClass('compact').css('font-size', 'inherit'); } }
            ],
            footerCallback: function() {
                var api = this.api();
                cols.forEach(function(c, i) {
                    if (c.sum) {
                        var t = api.column(i, { search: 'applied' }).data()
                                   .reduce(function(a, b) { return a + (parseFloat(b) || 0); }, 0);
                        $(api.column(i).footer()).html(c.isInt ? t : fmt(t));
                    }
                });
                $(api.column(0).footer()).html('<b>Total</b>');
            }
        });
    }

    $('#reportTabs').on('click', '.sx-tab', function(e) {
        e.preventDefault();
        selectReport(parseInt($(this).data('report')));
    });
    $('#btnGenerate').click(loadReport);

    selectReport(1);
});
</script>
<?php include "include/footer.php"; ?>