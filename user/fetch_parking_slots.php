<?php
include '../config/db_connect.php';

if (isset($_GET['location'])) {
    $location = "%" . trim($_GET['location']) . "%";


    $stmt = $conn->prepare("SELECT p.*, 
        (p.capacity - COALESCE((SELECT COUNT(*) FROM bookings b WHERE b.parking_id = p.id AND b.status IN ('confirmed')), 0)) AS available_slots 
        FROM parking_slots p WHERE p.location LIKE ?"); 


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
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>';
        while ($row = $result->fetch_assoc()) {
            
            echo '<tr>
                    <td>' . htmlspecialchars($row['name']) . '</td>
                    <td>' . htmlspecialchars($row['location']) . '</td>
                    <td>' . htmlspecialchars($row['capacity']) . '</td>
                   <td>' . ($row['available_slots'] > 0 ? $row['available_slots'] . " available" : "Fully booked") . '</td>
                    
                    <td>
                        <a href="book_parking.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm">Book Now</a>
                        <a href="https://www.google.com/maps/dir/?api=1&destination=' . $row['latitude'] . ',' . $row['longitude'] . '" target="_blank" class="btn btn-info btn-sm">Get Directions</a>
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
