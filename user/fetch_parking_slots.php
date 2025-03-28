<?php
include '../config/db_connect.php';

if (isset($_GET['location'])) {
    $location = "%" . trim($_GET['location']) . "%";

    $stmt = $conn->prepare("SELECT * FROM parking_slots WHERE location LIKE ?");
    $stmt->bind_param("s", $location);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<table class="table">
                <thead>
                    <tr>
                        <th>Parking Name</th>
                        <th>Location</th>
                        <th>Capacity</th>
                        <th>Available Slots</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>';
        while ($row = $result->fetch_assoc()) {
            $available_slots = $row['capacity'] - $row['booked_slots'];
            echo '<tr>
                    <td>' . htmlspecialchars($row['name']) . '</td>
                    <td>' . htmlspecialchars($row['location']) . '</td>
                    <td>' . htmlspecialchars($row['capacity']) . '</td>
                    <td>' . ($available_slots > 0 ? $available_slots . " available" : "Fully booked") . '</td>
                    <td>$' . htmlspecialchars($row['price']) . '</td>
                    <td>
                        <a href="book_parking.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm">Book Now</a>
                    </td>
                </tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p class="text-center text-danger">No parking slots available in this location.</p>';
    }
    $stmt->close();
}
?>
