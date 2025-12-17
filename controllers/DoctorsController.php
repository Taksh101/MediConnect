<?php
// controllers/DoctorsController.php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/SpecialityModel.php';

function has_csrf(): bool {
    return function_exists('csrf_verify') && function_exists('csrf_token');
}

function verify_csrf_or_die(array $source) {
    if (!has_csrf()) return;
    $token = $source['csrf_token'] ?? ($source['csrf'] ?? '');
    if (!csrf_verify($token)) {
        $_SESSION['flash_error'] = "Invalid request (CSRF).";
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos(($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json') !== false) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Invalid CSRF']);
            exit;
        }
        header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/doctors');
        exit;
    }
}

function index_doctors() {
    require_admin_login();
    global $db;
    $model = new DoctorModel($db);

    $perPage = 10;
    // Show 4 doctors per page to match specialities UI
    $perPage = 4;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($page - 1) * $perPage;

    $total = $model->countAll();
    $doctors = $model->paginate($perPage, $offset);
    $totalPages = (int)ceil($total / max(1,$perPage));

    include __DIR__ . '/../views/admin/doctors/index.php';
}

function create_doctor() {
    require_admin_login();
    global $db;
    $specialityModel = new SpecialityModel($db);
    $specialities = $specialityModel->all();
    include __DIR__ . '/../views/admin/doctors/create.php';
}

function store_doctor() {
    require_admin_login();
    global $db;
    $model = new DoctorModel($db);

    verify_csrf_or_die($_POST);

    $name = trim($_POST['Name'] ?? '');
    $email = trim($_POST['Email'] ?? '');
    $password = $_POST['Password'] ?? '';
    $phone = trim($_POST['Phone'] ?? '');
    $speciality = (int)($_POST['Speciality_Id'] ?? 0);
    $qualification = trim($_POST['Qualification'] ?? '');
    $experience = (int)($_POST['Experience_Years'] ?? 0);
    $bio = trim($_POST['Bio'] ?? '') ?: null;
    $status = strtoupper(trim($_POST['Status'] ?? 'AVAILABLE'));

    $errors = [];
    if ($name === '') $errors['Name'] = 'Name is required';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['Email'] = 'Valid email required';
    if ($model->existsByEmail($email)) $errors['Email'] = 'Email already exists';
    if ($password === '') $errors['Password'] = 'Password is required';
    if ($phone === '' || !preg_match('/^[6-9][0-9]{9}$/', $phone)) $errors['Phone'] = 'Phone must be 10 digits and start with 6-9';
    if ($speciality <= 0) $errors['Speciality_Id'] = 'Select a speciality';
    if ($qualification === '' || strlen($qualification) < 2 || strlen($qualification) > 200) $errors['Qualification'] = 'Qualification is required (2-200 chars)';
    if ($bio === null || strlen($bio) < 10 || strlen($bio) > 2000) $errors['Bio'] = 'Bio is required (10-2000 chars)';
    if (!in_array($status, ['AVAILABLE','UNAVAILABLE'])) $errors['Status'] = 'Invalid status';

    // password strength
    if ($password !== '') {
        if (strlen($password) < 8) $errors['Password'] = 'Password must be at least 8 characters';
        if (!preg_match('/[A-Z]/', $password)) $errors['Password'] = 'Password must include an uppercase letter';
        if (!preg_match('/[a-z]/', $password)) $errors['Password'] = 'Password must include a lowercase letter';
        if (!preg_match('/[0-9]/', $password)) $errors['Password'] = 'Password must include a digit';
        if (!preg_match('/[^A-Za-z0-9]/', $password)) $errors['Password'] = 'Password must include a symbol';
    }

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['old'] = $_POST;
        header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/doctors/create');
        exit;
    }

    $ok = $model->create($name, $email, $password, $phone, $speciality, $qualification, $experience, $bio, $status);
    if ($ok) $_SESSION['flash_success'] = 'Doctor added successfully';
    else $_SESSION['flash_error'] = 'Failed to add doctor';

    header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/doctors');
    exit;
}



