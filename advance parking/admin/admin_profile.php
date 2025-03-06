<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
include '../config/db_connect.php'; // Database connection

$admin_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($name, $email);
$stmt->fetch();
$stmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $new_name = trim($_POST['name']);
    $new_email = trim($_POST['email']);
    
    if (!empty($new_name) && !empty($new_email)) {
        $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $new_name, $new_email, $admin_id);
        if ($update_stmt->execute()) {
            $_SESSION['success_msg'] = "Profile updated successfully!";
        } else {
            $_SESSION['error_msg'] = "Profile update failed!";
        }
        $update_stmt->close();
        header("Location: admin_profile.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Profile | Advance Parking Finder</title>
    <?php include '../includes/head.php'; ?>
</head>
<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <h5 class="mb-0">Admin Profile</h5>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6>Update Profile</h6>
                            <?php 
                                if (isset($_SESSION['success_msg'])) {
                                    echo "<p class='text-success'>{$_SESSION['success_msg']}</p>";
                                    unset($_SESSION['success_msg']);
                                }
                                if (isset($_SESSION['error_msg'])) {
                                    echo "<p class='text-danger'>{$_SESSION['error_msg']}</p>";
                                    unset($_SESSION['error_msg']);
                                }
                            ?>
                            <form method="POST">
                                <div class="form-group mb-3">
                                    <label>Name</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
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
