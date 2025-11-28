<?php
// MediConnect/views/includes/header.php
// Minimal header for single-page landing (CDN bootstrap + session/cache control)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prevent browser caching for pages where session matters
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Helper: check logged-in (kept for future use; not used in landing now)
$loggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$userRole = $loggedIn ? ($_SESSION['role'] ?? 'patient') : null;

// Compute base url for local assets
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = rtrim($scriptDir, '/\\');
if ($baseUrl === '') $baseUrl = '/';

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
$landingCss = ($baseUrl === '/' ? '' : $baseUrl) . '/assets/css/landing.css';
$sessionJs  = ($baseUrl === '/' ? '' : $baseUrl) . '/assets/js/session.js';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>MediConnect</title>

  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Project CSS -->
  <link href="<?= h($landingCss) ?>" rel="stylesheet" />

  <!-- Small fallback so page looks okay even if css didn't load -->
  <style>
    :root{ --accent: #4f46e5; --accent-2: #06b6d4; --muted:#6b7280; }
    body { font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; }
  </style>

  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script defer src="<?= h($sessionJs) ?>"></script>
</head>
<body>
