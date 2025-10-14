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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">All Users Management</h2>
                        <span class="badge bg-primary"><?= $totalUsers ?> Total Users</span>
                    </div>

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
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($users): ?>
                                        <?php foreach ($users as $index => $user): ?>
                                            <tr>
                                                <td><?= $offset + $index + 1 ?></td>
                                                <td><img src="<?= $user['profile_image'] ? 'assets/images/' . htmlspecialchars($user['profile_image'] ?? '') : 'assets/img/default-avatar.png' ?>" class="rounded-circle profile-img" alt="Profile"></td>
                                                <td><?= htmlspecialchars($user['username'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($user['mobile'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($user['role_name'] ?? 'N/A') ?></td>
                                                <td><?= $user['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; ?></td>
                                                <td><?= date('Y-m-d H:i', strtotime($user['created_at'])) ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <!-- Edit Button -->
                                                        <a href="edit_manage_user.php?id=<?= $user['id'] ?>"
                                                           class="btn btn-sm btn-outline-primary"
                                                           title="Edit User">
                                                            <i class="fas fa-edit"></i>
                                                        </a>

                                                        <!-- Activate/Deactivate Button -->
                                                        <?php if ($user['is_active']): ?>
                                                            <button onclick="toggleUserStatus(<?= $user['id'] ?>, 0)"
                                                                    class="btn btn-sm btn-outline-warning"
                                                                    title="Deactivate User">
                                                                <i class="fas fa-user-slash"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <button onclick="toggleUserStatus(<?= $user['id'] ?>, 1)"
                                                                    class="btn btn-sm btn-outline-success"
                                                                    title="Activate User">
                                                                <i class="fas fa-user-check"></i>
                                                            </button>
                                                        <?php endif; ?>

                                                        <!-- Delete Button (only if not current user) -->
                                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                            <button onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>')"
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    title="Delete User">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="9" class="text-center">No users found.</td></tr>
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

<!-- Custom CSS for Action Buttons -->
<style>
    .btn-group .btn {
        margin: 0 2px;
        border-radius: 4px;
    }

    .table th:last-child,
    .table td:last-child {
        width: 160px;
        text-align: center;
        white-space: nowrap;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }

    .badge {
        font-size: 0.75rem;
    }

    .profile-img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border: 2px solid #e3e6f0;
    }

    .btn-outline-primary:hover {
        transform: translateY(-1px);
    }

    .btn-outline-warning:hover {
        transform: translateY(-1px);
    }

    .btn-outline-danger:hover {
        transform: translateY(-1px);
    }

    .btn-outline-success:hover {
        transform: translateY(-1px);
    }

    @media (max-width: 768px) {
        .btn-group {
            flex-direction: column;
            gap: 5px;
        }

        .btn-group .btn {
            margin: 2px 0;
        }

        .table th:last-child,
        .table td:last-child {
            width: auto;
            min-width: 120px;
        }
    }
</style>

<!-- Toast Container for Messages -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="actionToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>

<script src="../assets/vendor/libs/jquery/jquery.js"></script>
<script src="../assets/vendor/libs/popper/popper.js"></script>
<script src="../assets/vendor/js/bootstrap.js"></script>
<script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="../assets/vendor/js/menu.js"></script>
<script src="../assets/vendor/libs/apex-charts/apexcharts.js"></script>
<script src="../assets/js/main.js"></script>

<!-- User Management JavaScript -->
<script>
function showToast(message, type = 'success') {
    const toast = document.getElementById('actionToast');
    const toastBody = toast.querySelector('.toast-body');
    const toastHeader = toast.querySelector('.toast-header');

    // Set message
    toastBody.textContent = message;

    // Set color based on type
    toast.className = 'toast';
    if (type === 'success') {
        toast.classList.add('bg-success', 'text-white');
    } else if (type === 'error') {
        toast.classList.add('bg-danger', 'text-white');
    } else if (type === 'warning') {
        toast.classList.add('bg-warning', 'text-dark');
    }

    // Show toast
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
}

function toggleUserStatus(userId, newStatus) {
    const action = newStatus ? 'activate' : 'deactivate';
    const message = `Are you sure you want to ${action} this user?`;

    if (confirm(message)) {
        fetch('ajax/toggle_user_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId,
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                // Reload page after short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast(data.message || 'An error occurred', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Network error occurred', 'error');
        });
    }
}

function deleteUser(userId, username) {
    const message = `Are you sure you want to delete user "${username}"? This action cannot be undone.`;

    if (confirm(message)) {
        fetch('ajax/delete_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                // Reload page after short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast(data.message || 'An error occurred', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Network error occurred', 'error');
        });
    }
}
</script>
