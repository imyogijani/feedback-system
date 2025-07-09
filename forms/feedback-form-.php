<?php
// This is the generated feedback form for 
// Form ID: 
// Title: Aksharraj infotech
// Description: 
// You can add more dynamic content generation here based on questions and options
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aksharraj infotech</title>
    <link rel="stylesheet" href="/feedback-system/assets/css/demo.css">
</head>
<body>
    <h1>Aksharraj infotech</h1>
    <p></p>
    <form action="/feedback-system/admin/crud/process_response.php" method="POST">
        <input type="hidden" name="form_id" value="">
        <label for="firstname">First Name:</label><br>
        <input type="text" id="firstname" name="firstname" required><br><br>
        <label for="lastname">Last Name:</label><br>
        <input type="text" id="lastname" name="lastname" required><br><br>
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>
        <label for="number">Phone Number:</label><br>
        <input type="tel" id="number" name="number" pattern="[0-9]{10}" required><br><br>
        <div class="question">
            <p>behavior of emplyee</p>
        </div>
        <div class="question">
            <p>internet speed</p>
        </div>
        <div class="question">
            <p>doubt solving</p>
        </div>
        <div class="question">
            <p>any suggestion</p>
            <textarea name="question_4" rows="4" cols="50" required></textarea><br>
        </div>
        <input type="submit" value="Submit Feedback" class="submit-btn">
    </form>
</body>
</html>
