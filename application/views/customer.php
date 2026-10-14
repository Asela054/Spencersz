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
                            <div class="page-header-icon"><i class="fas fa-archive"></i></div>
                            <span>Customer</span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="container-fluid mt-2 p-0 p-2">
                <div class="card">
                    <div class="card-body p-0 p-2">
                        <form action="<?php echo base_url() ?>Customer/Customerinsertupdate"enctype="multipart/form-data" method="post" autocomplete="off">
                            <div class="form-row mb-1">
                                <div class="col-3">
                                    <label class="small font-weight-bold">Registered Name of the Company*</label>
                                    <input type="text" class="form-control form-control-sm" name="customer_name" id="customer_name" required>
                                </div>
                                <div class="col-3">
                                    <label class="small font-weight-bold">Business registration No *</label>
                                    <input type="text" class="form-control form-control-sm" name="business_regno" id="business_regno">
                                </div>
                                <div class="col-2">
                                    <label class="small font-weight-bold">Reference No</label>
                                    <input type="text" class="form-control form-control-sm" name="ref_no" id="ref_no">
                                </div>
                                <div class="col-2">
                                    <label class="small font-weight-bold">VAT Reg Type*</label>
                                    <select class="form-control form-control-sm  px-0" name="vat_customer"
                                        id="vat_customer" required>
                                        <option value="">Select VAT Type</option>
                                        <option value="0">Non VAT</option>
                                        <option value="1">VAT</option>
                                        <option value="2">SVAT</option>
                                    </select>
                                </div>
                                <div class="col-2">
                                    <label class="small font-weight-bold">VAT Registration No*</label>
                                    <input type="text" class="form-control form-control-sm" name="vatno" id="vatno" readonly>
                                </div>
                            </div>
                            <div class="form-row mb-1">
                                <div class="col-2">
                                    <label class="small font-weight-bold">NBT Registration No*</label>
                                    <input type="text" class="form-control form-control-sm" name="nbtno" id="nbtno">
                                </div>
                                <div class="col-2">
                                    <label class="small font-weight-bold">SVAT Registration No*</label>
                                    <input type="text" class="form-control form-control-sm" name="svatno" id="svatno" readonly>
                                </div>
                                <div class="col-2">
                                    <label class="small font-weight-bold">Telephone No*</label>
                                    <input type="number" class="form-control form-control-sm" name="telephoneno" id="telephoneno">
                                </div>
                                <div class="col-2">
                                    <label class="small font-weight-bold">FAX No*</label>
                                    <input type="text" class="form-control form-control-sm" name="faxno" id="faxno">
                                </div>
                                <div class="col-2">
                                    <label class="small font-weight-bold ">Company*</label>
                                    <input type="text" id="f_company_name" name="f_company_name" value="<?php echo $_SESSION['companyname'] ?>" class="form-control form-control-sm" required readonly>
                                </div>
                                <div class="col-2">
                                    <label class="small font-weight-bold ">Company Branch*</label>
                                    <input type="text" id="f_branch_name" name="f_branch_name" value="<?php echo $_SESSION['branchname'] ?>" class="form-control form-control-sm" required readonly>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-4">
                                    <h6 class="title-style small"><span>Business Address*</span></h6>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Address Line 1*</label>
                                        <input type="text" class="form-control form-control-sm" name="line1" id="line1" required>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Address Line 2*</label>
                                        <input type="text" class="form-control form-control-sm" name="line2" id="line2" required>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">City*</label>
                                        <input type="text" class="form-control form-control-sm" name="city" id="city">
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">State*</label>
                                        <input type="text" class="form-control form-control-sm" name="state" id="state">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <h6 class="small title-style"><span>Delivery Address*</span></h6>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Delivery Address Line 1*</label>
                                        <input type="text" class="form-control form-control-sm" name="dline1" id="dline1" required>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Delivery Address Line 2*</label>
                                        <input type="text" class="form-control form-control-sm" name="dline2" id="dline2" required>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">City*</label>
                                        <input type="text" class="form-control form-control-sm" name="dcity" id="dcity">
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">State*</label>
                                        <input type="text" class="form-control form-control-sm" name="dstate" id="dstate">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Submit copy of BR Cetificate</label>
                                        <input type="file" class="form-control form-control-sm" name="image" style="padding-bottom:32px;" />
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Submit copy of VAT,NBT,SVAT Cetificate</label>
                                        <input class="form-control form-control-sm" id="certificates" name="cretificates" type="file" class="file" style="padding-bottom:32px;" />
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Business Status*</label><br>
                                        <input type="radio" id="Proprietorship" name="bstatus" value="Proprietorship" required>
                                        <label for="age1">Proprietorship</label>
                                        <input type="radio" id="bstatusPartnership" name="bstatus" value="Partnership" required>
                                        <label for="age2">Partnership</label>
                                        <input type="radio" id="bstatusIncorporation" name="bstatus" value="Incorporation" required>
                                        <label for="age3">Incorporation</label>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small font-weight-bold">Method of Payment*</label><br>
                                        <input type="radio" id="cashpayementmethod" name="payementmethod" value="Cash" required>
                                        <label for="age1">Cash</label>
                                        <input type="radio" id="bankpayementmethod" name="payementmethod" value="Bank" required>
                                        <label for="age2">Bank</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 text-right">
                                    <hr>
                                    <?php if($addcheck==1){ ?>
                                    <button type="submit" id="submitBtn" class="btn btn-primary btn-sm px-5"><i class="far fa-save"></i>&nbsp;Add</button>
                                    <?php } ?>
                                </div>
                            </div>
                            <input type="hidden" name="recordOption" id="recordOption" value="1">
                            <input type="hidden" name="recordID" id="recordID" value="">
                        </form>
                        <div class="row mt-3">
                            <div class="col-12">
                                <hr>
                                <div class="scrollbar pb-3" id="style-2">
                                    <table class="table table-bordered table-striped table-sm nowrap" id="tblcustomer">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Reference No</th>
                                                <th>BR No</th>
                                                <th>VAT No</th>
                                                <th>NBT No</th>
                                                <th>SVAT No</th>
                                                <th>Address</th>
                                                <th>City</th>
                                                <th class="text-right"></th>
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
    var addcheck = '<?php echo $addcheck; ?>';
    var editcheck = '<?php echo $editcheck; ?>';
    var statuscheck = '<?php echo $statuscheck; ?>';
    var deletecheck = '<?php echo $deletecheck; ?>';

    $('#tblcustomer').DataTable({
        "destroy": true,
        "processing": true,
        "serverSide": true,
        dom: "<'row'<'col-sm-5'B><'col-sm-2'l><'col-sm-5'f>>" + "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        responsive: true,
        lengthMenu: [
            [10, 25, 50, -1],
            [10, 25, 50, 'All'],
        ],
        "buttons": [{
                extend: 'csv',
                className: 'btn btn-success btn-sm',
                title: 'Customer  Information',
                text: '<i class="fas fa-file-csv mr-2"></i> CSV',
            },
            { 
                extend: 'pdf', 
                className: 'btn btn-danger btn-sm', 
                title: 'Location Information', 
                text: '<i class="fas fa-file-pdf mr-2"></i> PDF',
                orientation: 'landscape', 
                pageSize: 'legal', 
                customize: function(doc) {
                    doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split('');
                }
            },
            {
                extend: 'print',
                title: 'Customer  Information',
                className: 'btn btn-primary btn-sm',
                text: '<i class="fas fa-print mr-2"></i> Print',
                customize: function(win) {
                    $(win.document.body).find('table')
                        .addClass('compact')
                        .css('font-size', 'inherit');
                },
            },
            // 'copy', 'csv', 'excel', 'pdf', 'print'
        ],

        ajax: {
            url: "<?php echo base_url() ?>scripts/customerlist.php",
            type: "POST", // you can use GET
        },
        "order": [
            [0, "desc"]
        ],
        "columns": [
            // {
            //     "data": null,
            //     "render": function(data, type, full, meta) {
            //         return meta.settings._iRecordsDisplay - meta.row;
            //     }
            // },
            {
                "data": null,
                "render": function(data, type, full, meta) {
                    return meta.row + 1 + meta.settings._iDisplayStart;
                }
            },
            {
                "data": "customer"
            },
            {
                "data": "ref_no"
            },
            {
                "data": "bus_reg_no"
            },
            {
                "data": "vat_no"
            },
            {
                "data": "nbt_no"
            },
            {
                "data": "svat_no"
            },
            {
                "targets": [4],
                "render": function(data, type, row) {
                    return row.address_line1 + ',' + row.address_line2 + '';
                }
            },
            {
                "data": "city"
            },
            {
                "targets": -1,
                "className": 'text-right',
                "data": null,
                "render": function(data, type, full) {
                    var button = '';
                    if(editcheck==1){
                        button+='<button type="button" class="btn btn-primary btn-sm btnEdit mr-1" id="'+full['idtbl_customer']+'" data-toggle="tooltip" title="Edit"><i class="fas fa-pen"></i></button>';
                    }
                    if(full['status']==1 && statuscheck==1){
                        button+='<button type="button" data-url="Customer/Customerstatus/'+full['idtbl_customer']+'/2" data-toggle="tooltip" title="Active" data-actiontype="2" class="btn btn-success btn-sm mr-1 btntableaction"><i class="fas fa-check"></i></button>';
                    }else if(full['status']==2 && statuscheck==1){
                        button+='<button type="button" data-url="Customer/Customerstatus/'+full['idtbl_customer']+'/1" data-toggle="tooltip" title="Deactive" data-actiontype="1" class="btn btn-warning btn-sm mr-1 text-light btntableaction"><i class="fas fa-times"></i></button>';
                    }
                    if(deletecheck==1){
                        button+='<button type="button" data-url="Customer/Customerstatus/'+full['idtbl_customer']+'/3" data-toggle="tooltip" title="Delete" data-actiontype="3" class="btn btn-danger btn-sm text-light btntableaction"><i class="fas fa-trash-alt"></i></button>';
                    }

                    return button;
                }
            }
        ],
        drawCallback: function(settings) {
            $('[data-toggle="tooltip"]').tooltip();
        },
    });
    $('#tblcustomer tbody').on('click', '.btnEdit', async function() {
        var r = await Otherconfirmation("You want to Edit this ? ");
        if (r == true) {
            var id = $(this).attr('id');
            $.ajax({
                type: "POST",
                data: {
                    recordID: id
                },
                url: '<?php echo base_url() ?>Customer/Customeredit',
                success: function(result) { //alert(result);
                    var obj = JSON.parse(result);
                    $('#recordID').val(obj.id);
                    $('#customer_name').val(obj.name);
                    $('#business_regno').val(obj.business_regno);
                    $('#nbtno').val(obj.nbtno);
                    $('#svatno').val(obj.svatno);
                    $('#vat_customer').val(obj.vat_customer).trigger('change');
                    $('#telephoneno').val(obj.telephoneno);
                    $('#faxno').val(obj.faxno);
                    $('#dline1').val(obj.dline1);
                    $('#dline2').val(obj.dline2);
                    $('#dcity').val(obj.dcity);
                    $('#dstate').val(obj.dstate);
                    $('#line1').val(obj.line1);
                    $('#line2').val(obj.line2);
                    $('#city').val(obj.city);
                    $('#state').val(obj.state);
                    $('#ref_no').val(obj.ref_no);
                    // $('#country').val(obj.country);
                    $('#vatno').val(obj.vat_no);
                    //$('#bstatus').val(obj.business_status);
                    // $('#payementmethod').val(obj.payementmethod);

                    var payementmethod = obj.payementmethod;
                    //alert(busstatus);
                    if (payementmethod == "Cash") {
                        $('#cashpayementmethod').prop('checked', true);

                    } else {
                        $('#bankpayementmethod').prop('checked', true);
                    }
                    // $('#nic').val(obj.nic);
                    var busstatus = obj.business_status;
                    //alert(busstatus);
                    if (busstatus == "Proprietorship") {
                        $('#Proprietorship').prop('checked', true);

                    } else if (busstatus == "Partnership") {
                        $('#bstatusPartnership').prop('checked', true);
                    } else {
                        $('#bstatusIncorporation').prop('checked', true);
                    }
                    $('#recordOption').val('2');
                    $('#submitBtn').html('<i class="far fa-save"></i>&nbsp;Update');
                }
            });
        }
    });

    $('#vat_customer').change(function(){
        if($(this).val()==1){
            $('#svatno').prop('readonly', true);
            $('#vatno').prop('readonly', false);
        }
        else if($(this).val()==2){
            $('#svatno').prop('readonly', false);
            $('#vatno').prop('readonly', false);
        }
        else{
            $('#svatno').prop('readonly', true);
            $('#vatno').prop('readonly', true);
        }
    });
});


$(document).on("click", ".btntableactionnoreload", async function () {
    var url = '<?php echo base_url() ?>'+$(this).attr("data-url");
    var actiontype = $(this).attr("data-actiontype");
    var datareturn = await noReloadAjaxControl(url, actiontype);
    var tableId = $(this).closest("table").attr("id");

    var obj = JSON.parse(datareturn);
    if(obj.status==1){
        $('#hidebankreset').click();
        $('#'+tableId).DataTable().ajax.reload( null, false );
    }

    action(obj.action);
});
</script>
<?php include "include/footer.php"; ?>