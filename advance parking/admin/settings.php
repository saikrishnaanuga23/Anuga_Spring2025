<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
include '../config/db_connect.php'; // Database connection

$admin_id = $_SESSION['user_id'];
$error = "";

// Handle password update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Fetch stored password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->bind_result($stored_password);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($current_password, $stored_password)) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) < 6) {
                $error = "New password must be at least 6 characters.";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $hashed_password, $admin_id);
                if ($update_stmt->execute()) {
                    header("Location: settings.php?success");
                    exit();
                } else {
                    $error = "Error updating password. Try again.";
                }
                $update_stmt->close();
            }
        } else {
            $error = "New passwords do not match.";
        }
    } else {
        $error = "Current password is incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Settings | Advance Parking Finder</title>
    <?php include '../includes/head.php'; ?>
</head>
<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <h5 class="mb-0">Admin Settings</h5>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6>Change Password</h6>
                            <?php if (!empty($error)) echo "<p class='text-danger'>$error</p>"; ?>
                            <?php if (isset($_GET['success'])) echo "<p class='text-success'>Password updated successfully!</p>"; ?>
                            <form method="POST">
                                <div class="form-group mb-3">
                                    <label>Current Password</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label>New Password</label>
                                    <input type="password" name="new_password" class="form-control" required minlength="6">
                                    <small class="text-muted">Minimum 6 characters.</small>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Confirm Password</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                                <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
