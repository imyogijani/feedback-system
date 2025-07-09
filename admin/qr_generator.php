<?php
session_start();
include('config/config.php');
include('assets/inc/incHeader.php');

include "../phpqrcode/qrlib.php"; // Make sure this path is correct

$file = null; // Initialize

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['data'])) {
    $data = trim($_POST['data']);
    $folder = 'qrcodes/';

    // Create folder if it doesn't exist
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    // Generate filename
    $filename = $folder . md5($data . time()) . '.png';

    // Generate QR code
    QRcode::png($data, $filename, QR_ECLEVEL_H, 6);

    // Save filename to display later
    $file = $filename;
}
?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include('assets/inc/incSidebar.php'); ?>

            <!-- Layout container -->
            <div class="layout-page">

                <?php include('assets/inc/incNavbar.php'); ?>
                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row">
                            <div class="col-lg-12">
                                <h2 class="text-center title">QR Code Generator</h2>
                                <p class="text-center">Generate QR codes for any data you want!</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Generate QR Code</h5>
                                        <form method="POST" action="">
                                            <div class="mb-3">
                                                <label for="data" class="form-label">Data to encode:</label>
                                                <input type="text" class="form-control" id="data" name="data" required>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Generate QR Code</button>
                                        </form>

                                        <?php if ($file): ?>
                                            <h4 class="mt-4">Generated QR Code:</h4>
                                            <img src="<?= $file ?>" alt="QR Code" class="img-fluid" style="max-width: 200px;">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- / Content -->
                </div>
                <!-- / Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
</body>