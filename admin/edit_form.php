<?php
session_start();
// Allow users with role_id = 1 (admin), 2 (moderator), or 3 (user) to access this page
$isGoogleLogin = isset($_SESSION['auth_method']) && $_SESSION['auth_method'] === 'google';
$isTraditional = isset($_SESSION['role_id']) && in_array($_SESSION['role_id'], [1, 2, 3]);

if (!($isGoogleLogin || $isTraditional)) {
    header("Location: login.php");
    exit();
}
include('config/config.php');
include('assets/inc/incHeader.php');

$form_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch form
$stmt = $conn->prepare("SELECT * FROM forms WHERE id = :id");
$stmt->execute([':id' => $form_id]);
$form = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$form) die("Form not found");

// Fetch questions
$questionsStmt = $conn->prepare("SELECT * FROM questions WHERE form_id = :form_id ORDER BY id ASC");
$questionsStmt->execute([':form_id' => $form_id]);
$questions = $questionsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch options
$optionMap = [];
$questionIds = array_column($questions, 'id');
if (!empty($questionIds)) {
    $inClause = implode(',', array_fill(0, count($questionIds), '?'));
    $optStmt = $conn->prepare("SELECT * FROM options WHERE question_id IN ($inClause)");
    $optStmt->execute($questionIds);
    foreach ($optStmt->fetchAll(PDO::FETCH_ASSOC) as $opt) {
        $optionMap[$opt['question_id']][] = $opt['option_text'];
    }
}

// ✅ Fetch responses (grouped by question)
$resStmt = $conn->prepare("SELECT id, question_id, answer FROM responses WHERE form_id = ?");
$resStmt->execute([$form_id]);
$responses = $resStmt->fetchAll(PDO::FETCH_ASSOC);

// Group responses by question_id
$responseMap = [];
foreach ($responses as $res) {
    $responseMap[$res['question_id']][] = $res;
}

?>


<body>
    <!-- Layout wrapper -->
    <div class="">
        <div class="container">
            <!-- Menu -->

            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->

                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <!-- Your page content goes here -->
                        <h2 class="text-center">Edit Form: <?= htmlspecialchars($form['title']) ?></h2>

                        <form action="update_form.php" method="POST">
                            <input type="hidden" name="form_id" value="<?= $form_id ?>">
                            <div class="mb-3">
                                <label class="form-label">Form Title:</label>
                                <input type="text" class="form-control" name="form_title" value="<?= htmlspecialchars($form['title']) ?>" required>
                            </div>

                            <?php foreach ($questions as $index => $q): ?>
                                <div class="mb-4 border p-3 rounded bg-light">
                                    <input type="hidden" name="questions[<?= $index ?>][id]" value="<?= $q['id'] ?>">

                                    <div class="mb-3">
                                        <label class="form-label">Question:</label>
                                        <input type="text" class="form-control" name="questions[<?= $index ?>][text]" value="<?= htmlspecialchars($q['question_text']) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Type:</label>
                                        <select class="form-select" name="questions[<?= $index ?>][type]" onchange="toggleOptionField(this, <?= $index ?>)">
                                            <option value="text" <?= $q['question_type'] === 'text' ? 'selected' : '' ?>>Text</option>
                                            <option value="textarea" <?= $q['question_type'] === 'textarea' ? 'selected' : '' ?>>Textarea</option>
                                            <option value="radio" <?= $q['question_type'] === 'radio' ? 'selected' : '' ?>>Radio</option>
                                            <option value="checkbox" <?= $q['question_type'] === 'checkbox' ? 'selected' : '' ?>>Checkbox</option>
                                            <option value="dropdown" <?= $q['question_type'] === 'dropdown' ? 'selected' : '' ?>>Dropdown</option>
                                            <option value="date" <?= $q['question_type'] === 'date' ? 'selected' : '' ?>>Date Picker</option>
                                            <option value="rating_star" <?= $q['question_type'] === 'rating_star' ? 'selected' : '' ?>>Rating Star</option>
                                            <option value="rating_thumb" <?= $q['question_type'] === 'rating_thumb' ? 'selected' : '' ?>>Rating Thumb</option>
                                            <option value="rating_heart" <?= $q['question_type'] === 'rating_heart' ? 'selected' : '' ?>>Rating Heart</option>
                                        </select>
                                    </div>

                                    <div class="mb-3 option-group" id="options-<?= $index ?>" style="display: <?= in_array($q['question_type'], ['radio', 'checkbox']) ? 'block' : 'none' ?>;">
                                        <label class="form-label">Options (comma separated):</label>
                                        <input type="text" class="form-control" name="questions[<?= $index ?>][options]" value="<?= isset($optionMap[$q['id']]) ? htmlspecialchars(implode(',', $optionMap[$q['id']])) : '' ?>">
                                    </div>

                                    <!-- <?php if (isset($responseMap[$q['id']])): ?>
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Edit Answers:</strong></label>
                                            <?php foreach ($responseMap[$q['id']] as $i => $res): ?>
                                                <input type="hidden" name="responses[<?= $res['id'] ?>][id]" value="<?= $res['id'] ?>">
                                                <input
                                                    type="<?= in_array($q['question_type'], ['rating_bar', 'rating_star', 'rating_thumb', 'rating_heart']) ? 'number' : 'text' ?>"
                                                    <?= in_array($q['question_type'], ['rating_bar', 'rating_star', 'rating_thumb', 'rating_hearts']) ? 'min="1" max="5" step="1"' : '' ?>
                                                    class="form-control mb-2"
                                                    name="responses[<?= $res['id'] ?>][answer]"
                                                    value="<?= htmlspecialchars($res['answer']) ?>">
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?> -->
                                </div>
                            <?php endforeach; ?>

                            <!-- In your foreach for responses -->

                            <button type="submit" class="btn btn-primary mt-3">Save All Changes</button>

                        </form>


                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    <?php include('assets/inc/incFooter.php'); ?>
                    <!-- / Footer -->
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
    <script>
        function toggleOptionField(selectElem, index) {
            const optDiv = document.getElementById('options-' + index);
            if (selectElem.value === 'radio' || selectElem.value === 'checkbox' || selectElem.value === 'dropdown') {
                optDiv.style.display = 'block';
            } else {
                optDiv.style.display = 'none';
            }
        }
    </script>
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