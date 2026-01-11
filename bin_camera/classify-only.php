<?php
/**
 * Classify waste item using AI (does NOT save to database)
 * Returns classification and confidence only
 */

header('Content-Type: application/json');

// 1. Load Database Config AND Environment Variables
require_once '../php/config.php'; // Add this to get DB connection
require_once '../php/env.php';

// 2. Fetch Valid Materials from Database
$conn = getDBConnection();
$material_list = [];
// We only need the names to tell the AI what is allowed
$sql = "SELECT material_name FROM material"; 
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $material_list[] = $row['material_name'];
    }
}
// Create a string like: "Plastic Bottle (PET), Aluminum Can, Glass Bottle..."
$valid_materials_string = implode(', ', $material_list);

// API CONFIGURATION
$GEMINI_API_KEY = env('GEMINI_API_KEY');
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

// 3. Update AI prompt with the Database List
$prompt = "Analyze the image of the waste item. 
Classify it into EXACTLY ONE of the following valid categories: [{$valid_materials_string}]. 
If it does not fit any of these specific categories, return 'Other'.
Your response MUST be a JSON object with two fields: 'classification' (the exact string from the list) and 'confidence_score' (a score from 0.0 to 1.0 representing classification certainty).";

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

if (strpos($class_lower, 'not a waste') !== false || strpos($class_lower, 'non-recyclable') !== false || $class_lower === 'other') {
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

// Close DB connection
$conn->close();

echo json_encode($response);
?>