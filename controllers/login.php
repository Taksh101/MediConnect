<?php
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/PatientModel.php';
global $mysqli;
// No session_start here because csrf.php already starts it

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = $_POST ?: json_decode($input, true) ?? [];

// CSRF check
$token = $data['csrf_token'] ?? '';
if (!csrf_verify($token)) {
    echo json_encode(['ok' => false, 'errors' => ['email' => 'Invalid request']]);
    exit;
}

$role = strtoupper(trim($data['role'] ?? ''));
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (!$role || !$email || !$password) {
    echo json_encode(['ok' => false, 'errors' => ['email' => 'Invalid Email ID']]);
    exit;
}



// ---------------------- PATIENT LOGIN ----------------------
if ($role === "PATIENT") {

    $model = new PatientModel($mysqli);
    $user = $model->getByEmail($email);

    // Email not found
    if (!$user) {
        echo json_encode(['ok' => false, 'errors' => ['email' => 'Invalid Email ID']]);
        exit;
    }

    // Email exists but password wrong → error under password field
    if (!password_verify($password, $user['Password'])) {
        echo json_encode(['ok' => false, 'errors' => ['password' => 'Invalid password']]);
        exit;
    }

    // ---------------------- PATIENT LOGIN (after password verified) ----------------------
// normalize profile flag (handles tinyint or enum)
$profileFlag = (int)($user['is_profile_complete'] ?? 0);
if (isset($user['is_profile_complete'])) {
    // support both tinyint(1) or ENUM('YES','NO')
    $v = $user['is_profile_complete'];
    if ($v === 'YES' || $v === 'yes' || $v === 'Y' || $v === '1' || $v === 1) $profileFlag = 1;
    if ($v === 'NO' || $v === 'no' || $v === 'N' || $v === '0' || $v === 0) $profileFlag = 0;
    if (is_numeric($v)) $profileFlag = ((int)$v) ? 1 : 0;
}

// set session values
$_SESSION['patient_id'] = $user['Patient_Id'];
$_SESSION['role'] = "PATIENT";
$_SESSION['is_profile_complete'] = $profileFlag;

// redirect based on completion flag
if ($profileFlag === 1) {
    // profile complete -> go to patient dashboard
    echo json_encode(['ok' => true, 'redirect' => '/MediConnect/index.php?route=patient/dashboard']);
    exit;
} else {
    // profile not complete -> force complete profile page
    echo json_encode(['ok' => true, 'redirect' => '/MediConnect/index.php?route=patient/medical']);
    exit;
}

}



// ---------------------- DOCTOR LOGIN (BASIC) ----------------------
if ($role === "DOCTOR") {

    require_once __DIR__ . '/../models/DoctorModel.php';
    $doctorModel = new DoctorModel($mysqli);

    $user = $doctorModel->getByEmail($email);

    // doctor not found
    if (!$user) {
        echo json_encode(['ok' => false, 'errors' => ['email' => 'Invalid Email ID']]);
        exit;
    }

    // check active status
    if ($user['Status'] !== 'ACTIVE') {
        echo json_encode(['ok' => false, 'errors' => ['email' => 'Account disabled, contact admin']]);
        exit;
    }

    // wrong password
    if (!password_verify($password, $user['Password'])) {
        echo json_encode(['ok' => false, 'errors' => ['password' => 'Invalid password']]);
        exit;
    }

    // success
    $_SESSION['doctor_id'] = $user['Doctor_Id'];
    $_SESSION['role'] = "DOCTOR";

    echo json_encode([
        'ok' => true,
        'redirect' => '/MediConnect/index.php?route=doctor/dashboard'
    ]);
    exit;
}



// ---------------------- ADMIN LOGIN ----------------------
if ($role === "ADMIN") {

    require_once __DIR__ . '/../models/AdminModel.php';
    $adminModel = new AdminModel($mysqli);

    $user = $adminModel->getByEmail($email);

    // not found
    if (!$user) {
        echo json_encode(['ok' => false, 'errors' => ['email' => 'Invalid Email ID']]);
        exit;
    }

    // check password
    if (!password_verify($password, $user['Password'])) {
        echo json_encode(['ok' => false, 'errors' => ['password' => 'Invalid password']]);
        exit;
    }

    // success: set session
    $_SESSION['admin_id'] = $user['Admin_Id'];
    $_SESSION['role'] = "ADMIN";
    // optional: store admin name
    $_SESSION['admin_name'] = $user['Name'];

    echo json_encode([
        'ok' => true,
        'redirect' => '/MediConnect/index.php?route=admin/dashboard'
    ]);
    exit;
}


echo json_encode(['ok' => false, 'errors' => ['email' => 'Invalid Email ID']]);
exit;
