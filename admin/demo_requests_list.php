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

$statusFilter = isset($_GET['approved']) ? intval($_GET['approved']) : null;
$statusCondition = '';
$params = [];

if ($statusFilter !== null) {
    $statusCondition = 'WHERE approved = :approved';
    $params[':approved'] = $statusFilter;
}

// Fetch demo requests
try {
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, mobile, business_name, approved, created_at FROM demo_requests $statusCondition ORDER BY created_at DESC");
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $demoRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching demo requests: " . $e->getMessage());
    $demoRequests = [];
}
?>

<style>
    .badge-pending { background-color: #ffc107; color: black; }
    .badge-approved { background-color: #28a745; color: white; }
    .badge-rejected { background-color: #dc3545; color: white; }
    .card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
</style>

<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php require_once 'assets/inc/incSidebar.php'; ?>
        <div class="layout-page">
            <?php require_once 'assets/inc/incNavbar.php'; ?>
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <h2 class="mb-4">Demo Requests <?= $statusFilter !== null ? ' - ' . ($statusFilter == 0 ? 'Pending' : ($statusFilter == 1 ? 'Approved' : 'Rejected')) : '' ?></h2>

                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Business Name</th>
                                        <th>approved</th>
                                        <th>Requested At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($demoRequests)): ?>
                                        <?php foreach ($demoRequests as $request): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($request['id']) ?></td>
                                                <td><?= htmlspecialchars($request['first_name'] . ' ' . $request['last_name']) ?></td>
                                                <td><?= htmlspecialchars($request['email']) ?></td>
                                                <td><?= htmlspecialchars($request['mobile']) ?></td>
                                                <td><?= htmlspecialchars($request['business_name']) ?></td>
                                                <td>
                                                    <?php
                                                        if ($request['approved'] == 0) {
                                                            echo '<span class="badge badge-pending">Pending</span>';
                                                        } elseif ($request['approved'] == 1) {
                                                            echo '<span class="badge badge-approved">Approved</span>';
                                                        } 
                                                    ?>
                                                </td>
                                                <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($request['created_at']))) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No demo requests found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

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
