<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}
include '../config/db_connect.php'; // Database connection

// Get parking ID from URL
$parking_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch parking details
$stmt = $conn->prepare("SELECT * FROM parking_slots WHERE id = ?");
$stmt->bind_param("i", $parking_id);
$stmt->execute();
$result = $stmt->get_result();
$parking = $result->fetch_assoc();
$stmt->close();

// Redirect if parking slot doesn't exist
if (!$parking) {
    header("Location: search_parking.php?error=not_found");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Parking Details | Advance Parking Finder</title>
    <?php include '../includes/head.php'; ?>
</head>
<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <h5 class="mb-0">Parking Details</h5>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h6><?php echo htmlspecialchars($parking['name']); ?></h6>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($parking['location']); ?></p>
                            <p><strong>Capacity:</strong> <?php echo htmlspecialchars($parking['capacity']); ?></p>
                            <a href="book_parking.php?id=<?php echo $parking['id']; ?>" class="btn btn-primary">Book Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
