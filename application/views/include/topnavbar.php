<nav class="topnav navbar navbar-expand shadow navbar-light bg-white" id="sidenavAccordion">
    <!-- <a class="navbar-brand d-none d-sm-block" href="#" style="color: blue;  font-size: 14px;"><i class="fas fa-print mr-2"></i><?php // echo ucfirst($_SESSION['companyname']); ?></a> -->
    <button class="btn btn-icon btn-transparent-dark order-1 order-lg-0 mr-lg-2" id="sidebarToggle" href="#"><i data-feather="menu"></i></button>
    <div class="topnav-datetime d-none d-sm-block">
        <i data-feather="calendar"></i>
        <span class="dt-time" id="topnavTime">04:52 PM</span>
        <span class="dt-sep">|</span>
        <span class="dt-date" id="topnavDate">Fri, Aug 21</span>
    </div>
    <ul class="navbar-nav align-items-center ml-auto">
        <li class="nav-item dropdown no-caret dropdown-notifications">
            <a class="dropdown-toggle btn-icon-notify" id="navbarDropdownAlerts" href="javascript:void(0);" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                ><i data-feather="bell"></i><span class="notify-dot"></span
            ></a>
            <div class="dropdown-menu dropdown-menu-right border-0 shadow animated--fade-in-up" aria-labelledby="navbarDropdownAlerts">
                <h6 class="dropdown-header dropdown-notifications-header"><i class="mr-2" data-feather="bell"></i>Alerts Center</h6>
                <a class="dropdown-item dropdown-notifications-footer" href="#!">View All Alerts</a>
            </div>
        </li>
        <li class="nav-item dropdown no-caret dropdown-user">
            <a class="dropdown-toggle" id="navbarDropdownUserImage" href="javascript:void(0);" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><div class="avatar-circle">SA</div></a>
            <div class="dropdown-menu dropdown-menu-right border-0 shadow animated--fade-in-up" aria-labelledby="navbarDropdownUserImage">
                <h6 class="dropdown-header d-flex align-items-center">
                    <div class="avatar-circle mr-2">
                        <?php
                        $string = ucfirst($_SESSION['name']);

                        $initials = implode('', array_map(function ($w) {
                            return isset($w[0]) ? $w[0] : '';
                        }, explode(' ', $string)));

                        echo strtoupper($initials); // Output: LASER
                        ?>
                    </div>
                    <div class="dropdown-user-details">
                        <div class="dropdown-user-details-name"><?php echo ucfirst($_SESSION['name']); ?></div>
                        <div class="dropdown-user-details-email"><?php echo $_SESSION['typename']; ?></div>
                    </div>
                </h6>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#!"
                    ><div class="dropdown-item-icon"><i data-feather="settings"></i></div>
                    Account</a
                ><a class="dropdown-item" href="<?php echo base_url() ?>Welcome/Logout"
                    ><div class="dropdown-item-icon"><i data-feather="log-out"></i></div>
                    Logout</a
                >
            </div>
        </li>
    </ul>
</nav>
