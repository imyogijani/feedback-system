<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit();
}

include('config/config.php');
include('assets/inc/incHeader.php'); // Assuming this includes necessary HTML header info

$form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
if ($form_id <= 0) {
    $_SESSION['alert_message'] = "Invalid form ID.";
    header("Location: index.php");
    exit();
}

// Fetch form title
$stmt = $conn->prepare("SELECT title FROM forms_combined WHERE id = :form_id");
$stmt->execute([':form_id' => $form_id]);
$form = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$form) {
    $_SESSION['alert_message'] = "Form not found.";
    header("Location: index.php");
    exit();
}

// Fetch all responses from form_responses_combined
$stmt = $conn->prepare("SELECT * FROM form_responses_combined WHERE form_id = :form_id ORDER BY submitted_at DESC, id ASC");
$stmt->execute([':form_id' => $form_id]);
$responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch questions from forms_combined (sectioned)
$qstmt = $conn->prepare("SELECT questions_json FROM forms_combined WHERE id = :form_id");
$qstmt->execute([':form_id' => $form_id]);
$formRow = $qstmt->fetch(PDO::FETCH_ASSOC);
$questions = [];
if (!empty($formRow['questions_json'])) {
    $questions = json_decode($formRow['questions_json'], true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($questions)) {
        $questions = [];
    }
}

// Prepare table headers (section-wise)
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Responses for <?= htmlspecialchars($form['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #fff; }
        .table { border: 1px solid #dee2e6; background: #fff; }
        .table th, .table td { vertical-align: middle; border: 1px solid #dee2e6; background: #fff; }
        .section-header { font-weight: bold; }
        .table thead th { background: #fff; color: #343a40; }
        .alert-info { margin-top: 2rem; }
    </style>
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4 text-primary">Responses for: <?= htmlspecialchars($form['title']) ?></h2>
    <?php if (empty($responses)): ?>
        <div class="alert alert-info">No responses yet for this form.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Number</th>
                    <th>Submitted At</th>
                    <?php foreach ($headers as $h): ?>
                        <th><span class="section-header"><?= htmlspecialchars($h['section_title']) ?></span><br><span><?= htmlspecialchars($h['question_text']) ?></span></th>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <?php $i = 1; foreach ($responses as $resp): ?>
                    <?php
                    $answers = [];
                    if (!empty($resp['responses_json'])) {
                        $answers_json = json_decode($resp['responses_json'], true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($answers_json)) {
                            foreach ($answers_json as $section) {
                                foreach (($section['answers'] ?? []) as $a) {
                                    $answers[] = $a['answer'] ?? '-';
                                }
                            }
                        }
                    }
                    ?>
                    <tr>
                        <td><?= $i ?></td>
                        <td><?= htmlspecialchars($resp['firstname']) ?></td>
                        <td><?= htmlspecialchars($resp['lastname']) ?></td>
                        <td><?= htmlspecialchars($resp['email']) ?></td>
                        <td><?= htmlspecialchars($resp['number']) ?></td>
                        <td>
                            <?php
                            $dt = $resp['submitted_at'];
                            if ($dt && strtotime($dt)) {
                                echo date('d-m-y h:i:s', strtotime($dt));
                            } else {
                                echo htmlspecialchars($dt);
                            }
                            ?>
                        </td>
                        <?php foreach ($answers as $ans): ?>
                            <td><?= htmlspecialchars($ans) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php $i++; endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <a href="index.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => alert.remove());
    }, 5000);
</script>
</body>
</html>