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
                            <div class="page-header-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                            <span>Tax &amp; Accounts Reports</span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2 sx">
                <div class="sx-tabs" id="reportTabs">
                    <a href="#" class="sx-tab on" data-report="21"><i class="fas fa-percentage"></i> VAT Report</a>
                    <a href="#" class="sx-tab" data-report="22"><i class="fas fa-cash-register"></i> Payment Collection</a>
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
                            <div class="col-auto">
                                <button type="button" id="btnGenerate" class="btn btn-primary btn-sm"><i class="fas fa-sync-alt mr-1"></i>Generate</button>
                            </div>
                        </div>

                        <div id="rptNotice" class="alert alert-warning small py-1 d-none"></div>

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

    // ----- the two reports -----
    var vatSummary = [
        txt('period', 'Period'),
        num('taxable_sales', 'Taxable Sales (excl. VAT)', 1),
        num('output_vat', 'Output VAT (Sales)', 1),
        num('cn_vat', 'Less: Credit Note VAT', 1),
        num('net_output', 'Net Output VAT', 1),
        num('input_vat', 'Input VAT (Purchases)', 1),
        num('vat_payable', 'VAT Payable / (Refundable)', 1)
    ];

    var R = {
        21: { title: 'VAT Report', show: ['dates', 'branch', 'mode'],
            modes: [['month', 'Monthly summary'], ['day', 'Daily summary'],
                    ['invoice', 'Sales VAT detail (invoices & credit notes)'], ['purchase', 'Purchase VAT detail (GRN)']],
            cols: {
                month: vatSummary,
                day:   vatSummary,
                invoice: [txt('doc_type', 'Type'), txt('doc_no', 'Document No'), txt('doc_date', 'Date'), txt('customer_name', 'Customer'),
                        txt('customer_vat', 'Customer VAT No'), num('taxable', 'Taxable Value', 1), num('vat', 'VAT', 1), num('total', 'Total', 1)],
                purchase: [txt('grn_no', 'GRN No'), txt('grndate', 'Date'), txt('invoicenum', 'Supplier Invoice No'), txt('supplier', 'Supplier'),
                        txt('vat_type', 'VAT Type'), num('subtotal', 'Sub Total', 1), num('discount', 'Discount', 1),
                        num('vatamount', 'Input VAT', 1), num('total', 'Total', 1)]
            } },

        22: { title: 'Payment Collection Report', show: ['dates', 'branch', 'cashier', 'mode'],
              modes: [['day', 'Per day'], ['daycashier', 'Per day & cashier'], ['detail', 'Card / Cheque / Bank transfer detail']],
              cols: {
                day: [txt('day', 'Date'), cnt('invoices', 'Invoices', 1), num('cash', 'Cash', 1), num('card', 'Card', 1),
                      num('cheque', 'Cheque', 1), num('transfer', 'Bank Transfer', 1), num('collected', 'Total Collected', 1),
                      num('voucher', 'Gift Voucher (not money)', 1), num('creditnote', 'Credit Note (not money)', 1)],
                daycashier: [txt('day', 'Date'), txt('cashier', 'Cashier'), cnt('invoices', 'Invoices', 1), num('cash', 'Cash', 1), num('card', 'Card', 1),
                      num('cheque', 'Cheque', 1), num('transfer', 'Bank Transfer', 1), num('collected', 'Total Collected', 1),
                      num('voucher', 'Gift Voucher (not money)', 1), num('creditnote', 'Credit Note (not money)', 1)],
                detail: [txt('day', 'Date'), txt('invoice_no', 'Invoice No'), txt('cashier', 'Cashier'), txt('paytype', 'Payment Type'),
                         txt('bank', 'Bank'), txt('ref', 'Reference'), txt('approval', 'Approval Code'), txt('cheque_date', 'Cheque Date'),
                         txt('cheque_status', 'Cheque Status'), num('amount', 'Amount', 1)]
              } }
    };

    var current = 21;

    function selectReport(n) {
        current = n;
        var cfg = R[n];
        $('#reportTabs .sx-tab').removeClass('on');
        $('#reportTabs .sx-tab[data-report="' + n + '"]').addClass('on');

        $('.f-item').hide();
        cfg.show.forEach(function(k) { $('.f-item[data-f="' + k + '"]').show(); });

        var o = '';
        cfg.modes.forEach(function(m) { o += '<option value="' + m[0] + '">' + m[1] + '</option>'; });
        $('#fMode').html(o);

        loadReport();
    }

    function loadReport() {
        var cfg = R[current];
        $('#btnGenerate').prop('disabled', true);
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo base_url() ?>Accountreport/Getreport',
            data: {
                report: current,
                from: $('#fFrom').val(), to: $('#fTo').val(),
                branch: $('#fBranch').val(), cashier: $('#fCashier').val(),
                mode: $('#fMode').val()
            },
            success: function(res) {
                $('#btnGenerate').prop('disabled', false);
                if (res.status != 1) { action(res.action); return; }
                if (res.notice) { $('#rptNotice').text(res.notice).removeClass('d-none'); }
                else { $('#rptNotice').addClass('d-none'); }
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

        var title = cfg.title + ' (' + $('#fFrom').val() + ' to ' + $('#fTo').val() + ')';

        $('#reportTable').DataTable({
            data: rows,
            columns: cols,
            order: [],                      // the server already sorts the rows
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

    selectReport(21);
});
</script>
<?php include "include/footer.php"; ?>