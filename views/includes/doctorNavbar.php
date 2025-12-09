<?php
require_once __DIR__ . '/../../config/auth.php';
// views/includes/doctorNavbar.php

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!function_exists('navActive')) {
    function navActive(string $key): string {
        $current = $_GET['route'] ?? '';
        if ($current === $key) return 'active-link';
        if (str_contains($_SERVER['REQUEST_URI'], $key)) return 'active-link';
        return '';
    }
}

if (!function_exists('route_url')) {
    function route_url(string $r): string {
        $base = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
        return $base . '/index.php?route=' . ltrim($r, '/');
    }
}

// Ensure session data is available
$doctorId = $_SESSION['doctor_id'] ?? 0;
$pageTitle = $pageTitle ?? 'MediConnect - Doctor';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png">
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* navbar theme (Enhanced Premium Look - Matched to Patient for Consistency) */
.navbar-premium {
    /* Using teal/indigo gradient to distinguish but keep premium feel */
    background: linear-gradient(90deg, #4f46e5 0%, #4338ca 100%);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    padding: 0.5rem 1rem;
    min-height: 60px;
    position: fixed; 
    top: 0px;
    right: 0px;
    left: 0px;
    z-index: 1040;
}
.navbar-premium .navbar-brand { 
    color:#fff; 
    font-weight:800; 
    font-size: 1.5rem;
}
.navbar-premium .nav-link { 
    color: rgba(255,255,255,0.92); 
    margin:0 .35rem; 
    padding:.6rem .9rem;
    border-radius:8px; 
    transition: all .2s; 
    font-weight: 500;
}
.navbar-premium .nav-link:hover { 
    background: rgba(255,255,255,0.1); 
    color:#fff; 
    transform:scale(1.02);
}
.navbar-premium .active-link { 
    position:relative; 
    color:#fff; 
    font-weight:700; 
    background: rgba(255,255,255,0.15);
}
.navbar-premium .active-link::after {
    content:'';
    position:absolute;
    left:15%;
    right:15%;
    bottom:0;
    height:2px;
    border-radius:2px;
    background: #a5b4fc; /* Light indigo accent */
}
.navbar-premium .logout-link { 
    color:#fbbf24 !important; /* Amber for logout */
    font-weight:700; 
    background: rgba(255,255,255,0.1); 
    padding:.45rem .8rem; 
    border-radius:8px; 
    border: 1px solid rgba(251,191,36,0.5);
}
.navbar-premium .logout-link:hover { 
    background: rgba(251,191,36,0.2); 
    color:#fff !important; 
    border-color: #fff;
}

.navbar-premium .nav-right { margin-left: auto; display:flex; gap:.25rem; align-items:center; }
@media (max-width: 991.98px) {
    .navbar-premium .nav-right {
        width: 100%;
        flex-direction: column;
        align-items: flex-start !important;
        justify-content: flex-start !important;
        padding: .5rem 0;
        gap: .5rem;
    }
    .navbar-premium .nav-right .nav-link,
    .navbar-premium .logout-link {
        width: 100%;
        text-align: left !important;
        padding-left: .75rem;
    }
}
</style>
<body>
<nav class="navbar navbar-expand-lg navbar-premium">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= route_url('doctor/dashboard') ?>">MediConnect Doctor</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#doctorNav">
            <span class="navbar-toggler-icon" style="filter:invert(1)"></span>
        </button>

        <div class="collapse navbar-collapse" id="doctorNav">
            <ul class="navbar-nav"></ul>
            <ul class="navbar-nav nav-right">
                <li class="nav-item"><a class="nav-link <?= navActive('doctor/dashboard') ?>" href="<?= route_url('doctor/dashboard') ?>">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link <?= navActive('doctor/appointments') ?>" href="<?= route_url('doctor/appointments') ?>">My Appointments</a></li>
                <li class="nav-item"><a class="nav-link <?= navActive('doctor/profile') ?>" href="<?= route_url('doctor/profile') ?>">Profile</a></li>
                <li class="nav-item ms-2">
                    <a class="nav-link logout-link" href="<?= route_url('auth/logout') ?>">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
// Prevent Back Button usage after logout
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        window.location.reload();
    }
});
</script>

