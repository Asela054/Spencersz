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
                            <div class="page-header-icon"><i class="fas fa-bookmark"></i></div>
                            <span>Item Reservation</span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2">
                <div class="card">
                    <div class="card-body p-0 p-2">
                        <form action="<?php echo base_url() ?>Reservation/Reservationinsertupdate" method="post" autocomplete="off" id="reservationform">
                            <div class="row">
                                <!-- Header details -->
                                <div class="col-4">
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Customer*</label>
                                        <select class="form-control form-control-sm" name="customer" id="customer" required>
                                            <option value="">Select</option>
                                            <?php foreach($customerlist->result() as $rowcustomer){ ?>
                                            <option value="<?php echo $rowcustomer->idtbl_customer ?>"><?php echo $rowcustomer->customer.' - '.$rowcustomer->telephone_no ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="form-row mb-1">
                                        <div class="col">
                                            <label class="small font-weight-bold">Reservation Date*</label>
                                            <input type="date" class="form-control form-control-sm" name="reservationdate" id="reservationdate" value="<?php echo date('Y-m-d') ?>" required>
                                        </div>
                                        <div class="col">
                                            <label class="small font-weight-bold">Hold Until*</label>
                                            <input type="date" class="form-control form-control-sm" name="expirydate" id="expirydate" value="<?php echo date('Y-m-d', strtotime('+7 days')) ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-row mb-1">
                                        <div class="col">
                                            <label class="small font-weight-bold">VAT</label>
                                            <select class="form-control form-control-sm" name="vatapplicable" id="vatapplicable">
                                                <option value="1">Apply VAT</option>
                                                <option value="0">No VAT</option>
                                            </select>
                                        </div>
                                        <div class="col">
                                            <label class="small font-weight-bold">VAT Rate (%)</label>
                                            <input type="text" class="form-control form-control-sm" id="vatrate" value="<?php echo $vatrate ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Advance Amount</label>
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm" name="advance" id="advance" value="0">
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Remark</label>
                                        <textarea class="form-control form-control-sm" name="remark" id="remark" rows="2"></textarea>
                                    </div>
                                </div>

                                <!-- Item entry + item list -->
                                <div class="col-8">
                                    <div class="form-row">
                                        <div class="col-5">
                                            <label class="small font-weight-bold">Product*</label>
                                            <select class="form-control form-control-sm" id="product">
                                                <option value="">Select</option>
                                                <?php foreach($productlist->result() as $rowproduct){ ?>
                                                <option value="<?php echo $rowproduct->idtbl_product ?>" data-price="<?php echo $rowproduct->selling_price ?>" data-code="<?php echo $rowproduct->product_code ?>" data-name="<?php echo htmlspecialchars($rowproduct->product_name) ?>"><?php echo $rowproduct->product_code.' - '.$rowproduct->product_name ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-2">
                                            <label class="small font-weight-bold">Qty*</label>
                                            <input type="number" min="1" step="1" class="form-control form-control-sm" id="qty" value="1">
                                        </div>
                                        <div class="col-2">
                                            <label class="small font-weight-bold">Unit Price*</label>
                                            <input type="number" min="0" step="0.01" class="form-control form-control-sm" id="unitprice" value="0">
                                        </div>
                                        <div class="col-3">
                                            <label class="small font-weight-bold">Comment</label>
                                            <input type="text" class="form-control form-control-sm" id="comment">
                                        </div>
                                    </div>
                                    <div class="text-right mt-1">
                                        <button type="button" id="btnAddItem" class="btn btn-dark btn-sm px-3"><i class="fas fa-plus"></i>&nbsp;Add Item</button>
                                    </div>

                                    <table class="table table-bordered table-striped table-sm mt-2" id="tblitems">
                                        <thead>
                                            <tr>
                                                <th>Code</th>
                                                <th>Product</th>
                                                <th class="text-right">Qty</th>
                                                <th class="text-right">Unit Price</th>
                                                <th class="text-right">Total</th>
                                                <th>Comment</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2" class="text-right">Subtotal</th>
                                                <th class="text-right" id="totalqty">0</th>
                                                <th></th>
                                                <th class="text-right" id="subtotal">0.00</th>
                                                <th colspan="2"></th>
                                            </tr>
                                            <tr>
                                                <th colspan="4" class="text-right">VAT (<span id="vatlabel">0</span>%)</th>
                                                <th class="text-right" id="vatamount">0.00</th>
                                                <th colspan="2"></th>
                                            </tr>
                                            <tr>
                                                <th colspan="4" class="text-right">Total</th>
                                                <th class="text-right" id="totalamount">0.00</th>
                                                <th colspan="2"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="form-group mt-2 text-right">
                                <button type="button" id="btnReset" class="btn btn-outline-dark btn-sm px-4"><i class="fas fa-redo"></i>&nbsp;Reset</button>
                                <button type="submit" id="submitBtn" class="btn btn-primary btn-sm px-4"
                                 <?php if($addcheck==0){echo 'disabled';} ?>><i class="far fa-save"></i>&nbsp;Reserve</button>
                            </div>
                            <input type="hidden" name="tableData" id="tableData" value="">
                            <input type="hidden" name="recordOption" id="recordOption" value="1">
                            <input type="hidden" name="recordID" id="recordID" value="">
                        </form>
                    </div>
                </div>

                <!-- Reservation list -->
                <div class="card mt-2">
                    <div class="card-body p-0 p-2">
                        <div class="scrollbar pb-3" id="style-2">
                            <table class="table table-bordered table-striped table-sm nowrap" id="tblreservation">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Reservation No</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Hold Until</th>
                                        <th class="text-right">Qty</th>
                                        <th class="text-right">Subtotal</th>
                                        <th class="text-right">VAT</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-right">Advance</th>
                                        <th>Status</th>
                                        <th class="text-right">Actions</th>
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

<!-- View items modal -->
<div class="modal fade" id="modalitems" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header p-2">
                <h6 class="modal-title" id="modalitemstitle">Reserved Items</h6>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-2">
                <table class="table table-bordered table-sm" id="tblmodalitems">
                    <thead>
                        <tr><th>Code</th><th>Product</th><th class="text-right">Qty</th><th class="text-right">Unit Price</th><th class="text-right">Total</th><th class="text-right">VAT</th><th class="text-right">Net</th><th>Comment</th></tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr><th colspan="4" class="text-right">Subtotal</th><th class="text-right" id="mdsubtotal"></th><th class="text-right" id="mdvat"></th><th class="text-right" id="mdtotal"></th><th></th></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include "include/footerscripts.php"; ?>
<script>
    $(document).ready(function() {
        var addcheck='<?php echo $addcheck; ?>';
        var editcheck='<?php echo $editcheck; ?>';
        var statuscheck='<?php echo $statuscheck; ?>';
        var deletecheck='<?php echo $deletecheck; ?>';
        var today='<?php echo date('Y-m-d'); ?>';

        var items=[];
        var vatRate=parseFloat('<?php echo $vatrate; ?>');

        function fmt(n){ return parseFloat(n).toFixed(2); }

        function currentRate(){
            return $('#vatapplicable').val()==1 ? vatRate : 0;
        }

        function renderItems(){
            var html='', tq=0, sub=0, vat=0;
            var rate=currentRate();
            $.each(items, function(i, r){
                var total=Math.round(r.qty*r.unitprice*100)/100;
                var linevat=Math.round(total*rate)/100;
                tq+=parseFloat(r.qty); sub+=total; vat+=linevat;
                html+='<tr>'+
                    '<td>'+r.code+'</td><td>'+r.name+'</td>'+
                    '<td class="text-right">'+r.qty+'</td>'+
                    '<td class="text-right">'+fmt(r.unitprice)+'</td>'+
                    '<td class="text-right">'+fmt(total)+'</td>'+
                    '<td>'+r.comment+'</td>'+
                    '<td class="text-right"><button type="button" class="btn btn-danger btn-sm btnRemoveItem" data-index="'+i+'"><i class="fas fa-trash-alt"></i></button></td>'+
                    '</tr>';
            });
            $('#tblitems tbody').html(html);
            $('#totalqty').text(tq);
            $('#subtotal').text(fmt(sub));
            $('#vatlabel').text(rate);
            $('#vatamount').text(fmt(vat));
            $('#totalamount').text(fmt(sub+vat));
        }

        // fetch the VAT % for the selected reservation date
        function loadVatRate(callback){
            $.ajax({
                type: "POST",
                data: { date: $('#reservationdate').val() },
                url: '<?php echo base_url() ?>Reservation/Reservationvatrate',
                success: function(result) {
                    vatRate=parseFloat(JSON.parse(result).rate);
                    $('#vatrate').val(vatRate);
                    renderItems();
                    if(callback){ callback(); }
                }
            });
        }

        function resetForm(){
            $('#reservationform')[0].reset();
            items=[];
            $('#recordOption').val('1'); $('#recordID').val('');
            $('#submitBtn').html('<i class="far fa-save"></i>&nbsp;Reserve');
            loadVatRate();
        }

        $('#reservationdate').change(function(){ loadVatRate(); });
        $('#vatapplicable').change(function(){ renderItems(); });

        $('#product').change(function(){
            $('#unitprice').val($('#product option:selected').data('price') || 0);
        });

        $('#btnAddItem').click(function(){
            var productID=$('#product').val();
            var qty=parseFloat($('#qty').val());
            var unitprice=parseFloat($('#unitprice').val());
            if(productID=='' || isNaN(qty) || qty<=0 || isNaN(unitprice) || unitprice<0){
                alert('Please select a product and enter a valid qty and price');
                return;
            }
            var opt=$('#product option:selected');
            var found=false;
            $.each(items, function(i, r){
                if(r.productID==productID){ r.qty+=qty; r.unitprice=unitprice; found=true; }
            });
            if(!found){
                items.push({
                    productID: productID, code: opt.data('code'), name: opt.data('name'),
                    qty: qty, unitprice: unitprice, comment: $('#comment').val()
                });
            }
            renderItems();
            $('#product').val(''); $('#qty').val(1); $('#unitprice').val(0); $('#comment').val('');
        });

        $('#tblitems tbody').on('click', '.btnRemoveItem', function(){
            items.splice($(this).data('index'), 1);
            renderItems();
        });

        $('#btnReset').click(function(){ resetForm(); });

        $('#reservationform').submit(function(e){
            if(items.length==0){
                alert('Please add at least one item');
                e.preventDefault(); return false;
            }
            if($('#expirydate').val() < $('#reservationdate').val()){
                alert('Hold Until date cannot be before the reservation date');
                e.preventDefault(); return false;
            }
            var grand=parseFloat($('#totalamount').text());
            if(parseFloat($('#advance').val() || 0) > grand){
                alert('Advance amount cannot be greater than the total');
                e.preventDefault(); return false;
            }
            $('#tableData').val(JSON.stringify(items));
        });

        $('#tblreservation').DataTable({
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
                { extend: 'csv', className: 'btn btn-success btn-sm', title: 'Item Reservation', text: '<i class="fas fa-file-csv mr-2"></i> CSV', },
                { extend: 'pdf', className: 'btn btn-danger btn-sm', title: 'Item Reservation', text: '<i class="fas fa-file-pdf mr-2"></i> PDF', },
                { 
                    extend: 'print', 
                    title: 'Item Reservation',
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
                url: "<?php echo base_url() ?>scripts/reservationlist.php",
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
                { "data": "reservation_no" },
                { "data": "customer" },
                { "data": "reservation_date" },
                { "data": "expiry_date" },
                { "data": "total_qty", "className": "text-right" },
                { "data": "subtotal", "className": "text-right", "render": function(d){ return fmt(d); } },
                { "data": "vat_amount", "className": "text-right", "render": function(d){ return fmt(d); } },
                { "data": "total_amount", "className": "text-right", "render": function(d){ return fmt(d); } },
                { "data": "advance_amount", "className": "text-right", "render": function(d){ return fmt(d); } },
                {
                    "data": null,
                    "render": function(data, type, full) {
                        if(full['reserve_status']==2){ return '<span class="badge badge-success">Invoice Completed</span>'; }
                        if(full['reserve_status']==3){ return '<span class="badge badge-secondary">Cancelled</span>'; }
                        if(full['expiry_date'] < today){ return '<span class="badge badge-danger">Expired</span>'; }
                        return '<span class="badge badge-primary">Open</span>';
                    }
                },
                {
                    "targets": -1,
                    "className": 'text-right',
                    "data": null,
                    "render": function(data, type, full) {
                        var button='';
                        button+='<button data-toggle="tooltip" data-placement="bottom" title="View Items" class="btn btn-dark btn-sm btnView mr-1" data-no="'+full['reservation_no']+'" id="'+full['idtbl_reservation']+'"><i class="fas fa-eye"></i></button>';
                        button+='<a href="<?php echo base_url() ?>Reservation/Reservationnote/'+full['idtbl_reservation']+'" target="_blank" data-toggle="tooltip" data-placement="bottom" title="Reservation Note (PDF)" class="btn btn-danger btn-sm mr-1"><i class="fas fa-file-pdf"></i></a>';
                        if(full['reserve_status']==1){
                            button+='<button data-toggle="tooltip" data-placement="bottom" title="Edit" class="btn btn-primary btn-sm btnEdit mr-1 ';if(editcheck!=1){button+='d-none';}button+='" id="'+full['idtbl_reservation']+'"><i class="fas fa-pen"></i></button>';
                            button+='<a href="<?php echo base_url() ?>Reservation/Reservationstatus/'+full['idtbl_reservation']+'/2" data-toggle="tooltip" data-placement="bottom" title="Cancel Reservation" onclick="return cancel_confirm()" target="_self" class="btn btn-warning btn-sm mr-1 ';if(statuscheck!=1){button+='d-none';}button+='"><i class="fas fa-times"></i></a>';
                        }
                        else if(full['reserve_status']==3){
                            button+='<a href="<?php echo base_url() ?>Reservation/Reservationstatus/'+full['idtbl_reservation']+'/1" data-toggle="tooltip" data-placement="bottom" title="Reopen" onclick="return reopen_confirm()" target="_self" class="btn btn-success btn-sm mr-1 ';if(statuscheck!=1){button+='d-none';}button+='"><i class="fas fa-undo"></i></a>';
                        }
                        if(full['reserve_status']!=2){
                            button+='<a href="<?php echo base_url() ?>Reservation/Reservationstatus/'+full['idtbl_reservation']+'/3" data-toggle="tooltip" data-placement="bottom" title="Delete" onclick="return delete_confirm()" target="_self" class="btn btn-danger btn-sm ';if(deletecheck!=1){button+='d-none';}button+='"><i class="fas fa-trash-alt"></i></a>';
                        }
                        return button;
                    }
                }
            ],
            drawCallback: function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });

        $('#tblreservation tbody').on('click', '.btnView', function() {
            var id = $(this).attr('id');
            $('#modalitemstitle').text('Reserved Items - '+$(this).data('no'));
            $.ajax({
                type: "POST",
                data: { recordID: id },
                url: '<?php echo base_url() ?>Reservation/Reservationitems',
                success: function(result) {
                    var obj = JSON.parse(result);
                    var html='';
                    $.each(obj.items, function(i, r){
                        html+='<tr><td>'+r.product_code+'</td><td>'+r.product_name+'</td>'+
                              '<td class="text-right">'+r.qty+'</td>'+
                              '<td class="text-right">'+fmt(r.unitprice)+'</td>'+
                              '<td class="text-right">'+fmt(r.line_total)+'</td>'+
                              '<td class="text-right">'+fmt(r.vat_amount)+'</td>'+
                              '<td class="text-right">'+fmt(r.line_net_total)+'</td>'+
                              '<td>'+(r.comment||'')+'</td></tr>';
                    });
                    $('#tblmodalitems tbody').html(html);
                    $('#mdsubtotal').text(fmt(obj.subtotal));
                    $('#mdvat').text(fmt(obj.vatamount)+' ('+parseFloat(obj.vatrate)+'%)');
                    $('#mdtotal').text(fmt(obj.total));
                    $('#modalitems').modal('show');
                }
            });
        });

        $('#tblreservation tbody').on('click', '.btnEdit', function() {
            var r = confirm("Are you sure, You want to Edit this ? ");
            if (r == true) {
                var id = $(this).attr('id');
                $.ajax({
                    type: "POST",
                    data: { recordID: id },
                    url: '<?php echo base_url() ?>Reservation/Reservationedit',
                    success: function(result) {
                        var obj = JSON.parse(result);
                        $('#recordID').val(obj.id);
                        $('#customer').val(obj.customer);
                        $('#reservationdate').val(obj.reservationdate);
                        $('#expirydate').val(obj.expirydate);
                        $('#vatapplicable').val(obj.vatapplicable);
                        $('#advance').val(obj.advance);
                        $('#remark').val(obj.remark);

                        vatRate=parseFloat(obj.vatrate);
                        $('#vatrate').val(vatRate);

                        items=[];
                        $.each(obj.items, function(i, r){
                            items.push({
                                productID: r.productID, code: r.product_code, name: r.product_name,
                                qty: parseFloat(r.qty), unitprice: parseFloat(r.unitprice), comment: r.comment||''
                            });
                        });
                        renderItems();

                        $('#recordOption').val('2');
                        $('#submitBtn').html('<i class="far fa-save"></i>&nbsp;Update');
                        $('html, body').animate({scrollTop: 0}, 300);
                    }
                });
            }
        });
    });

    function cancel_confirm() {
        return confirm("Are you sure you want to cancel this reservation?");
    }

    function reopen_confirm() {
        return confirm("Are you sure you want to reopen this reservation?");
    }

    function delete_confirm() {
        return confirm("Are you sure you want to remove this?");
    }
</script>
<?php include "include/footer.php"; ?>