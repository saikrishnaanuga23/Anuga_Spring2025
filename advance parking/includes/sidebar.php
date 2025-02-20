<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session only if it's not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Debugging: Log session values
if (!isset($_SESSION['role'])) {
    echo "<script>console.error('Role is not set in SESSION');</script>";
} else {
    echo "<script>console.log('User Role: " . $_SESSION['role'] . "');</script>";
}
?>
<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="#" class="b-brand text-primary">
                <img src="../assets/images/favicon.svg" class="img-fluid logo-lg" alt="logo" />
            </a>
        </div>
        <div class="navbar-content">
            <ul class="pc-navbar">
                <li class="pc-item">
                    <a href="#" class="pc-link">
                        <span class="pc-micon"><i data-feather="home"></i></span>
                        <span class="pc-mtext">Dashboard</span>
                    </a>
                </li>

                <?php if ($_SESSION['role'] === 'admin') { ?>
                    <li class="pc-item">
                        <a href="../admin/manage_parking.php" class="pc-link">
                            <span class="pc-micon"><i data-feather="map"></i></span>
                            <span class="pc-mtext">Manage Parking</span>
                        </a>
                    </li>
                    <li class="pc-item">
                        <a href="../admin/manage_reservations.php" class="pc-link">
                            <span class="pc-micon"><i data-feather="calendar"></i></span>
                            <span class="pc-mtext">Reservations</span>
                        </a>
                    </li>
                    <li class="pc-item">
                        <a href="../admin/manage_users.php" class="pc-link">
                            <span class="pc-micon"><i data-feather="users"></i></span>
                            <span class="pc-mtext">Manage Users</span>
                        </a>
                    </li>
                <?php } else { ?>
                    <li class="pc-item">
                        <a href="../user/search_parking.php" class="pc-link">
                            <span class="pc-micon"><i data-feather="search"></i></span>
                            <span class="pc-mtext">Search Parking</span>
                        </a>
                    </li>
                    <li class="pc-item">
                        <a href="../user/booking_history.php" class="pc-link">
                            <span class="pc-micon"><i data-feather="clock"></i></span>
                            <span class="pc-mtext">Booking History</span>
                        </a>
                    </li>
                <?php } ?>
                
                <li class="pc-item">
                    <a href="../auth/logout.php" class="pc-link">
                        <span class="pc-micon"><i data-feather="log-out"></i></span>
                        <span class="pc-mtext">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Ensure Feather Icons Load Properly -->
<script src="https://unpkg.com/feather-icons"></script>
<script>
    feather.replace(); // Initialize Feather Icons
</script>
