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
                            <div class="page-header-icon"><i class="fas fa-barcode"></i></div>
                            <span>Product Serial Numbers</span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2">
                <div class="card">
                    <div class="card-body p-0 p-2">
                        <div class="row">
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold">Product*</label>
                                    <select class="form-control form-control-sm" name="product" id="product">
                                        <option value="">Select</option>
                                        <?php foreach($productlist as $rowproduct){ ?>
                                        <option value="<?php echo $rowproduct->idtbl_product ?>">
                                            <?php echo $rowproduct->product_code.' - '.$rowproduct->product_name.(!empty($rowproduct->model_no) ? ' ('.$rowproduct->model_no.')' : ''); ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-2" id="emptyCard">
                    <div class="card-body ps-empty">
                        <strong>No product selected</strong>
                        Select a product to see its details and every serial number.
                    </div>
                </div>

                <div id="resultArea" style="display:none">
                    <div class="card mt-2">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h4 class="mb-0 d-inline-block font-weight-bold" id="pName"></h4>
                                    <span id="pStatus" class="ml-2"></span>
                                    <div class="small text-muted mb-3" id="pSub"></div>
                                    <div class="row" id="pFacts"></div>
                                    <div class="border-top mt-3 pt-2 small text-muted" id="pDesc"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2" id="pStats"></div>

                    <div class="card mt-2">
                        <div class="card-body p-0 p-2">
                            <div class="scrollbar pb-3" id="style-2">
                                <table class="table table-bordered table-striped table-sm nowrap" id="tblserial" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Serial No</th>
                                            <th>GRN</th>
                                            <th class="text-right">Cost Price</th>
                                            <th>Stock Status</th>
                                            <th>Invoice</th>
                                            <th>Sold Date</th>
                                            <th>Warranty End</th>
                                            <th>Warranty</th>
                                            <th>Added</th>
                                        </tr>
                                    </thead>
                                </table>
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
        var serialtable=null;
        var productname='';

        if($.fn.select2){ $('#product').select2(); }

        function esc(v){ return $('<div>').text(v==null ? '' : v).html(); }
        function money(v){ return Number(v||0).toLocaleString('en-LK',{minimumFractionDigits:2,maximumFractionDigits:2}); }
        function fdate(v){ return (v && v.indexOf('0000')!==0) ? v.substr(0,10) : '-'; }
        function fact(label,value){ return '<div class="col-6 col-md-4 col-lg-3 mb-3 ps-fact"><span>'+label+'</span><b>'+value+'</b></div>'; }
        function badge(cls,text){ return '<span class="badge badge-'+cls+'">'+esc(text)+'</span>'; }
        function stat(label,value,color){ return '<div class="col-6 col-md mb-2"><div class="card ps-stat" style="--c:'+color+'"><div class="card-body py-2"><span>'+label+'</span><b>'+value+'</b></div></div></div>'; }
        function issold(full){ return (full['sold_invoice_id'] && full['sold_invoice_id']!='0') ? true : false; }

        function stockbadge(full){
            if(issold(full)){ return badge('primary','Sold'); }
            var t=(full['stock_status']||'').toString().toLowerCase();
            if(t.indexOf('return')>-1){ return badge('warning','Returned'); }
            if(t.indexOf('damage')>-1 || t.indexOf('defect')>-1){ return badge('danger',full['stock_status']); }
            return badge('success', full['stock_status'] ? full['stock_status'] : 'In stock');
        }
        function warrantybadge(full){
            var w=full['warranty_end'];
            if(!w || w.indexOf('0000')===0){ return '<span class="text-muted">Not started</span>'; }
            var end=new Date(w.substr(0,10)+'T00:00:00');
            var today=new Date(); today.setHours(0,0,0,0);
            var d=Math.round((end-today)/86400000);
            if(d<0){ return badge('danger','Expired'); }
            return badge(d<=30 ? 'warning' : 'success', d+' days left');
        }

        function showproduct(p, sm){
            productname=p.product_name;
            $('#pName').text(p.product_name);
            $('#pStatus').html(p.status==1 ? badge('success','Active') : badge('secondary','Inactive'));
            $('#pSub').text([p.product_code,p.model_no].filter(Boolean).join('  |  '));
            $('#pFacts').html(
                fact('Barcode', esc(p.barcode||'-'))+
                fact('Category', esc(p.category_name||'-'))+
                fact('Brand', esc(p.brand_name||'-'))+
                fact('Unit', esc(p.unit_name||'-'))+
                fact('Cost Price', money(p.cost_price))+
                fact('Selling Price', money(p.selling_price))+
                fact('Reorder Level', esc(p.reorder_level||0))+
                fact('Warranty', (p.warranty_months||0)+' months')+
                fact('Serial Tracking', p.has_serial==1 ? 'Yes' : 'No')+
                fact('Created', esc(fdate(p.insertdatetime)))
            );
            $('#pDesc').text(p.description || 'No description added.');

            var low=parseInt(sm.instock,10)<=parseInt(p.reorder_level||0,10);
            $('#pStats').html(
                stat('Total Serials', sm.total, '#0f766e')+
                stat('In Stock'+(low?' (at/below reorder level)':''), sm.instock, low ? '#b45309' : '#15803d')+
                stat('Sold', sm.sold, '#1d4ed8')+
                stat('Under Warranty', sm.warrantyactive, '#15803d')+
                stat('Warranty Expired', sm.warrantyexpired, '#b91c1c')
            );
        }

        function loadtable(){
            if(serialtable){
                serialtable.ajax.reload();
                return;
            }

            serialtable=$('#tblserial').DataTable({
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
                    { extend: 'csv', className: 'btn btn-success btn-sm', title: function(){ return 'Serial Numbers - '+productname; }, text: '<i class="fas fa-file-csv mr-2"></i> CSV', },
                    { extend: 'pdf', className: 'btn btn-danger btn-sm', title: function(){ return 'Serial Numbers - '+productname; }, text: '<i class="fas fa-file-pdf mr-2"></i> PDF', },
                    { 
                        extend: 'print', 
                        title: function(){ return 'Serial Numbers - '+productname; },
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
                    url: "<?php echo base_url() ?>scripts/productseriallist.php",
                    type: "POST", // you can use GET
                    data: function(d) {
                        d.productID = $('#product').val();
                    }
                },
                "order": [[ 0, "desc" ]],
                "language": { "emptyTable": "No serial numbers yet. They appear after a GRN is saved." },
                "columns": [
                    {
                        "data": null,
                        "render": function(data, type, full, meta) {
                            return meta.settings._iRecordsDisplay - meta.row;
                        }
                    },
                    {
                        "data": "serialno",
                        "render": function(data, type, full) {
                            return '<span class="ps-serial">'+esc(data)+'</span>';
                        }
                    },
                    {
                        "data": "grn_no",
                        "render": function(data, type, full) {
                            return esc(data || full['tbl_grn_idtbl_grn'] || '-');
                        }
                    },
                    {
                        "data": "costunitprice",
                        "className": 'text-right',
                        "render": function(data) {
                            return money(data);
                        }
                    },
                    {
                        "data": null,
                        "orderable": false,
                        "render": function(data, type, full) {
                            return stockbadge(full);
                        }
                    },
                    {
                        "data": "sold_invoice_id",
                        "render": function(data, type, full) {
                            return issold(full) ? esc(data) : '-';
                        }
                    },
                    {
                        "data": "sold_date",
                        "render": function(data) {
                            return fdate(data);
                        }
                    },
                    {
                        "data": "warranty_end",
                        "render": function(data) {
                            return fdate(data);
                        }
                    },
                    {
                        "data": null,
                        "orderable": false,
                        "render": function(data, type, full) {
                            return warrantybadge(full);
                        }
                    },
                    {
                        "data": "insertdatetime",
                        "render": function(data) {
                            return fdate(data);
                        }
                    }
                ]
            });
        }

        $('#product').on('change', function() {
            var id = $(this).val();
            if(id==''){
                $('#resultArea').hide();
                $('#emptyCard').show();
                return;
            }
            $.ajax({
                type: "POST",
                data: {
                    productID: id
                },
                url: '<?php echo base_url() ?>Productserial/Getproductdetail',
                success: function(result) { //alert(result);
                    var obj = JSON.parse(result);
                    if(obj.status!=true){
                        alert('Product not found');
                        return;
                    }
                    showproduct(obj.product, obj.summary);
                    $('#emptyCard').hide();
                    $('#resultArea').show();
                    loadtable();
                }
            });
        });
    });
</script>
<?php include "include/footer.php"; ?>