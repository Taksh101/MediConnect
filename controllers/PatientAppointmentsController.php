<?php
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../config/auth.php';
class PatientAppointmentsController {
    protected $db;
    protected $appointmentModel;

    public function __construct($db) {
        $this->db = $db;
        $this->appointmentModel = new AppointmentModel($db);
    }

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        require_patient_login();

        $patientId = $_SESSION['patient_id'];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 4;
        
        $total = $this->appointmentModel->countByPatientId($patientId);
        $totalPages = (int)max(1, ceil($total / max(1, $perPage)));
        $offset = ($page - 1) * $perPage;

        $appointments = $this->appointmentModel->findByPatientId($patientId, $perPage, $offset);

        // Calculate start index for the list numbering
        $startIndex = ($page - 1) * $perPage + 1;

        include __DIR__ . '/../views/patient/appointments/index.php';
    }

    public function view() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        require_patient_login();

        $id = (int)($_GET['id'] ?? 0);
        $page = max(1, (int)($_GET['page'] ?? 1));

        if (!$id) {
            header("Location: " . (defined('BASE_PATH') ? BASE_PATH : '') . "/index.php?route=patient/appointments&page=" . $page);
            exit;
        }

        $appointment = $this->appointmentModel->findByIdWithAll($id);
        
        if (!$appointment) {
            header("Location: " . (defined('BASE_PATH') ? BASE_PATH : '') . "/index.php?route=patient/appointments");
            exit;
        }

        // Security Check: Ensure this appointment belongs to the logged-in patient
        if ($appointment['Patient_Id'] != $_SESSION['patient_id']) {
            // Unauthorized check - generic mismatch error or redirect
            header("Location: " . (defined('BASE_PATH') ? BASE_PATH : '') . "/index.php?route=patient/appointments");
            exit;
        }

        include __DIR__ . '/../views/patient/appointments/view.php';
    }
}