function edit_doctor() {
    require_admin_login();
    global $db;
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/doctors');
    $model = new DoctorModel($db);
    $doctor = $model->find($id);
    if (!$doctor) { $_SESSION['flash_error']='Doctor not found'; header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/doctors'); exit; }
    $specialityModel = new SpecialityModel($db);
    $specialities = $specialityModel->all();
    include __DIR__ . '/../views/admin/doctors/edit.php';
}

function update_doctor() {
    require_admin_login();
    global $db;
    $model = new DoctorModel($db);

    verify_csrf_or_die($_POST);

    $id = (int)($_POST['Doctor_Id'] ?? 0);
    if ($id <= 0) { $_SESSION['flash_error']='Invalid doctor id'; header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/doctors'); exit; }

    $name = trim($_POST['Name'] ?? '');
    $email = trim($_POST['Email'] ?? '');
    $password = $_POST['Password'] ?? '';
    $phone = trim($_POST['Phone'] ?? '');
    $speciality = (int)($_POST['Speciality_Id'] ?? 0);
    $qualification = trim($_POST['Qualification'] ?? '');
    $experience = (int)($_POST['Experience_Years'] ?? 0);
    $bio = trim($_POST['Bio'] ?? '') ?: null;
    $status = strtoupper(trim($_POST['Status'] ?? 'AVAILABLE'));

    $errors = [];
    if ($name === '') $errors['Name'] = 'Name is required';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['Email'] = 'Valid email required';
    if ($model->existsByEmail($email, $id)) $errors['Email'] = 'Another doctor with this email exists';
    if ($phone === '' || !preg_match('/^[6-9][0-9]{9}$/', $phone)) $errors['Phone'] = 'Phone must be 10 digits and start with 6-9';
    if ($speciality <= 0) $errors['Speciality_Id'] = 'Select a speciality';
    if ($qualification === '' || strlen($qualification) < 2 || strlen($qualification) > 200) $errors['Qualification'] = 'Qualification is required (2-200 chars)';
    if ($bio === null || strlen($bio) < 10 || strlen($bio) > 2000) $errors['Bio'] = 'Bio is required (10-2000 chars)';
    if (!in_array($status, ['AVAILABLE','UNAVAILABLE'])) $errors['Status'] = 'Invalid status';

    // password strength (only when provided)
    if ($password !== '') {
        if (strlen($password) < 8) $errors['Password'] = 'Password must be at least 8 characters';
        if (!preg_match('/[A-Z]/', $password)) $errors['Password'] = 'Password must include an uppercase letter';
        if (!preg_match('/[a-z]/', $password)) $errors['Password'] = 'Password must include a lowercase letter';
        if (!preg_match('/[0-9]/', $password)) $errors['Password'] = 'Password must include a digit';
        if (!preg_match('/[^A-Za-z0-9]/', $password)) $errors['Password'] = 'Password must include a symbol';
    }

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['old'] = $_POST;
        header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/doctors/edit&id=' . $id);
        exit;
    }

    $ok = $model->update($id, $name, $email, $password, $phone, $speciality, $qualification, $experience, $bio, $status);
    if ($ok) $_SESSION['flash_success'] = 'Doctor updated';
    else $_SESSION['flash_error'] = 'Update failed';

    header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/doctors');
    exit;
}



function delete_doctor() {
    require_admin_login();
    global $db;
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid id']); exit; }
    verify_csrf_or_die($_POST);
    $model = new DoctorModel($db);
    $ok = $model->delete($id);
    if ($ok) {
        echo json_encode(['ok'=>true]);
    } else {
        $err = $db->error ?: 'Delete failed';
        http_response_code(500);
        echo json_encode(['ok'=>false,'error'=>$err]);
    }
    exit;
}
function edit_availability() {
    require_admin_login();
    global $db;

    $availabilityId = (int)($_GET['id'] ?? 0);
    $doctor_id = (int)($_GET['doctor_id'] ?? 0);

    if ($availabilityId <= 0) {
        $_SESSION['flash_error'] = 'Invalid availability id';
        header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/doctors/availability&doctor_id=' . $doctor_id);
        exit;
    }

    $model = new DoctorModel($db);
    $availability = $model->findAvailability($availabilityId);

    if (!$availability) {
        $_SESSION['flash_error'] = 'Availability not found';
        header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/doctors/availability&doctor_id=' . $doctor_id);
        exit;
    }

    $doctor = $model->find((int)$availability['Doctor_Id']);
    include __DIR__ . '/../views/admin/doctors/availability_edit.php';
}

function update_availability() {
    require_admin_login();
    global $db;

    verify_csrf_or_die($_POST);

    $availabilityId = (int)($_POST['Availability_Id'] ?? 0);
    $doctor_id = (int)($_POST['Doctor_Id'] ?? 0);
    $day = trim($_POST['Available_Day'] ?? '');
    $start = trim($_POST['Start_Time'] ?? '');
    $end = trim($_POST['End_Time'] ?? '');

    $errors = [];
    if ($availabilityId <= 0) $errors['general'] = 'Invalid availability id';
    if ($doctor_id <= 0) $errors['Doctor_Id'] = 'Invalid doctor';
    $validDays = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    if (!in_array($day, $validDays)) $errors['Available_Day'] = 'Select a valid day';
    // Basic time validation (HH:MM)
    if (!preg_match('/^\d{2}:\d{2}$/', $start)) $errors['Start_Time'] = 'Start time required (HH:MM)';
    if (!preg_match('/^\d{2}:\d{2}$/', $end)) $errors['End_Time'] = 'End time required (HH:MM)';

    if (empty($errors)) {
        list($sh,$sm) = explode(':', $start);
        list($eh,$em) = explode(':', $end);
        $smin = (int)$sh * 60 + (int)$sm;
        $emin = (int)$eh * 60 + (int)$em;
        if ($smin >= $emin) $errors['Start_Time'] = 'Start time must be before end time';
    }

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['old'] = $_POST;
        header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/doctors/availability/edit&id=' . $availabilityId . '&doctor_id=' . $doctor_id);
        exit;
    }

    $model = new DoctorModel($db);
    // Server-side overlap safety: give descriptive feedback and return to edit form
    if ($model->overlaps($doctor_id, $day, $start, $end, $availabilityId)) {
        $_SESSION['form_errors'] = ['Start_Time' => 'This time slot overlaps with an existing one.'];
        $_SESSION['old'] = $_POST;
        header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/doctors/availability/edit&id=' . $availabilityId . '&doctor_id=' . $doctor_id);
        exit;
    }
    $ok = $model->updateAvailability($availabilityId, $doctor_id, $day, $start, $end);

    if ($ok) $_SESSION['flash_success'] = 'Availability updated';
    else $_SESSION['flash_error'] = 'Failed to update availability';

    header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/doctors/availability&doctor_id=' . $doctor_id);
    exit;
}


