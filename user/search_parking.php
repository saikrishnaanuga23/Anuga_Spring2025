<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}
include '../config/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Search & Compare Parking | Advance Parking Finder</title>
    <?php include '../includes/head.php'; ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB7KZI4jZkAPvVeaxEKvYF62Kf3fFQg44Q&libraries=places"></script>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>

    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <h5 class="mb-0">Search & Compare Parking</h5>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h6>Find Parking Near You</h6>
                            <input type="text" id="search-location" class="form-control mb-3" placeholder="Enter a location">
                            <div id="map" style="height: 400px;"></div>

                            <h6 class="mt-4">Available Parking Spots</h6>
                            <div id="parking-results">
                                <p class="text-center">Start typing a location to search for parking spots.</p>
                            </div>

                            <!-- <h6 class="mt-4">Price Comparison (Low to High)</h6>
                            <div id="price-comparison">
                                <p class="text-center">Enter a location to compare parking prices.</p>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        let map, marker;

        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: 35.50008, lng: -97.55392 }, 
                zoom: 13
            });

            let input = document.getElementById('search-location');
            let autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.bindTo('bounds', map);

            autocomplete.addListener('place_changed', function () {
                let place = autocomplete.getPlace();
                if (!place.geometry) return;

                if (marker) marker.setMap(null);

                map.setCenter(place.geometry.location);
                map.setZoom(15);

                marker = new google.maps.Marker({
                    map: map,
                    position: place.geometry.location
                });

                let locationName = place.name || place.formatted_address;
                fetchParkingSlots(locationName);
                fetchPriceComparison(locationName);
            });

            document.getElementById('search-location').addEventListener('keyup', function () {
                let location = this.value.trim();
                if (location.length > 2) {
                    fetchParkingSlots(location);
                    fetchPriceComparison(location);
                }
            });
        }

        function fetchParkingSlots(location) {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "fetch_parking_slots.php?location=" + encodeURIComponent(location), true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById("parking-results").innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }

        function fetchPriceComparison(location) {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "fetch_price_comparison.php?location=" + encodeURIComponent(location), true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById("price-comparison").innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }

        google.maps.event.addDomListener(window, 'load', initMap);
    </script>
</body>
</html>

