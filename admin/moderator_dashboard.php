<?php

session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
// Only allow moderators (role_id = 2) to access this page
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
    header("Location: login.php");
    exit();
}

require_once 'config/config.php';
require_once 'assets/inc/incHeader.php';

// Pagination settings
$limit = 10; // Forms per page
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$offset = ($page - 1) * $limit;

try {
    // Count total forms for current user (created by or created for)
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM forms_combined WHERE created_by = :user_id OR created_for = :user_id");
    $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $totalForms = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = max(1, ceil($totalForms / $limit));

    // Ensure page number doesn't exceed total pages
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $limit;

    // Get paginated forms with creator info for current user (created by or created for)
    $stmt = $conn->prepare("
        SELECT f.id, f.title, f.created_at, f.questions_json, f.created_for, u.username AS created_by, u.firebase_uid 
        FROM forms_combined f 
        LEFT JOIN users u ON f.created_by = u.id
        WHERE f.created_by = :user_id OR f.created_for = :user_id
        ORDER BY f.created_at DESC 
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $formList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['alert_message'] = "An error occurred while fetching the forms.";
    $formList = [];
    $totalPages = 0;
    $totalForms = 0;
}
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
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h1>Moderator Dashboard</h1>

                        <div class="card">
                            <h2 class="text-center">Total Feedback Forms: <?= htmlspecialchars($totalForms) ?></h2>
                        </div>

                        <div class="card">
                            <h3>Form List</h3>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th width="5%">S.No</th>
                                            <th width="25%">Form Title</th>
                                            <th width="20%">Created At</th>
                                            <th width="25%">Created By</th>
                                            <th width="25%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $serial = $offset + 1;
                                        foreach ($formList as $form):
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($form['id']) ?></td>
                                                <!-- <td><?= htmlspecialchars($serial++) ?></td> -->
                                                <td><?= htmlspecialchars($form['title']) ?></td>
                                                <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($form['created_at']))) ?></td>
                                                <td>
                                                    <?= htmlspecialchars($form['created_by']) ?>
                                                    <?php if (!empty($form['firebase_uid'])): ?>
                                                        <span class="badge bg-info">Google</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="action-links">
                                                    <a href="edit_form.php?id=<?= htmlspecialchars($form['id']) ?>"
                                                        class="btn btn-sm" title="Edit">
                                                        <i class="fa-solid fa-pen-to-square" style="color: #007bff;"></i>
                                                    </a>
                                                    <a href="delete_form.php?id=<?= htmlspecialchars($form['id']) ?>"
                                                        class="btn btn-sm"
                                                        title="Delete"
                                                        onclick="return confirm('Are you sure you want to delete this form? This action cannot be undone.')">
                                                        <i class="fa-solid fa-trash" style="color: #dc3545;"></i>
                                                    </a>
                                                    <a href="publish_form.php?id=<?= htmlspecialchars($form['id']) ?>"
                                                        class="btn btn-sm"
                                                        title="Publish">
                                                        <i class="fa-solid fa-upload" style="color: #28a745;"></i>
                                                    </a>
                                                    <a href="preview_form.php?id=<?= htmlspecialchars($form['id']) ?>"
                                                        class="btn btn-sm"
                                                        title="Preview">
                                                        <i class="fa-solid fa-eye" style="color: #007bff;"></i>
                                                    </a>
                                                    <a href="form_responses.php?form_id=<?= htmlspecialchars($form['id']) ?>"
                                                        class="btn btn-sm"
                                                        title="View Responses">
                                                        <i class="fa-solid fa-list-check" style="color: #ff9800;"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($formList)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No forms found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if ($totalPages > 1): ?>
                                <div style="margin-top: 20px;">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination">
                                            <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=1" title="First page">&laquo;</a>
                                                </li>
                                            <?php endif; ?>

                                            <?php
                                            $start = max(1, min($page - 2, $totalPages - 4));
                                            $end = min($totalPages, max(5, $page + 2));

                                            for ($i = $start; $i <= $end; $i++):
                                            ?>
                                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                                </li>
                                            <?php endfor; ?>

                                            <?php if ($page < $totalPages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?= $totalPages ?>" title="Last page">&raquo;</a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success" role="alert">
                                <?= htmlspecialchars($_SESSION['success_message']) ?>
                                <?php unset($_SESSION['success_message']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['alert_message'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= htmlspecialchars($_SESSION['alert_message']) ?>
                                <?php unset($_SESSION['alert_message']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php require_once 'assets/inc/incFooter.php'; ?>
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