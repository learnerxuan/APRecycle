<?php

header('Content-Type: application/json');

// API CONFIGURATION 
$GEMINI_API_KEY = 'YOUR GEMINI API KEY'; 
$GEMINI_ENDPOINT = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$GEMINI_API_KEY}";

// Settings for local XAMPP
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'aprecycle';

// Initialize a standard response structure
$response = [
    'status' => 'error',
    'message' => 'Initialization error.',
    'classification' => 'N/A',
    'confidence' => 0.0
];

// Receive and Decode the raw JSON payload from the AJAX request
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!isset($data['image']) || empty($data['image'])) {
    $response['message'] = 'Error: No image data received from the frontend.';
    echo json_encode($response);
    exit;
}

$base64Image = $data['image'];

// Prepare the JSON Payload for the Gemini API
$prompt = "Analyze the image of the waste item. Your response MUST be a JSON object with two fields: 'classification' (the type of item, e.g., Plastic Bottle, Cardboard Box, Organic) and 'confidence_score' (a score from 0.0 to 1.0 representing classification certainty).";

$payload = json_encode([
    'contents' => [
        [
            'parts' => [
                // Text part (the instruction/prompt)
                ['text' => $prompt],
                // Image part (the Base64 image data)
                [
                    'inlineData' => [
                        'mimeType' => 'image/jpeg',
                        'data' => $base64Image,
                    ],
                ],
            ],
        ],
    ],
    // Ensure the model returns structured JSON output
    'generationConfig' => [
        'responseMimeType' => 'application/json',
        'responseSchema' => [
            'type' => 'object',
            'properties' => [
                'classification' => ['type' => 'string'],
                'confidence_score' => ['type' => 'number'],
            ],
        ],
    ]
]);

// Implement cURL to send the request to Gemini
$ch = curl_init($GEMINI_ENDPOINT);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Get the response as a string
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($payload)
]);

// SSL and timeout settings for Windows/WAMP
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$api_response_raw = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($http_code != 200) {
    $error_details = $curl_error ? " cURL Error: {$curl_error}" : "";
    $response['message'] = "API Error. HTTP Status: {$http_code}. Response: {$api_response_raw}{$error_details}";
    echo json_encode($response);
    exit;
}

$api_response = json_decode($api_response_raw, true);

// Extract the structured JSON response from the API result
$classification_data = null;
if (isset($api_response['candidates'][0]['content']['parts'][0]['text'])) {
    // The response text is a JSON string itself (as requested by 'responseMimeType')
    $classification_json_string = $api_response['candidates'][0]['content']['parts'][0]['text'];
    $classification_data = json_decode($classification_json_string, true);
}

// Check if we successfully extracted the required data
if (
    !$classification_data || 
    !isset($classification_data['classification']) || 
    !isset($classification_data['confidence_score'])
) {
    $response['message'] = 'AI could not return a structured classification. Raw AI response received: ' . $api_response_raw;
    echo json_encode($response);
    exit;
}

// Extraction Successful 
$classification = $classification_data['classification'];
$confidence = (float)$classification_data['confidence_score']; // Treated as a number

$response['classification'] = $classification;
$response['confidence'] = $confidence;

$class_lower = strtolower($classification);
$is_valid_waste = true;

if (strpos($class_lower, 'not a waste') !== false || strpos($class_lower, 'non-recyclable') !== false) {
    $is_valid_waste = false;
}

// Always save the image regardless of confidence level
$target_dir = "uploads/";
$file_name = "waste_" . uniqid() . ".jpg";
$target_file_path = $target_dir . $file_name;
$image_data_binary = base64_decode($base64Image);

if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

if (file_put_contents($target_file_path, $image_data_binary)) {

    // Connect Database
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

    if ($conn -> connect_error) {
        $response['status'] = 'error';
        $response['message'] = "Database Connection Failed: ". $conn -> connect_error;
        echo json_encode($response);
        exit;
    }

    $simulated_user_id = 1;
    $simulated_bin_id = 1;

    // Determine status and points based on confidence level
    if ($confidence >= 0.80 && $is_valid_waste) {
        $status = 'Approved';
        $points_awarded = 15;
    } elseif ($confidence >= 0.80 && !$is_valid_waste) {
        // High confidence AND it is NOT waste -> Reject immediately
        $status = 'Rejected';
        $points_awarded = 0;
    } else {
        // Low confidence -> Needs review
        $status = 'Pending';
        $points_awarded = 0;
    }

    $sql_insert = "INSERT INTO recycling_submission (user_id, bin_id, image_url, ai_confidence, status) VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("iisds", $simulated_user_id, $simulated_bin_id, $target_file_path, $confidence, $status);

    if ($stmt->execute()) {

        // Only award points if status is Approved
        if ($points_awarded > 0) {
            $sql_update_points = "UPDATE user SET lifetime_points = lifetime_points + ? WHERE user_id = ?";
            $stmt_points = $conn->prepare($sql_update_points);
            $stmt_points->bind_param("ii", $points_awarded, $simulated_user_id);
            $stmt_points->execute();
            $stmt_points->close();

            $response['status'] = 'success';
            $response['message'] = "Success! Item classified as {$classification}. Saved to DB. +{$points_awarded} points added.";
        } elseif ($status === 'Rejected') {
            $response['status'] = 'error';
            $response['message'] = "Item rejected: This is not a recyclable waste item.";
        } else {
            $response['status'] = 'success';
            $response['message'] = "Item classified as {$classification} with " . round($confidence * 100) . "% confidence. Saved as 'Pending' for manual review.";
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = "Database Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    $response['status'] = 'error';
    $response['message'] = "Failed to save image file.";
}

// Send the final JSON response back to the frontend
echo json_encode($response);

?>
