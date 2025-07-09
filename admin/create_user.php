<?php
session_start();

// Only allow logged-in admin or moderator
if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [1, 2])) {
    header("Location: login.php");
    exit();
}

include('config/config.php');
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role_id = intval($_POST['role_id']);
    $created_by = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $created_at = date('Y-m-d H:i:s');
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    // Validation: Admin can create moderator/user, moderator can only create user
    // echo "<pre>";
    // var_dump($_SESSION);
    // exit;
    if ($_SESSION['role_id'] == 1) {
        $valid_roles = [2, 3];
    } elseif ($_SESSION['role_id'] == 2) {
        $valid_roles = [3];
    } else {
        $valid_roles = [];
    }
    if (empty($username) || empty($email) || empty($_POST['password']) || !in_array($role_id, $valid_roles)) {
        $error = $_SESSION['role_id'] == 2 ? "Moderators can only create users. All fields are required." : "All fields are required and role must be moderator or user.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role_id, created_by, created_at, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$username, $email, $password, $role_id, $created_by, $created_at, $start_date, $end_date])) {
            $success = "User created successfully.";
        } else {
            $error = "Failed to create user. Email may already exist.";
        }
    }
}




include('assets/inc/incHeader.php');
?>



<title>Response Analytics</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">


<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include('assets/inc/incSidebar.php'); ?>
            <div class="layout-page">
                <?php include('assets/inc/incNavbar.php'); ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h2>Create New User/Moderator</h2>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php elseif ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Role</label>
                                <select name="role_id" id="role_id" class="form-select" required>
                                    <option value="">select option</option>
                                    <?php if ($_SESSION['role_id'] == 1): ?>
                                        <option value="2">Moderator</option>
                                        <option value="3">User</option>
                                    <?php elseif ($_SESSION['role_id'] == 2): ?>
                                        <option value="3">User</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="mb-3" id="start_date_group" style="display:none;">
                                <label>Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control">
                            </div>
                            <div class="mb-3" id="end_date_group" style="display:none;">
                                <label>End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-primary">Create User</button>
                        </form>

                        <!-- Show/hide Start/End Date fields based on role selection -->
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const roleSelect = document.getElementById('role_id');
                                const startDateGroup = document.getElementById('start_date_group');
                                const endDateGroup = document.getElementById('end_date_group');
                                const startDateInput = document.getElementById('start_date');
                                const endDateInput = document.getElementById('end_date');

                                function toggleDateFields() {
                                    if (roleSelect.value === '3') { // User
                                        startDateGroup.style.display = 'block';
                                        endDateGroup.style.display = 'block';
                                        startDateInput.required = true;
                                        endDateInput.required = true;
                                    } else {
                                        startDateGroup.style.display = 'none';
                                        endDateGroup.style.display = 'none';
                                        startDateInput.required = false;
                                        endDateInput.required = false;
                                    }
                                }
                                roleSelect.addEventListener('change', toggleDateFields);
                                toggleDateFields(); // Initial call
                            });
                        </script>

                        <?php if (isset($_SESSION['role_id'])): ?>
                            <?php if ($_SESSION['role_id'] == 1): ?>
                                <a href="index.php" class="btn btn-secondary mt-3">Back to Admin Dashboard</a>
                            <?php elseif ($_SESSION['role_id'] == 2): ?>
                                <a href="moderator_dashboard.php" class="btn btn-secondary mt-3">Back to Moderator Dashboard</a>
                            <?php elseif ($_SESSION['role_id'] == 3): ?>
                                <a href="user_dashboard.php" class="btn btn-secondary mt-3">Back to User Dashboard</a>
                            <?php else: ?>
                                <a href="index.php" class="btn btn-secondary mt-3">Back to Home</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="index.php" class="btn btn-secondary mt-3">Back to Home</a>
                        <?php endif; ?>

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