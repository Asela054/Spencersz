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
                            <div class="page-header-icon"><i class="fas fa-truck"></i></div>
                            <span>Pending Deliveries</span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2">
                <div class="card">
                    <div class="card-body p-0 p-2">
                        <div class="row align-items-end mb-2">
                            <div class="col-12 col-md-3">
                                <label class="small font-weight-bold mb-1">Show</label>
                                <select id="f_view" class="form-control form-control-sm">
                                    <option value="all">All pending</option>
                                    <option value="ready">Ready (item is in stock)</option>
                                    <option value="overdue">Overdue</option>
                                    <option value="week">Due in 7 days</option>
                                </select>
                            </div>
                        </div>
                        <div class="scrollbar pb-3" id="style-2">
                            <table class="table table-bordered table-striped table-sm nowrap w-100" id="tblpending">
                                <thead>
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Customer</th>
                                        <th>Phone</th>
                                        <th>Product</th>
                                        <th class="text-right">Pending</th>
                                        <th class="text-right">In stock</th>
                                        <th>Delivery date</th>
                                        <th>Status</th>
                                        <th class="text-right">Action</th>
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

<!-- deliver modal -->
<div class="modal fade" id="deliverymodal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-truck mr-2"></i>Deliver</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="small mb-2"><b id="dv_title"></b><br><span class="text-muted" id="dv_sub"></span></p>
                <div class="form-group mb-2">
                    <label class="small font-weight-bold">Delivered date*</label>
                    <input type="date" id="dv_date" class="form-control form-control-sm" max="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group mb-2" id="dv_qtyrow">
                    <label class="small font-weight-bold">Quantity to deliver* <span class="text-muted font-weight-normal" id="dv_max"></span></label>
                    <input type="number" id="dv_qty" class="form-control form-control-sm" min="1">
                </div>
                <div class="form-group mb-0" id="dv_serialrow">
                    <label class="small font-weight-bold">Serial number* (oldest stock first)</label>
                    <div id="dv_serials" style="max-height:200px;overflow:auto;" class="border rounded p-1"></div>
                </div>
                <div id="dv_msg" class="text-danger small mt-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm border px-3" data-dismiss="modal">Cancel</button>
                <button type="button" id="btndeliver" class="btn btn-primary btn-sm px-4"><i class="fas fa-check mr-1"></i>Deliver</button>
            </div>
        </div>
    </div>
</div>

