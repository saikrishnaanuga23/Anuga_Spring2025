<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session only if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
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
<header class="pc-header">
    <div class="header-wrapper">
        <div class="me-auto pc-mob-drp">
            <ul class="list-unstyled">
                <li class="pc-h-item pc-sidebar-collapse">
                    <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
                        <i data-feather="menu"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="ms-auto">
            <ul class="list-unstyled">
                <li class="dropdown pc-h-item header-user-profile">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <i data-feather="user"></i>
                    </a>
                    <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown p-0 overflow-hidden">
                        <div class="dropdown-header d-flex align-items-center justify-content-between bg-primary">
                            <div class="d-flex my-2">
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-white mb-1">
                                        <?php echo isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) . " Dashboard" : "User Dashboard"; ?>
                                    </h6>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-body">
                            <div class="profile-notification-scroll position-relative" style="max-height: calc(100vh - 225px)">
                                <a href="<?php echo ($_SESSION['role'] === 'admin') ? '../admin/admin_profile.php' : '../user/user_profile.php'; ?>" class="dropdown-item">
                                    <span>
                                        <i data-feather="settings"></i>
                                        <span>Profile</span>
                                    </span>
                                </a>
                                <div class="d-grid my-2">
                                    <a href="../auth/logout.php" class="btn btn-primary">
                                        <i data-feather="log-out"></i> Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>

<!-- Ensure Feather Icons Load Properly -->
<script src="https://unpkg.com/feather-icons"></script>
<script>
    feather.replace(); // Initialize Feather Icons
</script>
