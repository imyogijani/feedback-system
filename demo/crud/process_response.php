<?php
session_start();
include('../../admin/config/config.php');


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
    $stmt = $conn->prepare("SELECT * FROM forms WHERE id = ?");
    $stmt->execute([$form_id]);
    $form = $stmt->fetch();

    if (!$form) {
        throw new Exception("Form not found.");
    }

    // Get questions
    $stmt = $conn->prepare("SELECT * FROM questions WHERE form_id = ?");
    $stmt->execute([$form_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Begin transaction
    $conn->beginTransaction();

    // Insert into form_responses
    $stmtResponse = $conn->prepare("INSERT INTO form_responses (form_id, firstname, lastname, email, number, submitted_at) VALUES (?, ?, ?, ?, ?, NOW())");

    $firstname = !empty($form['firstname']) ? ($_POST['firstname'] ?? null) : null;
    $lastname  = !empty($form['lastname']) ? ($_POST['lastname'] ?? null) : null;
    $email     = !empty($form['email']) ? ($_POST['email'] ?? null) : null;
    $number    = !empty($form['number']) ? ($_POST['number'] ?? null) : null;

    $stmtResponse->execute([$form_id, $firstname, $lastname, $email, $number]);

    $response_id = $_POST['form_id'] ?? 0;

    // Insert into user_responses
    $stmtAnswer = $conn->prepare("INSERT INTO responses (form_id, question_id, answer) VALUES (?, ?, ?)");

    foreach ($questions as $q) {
        $qid = $q['id'];
        $fieldName = 'q_' . $qid;

        $answer = $_POST[$fieldName] ?? null;

        // Handle checkbox arrays
        if ($q['question_type'] === 'checkbox' && is_array($answer)) {
            $answer = json_encode($answer);
        }

        // Default rating to 0 if not selected
        if (in_array($q['question_type'], ['rating_star', 'rating_heart', 'rating_thumb']) && $answer === null) {
            $answer = '0';
        }

        // Insert answer (even if empty)
        $stmtAnswer->execute([$response_id, $qid, $answer ?? '']);
    }

    $conn->commit();

    $_SESSION['success'] = "Your response has been submitted.";
    header("Location: ../crud/thank_you.php");
    exit;
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Form submission error: " . $e->getMessage());
    $_SESSION['error'] = "There was an error saving your response.";
    header("Location: ../view_form.php?id=" . urlencode($form_id));
    exit;
}
