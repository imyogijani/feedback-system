<?php
// Alternative login page to bypass the redirect issue
session_start();

// Clear output buffer to prevent any unwanted output
ob_start();
ob_clean();

// Check if user is already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['role_id'])) {
    switch ($_SESSION['role_id']) {
        case 1:
            header("Location: index.php");
            exit();
        case 2:
            header("Location: moderator_dashboard.php");
            exit();
        default:
            header("Location: user_dashboard.php");
            exit();
    }
}

// Database connection
try {
    $host = "localhost";
    $username = "root";
    $password = "070803";
    $conn = new PDO("mysql:host=$host;dbname=u334564157_feedback", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$error_message = '';
$success_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $input_username = trim($_POST['username'] ?? '');
    $input_password = $_POST['password'] ?? '';

    if (!empty($input_username) && !empty($input_password)) {
        try {
            // Fetch user from database
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$input_username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $isDefaultAdmin = ($input_username === 'admin' && $input_password === 'admin@123');
                $isValidUser = password_verify($input_password, $user['password']);

                if ($isDefaultAdmin || $isValidUser) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role_id'] = $user['role_id'] ?? 3;
                    $_SESSION['role'] = $user['role'] ?? 'user';

                    // Set role-specific session variables
                    switch ($_SESSION['role_id']) {
                        case 1:
                            $_SESSION['admin_logged_in'] = true;
                            $redirect = 'index.php';
                            break;
                        case 2:
                            $_SESSION['moderator_logged_in'] = true;
                            $redirect = 'moderator_dashboard.php';
                            break;
                        default:
                            $_SESSION['user_logged_in'] = true;
                            $redirect = 'user_dashboard.php';
                    }

                    $success_message = "Login successful! Redirecting...";
                    echo "<script>
                        alert('Login successful!');
                        window.location.href = '$redirect';
                    </script>";
                } else {
                    $error_message = "Invalid username or password.";
                }
            } else {
                $error_message = "User not found.";
            }
        } catch(Exception $e) {
            $error_message = "Login error: " . $e->getMessage();
        }
    } else {
        $error_message = "Please enter both username and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Feedback System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #333;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #fcc;
        }

        .success-message {
            background: #efe;
            color: #363;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #cfc;
        }

        .links {
            text-align: center;
            margin-top: 20px;
        }

        .links a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Admin Login</h1>
            <p>Welcome back! Please sign in to your account</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       placeholder="Enter your username">
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required
                       placeholder="Enter your password">
            </div>

            <button type="submit" name="login_submit" class="btn-login">
                Sign In
            </button>
        </form>

        <div class="links">
            <a href="register.php">Create new account</a> |
            <a href="forgot-password.php">Forgot password?</a>
        </div>

        <div style="margin-top: 20px; padding: 10px; background: #f5f5f5; border-radius: 10px; font-size: 12px; color: #666;">
            <strong>Default Admin Login:</strong><br>
            Username: admin<br>
            Password: admin@123
        </div>
    </div>
</body>
</html>
