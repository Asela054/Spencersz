<?php 
include "include/header.php";  
include "include/topnavbar.php"; 
?>
<style>
    .de-box{background:#f8f9fa;border:1px solid #e9ecef;border-radius:4px;padding:8px 12px}
    .de-big{font-size:1.15rem;font-weight:700}
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
                            <div class="page-header-icon"><i class="fas fa-calendar-check"></i></div>
                            <span>Day End</span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2">

                <div class="card mb-2">
                    <div class="card-body py-2">
                        <div class="row align-items-end">
                            <div class="col-12 col-md-3">
                                <label class="small font-weight-bold mb-1">Date</label>
                                <input type="date" id="de_date" class="form-control form-control-sm" value="<?php echo $date; ?>" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-12 col-md-9 text-md-right pt-2" id="de_status"></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- ============ my cashier close ============ -->
                    <div class="col-lg-6 mb-2">
                        <div class="card h-100">
                            <div class="card-header py-2 font-weight-bold"><i class="fas fa-cash-register mr-2"></i>My shift close <span class="text-muted font-weight-normal" id="me_name"></span></div>
                            <div class="card-body">
                                <form action="<?php echo base_url() ?>Dayend/Cashierclose" method="post" autocomplete="off" id="closeform">
                                    <input type="hidden" name="date" class="f-date" value="<?php echo $date; ?>">

                                    <div class="form-row">
                                        <div class="form-group col-6 mb-2">
                                            <label class="small font-weight-bold mb-1">Opening balance</label>
                                            <input type="number" step="0.01" min="0" class="form-control form-control-sm text-right" name="opening_balance" id="opening" value="0.00">
                                        </div>
                                        <div class="form-group col-6 mb-2">
                                            <label class="small font-weight-bold mb-1">Cash sales (system)</label>
                                            <div class="de-box text-right font-monospace" id="cashsales">0.00</div>
                                        </div>
                                    </div>

                                    <table class="table table-sm table-bordered mb-2">
                                        <thead class="thead-light"><tr><th>Note / Coin</th><th style="width:120px;">Quantity</th><th class="text-right">Amount</th></tr></thead>
                                        <tbody>
                                        <?php foreach($denoms as $d){ ?>
                                            <tr>
                                                <td class="font-weight-bold">Rs. <?php echo number_format($d); ?></td>
                                                <td><input type="number" min="0" step="1" class="form-control form-control-sm text-right dn-qty" name="qty[<?php echo $d; ?>]" data-d="<?php echo $d; ?>" value="0"></td>
                                                <td class="text-right font-monospace" id="amt_<?php echo $d; ?>">0.00</td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>

                                    <div class="de-box mb-2">
                                        <div class="d-flex justify-content-between"><span>Expected (opening + cash sales)</span><span class="font-monospace" id="expected">0.00</span></div>
                                        <div class="d-flex justify-content-between"><span>Counted cash</span><span class="font-monospace de-big" id="counted">0.00</span></div>
                                        <div class="d-flex justify-content-between"><span>Short / Over</span><span id="diff"></span></div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-6 mb-2">
                                            <label class="small font-weight-bold mb-1">Handover (to manager / bank)</label>
                                            <input type="number" step="0.01" min="0" class="form-control form-control-sm text-right" name="handover_amount" id="handover" value="0.00">
                                            <div class="small text-danger d-none" id="handwarn">Cannot be more than the counted cash.</div>
                                        </div>
                                        <div class="form-group col-6 mb-2">
                                            <label class="small font-weight-bold mb-1">Closing balance (next opening)</label>
                                            <div class="de-box text-right font-monospace" id="closing">0.00</div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold mb-1">Remark</label>
                                        <input type="text" class="form-control form-control-sm" name="remark" id="remark" maxlength="255">
                                    </div>
                                    <div class="text-right">
                                        <button type="submit" id="btnclose" class="btn btn-primary btn-sm px-4" disabled><i class="far fa-save"></i>&nbsp;Close my shift</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- ============ branch day end ============ -->
                    <div class="col-lg-6 mb-2">
                        <div class="card h-100">
                            <div class="card-header py-2 font-weight-bold"><i class="fas fa-store mr-2"></i>Branch day end</div>
                            <div class="card-body">
                                <div id="bc_warn"></div>
                                <table class="table table-sm table-bordered">
                                    <thead class="thead-light">
                                        <tr><th>Cashier</th><th class="text-right">Expected</th><th class="text-right">Counted</th><th class="text-right">Short / Over</th><th>Status</th></tr>
                                    </thead>
                                    <tbody id="tblcashiers"></tbody>
                                </table>

                                <div class="font-weight-bold small mb-1">Sales summary</div>
                                <table class="table table-sm table-bordered mb-3">
                                    <tbody id="tblsummary"></tbody>
                                </table>

                                <form action="<?php echo base_url() ?>Dayend/Dayendsave" method="post" id="dayendform">
                                    <input type="hidden" name="date" class="f-date" value="<?php echo $date; ?>">
                                    <div class="text-right">
                                        <button type="submit" id="btndayend" class="btn btn-danger btn-sm px-4" disabled><i class="fas fa-lock"></i>&nbsp;Do Day End</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============ history ============ -->
                <div class="card">
                    <div class="card-header py-2 font-weight-bold"><i class="fas fa-history mr-2"></i>Day end history</div>
                    <div class="card-body p-0 p-2">
                        <div class="scrollbar pb-3" id="style-2">
                            <table class="table table-bordered table-striped table-sm nowrap w-100" id="tbldayend">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th class="text-right">Invoices</th>
                                        <th class="text-right">Net sales</th>
                                        <th class="text-right">Expected cash</th>
                                        <th class="text-right">Counted cash</th>
                                        <th class="text-right">Short / Over</th>
                                        <th>Done by</th>
                                        <th>Done at</th>
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
<?php include "include/footerscripts.php"; ?>
<script>
    $(document).ready(function() {
        var addcheck='<?php echo $addcheck; ?>';
        var statuscheck='<?php echo $statuscheck; ?>';
        var state=null;

        function fm(n){ return (parseFloat(n)||0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
        function esc(s){ return $('<div>').text(s === null || s === undefined ? '' : s).html(); }
        function diffHtml(v){
            v=Math.round((parseFloat(v)||0)*100)/100;
            if(v<0){ return '<span class="font-weight-bold text-danger">SHORT '+fm(-v)+'</span>'; }
            if(v>0){ return '<span class="font-weight-bold text-warning">OVER '+fm(v)+'</span>'; }
            return '<span class="font-weight-bold text-success">Balanced</span>';
        }

        /* ---------- live totals of my shift ---------- */
        function recalc(){
            var counted=0;
            $('.dn-qty').each(function(){
                var q=parseInt($(this).val(), 10);
                if(isNaN(q) || q<0){ q=0; }
                var d=parseInt($(this).data('d'), 10);
                counted+=q*d;
                $('#amt_'+d).text(fm(q*d));
            });
            var opening=parseFloat($('#opening').val())||0;
            var sales=state ? state.me.cash_sales : 0;
            var expected=opening+sales;
            var handover=parseFloat($('#handover').val())||0;

            $('#expected').text(fm(expected));
            $('#counted').text(fm(counted));
            $('#diff').html(diffHtml(counted-expected));
            $('#closing').text(fm(counted-handover));
            $('#handwarn').toggleClass('d-none', handover<=counted+0.001);
        }
        $(document).on('input', '.dn-qty, #opening, #handover', recalc);

        /* ---------- fill the screen ---------- */
        function render(){
            var m=state.me, locked=state.dayend_done;

            $('.f-date').val(state.date);
            $('#me_name').text(m.name ? '- '+m.name : '');
            $('#opening').val(m.opening.toFixed(2)).prop('readonly', !m.opening_editable || locked);
            $('#cashsales').text(fm(m.cash_sales));
            $('.dn-qty').each(function(){
                var d=$(this).data('d');
                $(this).val(m.qty[d] ? m.qty[d] : 0).prop('readonly', locked);
            });
            $('#handover').val(m.handover.toFixed(2)).prop('readonly', locked);
            $('#remark').val(m.remark || '').prop('readonly', locked);
            $('#btnclose').prop('disabled', locked || addcheck!=1)
                          .html('<i class="far fa-save"></i>&nbsp;'+(m.closed ? 'Update my close' : 'Close my shift'));
            recalc();

            if(locked){
                $('#de_status').html('<span class="badge badge-success p-2"><i class="fas fa-lock mr-1"></i>Day end completed by '+esc(state.dayend.done_by)+' at '+esc(state.dayend.at)+'</span>');
            }else{
                $('#de_status').html('<span class="badge badge-warning p-2">Day end not done yet</span>');
            }
            renderBranch();
        }

        function renderBranch(){
            var rows='';
            $.each(state.cashiers, function(i, c){
                var st;
                if(!c.closed){ st='<span class="badge badge-danger">Not closed</span>'; }
                else if(c.stale){ st='<span class="badge badge-warning">Re-close needed</span>'; }
                else{ st='<span class="badge badge-success">Closed</span>'; }
                rows+='<tr><td>'+esc(c.name)+'</td>'+
                      '<td class="text-right font-monospace">'+(c.closed ? fm(c.expected) : '-')+'</td>'+
                      '<td class="text-right font-monospace">'+(c.closed ? fm(c.counted) : '-')+'</td>'+
                      '<td class="text-right">'+(c.closed ? diffHtml(c.difference) : '-')+'</td>'+
                      '<td>'+st+'</td></tr>';
            });
            if(!rows){ rows='<tr><td colspan="5" class="text-center text-muted">No cashier activity for this date</td></tr>'; }
            $('#tblcashiers').html(rows);

            var warn='';
            if(!state.dayend_done && state.missing.length){ warn+='<div class="alert alert-danger py-1 small mb-2">Cashier close is not done for: <b>'+esc(state.missing.join(', '))+'</b></div>'; }
            if(!state.dayend_done && state.stale.length){ warn+='<div class="alert alert-warning py-1 small mb-2">New cash invoices after the close of: <b>'+esc(state.stale.join(', '))+'</b>. They must close again.</div>'; }
            $('#bc_warn').html(warn);

            var s=state.summary;
            var sum='<tr><td>Invoices</td><td class="text-right font-monospace">'+s.invoices+'</td></tr>'+
                    '<tr><td>Gross total</td><td class="text-right font-monospace">'+fm(s.gross)+'</td></tr>'+
                    '<tr><td>Discount</td><td class="text-right font-monospace">'+fm(s.discount)+'</td></tr>'+
                    '<tr><td>VAT</td><td class="text-right font-monospace">'+fm(s.vat)+'</td></tr>'+
                    '<tr class="font-weight-bold"><td>Net total</td><td class="text-right font-monospace">'+fm(s.net)+'</td></tr>';
            $.each(s.payments, function(k, v){
                sum+='<tr class="text-muted"><td>Paid by '+esc(k)+'</td><td class="text-right font-monospace">'+fm(v)+'</td></tr>';
            });
            $('#tblsummary').html(sum);

            var can = statuscheck==1 && !state.dayend_done && !state.missing.length && !state.stale.length;
            $('#btndayend').prop('disabled', !can);
        }

        function loadPreview(){
            $.ajax({
                type: "POST",
                data: { date: $('#de_date').val() },
                url: '<?php echo base_url() ?>Dayend/Dayendpreview',
                success: function(result){
                    var obj=JSON.parse(result);
                    if(!obj.ok){ alert(obj.msg); return; }
                    state=obj;
                    render();
                }
            });
        }

        $('#de_date').on('change', loadPreview);
        $('#closeform').on('submit', function(){ return confirm('Close your shift for '+$('#de_date').val()+'?'); });
        $('#dayendform').on('submit', function(){ return confirm('Do the day end for '+$('#de_date').val()+'? Cashier closes of this date can not be changed afterwards.'); });

        /* ---------- history (server side) ---------- */
        $('#tbldayend').DataTable({
            "destroy": true,
            "processing": true,
            "serverSide": true,
            responsive: true,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
            ajax: {
                url: "<?php echo base_url() ?>scripts/dayendlist.php",
                type: "POST",
                data: function(d) { d.branch = '<?php echo (int)$_SESSION['branch_id']; ?>'; }
            },
            "order": [[ 0, "desc" ]],
            "columns": [
                {
                    "data": null,
                    "render": function(data, type, full, meta) {
                        return meta.settings._iRecordsDisplay - meta.row;
                    }
                },
                { "data": "day_date" },
                { "data": "invoice_count", "className": "text-right" },
                { "data": "net_total", "className": "text-right", "render": function(d){ return fm(d); } },
                { "data": "expected_cash", "className": "text-right", "render": function(d){ return fm(d); } },
                { "data": "counted_cash", "className": "text-right", "render": function(d){ return fm(d); } },
                { "data": "difference_total", "className": "text-right", "render": function(d){ return diffHtml(d); } },
                { "data": "done_by" },
                { "data": "insertdatetime" }
            ]
        });

        loadPreview();
    });
</script>
<?php include "include/footer.php"; ?>