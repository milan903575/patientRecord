<?php

// Get the location query from the request
$location_query = isset($_GET['location_query']) ? $_GET['location_query'] : '';

// Google Places API Key (Replace with your actual API key)
$google_api_key = 'YOUR_GOOGLE_API_KEY';  // Replace with your API key

// Google Places API endpoint for the autocomplete search
$google_places_url = "https://maps.googleapis.com/maps/api/place/autocomplete/json";

// Ensure the query is not empty
if (!empty($location_query)) {
    // Prepare the URL for the API request
    $url = $google_places_url . "?input=" . urlencode($location_query) . "&key=" . $google_api_key;

    // Send the request to the Google Places API
    $response = file_get_contents($url);

    // Check if the API request was successful
    if ($response !== FALSE) {
        $data = json_decode($response, true);
        
        // Check if predictions were found
        if (isset($data['predictions']) && count($data['predictions']) > 0) {
            foreach ($data['predictions'] as $prediction) {
                // Display each prediction (formatted as you want)
                echo '<div class="location-option">' . htmlspecialchars($prediction['description']) . '</div>';
            }
        } else {
            echo 'No locations found.';
        }
    } else {
        echo 'Error: Could not fetch data from Google Places API.';
    }
} else {
    echo 'No search query provided.';
}
?>
