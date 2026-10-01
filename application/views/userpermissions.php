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
                            <span>User Permissions</span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2">
                <div class="card">
                    <div class="card-body p-0 p-2">
                        <div class="row">
                            <div class="col-3">
                                <form action="<?php echo base_url() ?>User/Userpermissionsinsertupdate" method="post" autocomplete="off">
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Permission <span class="small">(Controller / Function)</span>*</label>
                                        <input type="text" class="form-control form-control-sm" name="userpermission" id="userpermission" required>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Permissions*</label>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="permission_create" id="permission_create" value="1">
                                            <label class="custom-control-label" for="permission_create">Create Permission</label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="permission_edit" id="permission_edit" value="1">
                                            <label class="custom-control-label" for="permission_edit">Edit Permission</label>
                                        </div> 
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="permission_status" id="permission_status" value="1">
                                            <label class="custom-control-label" for="permission_status">Status Permission</label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="permission_delete" id="permission_delete" value="1">
                                            <label class="custom-control-label" for="permission_delete">Delete Permission</label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="permission_approve" id="permission_approve" value="1">
                                            <label class="custom-control-label" for="permission_approve">Approve Permission</label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="permission_check" id="permission_check" value="1">
                                            <label class="custom-control-label" for="permission_check">Check Permission</label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="permission_account" id="permission_account" value="1">
                                            <label class="custom-control-label" for="permission_account">Account Permission</label>
                                        </div>
                                    </div>
                                    <div class="form-group mt-3 text-right">
                                        <button type="submit" id="submitBtn" class="btn btn-primary btn-sm px-4"><i class="far fa-save"></i>&nbsp;Add</button>
                                    </div>
                                    <input type="hidden" name="recordOption" id="recordOption" value="1">
                                    <input type="hidden" name="recordID" id="recordID" value="">
                                </form>
                            </div>
                            <div class="col-9">
                                <table class="table table-bordered table-striped table-sm nowrap" id="dataTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Permission</th>
                                            <th>Guard</th>
                                            <th>Module</th>
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
<?php include "include/footerscripts.php"; ?>
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "destroy": true,
            "processing": true,
            "serverSide": true,
            ajax: {
                url: "<?php echo base_url() ?>scripts/userpermissionslist.php",
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
                    "data": "permission"
                },
                {
                    "data": "guard_name"
                },
                {
                    "data": "module"
                },
                {
                    "targets": -1,
                    "className": 'text-right',
                    "data": null,
                    "render": function(data, type, full) {
                        var button='';

                        button+='<button type="button" data-url="User/Userpermissionsstatus/'+full['idtbl_permissions']+'/3" data-actiontype="3" class="btn btn-danger btn-sm text-light btntableaction"><i class="fas fa-trash-alt"></i></button>';
                        
                        return button;
                    }
                }
            ],
            drawCallback: function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    });
</script>
<?php include "include/footer.php"; ?>
