<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}
include '../config/db_connect.php'; // Database connection

// Fetch user's booking details (last 3 bookings)
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT p.name AS parking_name, b.created_at AS booking_date, b.status 
    FROM bookings b 
    JOIN parking_slots p ON b.parking_id = p.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
    LIMIT 3
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>User Dashboard | Advance Parking Finder</title>
    <?php include '../includes/head.php'; ?>
</head>

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <h5 class="mb-0">User Dashboard</h5>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h6>Recent Bookings</h6>
                            <?php if (!empty($bookings)) { ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Parking Name</th>
                                            <th>Booking Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bookings as $booking) { ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($booking['parking_name']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                                                <td>
                                                    <span class="badge 
        <?php
                                            if ($booking['status'] === 'confirmed') {
                                                echo 'bg-success'; // Green for confirmed
                                            } elseif ($booking['status'] === 'cancelled') {
                                                echo 'bg-danger'; // Red for cancelled
                                            } else {
                                                echo 'bg-warning'; // Yellow for reserved
                                            }
        ?>">
                                                        <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
                                                    </span>
                                                </td>

                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            <?php } else { ?>
                                <p class="text-center text-danger">No recent bookings found.</p>
                            <?php } ?>
                            <a href="search_parking.php" class="btn btn-primary mt-3">Find Parking</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>

</html>