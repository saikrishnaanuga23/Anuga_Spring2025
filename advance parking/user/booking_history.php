<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}
include '../config/db_connect.php'; // Database connection

// Fetch user's booking history
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT p.name AS parking_name, r.booking_date, r.status FROM reservations r JOIN parking_slots p ON r.parking_id = p.id WHERE r.user_id = ? ORDER BY r.booking_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Booking History | Advance Parking Finder</title>
    <?php include '../includes/head.php'; ?>
</head>
<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <h5 class="mb-0">Booking History</h5>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h6>Your Past Bookings</h6>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Parking Name</th>
                                        <th>Booking Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($bookings)) { ?>
                                        <?php foreach ($bookings as $booking) { ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($booking['parking_name']); ?></td>
                                                <td><?php echo date("d M Y, H:i A", strtotime($booking['booking_date'])); ?></td>
                                                <td>
                                                    <span class="badge 
                                                        <?php echo ($booking['status'] == 'Confirmed') ? 'bg-success' : 
                                                                   (($booking['status'] == 'Pending') ? 'bg-warning' : 'bg-danger'); ?>">
                                                        <?php echo htmlspecialchars($booking['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No booking history available.</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
