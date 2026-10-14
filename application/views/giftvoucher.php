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
                            <div class="page-header-icon"><i class="fas fa-gift"></i></div>
                            <span>Gift Voucher</span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2">
                <div class="card">
                    <div class="card-body p-0 p-2">
                        <div class="row">
                            <div class="col-3">
                                <form action="<?php echo base_url() ?>Giftvoucher/Giftvoucherinsertupdate" method="post" autocomplete="off">
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Voucher Code*</label>
                                        <input type="text" class="form-control form-control-sm text-uppercase" name="vouchercode" id="vouchercode" required>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Voucher Value*</label>
                                        <input type="number" step="0.01" min="0.01" class="form-control form-control-sm text-right" name="vouchervalue" id="vouchervalue" required>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Issue Date*</label>
                                        <input type="date" class="form-control form-control-sm" name="issuedate" id="issuedate" value="<?php echo date('Y-m-d') ?>" required>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Expiry Date*</label>
                                        <input type="date" class="form-control form-control-sm" name="expirydate" id="expirydate" required>
                                    </div>
                                    <div class="form-group mt-2 text-right">
                                        <button type="submit" id="submitBtn" class="btn btn-primary btn-sm px-4"
                                         <?php if($addcheck==0){echo 'disabled';} ?>><i class="far fa-save"></i>&nbsp;Add</button>
                                    </div>
                                    <input type="hidden" name="recordOption" id="recordOption" value="1">
                                    <input type="hidden" name="recordID" id="recordID" value="">
                                </form>
                            </div>
                            <div class="col-9">
                                <div class="scrollbar pb-3" id="style-2">
                                    <table class="table table-bordered table-striped table-sm nowrap" id="tblgiftvoucher">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Voucher Code</th>
                                                <th class="text-right">Value</th>
                                                <th>Issue Date</th>
                                                <th>Expiry Date</th>
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

