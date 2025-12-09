<?php
require_once __DIR__ . '/../models/PatientModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../config/auth.php';
class AdminPatientsController {
    protected $db;
    protected $patientModel;
    protected $appointmentModel;

    public function __construct($db) {
        $this->db = $db;
        $this->patientModel = new PatientModel($db);
        $this->appointmentModel = new AppointmentModel($db);
    }

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        require_admin_login();

        // pagination
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 4;
        $total = $this->patientModel->countAll();
        $totalPages = (int)max(1, ceil($total / max(1, $perPage)));
        $offset = ($page - 1) * $perPage;

        $patients = $this->patientModel->paginate($perPage, $offset);

        // pass to view
        $doInclude = __DIR__ . '/../views/admin/patients/index.php';
        include __DIR__ . '/../views/admin/patients/index.php';
    }

    public function view() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        require_admin_login();

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            header("Location: " . (defined('BASE_PATH') ? BASE_PATH : '') . "/index.php?route=admin/patients");
            exit;
        }
        $patient = $this->patientModel->findByIdWithProfile($id);
        if (!$patient) {
            // not found
            header("Location: " . (defined('BASE_PATH') ? BASE_PATH : '') . "/index.php?route=admin/patients");
            exit;
        }
        include __DIR__ . '/../views/admin/patients/view.php';
    }

    public function delete() {
        header('Content-Type: application/json');
        
        // Basic CSRF check (simplified for this context)
        // In a real app, verify $_POST['csrf_token'] against session
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid method']);
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
            exit;
        }

        // Check if patient exists
        $patient = $this->patientModel->findByIdWithProfile($id);
        if (!$patient) {
            echo json_encode(['status' => 'error', 'message' => 'Patient not found']);
            exit;
        }

        // Delete patient
        // Assuming cascade delete is set up in DB for related appointments/profiles
        // If not, we should delete those first manually.
        // Based on SQL schema:
        // FOREIGN KEY (Patient_Id) REFERENCES patients(Patient_Id) ON DELETE CASCADE
        // So deleting patient is enough.
        
        $sql = "DELETE FROM patients WHERE Patient_Id = ?";
        $stmt = $this->db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'DB Delete failed']);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'DB Prepare failed']);
        }
        exit;
    }

    // patient-specific appointment history
    public function appointments() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        require_admin_login();

        $patientId = (int)($_GET['patient_id'] ?? 0);
        if (!$patientId) {
            header("Location: " . (defined('BASE_PATH') ? BASE_PATH : '') . "/index.php?route=admin/patients");
            exit;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 4;
        $offset = ($page - 1) * $perPage;
        $total = $this->appointmentModel->countByPatientId($patientId);
        $totalPages = (int)max(1, ceil($total / max(1, $perPage)));

        $appointments = $this->appointmentModel->getByPatientId($patientId, $perPage, $offset);

        // fetch patient basic for header
        $patient = $this->patientModel->findByIdWithProfile($patientId);

        include __DIR__ . '/../views/admin/patients/appointments.php';
    }
}
