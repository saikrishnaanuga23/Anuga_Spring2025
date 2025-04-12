<?php
session_start();
ini_set('display_errors', 1);  // Enable error reporting
error_reporting(E_ALL);        // Report all errors

include '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['parking_id'])) {
    $user_id = $_SESSION['user_id'];
    $parking_id = intval($_POST['parking_id']);

    // Fetch parking details
    $stmt = $conn->prepare("SELECT * FROM parking_slots WHERE id = ?");
    $stmt->bind_param("i", $parking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $parking = $result->fetch_assoc();

    if (!$parking || ($parking['capacity'] - $parking['booked_slots']) <= 0) {
        $_SESSION['error_msg'] = "Parking slot is not available.";
        header("Location: search_parking.php");
        exit();
    }

    // Insert booking into database with confirmed status
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, parking_id, status) VALUES (?, ?, 'confirmed')");
    $stmt->bind_param("ii", $user_id, $parking_id);
    if ($stmt->execute()) {
        // Update the parking slot's booked_slots count
        $stmt = $conn->prepare("UPDATE parking_slots SET booked_slots = booked_slots + 1 WHERE id = ?");
        $stmt->bind_param("i", $parking_id);
        $stmt->execute();
        
        $_SESSION['success_msg'] = "Your parking slot has been successfully booked!";
        header("Location: booking_success.php");
        exit();
    } else {
        $_SESSION['error_msg'] = "Failed to book parking slot.";
        header("Location: search_parking.php");
        exit();
    }
} else {
    header("Location: search_parking.php");
    exit();
}
?>