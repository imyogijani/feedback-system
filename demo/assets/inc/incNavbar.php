<?php
// Get logged-in user ID and role
$userId =  $_SESSION['user_id'] ?? null;
if (isset($_SESSION['user_id'])) {
    $role = 'user';
} else {
    $role = null;
}
$userData = [];
// Debugging line to check session data

if ($userId) {
    // Fetch user data from DB for any logged-in user
    $stmt = $conn->prepare("SELECT * FROM demo_requests WHERE id = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    // var_dump($userData); // Debugging line to check user data
}

// Debugging line to check user data
// Fallback values
$username = htmlspecialchars($userData['first_name'] ?? 'User');
$profileImage = !empty($userData['profile_image'])
    ? 'assets/images/' . htmlspecialchars($userData['profile_image'])
    : 'assets/img/default-avatar.png';
$roleDisplay = ucfirst(htmlspecialchars($role ?? 'User'));
?>

<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

        <ul class="navbar-nav flex-row align-items-center ms-auto">

            <!-- <div class="btn btn-danger">Session expires in: <span id="sessionCountdown"></span></div> -->
            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="<?= $profileImage ?>" alt="Profile" width="40" height="40" style="border-radius: 50%;" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="<?= $profileImage ?>" alt="Avatar" width="40" height="40" style="border-radius: 50%;" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-semibold d-block"><?= $username ?></span>
                                    <small class="text-muted"><?= $roleDisplay ?></small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="profile.php">
                            <i class="bx bx-user me-2"></i>
                            <span class="align-middle">My Profile</span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="logout.php">
                            <i class="bx bx-power-off me-2"></i>
                            <span class="align-middle">Log Out</span>
                        </a>
                    </li>
                </ul>
            </li>
            <!--/ User -->

        </ul>
    </div>
</nav>