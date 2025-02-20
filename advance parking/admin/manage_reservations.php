<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
include '../config/db_connect.php'; // Database connection

// Fetch reservations
$result = $conn->query("SELECT r.id, u.name AS user_name, p.name AS parking_name, r.booking_date, r.status 
                        FROM reservations r 
                        JOIN users u ON r.user_id = u.id 
                        JOIN parking_slots p ON r.parking_id = p.id 
                        ORDER BY r.booking_date DESC");

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $reservation_id = (int) $_POST['reservation_id'];
    $new_status = trim($_POST['status']);

    if (!empty($reservation_id) && in_array($new_status, ['Pending', 'Confirmed', 'Cancelled'])) {
        $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $reservation_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Reservation status updated successfully!";
        } else {
            $_SESSION['error_msg'] = "Failed to update status!";
        }
        $stmt->close();
        header("Location: manage_reservations.php");
        exit();
    } else {
        $_SESSION['error_msg'] = "Invalid input!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Reservations | Advance Parking Finder</title>
    <?php include '../includes/head.php'; ?>
</head>
<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <h5 class="mb-0">Manage Reservations</h5>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h6>Reservations List</h6>
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
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Parking Spot</th>
                                        <th>Booking Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['parking_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['booking_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="reservation_id" value="<?php echo $row['id']; ?>">
                                                    <select name="status" class="form-select d-inline w-auto">
                                                        <option value="Pending" <?php if ($row['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                                        <option value="Confirmed" <?php if ($row['status'] == 'Confirmed') echo 'selected'; ?>>Confirmed</option>
                                                        <option value="Cancelled" <?php if ($row['status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                                                    </select>
                                                    <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                                                </form>
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
</body>
</html>
