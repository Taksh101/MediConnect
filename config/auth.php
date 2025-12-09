<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Helper to prevent browser caching for protected pages
function prevent_cache_headers() {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}

function require_patient_login() {
    prevent_cache_headers();
    if (empty($_SESSION['patient_id']) || ($_SESSION['role'] ?? '') !== 'PATIENT') {
        header("Location: /MediConnect/index.php?route=auth/login");
        exit;
    }
}

function require_profile_completed() {
    // If flag missing or falsy -> redirect to complete profile
    $flag = $_SESSION['is_profile_complete'] ?? 0;
    if (!((int)$flag)) {
        header("Location: /MediConnect/index.php?route=patient/complete-profile");
        exit;
    }
}

function block_if_profile_completed() {
    $flag = $_SESSION['is_profile_complete'] ?? 0;
    if ((int)$flag) {
        header("Location: /MediConnect/index.php?route=patient/dashboard");
        exit;
    }
}

function require_doctor_login() {
    prevent_cache_headers();
    if (empty($_SESSION['doctor_id']) || ($_SESSION['role'] ?? '') !== 'DOCTOR') {
        header("Location: /MediConnect/index.php?route=auth/login");
        exit;
    }
}

function require_admin_login() {
    prevent_cache_headers();
    if (empty($_SESSION['admin_id']) || ($_SESSION['role'] ?? '') !== 'ADMIN') {
        header("Location: /MediConnect/index.php?route=auth/login");
        exit;
    }
}
function is_user_logged_in(): bool {
    // treat as logged in only if one of the known auth/session keys is present
    return !empty($_SESSION['admin_id'])
        || !empty($_SESSION['doctor_id'])
        || !empty($_SESSION['patient_id'])
        || !empty($_SESSION['role']);
}

function require_guest() {
    // If there's no authenticated session, allow access (guest)
    if (!is_user_logged_in()) {
        return;
    }

    // If authenticated, redirect to role-specific dashboard
    $role = strtoupper($_SESSION['role'] ?? '');

    if (!empty($_SESSION['admin_id']) || $role === 'ADMIN') {
        header("Location: /MediConnect/index.php?route=admin/dashboard");
        exit;
    }

    if (!empty($_SESSION['doctor_id']) || $role === 'DOCTOR') {
        header("Location: /MediConnect/index.php?route=doctor/dashboard");
        exit;
    }

    if (!empty($_SESSION['patient_id']) || $role === 'PATIENT') {
        header("Location: /MediConnect/index.php?route=patient/dashboard");
        exit;
    }
}
