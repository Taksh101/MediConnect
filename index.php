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

    // ----- AUTH VIEWS -----
    case 'auth/register':
        safe_require(__DIR__ . '/views/auth/register.php');
        break;

    case 'auth/login':
        safe_require(__DIR__ . '/views/auth/login.php');
        break;

    // ----- CONTROLLERS / ACTIONS -----
    case 'auth/register-action':
        safe_require(__DIR__ . '/controllers/register.php');
        break;

    case 'auth/check-email':
        safe_require(__DIR__ . '/controllers/check-email.php');
        break;

    // Add other controller routes here
    // case 'auth/login-action':
    //     safe_require(__DIR__ . '/controllers/login.php');
    //     break;

    // ----- STATIC PAGES / DEFAULT -----
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
