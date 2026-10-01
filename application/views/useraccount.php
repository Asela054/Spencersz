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
                            <div class="page-header-icon"><i data-feather="user"></i></div>
                            <span>User Account</span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2">
                <div class="card">
                    <div class="card-body p-0 p-2">
                        <div class="row">
                            <div class="col-3">
                                <form action="<?php echo base_url() ?>User/Useraccountinsertupdate" method="post" autocomplete="off">
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Account Name*</label>
                                        <input type="text" class="form-control form-control-sm" name="accountname" id="accountname" required>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Email*</label>
                                        <input type="email" class="form-control form-control-sm" name="username" id="username" required>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Password*</label>
                                        <input type="password" class="form-control form-control-sm" name="password" id="password" required>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">User Type*</label>
                                        <select class="form-control form-control-sm" name="usertype" id="usertype" required>
                                            <option value="">Select</option>
                                            <?php foreach ($usertype->result() as $rowusertype) { ?>
                                            <option value="<?php echo $rowusertype->idtbl_user_type ?>"><?php echo $rowusertype->type ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="small font-weight-bold">User Roles*</label>
                                        <select class="form-control form-control-sm" name="userroles[]" id="userroles" multiple required>
                                            <option value="">Select</option>
                                            <?php foreach ($userroles->result() as $rowuserroles) { ?>
                                            <option value="<?php echo $rowuserroles->idtbl_roles ?>"><?php echo $rowuserroles->role ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="form-group mt-2 text-right">
                                        <button type="submit" id="submitBtn" class="btn btn-primary btn-sm px-4" <?php if($addcheck==0){echo 'disabled';} ?>><i class="far fa-save"></i>&nbsp;Add</button>
                                    </div>
                                    <input type="hidden" name="recordOption" id="recordOption" value="1">
                                    <input type="hidden" name="recordID" id="recordID" value="">
                                </form>
                            </div>
                            <div class="col-9">
                                <div class="scrollbar pb-3" id="style-2">
                                    <table class="table table-bordered table-striped table-sm nowrap" id="dataTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>NAME</th>
                                                <th>Email</th>
                                                <th>User Type</th>
                                                <th>Roles</th>
                                                <th class="text-right">&nbsp;</th>
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

        $('#userroles').select2({
            placeholder: "Select User Roles",
            allowClear: true
        });

        $('#dataTable').DataTable({
            "destroy": true,
            "processing": true,
            "serverSide": true,
            ajax: {
                url: "<?php echo base_url() ?>scripts/useraccountlist.php",
                type: "POST", // you can use GET
                data: function(d) {
                    d.userID = '<?php echo $_SESSION['userid']; ?>';
                }
            },
            "order": [[ 0, "desc" ]],
            "columns": [
                {
                    "data": "idtbl_user"
                },
                {
                    "data": "name"
                },
                {
                    "data": "username"
                },
                {
                    "data": "type"
                },
                {
                    "data": "role"
                },
                {
                    "targets": -1,
                    "className": 'text-right',
                    "data": null,
                    "render": function(data, type, full) {
                        var button='';
                        button+='<button class="btn btn-primary btn-sm btnEdit mr-1 ';if(editcheck!=1){button+='d-none';}button+='" id="'+full['idtbl_user']+'"><i class="fas fa-pen"></i></button>';
                        if(full['status']==1){
                            button+='<a href="<?php echo base_url() ?>User/Useraccountstatus/'+full['idtbl_user']+'/2" onclick="return deactive_confirm()" target="_self" class="btn btn-success btn-sm mr-1 ';if(statuscheck!=1){button+='d-none';}button+='"><i class="fas fa-check"></i></a>';
                        }else{
                            button+='<a href="<?php echo base_url() ?>User/Useraccountstatus/'+full['idtbl_user']+'/1" onclick="return active_confirm()" target="_self" class="btn btn-warning btn-sm mr-1 ';if(statuscheck!=1){button+='d-none';}button+='"><i class="fas fa-times"></i></a>';
                        }
                        button+='<a href="<?php echo base_url() ?>User/Useraccountstatus/'+full['idtbl_user']+'/3" onclick="return delete_confirm()" target="_self" class="btn btn-danger btn-sm ';if(deletecheck!=1){button+='d-none';}button+='"><i class="fas fa-trash-alt"></i></a>';
                        
                        return button;
                    }
                }
            ],
            drawCallback: function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
        $('#dataTable tbody').on('click', '.btnEdit', function() {
            var r = confirm("Are you sure, You want to Edit this ? ");
            if (r == true) {
                var id = $(this).attr('id');
                $.ajax({
                    type: "POST",
                    data: {
                        recordID: id
                    },
                    url: '<?php echo base_url() ?>User/Useraccountedit',
                    success: function(result) { //alert(result);
                        var obj = JSON.parse(result);
                        $('#recordID').val(obj.id);
                        $('#accountname').val(obj.name); 
                        $('#username').val(obj.username); 
                        $('#usertype').val(obj.type);  

                        var userroles = obj.roles;
                        var userrolesoption = [];
                        $.each(userroles, function(i, item) {
                            userrolesoption.push(userroles[i].roleID);
                        });
                        console.log(userrolesoption);
                        $('#userroles').val(userrolesoption);
                        $('#userroles').trigger('change');
                        
                        $('#password').removeAttr("required");

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
