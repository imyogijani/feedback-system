<?php
session_start();
include('../assets/inc/incHeader.php');
$form_id = $_POST['form_id'] ?? 0;
if (!$form_id) {
   
}
?>
<div class="container mt-5 text-center">
    <h3>Thank you for your submission!</h3>

    <?php if (isset($_GET['form_id']) && intval($_GET['form_id']) > 0): ?>
        <a href="view_form.php?id=<?= intval($_GET['form_id']) ?>" class="btn btn-primary mt-3">Back to Form</a>
        <button class="btn btn-success mt-3" onclick="window.location.href='view_form.php?id=<?= intval($_GET['form_id']) ?>&d=1'">Submit Another Response</button>
    <?php elseif (isset($_SESSION['role_id'])): ?>
        <?php if ($_SESSION['role_id'] == 1): ?>
            <a href="../index.php" class="btn btn-primary mt-3">Back to Admin Dashboard</a>
        <?php elseif ($_SESSION['role_id'] == 2): ?>
            <a href="../moderator_dashboard.php" class="btn btn-primary mt-3">Back to Moderator Dashboard</a>
        <?php elseif ($_SESSION['role_id'] == 3): ?>
            <a href="../user_dashboard.php" class="btn btn-primary mt-3">Back to User Dashboard</a>
        <?php endif; ?>
    <?php else: ?>
        <a href="../index.php" class="btn btn-primary mt-3">Back to Home</a>
    <?php endif; ?>
</div>
