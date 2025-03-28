<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db_connect.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: Parking slot ID is missing.");
}

$parking_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM parking_slots WHERE id = ?");
$stmt->bind_param("i", $parking_id);
$stmt->execute();
$result = $stmt->get_result();
$parking = $result->fetch_assoc();

if (!$parking) {
    die("Error: Parking slot not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Book Parking | Advance Parking Finder</title>
    <?php include '../includes/head.php'; ?>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>

    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <h5 class="mb-0">Book Parking Slot</h5>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h6>Parking Slot Details</h6>
                            <p><strong>Parking Name:</strong> <?php echo htmlspecialchars($parking['name']); ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($parking['location']); ?></p>
                           
                            
                            <form action="confirm_booking.php" method="POST">
                                <input type="hidden" name="parking_id" value="<?php echo $parking_id; ?>">
                                <button type="submit" class="btn btn-success">Confirm Booking</button>
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
