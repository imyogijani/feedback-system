<?php
session_start();

// Initialize $user_id from session, assuming it's set upon successful login
$user_id = $_SESSION['user_id'] ?? 0;

// D:\xampp\htdocs\feedback-system\admin\dashboard.php

header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache");
header("Expires: 0");

// This check now relies on google-login.php setting $_SESSION['role_id'] correctly
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit();
}

require_once 'config/config.php';

// Fetch current data
 $stmt = $conn->prepare("SELECT first_name,last_name,username, email, profile_image FROM users WHERE id = ?");
 $stmt->execute([$user_id]);
 $user = $stmt->fetch(PDO::FETCH_ASSOC);

// Pagination settings
$limit = 10; // Forms per page
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$offset = ($page - 1) * $limit;



try {
    // Count total forms
    $stmt = $conn->query("SELECT COUNT(*) AS total FROM forms_combined");
    $totalForms = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = max(1, ceil($totalForms / $limit));

    // Ensure page number doesn't exceed total pages
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $limit;

    // Get paginated forms with creator info
    $stmt = $conn->prepare("
        SELECT f.id, f.title, f.created_at, u.username AS created_by, u.firebase_uid 
        FROM forms_combined f 
        LEFT JOIN users u ON f.created_by = u.id 
        ORDER BY f.created_at DESC 
        LIMIT :limit OFFSET :offset
    ");
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
require_once 'assets/inc/incHeader.php';
?>

<style>
    .card {
        background: #f7f7f7;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
        table-layout: fixed;
    }

    th, td {
        padding: 12px;
        border: 1px solid #ccc;
        text-align: left;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    th {
        background-color: #f0f0f0;
    }

    .pagination {
        display: flex;
        list-style: none;
        padding: 0;
        justify-content: center;
        flex-wrap: wrap;
        gap: 5px;
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
        transition: all 0.3s ease;
    }

    .pagination .active .page-link {
        background: #007bff;
        color: white;
    }

    .pagination .page-link:hover {
        background: #ddd;
    }

    .alert {
        z-index: 2000;
        position: fixed;
        bottom: 20px;
        right: 20px;
        margin: 0;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        max-width: 90%;
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .action-links {
        display: flex;
        gap: 10px;
        justify-content: flex-start;
        align-items: center;
    }

    .action-links a {
        padding: 5px;
        transition: all 0.2s ease;
    }

    .action-links a:hover {
        transform: scale(1.1);
    }

    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
        }
        
        .action-links {
            flex-wrap: wrap;
        }
    }
    .dashboard-card {
        border-radius: 14px;
        padding: 20px;
        color: #1f2937;
        text-align: center;
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s, box-shadow 0.2s;
        min-height: 200px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
    }

    .bg-palette-light {
        background: #f1e3dd;
        color: #4b5563;
    }

    .bg-palette-soft-blue {
        background: #748DAE;
        color: #f9fafb;
    }

    .bg-palette-medium-blue {
        background: #748DAE;
        color: #f9fafb;
    }

    .bg-palette-deep-blue {
        background: #8174A0;
        color: #f9fafb;
    }

    .dashboard-card h2 {
        font-size: 2.2rem;
        font-weight: 700;
        margin: 10px 0;
    }

    .dashboard-card h5 {
        font-size: 1.1rem;
        font-weight: 600;
    }

    .dashboard-card h6 {
        font-size: 0.9rem;
        font-weight: 500;
        margin-top: 5px;
    }

    .dashboard-card a.btn {
        font-weight: 600;
        padding: 8px 14px;
    }

    .btn-light {
        background-color: #e5e7eb;
        color: #1f2937;
    }

    .btn-light:hover {
        background-color: #d1d5db;
        color: #111827;
    }

    .btn-dark {
        background-color: #374151;
        color: #f9fafb;
    }

    .btn-dark:hover {
        background-color: #1f2937;
        color: #f9fafb;
    }

    @media (max-width: 576px) {
        .dashboard-card h2 {
            font-size: 1.6rem;
        }
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php require_once 'assets/inc/incSidebar.php'; ?>
            <div class="layout-page">
                <?php require_once 'assets/inc/incNavbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h1 class="mb-3">Admin Dashboard</h1>
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
                                                <td><?= htmlspecialchars($serial++) ?></td>
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
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <!-- Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content shadow-lg rounded-4">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="profileModalLabel">My Profile</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row">
          <div class="col-md-4 text-center mb-3">
            <img src="<?= htmlspecialchars($profileImage ?? 'assets/images/default_profile.png') ?>" alt="Profile" class="img-thumbnail rounded-circle shadow" style="width:150px;height:150px;">
          </div>
          <div class="col-md-8">
            <table class="table table-borderless">
              <tr>
                <th>Name:</th>
                <td><?= htmlspecialchars($profileData['username'] ?? 'N/A') ?></td>
              </tr>
              <tr>
                <th>Email:</th>
                <td><?= htmlspecialchars($profileData['email'] ?? 'N/A') ?></td>
              </tr>
              <tr>
                <th>Mobile:</th>
                <td><?= htmlspecialchars($profileData['mobile'] ?? 'N/A') ?></td>
              </tr>
              <tr>
                <th>Role:</th>
                <td><?= htmlspecialchars($profileData['role_name'] ?? 'User') ?></td>
              </tr>
              <tr>
                <th>Business:</th>
                <td><?= htmlspecialchars($profileData['business_name'] ?? 'N/A') ?></td>
              </tr>
              <tr>
                <th>Joined:</th>
                <td><?= htmlspecialchars(date('d-m-Y', strtotime($profileData['created_at'] ?? date('Y-m-d')))) ?></td>
              </tr>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer">
       <a href="edit_profile.php" class="btn btn-primary">
    <i class="fa-solid fa-user-edit"></i> Edit Profile
</a>

</button>

        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Close
        </button>
      </div>
    </div>
  </div>
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'all 0.5s ease-out';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(20px)';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>