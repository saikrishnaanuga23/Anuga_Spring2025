<?php
session_start();
if (!isset($_SESSION['success_msg'])) {
    header("Location: search_parking.php");
    exit();
}
$success_msg = $_SESSION['success_msg'];
unset($_SESSION['success_msg']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Booking Successful</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        Swal.fire({
            title: "Booking Confirmed 🎉",
            text: "<?php echo $success_msg; ?>",
            icon: "success",
            confirmButtonText: "OK"
        }).then(() => {
            window.location.href = "booking_history.php"; // Redirect to booking history
        });
    </script>
</body>
</html>
