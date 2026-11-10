<?php 
include "include/header.php";  
include "include/topnavbar.php"; 
?>
<style>
    .warranty-card{
        border:1px solid #e3e6ef;
        border-radius:10px;
        background:#fff;
        box-shadow:0 2px 8px rgba(30,40,80,.06);
        overflow:hidden;
    }
    .warranty-card-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        padding:10px 14px;
        background:#f6f7fb;
        border-bottom:1px solid #e3e6ef;
    }
    .warranty-title{
        font-size:.85rem;
        font-weight:700;
        color:#2d3250;
        letter-spacing:.2px;
    }
    .warranty-title i{ color:#c0102f; }
    .warranty-count{
        background:#e9ecf5;
        color:#4a5170;
        font-weight:600;
        padding:5px 10px;
    }
    .warranty-card-body{ padding:14px; }
    .warranty-label{
        display:block;
        font-size:.75rem;
        font-weight:700;
        color:#5b6280;
        text-transform:uppercase;
        letter-spacing:.4px;
        margin-bottom:6px;
    }
    .warranty-card .input-group-text{
        font-size:.75rem;
        background:#f6f7fb;
        color:#5b6280;
    }
    .btn-add-warranty{
        font-size:.75rem;
        padding:2px 10px;
        border-radius:20px;
    }
    .warranty-empty{
        text-align:center;
        padding:12px 10px;
        border:1px dashed #cfd4e4;
        border-radius:8px;
        background:#fafbfd;
        font-size:.78rem;
        color:#8a90a8;
    }
    .warranty-empty span{ font-size:.72rem; margin-top:2px; }
    .warranty-row{
        display:flex;
        align-items:center;
        gap:8px;
        padding:8px;
        margin-bottom:8px;
        border:1px solid #e3e6ef;
        border-radius:8px;
        background:#fafbfd;
    }
    .warranty-row .w-name{ flex:1; }
    .warranty-row .w-months-group{ width:130px; flex:none; }
    .warranty-row .btnRemoveWarranty{
        flex:none;
        width:30px;
        height:30px;
        padding:0;
        border-radius:50%;
        line-height:1;
    }
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
                            <div class="page-header-icon"><i class="fas fa-boxes"></i></div>
                            <span>Product</span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2">
                <div class="card">
                    <div class="card-body p-0 p-2">
                        <div class="row">
                            <div class="col-4">
                                <form action="<?php echo base_url() ?>Product/Productinsertupdate" method="post" autocomplete="off">
                                    <div class="row">
                                        <div class="col-6">
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Product Code*</label>
                                        <input type="text" class="form-control form-control-sm" name="product_code" id="product_code" required>
                                    </div>
                                        </div>
                                        <div class="col-6">
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Barcode</label>
                                        <input type="text" class="form-control form-control-sm" name="barcode" id="barcode">
                                    </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Product Name*</label>
                                        <input type="text" class="form-control form-control-sm" name="product_name" id="product_name" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Model No</label>
                                        <input type="text" class="form-control form-control-sm" name="model_no" id="model_no">
                                    </div>
                                        </div>
                                        <div class="col-6">
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Category</label>
                                        <select class="form-control form-control-sm" name="tbl_category_idtbl_category" id="tbl_category_idtbl_category">
                                            <option value="">Select</option>
                                            <?php foreach($categorylist as $rowcategorylist){ ?>
                                            <option value="<?php echo $rowcategorylist->idtbl_category; ?>"><?php echo $rowcategorylist->category; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Brand</label>
                                        <select class="form-control form-control-sm" name="tbl_brand_idtbl_brand" id="tbl_brand_idtbl_brand">
                                            <option value="">Select</option>
                                            <?php foreach($brandlist as $rowbrandlist){ ?>
                                            <option value="<?php echo $rowbrandlist->idtbl_brand; ?>"><?php echo $rowbrandlist->brand; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                        </div>
                                        <div class="col-6">
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Unit</label>
                                        <select class="form-control form-control-sm" name="tbl_unit_idtbl_unit" id="tbl_unit_idtbl_unit">
                                            <option value="">Select</option>
                                            <?php foreach($unitlist as $rowunitlist){ ?>
                                            <option value="<?php echo $rowunitlist->idtbl_unit; ?>"><?php echo $rowunitlist->unit; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Cost Price*</label>
                                        <input type="number" class="form-control form-control-sm" name="cost_price" id="cost_price" step="0.01" min="0" value="0" required>
                                    </div>
                                        </div>
                                        <div class="col-6">
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Selling Price*</label>
                                        <input type="number" class="form-control form-control-sm" name="selling_price" id="selling_price" step="0.01" min="0" value="0" required>
                                    </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Re-order Level</label>
                                                <input type="number" class="form-control form-control-sm" name="reorder_level" id="reorder_level" min="0" value="0">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Serial Number Tracking*</label>
                                                <select class="form-control form-control-sm" name="has_serial" id="has_serial" required>
                                                    <option value="0">No</option>
                                                    <option value="1" selected>Yes</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ================= WARRANTY ================= -->
                                    <div class="warranty-card mt-3 mb-3">
                                        <div class="warranty-card-header">
                                            <span class="warranty-title"><i class="fas fa-shield-alt"></i>&nbsp;Warranty Details</span>
                                            <span class="badge badge-pill warranty-count" id="warrantyCount">0 extra</span>
                                        </div>

                                        <div class="warranty-card-body">
                                            <!-- Main warranty -->
                                            <label class="warranty-label">Main Warranty (whole product)</label>
                                            <div class="input-group input-group-sm mb-3">
                                                <input type="number" class="form-control" name="warranty_months" id="warranty_months" min="0" value="0">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">Months</span>
                                                </div>
                                            </div>

                                            <!-- Additional warranties -->
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="warranty-label mb-0">Additional Part Warranties</label>
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-add-warranty" id="btnAddWarranty">
                                                    <i class="fas fa-plus"></i>&nbsp;Add Part
                                                </button>
                                            </div>

                                            <div id="warrantyEmpty" class="warranty-empty">
                                                <i class="fas fa-info-circle"></i>&nbsp;No part warranties added.
                                                <span class="d-block">Example: Compressor – 60 months</span>
                                            </div>

                                            <div id="warrantyRows"></div>
                                        </div>
                                    </div>
                                    <!-- =========================================== -->

                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Description</label>
                                        <textarea class="form-control form-control-sm" name="description" id="description" rows="2"></textarea>
                                    </div>
                                    <div class="form-group mt-2 text-right">
                                        <button type="submit" id="submitBtn" class="btn btn-primary btn-sm px-4"
                                         <?php if($addcheck==0){echo 'disabled';} ?>><i class="far fa-save"></i>&nbsp;Add</button>
                                    </div>
                                    <input type="hidden" name="recordOption" id="recordOption" value="1">
                                    <input type="hidden" name="recordID" id="recordID" value="">
                                </form>
                            </div>
                            <div class="col-8">
                                <div class="scrollbar pb-3" id="style-2">
                                    <table class="table table-bordered table-striped table-sm nowrap" id="tblproduct">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Code</th>
                                                <th>Barcode</th>
                                                <th>Product Name</th>
                                                <th>Model</th>
                                                <th>Category</th>
                                                <th>Brand</th>
                                                <th>Cost</th>
                                                <th>Selling</th>
                                                <th>Serial</th>
                                                <th class="text-right">Actions</th>
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
        var addcheck='<?php echo $addcheck; ?>';
        var editcheck='<?php echo $editcheck; ?>';
        var statuscheck='<?php echo $statuscheck; ?>';
        var deletecheck='<?php echo $deletecheck; ?>';
        $('#tbl_category_idtbl_category, #tbl_brand_idtbl_brand, #tbl_unit_idtbl_unit').select2({width: '100%'});

        // ---------- Additional warranty rows ----------
        function refreshWarrantyUI(){
            var count = $('#warrantyRows .warranty-row').length;
            $('#warrantyEmpty').toggle(count === 0);
            $('#warrantyCount').text(count + ' extra');
        }

        function addWarrantyRow(name, months){
            var $row = $(
                '<div class="warranty-row">' +
                    '<input type="text" class="form-control form-control-sm w-name" name="warranty_name[]" placeholder="Part name (e.g. Compressor)">' +
                    '<div class="input-group input-group-sm w-months-group">' +
                        '<input type="number" class="form-control w-months" name="warranty_part_months[]" placeholder="0" min="1">' +
                        '<div class="input-group-append"><span class="input-group-text">Months</span></div>' +
                    '</div>' +
                    '<button type="button" class="btn btn-outline-danger btn-sm btnRemoveWarranty" title="Remove"><i class="fas fa-times"></i></button>' +
                '</div>'
            );
            $row.find('.w-name').val(name || '');
            $row.find('.w-months').val(months || '');
            $('#warrantyRows').append($row);
            refreshWarrantyUI();
        }

        function resetWarranties(){
            $('#warrantyRows').empty();
            refreshWarrantyUI();
        }

        $('#btnAddWarranty').on('click', function(){
            addWarrantyRow();
            $('#warrantyRows .warranty-row:last .w-name').focus();
        });

        $('#warrantyRows').on('click', '.btnRemoveWarranty', function(){
            $(this).closest('.warranty-row').remove();
            refreshWarrantyUI();
        });

        refreshWarrantyUI();

        // ---------- Product table ----------
        $('#tblproduct').DataTable({
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
                { extend: 'csv', className: 'btn btn-success btn-sm', title: 'Product Information', text: '<i class="fas fa-file-csv mr-2"></i> CSV', },
                { extend: 'pdf', className: 'btn btn-danger btn-sm', title: 'Product Information', text: '<i class="fas fa-file-pdf mr-2"></i> PDF', },
                { 
                    extend: 'print', 
                    title: 'Product Information',
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
                url: "<?php echo base_url() ?>scripts/productlist.php",
                type: "POST",
            },
            "order": [[ 0, "desc" ]],
            "columns": [
                {
                    "data": null,
                    "render": function(data, type, full, meta) {
                        return meta.settings._iRecordsDisplay - meta.row;
                    }
                },
                { "data": "product_code" },
                { "data": "barcode" },
                { "data": "product_name" },
                { "data": "model_no" },
                { "data": "category" },
                { "data": "brand" },
                { "data": "cost_price" },
                { "data": "selling_price" },
                {
                    "data": null,
                    "render": function(data, type, full) {
                        return full['has_serial']==1 ? 'Yes' : 'No';
                    }
                },
                {
                    "targets": -1,
                    "className": 'text-right',
                    "data": null,
                    "render": function(data, type, full) {
                        var button='';
                        button+='<button data-toggle="tooltip" data-placement="bottom" title="Edit" class="btn btn-primary btn-sm btnEdit mr-1 ';if(editcheck!=1){button+='d-none';}button+='" id="'+full['idtbl_product']+'"><i class="fas fa-pen"></i></button>';
                        button+='<button data-toggle="tooltip" data-placement="bottom" title="Print Barcode" class="btn btn-secondary btn-sm btnBarcode mr-1" data-id="'+full['idtbl_product']+'"><i class="fas fa-barcode"></i></button>';
                        if(full['status']==1){
                            button+='<a href="<?php echo base_url() ?>Product/Productstatus/'+full['idtbl_product']+'/2"  data-toggle="tooltip" data-placement="bottom" title="Active" onclick="return deactive_confirm()" target="_self" class="btn btn-success btn-sm mr-1 ';if(statuscheck!=1){button+='d-none';}button+='"><i class="fas fa-check"></i></a>';
                        }else{
                            button+='<a href="<?php echo base_url() ?>Product/Productstatus/'+full['idtbl_product']+'/1" data-toggle="tooltip" data-placement="bottom" title="Deactive" onclick="return active_confirm()" target="_self" class="btn btn-warning btn-sm mr-1 ';if(statuscheck!=1){button+='d-none';}button+='"><i class="fas fa-times"></i></a>';
                        }
                        button+='<a href="<?php echo base_url() ?>Product/Productstatus/'+full['idtbl_product']+'/3" data-toggle="tooltip" data-placement="bottom" title="Delete" onclick="return delete_confirm()" target="_self" class="btn btn-danger btn-sm ';if(deletecheck!=1){button+='d-none';}button+='"><i class="fas fa-trash-alt"></i></a>';
                        
                        return button;
                    }
                }
            ],
            drawCallback: function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });

        $('#tblproduct tbody').on('click', '.btnEdit', function() {
            var r = confirm("Are you sure, You want to Edit this ? ");
            if (r == true) {
                var id = $(this).attr('id');
                $.ajax({
                    type: "POST",
                    data: {
                        recordID: id
                    },
                    url: '<?php echo base_url() ?>Product/Productedit',
                    success: function(result) {
                        var obj = JSON.parse(result);
                        $('#recordID').val(obj.id);
                        $('#product_code').val(obj.product_code); 
                        $('#barcode').val(obj.barcode); 
                        $('#product_name').val(obj.product_name); 
                        $('#model_no').val(obj.model_no); 
                        $('#description').val(obj.description); 
                        $('#tbl_category_idtbl_category').val(obj.tbl_category_idtbl_category).trigger('change'); 
                        $('#tbl_brand_idtbl_brand').val(obj.tbl_brand_idtbl_brand).trigger('change'); 
                        $('#tbl_unit_idtbl_unit').val(obj.tbl_unit_idtbl_unit).trigger('change'); 
                        $('#cost_price').val(obj.cost_price); 
                        $('#selling_price').val(obj.selling_price); 
                        $('#reorder_level').val(obj.reorder_level); 
                        $('#has_serial').val(obj.has_serial); 
                        $('#warranty_months').val(obj.warranty_months); 

                        // load additional warranties
                        resetWarranties();
                        if(obj.warranties && obj.warranties.length){
                            $.each(obj.warranties, function(i, w){
                                addWarrantyRow(w.warranty_name, w.warranty_months);
                            });
                        }
                         
                        $('#recordOption').val('2');
                        $('#submitBtn').html('<i class="far fa-save"></i>&nbsp;Update');
                    }
                });
            }
        });
        $('#tblproduct tbody').on('click', '.btnBarcode', function() {
            var qty = parseInt(prompt("How many labels to print?", "1"));
            if (qty > 0) {
                window.open('<?php echo base_url() ?>Product/Barcodeprint/' + $(this).data('id') + '/' + qty, '_blank');
            }
        });
    });

    function deactive_confirm() {
        return confirm("Are you sure you want to deactive this?");
    }

    function active_confirm() {
        return confirm("Are you sure you want to active this?");
    }

    function delete_confirm() {
        return confirm("Are you sure you want to remove this?");
    }
</script>
<?php include "include/footer.php"; ?>