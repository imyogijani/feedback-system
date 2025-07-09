<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Only allow logged-in admin or moderator
if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [1, 2])) {
    header("Location: login.php");
    exit();
}

include('config/config.php');


// Fetch users, include firebase_uid of creator
if ($_SESSION['role_id'] == 1) { // Admin: see all users
    $stmt = $conn->prepare("SELECT u.*, r.role_name AS role_name, c.username AS created_by_name, c.firebase_uid AS created_by_firebase_uid
                        FROM users u
                        LEFT JOIN roles r ON u.role_id = r.id
                        LEFT JOIN users c ON u.created_by = c.id
                        ORDER BY u.created_at DESC");
    $stmt->execute();
} else { // Moderator: see only users they created
    $stmt = $conn->prepare("SELECT u.*, r.role_name AS role_name, c.username AS created_by_name, c.firebase_uid AS created_by_firebase_uid
                        FROM users u
                        LEFT JOIN roles r ON u.role_id = r.id
                        LEFT JOIN users c ON u.created_by = c.id
                        WHERE u.created_by = ?
                        ORDER BY u.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
}
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Flash messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);



include('assets/inc/incHeader.php');
?>



<title>Manage Users</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include('assets/inc/incSidebar.php'); ?>
            <div class="layout-page">
                <?php include('assets/inc/incNavbar.php'); ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h2>Manage Users</h2>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <table class="table table-bordered table-hover mt-3">
                            <thead class="table-light">
                                <tr>
                                    <th>Sr. No.</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Created By</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $sr = 1;
                                foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= $sr++ ?></td>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= htmlspecialchars($user['role_name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($user['created_by_name'] ?? 'System') ?>
                                            <?php if (!empty($user['firebase_uid'])): ?>
                                                <span> - (Google Login)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($user['start_date'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($user['end_date'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($user['created_at']) ?></td>
                                        <td>
                                            <a href="edit_manage_user.php?id=<?= $user['id'] ?>" title="Edit">
                                                <i class="fa-solid fa-pen-to-square" style="color: #0d6efd;"></i>
                                            </a>
                                            <a href="delete_manage_user.php?id=<?= $user['id'] ?>" title="Delete" onclick="return confirm('Are you sure you want to delete this user?')">
                                                <i class="fa-solid fa-trash" style="color: #dc3545;"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No users found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <a href="create_user.php" class="btn btn-success">➕ Add New User</a>
                        <a href="<?= $_SESSION['role_id'] == 1 ? 'index.php' : 'moderator_dashboard.php' ?>" class="btn btn-secondary">Back to Dashboard</a>


                    </div>

                    <!-- Footer -->
                    <?php include('assets/inc/incFooter.php'); ?>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <!-- Core JS -->
    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../assets/vendor/js/menu.js"></script>
    <script src="../assets/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/dashboards-analytics.js"></script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>

    <!-- Toast auto-hide -->
    <script>
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => alert.remove());
        }, 5000);
    </script>

</body>

</html>