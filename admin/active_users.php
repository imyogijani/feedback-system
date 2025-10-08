<?php
session_start();
// D:\xampp\htdocs\feedback-system\admin\active_users.php

header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache");
header("Expires: 0");

// Admin-only access
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit();
}

require_once 'config/config.php';
require_once 'assets/inc/incHeader.php';

// Pagination settings
$limit = 10;
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$offset = ($page - 1) * $limit;

try {
    // Count total active users
    $stmt = $conn->query("SELECT COUNT(*) AS total_active FROM users WHERE is_active = 1");
    $totalActiveUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_active'] ?? 0;
    $totalPages = max(1, ceil($totalActiveUsers / $limit));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $limit;

    // Fetch active users with role
    $stmt = $conn->prepare("
        SELECT u.id, u.username, u.email, u.mobile, r.role_name, u.created_at
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        WHERE u.is_active = 1
        ORDER BY u.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $activeUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("DB error: " . $e->getMessage());
    $_SESSION['alert_message'] = "Error fetching active users.";
    $activeUsers = [];
    $totalPages = 1;
    $totalActiveUsers = 0;
}
?>

<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php require_once 'assets/inc/incSidebar.php'; ?>
        <div class="layout-page">
            <?php require_once 'assets/inc/incNavbar.php'; ?>
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <h1 class="mb-3">Active Users (<?= htmlspecialchars($totalActiveUsers) ?>)</h1>

                    <div class="card p-3">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Role</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($activeUsers)): ?>
                                        <?php $serial = $offset + 1; ?>
                                        <?php foreach ($activeUsers as $user): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($serial++) ?></td>
                                                <td><?= htmlspecialchars($user['username']) ?></td>
                                                <td><?= htmlspecialchars($user['email']) ?></td>
                                                <td><?= htmlspecialchars($user['mobile']) ?></td>
                                                <td><?= htmlspecialchars($user['role_name'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($user['created_at']))) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No active users found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mt-3">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=1">&laquo; First</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php
                                    $start = max(1, $page - 2);
                                    $end = min($totalPages, $page + 2);
                                    for ($i = $start; $i <= $end; $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $totalPages ?>">Last &raquo;</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>

                    <?php if (isset($_SESSION['alert_message'])): ?>
                        <div class="alert alert-danger mt-3">
                            <?= htmlspecialchars($_SESSION['alert_message']) ?>
                            <?php unset($_SESSION['alert_message']); ?>
                        </div>
                    <?php endif; ?>

                </div>
                <?php require_once 'assets/inc/incFooter.php'; ?>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>

<script src="../assets/vendor/libs/jquery/jquery.js"></script>
<script src="../assets/vendor/libs/popper/popper.js"></script>
<script src="../assets/vendor/js/bootstrap.js"></script>
<script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="../assets/vendor/js/menu.js"></script>
<script src="../assets/vendor/libs/apex-charts/apexcharts.js"></script>
<script src="../assets/js/main.js"></script>
<script src="../assets/js/dashboards-analytics.js"></script>
<script async defer src="https://buttons.github.io/buttons.js"></script>
</body>
</html>
