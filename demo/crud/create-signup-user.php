<?php
session_start();
include('../config/config.php'); // Ensure this sets up $conn as a PDO object
include('../assets/inc/incHeader.php'); // Include header for HTML structure

try {
    $alert = ''; // Alert message

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $email    = trim($_POST['email']);
        $password = trim($_POST['password']);

        // Server-side validation
        if (empty($username) || empty($email) || empty($password)) {
            $_SESSION['alert_message'] = "❌ Please fill in all fields.";
            echo "<script>window.location.href = '../register.php';</script>";
            exit();
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['alert_message'] = "❌ Invalid email format.";
            echo "<script>window.location.href = '../register.php';</script>";
            exit();
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
            $_SESSION['alert_message'] = "❌ Password must be at least 8 characters long, include an uppercase letter, a lowercase letter, a number, and a special character.";
            echo "<script>window.location.href = '../register.php';</script>";
            exit();
        } else {
            // Check if username or email already exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email");
            $stmt->execute(['username' => $username, 'email' => $email]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $_SESSION['alert_message'] = "❌ Username or email already taken.";
                echo "<script>window.location.href = '../register.php';</script>";
                exit();
            } else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert user into DB
                $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
                $stmt->execute([
                    'username' => $username,
                    'email'    => $email,
                    'password' => $hashedPassword
                ]);

                // Set success message in session
                $_SESSION['success_message'] = "✅ Registration successful!";
                echo "<script>window.location.href = '../login.php';</script>";
                exit();
            }
        }
    }
} catch (PDOException $e) {
    $_SESSION['alert_message'] = "❌ Database error: " . $e->getMessage();
    echo "<script>window.location.href = '../register.php';</script>";
    exit();
}
