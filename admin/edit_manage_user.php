<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('config/config.php');

// Check if user is admin or moderator
if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [1, 2])) {
    header("Location: login.php");
    exit();
}

$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    header("Location: manage_users.php");
    exit();
}

// Fetch user data to edit
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role_id = intval($_POST['role_id']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    if (empty($username) || empty($email) || empty($role_id)) {
        $error = "Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role_id = ?, start_date = ?, end_date = ? WHERE id = ?");
        if ($stmt->execute([$username, $email, $role_id, $start_date, $end_date, $user_id])) {
            $success = "User updated successfully.";
            header("refresh:2;url=manage_users.php");
        } else {
            $error = "Update failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-5">
    <h2>Edit User</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Role</label>
            <select name="role_id" id="role_id" class="form-select" required>
                <option value="2" <?= $user['role_id'] == 2 ? 'selected' : '' ?>>Moderator</option>
                <option value="3" <?= $user['role_id'] == 3 ? 'selected' : '' ?>>User</option>
            </select>
        </div>
        <div class="mb-3" id="start_date_group" style="display:none;">
            <label>Start Date</label>
            <input type="date" name="start_date" id="start_date" class="form-control" value="<?= $user['start_date'] ?>">
        </div>
        <div class="mb-3" id="end_date_group" style="display:none;">
            <label>End Date</label>
            <input type="date" name="end_date" id="end_date" class="form-control" value="<?= $user['end_date'] ?>">
        </div>
        <button type="submit" class="btn btn-primary">Update User</button>
        <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role_id');
            const startDateGroup = document.getElementById('start_date_group');
            const endDateGroup = document.getElementById('end_date_group');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            function toggleDateFields() {
                if (roleSelect.value === '3') { // User
                    startDateGroup.style.display = 'block';
                    endDateGroup.style.display = 'block';
                    startDateInput.required = true;
                    endDateInput.required = true;
                } else {
                    startDateGroup.style.display = 'none';
                    endDateGroup.style.display = 'none';
                    startDateInput.required = false;
                    endDateInput.required = false;
                }
            }
            roleSelect.addEventListener('change', toggleDateFields);
            toggleDateFields(); // Initial call
        });
    </script>
</body>

</html>