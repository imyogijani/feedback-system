<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Feedback Form</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<?php
session_start();
include('config/config.php');

$businesses = [];
if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1) {
    $stmt = $conn->prepare("SELECT id, business_name FROM users WHERE role_id = 3 ORDER BY business_name");
    $stmt->execute();
    $businesses = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Feedback Form</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="container mt-5">

    <h3>Create Feedback Form</h3>

    <form method="post" action="save_form.php" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Form Title</label>
            <input type="text" class="form-control" name="title" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description"></textarea>
        </div>

        <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
            <div class="mb-3">
                <label class="form-label">Created For (Business)</label>
                <select class="form-select" name="created_for" id="created_for_select">
                    <option value="">-- Select Existing Business or Create New --</option>
                    <?php foreach ($businesses as $business): ?>
                        <option value="<?= htmlspecialchars($business['id']) ?>"><?= htmlspecialchars($business['business_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3" id="new_company_fields" style="display: none;">
                <label class="form-label">New Company Name</label>
                <input type="text" class="form-control mb-2" name="company_name" placeholder="Enter new company name">
                <label class="form-label">Company Logo</label>
                <input type="file" class="form-control" name="company_logo" accept="image/*">
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label class="form-label">Form Type</label>
            <select class="form-select" name="form_type" required>
                <option value="">Select Form Type</option>
                <option value="feedback">Feedback</option>
                <option value="survey">Survey</option>
                <option value="quiz">Quiz</option>
            </select>
        </div>

        <div id="questions">
            <!-- Default Question -->
            <div class="mb-3 question-block">
                <label>Question 1</label>
                <input type="text" name="questions[]" class="form-control" placeholder="Question text">
                <select name="types[]" class="form-select mt-2">
                    <option value="text">Short Answer</option>
                    <option value="textarea">Paragraph</option>
                    <option value="rating">Rating (1 to 5)</option>
                </select>
            </div>
        </div>

        <button type="button" class="btn btn-secondary" onclick="addQuestion()">+ Add Question</button>
        <button type="submit" class="btn btn-primary">Create Form</button>
    </form>

    <script>
        function addQuestion() {
            const index = document.querySelectorAll('.question-block').length + 1;
            const questionBlock = document.createElement('div');
            questionBlock.classList.add('mb-3', 'question-block');
            questionBlock.innerHTML = `
    <label>Question ${index}</label>
    <input type="text" name="questions[]" class="form-control" placeholder="Question text">
    <select name="types[]" class="form-select mt-2">
      <option value="text">Short Answer</option>
      <option value="textarea">Paragraph</option>
      <option value="rating">Rating (1 to 5)</option>
    </select>`;
            document.getElementById('questions').appendChild(questionBlock);
        }
    </script>

</body>

</html>