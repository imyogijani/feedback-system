<?php
session_start();

// include('assets/inc/incHeader.php');
include('config/config.php');

if (isset($_POST['submit'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Fetch user from database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Handle login logic
    if ($user) {
        $isDefaultAdmin = ($username === 'admin' && $password === 'admin@123');
        $isValidUser = password_verify($password, $user['password']);

        if ($isDefaultAdmin || $isValidUser) {
            // Set common session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_id'] = $user['role_id'] ?? 3;
            $_SESSION['role'] = $user['role'] ?? 'user';

            // Set role-specific session variables
            switch ($_SESSION['role_id']) {
                case 1:
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['success_message'] = "Welcome, {$_SESSION['username']}! You are logged in as an admin.";
                    $redirect = 'index.php';
                    break;
                case 2:
                    $_SESSION['moderator_logged_in'] = true;
                    $_SESSION['success_message'] = "Welcome, {$_SESSION['username']}! You are logged in as a moderator.";
                    $redirect = 'moderator_dashboard.php';
                    break;
                default:
                    $_SESSION['user_logged_in'] = true;
                    $_SESSION['success_message'] = "Welcome, {$_SESSION['username']}! You are logged in.";
                    $redirect = 'user_dashboard.php';
            }

            header("Location: $redirect");
            exit();
        }
    }

    // Invalid login
    $_SESSION['alert_message'] = "❌ Invalid username or password.";
    echo "invalid";
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en" class="light-style customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="../assets/" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Login - Admin For System</title>
    <meta name="description" content="" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="../assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="../assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />
    <link rel="stylesheet" href="assets/css/style.css"/>
    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="../assets/vendor/css/pages/page-auth.css" />

    <!-- Scripts -->
    <script src="../assets/vendor/js/helpers.js"></script>
    <script src="../assets/js/config.js"></script>
    <script type="module" src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app.js"></script>
    <script type="module" src="https://www.gstatic.com/firebasejs/9.6.1/firebase-auth.js"></script>

    <style>
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 2000;
            min-width: 250px;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 0.5rem;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            color: white;
        }

        .toast.show {
            opacity: 1;
        }

        .toast-success {
            background-color: #5cb85c;
        }

        .toast-error {
            background-color: #d9534f;
        }
    </style>
</head>

<body>
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <div class="card">
                    <div class="card-body">
                        <div class="app-brand justify-content-center">
                            <a href="index.html" class="app-brand-link gap-2">
                                <span class="app-brand-logo demo"></span>
                                <span class="app-brand-text demo text-body fw-bolder">Admin For System</span>
                            </a>
                        </div>
                        <form id="formAuthentication" class="mb-3" action="" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" placeholder="Enter your username" autofocus required />
                            </div>
                            <div class="mb-3 form-password-toggle">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label" for="password">Password</label>
                                    <a href="forgot-password.php">
                                        <small>Forgot Password?</small>
                                    </a>
                                </div>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password" class="form-control" name="password" 
                                           placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" 
                                           aria-describedby="password" required />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember-me" />
                                    <label class="form-check-label" for="remember-me"> Remember Me </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <button class="btn btn-primary d-grid w-100" type="submit" name="submit">Sign in</button>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <button class="btn btn-primary d-grid w-100" type="button" onclick="signInWithGoogle()">Sign in Google</button>
                                </div>
                            </div>
                        </form>

                        <p class="text-center">
                            <span>New on our platform?</span>
                            <a href="register.php">
                                <span>Create an account</span>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="toast toast-success show" role="alert" aria-live="assertive" aria-atomic="true">
            <?php echo htmlspecialchars($_SESSION['success_message']); ?>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['alert_message'])): ?>
        <div class="toast toast-error show" role="alert" aria-live="assertive" aria-atomic="true">
            <?php echo htmlspecialchars($_SESSION['alert_message']); ?>
            <?php unset($_SESSION['alert_message']); ?>
        </div>
    <?php endif; ?>

    <!-- Core JS -->
    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../assets/vendor/js/menu.js"></script>
    <script src="../assets/js/main.js"></script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/9.6.1/firebase-app.js";
        import { getAuth, GoogleAuthProvider, signInWithPopup } from "https://www.gstatic.com/firebasejs/9.6.1/firebase-auth.js";

        const firebaseConfig = {
            apiKey: "AIzaSyDMTtRugW9lFa3ITfippO0DP7iSmGuiVRY",
            authDomain: "sign-in-549ee.firebaseapp.com",
            projectId: "sign-in-549ee",
            storageBucket: "sign-in-549ee.firebasestorage.app",
            messagingSenderId: "723595676696",
            appId: "1:723595676696:web:a0925f60f0cfc710cf876d",
            measurementId: "G-PCQDPTBF5Y"
        };

        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);

        function showToast(message, type = 'info') {
            const toastDiv = document.createElement('div');
            toastDiv.className = `toast toast-${type}`;
            toastDiv.setAttribute('role', 'alert');
            toastDiv.setAttribute('aria-live', 'assertive');
            toastDiv.setAttribute('aria-atomic', 'true');
            toastDiv.textContent = message;
            document.body.appendChild(toastDiv);

            setTimeout(() => toastDiv.classList.add('show'), 100);
            setTimeout(() => {
                toastDiv.classList.remove('show');
                setTimeout(() => toastDiv.remove(), 300);
            }, 5000);
        }

        window.signInWithGoogle = async function() {
            try {
                const provider = new GoogleAuthProvider();
                const result = await signInWithPopup(auth, provider);
                const idToken = await result.user.getIdToken();

                const response = await fetch("google-login.php", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ idToken })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    showToast("Google Sign-In successful! Redirecting...", "success");
                    setTimeout(() => window.location.href = "user_dashboard.php", 1500);
                } else {
                    showToast(`Login failed: ${data.message || 'Unknown error'}`, "error");
                }
            } catch (error) {
                console.error("Google Sign-In Error:", error);
                showToast(`Google Sign-In failed: ${error.message}`, "error");
            }
        };

        $(document).ready(function() {
            <?php if (isset($_SESSION['success_message'])): ?>
                showToast("<?php echo addslashes($_SESSION['success_message']); ?>", "success");
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['alert_message'])): ?>
                showToast("<?php echo addslashes($_SESSION['alert_message']); ?>", "error");
                <?php unset($_SESSION['alert_message']); ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>