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
                            <div class="page-header-icon"><i class="fas fa-boxes"></i></div>
                            <span>Combo Pack</span>
                        </h1>
                    </div>
                </div>
            </div>

            <div class="container-fluid mt-2 p-0 p-2">
                <div class="card">
                    <div class="card-body p-0 p-2">
                        <div class="row">
                            <div class="col-12 text-right mb-2">
                                <button type="button" class="btn btn-primary btn-sm" id="btnNewCombo"><i class="fas fa-plus mr-1"></i>New Combo Pack</button>
                                <hr class="my-2">
                            </div>
                            <div class="col-12">
                                <div class="scrollbar pb-3" id="style-2">
                                    <table class="table table-bordered table-striped table-sm nowrap" id="dataTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Code</th>
                                                <th>Combo Name</th>
                                                <th class="text-right">Total Cost</th>
                                                <th class="text-right">Combo Price</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
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

<!-- ============ ADD / EDIT MODAL ============ -->
<div class="modal fade" id="modalCombo" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalComboTitle">New Combo Pack</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="recordOption" value="1">
                <input type="hidden" id="recordID" value="">

                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label class="small font-weight-bold">Combo Name*</label>
                        <input type="text" class="form-control form-control-sm" id="combo_name" placeholder="Combo1 - AC + Fan">
                    </div>
                    <div class="form-group col-md-4">
                        <label class="small font-weight-bold">Description</label>
                        <input type="text" class="form-control form-control-sm" id="description">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small font-weight-bold">Combo Price (Rs.)*</label>
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm text-right" id="combo_price">
                    </div>
                </div>

                <hr class="my-2">

                <div class="form-row align-items-end">
                    <div class="form-group col-md-8">
                        <label class="small font-weight-bold">Add Component Product</label>
                        <select class="form-control form-control-sm" id="sel_product" style="width:100%">
                            <option></option>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label class="small font-weight-bold">Qty</label>
                        <input type="number" min="1" value="1" class="form-control form-control-sm" id="sel_qty">
                    </div>
                    <div class="form-group col-md-2">
                        <button type="button" class="btn btn-success btn-sm btn-block" id="btnAddItem"><i class="fas fa-plus mr-1"></i>Add</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tblItems">
                        <thead class="thead-light">
                            <tr>
                                <th>Product</th>
                                <th style="width:80px">Qty</th>
                                <th class="text-right" style="width:110px">Cost</th>
                                <th class="text-right" style="width:110px">Sell Price</th>
                                <th style="width:120px">Main Warranty (months)</th>
                                <th style="width:340px">Part Warranties (e.g. Compressor 60)</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="row">
                    <div class="col-md-12 text-right small">
                        <span class="mr-3">Total Cost: <b id="t_cost">0.00</b></span>
                        <span class="mr-3">Normal Price Total: <b id="t_list">0.00</b></span>
                        <span class="mr-3">Combo Price: <b id="t_price">0.00</b></span>
                        <span>Customer Saves: <b id="t_save">0.00</b></span>
                        <div id="priceWarn" class="text-danger font-weight-bold mt-1" style="display:none">Combo price is below total cost.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSaveCombo"><i class="fas fa-save mr-1"></i>Save</button>
            </div>
        </div>
    </div>
</div>

<!-- ============ VIEW MODAL ============ -->
<div class="modal fade" id="modalView" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewTitle">Combo</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row small mb-2">
                    <div class="col-md-4">Code: <b id="v_code"></b></div>
                    <div class="col-md-4">Combo Price: <b id="v_price"></b></div>
                    <div class="col-md-4">Available Packs: <b id="v_avail"></b></div>
                    <div class="col-12 mt-1" id="v_desc"></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tblViewItems">
                        <thead class="thead-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Price in Combo</th>
                                <th class="text-center">Main Warranty</th>
                                <th>Part Warranties</th>
                                <th class="text-center">Stock</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "include/footerscripts.php"; ?>
