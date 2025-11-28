<?php
// MediConnect/controllers/check-email.php
header('Content-Type: application/json');

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Method not allowed']);
    exit;
}

// Read JSON body (client sends JSON)
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$email = trim($data['email'] ?? '');

// Basic sanity
if ($email === '') {
    echo json_encode(['ok' => true, 'exists' => false]);
    exit;
}

// Validate email format server-side too (light)
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'msg' => 'Invalid email format']);
    exit;
}

// load DB (adjust path if your config is elsewhere)
require_once __DIR__ . '/../config/database.php'; // this should expose $mysqli (or change accordingly)
require_once __DIR__ . '/../models/PatientModel.php';

// depending on how your database.php sets up connection variable name:
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    // try alternative: if config creates $db or $conn change below accordingly
    // return friendly server error (so client doesn't misinterpret)
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'DB connection not found']);
    exit;
}

$patientModel = new PatientModel($mysqli);
$exists = false;
try {
    $exists = $patientModel->emailExists($email);
} catch (Exception $e) {
    // log if you want: error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Server error']);
    exit;
}

// successful JSON response
echo json_encode(['ok' => true, 'exists' => (bool)$exists]);
exit;
