<?php 
include "include/header.php";  
include "include/topnavbar.php"; 
?>
<style>
    .input-group-text {
        width: 90px;
        justify-content: center;
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
                            <div class="page-header-icon"><i data-feather="user"></i></div>
                            <span>User Roles</span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2">
                <div class="card">
                    <div class="card-body p-0 p-2">
                        <div class="row">
                            <div class="col-12 text-right">
                                <button type="button" class="btn btn-primary btn-sm px-3" data-toggle="modal" data-target="#createroleModal"><i class="fas fa-plus mr-2"></i>Create Role</button>
                            </div>
                            <div class="col-12">
                                <hr>
                                <table class="table table-bordered table-striped table-sm nowrap" id="dataTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Permission</th>
                                            <th>Guard</th>
                                            <th class="text-right">Actions</th>
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
<!-- Modal -->
<div class="modal fade" id="createroleModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="createroleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="createroleModalLabel">Create Role</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
                <form action="<?php echo base_url() ?>User/Userrolesinsertupdate" method="post" autocomplete="off">
                    <div class="form-row mb-2">
                        <div class="col-4">
                            <label class="small font-weight-bold">Role Name*</label>
                            <input type="text" class="form-control form-control-sm" name="rolename" id="rolename" required>
                        </div>
                    </div> 
                    <div class="form-row mb-1">
                        <div class="col-12">
                            <h6 class="title-style small"><span>Permissions</span></h6>
                            <div class="card-columns">
                                <?php foreach ($permissionlist as $module_name => $items) { ?>
                                <div class="card border shadow-none">
                                    <div class="card-header bg-light">
                                        <h6 class="card-title mb-0 font-weight-bold text-primary">
                                            <?php echo $module_name; ?>
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <?php foreach ($items as $row) { ?>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input permissioncheckbox" name="permission[]" id="permission_<?php echo $row->idtbl_permissions; ?>" value="<?php echo $row->idtbl_permissions; ?>">
                                                <label class="custom-control-label" for="permission_<?php echo $row->idtbl_permissions; ?>"><?php echo $row->permission; ?></label>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div> 
                    <div class="form-row mb-2">
                        <div class="col-12 text-right">
                            <button type="submit" id="submitBtn" class="btn btn-primary btn-sm px-4"><i class="fas fa-save mr-2"></i>Save</button>
                        </div>
                    </div> 
                    <input type="hidden" name="recordOption" id="recordOption" value="1">
                    <input type="hidden" name="recordID" id="recordID" value="">
                </form>
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

        $('#dataTable').DataTable({
            "destroy": true,
            "processing": true,
            "serverSide": true,
            ajax: {
                url: "<?php echo base_url() ?>scripts/userroleslist.php",
                type: "POST", // you can use GET
                data: function(d) {
                    d.userID = '<?php echo $_SESSION['userid']; ?>';
                }
            },
            "order": [[ 0, "desc" ]],
            "columns": [
                {
                    "data": null,
                    "render": function(data, type, full, meta) {
                        return meta.row + 1 + meta.settings._iDisplayStart;
                    }
                },
                {
                    "data": "role"
                },
                {
                    "data": "guard_name"
                },
                {
                    "targets": -1,
                    "className": 'text-right',
                    "data": null,
                    "render": function(data, type, full) {
                        var button='';

                        if(editcheck==1){
                            button+='<button type="button" class="btn btn-primary btn-sm btnEdit mr-1" id="'+full['idtbl_roles']+'"><i class="fas fa-pen"></i></button>';
                        }
                        
                        if(full['status']==1 && statuscheck==1){
                            button+='<button type="button" data-url="User/Userrolesstatus/'+full['idtbl_roles']+'/2" data-actiontype="2" class="btn btn-success btn-sm mr-1 btntableaction"><i class="fas fa-check"></i></button>';
                        }else if(full['status']==2 && statuscheck==1){
                            button+='<button type="button" data-url="User/Userrolesstatus/'+full['idtbl_roles']+'/1" data-actiontype="1" class="btn btn-warning btn-sm mr-1 text-light btntableaction"><i class="fas fa-times"></i></button>';
                        }
                        if(deletecheck==1){
                            button+='<button type="button" data-url="User/Userrolesstatus/'+full['idtbl_roles']+'/3" data-actiontype="3" class="btn btn-danger btn-sm text-light btntableaction"><i class="fas fa-trash-alt"></i></button>';
                        }
                        
                        return button;
                    }
                }
            ],
            drawCallback: function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
        $('#dataTable tbody').on('click', '.btnEdit', async function() {
            var r = await Otherconfirmation("You want to Edit this ? ");
            if (r == true) {
                var id = $(this).attr('id');
                $.ajax({
                    type: "POST",
                    data: {
                        recordID: id
                    },
                    url: '<?php echo base_url() ?>User/Userrolesedit',
                    success: function(result) { //alert(result);
                        var obj = JSON.parse(result);
                        $('#recordID').val(obj.id);
                        $('#rolename').val(obj.role);
                        $('#recordOption').val('2');
                        $('#submitBtn').html('<i class="far fa-save"></i>&nbsp;Update');
                        
                        $('.permissioncheckbox').prop('checked', false);

                        if (obj.permission && obj.permission.length > 0) {
                            $.each(obj.permission, function(index, value) {
                                $('#permission_' + value).prop('checked', true);
                            });
                        }

                        $('#createroleModal').modal('show');
                    }
                });
            }
        });
    });
</script>
<?php include "include/footer.php"; ?>
