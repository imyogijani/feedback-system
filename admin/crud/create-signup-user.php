<?php
session_start();
include('../config/config.php');


try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $firstname = trim($_POST['firstname']);
        $lastname  = trim($_POST['lastname']);
        $username  = trim($_POST['username']);
        $email     = trim($_POST['email']);
        $mobile    = trim($_POST['mobile']);
        $password  = trim($_POST['password']);

        // Validation
        if (empty($firstname) || empty($lastname) || empty($username) || empty($email) || empty($mobile) || empty($password)) {
            $_SESSION['alert_message'] = "❌ Please fill in all fields.";
            header("Location: ../register.php");
            exit();
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['alert_message'] = "❌ Invalid email format.";
            header("Location: ../register.php");
            exit();
        }
        if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
            $_SESSION['alert_message'] = "❌ Invalid mobile number.";
            header("Location: ../register.php");
            exit();
        }
        // Amount validation removed
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
            $_SESSION['alert_message'] = "❌ Password must be strong.";
            header("Location: ../register.php");
            exit();
        }

        // Check existing user
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email");
        $stmt->execute(['username' => $username, 'email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['alert_message'] = "❌ Username or email already taken.";
            header("Location: ../register.php");
            exit();
        }

        // Store user with business_name and business_type, remove amount
        $business_name = isset($_POST['business_name']) ? trim($_POST['business_name']) : '';
        $business_type = isset($_POST['business_type']) ? trim($_POST['business_type']) : '';
        if ($business_type === 'Other' && isset($_POST['other_business_type'])) {
            $business_type = trim($_POST['other_business_type']);
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insertUser = $conn->prepare("
            INSERT INTO users (first_name, last_name, username, email, mobile, business_name, business_type, password, role_id, created_at)
            VALUES (:first_name, :last_name, :username, :email, :mobile, :business_name, :business_type, :password, 3, NOW())
        ");
        $insertUser->execute([
            'first_name' => $firstname,
            'last_name'  => $lastname,
            'username'   => $username,
            'email'      => $email,
            'mobile'     => $mobile,
            'business_name' => $business_name,
            'business_type' => $business_type,
            'password'   => $hashedPassword
        ]);

        // Easebuzz payment removed. Registration completes after DB insert.
        $_SESSION['success_message'] = "✅ Registration successful! You can now log in.";
        header("Location: ../login.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['alert_message'] = "❌ DB Error: " . $e->getMessage();
    header("Location: ../register.php");
    exit();
} catch (Exception $e) {
    $_SESSION['alert_message'] = "❌ Error: " . $e->getMessage();
    header("Location: ../register.php");
    exit();
}
?>
