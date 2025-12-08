<?php
/**
 * Classify waste item using AI (does NOT save to database)
 * Returns classification and confidence only
 */

header('Content-Type: application/json');

// API CONFIGURATION
$GEMINI_API_KEY = 'a';
$GEMINI_ENDPOINT = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$GEMINI_API_KEY}";

// Initialize response
$response = [
    'status' => 'error',
    'message' => 'Initialization error.',
    'classification' => 'N/A',
    'confidence' => 0.0
];

// Get image data
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!isset($data['image']) || empty($data['image'])) {
    $response['message'] = 'Error: No image data received.';
    echo json_encode($response);
    exit;
}

$base64Image = $data['image'];

// Prepare AI prompt
$prompt = "Analyze the image of the waste item. Your response MUST be a JSON object with two fields: 'classification' (the type of item, e.g., Plastic Bottle, Cardboard Box, Organic) and 'confidence_score' (a score from 0.0 to 1.0 representing classification certainty).";

$payload = json_encode([
    'contents' => [
        [
            'parts' => [
                ['text' => $prompt],
                [
                    'inlineData' => [
                        'mimeType' => 'image/jpeg',
                        'data' => $base64Image,
                    ],
                ],
            ],
        ],
    ],
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

// Send to Gemini API
$ch = curl_init($GEMINI_ENDPOINT);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($payload)
]);
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

// Extract classification
$classification_data = null;
if (isset($api_response['candidates'][0]['content']['parts'][0]['text'])) {
    $classification_json_string = $api_response['candidates'][0]['content']['parts'][0]['text'];
    $classification_data = json_decode($classification_json_string, true);
}

if (
    !$classification_data ||
    !isset($classification_data['classification']) ||
    !isset($classification_data['confidence_score'])
) {
    $response['message'] = 'AI could not return a structured classification.';
    echo json_encode($response);
    exit;
}

// Return classification result
$classification = $classification_data['classification'];
$confidence = (float) $classification_data['confidence_score'];

$class_lower = strtolower($classification);
$is_valid_waste = true;

if (strpos($class_lower, 'not a waste') !== false || strpos($class_lower, 'non-recyclable') !== false) {
    $is_valid_waste = false;
}

if ($confidence >= 0.80 && !$is_valid_waste) {
    $response['status'] = 'error';
    $response['message'] = "Item rejected: This is not a recyclable waste item.";
    $response['classification'] = $classification;
    $response['confidence'] = $confidence;
} else {
    $response['status'] = 'success';
    $response['message'] = "Item classified successfully.";
    $response['classification'] = $classification;
    $response['confidence'] = $confidence;
}

echo json_encode($response);
?>
