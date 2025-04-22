<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db_connect.php';

// Handle booking deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_booking'])) {
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;

    if ($booking_id > 0) {
        $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $booking_id);

        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Booking deleted successfully!";
        } else {
            $_SESSION['error_msg'] = "Failed to delete booking: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_msg'] = "Invalid booking ID!";
    }

    header("Location: manage_reservations.php");
    exit();
}


// Fetch bookings
$result = $conn->query("SELECT b.id, u.name AS user_name, p.name AS parking_name, b.created_at AS booking_date, b.status 
                        FROM bookings b 
                        LEFT JOIN users u ON b.user_id = u.id 
                        LEFT JOIN parking_slots p ON b.parking_id = p.id 
                        ORDER BY b.created_at DESC");

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
                                                <?php echo htmlspecialchars($row['status']); ?>
                                            </td>
                                            <td>
                                                <form method="POST" style="display: inline;" onsubmit="return confirmDelete()">
                                                    <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" name="delete_booking" class="btn btn-sm btn-danger">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php } ?>

                                    <?php if ($result->num_rows == 0): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No bookings available.</td>
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
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this booking? This action cannot be undone.");
        }
    </script>
</body>

</html>