<?php
session_start();
include '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage_parking.php");
    exit();
}

$id = $_GET['id'];
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $latitude = trim($_POST['latitude']);
    $longitude = trim($_POST['longitude']);
    $capacity = (int) $_POST['capacity'];
    $price = (float) $_POST['price'];

    if (!empty($name) && !empty($location) && !empty($latitude) && !empty($longitude) && $capacity > 0 && $price >= 0) {
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
    <title>Edit Parking Slot</title>
    <?php include '../includes/head.php'; ?>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="pc-container">
        <div class="pc-content">
            <h5>Edit Parking Slot</h5>
            <form method="POST">
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($parking['name']); ?>" required>
                <input type="text" id="location" name="location" class="form-control" value="<?php echo htmlspecialchars($parking['location']); ?>" required>
                <input type="hidden" id="latitude" name="latitude" value="<?php echo htmlspecialchars($parking['latitude']); ?>">
                <input type="hidden" id="longitude" name="longitude" value="<?php echo htmlspecialchars($parking['longitude']); ?>">
                <input type="number" name="capacity" class="form-control" value="<?php echo htmlspecialchars($parking['capacity']); ?>" required>
                <input type="number" name="price" class="form-control" value="<?php echo htmlspecialchars($parking['price']); ?>" step="0.01" required>
                <button type="submit" class="btn btn-success">Update Parking</button>
            </form>
            <a href="manage_parking.php" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</body>
</html>
