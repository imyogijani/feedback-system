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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_id = intval($_POST['form_id']);
    $form_title = trim($_POST['form_title']);
    $questions = $_POST['questions'] ?? [];

    try {
        $conn->beginTransaction();

        // 1. Update form title
        $stmt = $conn->prepare("UPDATE demo_forms_combined SET title = :title WHERE id = :id");
        $stmt->execute([
            ':title' => $form_title,
            ':id' => $form_id
        ]);

        // 2. Loop through questions
        foreach ($questions as $q) {
            // Only process if question has a valid numeric id
            if (!isset($q['id']) || !is_numeric($q['id']) || intval($q['id']) <= 0) {
                // TODO: Handle new questions (insert into questions table, then get new id, then insert options)
                continue;
            }
            $question_id = intval($q['id']);
            $question_text = trim($q['text']);
            $question_type = trim($q['type']);
            $options_str = isset($q['options']) ? trim($q['options']) : '';

            // Update question text & type
            $qstmt = $conn->prepare("UPDATE demo_forms_combined SET question_text = :text, question_type = :type WHERE id = :id AND form_id = :form_id");
            $qstmt->execute([
                ':text' => $question_text,
                ':type' => $question_type,
                ':id' => $question_id,
                ':form_id' => $form_id
            ]);

            // Always handle options for radio/checkbox
            if (in_array($question_type, ['radio', 'checkbox'])) {
                // Clear old options
                $del = $conn->prepare("DELETE FROM demo_form_responses_combined WHERE question_id = :qid");
                $del->execute([':qid' => $question_id]);

                // Insert new options
                $options = array_map('trim', explode(',', $options_str));
                $opt_stmt = $conn->prepare("INSERT INTO demo_form_responses_combined (question_id, option_text) VALUES (:qid, :opt)");
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
                $del = $conn->prepare("DELETE FROM demo_forms_combined WHERE question_id = :qid");
                $del->execute([':qid' => $question_id]);
            }

            // ✅ Update responses (answers)
            if (isset($_POST['responses']) && is_array($_POST['responses'])) {
                $resStmt = $conn->prepare("UPDATE demo_form_responses_combined SET answer = :answer WHERE id = :id");
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

        // --- Update forms_combined.questions_json with the new structure ---
        // Rebuild the section-wise questions array from POST data
        $sections = [];
        if (isset($_POST['questions']) && is_array($_POST['questions'])) {
            // Group questions by section if section info is available, else flat
            // For now, assume flat (all in one section)
            $section = [
                'section_title' => isset($form_title) ? $form_title : '',
                'questions' => []
            ];
            foreach ($_POST['questions'] as $q) {
                $qArr = [
                    'id' => isset($q['id']) ? $q['id'] : '',
                    'text' => isset($q['text']) ? $q['text'] : '',
                    'type' => isset($q['type']) ? $q['type'] : '',
                ];
                // Handle options as array
                if (isset($q['options'])) {
                    $opts = array_map('trim', explode(',', $q['options']));
                    $opts = array_filter($opts, function($v) { return $v !== ''; });
                    $qArr['options'] = array_values($opts);
                }
                $section['questions'][] = $qArr;
            }
            $sections[] = $section;
        }
        $questions_json = json_encode($sections, JSON_UNESCAPED_UNICODE);
        $updateJson = $conn->prepare("UPDATE demo_forms_combined SET questions_json = :qjson WHERE id = :id");
        $updateJson->execute([
            ':qjson' => $questions_json,
            ':id' => $form_id
        ]);

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
            } elseif ($_SESSION['role_id'] == 4) {
                header("Location: user_dashboard.php");}
            else {
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
