<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
include '../config/db_connect.php'; // Database connection
// Fetch users
$result = $conn->query("SELECT id, name, email, role FROM users ORDER BY role, name ASC");
// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $user_id = (int) $_POST['user_id'];
    
    // Start transaction to ensure data consistency
    $conn->begin_transaction();
    
    try {
        // First, get all parking slots booked by this user
        $stmt = $conn->prepare("SELECT parking_id FROM bookings WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $bookingsResult = $stmt->get_result();
        
        // Count bookings per parking slot
        $parkingCounts = [];
        while ($booking = $bookingsResult->fetch_assoc()) {
            $parking_id = $booking['parking_id'];
            if (!isset($parkingCounts[$parking_id])) {
                $parkingCounts[$parking_id] = 1;
            } else {
                $parkingCounts[$parking_id]++;
            }
        }
        $stmt->close();
        
        // Update each parking slot to free up the booked slots
        foreach ($parkingCounts as $parking_id => $count) {
            $updateStmt = $conn->prepare("UPDATE parking_slots SET booked_slots = GREATEST(0, booked_slots - ?) WHERE id = ?");
            $updateStmt->bind_param("ii", $count, $parking_id);
            $updateStmt->execute();
            $updateStmt->close();
        }
        
        // Delete all bookings by this user
        $deleteBookingsStmt = $conn->prepare("DELETE FROM bookings WHERE user_id = ?");
        $deleteBookingsStmt->bind_param("i", $user_id);
        $deleteBookingsStmt->execute();
        $deleteBookingsStmt->close();
        
        // Finally, delete the user
        $deleteUserStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $deleteUserStmt->bind_param("i", $user_id);
        $deleteUserStmt->execute();
        $deleteUserStmt->close();
        
        // Commit the transaction
        $conn->commit();
        
        $_SESSION['success_msg'] = "User deleted successfully! Any booked parking slots have been freed up.";
    } catch (Exception $e) {
        // If there's an error, roll back the transaction
        $conn->rollback();
        $_SESSION['error_msg'] = "Error deleting user: " . $e->getMessage();
    }
    header("Location: manage_users.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Users | Advance Parking Finder</title>
    <?php include '../includes/head.php'; ?>
</head>
<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <h5 class="mb-0">Manage Users</h5>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h6>Users List</h6>
                            <?php 
                                if (isset($_SESSION['success_msg'])) {
                                    echo "<div class='alert alert-success'>{$_SESSION['success_msg']}</div>";
                                    unset($_SESSION['success_msg']);
                                }
                                if (isset($_SESSION['error_msg'])) {
                                    echo "<div class='alert alert-danger'>{$_SESSION['error_msg']}</div>";
                                    unset($_SESSION['error_msg']);
                                }
                            ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result->fetch_assoc()) { 
                                            // Get booking count for this user
                                            $bookingStmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE user_id = ?");
                                            $bookingStmt->bind_param("i", $row['id']);
                                            $bookingStmt->execute();
                                            $bookingResult = $bookingStmt->get_result();
                                            $bookingCount = $bookingResult->fetch_assoc()['count'];
                                            $bookingStmt->close();
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td><?php echo htmlspecialchars(ucfirst($row['role'])); ?></td>
                                                <td>
                                                    <?php if ($row['role'] !== 'admin' || $_SESSION['user_id'] != $row['id']) { ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                                            <button type="submit" name="delete_user" class="btn btn-sm btn-danger" 
                                                                    onclick="return confirm('Are you sure you want to delete this user? All their bookings will be canceled and slots will be freed up.');">
                                                                <i class="feather icon-trash me-1"></i>Delete
                                                            </button>
                                                        </form>
                                                    <?php } else { ?>
                                                        <button class="btn btn-sm btn-secondary" disabled>Cannot delete</button>
                                                    <?php } ?>
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
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
