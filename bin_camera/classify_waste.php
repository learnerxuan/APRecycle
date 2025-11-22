<?php

header('Content-Type: application/json');

// API CONFIGURATION 
$GEMINI_API_KEY = 'AIzaSyDcR19QkSnIFxrmo4W5sFvk2a3GOUE7c64'; 
$GEMINI_ENDPOINT = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$GEMINI_API_KEY}";

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

if ($confidence >= 0.80) {
    // Condition PASSES: Item is accepted for recycling and points are calculated.
    $points_awarded = 15; /
    
    // --- Phase 3 (DB Update) will go here ---
    
    $response['status'] = 'success';
    $response['message'] = "Classification successful! Item is: {$classification}. Awarded {$points_awarded} points. Confidence: " . round($confidence * 100) . "%.";
    
} else {
    $response['status'] = 'error';
    $response['message'] = "Classification failed! Confidence is too low (" . round($confidence * 100) . "%). The item was classified as '{$classification}'. Please try again with a clearer image or classify manually.";
}

// Send the final JSON response back to the frontend
echo json_encode($response);

?>