<?php include "include/footerscripts.php"; ?>
<script>
    $(document).ready(function() {
        var editcheck='<?php echo $editcheck; ?>';
        var rows=[], current=null;

        function esc(s){ return $('<div>').text(s === null || s === undefined ? '' : s).html(); }

        var table=$('#tblpending').DataTable({
            "destroy": true,
            data: [],
            responsive: true,
            "order": [[ 6, "asc" ]],
            "columns": [
                { "data": "invoice_no" },
                { "data": "customer_name", "render": function(d){ return esc(d); } },
                { "data": "phone", "render": function(d){ return esc(d); } },
                { "data": "product_name", "render": function(d, t, r){ return esc(d)+' <small class="text-muted">'+esc(r.product_code)+'</small>'; } },
                { "data": "remaining", "className": "text-right" },
                { "data": "in_stock", "className": "text-right" },
                { "data": "delivery_date" },
                { "data": null, "render": function(d, t, r){
                    var h='', dl=parseInt(r.days_left, 10);
                    if(dl<0){ h+='<span class="badge badge-danger">Overdue '+(-dl)+' d</span> '; }
                    else if(dl<=7){ h+='<span class="badge badge-warning">Due in '+dl+' d</span> '; }
                    else{ h+='<span class="badge badge-secondary">In '+dl+' d</span> '; }
                    if(parseFloat(r.in_stock)>=parseFloat(r.remaining)){ h+='<span class="badge badge-success">Ready</span>'; }
                    return h;
                } },
                { "data": "id", "className": "text-right", "orderable": false, "render": function(d){
                    return '<button class="btn btn-primary btn-sm btndeliver '+(editcheck!=1 ? 'd-none' : '')+'" data-id="'+d+'"><i class="fas fa-truck"></i> Deliver</button>';
                } }
            ]
        });

        function render(){
            var v=$('#f_view').val();
            var list=rows.filter(function(r){
                if(v==='ready'){ return parseFloat(r.in_stock)>=parseFloat(r.remaining); }
                if(v==='overdue'){ return parseInt(r.days_left, 10)<0; }
                if(v==='week'){ return parseInt(r.days_left, 10)<=7; }
                return true;
            });
            table.clear().rows.add(list).draw();
        }

        function load(){
            $.ajax({
                type: "POST",
                url: '<?php echo base_url() ?>Pendingdelivery/Getpending',
                success: function(result){ rows=JSON.parse(result); render(); }
            });
        }

        $('#f_view').on('change', render);

        $('#tblpending tbody').on('click', '.btndeliver', function(){
            var id=$(this).attr('data-id');
            current=null;
            $.each(rows, function(i, r){ if(String(r.id)===String(id)){ current=r; } });
            if(!current){ return; }

            $('#dv_title').text(current.product_name);
            $('#dv_sub').text('Invoice '+current.invoice_no+' - '+current.customer_name+' - asked for '+current.delivery_date);
            $('#dv_date').val('<?php echo date('Y-m-d'); ?>');
            $('#dv_msg').text('');

            if(parseInt(current.has_serial, 10)===1){
                $('#dv_qtyrow').hide();
                $('#dv_serialrow').show();
                $('#dv_serials').html('<div class="text-muted small p-1">Loading...</div>');
                $.ajax({
                    type: "POST", data: { detail_id: current.id },
                    url: '<?php echo base_url() ?>Pendingdelivery/Getdeliveryserials',
                    success: function(result){
                        var list=JSON.parse(result), h='';
                        $.each(list, function(i, s){
                            h+='<label class="d-flex align-items-center mb-1" style="cursor:pointer;">'+
                               '<input type="radio" name="dv_serial" class="mr-2" value="'+s.id+'" '+(i===0 ? 'checked' : '')+'>'+
                               '<span class="font-monospace">'+esc(s.serialno)+'</span>'+
                               '<small class="text-muted ml-2">'+esc(s.grn_no)+' / '+esc(s.grndate)+(i===0 ? ' - oldest' : '')+'</small></label>';
                        });
                        $('#dv_serials').html(h || '<div class="text-danger small p-1">No serial in stock for this product.</div>');
                    }
                });
            }else{
                $('#dv_serialrow').hide();
                $('#dv_qtyrow').show();
                var max=parseInt(current.remaining, 10);
                $('#dv_qty').attr('max', max).val(Math.max(1, Math.min(max, Math.floor(parseFloat(current.in_stock)||0))));
                $('#dv_max').text('(pending '+max+', in stock '+current.in_stock+')');
            }
            $('#deliverymodal').modal('show');
        });

        $('#btndeliver').on('click', function(){
            if(!current){ return; }
            var d={ detail_id: current.id, delivered_date: $('#dv_date').val() };
            if(parseInt(current.has_serial, 10)===1){
                var sid=$('input[name=dv_serial]:checked').val();
                if(!sid){ $('#dv_msg').text('Select the serial number.'); return; }
                d.serial_ids=[sid];
            }else{
                d.qty=$('#dv_qty').val();
            }
            if(!d.delivered_date){ $('#dv_msg').text('Enter the delivered date.'); return; }

            $('#btndeliver').prop('disabled', true);
            $.ajax({
                type: "POST", dataType: "json", data: d,
                url: '<?php echo base_url() ?>Pendingdelivery/Deliverpending',
                success: function(res){
                    $('#btndeliver').prop('disabled', false);
                    if(!res.ok){ $('#dv_msg').text(res.msg); return; }
                    $('#deliverymodal').modal('hide');
                    window.open('<?php echo base_url() ?>Pendingdelivery/Deliverynote/'+res.invoice_id+'/'+res.date, '_blank');
                    load();
                },
                error: function(){ $('#btndeliver').prop('disabled', false); $('#dv_msg').text('Server error. Please try again.'); }
            });
        });

        load();
    });
</script>
<?php include "include/footer.php"; ?>