<?php include "include/header.php"; ?>
<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --primary-blue: #C8102E;        /* main red - change this */
        --primary-dark: #9E0C24;        /* hover / darker red */
        --primary-rgb: 200, 16, 46;     /* same as main red, in RGB */
        --primary-dark-rgb: 158, 12, 36; /* same as darker red, in RGB */
        --text-dark: #1F2937;
        --text-gray: #6B7280;
        --text-light-gray: #9CA3AF;
        --border-color: #D1D5DB;
        --bg-light: #F9FAFB;
        --error-red: #EF4444;
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--bg-light);
        min-height: 100vh;
    }

    /* Layout & Backgrounds */
    .full-height {
        min-height: 100vh;
    }

    .bg-custom-gradient {
        background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.92) 0%, rgba(var(--primary-dark-rgb), 0.75) 100%), 
                    url("images/electronicsbg.png") center/cover no-repeat;
        color: white;
    }

    .logo-box {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 20px;
        display: inline-flex;
    }

    .logo-box img {
        height: 100px;
        object-fit: contain;
    }

    .main-title {
        font-size: 2.5rem;
        letter-spacing: -0.5px;
        color: #FFFFFF;
    }

    .check-icon {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.9);
    }

    .login-card {
        border-radius: 16px;
        box-shadow: 0px 10px 40px rgba(0, 0, 0, 0.06);
        border: none;
        width: 100%;
        max-width: 460px;
    }

    /* Form Customizations over Bootstrap */
    .custom-label {
        font-size: 13px;
        font-weight: 500;
        color: var(--text-dark);
    }

    .required-star {
        color: var(--error-red);
    }

    .input-group-text {
        background-color: white;
        border-right: none;
        color: var(--text-light-gray);
    }

    .form-control {
        border-left: none;
        font-size: 14px;
    }
    
    .form-control:focus {
        box-shadow: none;
        border-color: var(--primary-blue);
    }
    
    /* Fix border on focus for the whole group */
    .input-group:focus-within .input-group-text,
    .input-group:focus-within .form-control {
        border-color: var(--primary-blue);
    }
    
    .input-group-text.append {
        border-left: none;
        border-right: 1px solid #ced4da;
        background: white;
        cursor: pointer;
    }

    .input-group:focus-within .input-group-text.append {
        border-color: var(--primary-blue);
    }

    .custom-select {
        font-size: 14px;
        color: var(--text-dark);
    }
    
    .custom-select:focus {
        box-shadow: none;
        border-color: var(--primary-blue);
    }

    .btn-primary-custom {
        background-color: var(--primary-blue);
        border-color: var(--primary-blue);
        font-weight: 500;
        padding: 12px;
        border-radius: 8px;
    }

    .btn-primary-custom:hover {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
    }

    .text-primary-custom {
        color: var(--primary-blue) !important;
    }

    .small-text {
        font-size: 13px;
    }

    @media (max-width: 767.98px) {
        .mobile-hide {
            display: none !important;
        }
        .mobile-overlap {
            margin-top: -30px;
            position: relative;
            z-index: 10;
        }
        .bg-custom-gradient {
            padding: 40px 20px !important;
        }
        .logo-box {
            padding: 12px;
            border-radius: 15px;
        }
        .logo-box img {
            height: 55px; /* Reduced logo size on mobile */
        }
        .main-title {
            font-size: 1.75rem; /* Reduced topic/heading size on mobile */
        }
    }
</style>
<?php
 $companyaql="SELECT * FROM `tbl_company`";
 $companylist = $this->db->query($companyaql);
