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
    $form_type = $_POST['form_type'] ?? '';

    $created_for = $_POST['created_for'] ?? null;
    $company_name = $_POST['company_name'] ?? '';
    $company_logo = '';

    if ($created_for === null || $created_for === '') {
        // If no existing business is selected, create a new user entry
        if (!empty($company_name)) {
            // Handle company logo upload
            if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
                $logo_tmp_name = $_FILES['company_logo']['tmp_name'];
                $logo_name = basename($_FILES['company_logo']['name']);
                $upload_dir = '../../assets/images/'; // Adjust this path as needed

                $target_file = $upload_dir . $logo_name;

                if (move_uploaded_file($logo_tmp_name, $target_file)) {
                    $company_logo = $logo_name; // Save only the filename
                } else {
                    error_log("Failed to move uploaded file: " . $logo_tmp_name . " to " . $target_file);
                }
            }

            $sanitized_company_name = preg_replace('/[^a-zA-Z0-9_ -]/', '', $company_name);
            $sanitized_company_name = str_replace(' ', '_', $sanitized_company_name);
            $sanitized_company_name = strtolower($sanitized_company_name);
            $email = $sanitized_company_name . '@gmail.com';
            // Insert new user (company) into the users table
            $stmt = $conn->prepare("INSERT INTO users (username,password,business_name, profile_image, role_id,email) VALUES (?,?,'123456',?, ?, ?)");
            // Assuming a default role_id for newly created companies, e.g., 4 for 'Company'
            $stmt->execute([$company_name, $company_name, $company_logo, 3,$email]);
            $created_for = $conn->lastInsertId(); // Get the ID of the newly created user
        } else {
            $created_for = 1; // Default to user ID 1 if no company name is provided
        }
    }

    $business_name = '';

    if ($created_for !== 1) {
        $stmt = $conn->prepare("SELECT business_name FROM users WHERE id = ?");
        $stmt->execute([$created_for]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $business_name = $user['business_name'];
        } else {
            $business_name = 'Aksharraj infotech';
        }
    }

    // Sanitize business name consistently
    $sanitized_business_name = preg_replace('/[^a-zA-Z0-9_ -]/', '', $business_name);
    $sanitized_business_name = str_replace(' ', '_', $sanitized_business_name);
    // $sanitized_business_name = strtolower($sanitized_business_name);

    $formsBasePath = '../../forms/';
    $businessFormsPath = $formsBasePath . $sanitized_business_name . '/';

    // Create directory if it doesn't exist and not empty
    if (!empty($sanitized_business_name) && !is_dir($businessFormsPath)) {
        if (!mkdir($businessFormsPath, 0777, true)) {
            throw new Exception("Failed to create directory: " . $businessFormsPath);
        }
    }

    try {
        $conn->beginTransaction();

        // Insert form details
        $stmt = $conn->prepare("INSERT INTO forms (title, description, form_type, created_by, created_for, firstname, lastname, email, number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $form_type, $created_by, $created_for, $firstname, $lastname, $email, $number]);
        $form_id = $conn->lastInsertId();
        $_SESSION['form_id'] = $form_id; // Set formId from the generated ID


        // Generate form file
        $formFileName = "feedback-form-{$form_id}.php";
        $formFilePath = $businessFormsPath . $formFileName;

        // Start building the form content string
        $formContent = <<<'EOD'
<?php
session_start();
include('../config/config.php'); // Adjust path as needed

// Ensure PDO is set to throw exceptions for better error handling during development
if (isset($conn)) {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

$formId = $_SESSION['form_id'];

// Basic validation for form ID
if ($formId <= 0) {
    echo "Invalid Form ID provided.";
    exit();
}

// Fetch form details
$stmt = $conn->prepare("SELECT * FROM forms WHERE id = ?");
$stmt->execute([$formId]);
$form = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch as associative array

// Handle case where form is not found
if (!$form) {
    echo "Form not found.";
    exit();
}

// Fetch user info (business_name, profile_image) if the form is associated with a user
$user = null;
if (!empty($form['created_for'])) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$form['created_for']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch questions associated with the form
// Ordered by ID for consistent display order
$stmt = $conn->prepare("SELECT * FROM questions WHERE form_id = ? ORDER BY id ASC");
$stmt->execute([$formId]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($form['title']) ?> Feedback Form</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container.view-form {
            max-width: 800px;
            margin-top: 50px;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .title {
            color: #333;
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 500;
        }
        .rating-icons i {
            color: #ccc;
            transition: color 0.2s;
        }
        .rating-icons i.text-warning {
            color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="container mt-5 view-form">
        <div class="row mb-4 position-relative">
            <div class="col-md-12">
                <div style="display: flex; align-items: center; justify-content: center; background: #673ab7; color: #fff; border-radius: 8px; padding: 16px 18px 12px 18px; margin-bottom: 10px; min-height: 60px; gap: 14px;">
                    <?php
                    if ($user) {
                        $img = $user['profile_image'] ?? '';
                        $label = $user['business_name'] ?? '';
                        $imgPath = '';
                        if ($img && strpos($img, 'http') !== 0 && strpos($img, '/') !== 0) {
                            $imgPath = '../../admin/assets/images/' . $img;
                        } else {
                            $imgPath = $img;
                        }
                        if (!$imgPath || !file_exists(__DIR__ . '/' . $imgPath)) {
                            $imgPath = 'https://ui-avatars.com/api/?name=' . urlencode($label) . '&background=cccccc&color=222222&size=100';
                        }
                        echo '<img src="' . htmlspecialchars($imgPath) . '" alt="Profile Image" style="max-width:56px; max-height:56px; border-radius:8px; border:1.5px solid #fff; background:#fff;">';
                    }
                    if ($user && !empty($user['business_name'])) {
                        echo '<span style="font-size:1.2rem;font-weight:600; color:#fff; display:inline-block; white-space:nowrap;">' . htmlspecialchars($user['business_name']) . '</span>';
                    } else {
                        echo '<span style="font-size:1.2rem;font-weight:600; color:#fff; display:inline-block; white-space:nowrap;">Feedback Form</span>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <h2 class="text-center title"><?= htmlspecialchars($form['title']) ?></h2>
        <p><?= htmlspecialchars($form['description']) ?></p>
        <form method="POST" action="process_response.php">
            <input type="hidden" name="form_id" value="<?= htmlspecialchars($formId) ?>">

            <?php if (!empty($form['firstname']) || !empty($form['lastname'])): ?>
                <div class="row mb-3">
                    <?php if (!empty($form['firstname'])): ?>
                        <div class="col">
                            <label class="form-label" style="font-weight: 500;">First Name</label>
                            <input type="text" class="form-control" name="firstname" pattern="[A-Za-z\s]+" title="Only letters allowed" required>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($form['lastname'])): ?>
                        <div class="col">
                            <label class="form-label" style="font-weight: 500;">Last Name</label>
                            <input type="text" class="form-control" name="lastname" pattern="[A-Za-z\s]+" title="Only letters allowed" required>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($form['email']) || !empty($form['number'])): ?>
                <div class="row mb-3">
                    <?php if (!empty($form['email'])): ?>
                        <div class="col">
                            <label class="form-label" style="font-weight: 500;">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($form['number'])): ?>
                        <div class="col">
                            <label class="form-label" style="font-weight: 500;">Phone Number</label>
                            <input type="tel" class="form-control" name="number"
                                pattern="\d{10}"
                                maxlength="10"
                                title="Enter exactly 10 digits"
                                required
                                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)">
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php foreach ($questions as $q): ?>
                <div class="mb-3">
                    <label class="form-label" style="font-weight: 500;">
                        <?= htmlspecialchars($q['question_text']) ?>
                    </label>
                    <?php
                    // Fetch options for the current question (if applicable)
                    $stmt = $conn->prepare("SELECT * FROM options WHERE question_id = ? ORDER BY id ASC");
                    $stmt->execute([$q['id']]);
                    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    switch ($q['question_type']) {
                        case 'text':
                            echo '<input type="text" class="form-control" name="q_' . $q['id'] . '">';
                            break;
                        case 'textarea':
                            echo '<textarea class="form-control" name="q_' . $q['id'] . '"></textarea>';
                            break;
                        case 'radio':
                            foreach ($options as $opt) {
                                echo '<div class="form-check">
                                        <input class="form-check-input" type="radio" name="q_' . $q['id'] . '" value="' . htmlspecialchars($opt['option_text']) . '">
                                        <label class="form-check-label">' . htmlspecialchars($opt['option_text']) . '</label>
                                      </div>';
                            }
                            break;
                        case 'checkbox':
                            foreach ($options as $opt) {
                                echo '<div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="q_' . $q['id'] . '[]" value="' . htmlspecialchars($opt['option_text']) . '">
                                        <label class="form-check-label">' . htmlspecialchars($opt['option_text']) . '</label>
                                      </div>';
                            }
                            break;
                        case 'dropdown':
                            echo '<select class="form-select" name="q_' . $q['id'] . '">';
                            echo '<option value="">Select...</option>'; // Placeholder option
                            foreach ($options as $opt) {
                                echo '<option value="' . htmlspecialchars($opt['option_text']) . '">' . htmlspecialchars($opt['option_text']) . '</option>';
                            }
                            echo '</select>';
                            break;
                        case 'date':
                            echo '<input type="date" class="form-control" name="q_' . $q['id'] . '">';
                            break;
                        case 'rating_star':
                        case 'rating_heart':
                        case 'rating_thumb':
                            $icon = $q['question_type'] === 'rating_star' ? 'star' : ($q['question_type'] === 'rating_heart' ? 'heart' : 'hand-thumbs-up');
                            echo '<div class="rating-icons" data-question-id="' . $q['id'] . '" data-icon="' . $icon . '">';
                            for ($i = 1; $i <= 5; $i++) {
                                echo '<i class="bi bi-' . $icon . '" data-value="' . $i . '" style="font-size: 1.5rem; cursor: pointer;"></i>';
                            }
                            echo '<input type="hidden" name="q_' . $q['id'] . '" value="0">'; // Hidden input for selected rating
                            echo '</div>';
                            break;
                        default:
                            echo '<input type="text" class="form-control" name="q_' . $q['id'] . '" placeholder="Unsupported question type">';
                    }
                    ?>
                </div>
            <?php endforeach; ?>
            <div class="mb-3">
                <button type="submit" class="btn btn-success">Save</button>
                <button type="reset" class="btn btn-outline-secondary ms-2">Clear</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.rating-icons').forEach(container => {
                const iconType = container.dataset.icon;
                const icons = container.querySelectorAll('i');
                const hiddenInput = container.querySelector('input[type="hidden"]');

                icons.forEach((icon, index) => {
                    icon.addEventListener('click', () => {
                        const selectedValue = index + 1;
                        hiddenInput.value = selectedValue;

                        icons.forEach((el, i) => {
                            el.className = 'bi'; // Reset classes
                            if (i < selectedValue) {
                                el.classList.add('bi-' + iconType + '-fill', 'text-warning');
                            } else {
                                el.classList.add('bi-' + iconType);
                                el.classList.remove('text-warning'); // Ensure unselected are not warning colored
                            }
                            el.style.fontSize = '1.5rem';
                            el.style.cursor = 'pointer';
                        });
                    });

                    // Optional: Add hover effects
                    icon.addEventListener('mouseover', () => {
                        const hoverValue = parseInt(icon.dataset.value);
                        icons.forEach((el, i) => {
                            if (i < hoverValue) {
                                el.classList.add('text-warning');
                            } else {
                                el.classList.remove('text-warning');
                            }
                        });
                    });

                    icon.addEventListener('mouseout', () => {
                        const currentValue = parseInt(hiddenInput.value);
                        icons.forEach((el, i) => {
                            if (i < currentValue) {
                                el.classList.add('text-warning');
                            } else {
                                el.classList.remove('text-warning');
                            }
                        });
                    });
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
EOD;

        // Write the content to the file
        if (file_put_contents($formFilePath, $formContent) === false) {
            throw new Exception("Failed to write form file: " . $formFilePath);
        }

        // Save questions and options to database
        foreach ($questions as $index => $questionText) {
            $questionText = trim($questionText);
            if (empty($questionText)) continue;

            $questionType = $types[$index] ?? null;

            $stmt = $conn->prepare("INSERT INTO questions (form_id, question_text, question_type) VALUES (?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare statement failed (Question): " . implode(' | ', $conn->errorInfo()));
            }

            $stmt->execute([$form_id, $questionText, $questionType]);
            $questionId = $conn->lastInsertId();

            if (in_array($questionType, ['checkbox', 'radio', 'dropdown'])) {
                $questionNumber = $index + 1;
                if (isset($options[$questionNumber])) {
                    foreach ($options[$questionNumber] as $optionText) {
                        $stmt = $conn->prepare("INSERT INTO options (question_id, option_text) VALUES (?, ?)");
                        $stmt->execute([$questionId, $optionText]);
                    }
                }
            }
        }

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
