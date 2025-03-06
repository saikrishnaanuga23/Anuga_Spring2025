<?php
// auth/logout.php - Logout Script
session_start();
session_unset();
session_destroy();
header("Location: ../home");
exit();
?>
