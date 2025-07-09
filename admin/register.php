<?php
session_start();
if (isset($_SESSION['alert_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['alert_message'] . '</div>';
    unset($_SESSION['alert_message']); // Clear the message after displaying
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
<?php
include('assets/inc/incHeader.php');

?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success position-fixed bottom-0 end-0 m-3" role="alert" style="z-index: 2000; width: auto;">
        <?= htmlspecialchars($_SESSION['success_message']) ?>
        <?php unset($_SESSION['success_message']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['alert_message'])): ?>
    <div class="alert alert-danger position-fixed bottom-0 end-0 m-3" role="alert" style="z-index: 2000; width: auto;">
        <?= htmlspecialchars($_SESSION['alert_message']) ?>
        <?php unset($_SESSION['alert_message']); ?>
    </div>
<?php endif; ?>


<style>
    .alert {
        z-index: 2000 !important;
        position: fixed;
        bottom: 0;
        right: 0;
        margin: 1rem;
        width: auto;
    }
</style>

<script>
    // Hide alerts after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => alert.remove());
    }, 5000);
</script>

<body>
    <!-- Content -->

    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <!-- Register Card -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center">
                            <a href="index.html" class="app-brand-link gap-2">
                                <span class="app-brand-logo demo">

                                </span>
                                <span class="app-brand-text demo text-body fw-bolder">Admin For System</span>
                            </a>
                        </div>
                        <!-- /Logo -->

                        <!-- Registration Form -->
                        <form id="formAuthentication" class="mb-3" action="crud/create-signup-user.php" method="POST">
                            <div class="mb-3">
                                <label for="firstname" class="form-label">First Name</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="firstname"
                                    name="firstname"
                                    placeholder="Enter your first name"
                                    required />
                            </div>
                            <div class="mb-3">
                                <label for="lastname" class="form-label">Last Name</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="lastname"
                                    name="lastname"
                                    placeholder="Enter your last name"
                                    required />
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="username"
                                    name="username"
                                    placeholder="Enter your username"
                                    autofocus
                                    required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Business Name</label>
                                <input type="text" name="business_name" required class="form-control" placeholder="Business Name">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Type of Business</label>
                                <select name="business_type" id="businessTypeSelect" required class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="" disabled selected>Select your business type</option>
                                    <option value="Retail">Retail</option>
                                    <option value="Education">Education</option>
                                    <option value="Healthcare">Healthcare</option>
                                    <option value="IT Services">IT Services</option>
                                    <option value="Manufacturing">Manufacturing</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="mb-3 hidden" id="otherBusinessTypeDiv">
                                <label class="form-label">Please specify your business type</label>
                                <input type="text" name="other_business_type" class="form-control" placeholder="Type of business">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input
                                    type="email"
                                    class="form-control"
                                    id="email"
                                    name="email"
                                    placeholder="Enter your email"
                                    required />
                            </div>
                            <div class="mb-3">
                                <label for="mobile" class="form-label">Mobile Number</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="mobile"
                                    name="mobile"
                                    placeholder="Enter your 10-digit mobile number"
                                    pattern="^[6-9]\d{9}$"
                                    maxlength="10"
                                    required />
                            </div>
                            
                            <div class="mb-3 form-password-toggle">
                                <label class="form-label" for="password">Password</label>
                                <div class="input-group input-group-merge">
                                    <input
                                        type="password"
                                        id="password"
                                        class="form-control"
                                        name="password"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                        aria-describedby="password"
                                        required />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                            </div>
                            <!-- <div class="mb-3">
                                <label for="amount" class="form-label">Amount</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    id="amount"
                                    name="amount"
                                    placeholder="Enter amount"
                                    min="0"
                                    step="0.01"
                                    required />
                            </div> -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms" required />
                                    <label class="form-check-label" for="terms-conditions">
                                        I agree to
                                        <a href="javascript:void(0);">privacy policy & terms</a>
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary d-grid w-100" name="submit">Sign up</button>
                        </form>

                        <p class="text-center">
                            <span>Already have an account?</span>
                            <a href="login.php">
                                <span>Sign in instead</span>
                            </a>
                        </p>
                    </div>
                </div>
                <!-- Register Card -->
            </div>
        </div>
    </div>

    <!-- / Content -->



    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="../assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->

    <!-- Main JS -->
    <script src="../assets/js/main.js"></script>

    <!-- Page JS -->

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>

    <script>
        // Client-side validation
        function validateForm() {
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();

            if (!username || !email || !password) {
                alert('Please fill in all fields.');
                return false;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Invalid email format.');
                return false;
            }

            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            if (!passwordRegex.test(password)) {
                alert('Password must be at least 8 characters long, include an uppercase letter, a lowercase letter, a number, and a special character.');
                return false;
            }

            return true;
        }
    </script>
    <script>
    // Show/hide 'Other' business type field (robust, runs on load and on change)
    document.addEventListener('DOMContentLoaded', function() {
        var businessTypeSelect = document.getElementById('businessTypeSelect');
        var otherBusinessTypeDiv = document.getElementById('otherBusinessTypeDiv');
        var otherInput = otherBusinessTypeDiv.querySelector('input');

        function toggleOtherBusinessType() {
            if (businessTypeSelect.value === 'Other') {
                otherBusinessTypeDiv.style.display = '';
                otherInput.required = true;
            } else {
                otherBusinessTypeDiv.style.display = 'none';
                otherInput.required = false;
                otherInput.value = '';
            }
        }

        businessTypeSelect.addEventListener('change', toggleOtherBusinessType);
        // Hide by default in case JS loads after DOM
        if (businessTypeSelect.value !== 'Other') {
            otherBusinessTypeDiv.style.display = 'none';
        }
        // Run on page load in case of browser autofill or back button
        toggleOtherBusinessType();
    });
    </script>
</body>

</html>