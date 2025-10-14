<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit();
}

require_once 'config/config.php';
require_once 'assets/inc/incHeader.php';

$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Count total users (with JOIN for roles)
$countQuery = "SELECT COUNT(*) FROM users u LEFT JOIN roles r ON u.role_id = r.id";
if ($search) {
    $countQuery .= " WHERE u.username LIKE :search OR u.email LIKE :search OR r.role_name LIKE :search";
}
$stmt = $conn->prepare($countQuery);
if ($search) {
    $stmt->execute([':search' => "%$search%"]);
} else {
    $stmt->execute();
}
$totalUsers = $stmt->fetchColumn();
$totalPages = max(1, ceil($totalUsers / $limit));

// Fetch users with JOIN and search
$query = "SELECT u.id, u.username, u.email, u.mobile, u.role_id, r.role_name, u.is_active, u.profile_image, u.created_at FROM users u LEFT JOIN roles r ON u.role_id = r.id";
if ($search) {
    $query .= " WHERE u.username LIKE :search OR u.email LIKE :search OR r.role_name LIKE :search";
}
$query .= " ORDER BY u.created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($query);
if ($search) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php require_once 'assets/inc/incSidebar.php'; ?>
        <div class="layout-page">
            <?php require_once 'assets/inc/incNavbar.php'; ?>
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <form class="mb-3" method="GET">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search by name, email, or role..." value="<?= htmlspecialchars($search) ?>">
                            <button class="btn btn-primary" type="submit">Search</button>
                            <?php if ($search): ?>
                                <a href="all_users_list.php" class="btn btn-secondary">Reset</a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Profile</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($users): ?>
                                        <?php foreach ($users as $index => $user): ?>
                                            <tr>
                                                <td><?= $offset + $index + 1 ?></td>
                                                <td><img src="<?= $user['profile_image'] ? 'assets/images/' . htmlspecialchars($user['profile_image'] ?? '') : 'assets/img/default-avatar.png' ?>" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;"></td>
                                                <td><?= htmlspecialchars($user['username'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($user['mobile'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($user['role_name'] ?? 'N/A') ?></td>
                                                <td><?= $user['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; ?></td>
                                                <td><?= date('Y-m-d H:i', strtotime($user['created_at'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="8" class="text-center">No users found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-3">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">&laquo;</a>
                                </li>
                                <?php
                                $adjacents = 2;
                                $start = max(1, $page - $adjacents);
                                $end = min($totalPages, $page + $adjacents);

                                if ($start > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?page=1&search=' . urlencode($search) . '">1</a></li>';
                                    if ($start > 2) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                }

                                for ($i = $start; $i <= $end; $i++) {
                                    echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="?page=' . $i . '&search=' . urlencode($search) . '">' . $i . '</a></li>';
                                }

                                if ($end < $totalPages) {
                                    if ($end < $totalPages - 1) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '&search=' . urlencode($search) . '">' . $totalPages . '</a></li>';
                                }
                                ?>
                                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">&raquo;</a>
                                </li>
                            </ul>
                        </nav>
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
