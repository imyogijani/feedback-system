<aside
    id="layout-menu"
    class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="index.php" class="app-brand-link">
            <span class="app-brand-logo demo">

            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">Feedback Sys.</span>
        </a>

        <a
            href="javascript:void(0);"
            class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <?php
        // Show dashboard link based on login role
        if (isset($_SESSION['admin_logged_in'])): ?>
            <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <a href="index.php" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-home-circle"></i>
                    <div data-i18n="Analytics">Admin Dashboard</div>
                </a>
            </li>
        <?php elseif (isset($_SESSION['moderator_logged_in'])): ?>
            <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'moderator_dashboard.php' ? 'active' : ''; ?>">
                <a href="moderator_dashboard.php" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-home-circle"></i>
                    <div data-i18n="Analytics">Moderator Dashboard</div>
                </a>
            </li>
        <?php elseif (isset($_SESSION['user_id'])): ?>
            <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'user_dashboard.php' ? 'active' : ''; ?>">
                <a href="user_dashboard.php" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-home-circle"></i>
                    <div data-i18n="Analytics">User Dashboard</div>
                </a>
            </li>
        <?php endif; ?>
        <!-- Other menu items can go here, or you can also restrict them by role if needed -->
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'form_generator.php' ? 'active' : ''; ?>">
            <a href="form_generator.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">Form Generator</div>
            </a>
        </li>
        <?php if (isset($_SESSION['role_id']) && in_array($_SESSION['role_id'], [1, 2])): ?>
            <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'create_user.php' ? 'active' : ''; ?>">
                <a href="create_user.php" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user-plus"></i>
                    <div data-i18n="CreateUser">Create User/Moderator</div>
                </a>
            </li>
        <?php endif; ?>
        <?php if (isset($_SESSION['role_id']) && in_array($_SESSION['role_id'], [1, 2])): ?>
            <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : ''; ?>">
                <a href="manage_users.php" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user"></i>
                    <div data-i18n="ManageUsers">Manage Users</div>
                </a>
            </li>
        <?php endif; ?>
        <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
            <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'demo_request.php' ? 'active' : ''; ?>">
                <a href="demo_request.php" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-home-circle"></i>
                    <div data-i18n="Analytics">Demo Requests</div>
                </a>
            </li>
        <?php endif; ?>
        <!-- <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'qr_generator.php' ? 'active' : ''; ?>">
            <a href="qr_generator.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">QR Code Generator</div>
            </a>
        </li> -->
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : ''; ?>">
            <a href="analytics.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">Analytics</div>
            </a>
        </li>
    </ul>
</aside>