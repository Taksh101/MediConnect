<?php
// controllers/AdminController.php
session_start();
require_once __DIR__ . '/../models/AdminModel.php';
require_once __DIR__ . '/../config/database.php'; // provides $mysqli
function dashboard() {
global $mysqli;
    require_admin_login();

    $model = new AdminModel($mysqli);
    
    // Auto-update statuses globally (handled in index.php)

    $data = [
      'total_patients' => $model->countPatients(),
      'total_doctors' => $model->countDoctors(),
      'total_specialities' => $model->countSpecialities(),
      'todays_appointments_count' => $model->countTodaysAppointments(),
      'todays_appointments' => $model->getTodaysAppointments()
    ];

    // render via layout: layout expects $view to be set
    $view = __DIR__ . '/../views/admin/dashboard.php';
    include __DIR__ . '/../views/admin/layout.php';
}
