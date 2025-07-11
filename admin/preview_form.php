<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit();
}

include('config/config.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>Invalid form ID. <a href='dashboard.php'>Back to Dashboard</a></p>";
    exit();
}

$form_id = intval($_GET['id']);


// Fetch form + created_for user info (business_name, profile_image)
$stmt = $conn->prepare("
    SELECT f.*, u.business_name, u.business_type, u.profile_image,f.questions_json AS questions
    FROM forms_combined f
    LEFT JOIN users u ON f.created_for = u.id
    WHERE f.id = :id
");
$stmt->execute([':id' => $form_id]);
$form = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$form) {
    echo "<p>Form not found. <a href='dashboard.php'>Back to Dashboard</a></p>";
    exit();
}

// Decode questions JSON (sectioned format) from forms_combined
$questions = [];
if (isset($form['questions']) && !empty($form['questions'])) {
    $questions = json_decode($form['questions'], true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($questions)) {
        $questions = [];
    }
}

// Determine logo path
$logoPath = (!empty($form['profile_image']) && file_exists("uploads/profile_images/" . $form['profile_image']))
    ? "uploads/profile_images/" . $form['profile_image']
    : null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Preview - <?= htmlspecialchars($form['title']) ?></title>
    <style>
        body {
            background: #f0f2f5;
            font-family: Roboto, sans-serif;
            display: flex;
            justify-content: center;
            padding: 30px;
        }
        .preview-container {
            background: white;
            width: 600px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .preview-header {
            background: #673ab7;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .preview-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .preview-header p {
            margin: 5px 0 0;
            font-size: 14px;
        }
        .business-info {
            margin-top: 10px;
            font-size: 13px;
            color: #ddd;
        }
        .business-logo {
            max-height: 80px;
            margin: 10px auto;
            display: block;
            border-radius: 5px;
            background: white;
            padding: 5px;
        }
        .preview-content {
            padding: 20px;
        }
        .question {
            margin-bottom: 20px;
        }
        .question-title {
            font-weight: 500;
            margin-bottom: 8px;
        }
        .option {
            margin: 5px 0;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background: #673ab7;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover {
            background: #5e35b1;
        }
        @media (max-width: 650px) {
            .preview-container {
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <div class="preview-header">
            <?php
            // Show created_for user's business profile image if available, else fallback
            $profileImg = (!empty($form['profile_image']) && file_exists("assets/images/" . $form['profile_image']))
                ? "assets/images/" . $form['profile_image']
                : (!empty($form['business_name']) ? 'https://ui-avatars.com/api/?name=' . urlencode($form['business_name']) . '&background=cccccc&color=222222&size=100' : null);
            ?>
            <div style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 8px;">
                <?php if ($profileImg): ?>
                    <img src="<?= htmlspecialchars($profileImg) ?>" alt="Business Logo" class="business-logo" style="margin:0; max-height:50px; max-width:50px;" >
                <?php endif; ?>
                <?php if (!empty($form['business_name'])): ?>
                    <span style="font-size: 16px; color: #fff; font-weight: 500;"><?= htmlspecialchars($form['business_name']) ?></span>
                <?php endif; ?>
                <?php if (!empty($form['business_type'])): ?>
                    <!-- <span style="font-size: 13px; color: #ddd; margin-left: 4px;">(<?= htmlspecialchars($form['business_type']) ?>)</span> -->
                <?php endif; ?>
            </div>
            <h1><?= htmlspecialchars($form['title']) ?></h1>
            <p><?= nl2br(htmlspecialchars($form['description'])) ?></p>
        </div>
        <div class="preview-content">
            <?php
            if ($questions):
                foreach ($questions as $section):
                    $sectionTitle = isset($section['section_title']) ? $section['section_title'] : '';
            ?>
                <div class="section-outline" style="border:2px solid #b3b3b3; border-radius:10px; margin-bottom:28px; margin-top:28px; box-shadow:0 2px 12px rgba(0,0,0,0.06); background:#fafaff; padding:18px 18px 10px 18px;">
                    <div style="margin-bottom:18px; font-weight:bold; font-size:18px; color:#673ab7; border-bottom:1.5px solid #e0e0e0; padding-bottom:6px; letter-spacing:0.5px;">
                        <?= htmlspecialchars($sectionTitle) ?>
                    </div>
                    <?php
                    if (!empty($section['questions']) && is_array($section['questions'])) {
                        $sectionQSerial = 1;
                        foreach ($section['questions'] as $q) {
                            $qText = isset($q['text']) ? $q['text'] : (isset($q['question']) ? $q['question'] : (isset($q['question_text']) ? $q['question_text'] : ''));
                            $qType = isset($q['type']) ? strtolower($q['type']) : (isset($q['question_type']) ? strtolower($q['question_type']) : 'text');
                        // Robustly decode and flatten options (handles array of JSON strings, double-encoded, etc.)
                        $opts = [];
                        if (isset($q['options'])) {
                            if (is_array($q['options'])) {
                                foreach ($q['options'] as $opt) {
                                    if (is_string($opt)) {
                                        $decoded = json_decode($opt, true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                            // If option is a JSON array, merge its values
                                            foreach ($decoded as $dopt) {
                                                if (is_string($dopt)) $opts[] = $dopt;
                                            }
                                        } else {
                                            $opts[] = $opt;
                                        }
                                    } elseif (is_array($opt)) {
                                        foreach ($opt as $dopt) {
                                            if (is_string($dopt)) $opts[] = $dopt;
                                        }
                                    }
                                }
                            } elseif (is_string($q['options'])) {
                                $decodedOpts = json_decode($q['options'], true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedOpts)) {
                                    foreach ($decodedOpts as $dopt) {
                                        if (is_string($dopt)) $opts[] = $dopt;
                                    }
                                } else {
                                    $opts[] = $q['options'];
                                }
                            }
                        }
                    ?>
                        <div class="question">
                            <div class="question-title"><?= ($sectionQSerial++) . '. ' . htmlspecialchars($qText) ?></div>
                            <div>
                                <?php
                                // Normalize question type for consistent handling
                                switch ($qType) {
                                    case 'radio':
                                    case 'multiple_choice':
                                    case 'multiple choice':
                                        if (!empty($opts)) {
                                            $hasOption = false;
                                            foreach ($opts as $opt) {
                                                if (is_string($opt) && trim($opt) !== '') {
                                                    echo "<div class='option'><input type='radio' > " . htmlspecialchars($opt) . "</div>";
                                                    $hasOption = true;
                                                }
                                            }
                                            if (!$hasOption) {
                                                echo "<div class='option'><input type='radio' > No options available</div>";
                                            }
                                        } else {
                                            echo "<div class='option'><input type='radio' > No options available</div>";
                                        }
                                        break;
                                    case 'checkbox':
                                    case 'checkboxes':
                                        if (!empty($opts)) {
                                            $hasOption = false;
                                            foreach ($opts as $opt) {
                                                if (is_string($opt) && trim($opt) !== '') {
                                                    echo "<div class='option'><input type='checkbox' > " . htmlspecialchars($opt) . "</div>";
                                                    $hasOption = true;
                                                }
                                            }
                                            if (!$hasOption) {
                                                echo "<div class='option'><input type='checkbox' > No options available</div>";
                                            }
                                        } else {
                                            echo "<div class='option'><input type='checkbox' > No options available</div>";
                                        }
                                        break;
                                    case 'dropdown':
                                    case 'select':
                                        echo "<select class='form-select' style='width: 100%; padding: 8px;' >";
                                        echo "<option value=''>Select...</option>";
                                        $hasOption = false;
                                        if (!empty($opts)) {
                                            foreach ($opts as $opt) {
                                                if (is_string($opt) && trim($opt) !== '') {
                                                    echo "<option>" . htmlspecialchars($opt) . "</option>";
                                                    $hasOption = true;
                                                }
                                            }
                                        }
                                        if (!$hasOption) {
                                            echo "<option>No options available</option>";
                                        }
                                        echo "</select>";
                                        break;
                                    case 'date':
                                        echo "<input type='date' style='width: 100%; padding: 8px;' >";
                                        break;
                                    default:
                                        echo "<input type='text' placeholder='Your answer' style='width: 100%; padding: 8px;' >";
                                        break;
                                }
                                ?>
                            </div>
                        </div>
                    <?php 
                        }
                    }
                    ?>
                </div>
            <?php endforeach;
            else:
            ?>
                <p>No questions available for this form.</p>
            <?php endif; ?>
            <a href="index.php" class="btn">Back to Dashboard</a>
        </div>
         <footer style="text-align:center; color:#fff; font-size:13px; margin-top:30px; padding:18px 0 8px 0; background: linear-gradient(90deg, #673ab7 0%, #512da8 100%); border-radius:0 0 8px 8px;">
        &copy; <?= date('Y') ?> Feedback System. All rights reserved.
    </footer>
    </div>
   
</body>
</html>
