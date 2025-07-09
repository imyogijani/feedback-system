<?php
session_start();
include('../config/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formTypes = $_POST['types1'] ?? [];
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $questions = $_POST['questions'] ?? [];
    $types = $_POST['types'] ?? [];
    $options = $_POST['options'] ?? [];
    $radio_options = $_POST['radio_options'] ?? [];
    $ratings = $_POST['rating'] ?? [];
    $firstname = isset($_POST['firstname']) ? 1 : 0;
    $lastname = isset($_POST['lastname']) ? 1 : 0;
    $email = isset($_POST['email']) ? 1 : 0;
    $number = isset($_POST['number']) ? 1 : 0;
    $created_by = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    $created_for = isset($_POST['created_for']) && $_POST['created_for'] !== '' ? $_POST['created_for'] : 1;
// var_dump($created_for); // Debugging line to check created_for value
// exit;

    // Only apply form count limit for users with role_id 2 or 3 (not admin)
    $role_id = $_SESSION['role_id'] ?? null;
    if (in_array($role_id, [2, 3])) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM forms WHERE created_by = ?");
        $stmt->execute([$created_by]);
        $formCount = $stmt->fetchColumn();
        if ($formCount >= 5) {
            $_SESSION['error'] = "You have reached the maximum limit of 5 forms.";
            header("Location: ../form_generator.php");
            exit;
        }
    }

    try {
        // Begin transaction
        $conn->beginTransaction();

        // Convert array values to string for form types
        $formTypesString = implode(',', $formTypes);

        // Save form (add created_by and created_for fields)
        $sql = "INSERT INTO forms (form_type, title, description, firstname, lastname, email, number, created_at, created_by, created_for) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare statement failed (Form): " . implode(' | ', $conn->errorInfo()));
        }

        $stmt->execute([
            $formTypesString,
            $title,
            $description,
            $firstname,
            $lastname,
            $email,
            $number,
            $created_by,
            $created_for
        ]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("Insert form failed");
        }

        $formId = $conn->lastInsertId();

        // Loop through questions
        foreach ($questions as $index => $questionText) {
            $questionText = trim($questionText);
            if (empty($questionText)) {
                continue;
            }

            $questionType = $types[$index] ?? null;

            $sql = "INSERT INTO questions (form_id, question_text, question_type) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Prepare statement failed (Question): " . implode(' | ', $conn->errorInfo()));
            }

            $stmt->execute([$formId, $questionText, $questionType]);
            $questionId = $conn->lastInsertId();

            // Handle options for checkbox/radio/dropdown
            if (in_array($questionType, ['checkbox', 'radio', 'dropdown'])) {
                $questionNumber = $index + 1;
                if (isset($options[$questionNumber])) {
                    foreach ($options[$questionNumber] as $optionText) {
                        $sql = "INSERT INTO options (question_id, option_text) VALUES (?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$questionId, $optionText]);
                    }
                }
            }
            // Save default/empty value for date picker (if needed)
            if ($questionType === 'date') {
                // Optionally, you can store a default value or just skip options for date picker
                // $sql = "INSERT INTO options (question_id, option_text) VALUES (?, ?)";
                // $stmt = $conn->prepare($sql);
                // $stmt->execute([$questionId, 'date']);
            }
        }

        // Commit the transaction
        $conn->commit();

        $_SESSION['success'] = "Feedback form created successfully.";
        header("Location: ../index.php");
        exit;
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        error_log("Error saving form: " . $e->getMessage());
        $_SESSION['error'] = "There was an error saving the form: " . $e->getMessage();
        header("Location: ../form_generator.php");
        exit;
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: ../form_generator.php");
    exit;
}
