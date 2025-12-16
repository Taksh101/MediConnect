<?php
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../config/auth.php';
class AdminAppointmentsController {
    protected $db;
    protected $appointmentModel;

    public function __construct($db) {
        $this->db = $db;
        $this->appointmentModel = new AppointmentModel($db);
        $this->appointmentModel->autoUpdateStatuses(); // Run auto-update logic on admin access too
    }

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        require_admin_login();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 4; // 4 items per page
        $total = $this->appointmentModel->countAll();
        $totalPages = (int)max(1, ceil($total / max(1, $perPage)));
        $offset = ($page - 1) * $perPage;

        $appointments = $this->appointmentModel->paginateAll($perPage, $offset);

        // Calculate start index for the list numbering
        $startIndex = ($page - 1) * $perPage + 1;

        include __DIR__ . '/../views/admin/appointments/index.php';
    }

    public function view() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        require_admin_login();

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            header("Location: " . (defined('BASE_PATH') ? BASE_PATH : '') . "/index.php?route=admin/appointments");
            exit;
        }
        $appointment = $this->appointmentModel->findByIdWithAll($id);
        if (!$appointment) {
            // Not found
            header("Location: " . (defined('BASE_PATH') ? BASE_PATH : '') . "/index.php?route=admin/appointments");
            exit;
        }
        include __DIR__ . '/../views/admin/appointments/view.php';
    }
}
