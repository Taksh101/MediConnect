<?php
// controllers/PatientDashboardController.php
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/PatientModel.php';

class PatientDashboardController {
    protected $db;
    protected $appointmentModel;
    protected $patientModel;

    public function __construct($db) {
        $this->db = $db;
        $this->appointmentModel = new AppointmentModel($db);
        $this->patientModel = new PatientModel($db);
    }

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $patientId = $_SESSION['patient_id'] ?? 0;
        if (!$patientId) {
            header("Location: " . (defined('BASE_PATH') ? BASE_PATH : '') . "/index.php?route=auth/login");
            exit;
        }

        // Get patient name for welcome message
        $patient = $this->patientModel->findById($patientId);
        $patientName = $patient['Name'] ?? 'Patient';

        // Stats
        $appointments = $this->appointmentModel->getByPatientId($patientId, 100, 0); // Fetch last 100 to calculate stats
        
        $totalAppointments = count($appointments);
        $upcomingCount = 0;
        $nextAppointment = null;
        
        $now = new DateTime();

        foreach ($appointments as $appt) {
            $apptDateTime = new DateTime($appt['Appointment_Date'] . ' ' . $appt['Appointment_Time']);
            
            // Check if upcoming (status pending/approved and future date)
            $status = strtolower($appt['Status']);
            if (($status === 'pending' || $status === 'approved') && $apptDateTime > $now) {
                $upcomingCount++;
                
                // Find immediate next appointment
                if ($nextAppointment === null || $apptDateTime < new DateTime($nextAppointment['Appointment_Date'] . ' ' . $nextAppointment['Appointment_Time'])) {
                    $nextAppointment = $appt;
                }
            }
        }

        include __DIR__ . '/../views/patient/dashboard.php';
    }
}
