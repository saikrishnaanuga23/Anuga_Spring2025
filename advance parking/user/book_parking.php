<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}
include '../config/db_connect.php'; // Database connection

// Get parking ID from URL
$parking_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($parking_id === 0) {
    header("Location: search_parking.php");
    exit();
}

// Fetch parking details
$stmt = $conn->prepare("SELECT * FROM parking_slots WHERE id = ?");
$stmt->bind_param("i", $parking_id);
$stmt->execute();
$result = $stmt->get_result();
$parking = $result->fetch_assoc();
$stmt->close();

if (!$parking) {
    header("Location: search_parking.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if user already booked this parking slot
$check_stmt = $conn->prepare("SELECT id FROM reservations WHERE user_id = ? AND parking_id = ?");
$check_stmt->bind_param("ii", $user_id, $parking_id);
$check_stmt->execute();
$check_stmt->store_result();
$already_booked = $check_stmt->num_rows > 0;
$check_stmt->close();

// Handle booking
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_now']) && !$already_booked) {
    $booking_date = date("Y-m-d H:i:s");
    $status = "Pending";

    $stmt = $conn->prepare("INSERT INTO reservations (user_id, parking_id, booking_date, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $parking_id, $booking_date, $status);
    $stmt->execute();
    $stmt->close();

    header("Location: user_dashboard.php?success=booked");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Book Parking | Advance Parking Finder</title>
    <?php include '../includes/head.php'; ?>
</head>
<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <h5 class="mb-0">Book Parking</h5>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6>Confirm Booking</h6>
                            <p><strong>Parking Name:</strong> <?php echo htmlspecialchars($parking['name']); ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($parking['location']); ?></p>
                            <p><strong>Capacity:</strong> <?php echo htmlspecialchars($parking['capacity']); ?></p>

                            <?php if ($already_booked): ?>
                                <p class="text-warning">You have already booked this parking slot.</p>
                            <?php else: ?>
                                <form method="POST">
                                    <button type="submit" name="book_now" class="btn btn-primary">Confirm Booking</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
