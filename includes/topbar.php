<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session only if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Debugging: Log session values
if (!isset($_SESSION['role'])) {
    echo "<script>console.error('Role is not set in SESSION');</script>";
} else {
    echo "<script>console.log('User Role: " . $_SESSION['role'] . "');</script>";
}
?>

<!-- Ensure Feather Icons Load Properly -->
<script src="https://unpkg.com/feather-icons"></script>
<script>
    feather.replace(); // Initialize Feather Icons
</script>
