<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db_connect.php';

// Fetch bookings
$result = $conn->query("SELECT b.id, u.name AS user_name, p.name AS parking_name, b.created_at AS booking_date, b.status 
                        FROM bookings b 
                        LEFT JOIN users u ON b.user_id = u.id 
                        LEFT JOIN parking_slots p ON b.parking_id = p.id 
                        ORDER BY b.created_at DESC");

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    $new_status = isset($_POST['status']) ? trim($_POST['status']) : '';

    if ($booking_id > 0 && in_array($new_status, ['Pending', 'Confirmed', 'Cancelled'])) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $booking_id);

        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Booking status updated successfully!";
        } else {
            $_SESSION['error_msg'] = "Failed to update status: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_msg'] = "Invalid input!";
    }

    header("Location: manage_reservations.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Bookings | Advance Parking Finder</title>
    <?php include '../includes/head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <h5 class="mb-0">Manage Bookings</h5>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h6>Bookings List</h6>

                            <!-- Display success or error messages -->
                            <?php if (isset($_SESSION['success_msg'])): ?>
                                <script>
                                    Swal.fire({
                                        title: "Success!",
                                        text: "<?php echo $_SESSION['success_msg']; ?>",
                                        icon: "success",
                                        confirmButtonText: "OK"
                                    });
                                </script>
                                <?php unset($_SESSION['success_msg']); ?>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['error_msg'])): ?>
                                <script>
                                    Swal.fire({
                                        title: "Error!",
                                        text: "<?php echo $_SESSION['error_msg']; ?>",
                                        icon: "error",
                                        confirmButtonText: "OK"
                                    });
                                </script>
                                <?php unset($_SESSION['error_msg']); ?>
                            <?php endif; ?>

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
                                            <td><?php echo htmlspecialchars($row['user_name'] ?? 'Unknown User'); ?></td>
                                            <td><?php echo htmlspecialchars($row['parking_name'] ?? 'Unknown Spot'); ?></td>
                                            <td><?php echo htmlspecialchars($row['booking_date']); ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?php echo ($row['status'] == 'Confirmed') ? 'bg-success' : 
                                                               (($row['status'] == 'Reserved') ? 'bg-warning' : 'bg-danger'); ?>">
                                                    <?php echo htmlspecialchars($row['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <form method="POST">
                                                    <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                                    <select name="status" class="form-select">
                                                        <option value="Confirmed" <?php if ($row['status'] == 'Confirmed') echo 'selected'; ?>>Confirmed</option>
                                                        <option value="Cancelled" <?php if ($row['status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                                                    </select>
                                                    <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php } ?>

                                    <?php if ($result->num_rows == 0): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No bookings available.</td>
                                        </tr>
                                    <?php endif; ?>

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
