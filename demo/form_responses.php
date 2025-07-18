<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [4])) {
    header("Location: login.php");
    exit();
}


include('config/config.php');
include('assets/inc/incHeader.php');

$form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
if ($form_id <= 0) {
    $_SESSION['alert_message'] = "Invalid form ID.";
    header("Location: index.php");
    exit();
}

// Fetch form title
$stmt = $conn->prepare("SELECT title, questions_json FROM demo_forms_combined WHERE id = :form_id");
$stmt->execute([':form_id' => $form_id]);
$form = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$form) {
    $_SESSION['alert_message'] = "Form not found.";
    header("Location: index.php");
    exit();
}

// Decode questions
$questions = [];
if (!empty($form['questions_json'])) {
    $questions = json_decode($form['questions_json'], true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($questions)) {
        $questions = [];
    }
}

// Build headers
$headers = [];
foreach ($questions as $section) {
    $sectionTitle = $section['section_title'] ?? '';
    foreach (($section['questions'] ?? []) as $q) {
        $headers[] = [
            'section_title' => $sectionTitle,
            'question_text' => $q['text'] ?? ''
        ];
    }
}

// Pagination
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Search filters
$conditions = "form_id = :form_id";
$params = [':form_id' => $form_id];

$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_number = isset($_GET['search_number']) ? trim($_GET['search_number']) : '';

if ($search_name !== '') {
    $conditions .= " AND (firstname LIKE :search_name OR lastname LIKE :search_name)";
    $params[':search_name'] = '%' . $search_name . '%';
}
if ($search_number !== '') {
    $conditions .= " AND number LIKE :search_number";
    $params[':search_number'] = '%' . $search_number . '%';
}

// Count total
$total_stmt = $conn->prepare("SELECT COUNT(*) FROM form_responses_combined WHERE $conditions");
$total_stmt->execute($params);
$total_responses = $total_stmt->fetchColumn();
$total_pages = ceil($total_responses / $limit);

// Fetch data
$query = "SELECT * FROM form_responses_combined WHERE $conditions ORDER BY submitted_at DESC, id ASC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($query);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Responses: <?= htmlspecialchars($form['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table th, .table td { vertical-align: middle; }
        .section-header { font-weight: bold; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">Responses for: <?= htmlspecialchars($form['title']) ?></h2>
        <a href="export_responses.php?form_id=<?= $form_id ?>&search_name=<?= urlencode($search_name) ?>&search_number=<?= urlencode($search_number) ?>" class="btn btn-success"><i class="bi bi-download"></i> Download CSV</a>
    </div>

    <!-- Search Form -->
    <form method="get" class="mb-4">
        <input type="hidden" name="form_id" value="<?= $form_id ?>">
        <input type="hidden" name="limit" value="<?= $limit ?>">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search_name" class="form-control" placeholder="Search by Name" value="<?= htmlspecialchars($search_name) ?>">
            </div>
            <div class="col-md-4">
                <input type="text" name="search_number" class="form-control" placeholder="Search by Number" value="<?= htmlspecialchars($search_number) ?>">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="form_responses.php?form_id=<?= $form_id ?>" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    <?php if (empty($responses)): ?>
        <div class="alert alert-info mt-3">No responses found.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Number</th>
                    <th>Submitted At</th>
                    <?php
                    foreach ($questions as $section) {
                        $colspan = count($section['questions'] ?? []);
                        if ($colspan > 0) {
                            echo '<th colspan="' . $colspan . '">' . htmlspecialchars($section['section_title'] ?? 'General') . '</th>';
                        }
                    }
                    ?>
                </tr>
                <tr>
                    <?php for ($i = 0; $i < 5; $i++) echo '<th></th>'; ?>
                    <?php foreach ($headers as $h): ?>
                        <th><?= htmlspecialchars($h['question_text']) ?></th>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <?php $i = 1 + $offset; foreach ($responses as $resp): ?>
                    <?php
                    $answers = [];
                    if (!empty($resp['responses_json'])) {
                        $json = json_decode($resp['responses_json'], true);
                        if (is_array($json)) {
                            foreach ($json as $section) {
                                foreach ($section['answers'] ?? [] as $a) {
                                    $answer_value = $a['answer'] ?? '-';
                                    // Check if the answer is a JSON string (likely from checkboxes)
                                    $decoded_answer = json_decode($answer_value, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_answer)) {
                                        $answers[] = implode(', ', $decoded_answer);
                                    } else {
                                        $answers[] = $answer_value;
                                    }
                                }
                            }
                        }
                    }
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($resp['firstname'] . ' ' . $resp['lastname']) ?></td>
                        <td><?= htmlspecialchars($resp['email']) ?></td>
                        <td><?= htmlspecialchars($resp['number']) ?></td>
                        <td><?= htmlspecialchars(date('d-m-Y H:i', strtotime($resp['submitted_at']))) ?></td>
                        <?php foreach ($answers as $ans): ?>
                            <td><?= htmlspecialchars($ans) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination and Limit Selector -->
        <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="d-flex align-items-center">
                <label for="limit-select" class="form-label mb-0">Items per page:</label>
                <select id="limit-select" class="form-select form-select-sm" onchange="updateLimit(this.value)">
                    <?php
                    $limits = [5, 10, 25, 50, 100];
                    foreach ($limits as $l) {
                        $selected = ($limit == $l) ? 'selected' : '';
                        echo '<option value="' . $l . '" ' . $selected . '>' . $l . '</option>';
                    }
                    ?>
                </select>
            </div>
        <nav>
                <ul class="pagination mb-0">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?form_id=<?= $form_id ?>&limit=<?= $limit ?>&page=<?= $page - 1 ?>&search_name=<?= urlencode($search_name) ?>&search_number=<?= urlencode($search_number) ?>">Previous</a>
                    </li>
                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <li class="page-item <?= ($page == $p) ? 'active' : '' ?>">
                            <a class="page-link" href="?form_id=<?= $form_id ?>&limit=<?= $limit ?>&page=<?= $p ?>&search_name=<?= urlencode($search_name) ?>&search_number=<?= urlencode($search_number) ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?form_id=<?= $form_id ?>&limit=<?= $limit ?>&page=<?= $page + 1 ?>&search_name=<?= urlencode($search_name) ?>&search_number=<?= urlencode($search_number) ?>">Next</a>
                    </li>
                </ul>
            </nav>

            
        </div>
    <?php endif; ?>
 <?php if ($_SESSION['role_id'] == 1) { ?>
            <a href="index.php" class="btn btn-secondary mt-4">Back to Dashboard</a>
            <?php } ?>
            <?php if ($_SESSION['role_id'] == 2) { ?>
            <a href="moderator_dashboard.php" class="btn btn-secondary mt-4">Back to Moderator Dashboard</a>
            <?php } ?>
            <?php if ($_SESSION['role_id'] == 3) { ?>
            <a href="user_dashboard.php" class="btn btn-secondary mt-4">Back to user Dashboard</a>
            <?php } ?>
    <!-- <a href="index.php" class="btn btn-secondary mt-4">Back to Dashboard</a> -->
</div>

<script>
    function updateLimit(newLimit) {
        var currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('limit', newLimit);
        currentUrl.searchParams.set('page', 1); // Reset to first page when limit changes
        window.location.href = currentUrl.toString();
    }
</script>
</body>
</html>
