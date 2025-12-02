<?php
require_once __DIR__ . '/../../config/auth.php';
// views/admin/includes/adminNavbar.php

// FIX: Prevent "Cannot redeclare" Fatal Error by using conditional definitions
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

$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* navbar theme (Enhanced Premium Look) */
.navbar-premium {
    /* Deeper, more sophisticated blue gradient */
    background: linear-gradient(90deg,#0d6efd 0%, #084ccd 100%);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    padding: 0.5rem 1rem;
    min-height: 60px; /* Fixed height for precise calculation */
    /* FIX: Ensure sticky position for proper scrolling behaviour */
    position: fixed; 
    top: 0px;
    right: 0px;
    left: 0px;
    z-index: 1040;
}
.navbar-premium .navbar-brand { 
    color:#fff; 
    font-weight:800; /* Bolder brand name */
    font-size: 1.5rem;
}
.navbar-premium .nav-link { 
    color: rgba(255,255,255,0.92); 
    margin:0 .35rem; 
    padding:.6rem .9rem; /* Increased padding */
    border-radius:8px; 
    transition: all .2s; 
    font-weight: 500;
}
.navbar-premium .nav-link:hover { 
    background: rgba(255,255,255,0.1); 
    color:#fff; 
    transform:scale(1.02); /* Subtle scale effect */
}
.navbar-premium .active-link { 
    position:relative; 
    color:#fff; 
    font-weight:700; 
    background: rgba(255,255,255,0.15); /* Highlight active link softly */
}
.navbar-premium .active-link::after {
    /* Subtle underline below the nav items */
    content:'';
    position:absolute;
    left:15%;
    right:15%;
    bottom:0;
    height:2px;
    border-radius:2px;
    background: #ffc107; /* Highlight color (Warning/Gold) */
}
.navbar-premium .logout-link { 
    color:#ffc107 !important; /* Yellow text for contrast */
    font-weight:700; 
    background: rgba(255,255,255,0.1); 
    padding:.45rem .8rem; 
    border-radius:8px; 
    border: 1px solid rgba(255,193,7,0.5); /* Subtle border */
}
.navbar-premium .logout-link:hover { 
    background: rgba(255,193,7,0.2); 
    color:#fff !important; 
    border-color: #fff;
}

/* keep links right-aligned */
.navbar-premium .nav-right { margin-left: auto; display:flex; gap:.25rem; align-items:center; }
/* FIX: On small screens, stack links vertically & left align them */
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
        <a class="navbar-brand" href="<?= route_url('admin/dashboard') ?>">MediConnect Admin</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
            <span class="navbar-toggler-icon" style="filter:invert(1)"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav"></ul>
            <ul class="navbar-nav nav-right">
                <li class="nav-item"><a class="nav-link <?= navActive('admin/dashboard') ?>" href="<?= route_url('admin/dashboard') ?>">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link <?= navActive('admin/specialities') ?>" href="<?= route_url('admin/specialities') ?>">Specialities</a></li>
                <li class="nav-item"><a class="nav-link <?= navActive('admin/doctors') ?>" href="<?= route_url('admin/doctors') ?>">Doctors</a></li>
                <li class="nav-item"><a class="nav-link <?= navActive('admin/patients') ?>" href="<?= route_url('admin/patients') ?>">Patients</a></li>
                <li class="nav-item"><a class="nav-link <?= navActive('admin/appointments') ?>" href="<?= route_url('admin/appointments') ?>">Appointments</a></li>
                <li class="nav-item"><a class="nav-link <?= navActive('admin/payments') ?>" href="<?= route_url('admin/payments') ?>">Payments</a></li>
                <li class="nav-item ms-2">
                    <a class="nav-link logout-link" href="<?= route_url('auth/logout') ?>">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>