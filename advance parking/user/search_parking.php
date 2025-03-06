<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}
include '../config/db_connect.php'; // Database connection

// Fetch available parking slots
$result = $conn->query("SELECT * FROM parking_slots ORDER BY location");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Search Parking | Advance Parking Finder</title>
    <?php include '../includes/head.php'; ?>
</head>
<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <h5 class="mb-0">Search Parking</h5>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h6>Available Parking Spots</h6>
                            <?php if ($result->num_rows > 0) { ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Parking Name</th>
                                            <th>Location</th>
                                            <th>Capacity</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result->fetch_assoc()) { ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                                <td><?php echo htmlspecialchars($row['capacity']); ?></td>
                                                <td>
                                                    <?php if ($row['capacity'] > 0) { ?>
                                                        <a href="book_parking.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Book Now</a>
                                                    <?php } else { ?>
                                                        <span class="text-danger">Fully Booked</span>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            <?php } else { ?>
                                <p class="text-center text-danger">No parking slots available at the moment.</p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
