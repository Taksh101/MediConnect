<?php
// MediConnect/index.php
// Front controller / simple router

// Show errors during dev; set to 0 on production
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ----- BASE PATH detection -----
// This builds the base path like "/MediConnect" automatically so client JS
// can reference assets / index.php using the same base.
$scriptPath = $_SERVER['SCRIPT_NAME'] ?? '/';
$base = rtrim(dirname($scriptPath), '/\\');
if ($base === '') $base = '/';
define('BASE_PATH', $base);
// --- Ensure DB connection is available to controllers ---
// Include your database config (it defines $mysqli); normalize to $db for controllers.
$dbFile = __DIR__ . '/config/database.php';
if (file_exists($dbFile)) {
    require_once $dbFile; // expected to create $mysqli (see config/database.php)
    // Normalize common names to $db (mysqli)
    if (!isset($db) || !($db instanceof mysqli)) {
        if (isset($mysqli) && $mysqli instanceof mysqli) $db = $mysqli;
        elseif (isset($conn) && $conn instanceof mysqli) $db = $conn;
        elseif (isset($link) && $link instanceof mysqli) $db = $link;
        elseif (isset($dbConn) && $dbConn instanceof mysqli) $db = $dbConn;
    }
}

// Fail-fast with a clear message (dev only) if no mysqli found
if (!isset($db) || !($db instanceof mysqli)) {
    http_response_code(500);
    echo "<h1>500</h1><p>Database connection not found. Expected <code>\$db</code> (mysqli) after including config.</p>";
    echo "<p>Checked file: " . htmlspecialchars($dbFile) . "</p>";
    exit;
}

// ----- Read route param and normalize -----
// We accept URLs like:
//  /index.php?route=auth/register
//  /index.php            (should serve landing)
//  /index.php?route=/    (also landing)
$rawRoute = $_GET['route'] ?? '';
// remove leading/trailing slashes and whitespace
$route = trim($rawRoute);
$route = trim($route, "/ \t\n\r\0\x0B");

// If route is empty (no ?route=) treat as landing
// This ensures visiting "/" or "/index.php" serves landing by default.
if ($route === '') {
    $route = '';
}

// small helper to safely include controller/view files
function safe_require($path) {
    if (file_exists($path)) {
        require $path;
    } else {
        http_response_code(500);
        echo "<h1>500</h1><p>Missing file: " . htmlspecialchars($path) . "</p>";
        exit;
    }
}

