<?php
session_start();
// Allow users with role_id = 1 (admin), 2 (moderator), or 3 (user) to access this page
if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [1, 2, 3])) {
    header("Location: login.php");
    exit();
}

include('config/config.php');
include('assets/inc/incHeader.php');
include "../phpqrcode/qrlib.php";

$form_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$publish_status = isset($_GET['status']) ? intval($_GET['status']) : 1;

if ($form_id <= 0 || ($publish_status !== 0 && $publish_status !== 1)) {
    die("Invalid request.");
}

// Update publish status
$stmt = $conn->prepare("UPDATE forms SET published = :status WHERE id = :id");
$stmt->execute([':status' => $publish_status, ':id' => $form_id]);


// Fetch form title for display
$stmt = $conn->prepare("SELECT * FROM forms WHERE id = :id");
$stmt->execute([':id' => $form_id]);
$form = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$form) {
    die("Form not found.");
}

// Public URL to view the form
$baseUrl = "http://" . $_SERVER['HTTP_HOST'];

// Fetch created_for and business_name
$created_for = $form['created_for'] ?? null;
$business_name = '';

if ($created_for !== null) {
    $stmtUser = $conn->prepare("SELECT business_name FROM users WHERE id = :id");
    $stmtUser->execute([':id' => $created_for]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $business_name = $user['business_name'];
    }
}

// Sanitize business name consistently with save_form.php
$sanitized_business_name = preg_replace('/[^a-zA-Z0-9_ -]/', '', $business_name);
$sanitized_business_name = str_replace(' ', '_', $sanitized_business_name);

$formFileName = "feedback-form-{$form_id}.php";
$formLink = $baseUrl . "/feedback-system/forms/" . ($sanitized_business_name ? $sanitized_business_name . '/' : '') . $formFileName;

// Use Google Chart API for QR code
$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($formLink);



?>



<body>
    <!-- Layout wrapper -->
    <div class="">
        <div class="container">
            <!-- Menu -->

            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->

                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <!-- Your page content goes here -->
                        <h2 class="card-title text-success mb-3">✅ Form Published Successfully</h2>
                        <p><strong>Form:</strong> <?= htmlspecialchars($form['title'])  ?></p>
                        <div class="mb-3">
                            <label><strong>Public Form Link:</strong></label>
                            <div class="input-group">
                                <input type="text" id="formLink" class="form-control text-center" value="<?= $formLink ?>" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyLink()">Copy</button>
                            </div>
                            <a href="<?= $formLink ?>" class="btn btn-primary mt-2" target="_blank">Open Form</a>
                        </div>



                        <h5 class="card-title">Generate QR Code</h5>
                        <p>Scan the QR code below to access the form quickly:</p>
                        <img src="<?= $qrCodeUrl ?>" alt="QR Code" class="img-thumbnail" id="qrImage">
                        <div class="mt-2">
                            <a href="<?= $qrCodeUrl ?>" id="downloadQrBtn" class="btn btn-outline-success">Download QR Code</a>
                        </div>
                        <script>
                        document.getElementById('downloadQrBtn').addEventListener('click', function(e) {
                            e.preventDefault();
                            const url = this.href;
                            fetch(url)
                                .then(resp => resp.blob())
                                .then(blob => {
                                    const link = document.createElement('a');
                                    link.href = window.URL.createObjectURL(blob);
                                    link.download = 'form-qr-code.png';
                                    document.body.appendChild(link);
                                    link.click();
                                    document.body.removeChild(link);
                                });
                        });
                        </script>


                        <div class="mb-3">
                            <?php if (isset($_SESSION['role_id'])): ?>
                                <?php if ($_SESSION['role_id'] == 1): ?>
                                    <a href="index.php" class="btn btn-dark mt-4">&larr; Back to Admin Dashboard</a>
                                <?php elseif ($_SESSION['role_id'] == 2): ?>
                                    <a href="moderator_dashboard.php" class="btn btn-dark mt-4">&larr; Back to Moderator Dashboard</a>
                                <?php elseif ($_SESSION['role_id'] == 3): ?>
                                    <a href="user_dashboard.php" class="btn btn-dark mt-4">&larr; Back to User Dashboard</a>
                                <?php else: ?>
                                    <a href="index.php" class="btn btn-dark mt-4">&larr; Back to Home</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="index.php" class="btn btn-dark mt-4">&larr; Back to Home</a>
                            <?php endif; ?>
                        </div>


                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success position-fixed bottom-0 end-0 m-3" role="alert" style="z-index: 2000; width: auto;">
                                <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                                <?php unset($_SESSION['success_message']); ?>
                            </div>
                        <?php endif; ?>

                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    <?php include('assets/inc/incFooter.php'); ?>
                    <!-- / Footer -->
                </div>
                <!-- / Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="../assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="../assets/vendor/libs/apex-charts/apexcharts.js"></script>

    <!-- Main JS -->
    <script src="../assets/js/main.js"></script>

    <!-- Page JS -->
    <script src="../assets/js/dashboards-analytics.js"></script>

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>

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
        // Automatically hide the toast after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => alert.remove());
        }, 5000);
    </script>
    <script>
        function copyLink() {
            const linkInput = document.getElementById('formLink');
            linkInput.select();
            linkInput.setSelectionRange(0, 99999); // For mobile support
            document.execCommand('copy');

            // Optional: show a brief confirmation
            const btn = event.target;
            btn.innerText = 'Copied!';
            setTimeout(() => btn.innerText = 'Copy', 2000);
        }
    </script>
    <script>
        window.onload = function() {
            const formLinkInput = document.getElementById('formLink');
            formLinkInput.select();
            formLinkInput.setSelectionRange(0, 99999);
            document.execCommand('copy');

            // Optional toast
            const toast = document.createElement('div');
            toast.className = 'alert alert-info';
            toast.innerText = 'Form link copied to clipboard!';
            document.body.appendChild(toast);

            setTimeout(() => toast.remove(), 3000);
        };
    </script>


</body>