<script>
$(document).ready(function(){

    var BASE = '<?php echo base_url(); ?>';
    var items = [];          // component lines

    function esc(s){ return $('<div>').text(s == null ? '' : s).html(); }
    function money(n){ return (parseFloat(n) || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}); }

    /* ---------- list ---------- */
    $('#dataTable').DataTable({
        "destroy": true,
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: "<?php echo base_url() ?>scripts/combolist.php",
            type: "POST"
        },
        "order": [[0, "desc"]],
        "columns": [
            { "data": "idtbl_combo" },
            { "data": "product_code" },
            { "data": "combo_name" },
            { "data": "total_cost",  "className": "text-right", "render": function(d){ return money(d); } },
            { "data": "combo_price", "className": "text-right", "render": function(d){ return money(d); } },
            {
                "targets": -1,
                "className": "text-center",
                "data": null,
                "orderable": false,
                "render": function(data, type, full){
                    var id = full['idtbl_combo'];
                    var b = '';
                    b += '<button class="btn btn-outline-dark btn-sm mr-1 btnView" data-id="'+id+'" title="View"><i class="fas fa-eye"></i></button>';
                    b += '<button class="btn btn-outline-primary btn-sm mr-1 btnEdit" data-id="'+id+'" title="Edit"><i class="fas fa-pen"></i></button>';
                    if(full['status'] == 1){
                        b += '<a href="'+BASE+'Combo/Combostatus/'+id+'/2" onclick="return confirm(\'Are you sure you want to deactivate this combo?\');" class="btn btn-outline-success btn-sm mr-1" title="Deactivate"><i class="fas fa-check"></i></a>';
                    }else{
                        b += '<a href="'+BASE+'Combo/Combostatus/'+id+'/1" onclick="return confirm(\'Are you sure you want to activate this combo?\');" class="btn btn-outline-warning btn-sm mr-1" title="Activate"><i class="fas fa-times"></i></a>';
                    }
                    b += '<a href="'+BASE+'Combo/Combostatus/'+id+'/3" onclick="return confirm(\'Are you sure you want to remove this combo?\');" class="btn btn-outline-danger btn-sm" title="Remove"><i class="fas fa-trash-alt"></i></a>';
                    return b;
                }
            }
        ]
    });

    $('#sel_product').select2({
        dropdownParent: $('#modalCombo'),
        width: '100%',
        placeholder: 'Search product code / name / model / serial no',
        allowClear: true,
        ajax: {
            url: BASE + 'Combo/Searchproducts',
            type: 'POST',
            dataType: 'json',
            delay: 250,
            data: function(params){
                return { q: params.term || '', page: params.page || 1 };
            },
            processResults: function(res, params){
                params.page = params.page || 1;
                return res;
            }
        },
        templateResult: function(item){
            if(item.loading){ return item.text; }
            var $r = $('<div></div>').text(item.text);
            if(item.serials){
                $r.append($('<div class="small text-muted"></div>').text('Serial: ' + item.serials));
            }
            return $r;
        },
        templateSelection: function(item){ return item.text; }
    });

    /* ---------- items table ---------- */
    function renderItems(){
        var html = '';
        $.each(items, function(i, it){
            var parts = '';
            $.each(it.parts, function(j, pt){
                parts += '<span class="badge badge-info mr-1 mb-1">'+esc(pt.name)+' - '+pt.months+' m '
                       + '<a href="#" class="text-white btnDelPart" data-i="'+i+'" data-j="'+j+'">&times;</a></span>';
            });
            parts += '<div class="form-inline mt-1">'
                   + '<input type="text" class="form-control form-control-sm mr-1 partName" placeholder="Part" style="width:130px">'
                   + '<input type="number" min="1" class="form-control form-control-sm mr-1 partMonths" placeholder="Months" style="width:80px">'
                   + '<button type="button" class="btn btn-outline-secondary btn-sm btnAddPart" data-i="'+i+'"><i class="fas fa-plus"></i></button>'
                   + '</div>';

            html += '<tr data-i="'+i+'">'
                  + '<td>'+esc(it.product_code+' - '+it.product_name)+'</td>'
                  + '<td><input type="number" min="1" class="form-control form-control-sm itemQty" value="'+it.qty+'"></td>'
                  + '<td class="text-right">'+money(it.cost_price)+'</td>'
                  + '<td class="text-right">'+money(it.selling_price)+'</td>'
                  + '<td><input type="number" min="0" class="form-control form-control-sm itemWar" value="'+it.warranty_months+'"></td>'
                  + '<td>'+parts+'</td>'
                  + '<td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm btnDelItem" data-i="'+i+'"><i class="fas fa-times"></i></button></td>'
                  + '</tr>';
        });
        if(items.length == 0){
            html = '<tr><td colspan="7" class="text-center text-muted">No components added</td></tr>';
        }
        $('#tblItems tbody').html(html);
        calcTotals();
    }

    function calcTotals(){
        var cost = 0, list = 0;
        $.each(items, function(i, it){
            cost += it.cost_price * it.qty;
            list += it.selling_price * it.qty;
        });
        var price = parseFloat($('#combo_price').val()) || 0;
        $('#t_cost').text(money(cost));
        $('#t_list').text(money(list));
        $('#t_price').text(money(price));
        $('#t_save').text(money(Math.max(0, list - price)));
        $('#priceWarn').toggle(price > 0 && items.length > 0 && price < cost);
    }

    $('#combo_price').on('input', calcTotals);

    // add component
    $('#btnAddItem').on('click', function(){
        var sel = $('#sel_product').select2('data')[0];
        var qty = parseInt($('#sel_qty').val()) || 0;
        if(!sel || !sel.id){ alert('Select a product'); return; }
        if(qty < 1){ alert('Enter a valid quantity'); return; }
        for(var k = 0; k < items.length; k++){
            if(items[k].product_id == sel.id){ alert('This product is already added'); return; }
        }
        $.ajax({
            url: BASE + 'Combo/Productwarranty',
            type: 'POST',
            dataType: 'json',
            data: { productID: sel.id },
            success: function(w){
                items.push({
                    product_id: sel.id,
                    product_code: sel.product_code,
                    product_name: sel.product_name,
                    qty: qty,
                    cost_price: parseFloat(sel.cost_price),
                    selling_price: parseFloat(sel.selling_price),
                    warranty_months: parseInt(w.warranty_months) || 0,
                    parts: $.map(w.parts || [], function(x){ return { name: x.name, months: parseInt(x.months) }; })
                });
                $('#sel_product').val(null).trigger('change');
                $('#sel_qty').val(1);
                renderItems();
            }
        });
    });

    $('#tblItems').on('input change', '.itemQty', function(){
        var i = $(this).closest('tr').data('i');
        items[i].qty = Math.max(1, parseInt($(this).val()) || 1);
        calcTotals();
    });
    $('#tblItems').on('input change', '.itemWar', function(){
        var i = $(this).closest('tr').data('i');
        items[i].warranty_months = Math.max(0, parseInt($(this).val()) || 0);
    });
    $('#tblItems').on('click', '.btnDelItem', function(){
        items.splice($(this).data('i'), 1);
        renderItems();
    });
    $('#tblItems').on('click', '.btnAddPart', function(){
        var i = $(this).data('i');
        var row = $(this).closest('tr');
        var name = $.trim(row.find('.partName').val());
        var months = parseInt(row.find('.partMonths').val()) || 0;
        if(name == '' || months < 1){ alert('Enter part name and months'); return; }
        items[i].parts.push({ name: name, months: months });
        renderItems();
    });
    $('#tblItems').on('click', '.btnDelPart', function(e){
        e.preventDefault();
        items[$(this).data('i')].parts.splice($(this).data('j'), 1);
        renderItems();
    });
    $('#tblItems').on('keypress', '.partName, .partMonths', function(e){
        if(e.which == 13){
            e.preventDefault();
            $(this).closest('td').find('.btnAddPart').click();
        }
    });

    /* ---------- new / edit ---------- */
    function resetForm(){
        $('#recordOption').val(1);
        $('#recordID').val('');
        $('#combo_name, #description, #combo_price').val('');
        $('#sel_product').val(null).trigger('change');
        $('#sel_qty').val(1);
        items = [];
        renderItems();
    }

    $('#btnNewCombo').on('click', function(){
        resetForm();
        $('#modalComboTitle').text('New Combo Pack');
        $('#modalCombo').modal('show');
    });

    $('#dataTable').on('click', '.btnEdit', function(){
        var id = $(this).data('id');
        resetForm();
        $.ajax({
            url: BASE + 'Combo/Comboedit',
            type: 'POST',
            dataType: 'json',
            data: { recordID: id },
            success: function(c){
                if(c.status != 1){ alert('Combo not found'); return; }
                $('#recordOption').val(2);
                $('#recordID').val(c.id);
                $('#combo_name').val(c.combo_name);
                $('#description').val(c.description);
                $('#combo_price').val(c.combo_price);
                items = $.map(c.items, function(x){
                    return {
                        product_id: x.product_id,
                        product_code: x.product_code,
                        product_name: x.product_name,
                        qty: parseInt(x.qty),
                        cost_price: parseFloat(x.cost_price),
                        selling_price: parseFloat(x.selling_price),
                        warranty_months: parseInt(x.warranty_months),
                        parts: $.map(x.parts || [], function(p){ return { name: p.name, months: parseInt(p.months) }; })
                    };
                });
                renderItems();
                $('#modalComboTitle').text('Edit Combo Pack - ' + c.code);
                $('#modalCombo').modal('show');
            }
        });
    });

    /* ---------- save ---------- */
    $('#btnSaveCombo').on('click', function(){
        var name = $.trim($('#combo_name').val());
        var price = parseFloat($('#combo_price').val()) || 0;

        // pick up part warranties that were typed but not added with the + button
        var pendingError = false;
        $('#tblItems tbody tr').each(function(){
            var i = $(this).data('i');
            if(i === undefined){ return; }

            var pn = $.trim($(this).find('.partName').val());
            var pm = parseInt($(this).find('.partMonths').val()) || 0;

            if(pn !== '' || pm > 0){
                if(pn === '' || pm < 1){
                    alert('Complete the part warranty (name and months) for ' + items[i].product_name);
                    pendingError = true;
                    return false;   // stop the loop
                }
                items[i].parts.push({ name: pn, months: pm });
                $(this).find('.partName, .partMonths').val('');
            }
        });
        if(pendingError){ return; }

        if(name == ''){ alert('Enter the combo name'); return; }
        if(price <= 0){ alert('Enter the combo price'); return; }
        if(items.length < 2){ alert('Add at least 2 component products'); return; }

        var payload = $.map(items, function(it){
            return {
                product_id: it.product_id,
                qty: it.qty,
                warranty_months: it.warranty_months,
                parts: it.parts
            };
        });

        var btn = $(this).prop('disabled', true);
        $.ajax({
            url: BASE + 'Combo/Comboinsertupdate',
            type: 'POST',
            dataType: 'json',
            data: {
                recordOption: $('#recordOption').val(),
                recordID: $('#recordID').val(),
                combo_name: name,
                description: $.trim($('#description').val()),
                combo_price: price,
                items: JSON.stringify(payload)
            },
            success: function(res){
                if(res.status == 1){
                    $('#modalCombo').modal('hide');
                    location.reload();      // flashdata message is shown after reload
                }else{
                    alert(res.msg);
                    btn.prop('disabled', false);
                }
            },
            error: function(){
                alert('Server error, please try again');
                btn.prop('disabled', false);
            }
        });
    });
    /* ---------- view ---------- */
    $('#dataTable').on('click', '.btnView', function(){
        $.ajax({
            url: BASE + 'Combo/Comboview',
            type: 'POST',
            dataType: 'json',
            data: { recordID: $(this).data('id') },
            success: function(c){
                if(c.status != 1){ alert('Combo not found'); return; }
                $('#viewTitle').text(c.combo_name);
                $('#v_code').text(c.code);
                $('#v_price').text('Rs. ' + money(c.combo_price));
                $('#v_avail').text(c.combos_available);
                $('#v_desc').text(c.description || '');

                var html = '';
                $.each(c.items, function(i, it){
                    var parts = '';
                    $.each(it.parts || [], function(j, p){
                        parts += '<span class="badge badge-info mr-1">'+esc(p.name)+' - '+p.months+' m</span>';
                    });
                    var low = it.stock < it.qty;
                    html += '<tr>'
                          + '<td>'+esc(it.product_code+' - '+it.product_name)+'</td>'
                          + '<td class="text-center">'+it.qty+'</td>'
                          + '<td class="text-right">'+money(it.unit_price)+'</td>'
                          + '<td class="text-center">'+it.warranty_months+' m</td>'
                          + '<td>'+(parts || '-')+'</td>'
                          + '<td class="text-center '+(low ? 'text-danger font-weight-bold' : '')+'">'+it.stock+'</td>'
                          + '</tr>';
                });
                $('#tblViewItems tbody').html(html);
                $('#modalView').modal('show');
            }
        });
    });

    renderItems();
});
</script>
<?php include "include/footer.php"; ?>