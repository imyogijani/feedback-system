<?php
session_start();

// Handle approve/unapprove via GET
if (isset($_GET['action']) && isset($_GET['id'])) {
    include 'config/config.php'; // DB connection
    $id = (int)$_GET['id'];
    $action = $_GET['action'] === 'approve' ? 1 : 0;

    $stmt = $conn->prepare("UPDATE demo_requests SET approved = ? WHERE id = ?");
    $stmt->execute([$action, $id]);

    $_SESSION['message'] = $action ? "Request approved." : "Approval removed.";
    header("Location: demo_request.php");
    exit;
}

include 'config/config.php'; // DB connection
include('assets/inc/incHeader.php');

// Fetch all demo requests
$stmt = $conn->query("SELECT * FROM demo_requests ORDER BY id DESC");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Demo Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
    /* Your existing CSS for .card, table, pagination */
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

    /* Styles for toasts/alerts if not already in external CSS */
    .alert {
        z-index: 2000 !important;
        position: fixed;
        bottom: 0;
        right: 0;
        margin: 1rem;
        width: auto;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include('assets/inc/incSidebar.php'); ?>
            <div class="layout-page">
                <?php include('assets/inc/incNavbar.php'); ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <h2 class="mb-4">Demo Requests</h2>

                        <?php if (!empty($_SESSION['message'])): ?>
                            <div class="alert alert-success">
                                <?= $_SESSION['message'] ?>
                                <?php unset($_SESSION['message']); ?>
                            </div>
                        <?php endif; ?>

                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Approved</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($requests) > 0): ?>
                                    <?php foreach ($requests as $request): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($request['id']) ?></td>
                                            <td><?= htmlspecialchars($request['email']) ?></td>
                                            <td><?= htmlspecialchars($request['mobile']) ?></td>
                                            <td>
                                                <?php if ($request['approved']): ?>
                                                    <span class="badge bg-success">Approved</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="get" style="display:inline;">
                                                    <input type="hidden" name="id" value="<?= $request['id'] ?>">
                                                    <input type="hidden" name="action" value="<?= $request['approved'] ? 'unapprove' : 'approve' ?>">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="toggle_approve" onchange="this.form.submit()" <?= $request['approved'] ? 'checked' : '' ?> >
                                                        <label class="form-check-label">
                                                            <?= $request['approved'] ? 'Approved' : 'Pending' ?>
                                                        </label>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No demo requests found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>


                    </div>
                    <?php include('assets/inc/incFooter.php'); ?>
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

    <script>
        // Automatically hide the toast after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => alert.remove());
        }, 5000);
    </script>
</body>

</html>