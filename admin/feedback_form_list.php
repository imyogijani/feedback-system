<?php
session_start();
include('config/config.php');
include('assets/inc/incHeader.php');

// Fetch feedback forms: admin sees all, moderator sees only their own and their users', others see only their own
if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1) {
    // Admin: show all
    $stmt = $conn->prepare("SELECT f.id, f.title, f.form_type, f.created_at, u.username AS created_by FROM forms f LEFT JOIN users u ON f.created_by = u.id ORDER BY f.created_at DESC");
    $stmt->execute();
} elseif (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 2) {
    // Moderator: show forms created by themselves and by users they created
    $moderator_id = $_SESSION['user_id'] ?? 0;
    // Get user ids created by this moderator
    $userStmt = $conn->prepare("SELECT id FROM users WHERE created_by = ?");
    $userStmt->execute([$moderator_id]);
    $userIds = $userStmt->fetchAll(PDO::FETCH_COLUMN);
    $allIds = array_merge([$moderator_id], $userIds);
    $inClause = implode(',', array_fill(0, count($allIds), '?'));
    $sql = "SELECT f.id, f.title, f.form_type, f.created_at, u.username AS created_by FROM forms f LEFT JOIN users u ON f.created_by = u.id WHERE f.created_by IN ($inClause) ORDER BY f.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($allIds);
} else {
    // Non-admin/moderator: show only forms created by this user
    $user_id = $_SESSION['user_id'] ?? 0;
    $stmt = $conn->prepare("SELECT f.id, f.title, f.form_type, f.created_at, u.username AS created_by FROM forms f LEFT JOIN users u ON f.created_by = u.id WHERE f.created_by = ? ORDER BY f.created_at DESC");
    $stmt->execute([$user_id]);
}
$forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include('assets/inc/incSidebar.php'); ?>

            <div class="layout-page">
                <?php include('assets/inc/incNavbar.php'); ?>

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h3>Feedback Forms List</h3>

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($forms) > 0): ?>
                                    <?php foreach ($forms as $index => $form): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($form['title']) ?></td>
                                            <td><?= htmlspecialchars($form['form_type']) ?></td>
                                            <td><?= htmlspecialchars($form['created_by'] ?? 'System') ?></td>
                                            <td><?= htmlspecialchars($form['created_at']) ?></td>
                                            <td>
                                                <a href="crud/view_form.php?id=<?= $form['id'] ?>" class="btn btn-primary btn-sm">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No forms found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
</body>

</html>