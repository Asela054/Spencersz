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
                            <div class="page-header-icon"><i class="fas fa-sliders-h"></i></div>
                            <span>Stock Adjustment</span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2">
                <div class="card">
                    <div class="card-body p-0 p-2">
                        <form action="<?php echo base_url() ?>Stockadjustment/Stockadjustmentinsert" method="post" autocomplete="off" id="adjForm">
                            <div class="row">
                                <div class="col-2">
                                    <label class="small font-weight-bold">Date*</label>
                                    <input type="date" class="form-control form-control-sm" name="adjustment_date" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-2">
                                    <label class="small font-weight-bold">Type*</label>
                                    <select class="form-control form-control-sm" name="adjustment_type" id="adjustment_type" required>
                                        <option value="1">Plus (+)</option>
                                        <option value="2">Minus (-)</option>
                                    </select>
                                </div>
                                <div class="col-2">
                                    <label class="small font-weight-bold">Branch*</label>
                                    <select class="form-control form-control-sm" name="branch_id" id="branch_id" required>
                                        <option value="">Select</option>
                                        <?php foreach($branchlist as $b){ ?>
                                        <option value="<?php echo $b->idtbl_company_branch; ?>"><?php echo $b->branch.' ('.($b->type==1?'Showroom':'Warehouse').')'; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label class="small font-weight-bold">Reason*</label>
                                    <select class="form-control form-control-sm" name="reason" required>
                                        <option value="">Select</option>
                                        <option>Stock count correction</option>
                                        <option>Damaged</option>
                                        <option>Lost / Theft</option>
                                        <option>Found / Excess</option>
                                        <option>Opening balance correction</option>
                                        <option>Other</option>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label class="small font-weight-bold">Remark</label>
                                    <input type="text" class="form-control form-control-sm" name="remark">
                                </div>
                            </div>

                            <hr class="my-2">
                            <div class="row">
                                <div class="col-4">
                                    <label class="small font-weight-bold">Search Product</label>
                                    <input type="text" class="form-control form-control-sm" id="productsearch" placeholder="code / name / model">
                                    <select class="form-control form-control-sm mt-1" id="product_id"></select>
                                </div>
                                <div class="col-3">
                                    <label class="small font-weight-bold">Batch*</label>
                                    <select class="form-control form-control-sm" id="stock_id"></select>
                                </div>
                                <div class="col-3">
                                    <div id="serialBox">
                                        <label class="small font-weight-bold" id="serialLabel">Serial No*</label>
                                        <select class="form-control form-control-sm" id="serial_id"></select>
                                    </div>
                                    <div id="qtyBox" class="d-none">
                                        <label class="small font-weight-bold">Qty*</label>
                                        <input type="number" min="1" step="1" class="form-control form-control-sm" id="qty_input" value="1">
                                    </div>
                                </div>
                                <div class="col-2 text-right">
                                    <label class="small d-block">&nbsp;</label>
                                    <button type="button" class="btn btn-dark btn-sm px-3" id="btnAddLine"><i class="fas fa-plus"></i>&nbsp;Add</button>
                                </div>
                            </div>

                            <table class="table table-bordered table-sm mt-2" id="tblLines">
                                <thead>
                                    <tr><th>Product</th><th>Serial No</th><th class="text-right">Qty</th><th>Batch</th><th class="text-right">Cost</th><th></th></tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <input type="hidden" name="items_json" id="items_json">
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary btn-sm px-4" <?php if($addcheck==0){echo 'disabled';} ?>><i class="far fa-save"></i>&nbsp;Send for Approval</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mt-2">
                    <div class="card-body p-0 p-2">
                        <div class="scrollbar pb-3" id="style-2">
                            <table class="table table-bordered table-striped table-sm nowrap" id="tbladj">
                                <thead>
                                    <tr>
                                        <th>#</th><th>Adj No</th><th>Date</th><th>Type</th><th>Branch</th><th>Reason</th>
                                        <th class="text-right">Qty</th><th class="text-right">Value</th><th>Status</th><th>Created By</th><th class="text-right">Actions</th>
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

<!-- view / approve modal -->
<div class="modal fade" id="adjModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header p-2"><h5 class="modal-title" id="adjTitle"></h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body p-2">
                <div id="adjHeader" class="small mb-2"></div>
                <table class="table table-bordered table-sm" id="tblView">
                    <thead><tr><th>Product</th><th>Serial No</th><th>Batch</th><th class="text-right">Batch Stock Now</th><th class="text-right">Cost</th></tr></thead>
                    <tbody></tbody>
                </table>
                <div id="approveBox" class="d-none">
                    <input type="text" class="form-control form-control-sm" id="approveRemark" placeholder="Approval remark">
                </div>
            </div>
            <div class="modal-footer p-2" id="approveButtons"></div>
        </div>
    </div>
</div>

<?php include "include/footerscripts.php"; ?>
<script>
$(document).ready(function() {
    var addcheck='<?php echo $addcheck; ?>';
    var deletecheck='<?php echo $deletecheck; ?>';
    var approvecheck='<?php echo $approvecheck; ?>';
    var items=[];
    var currentID=0;

    function isPlus(){ return $('#adjustment_type').val()==1; }

    // ---- fill hidden items_json before the form is submitted
    $('#adjForm').on('submit', function(e){
        if(items.length===0){
            e.preventDefault();
            alert('Add at least one item');
            return false;
        }
        $('#items_json').val(JSON.stringify(items));
    });

    function toggleMode(){
        $('#serialLabel').text(isPlus() ? 'Serial No* (out of stock - will be added back)' : 'Serial No* (in stock - will be removed)');
        items=[]; renderLines(); loadBatches();
    }

    // ---- product search
    var t;
    $('#productsearch').on('keyup', function(){
        clearTimeout(t);
        var q=$(this).val();
        t=setTimeout(function(){
            if(q.length<2){return;}
            $.post('<?php echo base_url() ?>Stockadjustment/Getproducts',{q:q},function(r){
                var list=JSON.parse(r), h='';
                $.each(list,function(i,p){
                    h+='<option value="'+p.idtbl_product+'" data-serial="'+p.has_serial+'">'+p.product_code+' - '+p.product_name+' '+(p.model_no||'')+'</option>';
                });
                $('#product_id').html(h).trigger('change');
            });
        },300);
    });

    function hasSerial(){ return $('#product_id option:selected').data('serial')==1; }

    // ---- product -> batches
    function loadBatches(){
        var pid=$('#product_id').val(), bid=$('#branch_id').val();
        $('#stock_id').html(''); $('#serial_id').html('');
        if(!pid||!bid){return;}
        $.post('<?php echo base_url() ?>Stockadjustment/Getbatches',{product_id:pid,branch_id:bid},function(r){
            var list=JSON.parse(r), h='';
            $.each(list,function(i,s){ h+='<option value="'+s.idtbl_stock+'" data-batch="'+s.batchno+'">'+s.batchno+' | stock '+s.qty+' | cost '+s.costunitprice+'</option>'; });
            if(h===''){h='<option value="">No batch in this branch</option>';}
            $('#stock_id').html(h).trigger('change');
        });
    }

    function loadSerials(){
        var sid=$('#stock_id').val();
        $('#serial_id').html('');
        if(!hasSerial()){ $('#serialBox').addClass('d-none'); $('#qtyBox').removeClass('d-none'); return; }
        $('#qtyBox').addClass('d-none'); $('#serialBox').removeClass('d-none');
        if(!sid){return;}
        $.post('<?php echo base_url() ?>Stockadjustment/Getserials',{stock_id:sid,branch_id:$('#branch_id').val(),adjustment_type:$('#adjustment_type').val()},function(r){
            var list=JSON.parse(r), h='';
            $.each(list,function(i,s){ h+='<option value="'+s.idtbl_product_serial+'" data-sn="'+s.serialno+'" data-cost="'+s.costunitprice+'">'+s.serialno+'</option>'; });
            if(h===''){h='<option value="">No serial available for this batch</option>';}
            $('#serial_id').html(h);
        });
    }
    $('#product_id').on('change', loadBatches);
    $('#stock_id').on('change', loadSerials);
    $('#branch_id').on('change', function(){ items=[]; renderLines(); loadBatches(); });
    $('#adjustment_type').on('change', toggleMode);

    // ---- add line (one serial = one line, qty always 1)
    $('#btnAddLine').on('click', function(){
        var pid=$('#product_id').val(), sid=$('#stock_id').val();
        if(!pid||!sid){alert('Select product and batch');return;}
        var b=$('#stock_id option:selected'), it;
        if(hasSerial()){
            var serid=$('#serial_id').val();
            if(!serid){alert('Select a serial number');return;}
            for(var i=0;i<items.length;i++){ if(String(items[i].serial_id)===String(serid)){alert('Serial already added');return;} }
            var o=$('#serial_id option:selected');
            it={product_id:pid,stock_id:sid,serial_id:serid,serialno:o.data('sn'),qty:1,
                product:$('#product_id option:selected').text(),batch:b.data('batch'),cost:o.data('cost')};
            $('#serial_id option:selected').remove();
        }else{
            var q=parseFloat($('#qty_input').val());
            if(!(q>0)){alert('Enter a valid quantity');return;}
            for(var j=0;j<items.length;j++){ if(String(items[j].stock_id)===String(sid)&&!items[j].serial_id){alert('Batch already added');return;} }
            it={product_id:pid,stock_id:sid,serial_id:0,serialno:'-',qty:q,
                product:$('#product_id option:selected').text(),batch:b.data('batch'),cost:b.data('cost')};
        }
        items.push(it); renderLines();
    });
    function renderLines(){
        var h='';
        $.each(items,function(i,it){
            h+='<tr><td>'+$('<div>').text(it.product).html()+'</td><td><b>'+$('<div>').text(it.serialno).html()+'</b></td><td class="text-right">'+it.qty+'</td><td>'+$('<div>').text(it.batch).html()+'</td><td class="text-right">'+it.cost+'</td><td><button type="button" class="btn btn-danger btn-sm btnRm" data-i="'+i+'"><i class="fas fa-times"></i></button></td></tr>';
        });
        $('#tblLines tbody').html(h);
    }
    $('#tblLines').on('click','.btnRm',function(){ items.splice($(this).data('i'),1); renderLines(); loadSerials(); });

    // ---- list
    $('#tbladj').DataTable({
        "destroy": true, "processing": true, "serverSide": true,
        dom: "<'row'<'col-sm-5'B><'col-sm-2'l><'col-sm-5'f>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        responsive: true,
        lengthMenu: [[10,25,50,-1],[10,25,50,'All']],
        "buttons": [
            { extend: 'csv', className: 'btn btn-success btn-sm', title: 'Stock Adjustment', text: '<i class="fas fa-file-csv mr-2"></i> CSV' },
            { extend: 'pdf', className: 'btn btn-danger btn-sm', title: 'Stock Adjustment', text: '<i class="fas fa-file-pdf mr-2"></i> PDF' }
        ],
        ajax: { url: "<?php echo base_url() ?>scripts/stockadjustmentlist.php", type: "POST" },
        "order": [[0,"desc"]],
        "columns": [
            { "data": null, "render": function(d,t,f,m){ return m.settings._iRecordsDisplay - m.row; } },
            { "data": "adjustment_no" },
            { "data": "adjustment_date" },
            { "data": "adjustment_type", "render": function(d){ return d==1?'<span class="badge badge-success">Plus</span>':'<span class="badge badge-danger">Minus</span>'; } },
            { "data": "branch" },
            { "data": "reason" },
            { "data": "total_qty", "className": "text-right" },
            { "data": "total_value", "className": "text-right", "render": function(d){ return parseFloat(d).toFixed(2); } },
            { "data": "approve_status", "render": function(d){
                if(d==1){return '<span class="badge badge-success">Approved</span>';}
                if(d==2){return '<span class="badge badge-secondary">Rejected</span>';}
                return '<span class="badge badge-warning">Pending</span>'; } },
            { "data": "name" },
            { "targets": -1, "className": 'text-right', "data": null, "render": function(d,t,f){
                var b='<button class="btn btn-primary btn-sm btnView mr-1" id="'+f['idtbl_stock_adjustment']+'" title="View"><i class="fas fa-eye"></i></button>';
                if(f['approve_status']==0){
                    b+='<a href="<?php echo base_url() ?>Stockadjustment/Stockadjustmentstatus/'+f['idtbl_stock_adjustment']+'/3" onclick="return confirm(\'Remove this pending adjustment?\')" class="btn btn-danger btn-sm '+(deletecheck!=1?'d-none':'')+'"><i class="fas fa-trash-alt"></i></a>';
                }
                return b; } }
        ],
        drawCallback: function(){ $('[data-toggle="tooltip"]').tooltip(); }
    });

    // ---- view / approve
    $('#tbladj tbody').on('click','.btnView',function(){
        currentID=$(this).attr('id');
        $.post('<?php echo base_url() ?>Stockadjustment/Stockadjustmentview',{recordID:currentID},function(r){
            var o=JSON.parse(r), h=o.header, rows='';
            $('#adjTitle').text(h.adjustment_no+' ('+(h.adjustment_type==1?'Plus':'Minus')+')');
            $('#adjHeader').html('Date: <b>'+h.adjustment_date+'</b> | Branch: <b>'+h.branch+'</b> | Reason: <b>'+h.reason+'</b> | Created by: <b>'+h.created_by+'</b><br>Remark: '+(h.remark||'-')+
                (h.approve_status!=0?'<br>Processed by <b>'+h.approved_by_name+'</b> on '+h.approve_datetime+' - '+(h.approve_remark||''):''));
            $.each(o.lines,function(i,l){
                rows+='<tr><td>'+l.product_code+' - '+l.product_name+'</td><td><b>'+l.serialno+'</b></td><td>'+l.batchno+'</td><td class="text-right">'+l.stock_qty+'</td><td class="text-right">'+parseFloat(l.line_value).toFixed(2)+'</td></tr>';
            });
            $('#tblView tbody').html(rows);
            var btns='<button class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>';
            if(h.approve_status==0 && approvecheck==1){
                $('#approveBox').removeClass('d-none');
                btns+='<button class="btn btn-warning btn-sm btnDecide" data-action="2">Reject</button><button class="btn btn-success btn-sm btnDecide" data-action="1">Approve</button>';
            }else{ $('#approveBox').addClass('d-none'); }
            $('#approveButtons').html(btns);
            $('#adjModal').modal('show');
        });
    });
    $('#approveButtons').on('click','.btnDecide',function(){
        var a=$(this).data('action');
        if(!confirm(a==1?'Approve and update stock?':'Reject this adjustment?')){return;}
        $.post('<?php echo base_url() ?>Stockadjustment/Stockadjustmentapprove',{recordID:currentID,action:a,remark:$('#approveRemark').val()},function(){ location.reload(); });
    });

    toggleMode();
});
</script>
<?php include "include/footer.php"; ?>