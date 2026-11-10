<?php 
include "include/header.php";  
include "include/topnavbar.php"; 
?>
<style>
    .em-sec{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#6c757d;border-bottom:1px solid #e9ecef;padding-bottom:4px;margin:14px 0 10px}
    .em-sec:first-child{margin-top:0}
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
                            <div class="page-header-icon"><i class="fas fa-users"></i></div>
                            <span>Employee Information</span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2">
                <div class="card">
                    <div class="card-body p-0 p-2">
                        <div class="row align-items-end mb-2">
                            <div class="col-12 col-md-3">
                                <label class="small font-weight-bold mb-1">Branch</label>
                                <select id="f_branch" class="form-control form-control-sm">
                                    <option value="">All</option>
                                    <?php foreach($branches as $b){ echo '<option value="'.(int)$b->id.'">'.htmlspecialchars($b->name).'</option>'; } ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-9 text-md-right">
                                <button type="button" id="btnnew" class="btn btn-primary btn-sm px-4" <?php if($addcheck==0){echo 'disabled';} ?>><i class="fas fa-plus"></i>&nbsp;New Employee</button>
                            </div>
                        </div>
                        <div class="scrollbar pb-3" id="style-2">
                            <table class="table table-bordered table-striped table-sm nowrap" id="tblemployee">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Emp No</th>
                                        <th>Name</th>
                                        <th>NIC</th>
                                        <th>Designation</th>
                                        <th>Department</th>
                                        <th>Branch</th>
                                        <th>Phone</th>
                                        <th>Joined</th>
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

<!-- New / Edit modal -->
<div class="modal fade" id="empmodal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user mr-2"></i><span id="em_title">New Employee</span></h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form action="<?php echo base_url() ?>Employee/Employeeinsertupdate" method="post" autocomplete="off">
                    <div class="em-sec">Personal details</div>
                    <div class="form-row mb-1">
                        <div class="col-md-8">
                            <label class="small font-weight-bold">Full name*</label>
                            <input type="text" class="form-control form-control-sm" name="full_name" id="e_full_name" maxlength="150" required>
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold">Name with initials</label>
                            <input type="text" class="form-control form-control-sm" name="name_with_initials" id="e_name_with_initials" maxlength="100">
                        </div>
                    </div>
                    <div class="form-row mb-1">
                        <div class="col-md-4">
                            <label class="small font-weight-bold">NIC*</label>
                            <input type="text" class="form-control form-control-sm" name="nic" id="e_nic" maxlength="12" pattern="([0-9]{9}[VvXx])|([0-9]{12})" title="9 digits + V/X, or 12 digits" required>
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold">Date of birth</label>
                            <input type="date" class="form-control form-control-sm" name="dob" id="e_dob">
                        </div>
                        <div class="col-md-2">
                            <label class="small font-weight-bold">Gender</label>
                            <select class="form-control form-control-sm" name="gender" id="e_gender">
                                <option value="">-</option><option>Male</option><option>Female</option><option>Other</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="small font-weight-bold">Marital</label>
                            <select class="form-control form-control-sm" name="marital_status" id="e_marital_status">
                                <option value="">-</option><option>Single</option><option>Married</option><option>Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group mb-1">
                        <label class="small font-weight-bold">Address</label>
                        <input type="text" class="form-control form-control-sm" name="address" id="e_address" maxlength="255">
                    </div>
                    <div class="form-row mb-1">
                        <div class="col-md-4">
                            <label class="small font-weight-bold">Phone</label>
                            <input type="text" class="form-control form-control-sm" name="phone" id="e_phone" maxlength="20">
                        </div>
                        <div class="col-md-8">
                            <label class="small font-weight-bold">Email</label>
                            <input type="email" class="form-control form-control-sm" name="email" id="e_email" maxlength="100">
                        </div>
                    </div>
                    <div class="form-row mb-1">
                        <div class="col-md-8">
                            <label class="small font-weight-bold">Emergency contact name</label>
                            <input type="text" class="form-control form-control-sm" name="emergency_name" id="e_emergency_name" maxlength="100">
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold">Emergency phone</label>
                            <input type="text" class="form-control form-control-sm" name="emergency_phone" id="e_emergency_phone" maxlength="20">
                        </div>
                    </div>

                    <div class="em-sec">Employment</div>
                    <div class="form-row mb-1">
                        <div class="col-md-4">
                            <label class="small font-weight-bold">Branch*</label>
                            <select class="form-control form-control-sm" name="branch_id" id="e_branch_id" required>
                                <option value="">Select</option>
                                <?php foreach($branches as $b){ echo '<option value="'.(int)$b->id.'">'.htmlspecialchars($b->name).'</option>'; } ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold">Designation</label>
                            <input type="text" class="form-control form-control-sm" name="designation" id="e_designation" maxlength="100" list="dl_designation">
                            <datalist id="dl_designation">
                                <?php foreach($designations as $v){ echo '<option value="'.htmlspecialchars($v).'">'; } ?>
                            </datalist>
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold">Department</label>
                            <input type="text" class="form-control form-control-sm" name="department" id="e_department" maxlength="100" list="dl_department">
                            <datalist id="dl_department">
                                <?php foreach($departments as $v){ echo '<option value="'.htmlspecialchars($v).'">'; } ?>
                            </datalist>
                        </div>
                    </div>
                    <div class="form-row mb-1">
                        <div class="col-md-3">
                            <label class="small font-weight-bold">Employment type*</label>
                            <select class="form-control form-control-sm" name="employment_type" id="e_employment_type" required>
                                <option value="1">Permanent</option>
                                <option value="2">Contract</option>
                                <option value="3">Probation</option>
                                <option value="4">Part-time</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="small font-weight-bold">Join date</label>
                            <input type="date" class="form-control form-control-sm" name="join_date" id="e_join_date">
                        </div>
                        <div class="col-md-3">
                            <label class="small font-weight-bold">Resign date</label>
                            <input type="date" class="form-control form-control-sm" name="resign_date" id="e_resign_date">
                        </div>
                        <div class="col-md-3">
                            <label class="small font-weight-bold">Status</label>
                            <select class="form-control form-control-sm" name="status" id="e_status">
                                <option value="1">Active</option>
                                <option value="2">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="em-sec">Payroll &amp; bank</div>
                    <div class="form-row mb-1">
                        <div class="col-md-4">
                            <label class="small font-weight-bold">EPF no</label>
                            <input type="text" class="form-control form-control-sm" name="epf_no" id="e_epf_no" maxlength="30">
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold">Basic salary</label>
                            <input type="number" class="form-control form-control-sm" name="basic_salary" id="e_basic_salary" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="form-row mb-1">
                        <div class="col-md-4">
                            <label class="small font-weight-bold">Bank</label>
                            <input type="text" class="form-control form-control-sm" name="bank_name" id="e_bank_name" maxlength="100">
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold">Bank branch</label>
                            <input type="text" class="form-control form-control-sm" name="bank_branch" id="e_bank_branch" maxlength="100">
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold">Account no</label>
                            <input type="text" class="form-control form-control-sm" name="bank_account_no" id="e_bank_account_no" maxlength="30">
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Remark</label>
                        <input type="text" class="form-control form-control-sm" name="remark" id="e_remark" maxlength="255">
                    </div>

                    <input type="hidden" name="recordOption" id="recordOption" value="1">
                    <input type="hidden" name="recordID" id="recordID" value="">
                    <div class="form-group mt-3 mb-0 text-right">
                        <button type="button" class="btn btn-light btn-sm border px-3" data-dismiss="modal">Cancel</button>
                        <button type="submit" id="submitBtn" class="btn btn-primary btn-sm px-4"><i class="far fa-save"></i>&nbsp;Add</button>
                    </div>
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

        var FIELDS = ['full_name', 'name_with_initials', 'nic', 'dob', 'gender', 'marital_status', 'address', 'phone', 'email',
                      'emergency_name', 'emergency_phone', 'branch_id', 'designation', 'department', 'employment_type',
                      'join_date', 'resign_date', 'status', 'epf_no', 'basic_salary', 'bank_name', 'bank_branch',
                      'bank_account_no', 'remark'];

        var table = $('#tblemployee').DataTable({
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
                { extend: 'csv', className: 'btn btn-success btn-sm', title: 'Employee Information', text: '<i class="fas fa-file-csv mr-2"></i> CSV', exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] } },
                { extend: 'pdf', className: 'btn btn-danger btn-sm', title: 'Employee Information', text: '<i class="fas fa-file-pdf mr-2"></i> PDF', exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] } },
                { 
                    extend: 'print', 
                    title: 'Employee Information',
                    className: 'btn btn-primary btn-sm', 
                    text: '<i class="fas fa-print mr-2"></i> Print',
                    exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] },
                    customize: function ( win ) {
                        $(win.document.body).find( 'table' )
                            .addClass( 'compact' )
                            .css( 'font-size', 'inherit' );
                    }, 
                },
            ],
            
            ajax: {
                url: "<?php echo base_url() ?>scripts/employeelist.php",
                type: "POST",
                data: function(d) {
                    d.branch = $('#f_branch').val();
                }
            },
            "order": [[ 0, "desc" ]],
            "columns": [
                {
                    "data": null,
                    "render": function(data, type, full, meta) {
                        return meta.settings._iRecordsDisplay - meta.row;
                    }
                },
                { "data": "emp_no" },
                { "data": "full_name" },
                { "data": "nic" },
                { "data": "designation" },
                { "data": "department" },
                { "data": "branch" },
                { "data": "phone" },
                { "data": "join_date" },
                {
                    "targets": -1,
                    "className": 'text-right',
                    "data": null,
                    "render": function(data, type, full) {
                        var button='';
                        button+='<button data-toggle="tooltip" data-placement="bottom" title="Edit" class="btn btn-primary btn-sm btnEdit mr-1 ';if(editcheck!=1){button+='d-none';}button+='" id="'+full['idtbl_employee']+'"><i class="fas fa-pen"></i></button>';
                        if(full['status']==1){
                            button+='<a href="<?php echo base_url() ?>Employee/Employeestatus/'+full['idtbl_employee']+'/2"  data-toggle="tooltip" data-placement="bottom" title="Active" onclick="return deactive_confirm()" target="_self" class="btn btn-success btn-sm mr-1 ';if(statuscheck!=1){button+='d-none';}button+='"><i class="fas fa-check"></i></a>';
                        }else{
                            button+='<a href="<?php echo base_url() ?>Employee/Employeestatus/'+full['idtbl_employee']+'/1" data-toggle="tooltip" data-placement="bottom" title="Deactive" onclick="return active_confirm()" target="_self" class="btn btn-warning btn-sm mr-1 ';if(statuscheck!=1){button+='d-none';}button+='"><i class="fas fa-times"></i></a>';
                        }
                        button+='<a href="<?php echo base_url() ?>Employee/Employeestatus/'+full['idtbl_employee']+'/3" data-toggle="tooltip" data-placement="bottom" title="Delete" onclick="return delete_confirm()" target="_self" class="btn btn-danger btn-sm ';if(deletecheck!=1){button+='d-none';}button+='"><i class="fas fa-trash-alt"></i></a>';
                        
                        return button;
                    }
                }
            ],
            drawCallback: function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });

        $('#f_branch').on('change', function() {
            table.ajax.reload();
        });

        $('#btnnew').on('click', function() {
            $('#empmodal form')[0].reset();
            $('#recordID').val('');
            $('#recordOption').val('1');
            $('#em_title').text('New Employee');
            $('#submitBtn').prop('disabled', addcheck != 1).html('<i class="far fa-save"></i>&nbsp;Add');
            $('#empmodal').modal('show');
        });

        $('#tblemployee tbody').on('click', '.btnEdit', function() {
            var r = confirm("Are you sure, You want to Edit this ? ");
            if (r == true) {
                var id = $(this).attr('id');
                $.ajax({
                    type: "POST",
                    data: {
                        recordID: id
                    },
                    url: '<?php echo base_url() ?>Employee/Employeeedit',
                    success: function(result) { //alert(result);
                        var obj = JSON.parse(result);
                        if (!obj.id) { alert('Employee not found.'); return; }

                        $('#empmodal form')[0].reset();
                        $.each(FIELDS, function(i, f) {
                            $('#e_' + f).val(obj[f] === null || obj[f] === undefined ? '' : obj[f]);
                        });
                        $('#recordID').val(obj.id);
                        $('#recordOption').val('2');
                        $('#em_title').text('Edit ' + obj.emp_no);
                        $('#submitBtn').prop('disabled', editcheck != 1).html('<i class="far fa-save"></i>&nbsp;Update');
                        $('#empmodal').modal('show');
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