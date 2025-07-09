<?php
session_start();
include('config/config.php');

if (!isset($_SESSION['reset_email'])) {
    header('Location: forgot-password.php');
    exit;
}

$error = $_SESSION['reset_error'] ?? '';
$success = $_SESSION['reset_success'] ?? '';
unset($_SESSION['reset_error'], $_SESSION['reset_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if (empty($password) || empty($confirm_password)) {
        $_SESSION['reset_error'] = 'Please fill in all fields.';
        header('Location: edit_password.php');
        exit;
    }
    if ($password !== $confirm_password) {
        $_SESSION['reset_error'] = 'Passwords do not match.';
        header('Location: edit_password.php');
        exit;
    }
    if (strlen($password) < 6) {
        $_SESSION['reset_error'] = 'Password must be at least 6 characters.';
        header('Location: edit_password.php');
        exit;
    }
    $email = $_SESSION['reset_email'];
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('UPDATE users SET password = ? WHERE email = ?');
    if ($stmt->execute([$hashed, $email])) {
        unset($_SESSION['reset_email']);
        $_SESSION['reset_success'] = 'Password updated successfully. You can now log in.';
        header('Location: login.php');
        exit;
    } else {
        $_SESSION['reset_error'] = 'Failed to update password. Please try again.';
        header('Location: edit_password.php');
        exit;
    }
}
?>
<!DOCTYPE html>

<!-- =========================================================
* Sneat - Bootstrap 5 HTML Admin Template - Pro | v1.0.0
==============================================================

* Product Page: https://themeselection.com/products/sneat-bootstrap-html-admin-template/
* Created by: ThemeSelection
* License: You must have a valid license purchased in order to legally use the theme for your project.
* Copyright ThemeSelection (https://themeselection.com)

=========================================================
 -->
<!-- beautify ignore:start -->
<html
    lang="en"
    class="light-style customizer-hide"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="../assets/"
    data-template="vertical-menu-template-free">

<?php
include('assets/inc/incHeader.php')
?>

<body>
    <!-- Content -->

    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-4">
                <!-- Forgot Password -->
                <div class="card">
                    <div class="card-body">
                        <div class="app-brand justify-content-center mb-3">
                            <a href="index.php" class="app-brand-link gap-2">
                                <span class="app-brand-logo demo"></span>
                                <span class="app-brand-text demo text-body fw-bolder">Admin For System</span>
                            </a>
                        </div>
                        <h4 class="mb-2">Reset Password 🔒</h4>
                        <p class="mb-4">Enter your new password below.</p>
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= $success ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php elseif ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        <form method="POST" action="edit_password.php" class="mb-3">
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="6" placeholder="Enter new password">
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Confirm new password">
                            </div>
                            <button type="submit" class="btn btn-primary d-grid w-100">Update Password</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="login.php" class="d-flex align-items-center justify-content-center">
                                <i class="bx bx-chevron-left scaleX-n1-rtl bx-sm"></i>
                                Back to login
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /Forgot Password -->
            </div>
        </div>
    </div>

    <!-- / Content -->


    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="../assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->

    <!-- Main JS -->
    <script src="../assets/js/main.js"></script>

    <!-- Page JS -->

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>

</html>