<?php
// Emergency admin login page - outside admin directory
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['role_id'])) {
    switch ($_SESSION['role_id']) {
        case 1:
            header("Location: admin/index.php");
            exit();
        case 2:
            header("Location: admin/moderator_dashboard.php");
            exit();
        default:
            header("Location: admin/user_dashboard.php");
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
                            $redirect = 'admin/index.php';
                            break;
                        case 2:
                            $_SESSION['moderator_logged_in'] = true;
                            $redirect = 'admin/moderator_dashboard.php';
                            break;
                        default:
                            $_SESSION['user_logged_in'] = true;
                            $redirect = 'admin/user_dashboard.php';
                    }

                    echo "<script>alert('Login successful!'); window.location.href = '$redirect';</script>";
                    exit();
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

// This should only execute if we're NOT being served the wrong file
echo "<!-- PHP LOGIN PAGE LOADING -->";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Admin Login - Feedback System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            border-left: 5px solid #2a5298;
        }

        .emergency-header {
            background: #ff6b6b;
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: bold;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #2a5298;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2a5298;
            box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(42, 82, 152, 0.3);
        }

        .error-message {
            background: #ffe6e6;
            color: #d63031;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #d63031;
            font-weight: 500;
        }

        .success-message {
            background: #e8f5e8;
            color: #00b894;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #00b894;
            font-weight: 500;
        }

        .debug-info {
            margin-top: 25px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 12px;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .credentials-box {
            margin-top: 20px;
            padding: 15px;
            background: #fff3cd;
            border-radius: 8px;
            border: 1px solid #ffeaa7;
        }

        .credentials-box h4 {
            color: #856404;
            margin-bottom: 10px;
        }

        .cred-item {
            font-family: monospace;
            background: #f8f9fa;
            padding: 8px;
            border-radius: 4px;
            margin: 5px 0;
            border-left: 3px solid #856404;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="emergency-header">
            🚨 EMERGENCY LOGIN ACCESS 🚨
        </div>

        <div class="login-header">
            <h1>Admin Login</h1>
            <p>This is an emergency access page bypassing Apache redirect issues</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                ❌ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="success-message">
                ✅ <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">👤 Username:</label>
                <input type="text" id="username" name="username" required
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       placeholder="Enter your username">
            </div>

            <div class="form-group">
                <label for="password">🔒 Password:</label>
                <input type="password" id="password" name="password" required
                       placeholder="Enter your password">
            </div>

            <button type="submit" name="login_submit" class="btn-login">
                🚀 LOGIN NOW
            </button>
        </form>

        <div class="credentials-box">
            <h4>🔑 Default Admin Credentials:</h4>
            <div class="cred-item"><strong>Username:</strong> admin</div>
            <div class="cred-item"><strong>Password:</strong> admin@123</div>
        </div>

        <div class="debug-info">
            <strong>🔍 Debug Information:</strong><br>
            Current URL: <?php echo $_SERVER['REQUEST_URI']; ?><br>
            Current File: <?php echo __FILE__; ?><br>
            Document Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?><br>
            Server Software: <?php echo $_SERVER['SERVER_SOFTWARE']; ?><br>
            PHP Version: <?php echo PHP_VERSION; ?>
        </div>
    </div>
</body>
</html>
