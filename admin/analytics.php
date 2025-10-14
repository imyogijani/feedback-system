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
    $forms = $conn->query("SELECT f.id, f.title, f.questions_json, f.created_for, u.firebase_uid FROM forms_combined f LEFT JOIN users u ON f.created_by = u.id")->fetchAll(PDO::FETCH_ASSOC);
} else {
    // For moderator, user, and Google login: show only forms created by this user
    $user_id = $_SESSION['user_id'] ?? 0;
    $stmt = $conn->prepare("SELECT f.id, f.title, f.questions_json, f.created_for, u.firebase_uid FROM forms_combined f LEFT JOIN users u ON f.created_by = u.id WHERE f.created_by = ? OR f.created_for = ?");
    $stmt->execute([$user_id, $user_id]);
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

$analytics = [];
$totalResponses = 0;

// Fetch and analyze responses only if form is selected
if ($formId) {
    // Fetch the selected form's details
    $stmt = $conn->prepare("SELECT title, questions_json FROM forms_combined WHERE id = ?");
    $stmt->execute([$formId]);
    $selectedForm = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($selectedForm && !empty($selectedForm['questions_json'])) {
        $formQuestions = json_decode($selectedForm['questions_json'], true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($formQuestions)) {
            $formQuestions = [];
        }

        // Initialize analytics structure for all question types
        $questionAnalytics = [];
        foreach ($formQuestions as $section) {
            foreach (($section['questions'] ?? []) as $q) {
                $questionType = strtolower($q['type'] ?? 'text');
                $questionText = $q['text'] ?? '';

                $questionAnalytics[$questionText] = [
                    'type' => $questionType,
                    'options' => $q['options'] ?? [],
                    'responses' => [],
                    'analytics' => []
                ];
            }
        }

        // Fetch all responses for the selected form
        $stmt = $conn->prepare("SELECT responses_json FROM form_responses_combined WHERE form_id = ?");
        $stmt->execute([$formId]);
        $allResponses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalResponses = count($allResponses);

        // Process each response
        foreach ($allResponses as $responseEntry) {
            $responsesJson = json_decode($responseEntry['responses_json'], true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($responsesJson)) {
                continue;
            }

            foreach ($responsesJson as $sectionResponse) {
                foreach (($sectionResponse['answers'] ?? []) as $answerEntry) {
                    $questionText = $answerEntry['question_text'] ?? '';
                    $answer = $answerEntry['answer'] ?? '';

                    if (isset($questionAnalytics[$questionText])) {
                        $questionAnalytics[$questionText]['responses'][] = $answer;
                    }
                }
            }
        }

        // Generate analytics for each question type
        foreach ($questionAnalytics as $questionText => &$data) {
            $responses = $data['responses'];
            $type = $data['type'];

            switch ($type) {
                case 'radio':
                case 'dropdown':
                case 'checkbox':
                    // Count occurrences for each option
                    $counts = array_count_values(array_map('trim', $responses));
                    $data['analytics'] = [
                        'type' => 'categorical',
                        'counts' => $counts,
                        'total' => count($responses)
                    ];
                    break;

                case 'text':
                case 'textarea':
                    // Text analytics - word count, length analysis
                    $wordCounts = [];
                    $lengths = [];
                    $commonWords = [];

                    foreach ($responses as $response) {
                        $response = trim($response);
                        if (!empty($response)) {
                            $lengths[] = strlen($response);
                            $words = str_word_count(strtolower($response), 1);
                            $wordCounts[] = count($words);

                            // Count common words (excluding short words)
                            foreach ($words as $word) {
                                if (strlen($word) > 3) {
                                    $commonWords[$word] = ($commonWords[$word] ?? 0) + 1;
                                }
                            }
                        }
                    }

                    arsort($commonWords);
                    $topWords = array_slice($commonWords, 0, 10, true);

                    $data['analytics'] = [
                        'type' => 'text',
                        'total_responses' => count($responses),
                        'avg_length' => !empty($lengths) ? round(array_sum($lengths) / count($lengths), 2) : 0,
                        'avg_word_count' => !empty($wordCounts) ? round(array_sum($wordCounts) / count($wordCounts), 2) : 0,
                        'top_words' => $topWords,
                        'response_count' => count(array_filter($responses, function($r) { return !empty(trim($r)); }))
                    ];
                    break;

                case 'date':
                    // Date analytics - date ranges, most common dates
                    $dates = array_filter(array_map('trim', $responses));
                    $dateCounts = array_count_values($dates);
                    arsort($dateCounts);

                    $data['analytics'] = [
                        'type' => 'date',
                        'total_responses' => count($dates),
                        'unique_dates' => count(array_unique($dates)),
                        'most_common_dates' => array_slice($dateCounts, 0, 5, true)
                    ];
                    break;

                case 'number':
                    // Number analytics - min, max, average
                    $numbers = array_filter(array_map(function($r) {
                        return is_numeric(trim($r)) ? (float)trim($r) : null;
                    }, $responses));

                    if (!empty($numbers)) {
                        $data['analytics'] = [
                            'type' => 'number',
                            'total_responses' => count($numbers),
                            'min' => min($numbers),
                            'max' => max($numbers),
                            'average' => round(array_sum($numbers) / count($numbers), 2),
                            'median' => $numbers[array_keys($numbers)[count($numbers) / 2]] ?? 0
                        ];
                    }
                    break;

                default:
                    // Generic analytics for other types
                    $data['analytics'] = [
                        'type' => 'generic',
                        'total_responses' => count($responses),
                        'response_count' => count(array_filter($responses, function($r) { return !empty(trim($r)); }))
                    ];
            }
        }

        $analytics = $questionAnalytics;
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

    .analytics-card {
        border: 1px solid #e3e6f0;
        border-radius: 10px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        transition: all 0.3s;
    }

    .analytics-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.25rem 2rem 0 rgba(58, 59, 69, 0.2);
    }

    .text-analytics p, .date-analytics p, .number-analytics p, .generic-analytics p {
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .badge {
        font-size: 0.75rem;
    }

    .card-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 10px;
        color: #5a5c69;
    }

    canvas {
        max-height: 200px;
    }

    .row .col-md-6 {
        margin-bottom: 1rem;
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
                        <h3>Comprehensive Form Analytics</h3>

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

                        <!-- Analytics Section -->
                        <?php if ($formId): ?>
                            <div class="card">
                                <h4>Analytics for: <?= htmlspecialchars($selectedForm['title']) ?></h4>
                                <p><strong>Total Responses:</strong> <?= $totalResponses ?></p>

                                <?php if (!empty($analytics)): ?>
                                    <div class="row">
                                        <?php foreach ($analytics as $questionText => $data): ?>
                                            <?php if (!empty($data['analytics'])): ?>
                                                <div class="col-md-6 col-lg-4 mb-4">
                                                    <div class="card h-100 analytics-card">
                                                        <div class="card-body">
                                                            <h6 class="card-title"><?= htmlspecialchars($questionText) ?></h6>
                                                            <small class="text-muted">Type: <?= ucfirst($data['type']) ?></small>

                                                            <?php $analyticsData = $data['analytics']; ?>

                                                            <?php if ($analyticsData['type'] === 'categorical'): ?>
                                                                <!-- Radio, Dropdown, Checkbox Analytics -->
                                                                <canvas id="chart-<?= md5($questionText) ?>" width="300" height="200"></canvas>
                                                                <div class="mt-2">
                                                                    <?php foreach ($analyticsData['counts'] as $option => $count): ?>
                                                                        <small class="d-block">
                                                                            <?= htmlspecialchars($option) ?>: <?= $count ?>
                                                                            (<?= round(($count / max($analyticsData['total'], 1)) * 100, 1) ?>%)
                                                                        </small>
                                                                    <?php endforeach; ?>
                                                                </div>

                                                            <?php elseif ($analyticsData['type'] === 'text'): ?>
                                                                <!-- Text Analytics -->
                                                                <div class="text-analytics">
                                                                    <p><strong>Responses:</strong> <?= $analyticsData['response_count'] ?></p>
                                                                    <p><strong>Avg Length:</strong> <?= $analyticsData['avg_length'] ?> chars</p>
                                                                    <p><strong>Avg Words:</strong> <?= $analyticsData['avg_word_count'] ?> words</p>

                                                                    <?php if (!empty($analyticsData['top_words'])): ?>
                                                                        <div class="mt-2">
                                                                            <small><strong>Top Words:</strong></small>
                                                                            <?php foreach (array_slice($analyticsData['top_words'], 0, 5) as $word => $count): ?>
                                                                                <span class="badge bg-primary me-1"><?= htmlspecialchars($word) ?> (<?= $count ?>)</span>
                                                                            <?php endforeach; ?>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>

                                                            <?php elseif ($analyticsData['type'] === 'date'): ?>
                                                                <!-- Date Analytics -->
                                                                <div class="date-analytics">
                                                                    <p><strong>Total Dates:</strong> <?= $analyticsData['total_responses'] ?></p>
                                                                    <p><strong>Unique Dates:</strong> <?= $analyticsData['unique_dates'] ?></p>

                                                                    <?php if (!empty($analyticsData['most_common_dates'])): ?>
                                                                        <div class="mt-2">
                                                                            <small><strong>Most Common:</strong></small>
                                                                            <?php foreach (array_slice($analyticsData['most_common_dates'], 0, 3) as $date => $count): ?>
                                                                                <small class="d-block"><?= htmlspecialchars($date) ?>: <?= $count ?> times</small>
                                                                            <?php endforeach; ?>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>

                                                            <?php elseif ($analyticsData['type'] === 'number'): ?>
                                                                <!-- Number Analytics -->
                                                                <div class="number-analytics">
                                                                    <p><strong>Responses:</strong> <?= $analyticsData['total_responses'] ?></p>
                                                                    <p><strong>Min:</strong> <?= $analyticsData['min'] ?></p>
                                                                    <p><strong>Max:</strong> <?= $analyticsData['max'] ?></p>
                                                                    <p><strong>Average:</strong> <?= $analyticsData['average'] ?></p>
                                                                </div>

                                                            <?php else: ?>
                                                                <!-- Generic Analytics -->
                                                                <div class="generic-analytics">
                                                                    <p><strong>Total Responses:</strong> <?= $analyticsData['total_responses'] ?></p>
                                                                    <p><strong>Non-empty Responses:</strong> <?= $analyticsData['response_count'] ?></p>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- JavaScript for Charts -->
                                    <script>
                                        <?php foreach ($analytics as $questionText => $data): ?>
                                            <?php if ($data['analytics']['type'] === 'categorical'): ?>
                                                <?php
                                                $chartId = 'chart-' . md5($questionText);
                                                $labels = array_keys($data['analytics']['counts']);
                                                $values = array_values($data['analytics']['counts']);
                                                $colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];
                                                ?>
                                                (function() {
                                                    const ctx = document.getElementById('<?= $chartId ?>');
                                                    if (ctx) {
                                                        new Chart(ctx.getContext('2d'), {
                                                            type: 'doughnut',
                                                            data: {
                                                                labels: <?= json_encode($labels) ?>,
                                                                datasets: [{
                                                                    data: <?= json_encode($values) ?>,
                                                                    backgroundColor: <?= json_encode(array_slice($colors, 0, count($labels))) ?>,
                                                                    borderWidth: 1
                                                                }]
                                                            },
                                                            options: {
                                                                responsive: true,
                                                                maintainAspectRatio: false,
                                                                plugins: {
                                                                    legend: {
                                                                        position: 'bottom',
                                                                        labels: {
                                                                            fontSize: 10,
                                                                            boxWidth: 12
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        });
                                                    }
                                                })();
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </script>

                                <?php else: ?>
                                    <div class="alert alert-info">
                                        No analytics data available for this form. Make sure the form has responses and valid questions.
                                    </div>
                                <?php endif; ?>
                            </div>
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
