<?php
// Set session lifetime to 5 minutes for testing (adjust as needed for 1 day = 86400)
ini_set('session.gc_maxlifetime', 300);
session_set_cookie_params([
    'lifetime' => 300, // 300 sec = 5 min for testing, set to 86400 for 1 day live
    'path' => '/',
    'domain' => '',
    'secure' => false,    // true if using HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

include '../demo/config/config.php'; // DB connection

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);

    // Basic mobile validation backend
    if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
        $error = "Please enter a valid 10-digit mobile number starting with 6-9.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM demo_requests WHERE email = ? AND mobile = ? AND approved = 1");
        $stmt->execute([$email, $mobile]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['demo_user'] = $user['email'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role_id'] = $user['role_id'];
            header("Location: user_dashboard.php");
            exit;
        } else {
            $error = "Invalid credentials or your demo request is not yet approved.";
        }
    }
}
?>

<!DOCTYPE html>
<html
    lang="en"
    class="light-style customizer-hide"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="../assets/"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Login - Admin For System</title>

    <meta name="description" content="" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="../assets/vendor/fonts/boxicons.css" />

    <link rel="stylesheet" href="../assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />

    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <link rel="stylesheet" href="../assets/vendor/css/pages/page-auth.css" />
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
            /* Ensure text color is white for all toasts */
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
                     <form method="POST" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Email address</label>
                    <input type="email" name="email" class="form-control" required placeholder="Enter your email">
                            </div>
                            
                            <div class="mb-3">
                    <label class="form-label">Mobile Number</label>
                    <input type="text" name="mobile" class="form-control"
                        pattern="^[6-9]\d{9}$" maxlength="10" required
                        title="Enter a valid 10-digit mobile number starting with 6-9"
                        placeholder="Enter your 10-digit mobile">
                </div>
                            <div class="row">
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Login
                </button>
                                </div>
                                <!-- <div class=" col-md-6 mb-3">
                                    <button class="btn btn-primary d-grid w-100" type="button" onclick="signInWithGoogle()">Sign in Google</button>
                                </div> -->
                            </div>
                        </form>

                        <!-- <p class="text-center">
                            <span>New on our platform?</span>
                            <a href="register.php">
                                <span>Create an account</span>
                            </a>
                        </p> -->
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

    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../assets/vendor/js/menu.js"></script>

    <script src="../assets/js/main.js"></script>

    <script async defer src="https://buttons.github.io/buttons.js"></script>

    <script type="module">
        // Import Firebase SDKs (v9+ modular syntax)
        // These imports are necessary because we are using specific functions (initializeApp, getAuth, etc.)
        import {
            initializeApp
        } from "https://www.gstatic.com/firebasejs/9.6.1/firebase-app.js";
        import {
            getAuth,
            GoogleAuthProvider,
            signInWithPopup
        } from "https://www.gstatic.com/firebasejs/9.6.1/firebase-auth.js";

        // Your web app's Firebase configuration
        const firebaseConfig = {
            apiKey: "AIzaSyDMTtRugW9lFa3ITfippO0DP7iSmGuiVRY",
            authDomain: "sign-in-549ee.firebaseapp.com",
            projectId: "sign-in-549ee",
            storageBucket: "sign-in-549ee.firebasestorage.app", // Corrected from .appspot.com to .firebasestorage.app if that's what Firebase provides
            messagingSenderId: "723595676696",
            appId: "1:723595676696:web:a0925f60f0cfc710cf876d",
            measurementId: "G-PCQDPTBF5Y"
        };

        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app); // Get the Auth service instance

        // Function to display toasts
        function showToast(message, type = 'info') {
            const toastDiv = document.createElement('div');
            toastDiv.className = `toast toast-${type}`;
            toastDiv.setAttribute('role', 'alert');
            toastDiv.setAttribute('aria-live', 'assertive');
            toastDiv.setAttribute('aria-atomic', 'true');
            toastDiv.textContent = message;
            document.body.appendChild(toastDiv);

            setTimeout(() => {
                toastDiv.classList.add('show');
            }, 100);

            setTimeout(() => {
                toastDiv.classList.remove('show');
                setTimeout(() => toastDiv.remove(), 300);
            }, 5000);
        }

        // Global function for Google Sign-In, called by the button
        window.signInWithGoogle = async function() {
            const provider = new GoogleAuthProvider();
            try {
                const result = await signInWithPopup(auth, provider);
                const user = result.user;
                console.log("Firebase Google Sign-In successful for:", user.email);

                const idToken = await user.getIdToken();
                console.log("Generated Firebase ID Token:", idToken);

                // Send ID token to your PHP backend for verification
                const response = await fetch("google-login.php", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        idToken: idToken
                    })
                });

                const data = await response.json();
                console.log("Response from PHP backend:", data);

                if (response.ok && data.success) {
                    showToast("Google Sign-In successful! Redirecting...", "success");
                    // Redirect to your user dashboard after a short delay
                    setTimeout(() => {
                        window.location.href = "user_dashboard.php"; // Redirect to your actual dashboard page
                    }, 1500);
                } else {
                    showToast(`Login failed: ${data.message || 'Unknown error'}`, "error");
                    console.error("PHP backend reported an error:", data.message, data);
                }
            } catch (error) {
                console.error("Google Sign-In Error:", error.code, error.message);
                showToast(`Google Sign-In failed: ${error.message}`, "error");
            }
        };

        // Handle initial PHP session messages (from traditional login/logout)
        // Using jQuery from your original setup
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