<?php
session_start();
// Allow admin, moderator, and user to access analytics
$isGoogleLogin = isset($_SESSION['auth_method']) && $_SESSION['auth_method'] === 'google';
$isTraditional = isset($_SESSION['role_id']) && in_array($_SESSION['role_id'], [1, 2, 3]);

if (!($isGoogleLogin || $isTraditional)) {
    header("Location: login.php");
    exit();
}

include('config/config.php');

// Fetch forms: admin sees all, moderator sees their own and their created users' forms, others see only their own
if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1) {
    // Admin: show all, include firebase_uid for Google login detection
    $forms = $conn->query("SELECT f.id, f.title, u.firebase_uid FROM forms f LEFT JOIN users u ON f.created_by = u.id")->fetchAll(PDO::FETCH_ASSOC);
} else {
    // For moderator, user, and Google login: show only forms created by this user
    $user_id = $_SESSION['user_id'] ?? 0;
    $stmt = $conn->prepare("SELECT f.id, f.title, u.firebase_uid FROM forms f LEFT JOIN users u ON f.created_by = u.id WHERE f.created_by = ?");
    $stmt->execute([$user_id]);
    $forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// echo "<pre>";
// var_dump($forms); // Debugging line to check fetched forms
// exit;

//     else {
//     $forms = [];
// }    

// Get selected form ID
$formId = $_GET['id'] ?? 0;

$positive = 0;
$negative = 0;

// Fetch and analyze responses only if form is selected
if ($formId) {
    $stmt = $conn->prepare("SELECT id FROM questions WHERE form_id = ? AND question_type = 'radio'");
    $stmt->execute([$formId]);
    $radioQuestions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($radioQuestions as $qid) {
        $stmt = $conn->prepare("SELECT answer FROM responses WHERE question_id = ?");
        $stmt->execute([$qid]);
        $responses = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($responses as $r) {
            $r = strtolower(trim($r));
            if ($r === 'yes') {
                $positive++;
            } elseif ($r === 'no') {
                $negative++;
            }
        }
    }
}

include('assets/inc/incHeader.php');
?>

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

<title>Response Analytics</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include('assets/inc/incSidebar.php'); ?>
            <div class="layout-page">
                <?php include('assets/inc/incNavbar.php'); ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h3>Radio Button Response Analytics</h3>

                        <!-- Dropdown Form -->
                        <form method="get" class="mb-4">
                            <label for="formId" class="form-label">Select a Form:</label>
                            <select name="id" id="formId" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Select Form --</option>
                                <?php foreach ($forms as $form): ?>
                                    <option value="<?= $form['id'] ?>" <?= ($formId == $form['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($form['title']) ?><?php if (!empty($form['firebase_uid'])): ?> - (Google login)<?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>

                        <!-- Chart Section -->
                        <?php if ($formId): ?>
                            <h4>Selected Form ID: <?= htmlspecialchars($formId) ?></h4>
                            <div class="col-end-6">
                                <canvas id="responseChart" width="400" height="400"></canvas>
                            </div>
                            <style>
                                #responseChart {
                                    width: 400px !important;
                                    height: 400px !important;
                                }
                            </style>
                            <script>
                                const ctx = document.getElementById('responseChart').getContext('2d');
                                const responseChart = new Chart(ctx, {
                                    type: 'pie',
                                    data: {
                                        labels: ['Positive (Yes)', 'Negative (No)'],
                                        datasets: [{
                                            label: 'Responses',
                                            data: [<?= $positive ?>, <?= $negative ?>],
                                            backgroundColor: ['#28a745', '#dc3545'],
                                        }]
                                    },
                                    options: {
                                        responsive: false,
                                        plugins: {
                                            legend: {
                                                position: 'bottom'
                                            },
                                            title: {
                                                display: true,
                                                text: 'Positive vs. Negative (Radio Button Responses)'
                                            }
                                        }
                                    }
                                });
                            </script>
                        <?php endif; ?>

                        <!-- Success Message -->
                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success position-fixed bottom-0 end-0 m-3" role="alert" style="z-index: 2000; width: auto;">
                                <?= htmlspecialchars($_SESSION['success_message']) ?>
                                <?php unset($_SESSION['success_message']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Footer -->
                    <?php include('assets/inc/incFooter.php'); ?>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <!-- Core JS -->
    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../assets/vendor/js/menu.js"></script>
    <script src="../assets/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/dashboards-analytics.js"></script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>

    <!-- Toast auto-hide -->
    <script>
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => alert.remove());
        }, 5000);
    </script>

</body>

</html>