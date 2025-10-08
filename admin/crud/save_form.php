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
    // var_dump($_POST['form_type']);
    if ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2) {
         $created_for = $_POST['created_for'] ?? null;
    } else {
       
        $created_for = $_SESSION['user_id'] ?? null;
    }
    
    // $created_for = $_POST['created_for'] ?? null;
    $company_name = $_POST['company_name'] ?? '';
    $company_logo = '';
    $thankyou_message = $_POST['thankyou_message'] ?? '';
    $allow_another_response = isset($_POST['allow_another_response']) ? 1 : 0;


    

    if ($created_for === null || $created_for === '') {
        // If no existing business is selected, create a new user entry or use existing
       if (!empty($company_name)) {
        // Check if company already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$company_name]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            $created_for = $existingUser['id'];
        } else {
            $sanitized_company_name = strtolower(str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9_ ]/', '', $company_name)));
            $email = $sanitized_company_name . '@gmail.com';

            // Insert new user (company) into the users table without profile_image first
            $stmt = $conn->prepare("INSERT INTO users (username, password, business_name, role_id, email) VALUES (?, '123456', ?, ?, ?)");
            $stmt->execute([$company_name, $company_name, 3, $email]);
            $created_for = $conn->lastInsertId();
        }

        // Handle company logo upload for both new and existing users
        if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION);
            $safe_name = 'company_' . $created_for . '.' . $ext; // Use created_for ID for unique name
            $upload_dir = '../assets/images/';


            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $upload_dir . $safe_name)) {
                $company_logo = $safe_name;
                // Update the user record with the profile image
                $update_stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                if (!$update_stmt->execute([$company_logo, $created_for])) {
                    error_log("ERROR: Failed to update user profile_image for ID: " . $created_for . ". Error Info: " . json_encode($update_stmt->errorInfo()));
                }
            } else {
                error_log("Logo upload failed: " . $_FILES['company_logo']['tmp_name'] . " to " . $upload_dir . $safe_name);
            }
        } else {
            error_log("No company logo uploaded or an error occurred during upload. Error code: " . ($_FILES['company_logo']['error'] ?? 'N/A'));
        }
    } else {
        $created_for = 1;
    }
    }

    // Always use the selected or submitted company name for folder creation
    if ($created_for !== null && $created_for !== '' && empty($company_name)) {
        // If a business is selected (not new), fetch its business_name from DB
        $stmt = $conn->prepare("SELECT business_name FROM users WHERE id = ?");
        $stmt->execute([$created_for]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $business_name = $user ? $user['business_name'] : '';
    } else {
        $business_name = $company_name;
    }
    // Sanitize business name consistently
    $sanitized_business_name = preg_replace('/[^a-zA-Z0-9_ -]/', '', $business_name);
    $sanitized_business_name = str_replace(' ', '_', $sanitized_business_name);
    $sanitized_business_name = strtolower($sanitized_business_name);

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
        $stmt = $conn->prepare("INSERT INTO forms_combined (title, description, form_type, created_by, created_for, firstname, lastname, email, number, questions_json, thankyou_message, allow_another_response) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $form_type, $created_by, $created_for, $firstname, $lastname, $email, $number,$questions_json, $thankyou_message, $allow_another_response]);
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

// Set formId to the actual form's ID (from forms_combined table), not from session
$formId = {FORM_ID_PLACEHOLDER};

// Basic validation for form ID
if ($formId <= 0) {
    echo "Invalid Form ID provided.";
    exit();
}

// Fetch form details
$stmt = $conn->prepare("SELECT * FROM forms_combined WHERE id = ?");
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

// Fetch sectioned questions from questions_json
$questions = [];
if (!empty($form['questions_json'])) {
    $questions = json_decode($form['questions_json'], true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($questions)) {
        $questions = [];
    }
}
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
        <form method="POST" action="../process_response.php">
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

            <?php if (!empty($questions)): ?>
                <?php foreach ($questions as $section): ?>
                    <div class="mb-4 p-3" style="background:#f6f6fa; border-radius:8px; border:1.5px solid #e0e0e0;">
                        <div style="font-weight:bold; color:#673ab7; font-size:1.1rem; margin-bottom:10px;">
                            <?= htmlspecialchars($section['section_title'] ?? '') ?>
                        </div>
                        <?php if (!empty($section['questions']) && is_array($section['questions'])): ?>
                            <?php foreach ($section['questions'] as $qidx => $q): ?>
                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 500;">
                                        <?= htmlspecialchars($q['text'] ?? '') ?>
                                    </label>
                                    <?php
                                    $qType = strtolower($q['type'] ?? 'text');
                                    $opts = [];
                                    if (isset($q['options']) && is_array($q['options'])) {
                                        foreach ($q['options'] as $opt) {
                                            if (is_string($opt)) {
                                                $opts[] = $opt;
                                            } elseif (is_array($opt) && isset($opt['label'])) {
                                                $opts[] = $opt['label'];
                                            }
                                        }
                                    }
                                    $qName = 'q_' . $section['section_id'] . '_' . $qidx;
                                    switch ($qType) {
                                        case 'text':
                                            echo '<input type="text" class="form-control" name="' . $qName . '">';
                                            break;
                                        case 'textarea':
                                            echo '<textarea class="form-control" name="' . $qName . '"></textarea>';
                                            break;
                                        case 'radio':
                                            foreach ($opts as $opt) {
                                                echo '<div class="form-check">'
                                                    . '<input class="form-check-input" type="radio" name="' . $qName . '" value="' . htmlspecialchars($opt) . '">' 
                                                    . '<label class="form-check-label">' . htmlspecialchars($opt) . '</label>'
                                                    . '</div>';
                                            }
                                            break;
                                        case 'checkbox':
                                            foreach ($opts as $opt) {
                                                echo '<div class="form-check">'
                                                    . '<input class="form-check-input" type="checkbox" name="' . $qName . '[]" value="' . htmlspecialchars($opt) . '">' 
                                                    . '<label class="form-check-label">' . htmlspecialchars($opt) . '</label>'
                                                    . '</div>';
                                            }
                                            break;
                                        case 'dropdown':
                                            echo '<select class="form-select" name="' . $qName . '">';
                                            echo '<option value="">Select...</option>';
                                            foreach ($opts as $opt) {
                                                echo '<option value="' . htmlspecialchars($opt) . '">' . htmlspecialchars($opt) . '</option>';
                                            }
                                            echo '</select>';
                                            break;
                                        case 'date':
                                            echo '<input type="date" class="form-control" name="' . $qName . '">';
                                            break;
                                        case 'rating_star':
                                        case 'rating_heart':
                                        case 'rating_thumb':
                                            $icon = $qType === 'rating_star' ? 'star' : ($qType === 'rating_heart' ? 'heart' : 'hand-thumbs-up');
                                            echo '<div class="rating-icons" data-question-id="' . $qName . '" data-icon="' . $icon . '">';
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo '<i class="bi bi-' . $icon . '" data-value="' . $i . '" style="font-size: 1.5rem; cursor: pointer;"></i>';
                                            }
                                            echo '<input type="hidden" name="' . $qName . '" value="0">';
                                            echo '</div>';
                                            break;
                                        default:
                                            echo '<input type="text" class="form-control" name="' . $qName . '" placeholder="Unsupported question type">';
                                    }
                                    ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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

        // Replace placeholder with actual form_id before writing to file
        $formContentFinal = str_replace('{FORM_ID_PLACEHOLDER}', $form_id, $formContent);
        if (file_put_contents($formFilePath, $formContentFinal) === false) {
            throw new Exception("Failed to write form file: " . $formFilePath);
        }

        // Save questions and options as JSON in the form row, grouped by section
        $section_titles = $_POST['section_titles'] ?? [];
        $questionsArr = [];
        $section_questions_map = [];
        // Build a map of section_id => array of questions indexes
        if (isset($_POST['question_section_ids'])) {
            // If frontend passes section ids for each question
            $section_questions_map = [];
            foreach ($_POST['question_section_ids'] as $qidx => $section_id) {
                $section_id = intval($section_id);
                if (!isset($section_questions_map[$section_id])) $section_questions_map[$section_id] = [];
                $section_questions_map[$section_id][] = $qidx;
            }
        } else {
            // Fallback: assign questions sequentially (old logic)
            $section_questions_map = [];
            $qidx = 0;
            $questions_per_section = ceil(count($questions) / max(1, count($section_titles)));
            $current_section = 1;
            foreach ($questions as $qidx => $qtext) {
                if (!isset($section_questions_map[$current_section])) $section_questions_map[$current_section] = [];
                $section_questions_map[$current_section][] = $qidx;
                if (count($section_questions_map[$current_section]) >= $questions_per_section && $current_section < count($section_titles)) {
                    $current_section++;
                }
            }
        }

        foreach ($section_titles as $sectionId => $sectionTitle) {
            $sectionKey = $sectionId + 1;
            $section = [
                'section_id' => $sectionKey,
                'section_title' => trim($sectionTitle),
                'questions' => []
            ];
            if (!empty($section_questions_map[$sectionKey])) {
                $qNumInSection = 1;
                foreach ($section_questions_map[$sectionKey] as $questionIndex) {
                    $questionText = trim($questions[$questionIndex]);
                    if (empty($questionText)) continue;
                    $questionType = $types[$questionIndex] ?? null;
                    $question = [
                        'text' => $questionText,
                        'type' => $questionType
                    ];
                    $optionsArr = [];
                    // Calculate the correct global index for this question (matches frontend order)
                    $globalIndex = 0;
                    $found = false;
                    foreach ($section_titles as $secIdx => $secTitle) {
                        $secKey = $secIdx + 1;
                        if (!empty($section_questions_map[$secKey])) {
                            foreach ($section_questions_map[$secKey] as $qIdxInSec) {
                                if ($secKey == $sectionKey && $qIdxInSec == $questionIndex) {
                                    $found = true;
                                    break;
                                }
                                $globalIndex++;
                            }
                            if ($found) break;
                        }
                    }
                    if (in_array($questionType, ['checkbox', 'radio', 'dropdown'])) {
                        // Fix: Some browsers may not submit empty option fields, so check for both numeric and string keys
                        $optArr = [];
                        if (isset($options[$globalIndex])) {
                            $optArr = is_array($options[$globalIndex]) ? $options[$globalIndex] : [];
                        } else if (isset($options[(string)$globalIndex])) {
                            $optArr = is_array($options[(string)$globalIndex]) ? $options[(string)$globalIndex] : [];
                        }
                        foreach ($optArr as $optionText) {
                            $optionText = trim($optionText);
                            if ($optionText === '') continue;
                            $optionsArr[] = [ 'label' => $optionText ];
                        }
                        if ($questionType === 'radio') {
                            $radioArr = [];
                            if (isset($radio_options[$globalIndex])) {
                                $radioArr = is_array($radio_options[$globalIndex]) ? $radio_options[$globalIndex] : [];
                            } else if (isset($radio_options[(string)$globalIndex])) {
                                $radioArr = is_array($radio_options[(string)$globalIndex]) ? $radio_options[(string)$globalIndex] : [];
                            }
                            foreach ($radioArr as $optionText) {
                                $optionText = trim($optionText);
                                if ($optionText === '') continue;
                                $optionsArr[] = [ 'label' => $optionText ];
                            }
                        }
                        $question['options'] = $optionsArr;
                    } else {
                        $question['options'] = [];
                    }
                    $section['questions'][] = $question;
                    $qNumInSection++;
                }
            }
            $questionsArr[] = $section;
        }
        echo '<pre>';
        // var_dump($optionsArr); // Debugging line to check options
                        // exit; // Uncomment this line to stop execution for debugging
        // var_dump($questionsArr); // Debugging line to check options
                        // exit;
        $questionsJson = json_encode($questionsArr, JSON_UNESCAPED_UNICODE);
        // Update the form row with questions_json
        $stmt = $conn->prepare("UPDATE forms_combined SET questions_json = ?, thankyou_message = ? WHERE id = ?");
        $stmt->execute([$questionsJson, $thankyou_message, $form_id]);

        $conn->commit();
        $_SESSION['success'] = "Feedback form created successfully.";
        if ($_SESSION['role_id'] == 3 ) {
         header("Location: ../user_dashboard.php"); 
        } 
        elseif ($_SESSION['role_id'] == 2) {
            header("Location: ../moderator_dashboard.php"); 
        }
        else {
            header("Location: ../forms_lists.php"); 
        }
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