?>
<div class="container-fluid p-0">
    <div class="row no-gutters full-height">
        
        <!-- Left Panel -->
        <div class="col-md-6 bg-custom-gradient d-flex flex-column justify-content-center p-5 text-center text-md-left">
            
            <div class="mb-4">
                <div class="logo-box">
                    <img src="images/logo.jpeg" alt="Company Logo">
                </div>
            </div>
            
            <h1 class="font-weight-bold mb-3 main-title">Buying &amp; Selling<br class="d-none d-md-block">Management Portal</h1>
            
            <p class="mb-5 mobile-hide" style="font-size: 16px; color: rgba(255, 255, 255, 0.85); max-width: 90%;">
                Streamline your buying and selling operations with our comprehensive management system. Track stock, handle purchases and sales, and keep every branch in sync.
            </p>
            
            <ul class="list-unstyled mobile-hide">
                <li class="d-flex align-items-center mb-4">
                    <div class="check-icon mr-3"><i class="fas fa-check"></i></div>
                    <span style="font-size: 15px; color: rgba(255, 255, 255, 0.85);">Real-time stock tracking</span>
                </li>
                <li class="d-flex align-items-center mb-4">
                    <div class="check-icon mr-3"><i class="fas fa-check"></i></div>
                    <span style="font-size: 15px; color: rgba(255, 255, 255, 0.85);">Multi-branch management</span>
                </li>
                <li class="d-flex align-items-center">
                    <div class="check-icon mr-3"><i class="fas fa-check"></i></div>
                    <span style="font-size: 15px; color: rgba(255, 255, 255, 0.85);">Sales &amp; purchase reports</span>
                </li>
            </ul>
        </div>

        <!-- Right Panel -->
        <div class="col-md-6 d-flex flex-column justify-content-center align-items-center p-3 p-md-5 bg-light position-relative">
            
            <div class="card login-card p-4 p-md-5 mobile-overlap">
                <div class="card-body p-0">
                    <h2 class="font-weight-bold" style="color: var(--text-dark); font-size: 28px;">Welcome back</h2>
                    <p class="mb-4" style="color: var(--text-gray); font-size: 14px;">Please enter your credentials to continue</p>

                    <form action="<?php echo base_url() ?>Welcome/LoginUser" method="post" autocomplete="off">
                        
                        <div class="form-group mb-3">
                            <label class="custom-label">Email Address</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-envelope"></i></span>
                                </div>
                                <input id="username" name="username" type="email" class="form-control" placeholder="Enter your email" required autofocus>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="custom-label">Password</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-lock" style="font-size: 14px;"></i></span>
                                </div>
                                <input id="password" name="password" type="password" class="form-control" placeholder="Enter your password" required>
                                <div class="input-group-append">
                                    <span class="input-group-text append"><i class="far fa-eye"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="custom-label">Company <span class="required-star">*</span></label>
                            <select class="custom-select" name="company_id" id="company_id" required>
                                <option value="" disabled selected>Select</option>
                                <?php foreach($companylist->result() as $rowcompanylist){ ?>
                                <option value="<?php echo $rowcompanylist->idtbl_company ?>">
                                    <?php echo $rowcompanylist->company ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group mb-4">
                            <label class="custom-label">Company Branch <span class="required-star">*</span></label>
                            <select class="custom-select" name="branch_id" id="branch_id" required>
                                <option value="" disabled selected>Select</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4 small-text">
                            <div style="color: var(--text-gray); cursor: pointer;">
                                Remember me
                            </div>
                            <a href="#" class="text-primary-custom font-weight-bold text-decoration-none">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary-custom btn-block text-white">
                            <i class="fas fa-lock mr-2" style="font-size: 13px;"></i> Login
                        </button>
                        <input type="hidden" name="company_text" id="company_text">
                        <input type="hidden" name="branch_text" id="branch_text">
                    </form>
                </div>
            </div>
            
            <div class="position-absolute w-100 text-center" style="bottom: 20px; color: var(--text-light-gray); font-size: 12px;">
                Copyright © Erav Technology 2026
            </div>
            
        </div>
    </div>
</div>
<?php include "include/footerscripts.php"; ?>
<?php include "include/footer.php"; ?>

<script>
    $(document).ready(function() {
        sessionStorage.clear();

        $('#company_id').change(function() {
            var company_id = $(this).val();
            if (company_id != '') {
                $.ajax({
                    url: '<?php echo base_url('Welcome/Getbranchaccocompany'); ?>', // Replace with your actual controller and method
                    type: 'post',
                    data: {company_id: company_id},
                    dataType: 'json',
                    success:function(response) {
                        var len = response.length;
                        $('#branch_id').empty();
                        $('#branch_id').append("<option value=''>Select</option>");
                        for (var i = 0; i < len; i++) {
                            var id = response[i]['idtbl_company_branch'];
                            var name = response[i]['branch'];
                            $('#branch_id').append("<option value='" + id + "'>" + name + "</option>");
                        }
                    }
                });
            } else {
                $('#branch_id').empty();
                $('#branch_id').append("<option value=''>Select</option>");
            }
        });


        $('#branch_id').change(function() {
            var companyname = $("#company_id option:selected").text().trim();
            var branchname = $("#branch_id option:selected").text().trim();

            $('#company_text').val(companyname);
            $('#branch_text').val(branchname);
        })
    });
</script>