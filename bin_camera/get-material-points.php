<?php
/**
 * Get Material Points by Classification
 * Returns points for a classified item based on materials database
 */

header('Content-Type: application/json');

// Database configuration
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'aprecycle';

// Initialize response
$response = [
    'status' => 'error',
    'message' => 'Initialization error.',
    'points' => 0,
    'material_found' => false,
    'material_name' => null
];

// Get classification from request
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!isset($data['classification']) || empty($data['classification'])) {
    $response['message'] = 'Error: No classification provided.';
    echo json_encode($response);
    exit;
}

$classification = trim($data['classification']);
$class_lower = strtolower($classification);

// Connect to database
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    $response['message'] = "Database connection failed: " . $conn->connect_error;
    echo json_encode($response);
    exit;
}

// Fetch all materials from database
$materials_query = "SELECT material_id, material_name, points_per_item FROM material ORDER BY material_name ASC";
$materials_result = mysqli_query($conn, $materials_query);

if (!$materials_result) {
    $response['message'] = 'Database error: Failed to fetch materials.';
    echo json_encode($response);
    $conn->close();
    exit;
}

// Build material map
$material_map = [];
while ($mat = mysqli_fetch_assoc($materials_result)) {
    $mat_name_lower = strtolower($mat['material_name']);
    $material_map[$mat_name_lower] = [
        'id' => $mat['material_id'],
        'name' => $mat['material_name'],
        'points' => $mat['points_per_item']
    ];
}

// Try to match classification to a material
$detected_material = null;

foreach ($material_map as $mat_name => $mat_data) {
    // Check if classification contains the material name
    if (strpos($class_lower, $mat_name) !== false) {
        $detected_material = $mat_data;
        break;
    }
}

// Return result
if ($detected_material) {
    $response['status'] = 'success';
    $response['message'] = 'Material found in database.';
    $response['points'] = $detected_material['points'];
    $response['material_found'] = true;
    $response['material_name'] = $detected_material['name'];
} else {
    $response['status'] = 'success';
    $response['message'] = 'Material not found in database. Item will be rejected.';
    $response['points'] = 0;
    $response['material_found'] = false;
    $response['material_name'] = null;
}

$conn->close();
echo json_encode($response);
?>
