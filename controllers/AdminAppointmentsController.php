<?php
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../config/auth.php';
class AdminAppointmentsController {
    protected $db;
    protected $appointmentModel;

    public function __construct($db) {
        $this->db = $db;
        $this->appointmentModel = new AppointmentModel($db);
        $this->updateAppointmentStatuses(); // Run auto-update logic on admin access too
    }

    private function updateAppointmentStatuses() {
        // Use consistent timezone
        date_default_timezone_set('Asia/Kolkata');
        $currentDateTime = date('Y-m-d H:i:s');

        // 1. Auto-Reject Pending appointments (Global check)
        $sqlReject = "UPDATE appointments 
                      SET Status = 'Rejected' 
                      WHERE Status = 'Pending' 
                      AND TIMESTAMP(Appointment_Date, Appointment_Time) < ?";
        $stmt = $this->db->prepare($sqlReject);
        $stmt->bind_param('s', $currentDateTime);
        $stmt->execute();

        // 2. Auto-Miss Approved appointments (Global check)
        try {
            $sqlMiss = "UPDATE appointments a 
                        JOIN doctors d ON a.Doctor_Id = d.Doctor_Id
                        JOIN specialities s ON d.Speciality_Id = s.Speciality_Id
                        LEFT JOIN consultation_notes cn ON a.Appointment_Id = cn.Appointment_Id
                        SET a.Status = 'Missed' 
                        WHERE a.Status = 'Approved' 
                        AND cn.Note_Id IS NULL
                        AND TIMESTAMP(a.Appointment_Date, a.Appointment_Time) + INTERVAL s.Consultation_Duration MINUTE < ?";
            
            $stmt2 = $this->db->prepare($sqlMiss);
            $stmt2->bind_param('s', $currentDateTime);
            $stmt2->execute();
        } catch (Exception $e) {
            error_log("Admin auto-miss update failed: " . $e->getMessage());
        }
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
