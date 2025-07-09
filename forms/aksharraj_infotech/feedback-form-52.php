<?php
session_start();
include('../config/config.php'); // Adjust path as needed

try {
    // Ensure PDO is set to throw exceptions for better error handling during development
    if (isset($conn)) {
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    $formId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? 52;

    // Basic validation for form ID
    if ($formId <= 0) {
        throw new Exception("Invalid Form ID provided.");
    }

    // Fetch form details
    $stmt = $conn->prepare("SELECT * FROM forms WHERE id = ?");
    $stmt->execute([$formId]);
    $form = $stmt->fetch(PDO::FETCH_ASSOC);

    // Handle case where form is not found
    if (!$form) {
        throw new Exception("Form not found.");
    }

    // Fetch user info if the form is associated with a user
    $user = null;
    if (!empty($form['created_for'])) {
        $stmt = $conn->prepare("SELECT business_name, profile_image FROM users WHERE id = ?");
        $stmt->execute([$form['created_for']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fetch questions associated with the form
    $stmt = $conn->prepare("SELECT * FROM questions WHERE form_id = ? ORDER BY id ASC");
    $stmt->execute([$formId]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // Log error and display user-friendly message
    error_log($e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'An error occurred. Please try again later.']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Feedback form for <?= htmlspecialchars($form['title'] ?? '') ?>">
    <title><?= htmlspecialchars($form['title'] ?? '') ?> Feedback Form</title>
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
        .header-banner {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #673ab7;
            color: #fff;
            border-radius: 8px;
            padding: 16px 18px 12px 18px;
            margin-bottom: 10px;
            min-height: 60px;
            gap: 14px;
        }
        .profile-image {
            max-width: 56px;
            max-height: 56px;
            border-radius: 8px;
            border: 1.5px solid #fff;
            background: #fff;
        }
        .business-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #fff;
            display: inline-block;
            white-space: nowrap;
        }
        .error-message {
            color: #dc3545;
            padding: 20px;
            text-align: center;
            font-size: 1.2rem;
            background: #f8d7da;
            border-radius: 4px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container mt-5 view-form">
        <?php if (isset($error)): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php else: ?>
            <div class="row mb-4 position-relative">
                <div class="col-md-12">
                    <div class="header-banner">
                        <?php
                        if ($user) {
                            $img = $user['profile_image'] ?? '';
                            $label = $user['business_name'] ?? '';
                            $imgPath = '';
                            
                            if ($img && !filter_var($img, FILTER_VALIDATE_URL) && strpos($img, '/') !== 0) {
                                $imgPath = '../assets/images/' . $img;
                            } else {
                                $imgPath = $img;
                            }
                            
                            if (!$imgPath || !is_file(__DIR__ . '/' . $imgPath)) {
                                $imgPath = 'https://ui-avatars.com/api/?name=' . urlencode($label) . '&background=cccccc&color=222222&size=100';
                            }
                            
                            echo '<img src="' . htmlspecialchars($imgPath) . '" alt="Profile Image" class="profile-image">';
                        }
                        
                        echo '<span class="business-name">' . 
                             htmlspecialchars($user['business_name'] ?? 'Feedback Form') . 
                             '</span>';
                        ?>
                    </div>
                </div>
            </div>

            <h2 class="text-center title"><?= htmlspecialchars($form['title']) ?></h2>
            <p><?= htmlspecialchars($form['description']) ?></p>

            <form method="POST" action="process_response.php" novalidate>
                <input type="hidden" name="form_id" value="<?= htmlspecialchars($formId) ?>">
                
                <?php if (!empty($form['firstname']) || !empty($form['lastname'])): ?>
                    <div class="row mb-3">
                        <?php if (!empty($form['firstname'])): ?>
                            <div class="col">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="firstname" 
                                       pattern="[A-Za-z\s]+" 
                                       title="Only letters and spaces allowed" 
                                       required>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($form['lastname'])): ?>
                            <div class="col">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="lastname" 
                                       pattern="[A-Za-z\s]+" 
                                       title="Only letters and spaces allowed" 
                                       required>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($form['email']) || !empty($form['number'])): ?>
                    <div class="row mb-3">
                        <?php if (!empty($form['email'])): ?>
                            <div class="col">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" 
                                       required>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($form['number'])): ?>
                            <div class="col">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="number"
                                       pattern="\d{10}"
                                       maxlength="10"
                                       title="Please enter exactly 10 digits"
                                       required
                                       oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)">
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php foreach ($questions as $q): ?>
                    <div class="mb-3">
                        <label class="form-label">
                            <?= htmlspecialchars($q['question_text']) ?>
                        </label>
                        <?php
                        try {
                            $stmt = $conn->prepare("SELECT * FROM options WHERE question_id = ? ORDER BY id ASC");
                            $stmt->execute([$q['id']]);
                            $options = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            switch ($q['question_type']) {
                                case 'text':
                                    echo '<input type="text" class="form-control" name="q_' . $q['id'] . '">';
                                    break;
                                case 'textarea':
                                    echo '<textarea class="form-control" name="q_' . $q['id'] . '" rows="3"></textarea>';
                                    break;
                                case 'radio':
                                    foreach ($options as $opt) {
                                        echo '<div class="form-check">
                                                <input class="form-check-input" type="radio" 
                                                       name="q_' . $q['id'] . '" 
                                                       id="q_' . $q['id'] . '_' . $opt['id'] . '"
                                                       value="' . htmlspecialchars($opt['option_text']) . '">
                                                <label class="form-check-label" for="q_' . $q['id'] . '_' . $opt['id'] . '">
                                                    ' . htmlspecialchars($opt['option_text']) . '
                                                </label>
                                              </div>';
                                    }
                                    break;
                                case 'checkbox':
                                    foreach ($options as $opt) {
                                        echo '<div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="q_' . $q['id'] . '[]" 
                                                       id="q_' . $q['id'] . '_' . $opt['id'] . '"
                                                       value="' . htmlspecialchars($opt['option_text']) . '">
                                                <label class="form-check-label" for="q_' . $q['id'] . '_' . $opt['id'] . '">
                                                    ' . htmlspecialchars($opt['option_text']) . '
                                                </label>
                                              </div>';
                                    }
                                    break;
                                case 'dropdown':
                                    echo '<select class="form-select" name="q_' . $q['id'] . '">
                                            <option value="">Select an option...</option>';
                                    foreach ($options as $opt) {
                                        echo '<option value="' . htmlspecialchars($opt['option_text']) . '">' 
                                             . htmlspecialchars($opt['option_text']) . '</option>';
                                    }
                                    echo '</select>';
                                    break;
                                case 'date':
                                    echo '<input type="date" class="form-control" name="q_' . $q['id'] . '">';
                                    break;
                                case 'rating_star':
                                case 'rating_heart':
                                case 'rating_thumb':
                                    $icon = $q['question_type'] === 'rating_star' ? 'star' : 
                                           ($q['question_type'] === 'rating_heart' ? 'heart' : 'hand-thumbs-up');
                                    
                                    echo '<div class="rating-icons" data-question-id="' . $q['id'] . '" data-icon="' . $icon . '">';
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo '<i class="bi bi-' . $icon . '" 
                                                 data-value="' . $i . '" 
                                                 style="font-size: 1.5rem; cursor: pointer;"
                                                 role="button"
                                                 aria-label="Rating ' . $i . ' out of 5"></i>';
                                    }
                                    echo '<input type="hidden" name="q_' . $q['id'] . '" value="0">';
                                    echo '</div>';
                                    break;
                                default:
                                    echo '<input type="text" class="form-control" name="q_' . $q['id'] . '" 
                                               placeholder="Unsupported question type">';
                            }
                        } catch (Exception $e) {
                            error_log($e->getMessage());
                            echo '<div class="alert alert-danger">Error loading question options</div>';
                        }
                        ?>
                    </div>
                <?php endforeach; ?>

                <div class="mb-3">
                    <button type="submit" class="btn btn-success">Submit Feedback</button>
                    <button type="reset" class="btn btn-outline-secondary ms-2">Clear Form</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.rating-icons').forEach(container => {
                const iconType = container.dataset.icon;
                const icons = container.querySelectorAll('i');
                const hiddenInput = container.querySelector('input[type="hidden"]');

                const updateIcons = (selectedValue, isHover = false) => {
                    icons.forEach((el, i) => {
                        el.className = 'bi';
                        if (i < selectedValue) {
                            el.classList.add(`bi-${iconType}-fill`, 'text-warning');
                        } else {
                            el.classList.add(`bi-${iconType}`);
                        }
                    });
                };

                icons.forEach((icon, index) => {
                    icon.addEventListener('click', () => {
                        const value = index + 1;
                        hiddenInput.value = value;
                        updateIcons(value);
                    });

                    icon.addEventListener('mouseover', () => {
                        updateIcons(index + 1, true);
                    });

                    icon.addEventListener('mouseout', () => {
                        updateIcons(parseInt(hiddenInput.value) || 0);
                    });

                    // Keyboard accessibility
                    icon.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            icon.click();
                        }
                    });
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>