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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_id = intval($_POST['form_id']);
    $form_title = trim($_POST['form_title']);
    $questions = $_POST['questions'] ?? [];

    try {
        $conn->beginTransaction();

        // 1. Update form title
        $stmt = $conn->prepare("UPDATE forms SET title = :title WHERE id = :id");
        $stmt->execute([
            ':title' => $form_title,
            ':id' => $form_id
        ]);

        // 2. Loop through questions
        foreach ($questions as $q) {
            $question_id = intval($q['id']);
            $question_text = trim($q['text']);
            $question_type = trim($q['type']);
            $options_str = isset($q['options']) ? trim($q['options']) : '';

            // Update question text & type
            $qstmt = $conn->prepare("UPDATE questions SET question_text = :text, question_type = :type WHERE id = :id AND form_id = :form_id");
            $qstmt->execute([
                ':text' => $question_text,
                ':type' => $question_type,
                ':id' => $question_id,
                ':form_id' => $form_id
            ]);

            // Always handle options for radio/checkbox
            if (in_array($question_type, ['radio', 'checkbox'])) {
                // Clear old options
                $del = $conn->prepare("DELETE FROM options WHERE question_id = :qid");
                $del->execute([':qid' => $question_id]);

                // Insert new options
                $options = array_map('trim', explode(',', $options_str));
                $opt_stmt = $conn->prepare("INSERT INTO options (question_id, option_text) VALUES (:qid, :opt)");
                foreach ($options as $opt) {
                    if ($opt !== '') {
                        $opt_stmt->execute([
                            ':qid' => $question_id,
                            ':opt' => $opt
                        ]);
                    }
                }
            } else {
                // If the type is not radio/checkbox, delete any old options
                $del = $conn->prepare("DELETE FROM options WHERE question_id = :qid");
                $del->execute([':qid' => $question_id]);
            }

            // ✅ Update responses (answers)
            if (isset($_POST['responses']) && is_array($_POST['responses'])) {
                $resStmt = $conn->prepare("UPDATE responses SET answer = :answer WHERE id = :id");
                foreach ($_POST['responses'] as $res) {
                    $response_id = intval($res['id']);
                    $answer = trim($res['answer']);
                    $resStmt->execute([
                        ':answer' => $answer,
                        ':id' => $response_id
                    ]);
                }
            }
        }

        $conn->commit();
        $_SESSION['success_message'] = "Form updated successfully.";
        // Redirect based on role_id
        if (isset($_SESSION['role_id'])) {
            if ($_SESSION['role_id'] == 1) {
                header("Location: index.php"); // Admin dashboard
            } elseif ($_SESSION['role_id'] == 2) {
                header("Location: moderator_dashboard.php"); // Moderator dashboard
            } elseif ($_SESSION['role_id'] == 3) {
                header("Location: user_dashboard.php"); // User dashboard (for both traditional and Google logins)
            } else {
                header("Location: index.php"); // Default fallback
            }
        } else {
            header("Location: index.php");
        }
        exit();
    } catch (PDOException $e) {
        $conn->rollBack();
        die("Failed to update form: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit();
}
