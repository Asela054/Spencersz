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
                            <div class="page-header-icon"><i class="fas fa-coins"></i></div>
                            <span>Cash Balance Information</span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2">

                <div class="card mb-2">
                    <div class="card-body py-2">
                        <div class="row align-items-end">
                            <div class="col-6 col-md-2 mb-2">
                                <label class="small font-weight-bold mb-1">From</label>
                                <input type="date" id="f_from" class="form-control form-control-sm" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-6 col-md-2 mb-2">
                                <label class="small font-weight-bold mb-1">To</label>
                                <input type="date" id="f_to" class="form-control form-control-sm" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-12 col-md-3 mb-2">
                                <label class="small font-weight-bold mb-1">Branch</label>
                                <select id="f_branch" class="form-control form-control-sm">
                                    <option value="0">All</option>
                                    <?php foreach($branches as $b){
                                        $sel=((int)$b->id===(int)$_SESSION['branch_id']) ? ' selected' : '';
                                        echo '<option value="'.(int)$b->id.'"'.$sel.'>'.htmlspecialchars($b->name).'</option>';
                                    } ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-2 mb-2">
                                <button type="button" id="btnview" class="btn btn-primary btn-sm px-4"><i class="fas fa-search mr-1"></i>View</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-6 col-md-3 mb-2"><div class="card"><div class="card-body py-2"><div class="small text-muted">Cash sales</div><div class="h5 mb-0 font-monospace" id="t_sales">0.00</div></div></div></div>
                    <div class="col-6 col-md-3 mb-2"><div class="card"><div class="card-body py-2"><div class="small text-muted">Counted cash</div><div class="h5 mb-0 font-monospace" id="t_counted">0.00</div></div></div></div>
                    <div class="col-6 col-md-3 mb-2"><div class="card"><div class="card-body py-2"><div class="small text-muted">Short / Over</div><div class="h5 mb-0" id="t_diff">-</div></div></div></div>
                    <div class="col-6 col-md-3 mb-2"><div class="card"><div class="card-body py-2"><div class="small text-muted">Cash balance in hand now</div><div class="h5 mb-0 font-monospace text-primary" id="t_balance">0.00</div></div></div></div>
                </div>

                <div class="card mb-2">
                    <div class="card-header py-2 font-weight-bold"><i class="fas fa-calendar-day mr-2"></i>Cashier closes</div>
                    <div class="card-body p-0 p-2">
                        <div class="scrollbar pb-3" id="style-2">
                            <table class="table table-bordered table-striped table-sm nowrap w-100" id="tblbalance">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Branch</th>
                                        <th>Cashier</th>
                                        <th class="text-right">Opening</th>
                                        <th class="text-right">Cash sales</th>
                                        <th class="text-right">Expected</th>
                                        <th class="text-right">Counted</th>
                                        <th class="text-right">Short / Over</th>
                                        <th class="text-right">Handover</th>
                                        <th class="text-right">Closing</th>
                                        <th>Day end</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header py-2 font-weight-bold"><i class="fas fa-wallet mr-2"></i>Cash balance in hand (latest closing of each cashier)</div>
                    <div class="card-body p-0 p-2">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="thead-light"><tr><th>Branch</th><th>Cashier</th><th>Last closed on</th><th class="text-right">Closing balance</th></tr></thead>
                            <tbody id="tblinhand"></tbody>
                        </table>
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
        function fm(n){ return (parseFloat(n)||0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
        function esc(s){ return $('<div>').text(s === null || s === undefined ? '' : s).html(); }
        function diffHtml(v){
            v=Math.round((parseFloat(v)||0)*100)/100;
            if(v<0){ return '<span class="font-weight-bold text-danger">SHORT '+fm(-v)+'</span>'; }
            if(v>0){ return '<span class="font-weight-bold text-warning">OVER '+fm(v)+'</span>'; }
            return '<span class="font-weight-bold text-success">Balanced</span>';
        }

        var table=$('#tblbalance').DataTable({
            "destroy": true,
            data: [],
            dom: "<'row'<'col-sm-5'B><'col-sm-2'l><'col-sm-5'f>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            responsive: true,
            "buttons": [
                { extend: 'csv', className: 'btn btn-success btn-sm', title: 'Cash Balance Information', text: '<i class="fas fa-file-csv mr-2"></i> CSV' },
                { extend: 'pdf', className: 'btn btn-danger btn-sm', title: 'Cash Balance Information', text: '<i class="fas fa-file-pdf mr-2"></i> PDF' },
                { extend: 'print', title: 'Cash Balance Information', className: 'btn btn-primary btn-sm', text: '<i class="fas fa-print mr-2"></i> Print' }
            ],
            "order": [[ 0, "desc" ]],
            "columns": [
                { "data": "close_date" },
                { "data": "branch_name" },
                { "data": "cashier" },
                { "data": "opening_balance", "className": "text-right", "render": function(d){ return fm(d); } },
                { "data": "cash_sales", "className": "text-right", "render": function(d){ return fm(d); } },
                { "data": "expected_cash", "className": "text-right", "render": function(d){ return fm(d); } },
                { "data": "counted_cash", "className": "text-right", "render": function(d){ return fm(d); } },
                { "data": "difference", "className": "text-right", "render": function(d, type){ return type==='display' ? diffHtml(d) : d; } },
                { "data": "handover_amount", "className": "text-right", "render": function(d){ return fm(d); } },
                { "data": "closing_balance", "className": "text-right", "render": function(d){ return fm(d); } },
                { "data": "dayend_done", "render": function(d){ return parseInt(d, 10)===1 ? '<span class="badge badge-success">Done</span>' : '<span class="badge badge-warning">Open</span>'; } }
            ]
        });

        function loadData(){
            $.ajax({
                type: "POST",
                data: { from: $('#f_from').val(), to: $('#f_to').val(), branch: $('#f_branch').val() },
                url: '<?php echo base_url() ?>Cashbalance/Cashbalancedata',
                success: function(result){
                    var obj=JSON.parse(result);

                    table.clear().rows.add(obj.rows).draw();

                    var sales=0, counted=0, diff=0;
                    $.each(obj.rows, function(i, r){
                        sales+=parseFloat(r.cash_sales)||0;
                        counted+=parseFloat(r.counted_cash)||0;
                        diff+=parseFloat(r.difference)||0;
                    });
                    $('#t_sales').text(fm(sales));
                    $('#t_counted').text(fm(counted));
                    $('#t_diff').html(obj.rows.length ? diffHtml(diff) : '-');
                    $('#t_balance').text(fm(obj.balance_total));

                    var h='';
                    $.each(obj.balances, function(i, b){
                        h+='<tr><td>'+esc(b.branch_name)+'</td><td>'+esc(b.cashier)+'</td><td>'+esc(b.close_date)+'</td>'+
                           '<td class="text-right font-monospace">'+fm(b.closing_balance)+'</td></tr>';
                    });
                    if(!h){ h='<tr><td colspan="4" class="text-center text-muted">No cashier close yet</td></tr>'; }
                    $('#tblinhand').html(h);
                }
            });
        }

        $('#btnview').on('click', loadData);
        loadData();
    });
</script>
<?php include "include/footer.php"; ?>