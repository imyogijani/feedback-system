<?php
// This is the generated feedback form for 
// Form ID: 49
// Title: Feedback Test
// Description: asdfas
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Test</title>
    <link rel="stylesheet" href="/feedback-system/assets/css/demo.css">
</head>
<body>
    <h1>Feedback Test</h1>
    <p>asdfas</p>
    <form action="/feedback-system/admin/crud/process_response.php" method="POST">
        <input type="hidden" name="form_id" value="49">
        <label for="firstname">First Name:</label><br>
        <input type="text" id="firstname" name="firstname" required><br><br>
        <label for="lastname">Last Name:</label><br>
        <input type="text" id="lastname" name="lastname" required><br><br>
        <div class="question">
            <p>performance</p>
            <input type="text" name="question_1" required><br>
        </div>
        <input type="submit" value="Submit Feedback" class="submit-btn">
    </form>
</body>
</html>
