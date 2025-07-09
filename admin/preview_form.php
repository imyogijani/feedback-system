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
    SELECT f.*, u.business_name, u.business_type, u.profile_image
    FROM forms f
    LEFT JOIN users u ON f.created_for = u.id
    WHERE f.id = :id
");
$stmt->execute([':id' => $form_id]);
$form = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$form) {
    echo "<p>Form not found. <a href='dashboard.php'>Back to Dashboard</a></p>";
    exit();
}

// Fetch questions
$stmt = $conn->prepare("
    SELECT id, question_text, question_type
    FROM questions
    WHERE form_id = :form_id
    ORDER BY id ASC
");
$stmt->execute([':form_id' => $form_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                // Fetch all options for these questions in one query
                $questionIds = array_column($questions, 'id');
                $optionsByQ = [];
                if (!empty($questionIds)) {
                    $in = str_repeat('?,', count($questionIds) - 1) . '?';
                    $optStmt = $conn->prepare("SELECT question_id, option_text FROM options WHERE question_id IN ($in) ORDER BY id ASC");
                    $optStmt->execute($questionIds);
                    while ($row = $optStmt->fetch(PDO::FETCH_ASSOC)) {
                        $optionsByQ[$row['question_id']][] = $row['option_text'];
                    }
                }
                foreach ($questions as $index => $q):
            ?>
                <div class="question">
                    <div class="question-title"><?= ($index + 1) . '. ' . htmlspecialchars($q['question_text']) ?></div>
                    <div>
                        <?php
                        $opts = isset($optionsByQ[$q['id']]) ? $optionsByQ[$q['id']] : [];
                        if ($q['question_type'] === 'radio' || $q['question_type'] === 'multiple_choice') {
                            if ($opts) {
                                foreach ($opts as $opt) {
                                    echo "<div class='option'><input type='radio'> " . htmlspecialchars($opt) . "</div>";
                                }
                            } else {
                                echo "<div class='option'><input type='radio'> Option 1</div>";
                            }
                        } elseif ($q['question_type'] === 'checkbox') {
                            if ($opts) {
                                foreach ($opts as $opt) {
                                    echo "<div class='option'><input type='checkbox'> " . htmlspecialchars($opt) . "</div>";
                                }
                            } else {
                                echo "<div class='option'><input type='checkbox'> Option 1</div>";
                            }
                        } elseif ($q['question_type'] === 'dropdown') {
                            echo "<select class='form-select' style='width: 100%; padding: 8px;'>";
                            echo "<option value=''>Select...</option>";
                            if ($opts) {
                                foreach ($opts as $opt) {
                                    echo "<option>" . htmlspecialchars($opt) . "</option>";
                                }
                            } else {
                                echo "<option>No options available</option>";
                            }
                            echo "</select>";
                        } elseif ($q['question_type'] === 'date') {
                            echo "<input type='date' style='width: 100%; padding: 8px;'>";
                        } else {
                            echo "<input type='text' placeholder='Your answer' style='width: 100%; padding: 8px;'>";
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach;
            else:
            ?>
                <p>No questions available for this form.</p>
            <?php endif; ?>
            <a href="index.php" class="btn">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
