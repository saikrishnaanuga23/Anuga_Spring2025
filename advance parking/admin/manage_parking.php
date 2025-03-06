<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
include '../config/db_connect.php'; // Database connection

// Handle add parking slot
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_parking'])) {
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $capacity = (int) $_POST['capacity'];

    if (!empty($name) && !empty($location) && $capacity > 0) {
        $stmt = $conn->prepare("INSERT INTO parking_slots (name, location, capacity) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $location, $capacity);
        
        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Parking slot added successfully!";
        } else {
            $_SESSION['error_msg'] = "Failed to add parking slot!";
        }
        $stmt->close();
        header("Location: manage_parking_slots.php");
        exit();
    } else {
        $_SESSION['error_msg'] = "Please fill in all fields!";
    }
}

// Fetch parking slots
$result = $conn->query("SELECT * FROM parking_slots ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Parking Slots | Advance Parking Finder</title>
    <?php include '../includes/head.php'; ?>
</head>
<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
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
                                    <div class="col-md-4">
                                        <input type="text" name="name" class="form-control" placeholder="Parking Name" required>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="location" class="form-control" placeholder="Location" required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="capacity" class="form-control" placeholder="Capacity" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" name="add_parking" class="btn btn-primary">Add Parking</button>
                                    </div>
                                </div>
                            </form>
                            <h6 class="mt-4">Parking Slots</h6>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Location</th>
                                        <th>Capacity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                                            <td><?php echo htmlspecialchars($row['capacity']); ?></td>
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
