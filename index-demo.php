<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'admin/vendor/autoload.php'; // PHPMailer

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'admin/config/config.php'; // DB

    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $business_name = trim($_POST['business_name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $comment = trim($_POST['comment']);

    // Save to DB
    $stmt = $conn->prepare("
        INSERT INTO demo_requests 
        (first_name, last_name, business_name, email, mobile, comment) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    if ($stmt->execute([$first_name, $last_name, $business_name, $email, $mobile, $comment])) {
        // Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'foramparikh1234@gmail.com'; 
            $mail->Password   = 'sgis ocuy nolq kujo';       
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('foramparikh1234@gmail.com', 'Feedback System');
            $mail->addAddress($email, $first_name . ' ' . $last_name);
            $mail->isHTML(true);
            $mail->Subject = 'Demo Request Received';

            $mailContent = "
                <h4>Hello {$first_name} {$last_name},</h4>
                <p>Thank you for requesting a demo. Here are your submitted details:</p>
                <ul>
                    <li><strong>Business Name:</strong> {$business_name}</li>
                    <li><strong>Email:</strong> {$email}</li>
                    <li><strong>Mobile:</strong> {$mobile}</li>
                    <li><strong>Comment:</strong> {$comment}</li>
                </ul>
                <p><strong>Login Credentials:</strong> <br>
                Username: {$email} <br>
                Password: {$mobile}</p>
                <p><a href='http://localhost/feedback-system/demo/login.php'>Click here to login</a></p>
                <p>We will get back to you soon.</p>
            ";
            $mail->Body = $mailContent;
            $mail->AltBody = "Thank you for requesting a demo. Username: {$email}, Password: {$mobile}. Login here: http://localhost/feedback-system/demo/login.php";

            $mail->send();
            $success = "Demo request submitted successfully! Please check your email.";
        } catch (Exception $e) {
            $error = "Request saved but email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Failed to save your request. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Feedback System - Request a Demo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <!-- Hero -->
    <div class="bg-dark text-white text-center p-5">
        <h1>Feedback System</h1>
        <p>Collect and analyze feedback with ease</p>
        <a href="#requestDemo" class="btn btn-warning">Request a Demo</a>
    </div>

    <!-- Request Demo Form -->
    <div id="requestDemo" class="container my-5">
        <h2 class="text-center mb-4">Request a Demo</h2>

        <?php if ($success): ?>
            <div class="alert alert-success text-center"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="row g-3">

            <div class="col-md-6">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control" required />
            </div>

            <div class="col-md-6">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control" required />
            </div>

            <div class="col-12">
                <label class="form-label">Business Name</label>
                <input type="text" name="business_name" class="form-control" required />
            </div>

            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required />
            </div>

            <div class="col-md-6">
                <label class="form-label">Mobile Number</label>
                <input type="text" name="mobile" class="form-control" pattern="^\d{10}$" maxlength="10" required title="Enter exactly 10 digits" />
            </div>

            <div class="col-12">
                <label class="form-label">Comment</label>
                <textarea name="comment" class="form-control" rows="3" required></textarea>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary w-100">Submit Request</button>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center py-3">
        &copy; <?= date('Y') ?> Feedback System. All Rights Reserved.
    </footer>
</body>
</html>
