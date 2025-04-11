<?php
session_start();
include '../config/db_connect.php'; // Database connection

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's booking history (Ensure correct table & column names)
$stmt = $conn->prepare("
    SELECT p.name AS parking_name, b.created_at AS booking_date, b.status 
    FROM bookings b 
    JOIN parking_slots p ON b.parking_id = p.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();


// Debugging: Check if data is being fetched
// var_dump($bookings); exit();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Booking History | Advance Parking Finder</title>
    <?php include '../includes/head.php'; ?>
</head>
<body>
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
        <?php 
            $status = strtolower($booking['status']); // Convert status to lowercase for consistency
            if ($status === 'confirmed') {
                echo 'bg-success'; // Green for confirmed
            } elseif ($status === 'reserved') {
                echo 'bg-warning'; // Yellow for reserved
            } elseif ($status === 'cancelled') {
                echo 'bg-danger'; // Red for cancelled
            } else {
                echo 'bg-secondary'; // Grey for unknown status
            }
        ?>">
        <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
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