<!-- Voucher history modal -->
<div class="modal fade" id="modalvoucherlog" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Voucher History</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped table-sm nowrap w-100">
                    <thead>
                        <tr>
                            <th>Date / Time</th>
                            <th>Action</th>
                            <th class="text-right">Amount</th>
                            <th>Invoice</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody id="voucherlogbody"></tbody>
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

        $('#tblgiftvoucher').DataTable({
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
                { extend: 'csv', className: 'btn btn-success btn-sm', title: 'Gift Voucher Information', text: '<i class="fas fa-file-csv mr-2"></i> CSV', },
                { extend: 'pdf', className: 'btn btn-danger btn-sm', title: 'Gift Voucher Information', text: '<i class="fas fa-file-pdf mr-2"></i> PDF', },
                { 
                    extend: 'print', 
                    title: 'Gift Voucher Information',
                    className: 'btn btn-primary btn-sm', 
                    text: '<i class="fas fa-print mr-2"></i> Print',
                    customize: function ( win ) {
                        $(win.document.body).find( 'table' )
                            .addClass( 'compact' )
                            .css( 'font-size', 'inherit' );
                    }, 
                },
                // 'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            
            ajax: {
                url: "<?php echo base_url() ?>scripts/giftvoucherlist.php",
                type: "POST", // you can use GET
                // data: function(d) {}
            },
            "order": [[ 0, "desc" ]],
            "columns": [
                {
                    "data": null,
                    "render": function(data, type, full, meta) {
                        return meta.settings._iRecordsDisplay - meta.row;
                    }
                },
                {
                    "data": "voucher_code"
                },
                {
                    "data": "voucher_value",
                    "className": 'text-right',
                    "render": function(data) {
                        return parseFloat(data).toFixed(2);
                    }
                },
                {
                    "data": "issue_date",
                    "render": function(data) {
                        return data ? data.substring(0, 10) : '';
                    }
                },
                {
                    "data": "expiry_date",
                    "render": function(data) {
                        var expiry = data ? data.substring(0, 10) : '';
                        var today = '<?php echo date('Y-m-d'); ?>';
                        if(expiry != '' && expiry < today){
                            return expiry + ' <span class="badge badge-danger">Expired</span>';
                        }
                        return expiry;
                    }
                },
                {
                    "targets": -1,
                    "className": 'text-right',
                    "data": null,
                    "render": function(data, type, full) {
                        var button='';
                        button+='<button data-toggle="tooltip" data-placement="bottom" title="History" class="btn btn-info btn-sm btnLog mr-1" id="'+full['idtbl_gift_voucher']+'"><i class="fas fa-history"></i></button>';
                        button+='<button data-toggle="tooltip" data-placement="bottom" title="Edit" class="btn btn-primary btn-sm btnEdit mr-1 ';if(editcheck!=1){button+='d-none';}button+='" id="'+full['idtbl_gift_voucher']+'"><i class="fas fa-pen"></i></button>';
                        if(full['status']==1){
                            button+='<a href="<?php echo base_url() ?>Giftvoucher/Giftvoucherstatus/'+full['idtbl_gift_voucher']+'/2"  data-toggle="tooltip" data-placement="bottom" title="Active" onclick="return deactive_confirm()" target="_self" class="btn btn-success btn-sm mr-1 ';if(statuscheck!=1){button+='d-none';}button+='"><i class="fas fa-check"></i></a>';
                        }else{
                            button+='<a href="<?php echo base_url() ?>Giftvoucher/Giftvoucherstatus/'+full['idtbl_gift_voucher']+'/1" data-toggle="tooltip" data-placement="bottom" title="Deactive" onclick="return active_confirm()" target="_self" class="btn btn-warning btn-sm mr-1 ';if(statuscheck!=1){button+='d-none';}button+='"><i class="fas fa-times"></i></a>';
                        }
                        button+='<a href="<?php echo base_url() ?>Giftvoucher/Giftvoucherstatus/'+full['idtbl_gift_voucher']+'/3" data-toggle="tooltip" data-placement="bottom" title="Delete" onclick="return delete_confirm()" target="_self" class="btn btn-danger btn-sm ';if(deletecheck!=1){button+='d-none';}button+='"><i class="fas fa-trash-alt"></i></a>';
                        
                        return button;
                    }
                }
            ],
            drawCallback: function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
        $('#tblgiftvoucher tbody').on('click', '.btnEdit', function() {
            var r = confirm("Are you sure, You want to Edit this ? ");
            if (r == true) {
                var id = $(this).attr('id');
                $.ajax({
                    type: "POST",
                    data: {
                        recordID: id
                    },
                    url: '<?php echo base_url() ?>Giftvoucher/Giftvoucheredit',
                    success: function(result) { //alert(result);
                        var obj = JSON.parse(result);
                        $('#recordID').val(obj.id);
                        $('#vouchercode').val(obj.vouchercode); 
                        $('#vouchervalue').val(obj.vouchervalue); 
                        $('#issuedate').val(obj.issuedate); 
                        $('#expirydate').val(obj.expirydate); 
                         
                        $('#recordOption').val('2');
                        $('#submitBtn').html('<i class="far fa-save"></i>&nbsp;Update');
                    }
                });
            }
        });
        $('#tblgiftvoucher tbody').on('click', '.btnLog', function() {
            var id = $(this).attr('id');
            $.ajax({
                type: "POST",
                data: {
                    recordID: id
                },
                url: '<?php echo base_url() ?>Giftvoucher/Giftvoucherlog',
                success: function(result) {
                    var rows = JSON.parse(result);
                    var html = '';
                    if(rows.length == 0){
                        html = '<tr><td colspan="5" class="text-center">No history found</td></tr>';
                    }
                    $.each(rows, function(i, row) {
                        html += '<tr>';
                        html += '<td>'+row.insertdatetime+'</td>';
                        html += '<td>'+row.action+'</td>';
                        html += '<td class="text-right">'+parseFloat(row.amount).toFixed(2)+'</td>';
                        html += '<td>'+(row.invoice ? row.invoice : '-')+'</td>';
                        html += '<td>'+(row.user ? row.user : '-')+'</td>';
                        html += '</tr>';
                    });
                    $('#voucherlogbody').html(html);
                    $('#modalvoucherlog').modal('show');
                }
            });
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