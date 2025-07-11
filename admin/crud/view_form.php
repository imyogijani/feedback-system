<?php
session_start();
include('../config/config.php'); // Adjust path as needed
include('../assets/inc/incHeader.php'); // Adjust path as needed, typically for HTML head/nav

// Ensure PDO is set to throw exceptions for better error handling during development
if (isset($conn)) {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

$formId = $_GET['id'] ?? 0;

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
    <style>
        .container.view-form {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin-top: 3rem !important;
            margin-bottom: 3rem !important;
        }

        .form-control:focus,
        .form-select:focus,
        .btn:focus {
            box-shadow: 0 0 0 0.25rem rgba(103, 58, 183, 0.25);
            border-color: #673ab7;
        }

        .rating-icons i {
            transition: transform 0.2s ease-in-out, color 0.2s ease-in-out;
        }

        .rating-icons i:hover {
            transform: scale(1.2);
        }

        .rating-icons i.bi-star-fill,
        .rating-icons i.bi-heart-fill,
        .rating-icons i.bi-hand-thumbs-up-fill {
            color: #ffc107; /* Bootstrap warning color for filled icons */
        }

        .btn-success {
            background-color: #673ab7;
            border-color: #673ab7;
            transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out;
        }

        .btn-success:hover {
            background-color: #5e35b1;
            border-color: #5e35b1;
        }

        .btn-outline-secondary {
            color: #6c757d;
            border-color: #6c757d;
            transition: all 0.2s ease-in-out;
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: #fff;
        }

        @media (max-width: 768px) {
            .container.view-form {
                padding: 20px; /* Adjusted padding for smaller screens */
                margin-top: 1.5rem !important;
                margin-bottom: 1.5rem !important;
            }
            
            .row > .col {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 1rem;
            }
            
            .rating-icons i {
                font-size: 2rem !important; /* Adjusted font size for better fit */
                margin: 0 8px; /* Adjusted margin */
            }
            
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .btn.ms-2 {
                margin-left: 0 !important;
            }
            
            .form-check {
                margin-left: 0;
            }
        }
        
        @media (max-width: 576px) {
            .container.view-form {
                padding: 15px; /* Further adjusted padding for very small screens */
            }
            
            h2.title {
                font-size: 2rem; /* Adjusted font size for title */
            }
            
            .header-banner {
                flex-direction: column;
                text-align: center;
                padding: 15px !important; /* Adjusted padding */
                gap: 8px; /* Reduced gap */
            }
            
            .header-banner img {
                margin-bottom: 5px; /* Adjusted margin */
                max-width: 48px; /* Slightly smaller image */
                max-height: 48px;
            }
            
            .header-banner span {
                font-size: 1rem; /* Adjusted font size for business name */
            }
            
            .rating-icons i {
                font-size: 2.2rem !important; /* Adjusted font size for very small screens */
            }
        }
    </style>
</head>
<body>
    <div class="container mt-5 view-form">
        <div class="row mb-4 position-relative">
            <div class="col-12">
                <div class="header-banner" style="display: flex; align-items: center; justify-content: center; background: #673ab7; color: #fff; border-radius: 8px; padding: 16px 18px 12px 18px; margin-bottom: 10px; min-height: 60px; gap: 14px;">
                    <?php
                    if ($user) {
                        $img = $user['profile_image'] ?? '';
                        $label = $user['business_name'] ?? '';
                        $imgPath = '';
                        if ($img && strpos($img, 'http') !== 0 && strpos($img, '/') !== 0) {
                            $imgPath = '../assets/images/' . $img;
                        } else {
                            $imgPath = $img;
                        }
                        if (!$imgPath || !file_exists(__DIR__ . '/' . $imgPath)) {
                            $imgPath = 'https://ui-avatars.com/api/?name=' . urlencode($label) . '&background=cccccc&color=222222&size=100';
                        }
                        echo '<img src="' . htmlspecialchars($imgPath) . '" alt="Profile Image" style="max-width:56px; max-height:56px; border-radius:8px; border:1.5px solid #fff; background:#fff;">';
                    }
                    if ($user && !empty($user['business_name'])) {
                        echo '<span style="font-size:1.2rem;font-weight:600; color:#fff; display:inline-block; word-break: break-word;">' . htmlspecialchars($user['business_name']) . '</span>';
                    } else {
                        echo '<span style="font-size:1.2rem;font-weight:600; color:#fff; display:inline-block;">Feedback Form</span>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <h2 class="text-center title"><?= htmlspecialchars($form['title']) ?></h2>
        <p class="text-break"><?= htmlspecialchars($form['description']) ?></p>
        <form method="POST" action="process_response.php" class="needs-validation" novalidate>
            <input type="hidden" name="form_id" value="<?= htmlspecialchars($formId) ?>">

            <?php if (!empty($form['firstname']) || !empty($form['lastname'])): ?>
                <div class="row mb-3">
                    <?php if (!empty($form['firstname'])): ?>
                        <div class="col-12 col-md-6 mb-3 mb-md-0">
                            <label class="form-label" style="font-weight: 500;">First Name</label>
                            <input type="text" class="form-control" name="firstname" pattern="[A-Za-z\s]+" title="Only letters allowed" required>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($form['lastname'])): ?>
                        <div class="col-12 col-md-6">
                            <label class="form-label" style="font-weight: 500;">Last Name</label>
                            <input type="text" class="form-control" name="lastname" pattern="[A-Za-z\s]+" title="Only letters allowed" required>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($form['email']) || !empty($form['number'])): ?>
                <div class="row mb-3">
                    <?php if (!empty($form['email'])): ?>
                        <div class="col-12 col-md-6 mb-3 mb-md-0">
                            <label class="form-label" style="font-weight: 500;">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($form['number'])): ?>
                        <div class="col-12 col-md-6">
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
                <div class="mb-4">
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
                            echo '<textarea class="form-control" name="q_' . $q['id'] . '" rows="3"></textarea>';
                            break;
                        case 'radio':
                            echo '<div class="d-flex flex-column gap-2">';
                            foreach ($options as $opt) {
                                echo '<div class="form-check">
                                        <input class="form-check-input" type="radio" name="q_' . $q['id'] . '" value="' . htmlspecialchars($opt['option_text']) . '">
                                        <label class="form-check-label">' . htmlspecialchars($opt['option_text']) . '</label>
                                      </div>';
                            }
                            echo '</div>';
                            break;
                        case 'checkbox':
                            echo '<div class="d-flex flex-column gap-2">';
                            foreach ($options as $opt) {
                                echo '<div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="q_' . $q['id'] . '[]" value="' . htmlspecialchars($opt['option_text']) . '">
                                        <label class="form-check-label">' . htmlspecialchars($opt['option_text']) . '</label>
                                      </div>';
                            }
                            echo '</div>';
                            break;
                        case 'dropdown':
                            echo '<select class="form-select" name="q_' . $q['id'] . '">';
                            echo '<option value="">Select...</option>';
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
                            echo '<div class="rating-icons d-flex justify-content-center gap-3 py-2" data-question-id="' . $q['id'] . '" data-icon="' . $icon . '">';
                            for ($i = 1; $i <= 5; $i++) {
                                echo '<i class="bi bi-' . $icon . '" data-value="' . $i . '" style="font-size: 1.5rem; cursor: pointer;"></i>';
                            }
                            echo '<input type="hidden" name="q_' . $q['id'] . '" value="0">';
                            echo '</div>';
                            break;
                        default:
                            echo '<input type="text" class="form-control" name="q_' . $q['id'] . '" placeholder="Unsupported question type">';
                    }
                    ?>
                </div>
            <?php endforeach; ?>
            <div class="mb-3 d-grid gap-2 d-md-flex justify-content-md-start">
                <button type="submit" class="btn btn-success">Save</button>
                <button type="reset" class="btn btn-outline-secondary">Clear</button>
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
                            el.className = 'bi';
                            if (i < selectedValue) {
                                el.classList.add('bi-' + iconType + '-fill');
                            } else {
                                el.classList.add('bi-' + iconType);
                            }
                            el.style.fontSize = '1.5rem';
                            el.style.cursor = 'pointer';
                        });
                    });

                    icon.addEventListener('mouseover', () => {
                        const hoverValue = parseInt(icon.dataset.value);
                        icons.forEach((el, i) => {
                            if (i < hoverValue) {
                                el.classList.add('bi-' + iconType + '-fill');
                            } else {
                                el.classList.remove('bi-' + iconType + '-fill');
                            }
                        });
                    });

                    icon.addEventListener('mouseout', () => {
                        const currentValue = parseInt(hiddenInput.value);
                        icons.forEach((el, i) => {
                            if (i < currentValue) {
                                el.classList.add('bi-' + iconType + '-fill');
                            } else {
                                el.classList.remove('bi-' + iconType + '-fill');
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