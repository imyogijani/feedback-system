<?php
session_start();
include('config/config.php');

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit();
}

$form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;

if ($form_id <= 0) {
    $_SESSION['alert_message'] = "Invalid form ID for export.";
    header("Location: form_responses.php");
    exit();
}

// Fetch form title and questions
$stmt = $conn->prepare("SELECT title, questions_json FROM forms_combined WHERE id = :form_id");
$stmt->execute([':form_id' => $form_id]);
$form = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$form) {
    $_SESSION['alert_message'] = "Form not found for export.";
    header("Location: form_responses.php");
    exit();
}

$form_title = $form['title'];
$questions_data = json_decode($form['questions_json'], true);

// Prepare CSV headers
$csv_headers = ['Response ID', 'Name', 'Email', 'Phone Number', 'Submitted At'];
if (is_array($questions_data)) {
    foreach ($questions_data as $section) {
        $section_title = $section['section_title'] ?? 'General';
        if (isset($section['questions']) && is_array($section['questions'])) {
            foreach ($section['questions'] as $q) {
                $question_text = $q['text'] ?? '';
                $csv_headers[] = ($section_title !== 'General' ? $section_title . ' - ' : '') . $question_text;
            }
        }
    }
}

// Fetch all responses for the form
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_number = isset($_GET['search_number']) ? trim($_GET['search_number']) : '';

$sql = "SELECT * FROM form_responses_combined WHERE form_id = :form_id";
$params = [':form_id' => $form_id];

if (!empty($search_name)) {
    $sql .= " AND (firstname LIKE :search_name OR lastname LIKE :search_name)";
    $params[':search_name'] = '%' . $search_name . '%';
}
if (!empty($search_number)) {
    $sql .= " AND number LIKE :search_number";
    $params[':search_number'] = '%' . $search_number . '%';
}

$sql .= " ORDER BY submitted_at DESC, id ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-zA-Z0-9_]/', '', $form_title) . '_responses.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Write CSV headers
fputcsv($output, $csv_headers);

// Write CSV data
foreach ($responses as $resp) {
    $row = [
        $resp['id'],
        $resp['firstname'] . ' ' . $resp['lastname'],
        $resp['email'],
        $resp['number'],
        date('d-m-Y H:i', strtotime($resp['submitted_at']))
    ];

    $answers_data = json_decode($resp['responses_json'], true);
    $current_response_answers = [];

    // Create a temporary map of answers for the current response, using section_id_question_id as key
    if (is_array($answers_data)) {
        foreach ($answers_data as $section_resp) {
            if (isset($section_resp['answers']) && is_array($section_resp['answers'])) {
                foreach ($section_resp['answers'] as $ans) {
                    $key = ($section_resp['section_id'] ?? '') . '_' . ($ans['question_id'] ?? '');
                    $current_response_answers[$key] = $ans['answer'] ?? '';
                }
            }
        }
    }

    // Populate the row with answers in the correct order based on questions_data
    if (is_array($questions_data)) {
        foreach ($questions_data as $section) {
            if (isset($section['questions']) && is_array($section['questions'])) {
                foreach ($section['questions'] as $q) {
                    $question_key = ($section['section_id'] ?? '') . '_' . ($q['question_id'] ?? '');
                    $answer_value = $current_response_answers[$question_key] ?? '-'; // Default to '-' if no answer found

                    // Handle array answers (e.g., checkboxes) by joining them
                    $decoded_answer = json_decode($answer_value, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_answer)) {
                        $row[] = implode(', ', $decoded_answer);
                    } else {
                        $row[] = $answer_value;
                    }
                }
            }
        }
    }
    fputcsv($output, $row);
}

fclose($output);
exit();

?>