<?php
session_start();
// Allow users with role_id = 1 (admin), 2 (moderator), or 3 (user) to access this page
$isGoogleLogin = isset($_SESSION['auth_method']) && $_SESSION['auth_method'] === 'google';
$isTraditional = isset($_SESSION['role_id']) && in_array($_SESSION['role_id'], [4]);

if (!($isGoogleLogin || $isTraditional)) {
    header("Location: login.php");
    exit();
}
include('config/config.php');
include('assets/inc/incHeader.php');

$form_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch form
$stmt = $conn->prepare("SELECT * FROM demo_forms_combined WHERE id = :id");
$stmt->execute([':id' => $form_id]);
$form = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$form) die("Form not found");


// Decode questions JSON from forms_combined (sectioned format)
$questions = [];
if (isset($form['questions_json']) && !empty($form['questions_json'])) {
    $questions = json_decode($form['questions_json'], true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($questions)) {
        $questions = [];
    }
}

// Debugging: Check the structure of $questions
// var_dump($questions);

// ✅ Fetch responses (grouped by question)
$resStmt = $conn->prepare("SELECT id, question_id, answer FROM demo_form_responses_combined WHERE form_id = ?");
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


                            <?php
                            // Section-wise display for decoded JSON structure
                            $qIndex = 0;
                            foreach ($questions as $section):
                                $sectionTitle = isset($section['section_title']) ? $section['section_title'] : '';
                                $sectionQuestions = isset($section['questions']) && is_array($section['questions']) ? $section['questions'] : [];
                            ?>
                                <div class="mb-4 border p-3 rounded bg-light">
                                    <div class="mb-2" style="font-weight:bold; color:#673ab7; font-size:17px;">
                                        <?= htmlspecialchars($sectionTitle) ?>
                                    </div>
                                    <?php foreach ($sectionQuestions as $q): ?>
                                        <input type="hidden" name="questions[<?= $qIndex ?>][id]" value="<?= isset($q['id']) ? $q['id'] : '' ?>">

                                        <div class="mb-3">
                                            <label class="form-label">Question:</label>
                                            <input type="text" class="form-control" name="questions[<?= $qIndex ?>][text]" value="<?= htmlspecialchars(isset($q['text']) ? $q['text'] : (isset($q['question']) ? $q['question'] : (isset($q['question_text']) ? $q['question_text'] : ''))) ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Type:</label>
                                            <select class="form-select" name="questions[<?= $qIndex ?>][type]" onchange="toggleOptionField(this, <?= $qIndex ?>)">
                                                <?php $qType = isset($q['type']) ? $q['type'] : (isset($q['question_type']) ? $q['question_type'] : 'text'); ?>
                                                <option value="text" <?= $qType === 'text' ? 'selected' : '' ?>>Text</option>
                                                <option value="textarea" <?= $qType === 'textarea' ? 'selected' : '' ?>>Textarea</option>
                                                <option value="radio" <?= $qType === 'radio' ? 'selected' : '' ?>>Radio</option>
                                                <option value="checkbox" <?= $qType === 'checkbox' ? 'selected' : '' ?>>Checkbox</option>
                                                <option value="dropdown" <?= $qType === 'dropdown' ? 'selected' : '' ?>>Dropdown</option>
                                                <option value="date" <?= $qType === 'date' ? 'selected' : '' ?>>Date Picker</option>
                                                <option value="rating_star" <?= $qType === 'rating_star' ? 'selected' : '' ?>>Rating Star</option>
                                                <option value="rating_thumb" <?= $qType === 'rating_thumb' ? 'selected' : '' ?>>Rating Thumb</option>
                                                <option value="rating_heart" <?= $qType === 'rating_heart' ? 'selected' : '' ?>>Rating Heart</option>
                                            </select>
                                        </div>

                                        <div class="mb-3 option-group" id="options-<?= $qIndex ?>" style="display: <?= in_array($qType, ['radio', 'checkbox', 'dropdown']) ? 'block' : 'none' ?>;">
                                            <label class="form-label">Options (comma separated):</label>
                                            <?php
                                            // Robustly flatten options for display (handles array, array of arrays, array of JSON, etc.)
                                            $optVal = '';
                                            if (isset($q['options'])) {
                                                if (is_array($q['options'])) {
                                                    $flatOpts = [];
                                                    foreach ($q['options'] as $opt) {
                                                        if (is_string($opt)) {
                                                            $decoded = json_decode($opt, true);
                                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                                foreach ($decoded as $dopt) {
                                                                    if (is_string($dopt)) $flatOpts[] = $dopt;
                                                                }
                                                            } else {
                                                                $flatOpts[] = $opt;
                                                            }
                                                        } elseif (is_array($opt)) {
                                                            foreach ($opt as $dopt) {
                                                                if (is_string($dopt)) $flatOpts[] = $dopt;
                                                            }
                                                        }
                                                    }
                                                    $optVal = htmlspecialchars(implode(',', $flatOpts));
                                                } else {
                                                    $optVal = htmlspecialchars($q['options']);
                                                }
                                            }
                                            ?>
                                            <input type="text" class="form-control" name="questions[<?= $qIndex ?>][options]" value="<?= $optVal ?>">
                                        </div>

                                        <!-- You can add response editing here if needed -->
                                        <?php $qIndex++; ?>
                                    <?php endforeach; ?>
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