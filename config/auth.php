<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function require_patient_login() {
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
    if (empty($_SESSION['doctor_id']) || ($_SESSION['role'] ?? '') !== 'DOCTOR') {
        header("Location: /MediConnect/index.php?route=auth/login");
        exit;
    }
}

function require_admin_login() {
    if (empty($_SESSION['admin_id']) || ($_SESSION['role'] ?? '') !== 'ADMIN') {
        header("Location: /MediConnect/index.php?route=auth/login");
        exit;
    }
}
