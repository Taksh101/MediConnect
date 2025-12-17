<?php
// controllers/SpecialityController.php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SpecialityModel.php';

// NOTE: do NOT auto-include csrf.php here. Instead detect CSRF functions at runtime
// to avoid double-includes that may regenerate tokens unexpectedly.

function has_csrf(): bool {
    return function_exists('csrf_verify') && function_exists('csrf_token');
}

function verify_csrf_or_die(array $source) {
    if (!has_csrf()) return; // CSRF not configured -> skip
    $token = $source['csrf_token'] ?? ($source['csrf'] ?? '') ;
    if (!csrf_verify($token)) {
        $_SESSION['flash_error'] = "Invalid request (CSRF).";
        // If this is an AJAX/JSON request, respond with JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos(($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json') !== false) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Invalid CSRF']);
            exit;
        }
        // Otherwise redirect back safely
        header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/specialities');
        exit;
    }
}

function index_specialities() {
    require_admin_login();
    global $db;
    $model = new SpecialityModel($db);

    // page config
    $perPage = 3; // change as you like
    $page = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($page - 1) * $perPage;

    $total = $model->countAll();
    $specialities = $model->paginate($perPage, $offset);

    // pass pagination data to view via variables
    $totalPages = (int)ceil($total / $perPage);

    include __DIR__ . '/../views/admin/specialities/index.php';
}


function create_speciality() {
    require_admin_login();
    $view = __DIR__ . '/../views/admin/specialities/create.php';
    include $view;
}

function store_speciality() {
    require_admin_login();
    global $db;
    $model = new SpecialityModel($db);

    // verify CSRF from POST if CSRF functions exist
    verify_csrf_or_die($_POST);

    $name = trim($_POST['Speciality_Name'] ?? '');
    $desc = trim($_POST['Description'] ?? '') ?: null;
    $duration = (int)($_POST['Consultation_Duration'] ?? 0);
    $fee = (float)($_POST['Consultation_Fee'] ?? 0);

    // validation
    $errors = [];
    if ($name === '') $errors['Speciality_Name'] = 'Name is required';
    if ($duration < 5 || $duration > 45) $errors['Consultation_Duration'] = 'Duration must be between 5 and 45 minutes';
    if ($fee < 0) $errors['Consultation_Fee'] = 'Fee must be >= 0';

    $len = strlen(trim($desc ?? ''));
if ($len < 5 || $len > 200) {
    $errors['Description'] = 'Description must be 5-200 characters';
}


    if ($model->existsByName($name)) $errors['Speciality_Name'] = 'Speciality name already exists';

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['old'] = $_POST;
        header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/specialities/create');
        exit;
    }

    $ok = $model->create($name, $desc, $duration, $fee);
    if ($ok) $_SESSION['flash_success'] = 'Speciality added successfully';
    else $_SESSION['flash_error'] = 'Failed to add speciality';

    header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/specialities');
    exit;
}

function edit_speciality() {
    require_admin_login();
    global $db;
    $id = (int)($_GET['id'] ?? 0);
    $page = max(1, (int)($_GET['page'] ?? 1));
    if ($id <= 0) {
        header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/specialities&page=' . $page);
        exit;
    }
    $model = new SpecialityModel($db);
    $speciality = $model->find($id);
    if (!$speciality) {
        $_SESSION['flash_error'] = 'Speciality not found';
        header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/specialities');
        exit;
    }
    $view = __DIR__ . '/../views/admin/specialities/edit.php';
    include $view;
}

function update_speciality() {
    require_admin_login();
    global $db;
    $model = new SpecialityModel($db);

    // verify csrf from POST
    verify_csrf_or_die($_POST);

    $id = (int)($_POST['Speciality_Id'] ?? 0);
    $name = trim($_POST['Speciality_Name'] ?? '');
    $desc = trim($_POST['Description'] ?? '') ?: null;
    $duration = (int)($_POST['Consultation_Duration'] ?? 0);
    $fee = (float)($_POST['Consultation_Fee'] ?? 0);

    if ($id <= 0) {
        $_SESSION['flash_error'] = 'Invalid speciality id';
        header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/specialities');
        exit;
    }

    // validation
    $errors = [];
    if ($name === '') $errors['Speciality_Name'] = 'Name is required';
    if ($duration < 5 || $duration > 45) $errors['Consultation_Duration'] = 'Duration must be between 5 and 45 minutes';
    if ($fee < 0) $errors['Consultation_Fee'] = 'Fee must be >= 0';

 $len = strlen(trim($desc ?? ''));
if ($len < 5 || $len > 200) {
    $errors['Description'] = 'Description must be 5-200 characters';
}


    if ($model->existsByName($name, $id)) $errors['Speciality_Name'] = 'Another speciality with same name exists';

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['old'] = $_POST;
        header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/specialities/edit&id=' . $id);
        exit;
    }

    $ok = $model->update($id, $name, $desc, $duration, $fee);
    if ($ok) $_SESSION['flash_success'] = 'Speciality updated';
    else $_SESSION['flash_error'] = 'Update failed';

    header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=admin/specialities');
    exit;
}

function delete_speciality() {
    require_admin_login();
    global $db;

    // Accept POST body params
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Invalid id']);
        exit;
    }

    // Verify CSRF (works for AJAX too: send csrf_token in body)
    verify_csrf_or_die($_POST);

    $model = new SpecialityModel($db);
    $ok = $model->delete($id);
    if ($ok) {
        echo json_encode(['ok' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Delete failed']);
    }
    exit;
}
