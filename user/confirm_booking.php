<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
include '../config/db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['parking_id'])) {
    $user_id = $_SESSION['user_id'];
    $parking_id = intval($_POST['parking_id']);
    // Start transaction to ensure data consistency
    $conn->begin_transaction();
    try {
        // Fetch parking details
        $stmt = $conn->prepare("SELECT * FROM parking_slots WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $parking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $parking = $result->fetch_assoc();
        if (!$parking || ($parking['capacity'] - $parking['booked_slots']) <= 0) {
            throw new Exception("Parking slot is not available.");
        }
        // Update the booked_slots count
        $stmt = $conn->prepare("UPDATE parking_slots SET booked_slots = booked_slots + 1 WHERE id = ?");
        $stmt->bind_param("i", $parking_id);
        $stmt->execute();
        
        if ($stmt->affected_rows <= 0) {
            throw new Exception("Failed to update parking availability.");
        }
        // Insert booking into database
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, parking_id, status, payment_status) VALUES (?, ?, 'confirmed', 'not_required')");
        $stmt->bind_param("ii", $user_id, $parking_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to confirm parking slot.");
        }
        
        // Get the booking ID for reference
        $booking_id = $conn->insert_id;
        
        // Store booking details in session for use on success page
        $_SESSION['booking_details'] = [
            'parking_name' => $parking['name'],
            'location' => $parking['location'],
            'booking_time' => date('Y-m-d H:i:s'),
            'booking_id' => $booking_id
        ];
        // Commit the transaction
        $conn->commit();
        
        $_SESSION['success_msg'] = "Your parking slot has been successfully booked!";
        header("Location: booking_success.php");  // Redirect to success page
        exit();
        
    } catch (Exception $e) {
        // Roll back the transaction if something failed
        $conn->rollback();
        
        $_SESSION['error_msg'] = $e->getMessage();
        header("Location: book_parking.php?id=$parking_id");
        exit();
    }
} else {
    header("Location: search_parking.php"); 
    exit();
}
?>