<?php
session_start();
include('config/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: ../feedback_form_list.php");
    exit;
}

$form_id = $_POST['form_id'] ?? 0;
if (!$form_id) {
    $_SESSION['error'] = "Form ID is missing.";
    header("Location: ../feedback_form_list.php");
    exit;
}

try {
    // Get form info
    $stmt = $conn->prepare("SELECT * FROM demo_forms_combined WHERE id = ?");
    $stmt->execute([$form_id]);
    $form = $stmt->fetch();

    if (!$form) {
        throw new Exception("Form not found.");
    }

    // Get questions from JSON in forms_combined
    $questions = [];
    if (!empty($form['questions_json'])) {
        $questions = json_decode($form['questions_json'], true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($questions)) {
            $questions = [];
        }
    }

    // Begin transaction
    $conn->beginTransaction();

    // Prepare answers for JSON storage
    $answers_json = [];
    foreach ($questions as $section) {
        $section_id = $section['section_id'] ?? 0;
        $section_title = $section['section_title'] ?? '';
        $section_answers = [];
        foreach (($section['questions'] ?? []) as $qidx => $q) {
            $qText = $q['text'] ?? '';
            $qType = strtolower($q['type'] ?? 'text');
            $fieldName = 'q_' . $section_id . '_' . $qidx;
            $answer = $_POST[$fieldName] ?? null;
            if ($qType === 'checkbox' && is_array($answer)) {
                $answer = json_encode($answer);
            }
            if (in_array($qType, ['rating_star', 'rating_heart', 'rating_thumb']) && $answer === null) {
                $answer = '0';
            }
            $section_answers[] = [
                'question_text' => $qText,
                'answer' => $answer ?? ''
            ];
        }
        $answers_json[] = [
            'section_id' => $section_id,
            'section_title' => $section_title,
            'answers' => $section_answers
        ];
    }
    $responses_json = json_encode($answers_json, JSON_UNESCAPED_UNICODE);

    // Insert into form_responses_combined with responses_json
    $stmtResponse = $conn->prepare("INSERT INTO demo_form_responses_combined (form_id, firstname, lastname, email, number, responses_json, submitted_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");

    $firstname = !empty($form['firstname']) ? ($_POST['firstname'] ?? null) : null;
    $lastname  = !empty($form['lastname']) ? ($_POST['lastname'] ?? null) : null;
    $email     = !empty($form['email']) ? ($_POST['email'] ?? null) : null;
    $number    = !empty($form['number']) ? ($_POST['number'] ?? null) : null;

    $stmtResponse->execute([$form_id, $firstname, $lastname, $email, $number, $responses_json]);
    $form_response_id = $conn->lastInsertId();

    $conn->commit();

    $_SESSION['success'] = "Your response has been submitted.";
    
    // Redirect to thank_you.php with form_id as GET param
    header("Location: demo_thankyou.php?form_id=" . urlencode($form_id));
    exit;
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Form submission error: " . $e->getMessage());
    $_SESSION['error'] = "There was an error saving your response.";
    header("Location: demo_thankyou.php?id=" . urlencode($form_id));
    exit;
}
