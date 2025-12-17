<?php
// controllers/DoctorAppointmentsController.php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../config/csrf.php';



// Check Doctor Login
class DoctorAppointmentsController {
    private $db;
    private $doctorId;

    public function __construct($db) {
        $this->require_doctor_login();
        $this->db = $db;
        $this->doctorId = $_SESSION['doctor_id'];
        
        // Auto-update statuses is now handled globally in index.php
    }

    private function require_doctor_login() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['doctor_id']) || $_SESSION['role'] !== 'DOCTOR') {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=auth/login');
            exit;
        }
    }

    public function index() {
        // Tab logic: default to 'today'
        $tab = $_GET['tab'] ?? 'today';
        $validTabs = ['today', 'upcoming', 'pending', 'history'];
        if (!in_array($tab, $validTabs)) $tab = 'today';

        // Pagination
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 4;
        $offset = ($page - 1) * $perPage;

        $result = $this->getAppointmentsByTab($tab, $perPage, $offset);
        $appointments = $result['data'];
        $total = $result['total'];
        $totalPages = (int)ceil($total / max(1, $perPage));

        // Pass data to view
        include __DIR__ . '/../views/doctor/appointments/index.php';
    }

    private function getAppointmentsByTab($tab, $limit, $offset) {
        $baseSql = "FROM appointments a
                    JOIN patients p ON a.Patient_Id = p.Patient_Id
                    WHERE a.Doctor_Id = ?";
        
        $where = "";
        $order = "";

        if ($tab === 'today') {
            $where = " AND a.Appointment_Date = CURDATE()";
            $order = " ORDER BY a.Appointment_Time ASC";
        } elseif ($tab === 'upcoming') {
            $where = " AND a.Appointment_Date > CURDATE() AND a.Status = 'Approved'";
            $order = " ORDER BY a.Appointment_Date ASC, a.Appointment_Time ASC";
        } elseif ($tab === 'pending') {
            $where = " AND a.Status = 'Pending' AND TIMESTAMP(a.Appointment_Date, a.Appointment_Time) >= NOW()";
            $order = " ORDER BY a.Appointment_Date ASC, a.Appointment_Time ASC";
        } elseif ($tab === 'history') {
            $where = " AND (a.Appointment_Date < CURDATE() OR a.Status IN ('Completed', 'Rejected', 'Cancelled', 'Missed'))";
            $order = " ORDER BY a.Appointment_Date DESC, a.Appointment_Time DESC";
        }

        // Count
        $countSql = "SELECT COUNT(*) as cnt " . $baseSql . $where;
        $stmt = $this->db->prepare($countSql);
        $stmt->bind_param('i', $this->doctorId);
        $stmt->execute();
        $total = $stmt->get_result()->fetch_assoc()['cnt'];

        // Data
        $dataSql = "SELECT a.*, p.Name as Patient_Name, p.Email as Patient_Email, p.Phone as Patient_Phone " . $baseSql . $where . $order . " LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($dataSql);
        $stmt->bind_param('iii', $this->doctorId, $limit, $offset);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return ['data' => $data, 'total' => $total];
    }

    public function view() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) $this->redirect('doctor/appointments');

        $appt = $this->getAppointmentDetails($id);
        if (!$appt) {
            $_SESSION['flash_error'] = "Appointment not found.";
            $this->redirect('doctor/appointments');
        }

        // Fetch existing notes if any
        $notes = $this->getConsultationNotes($id);

        // Capture navigation state
        $tab = $_GET['tab'] ?? 'today';
        $page = $_GET['page'] ?? 1;

        include __DIR__ . '/../views/doctor/appointments/view.php';
    }

    private function getAppointmentDetails($id) {
        // Secure fetch ensuring Doctor owns this appointment + Get Speciality Duration
        $sql = "SELECT a.*, p.*, m.*, d.Speciality_Id, s.Speciality_Name, s.Consultation_Duration
                FROM appointments a 
                JOIN patients p ON a.Patient_Id = p.Patient_Id
                LEFT JOIN patient_medical_profile m ON p.Patient_Id = m.Patient_Id
                JOIN doctors d ON a.Doctor_Id = d.Doctor_Id
                JOIN specialities s ON d.Speciality_Id = s.Speciality_Id
                WHERE a.Appointment_Id = ? AND a.Doctor_Id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $id, $this->doctorId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function update_status() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('doctor/appointments');
        
        // CSRF Check
        $token = $_POST['csrf_token'] ?? '';
        if (!function_exists('csrf_verify') || !csrf_verify($token)) {
            $_SESSION['flash_error'] = "Invalid security token.";
            $this->redirect('doctor/appointments');
        }

        $id = (int)($_POST['appointment_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if (!in_array($status, ['Approved', 'Rejected', 'Completed'])) {
            $_SESSION['flash_error'] = "Invalid status.";
            $this->redirect('doctor/appointments/view&id=' . $id);
        }

        $stmt = $this->db->prepare("UPDATE appointments SET Status = ? WHERE Appointment_Id = ? AND Doctor_Id = ?");
        $stmt->bind_param('sii', $status, $id, $this->doctorId);
        
        if ($stmt->execute()) {
            $_SESSION['flash_success'] = "Appointment marked as " . $status;
        } else {
            $_SESSION['flash_error'] = "Failed to update status.";
        }
        
        $this->redirect('doctor/appointments/view&id=' . $id);
    }

    public function save_notes() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('doctor/appointments');

         // CSRF Check
        $token = $_POST['csrf_token'] ?? '';
        if (!function_exists('csrf_verify') || !csrf_verify($token)) {
            $_SESSION['flash_error'] = "Invalid security token.";
            $this->redirect('doctor/appointments');
        }

        $apptId = (int)($_POST['appointment_id'] ?? 0);
        $symptoms = trim($_POST['symptoms'] ?? '');
        $diagnosis = trim($_POST['diagnosis'] ?? '');
        $advice = trim($_POST['advice'] ?? '');

        // Verify ownership AND Get Duration
        $sql = "SELECT a.Appointment_Id, a.Appointment_Date, a.Appointment_Time, s.Consultation_Duration 
                FROM appointments a
                JOIN doctors d ON a.Doctor_Id = d.Doctor_Id
                JOIN specialities s ON d.Speciality_Id = s.Speciality_Id
                WHERE a.Appointment_Id = ? AND a.Doctor_Id = ?";
        $check = $this->db->prepare($sql);
        $check->bind_param('ii', $apptId, $this->doctorId);
        $check->execute();
        $res = $check->get_result();
        
        if ($res->num_rows === 0) {
            $_SESSION['flash_error'] = "Unauthorized access.";
            $this->redirect('doctor/appointments');
        }
        
        $apptData = $res->fetch_assoc();
        
        // Dynamic Time Slot Validation
        date_default_timezone_set('Asia/Kolkata'); // Ensure server validates in correct timezone
        $durationMinutes = (int)$apptData['Consultation_Duration'];
        $durationSeconds = $durationMinutes * 60;
        
        $startDateTime = strtotime($apptData['Appointment_Date'] . ' ' . $apptData['Appointment_Time']);
        $endDateTime = $startDateTime + $durationSeconds; 
        
        $now = time();
        
        if ($now < $startDateTime) {
            $_SESSION['flash_error'] = "Consultation notes can only be added during the appointment time slot.";
            $this->redirect('doctor/appointments/view&id=' . $apptId);
        }
        
        if ($now > $endDateTime) {
            $_SESSION['flash_error'] = "Appointment timed out. Notes can only be added during the time slot.";
            $this->redirect('doctor/appointments/view&id=' . $apptId);
        }

        // Check if note exists
        $exists = $this->db->prepare("SELECT Note_Id FROM consultation_notes WHERE Appointment_Id = ?");
        $exists->bind_param('i', $apptId);
        $exists->execute();
        $res = $exists->get_result();

        if ($res->num_rows > 0) {
            // Update
            $sql = "UPDATE consultation_notes SET Symptoms = ?, Diagnosis = ?, Advice = ? WHERE Appointment_Id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('sssi', $symptoms, $diagnosis, $advice, $apptId);
        } else {
            // Insert
            $sql = "INSERT INTO consultation_notes (Appointment_Id, Doctor_Id, Symptoms, Diagnosis, Advice) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('iisss', $apptId, $this->doctorId, $symptoms, $diagnosis, $advice);
        }

        if ($stmt->execute()) {
            // Auto complete appointment if notes are added
            $this->db->query("UPDATE appointments SET Status = 'Completed' WHERE Appointment_Id = $apptId AND Status = 'Approved'");
            $_SESSION['flash_success'] = "Notes saved successfully.";
        } else {
            $_SESSION['flash_error'] = "Failed to save notes.";
        }

        $this->redirect('doctor/appointments/view&id=' . $apptId);
    }

    private function getConsultationNotes($apptId) {
        $stmt = $this->db->prepare("SELECT * FROM consultation_notes WHERE Appointment_Id = ?");
        $stmt->bind_param('i', $apptId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Helper for specialized Patient Profile View (Read Only)
    public function view_patient() {
        $patientId = (int)($_GET['patient_id'] ?? 0);
        if ($patientId <= 0) $this->redirect('doctor/dashboard');

        // Allow viewing only if there is at least one appointment (history or future) connecting them
        $check = $this->db->prepare("SELECT 1 FROM appointments WHERE Doctor_Id = ? AND Patient_Id = ? LIMIT 1");
        $check->bind_param('ii', $this->doctorId, $patientId);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
             $_SESSION['flash_error'] = "You do not have access to this patient profile.";
             $this->redirect('doctor/dashboard');
        }

        // Fetch Patient Data
        $stmt = $this->db->prepare("SELECT * FROM patients WHERE Patient_Id = ?");
        $stmt->bind_param('i', $patientId);
        $stmt->execute();
        $patient = $stmt->get_result()->fetch_assoc();

        // Fetch Medical Profile
        $stmt = $this->db->prepare("SELECT * FROM patient_medical_profile WHERE Patient_Id = ?");
        $stmt->bind_param('i', $patientId);
        $stmt->execute();
        $medical = $stmt->get_result()->fetch_assoc();

        include __DIR__ . '/../views/doctor/patients/view.php';
    }

    public function profile() {
        $sql = "SELECT d.*, s.Speciality_Name FROM doctors d LEFT JOIN specialities s ON d.Speciality_Id = s.Speciality_Id WHERE d.Doctor_Id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $this->doctorId);
        $stmt->execute();
        $doctor = $stmt->get_result()->fetch_assoc();
        
        include __DIR__ . '/../views/doctor/profile.php';
    }

    private function redirect($route) {
        header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=' . $route);
        exit;
    }
}
