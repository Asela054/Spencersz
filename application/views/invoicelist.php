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
                            <span>Invoice List</span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2">
                <div class="card">
                    <div class="card-body p-0 p-2">
                        <div class="row">
                            <div class="col-12">
                                <div class="scrollbar pb-3" id="style-2">
                                    <table class="table table-bordered table-striped table-sm nowrap" id="dataTable">
                                        <thead>
                                            <tr>
                                                <th>Invoice No</th>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Customer</th>
                                                <th class="text-right">Total</th>
                                                <th class="text-right">Paid</th>
                                                <th class="text-right">Balance</th>
                                                <th>Status</th>
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

<?php include "include/footerscripts.php"; ?>

<script>
$(document).ready(function() {

    function invEsc(s) { return $('<div>').text(s == null ? '' : s).html(); }
    function invFmt(n) { return addCommas(parseFloat(n || 0).toFixed(2)); }

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
                title: 'Invoice Information',
                text: '<i class="fas fa-file-csv mr-2"></i> CSV',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] }
            },
            {
                extend: 'pdf',
                className: 'btn btn-danger btn-sm',
                title: 'Invoice Information',
                text: '<i class="fas fa-file-pdf mr-2"></i> PDF',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] }
            },
            {
                extend: 'print',
                title: 'Invoice Information',
                className: 'btn btn-primary btn-sm',
                text: '<i class="fas fa-print mr-2"></i> Print',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] },
                customize: function(win) {
                    $(win.document.body).find('table')
                        .addClass('compact')
                        .css('font-size', 'inherit');
                },
            },
        ],
        ajax: {
            url: "<?php echo base_url() ?>scripts/invoicelist.php",
            type: "POST"
        },
        "order": [
            [1, "desc"],
            [9, "desc"]
        ],
        "columns": [
            { "data": "invoice_no" },
            { "data": "invoice_date" },
            { "data": "invoice_type" },
            { "data": "customer_name" },
            {
                "className": 'text-right',
                "data": "net_total",
                "render": function(data) { return invFmt(data); }
            },
            {
                "className": 'text-right',
                "data": "paid_total",
                "render": function(data) { return invFmt(data); }
            },
            {
                "className": 'text-right',
                "data": "balance_due",
                "render": function(data) { return invFmt(data); }
            },
            {
                "data": "payment_status",
                "render": function(data, type, row) {
                    if (row['invoice_type'] === 'Corporate Quotation') return '<span class="badge badge-secondary">Quotation</span>';
                    if (data == 1) return '<span class="badge badge-success">Paid</span>';
                    if (data == 2) return '<span class="badge badge-warning">Partial</span>';
                    return '<span class="badge badge-danger">Unpaid</span>';
                }
            },
            {
                "className": 'text-right',
                "orderable": false,
                "searchable": false,
                "data": null,
                "render": function(data, type, full) {
                    return '<a href="<?php echo base_url() ?>Invoice/Printinvoice/' + full['idtbl_invoice'] +
                        '" target="_blank" data-toggle="tooltip" data-placement="bottom" title="View PDF" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf"></i></a>';
                }
            },
            { "data": "idtbl_invoice", "visible": false, "searchable": false }
        ],
        drawCallback: function(settings) {
            $('[data-toggle="tooltip"]').tooltip();
        }
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
</script>
<?php include "include/footer.php"; ?>