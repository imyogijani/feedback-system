<?php
// ini_set('session.gc_maxlifetime', 300);
// session_set_cookie_params(lifetime_or_options: 300);
session_start();

include('../admin/config/config.php');

// Set session on login
// if (!isset($_SESSION['login_time'])) {
//     $_SESSION['login_time'] = time();
// }

// Set session duration (in seconds)
// $session_duration = 300; // 24 hours = 86400 seconds

// Remaining time
// $time_left = ($_SESSION['login_time'] + $session_duration) - time();
// $email = $_SESSION['email'] ?? $_SESSION['demo_user'] ?? null;

// if ($time_left <= 0) {
//     // Set approved=0 for this user in demo_requests if email is set
//     if ($email) {
//         $stmt = $conn->prepare("UPDATE demo_requests SET approved = 0 WHERE email = ?");
//         $stmt->execute([$email]);
//     }
//     session_unset();
//     session_destroy();
//     header("Location: login.php?expired=1");
//     exit();
// }



include('assets/inc/incHeader.php');

// Only show forms for role_id 4
if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 4) {
    $user_id = $_SESSION['user_id'] ?? 0;
    $stmt = $conn->prepare("SELECT f.id, f.title, f.form_type, f.created_at, u.email AS created_by FROM forms f LEFT JOIN demo_requests u ON f.created_by = u.id WHERE f.created_by = ? ORDER BY f.created_at DESC");
    $stmt->execute([$user_id]);
    $forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $forms = [];
}
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
    <!-- <script>
        // PHP seconds passed to JS
        let timeLeft = <?= $time_left ?>;

        function formatTime(seconds) {
            const d = Math.floor(seconds / (3600 * 24));
            const h = Math.floor((seconds % (3600 * 24)) / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = Math.floor(seconds % 60);
            return `${d}d ${h}h ${m}m ${s}s`;
        }

        function countdown() {
            if (timeLeft <= 0) {
                // Call session_expire.php to update approved=0 and destroy session
                fetch('session_expire.php', {
                        method: 'POST'
                    })
                    .then(response => response.json())
                    .then(data => {
                        window.location.href = 'login.php?session_expired=1';
                    });
                return;
            }

            document.getElementById('sessionCountdown').textContent = formatTime(timeLeft);
            timeLeft--;
        }

        // Start countdown every second
        countdown(); // call immediately
        setInterval(countdown, 1000);
    </script> -->
</body>

</html>