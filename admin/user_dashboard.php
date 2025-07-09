<?php

session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$isGoogleLogin = isset($_SESSION['auth_method']) && $_SESSION['auth_method'] === 'google';
$isUserLogin = isset($_SESSION['username']) && isset($_SESSION['role_id']) && $_SESSION['role_id'] == 3;

if (!$isGoogleLogin && !$isUserLogin) {
    header("Location: login.php");
    exit();
}

include('config/config.php');
include('assets/inc/incHeader.php');
$limit = 10; // Forms per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$user_id = $_SESSION['user_id'];
// Count total forms created by this user
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM forms WHERE created_by = ?");
$stmt->execute([$user_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$totalForms = $row['total'];
$totalPages = ceil($totalForms / $limit);

// Get paginated forms created by this user, including created_by username
$stmt = $conn->prepare("SELECT f.id, f.title, f.created_at, u.username AS created_by FROM forms f LEFT JOIN users u ON f.created_by = u.id WHERE f.created_by = ? ORDER BY f.created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$formList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!-- Font Awesome CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
    .card {
        background: #f7f7f7;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 12px;
        border: 1px solid #ccc;
    }

    .pagination {
        display: flex;
        list-style: none;
        padding: 0;
    }

    .pagination li {
        margin: 0 5px;
    }

    .pagination .page-link {
        display: block;
        padding: 8px 12px;
        background: #eee;
        color: #333;
        text-decoration: none;
        border-radius: 5px;
    }

    .pagination .active .page-link {
        background: #007bff;
        color: white;
    }
</style>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            <?php include('assets/inc/incSidebar.php'); ?>
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                <?php include('assets/inc/incNavbar.php'); ?>
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <!-- Your page content goes here -->
                        <h1>User Dashboard</h1>

                        <div class="card">
                            <h2 class="text-center">Total Feedback Forms: <?= $totalForms ?></h2>
                        </div>

                        <div class="card">
                            <h3>Form List</h3>
                            <table>
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Form Title</th>
                                        <th>Created At</th>
                                        <th>Created By</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $serial = 1; ?>
                                    <?php foreach ($formList as $form): ?>
                                        <tr>
                                            <td><?= $serial ?></td>
                                            <td><?= htmlspecialchars($form['title']) ?></td>
                                            <td><?= htmlspecialchars($form['created_at']) ?></td>
                                            <td><?= htmlspecialchars($form['created_by']) ?>
                                                <?php
                                                // Show Google Login badge if the creator is a Google user
                                                if (!empty($form['created_by']) && isset($_SESSION['auth_method']) && $_SESSION['auth_method'] === 'google') : ?>
                                                    <span> - (Google Login)</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="edit_form.php?id=<?= $form['id'] ?>" title="Edit">
                                                    <i class="fa-solid fa-pen-to-square" style="color: #0d6efd;"></i>
                                                </a>
                                                <a href="delete_form.php?id=<?= $form['id'] ?>" title="Delete" onclick="return confirm('Are you sure?')">
                                                    <i class="fa-solid fa-trash" style="color: #dc3545;"></i>
                                                </a>
                                                <a href="publish_form.php?id=<?= $form['id'] ?>" title="Publish">
                                                    <i class="fa-solid fa-upload" style="color: #198754;"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php $serial++; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div style="margin-top: 20px;">
                                <nav>
                                    <ul class="pagination">
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            </div>

                        </div>

                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success position-fixed bottom-0 end-0 m-3" role="alert" style="z-index: 2000; width: auto;">
                                <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                                <?php unset($_SESSION['success_message']); ?>
                            </div>
                        <?php endif; ?>

                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    <?php include('assets/inc/incFooter.php'); ?>
                    <!-- / Footer -->
                </div>
                <!-- / Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="../assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="../assets/vendor/libs/apex-charts/apexcharts.js"></script>

    <!-- Main JS -->
    <script src="../assets/js/main.js"></script>

    <!-- Page JS -->
    <script src="../assets/js/dashboards-analytics.js"></script>

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>

    <style>
        .alert {
            z-index: 2000 !important;
            position: fixed;
            bottom: 0;
            right: 0;
            margin: 1rem;
            width: auto;
        }
    </style>

    <script>
        // Automatically hide the toast after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => alert.remove());
        }, 5000);
    </script>
</body>

</html>