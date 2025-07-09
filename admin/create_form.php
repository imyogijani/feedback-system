<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Feedback Form</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="container mt-5">

    <h3>Create Feedback Form</h3>

    <form method="post" action="save_form.php">
        <div class="mb-3">
            <label class="form-label">Form Title</label>
            <input type="text" class="form-control" name="title" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description"></textarea>
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