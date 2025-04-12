<?php
session_start();
include '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage_parking.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("DELETE FROM parking_slots WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['success_msg'] = "Parking slot deleted successfully!";
} else {
    $_SESSION['error_msg'] = "Failed to delete parking slot!";
}

$stmt->close();
header("Location: manage_parking.php");
exit();
?>
<?php
session_start();
include '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage_parking.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("DELETE FROM parking_slots WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['success_msg'] = "Parking slot deleted successfully!";
} else {
    $_SESSION['error_msg'] = "Failed to delete parking slot!";
}

$stmt->close();
header("Location: manage_parking.php");
exit();
?>

