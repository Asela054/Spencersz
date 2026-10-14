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
                            <div class="page-header-icon"><i class="fas fa-store"></i></div>
                            <span>Company Branch</span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2">
                <div class="card">
                    <div class="card-body p-0 p-2">
                        <div class="row">
                            <div class="col-4">
                                <form action="<?php echo base_url() ?>Companybranch/Companybranchinsertupdate" method="post" autocomplete="off">
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Company*</label>
                                        <select class="form-control form-control-sm" name="company" id="company" required>
                                            <option value="">Select</option>
                                            <?php foreach($companylist as $rowcompany){ ?>
                                            <option value="<?php echo $rowcompany->idtbl_company ?>"><?php echo $rowcompany->company ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="form-row mb-1">
                                        <div class="col">
                                            <label class="small font-weight-bold">Type*</label>
                                            <select class="form-control form-control-sm" name="type" id="type" required>
                                                <option value="">Select</option>
                                                <option value="1">Show Room</option>
                                                <option value="2">Warehouse</option>
                                            </select>
                                        </div>
                                        <div class="col">
                                            <label class="small font-weight-bold">Code*</label>
                                            <input type="text" class="form-control form-control-sm" name="code" id="code" required>
                                        </div>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Branch*</label>
                                        <input type="text" class="form-control form-control-sm" name="branch" id="branch" required>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Address Line 1</label>
                                        <input type="text" class="form-control form-control-sm" name="address1" id="address1">
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Address Line 2</label>
                                        <input type="text" class="form-control form-control-sm" name="address2" id="address2">
                                    </div>
                                    <div class="form-row mb-1">
                                        <div class="col">
                                            <label class="small font-weight-bold">Mobile</label>
                                            <input type="text" class="form-control form-control-sm" name="mobile" id="mobile">
                                        </div>
                                        <div class="col">
                                            <label class="small font-weight-bold">Phone</label>
                                            <input type="text" class="form-control form-control-sm" name="phone" id="phone">
                                        </div>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Email</label>
                                        <input type="email" class="form-control form-control-sm" name="email" id="email">
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
                                    <table class="table table-bordered table-striped table-sm nowrap" id="tblcompanybranch">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Company</th>
                                                <th>Branch</th>
                                                <th>Type</th>
                                                <th>Code</th>
                                                <th>Mobile</th>
                                                <th>Phone</th>
                                                <th>Email</th>
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

        $('#tblcompanybranch').DataTable({
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
                { extend: 'csv', className: 'btn btn-success btn-sm', title: 'Company Branch Information', text: '<i class="fas fa-file-csv mr-2"></i> CSV', },
                { extend: 'pdf', className: 'btn btn-danger btn-sm', title: 'Company Branch Information', text: '<i class="fas fa-file-pdf mr-2"></i> PDF', },
                { 
                    extend: 'print', 
                    title: 'Company Branch Information',
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
                url: "<?php echo base_url() ?>scripts/companybranchlist.php",
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
                { "data": "company" },
                { "data": "branch" },
                {
                    "data": "type",
                    "render": function(data, type, full) {
                        if(full['type']==1){ return 'Show Room'; }
                        else if(full['type']==2){ return 'Warehouse'; }
                        return '';
                    }
                },
                { "data": "code" },
                { "data": "mobile" },
                { "data": "phone" },
                { "data": "email" },
                {
                    "targets": -1,
                    "className": 'text-right',
                    "data": null,
                    "render": function(data, type, full) {
                        var button='';
                        button+='<button data-toggle="tooltip" data-placement="bottom" title="Edit" class="btn btn-primary btn-sm btnEdit mr-1 ';if(editcheck!=1){button+='d-none';}button+='" id="'+full['idtbl_company_branch']+'"><i class="fas fa-pen"></i></button>';
                        if(full['status']==1){
                            button+='<a href="<?php echo base_url() ?>Companybranch/Companybranchstatus/'+full['idtbl_company_branch']+'/2"  data-toggle="tooltip" data-placement="bottom" title="Active" onclick="return deactive_confirm()" target="_self" class="btn btn-success btn-sm mr-1 ';if(statuscheck!=1){button+='d-none';}button+='"><i class="fas fa-check"></i></a>';
                        }else{
                            button+='<a href="<?php echo base_url() ?>Companybranch/Companybranchstatus/'+full['idtbl_company_branch']+'/1" data-toggle="tooltip" data-placement="bottom" title="Deactive" onclick="return active_confirm()" target="_self" class="btn btn-warning btn-sm mr-1 ';if(statuscheck!=1){button+='d-none';}button+='"><i class="fas fa-times"></i></a>';
                        }
                        button+='<a href="<?php echo base_url() ?>Companybranch/Companybranchstatus/'+full['idtbl_company_branch']+'/3" data-toggle="tooltip" data-placement="bottom" title="Delete" onclick="return delete_confirm()" target="_self" class="btn btn-danger btn-sm ';if(deletecheck!=1){button+='d-none';}button+='"><i class="fas fa-trash-alt"></i></a>';
                        
                        return button;
                    }
                }
            ],
            drawCallback: function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
        $('#tblcompanybranch tbody').on('click', '.btnEdit', function() {
            var r = confirm("Are you sure, You want to Edit this ? ");
            if (r == true) {
                var id = $(this).attr('id');
                $.ajax({
                    type: "POST",
                    data: {
                        recordID: id
                    },
                    url: '<?php echo base_url() ?>Companybranch/Companybranchedit',
                    success: function(result) {
                        var obj = JSON.parse(result);
                        $('#recordID').val(obj.id);
                        $('#company').val(obj.companyid);
                        $('#type').val(obj.type);
                        $('#branch').val(obj.branch);
                        $('#code').val(obj.code);
                        $('#address1').val(obj.address1);
                        $('#address2').val(obj.address2);
                        $('#mobile').val(obj.mobile);
                        $('#phone').val(obj.phone);
                        $('#email').val(obj.email);

                        $('#recordOption').val('2');
                        $('#submitBtn').html('<i class="far fa-save"></i>&nbsp;Update');
                    }
                });
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
