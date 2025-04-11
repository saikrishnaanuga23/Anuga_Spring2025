<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
include '../config/db_connect.php'; // Database 

// Handle add parking slot
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_parking'])) {
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $latitude = trim($_POST['latitude']);
    $longitude = trim($_POST['longitude']);
    $capacity = (int) $_POST['capacity'];
    $price = (int) $_POST['price'];

    if (!empty($name) && !empty($location) && !empty($latitude) && !empty($longitude) && $capacity > 0) {
        $stmt = $conn->prepare("INSERT INTO parking_slots (name, location, latitude, longitude, capacity, price) VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($stmt) {
            $stmt->bind_param("ssddii", $name, $location, $latitude, $longitude, $capacity, $price);
            
            if ($stmt->execute()) {
                $_SESSION['success_msg'] = "Parking slot added successfully!";
            } else {
                $_SESSION['error_msg'] = "Failed to add parking slot!";
            }
            $stmt->close();
        } else {
            $_SESSION['error_msg'] = "SQL Error: " . $conn->error;
        }

        header("Location: manage_parking.php");
        exit();
    } else {
        $_SESSION['error_msg'] = "Please fill in all fields!";
    }
}

// Fetch parking slots along with available slots calculation
// $query = "SELECT p.id, p.name, p.location, p.latitude, p.longitude, p.capacity, p.price, 
//                  (p.capacity - COALESCE((SELECT COUNT(*) FROM reservations r WHERE r.parking_id = p.id AND r.status = 'Confirmed'), 0)) AS available_slots 
//           FROM parking_slots p 
//           ORDER BY p.id DESC";
// $result = $conn->query($query);

$query = "SELECT p.id, p.name, p.location, p.latitude, p.longitude, p.capacity, p.price, 
                 (p.capacity - COALESCE((SELECT COUNT(*) FROM bookings b WHERE b.parking_id = p.id AND b.status IN ('confirmed')), 0)) AS available_slots 
          FROM parking_slots p 
          ORDER BY p.id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Parking Slots | Advance Parking Finder</title>
    <?php include '../includes/head.php'; ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB7KZI4jZkAPvVeaxEKvYF62Kf3fFQg44Q&libraries=places"></script>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <h5 class="mb-0">Manage Parking Slots</h5>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h6>Add New Parking Slot</h6>
                            <?php 
                                if (isset($_SESSION['success_msg'])) {
                                    echo "<p class='text-success'>{$_SESSION['success_msg']}</p>";
                                    unset($_SESSION['success_msg']);
                                }
                                if (isset($_SESSION['error_msg'])) {
                                    echo "<p class='text-danger'>{$_SESSION['error_msg']}</p>";
                                    unset($_SESSION['error_msg']);
                                }
                            ?>
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-3">
                                        <input type="text" name="name" class="form-control" placeholder="Parking Name" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" id="location" name="location" class="form-control" placeholder="Location" required>
                                    </div>
                                    <input type="hidden" id="latitude" name="latitude">
                                    <input type="hidden" id="longitude" name="longitude">
                                    <div class="col-md-2">
                                        <input type="number" name="capacity" class="form-control" placeholder="Capacity" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" name="add_parking" class="btn btn-primary">Add Parking</button>
                                    </div>
                                </div>
                            </form>
                            <div id="map" style="height: 400px; margin-top: 20px;"></div>
                            <h6 class="mt-4">Parking Slots</h6>
                            <table class="table table-bordered table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Name</th>
                                        <th>Location</th>
                                        <th>Latitude</th>
                                        <th>Longitude</th>
                                        <th>Capacity</th>
                                        <th>Available Slots</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                                            <td><?php echo htmlspecialchars($row['latitude']); ?></td>
                                            <td><?php echo htmlspecialchars($row['longitude']); ?></td>
                                            <td><?php echo htmlspecialchars($row['capacity']); ?></td>
                                            <td><?php echo max(0, $row['available_slots']); ?></td> <!-- Prevent negative values -->
                                            <td>
                                                <a href="edit_parking.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                                <a href="delete_parking.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                                            </td>
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
    <script>
        function initMap() {
            var map = new google.maps.Map(document.getElementById('map'), {
                center: {lat: 35.50008, lng: -97.55392},
                zoom: 13
            });
            var input = document.getElementById('location');
            var autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.bindTo('bounds', map);
            var marker = new google.maps.Marker({
                map: map,
                anchorPoint: new google.maps.Point(0, -29)
            });
            autocomplete.addListener('place_changed', function () {
                var place = autocomplete.getPlace();
                if (!place.geometry) {
                    return;
                }
                map.setCenter(place.geometry.location);
                map.setZoom(17);
                marker.setPosition(place.geometry.location);
                marker.setVisible(true);
                document.getElementById('latitude').value = place.geometry.location.lat();
                document.getElementById('longitude').value = place.geometry.location.lng();
            });
        }
        google.maps.event.addDomListener(window, 'load', initMap);
    </script>
</body>
</html>
