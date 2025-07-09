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
$stmt = $conn->prepare("SELECT title FROM forms WHERE id = :form_id");
$stmt->execute([':form_id' => $form_id]);
$form = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$form) {
    $_SESSION['alert_message'] = "Form not found.";
    header("Location: index.php");
    exit();
}

// Fetch all questions for the form (needed for table headers, even if unanswered)
$qstmt = $conn->prepare("SELECT id, question_text FROM questions WHERE form_id = :form_id ORDER BY id ASC");
$qstmt->execute([':form_id' => $form_id]);
$questions = $qstmt->fetchAll(PDO::FETCH_ASSOC);

// --- Consolidated Query to fetch all data ---
// This query now correctly joins using form_responses.id and responses.form_response_id
$stmt_all_data = $conn->prepare("
    SELECT
        fr.id AS response_id,
        fr.firstname,
        fr.lastname,
        fr.email,
        fr.number,
        fr.submitted_at,
        q.id AS question_id,
        q.question_text,
        r.answer
    FROM
        form_responses fr
    JOIN
        responses r ON fr.id = r.form_response_id -- CRUCIAL CHANGE: Linking via the new column
    JOIN
        questions q ON r.question_id = q.id AND q.form_id = fr.form_id
    WHERE
        fr.form_id = :form_id
    ORDER BY
        fr.submitted_at DESC, fr.id ASC, q.id ASC
");
$stmt_all_data->execute([':form_id' => $form_id]);
$raw_responses_data = $stmt_all_data->fetchAll(PDO::FETCH_ASSOC);
// --- End of Consolidated Query ---

// Process raw data to group answers by submission
$all_responses_display = [];
foreach ($raw_responses_data as $row) {
    $current_response_id = $row['response_id'];

    if (!isset($all_responses_display[$current_response_id])) {
        // Initialize the submission data if it's the first time we see this response_id
        $all_responses_display[$current_response_id] = [
            'id' => $row['response_id'],
            'firstname' => $row['firstname'] ?? '-',
            'lastname' => $row['lastname'] ?? '-',
            'email' => $row['email'] ?? '-',
            'number' => $row['number'] ?? '-',
            'submitted_at' => $row['submitted_at'] ?? '-',
            'Youtubes' => [] // Will store answers keyed by question_id
        ];
    }
    // Add the question's answer to the current submission
    $all_responses_display[$current_response_id]['Youtubes'][$row['question_id']] = $row['answer'];
}

// Convert associative array to indexed array for simpler iteration in HTML
$all_responses_display = array_values($all_responses_display);

// Sort the final display array (though the SQL ORDER BY helps, this ensures it)
usort($all_responses_display, function($a, $b) {
    return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
});

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Responses for <?= htmlspecialchars($form['title']) ?></title>
    <link rel="stylesheet" href="path/to/your/styles.css">
    </head>
<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <div class="layout-page">
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <h2>Responses for: <?= htmlspecialchars($form['title']) ?></h2>

                    <?php if (empty($all_responses_display)): ?>
                        <div class="alert alert-info">No responses yet for this form.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Email</th>
                                    <th>Number</th>
                                    <th>Submitted At</th>
                                    <!-- <th>Response ID</th> -->
                                    <?php foreach ($questions as $q): // Use the fetched questions for headers ?>
                                        <th><?= htmlspecialchars($q['question_text']) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $i = 1; ?>
                                <?php foreach ($all_responses_display as $data): ?>
                                    <tr>
                                        <td><?= $i ?></td>
                                        <td><?= htmlspecialchars($data['firstname']) ?></td>
                                        <td><?= htmlspecialchars($data['lastname']) ?></td>
                                        <td><?= htmlspecialchars($data['email']) ?></td>
                                        <td><?= htmlspecialchars($data['number']) ?></td>
                                        <td>
                                            <?php
                                            // Format date as dd-mm-yy h:m
                                            $dt = $data['submitted_at'];
                                            if ($dt && strtotime($dt)) {
                                                echo date('d-m-y h:i:s', strtotime($dt));
                                            } else {
                                                echo htmlspecialchars($dt);
                                            }
                                            ?>
                                        </td>
                                        <!-- <td><?= $data['id'] ?></td> -->
                                        <?php foreach ($questions as $q): ?>
                                            <td><?= htmlspecialchars($data['Youtubes'][$q['id']] ?? '-') ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php $i++; endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <a href="index.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
                </div>
                <?php include('assets/inc/incFooter.php'); // Assuming this includes your footer content ?>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>

<!-- incScripts.php not found, so this include is removed to prevent warnings. -->
<script>
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => alert.remove());
    }, 5000);
</script>
</body>
</html>