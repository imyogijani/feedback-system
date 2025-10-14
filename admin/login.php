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
                $isValidUser = password_verify($input_password, $user['password']);

                if ($isValidUser) {
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

    <!-- Firebase Scripts -->
    <script type="module" src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app.js"></script>
    <script type="module" src="https://www.gstatic.com/firebasejs/9.6.1/firebase-auth.js"></script>

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

        .google-btn {
            width: 100%;
            padding: 12px;
            background: #fff;
            color: #757575;
            border: 1px solid #dadce0;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .google-btn:hover {
            background: #f8f9fa;
            border-color: #dadce0;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .google-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
            color: #666;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #ddd;
        }

        .divider span {
            background: white;
            padding: 0 15px;
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        }

        .toast.show {
            opacity: 1;
            transform: translateX(0);
        }

        .toast-success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .toast-error {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
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

        <div class="divider">
            <span>OR</span>
        </div>

        <button type="button" id="googleSignInBtn" class="google-btn" onclick="signInWithGoogle()">
            <svg width="20" height="20" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Sign in with Google
        </button>

        <div class="links">
            <a href="register.php">Create new account</a> |
            <a href="forgot-password.php">Forgot password?</a>
        </div>
    </div>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/9.6.1/firebase-app.js";
        import { getAuth, GoogleAuthProvider, signInWithPopup } from "https://www.gstatic.com/firebasejs/9.6.1/firebase-auth.js";

        // Firebase configuration
        const firebaseConfig = {
            apiKey: "AIzaSyDMTtRugW9lFa3ITfippO0DP7iSmGuiVRY",
            authDomain: "sign-in-549ee.firebaseapp.com",
            projectId: "sign-in-549ee",
            storageBucket: "sign-in-549ee.firebasestorage.app",
            messagingSenderId: "723595676696",
            appId: "1:723595676696:web:a0925f60f0cfc710cf876d",
            measurementId: "G-PCQDPTBF5Y"
        };

        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);

        // Toast notification function
        function showToast(message, type = 'info') {
            // Remove any existing toasts
            document.querySelectorAll('.toast').forEach(toast => toast.remove());

            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `<strong>${type === 'success' ? '✓' : '✗'}</strong> ${message}`;
            document.body.appendChild(toast);

            // Show toast
            setTimeout(() => toast.classList.add('show'), 100);

            // Hide toast after 5 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

        // Google Sign-In function
        window.signInWithGoogle = async function() {
            const googleBtn = document.getElementById('googleSignInBtn');
            const originalText = googleBtn.innerHTML;

            try {
                // Show loading state
                googleBtn.disabled = true;
                googleBtn.innerHTML = `
                    <svg width="20" height="20" viewBox="0 0 24 24" class="animate-spin">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity="0.25"/>
                        <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                    Signing in...
                `;

                // Initialize Google Auth Provider
                const provider = new GoogleAuthProvider();

                // Sign in with popup
                const result = await signInWithPopup(auth, provider);
                const user = result.user;

                // Get ID token
                const idToken = await user.getIdToken();

                // Send token to server
                const response = await fetch('google-login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        idToken: idToken,
                        email: user.email,
                        name: user.displayName,
                        picture: user.photoURL
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Success - update button and redirect
                    googleBtn.innerHTML = `
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                        </svg>
                        Success! Redirecting...
                    `;
                    googleBtn.style.background = '#28a745';
                    googleBtn.style.color = 'white';
                    googleBtn.style.borderColor = '#28a745';

                    showToast('Google Sign-In successful! Redirecting...', 'success');

                    // Redirect to dashboard after delay
                    setTimeout(() => {
                        window.location.href = data.redirect || 'user_dashboard.php';
                    }, 1500);
                } else {
                    throw new Error(data.message || 'Login failed');
                }

            } catch (error) {
                console.error('Google Sign-In Error:', error);
                showToast(`Google Sign-In failed: ${error.message}`, 'error');

                // Reset button
                googleBtn.disabled = false;
                googleBtn.innerHTML = originalText;
            }
        };

        // Add spinning animation for loading state
        const style = document.createElement('style');
        style.textContent = `
            .animate-spin {
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);

    </script>
</body>
</html>
