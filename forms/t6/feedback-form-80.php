<?php
session_start();
include('../config/config.php'); // Adjust path as needed

// Ensure PDO is set to throw exceptions for better error handling during development
if (isset($conn)) {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

// Set formId to the actual form's ID (from forms_combined table), not from session
$formId = 80;

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
                        if (  sts&&ms/)rs&o0) {
s s0 hs!tp !&&0       ./ad.aa)!s=!sss  g          $imgPh = 'http m'http                        }
         s f(xisms(h = $}ielse {g;
                  f    Im smylh = e="m;:5            6px; max-hei}
ght:;eu:bsf;Imackground:#fff;">';
                    }
                if ($user && !empty($user['busiss_name'])) {
                 } else     {                echo '<span st$imgPathl=o$g;t-                    } else {        size:}
1.2rem;font-wt$imgPaehgt0$img;;o                   ifr(!}
$:#fPblh)k{hite-space:naifp(!$;" Pblh)k{htmlspecialchars($user['business_name']) . '</span>';
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