function view_doctor() {
    require_admin_login();
    global $db;
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/doctors');
    $model = new DoctorModel($db);
    $doctor = $model->find($id);
    if (!$doctor) { $_SESSION['flash_error']='Doctor not found'; header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/doctors'); exit; }
    include __DIR__ . '/../views/admin/doctors/view.php';
}

function manage_availability() {
    require_admin_login();
    global $db;
    $doctorId = (int)($_GET['doctor_id'] ?? ($_POST['doctor_id'] ?? 0));
    $page = max(1, (int)($_GET['page'] ?? 1));
    if ($doctorId <= 0) { $_SESSION['flash_error']='Invalid doctor id'; header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/doctors'); exit; }
    $model = new DoctorModel($db);
    $doctor = $model->find($doctorId);
    if (!$doctor) { $_SESSION['flash_error']='Doctor not found'; header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/doctors'); exit; }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf_or_die($_POST);
        $action = $_POST['action'] ?? '';
        if ($action === 'add') {
            $day = $_POST['Available_Day'] ?? '';
            $start = $_POST['Start_Time'] ?? '';
            $end = $_POST['End_Time'] ?? '';
            $res = $model->addAvailability($doctorId, $day, $start, $end);
            if ($res['ok']) $_SESSION['flash_success'] = 'Availability added'; else $_SESSION['flash_error'] = $res['error'] ?? 'Add failed';
        } elseif ($action === 'delete') {
            $availId = (int)($_POST['Availability_Id'] ?? 0);
            $res = $model->deleteAvailability($availId, $doctorId);
            if ($res) $_SESSION['flash_success'] = 'Availability deleted'; else $_SESSION['flash_error'] = 'Delete failed';
        } elseif ($action === 'update') {
            $availId = (int)($_POST['Availability_Id'] ?? 0);
            $day = $_POST['Available_Day'] ?? '';
            $start = $_POST['Start_Time'] ?? '';
            $end = $_POST['End_Time'] ?? '';
            $res = $model->updateAvailability($availId, $doctorId, $day, $start, $end);
            if ($res['ok']) $_SESSION['flash_success'] = 'Availability updated'; else $_SESSION['flash_error'] = $res['error'] ?? 'Update failed';
        }
        header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/doctors/availability&doctor_id=' . $doctorId);
        exit;
    }

    $availabilities = $model->getAvailability($doctorId);
    include __DIR__ . '/../views/admin/doctors/availability.php';
}
