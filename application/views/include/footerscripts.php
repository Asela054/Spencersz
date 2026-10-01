<!--<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>-->
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js'></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/scripts.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js"></script>
<!-- <script src="<?php echo base_url() ?>assets/demo/chart-area-demo.js"></script> -->
<!--<script src="<?php echo base_url() ?>assets/demo/chart-bar-demo.js"></script>-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>
<!-- <script src="https://cdn.datatables.net/fixedcolumns/3.3.3/js/dataTables.fixedColumns.min.js"></script> -->
<!--<script src="<?php echo base_url() ?>assets/demo/datatables-demo.js"></script>-->
<script src="<?php echo base_url() ?>assets/js/script.js"></script>
<script src="<?php echo base_url() ?>assets/js/sidebar-popover.js"></script>
<script src="<?php echo base_url() ?>assets/js/bootstrap-notify.js"></script>
<script src="<?php echo base_url() ?>assets/js/select2.full.js"></script>
<script src="<?php echo base_url() ?>assets/js/jquery.serializejson.js"></script>
<script src="<?php echo base_url() ?>assets/slick/slick.js"></script>
<script src="<?php echo base_url() ?>assets/js/print.js"></script>
<script src="<?php echo base_url() ?>assets/js/jquery.hotkeys.js"></script>
<script src="<?php echo base_url() ?>assets/js/table2csv.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.0/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.0/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.0/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.0/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>   
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
        setTimeout(tablerowhighlight, 1000);
    });
    <?php if($this->router->fetch_class()=='Cashier' && $this->router->fetch_method()=='Dashboard'){}else{ ?>
    function tablerowhighlight(){
        $('table tbody').on('click', 'tr', function(){
            $('table tbody>tr').removeClass('table-warning text-dark');
            $(this).addClass('table-warning text-dark');
        });
    }
    <?php } ?>

    actionText=[];
    var actionText=$('#actiontext').val();
    action(actionText);

    function action(data) {
        if(data!=''){
            var obj=JSON.parse(data);
            var icon, title;

            if(obj.type=='success'){icon='success'; title=obj.message;}
            else if(obj.type=='primary'){icon='success'; title=obj.message;}
            else if(obj.type=='warning'){icon='warning'; title=obj.message;}
            else if(obj.type=='danger'){icon='error'; title=obj.message;}

            Swal.fire({
                position: "top-end",
                icon: icon,
                title: title,
                showConfirmButton: false,
                timer: 2500
            });
        }
    }

    function actionreload(data) {
        if(data!=''){
            var obj=JSON.parse(data);
            var icon, title;

            if(obj.type=='success'){icon='success'; title=obj.message;}
            else if(obj.type=='primary'){icon='success'; title=obj.message;}
            else if(obj.type=='warning'){icon='warning'; title=obj.message;}
            else if(obj.type=='danger'){icon='error'; title=obj.message;}

            Swal.fire({
                position: "top-end",
                icon: icon,
                title: title,
                showConfirmButton: false,
                timer: 2500
            }).then(() => {
                window.location.reload();
            });
        }
    }

    $(document).on("click", ".btntableaction", function () {
        var url = '<?php echo base_url() ?>'+$(this).attr("data-url");
        var actiontype = $(this).attr("data-actiontype");

        var title;

        if(actiontype==1){title='You want to active this?'}
        else if(actiontype==2){title='You want to deactive this?'}
        else if(actiontype==3){title='You want to remove this?'}
        else if(actiontype==4){title='You want to approve this production, please double check befor appove?'}
        else if(actiontype==5){title='you want to lock this?'}
        else if(actiontype==6){title='you want manually complete this?'}

        Swal.fire({
            title: "Are you sure?",
            text: title,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Confirm",
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-danger'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href=url;
            }
        });
    });

    function Otherconfirmation(title){
        return Swal.fire({
            title: "Are you sure?",
            text: title,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Confirm",
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-danger'
            }
        }).then((result) => {
            return result.isConfirmed;
        });
    }

    async function noReloadAjaxControl(parseurl, actiontype) {
        return new Promise((resolve, reject) => {
            var title;

            if (actiontype == 1) { title = 'You want to activate this?'; }
            else if (actiontype == 2) { title = 'You want to deactivate this?'; }
            else if (actiontype == 3) { title = 'You want to remove this?'; }
            else if (actiontype == 4) { title = 'You want to approve this production? Please double-check before approval.'; }
            else if (actiontype == 5) { title = 'You want to lock this?'; }

            Swal.fire({
                title: "Are you sure?",
                text: title,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Confirm",
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-danger'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: '',
                        html: '<div class="div-spinner"><div class="custom-loader"></div></div>',
                        allowOutsideClick: false,
                        showConfirmButton: false, 
                        backdrop: `rgba(255, 255, 255, 0.5)`,
                        customClass: {
                            popup: 'fullscreen-swal'
                        },
                        didOpen: () => {
                            document.body.style.overflow = 'hidden';
                            
                            $.ajax({
                                type: "GET",
                                url: parseurl,
                                success: function (result) {
                                    Swal.close();
                                    document.body.style.overflow = 'auto';
                                    resolve(result);
                                },
                                error: function (error) {
                                    Swal.close();
                                    document.body.style.overflow = 'auto';
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Something went wrong. Please try again later.'
                                    });
                                    reject(error);
                                }
                            });
                        }
                    });
                } else {
                    resolve(null); 
                }
            });
        });
    }
    
    // Sidebar collapse toggle button (mirrors #sidebarToggle behavior)
    $("#sidenavToggleBtn").on("click", function (e) {
        e.preventDefault();
        $("body").toggleClass("sidenav-toggled");
    });

    // Live date/time in topbar
    function updateTopnavDateTime() {
        var now = new Date();
        var timeEl = document.getElementById('topnavTime');
        var dateEl = document.getElementById('topnavDate');
        if (!timeEl || !dateEl) return;
        timeEl.textContent = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
        dateEl.textContent = now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
    }
    updateTopnavDateTime();
    setInterval(updateTopnavDateTime, 30000);
</script>