// very small router
switch ($route) {

    // ============================
    // AUTHENTICATION
    // ============================
    case 'auth/login':
        safe_require(__DIR__ . '/views/auth/login.php');
        break;
    case 'auth/login-action':
        safe_require(__DIR__ . '/controllers/login.php');
        break;
    case 'auth/register':
        safe_require(__DIR__ . '/views/auth/register.php');
        break;
    case 'auth/register-action':
        safe_require(__DIR__ . '/controllers/register.php');
        break;
    case 'auth/check-email':
        safe_require(__DIR__ . '/controllers/check-email.php');
        break;
    case 'auth/logout':
        safe_require(__DIR__ . '/controllers/logout.php');
        break;
    case 'auth/check_status':
        safe_require(__DIR__ . '/controllers/check-status.php');
        break;


    // ============================
    // DOCTOR MODULE
    // ============================
    case 'doctor/dashboard':
        safe_require(__DIR__ . '/views/doctor/dashboard.php');
        break;
    case 'doctor/appointments':
        safe_require(__DIR__ . '/controllers/DoctorAppointmentsController.php');
        $controller = new DoctorAppointmentsController($db);
        $controller->index();
        break;
    case 'doctor/appointments/view':
        safe_require(__DIR__ . '/controllers/DoctorAppointmentsController.php');
        $controller = new DoctorAppointmentsController($db);
        $controller->view();
        break;
    case 'doctor/appointments/update_status':
        safe_require(__DIR__ . '/controllers/DoctorAppointmentsController.php');
        $controller = new DoctorAppointmentsController($db);
        $controller->update_status();
        break;
    case 'doctor/appointments/save_notes':
        safe_require(__DIR__ . '/controllers/DoctorAppointmentsController.php');
        $controller = new DoctorAppointmentsController($db);
        $controller->save_notes();
        break;
    case 'doctor/patients/view':
        safe_require(__DIR__ . '/controllers/DoctorAppointmentsController.php');
        $controller = new DoctorAppointmentsController($db);
        $controller->view_patient();
        break;
    case 'doctor/profile':
        safe_require(__DIR__ . '/controllers/DoctorAppointmentsController.php');
        $controller = new DoctorAppointmentsController($db);
        $controller->profile();
        break;


    // ============================
    // PATIENT MODULE
    // ============================
    case 'patient/dashboard':
        safe_require(__DIR__ . '/controllers/PatientDashboardController.php');
        $controller = new PatientDashboardController($db);
        $controller->index();
        break;
    
    // Appointments
    case 'patient/appointments':
        safe_require(__DIR__ . '/controllers/PatientAppointmentsController.php');
        $controller = new PatientAppointmentsController($db);
        $controller->index();
        break;
    case 'patient/appointments/view':
        safe_require(__DIR__ . '/controllers/PatientAppointmentsController.php');
        $controller = new PatientAppointmentsController($db);
        $controller->view();
        break;

    // Profile
    case 'patient/profile':
        safe_require(__DIR__ . '/controllers/PatientProfileController.php');
        $controller = new PatientProfileController($db);
        $controller->show();
        break;
    case 'patient/medical-save':
        safe_require(__DIR__ . '/controllers/PatientProfileController.php');
        $controller = new PatientProfileController($db);
        $controller->save();
        break;

    case 'patient/medical':
        safe_require(__DIR__ . '/controllers/PatientProfileController.php');
        $controller = new PatientProfileController($db);
        $controller->medical();
        break;

    // Booking Flow
    case 'patient/book/start':
        safe_require(__DIR__ . '/controllers/PatientBookingController.php');
        $controller = new PatientBookingController($db);
        $controller->start();
        break;
    case 'patient/book/step2':
        safe_require(__DIR__ . '/controllers/PatientBookingController.php');
        $controller = new PatientBookingController($db);
        $controller->step2();
        break;
    case 'patient/book/step3':
        safe_require(__DIR__ . '/controllers/PatientBookingController.php');
        $controller = new PatientBookingController($db);
        $controller->step3();
        break;
    case 'patient/book/step4':
        safe_require(__DIR__ . '/controllers/PatientBookingController.php');
        $controller = new PatientBookingController($db);
        $controller->step4();
        break;
    case 'patient/book/complete':
        safe_require(__DIR__ . '/controllers/PatientBookingController.php');
        $controller = new PatientBookingController($db);
        $controller->complete();
        break;
    case 'patient/book/success':
        safe_require(__DIR__ . '/controllers/PatientBookingController.php');
        $controller = new PatientBookingController($db);
        $controller->success();
        break;


    // ============================
    // ADMIN MODULE
    // ============================
    case 'admin/dashboard':
        safe_require(__DIR__ . '/views/admin/dashboard.php');
        break;
    
    // Doctors
    case 'admin/doctors':
        safe_require(__DIR__ . '/controllers/DoctorsController.php');
        index_doctors();
        break;
    case 'admin/doctors/create':
        safe_require(__DIR__ . '/controllers/DoctorsController.php');
        create_doctor();
        break;
    case 'admin/doctors/store':
        safe_require(__DIR__ . '/controllers/DoctorsController.php');
        store_doctor();
        break;
    case 'admin/doctors/edit':
        safe_require(__DIR__ . '/controllers/DoctorsController.php');
        edit_doctor();
        break;
    case 'admin/doctors/update':
        safe_require(__DIR__ . '/controllers/DoctorsController.php');
        update_doctor();
        break;
    case 'admin/doctors/delete':
        safe_require(__DIR__ . '/controllers/DoctorsController.php');
        delete_doctor();
        break;
    case 'admin/doctors/view':
        safe_require(__DIR__ . '/controllers/DoctorsController.php');
        view_doctor();
        break;
    case 'admin/doctors/availability':
        safe_require(__DIR__ . '/controllers/DoctorsController.php');
        manage_availability();
        break;
    case 'admin/doctors/availability/edit':
        safe_require(__DIR__ . '/controllers/DoctorsController.php');
        edit_availability();
        break;
    case 'admin/doctors/availability/update':
        safe_require(__DIR__ . '/controllers/DoctorsController.php');
        update_availability();
        break;

    // Specialities
    case 'admin/specialities':
        safe_require(__DIR__ . '/controllers/SpecialityController.php');
        index_specialities();
        break;
    case 'admin/specialities/create':
        safe_require(__DIR__ . '/controllers/SpecialityController.php');
        create_speciality();
        break;
    case 'admin/specialities/store':
        safe_require(__DIR__ . '/controllers/SpecialityController.php');
        store_speciality();
        break;
    case 'admin/specialities/edit':
        safe_require(__DIR__ . '/controllers/SpecialityController.php');
        edit_speciality();
        break;
    case 'admin/specialities/update':
        safe_require(__DIR__ . '/controllers/SpecialityController.php');
        update_speciality();
        break;
    case 'admin/specialities/delete':
        safe_require(__DIR__ . '/controllers/SpecialityController.php');
        delete_speciality();
        break;

    // Patients
    case 'admin/patients':
        safe_require(__DIR__ . '/controllers/AdminPatientsController.php');
        $controller = new AdminPatientsController($db);
        $controller->index();
        break;
    case 'admin/patients/view':
        safe_require(__DIR__ . '/controllers/AdminPatientsController.php');
        $controller = new AdminPatientsController($db);
        $controller->view();
        break;
    case 'admin/patients/appointments':
        safe_require(__DIR__ . '/controllers/AdminPatientsController.php');
        $controller = new AdminPatientsController($db);
        $controller->appointments();
        break;
    case 'admin/patients/delete':
        safe_require(__DIR__ . '/controllers/AdminPatientsController.php');
        $controller = new AdminPatientsController($db);
        $controller->delete();
        break;

    // Appointments
    case 'admin/appointments':
        safe_require(__DIR__ . '/controllers/AdminAppointmentsController.php');
        $controller = new AdminAppointmentsController($db);
        $controller->index();
        break;
    case 'admin/appointments/view':
        safe_require(__DIR__ . '/controllers/AdminAppointmentsController.php');
        $controller = new AdminAppointmentsController($db);
        $controller->view();
        break;

    // Payments
    case 'admin/payments':
        safe_require(__DIR__ . '/controllers/AdminPaymentsController.php');
        $controller = new AdminPaymentsController($db);
        $controller->index();
        break;
    case 'admin/payments/view':
        safe_require(__DIR__ . '/controllers/AdminPaymentsController.php');
        $controller = new AdminPaymentsController($db);
        $controller->view();
        break;


    // ============================
    // LANDING & DEFAULT
    // ============================
    case '':
    case 'home':
    case 'landing':
        // landing page (default)
        if (file_exists(__DIR__ . '/views/landing.php')) {
            safe_require(__DIR__ . '/views/landing.php');
        } else {
            // graceful fallback if landing missing
            echo "<!doctype html><html><head><meta charset='utf-8'><title>MediConnect</title></head><body style='font-family:system-ui,Segoe UI,Roboto,Arial;padding:2rem;'>";
            echo "<h2>MediConnect</h2><p>Landing page not found. To test registration open: <a href=\"" . htmlspecialchars(BASE_PATH . "/index.php?route=auth/register") . "\">Register</a></p>";
            echo "</body></html>";
        }
        break;

    default:
        // 404: If physical file exists in the project root (like an asset), let Apache serve it;
        // otherwise show a friendly 404 with the requested route.
        http_response_code(404);
        echo "<!doctype html><html><head><meta charset='utf-8'><title>404 Not Found</title></head><body style='font-family:system-ui,Segoe UI,Roboto,Arial;padding:2rem;'>";
        echo "<h1>404</h1><p>Route '<strong>" . htmlspecialchars($route) . "</strong>' not found.</p>";
        echo "<p>Try: <a href=\"" . htmlspecialchars(BASE_PATH . "/index.php?route=auth/register") . "\">Register</a></p>";
        echo "</body></html>";
        break;
}
