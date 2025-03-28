<?php
session_start();
include '../config/db_connect.php';

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Validate and sanitize parking slot ID
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $_SESSION['error_msg'] = "Invalid parking slot ID!";
    header("Location: manage_parking.php");
    exit();
}

$id = intval($_GET['id']);

// Fetch parking slot details
$stmt = $conn->prepare("SELECT * FROM parking_slots WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$parking = $result->fetch_assoc();
$stmt->close();

if (!$parking) {
    $_SESSION['error_msg'] = "Parking slot not found!";
    header("Location: manage_parking.php");
    exit();
}

// CSRF Token Generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['csrf_token'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_msg'] = "Invalid CSRF token!";
        header("Location: edit_parking.php?id=$id");
        exit();
    }

    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $latitude = trim($_POST['latitude']);
    $longitude = trim($_POST['longitude']);
    $capacity = filter_var($_POST['capacity'], FILTER_VALIDATE_INT);
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);

    if ($name && $location && $latitude && $longitude && $capacity > 0 && $price >= 0) {
        $stmt = $conn->prepare("UPDATE parking_slots SET name=?, location=?, latitude=?, longitude=?, capacity=?, price=? WHERE id=?");
        $stmt->bind_param("sssdidi", $name, $location, $latitude, $longitude, $capacity, $price, $id);

        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Parking slot updated successfully!";
        } else {
            $_SESSION['error_msg'] = "Failed to update parking slot!";
        }
        $stmt->close();
        header("Location: manage_parking.php");
        exit();
    } else {
        $_SESSION['error_msg'] = "Please fill in all fields correctly!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Parking Slot | Admin Panel</title>
    <?php include '../includes/head.php'; ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&libraries=places"></script>
    <script>
        function initAutocomplete() {
            let input = document.getElementById('location');
            let autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.addListener('place_changed', function () {
                let place = autocomplete.getPlace();
                if (!place.geometry) return;
                document.getElementById('latitude').value = place.geometry.location.lat();
                document.getElementById('longitude').value = place.geometry.location.lng();
            });
        }
        window.onload = initAutocomplete;
    </script>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="pc-container">
        <div class="pc-content">
            <h5>Edit Parking Slot</h5>

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
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group mb-3">
                    <label>Parking Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($parking['name']); ?>" required>
                </div>
                
                <div class="form-group mb-3">
                    <label>Location</label>
                    <input type="text" id="location" name="location" class="form-control" value="<?php echo htmlspecialchars($parking['location']); ?>" required>
                </div>
                
                <input type="hidden" id="latitude" name="latitude" value="<?php echo htmlspecialchars($parking['latitude']); ?>">
                <input type="hidden" id="longitude" name="longitude" value="<?php echo htmlspecialchars($parking['longitude']); ?>">

                <div class="form-group mb-3">
                    <label>Capacity</label>
                    <input type="number" name="capacity" class="form-control" value="<?php echo htmlspecialchars($parking['capacity']); ?>" required>
                </div>

                <!-- <div class="form-group mb-3">
                    <label>Price</label>
                    <input type="number" name="price" class="form-control" value="<?php echo htmlspecialchars($parking['price']); ?>" step="0.01" required>
                </div> -->

                <button type="submit" class="btn btn-success">Update Parking</button>
                <a href="manage_parking.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>
