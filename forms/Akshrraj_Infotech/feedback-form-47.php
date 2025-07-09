<?php
// This is the generated feedback form for Akshrraj_Infotech
// Form ID: 47
// Title: Gyanmanjari Fee Collection
// Description: 
// You can add more dynamic content generation here based on questions and options
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gyanmanjari Fee Collection</title>
    <link rel="stylesheet" href="/feedback-system/assets/css/demo.css">
</head>
<body>
    <h1>Gyanmanjari Fee Collection</h1>
    <p></p>
    <form action="/feedback-system/admin/crud/process_response.php" method="POST">
        <input type="hidden" name="form_id" value="47">
        <label for="firstname">First Name:</label><br>
        <input type="text" id="firstname" name="firstname" required><br><br>
        <label for="lastname">Last Name:</label><br>
        <input type="text" id="lastname" name="lastname" required><br><br>
        <label for="number">Phone Number:</label><br>
        <input type="tel" id="number" name="number" pattern="[0-9]{10}" required><br><br>
        <div class="question">
            <p>Performance</p>
        </div>
        <input type="submit" value="Submit Feedback" class="submit-btn">
    </form>
</body>
</html>
