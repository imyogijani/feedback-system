<?php
session_start();


include('config/config.php');

if (isset($_SESSION['admin_logged_in'])) {
    $user_id = $_SESSION['admin_logged_in'];
    $user_type = 'admin';
} elseif (isset($_SESSION['moderator_logged_in'])) {
    $user_id = $_SESSION['moderator_logged_in'];
    $user_type = 'moderator';
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_type = 'user';
} else {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';
// var_dump($_SESSION); // Debugging line to check session data
// exit;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // var_dump($_POST); // Debugging line to check POST data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $image = $_FILES['profile_image'] ?? null;

    // Image upload handling
    $uploadFileName = null;
    if ($image && $image['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
        $uploadFileName = $user_type . '_' . $user_id . '.' . $ext;
        $uploadPath = 'assets/images/' . $uploadFileName;

        // Move uploaded file
        if (!move_uploaded_file($image['tmp_name'], $uploadPath)) {
            $error_message = "Failed to upload image.";
        }
    }

    // Update user data
    if (empty($error_message)) {
        if ($uploadFileName) {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, profile_image = ? WHERE id = ?");
            $stmt->execute([$name, $email, $uploadFileName, $user_id]);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $email, $user_id]);
        }

        $success_message = "Profile updated successfully!";
    }
}

// Fetch current data
$stmt = $conn->prepare("SELECT username, email, profile_image FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit <?php echo ucfirst($user_type); ?> Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-5" style="max-width: 600px;">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>Edit <?php echo ucfirst($user_type); ?> Profile</h5>
            </div>
            <div class="card-body">
                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3 text-center">
                        <img src="<?= isset($user['profile_image']) ? 'assets/images/' . $user['profile_image'] : 'assets/img/default-avatar.png' ?>"
                            class="rounded-circle"
                            style="width: 100px; height: 100px; object-fit: cover;">
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" name="name" id="name" class="form-control" required value="<?= htmlspecialchars($user['username']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="profile_image" class="form-label">Profile Picture</label>
                        <input type="file" name="profile_image" id="profile_image" class="form-control">
                        <small class="form-text text-muted">Leave blank to keep existing image.</small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="profile.php" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>

</html>