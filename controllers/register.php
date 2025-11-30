<?php
// MediConnect/controllers/register.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../models/PatientModel.php';

header('Content-Type: application/json');

// read POST data (works for JSON body)
$input = file_get_contents('php://input');
$data = $_POST;
if (empty($data)) {
    $data = json_decode($input, true) ?? [];
}

// CSRF check
$token = $data['csrf_token'] ?? '';
if (!csrf_verify($token)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Invalid CSRF token']);
    exit;
}

// sanitize & server-side validation
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$confirm = $data['confirm_password'] ?? '';
$phone = trim($data['phone'] ?? '');
$gender = $data['gender'] ?? '';
$dob_raw = trim($data['dob'] ?? ''); // expected DD-MM-YYYY
$dob_iso = $dob_raw;
$address = trim($data['address'] ?? '');

$errors = [];

// name
if (!preg_match('/^[A-Za-z\s]{2,100}$/', $name)) {
    $errors['name'] = 'Name must be letters/spaces (2-100 chars)';
}

// email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email';
}

// password
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
    $errors['password'] = 'Password must be 8+ chars with upper, lower, digit and special char';
}
if ($password !== $confirm) {
    $errors['confirm_password'] = 'Passwords do not match';
}

// phone (Indian 10-digit)
if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
    $errors['phone'] = 'Invalid phone number';
}

// gender
$allowedGender = ['MALE','FEMALE','OTHER'];
if (!in_array(strtoupper($gender), $allowedGender)) {
    $errors['gender'] = 'Invalid gender';
}

// DOB: HTML <input type="date"> sends YYYY-MM-DD
$dob_iso = null;

if (!$dob_raw) {
    $errors['dob'] = 'Date of birth is required';
} elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob_raw)) {
    $errors['dob'] = 'Invalid date format';
} else {
    list($y, $m, $d) = explode('-', $dob_raw);
    if (!checkdate((int)$m, (int)$d, (int)$y)) {
        $errors['dob'] = 'Invalid date';
    } elseif ($y < 1900 || $y > (int)date('Y')) {
        $errors['dob'] = 'Invalid birth year';
    } else {
        $dob_iso = $dob_raw; // Perfect for MySQL DATE
    }
}




// return early on validation errors
if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => $errors]);
    exit;
}

// check duplicate email
global $mysqli;
$patientModel = new PatientModel($mysqli);
if ($patientModel->emailExists($email)) {
    http_response_code(409);
    echo json_encode(['ok' => false, 'errors' => ['email' => 'Email already exists']]);
    exit;
}

// create user
$password_hash = password_hash($password, PASSWORD_DEFAULT);
$insertData = [
    'name' => $name,
    'email' => $email,
    'password_hash' => $password_hash,
    'phone' => $phone,
    'gender' => strtoupper($gender),
    'dob' => $dob_iso, // ISO date for DB
    'address' => $address
];

$patientId = $patientModel->createPatient($insertData);
if (!$patientId) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'msg' => 'DB Insert Failed',
        'mysqli_error' => $mysqli->error ?? 'no error message',
        'insertData' => $insertData
    ]);
    exit;
}


// success
echo json_encode(['ok' => true, 'msg' => 'Registered', 'patient_id' => $patientId, 'redirect' => '/MediConnect/index.php?route=auth/login']);
