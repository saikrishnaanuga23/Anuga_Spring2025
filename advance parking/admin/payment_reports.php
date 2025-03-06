<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
include '../config/db_connect.php'; // Database connection

// Fetch payments data
$result = $conn->query("SELECT p.id, u.name AS user_name, p.amount, p.payment_date, p.status 
                        FROM payments p 
                        JOIN users u ON p.user_id = u.id 
                        ORDER BY p.payment_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Payment Reports | Advance Parking Finder</title>
    <?php include '../includes/head.php'; ?>
</head>
<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <h5 class="mb-0">Payment Reports</h5>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h6>Payments List</h6>
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>User</th>
                                        <th>Amount</th>
                                        <th>Payment Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                            <td>$<?php echo number_format($row['amount'], 2); ?></td>
                                            <td><?php echo date("d M Y, h:i A", strtotime($row['payment_date'])); ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?php 
                                                        echo ($row['status'] == 'Completed') ? 'bg-success' : 
                                                             (($row['status'] == 'Pending') ? 'bg-warning' : 'bg-danger'); 
                                                    ?>">
                                                    <?php echo htmlspecialchars($row['status']); ?>
                                                </span>
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
