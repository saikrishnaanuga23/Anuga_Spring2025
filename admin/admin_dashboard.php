<?php 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Regenerate session ID for security
session_regenerate_id(true);

include '../config/db_connect.php'; // Secure database connection

// Validate database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch summary statistics securely
$stmt_users = $conn->prepare("SELECT COUNT(*) FROM users WHERE role='user'");
$stmt_users->execute();
$stmt_users->bind_result($total_users);
$stmt_users->fetch();
$stmt_users->close();

$stmt_bookings = $conn->prepare("SELECT COUNT(*) FROM reservations");
$stmt_bookings->execute();
$stmt_bookings->bind_result($total_bookings);
$stmt_bookings->fetch();
$stmt_bookings->close();

$stmt_slots = $conn->prepare("SELECT COUNT(*) FROM parking_slots WHERE status = 'available'");
$stmt_slots->execute();
$stmt_slots->bind_result($total_parking_slots);
$stmt_slots->fetch();
$stmt_slots->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard | Advance Parking Finder</title>
    <?php include '../includes/head.php'; ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB7KZI4jZkAPvVeaxEKvYF62Kf3fFQg44Q&libraries=places"></script>
</head>
<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <h5 class="mb-0">Admin Dashboard</h5>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6>Total Users</h6>
                            <h3><?php echo htmlspecialchars($total_users); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6>Total Bookings</h6>
                            <h3><?php echo htmlspecialchars($total_bookings); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6>Available Parking Slots</h6>
                            <h3><?php echo htmlspecialchars($total_parking_slots); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Google Maps Integration -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h6>Parking Locations</h6>
                            <div id="map" style="height: 400px; width: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Parking Spot Management -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h6>Manage Parking Spots</h6>
                            <a href="add_parking.php" class="btn btn-primary">Add Parking Spot</a>
                            <a href="manage_parking.php" class="btn btn-secondary">Edit/Delete Parking</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <?php include '../includes/footer.php'; ?>

    <script>
        function initMap() {
            var map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: 37.7749, lng: -122.4194 },
                zoom: 12
            });

            // Fetch parking spots from database
            fetch('get_parking_spots.php')
                .then(response => response.json())
                .then(data => {
                    data.forEach(spot => {
                        new google.maps.Marker({
                            position: { lat: parseFloat(spot.lat), lng: parseFloat(spot.lng) },
                            map: map,
                            title: spot.name
                        });
                    });
                })
                .catch(error => console.error('Error fetching parking spots:', error));
        }

        window.onload = initMap;
    </script>
</body>
</html>
