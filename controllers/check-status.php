<?php
// controllers/check-status.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

// Check if any role session variable exists
$isLoggedIn = !empty($_SESSION['admin_id']) || !empty($_SESSION['doctor_id']) || !empty($_SESSION['patient_id']);

echo json_encode(['logged_in' => $isLoggedIn]);
exit;
