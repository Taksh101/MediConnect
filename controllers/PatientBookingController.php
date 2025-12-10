<?php
// controllers/PatientBookingController.php
require_once __DIR__ . '/../models/SpecialityModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';

class PatientBookingController {
    protected $db;
    protected $specialityModel;
    protected $doctorModel;
    protected $appointmentModel;

    public function __construct($db) {
        $this->db = $db;
        $this->specialityModel = new SpecialityModel($db);
        $this->doctorModel = new DoctorModel($db);
        $this->appointmentModel = new AppointmentModel($db);
    }

    // STEP 1: Select Speciality
    public function start() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        // Check patient login
        $patientId = $_SESSION['patient_id'] ?? 0;
        if (!$patientId) {
            header("Location: " . (defined('BASE_PATH') ? BASE_PATH : '') . "/index.php?route=auth/login");
            exit;
        }

        // Fetch all specialities
        $specialities = $this->specialityModel->all();

        include __DIR__ . '/../views/patient/booking/step1_speciality.php';
    }

    // STEP 2: Select Doctor
    public function step2() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $patientId = $_SESSION['patient_id'] ?? 0;
        if (!$patientId) {
            header("Location: " . (defined('BASE_PATH') ? BASE_PATH : '') . "/index.php?route=auth/login");
            exit;
        }

        $specialityId = (int)($_GET['speciality_id'] ?? 0);
        if (!$specialityId) {
            header("Location: " . (defined('BASE_PATH') ? BASE_PATH : '') . "/index.php?route=patient/book/start");
            exit;
        }

        // Get Speciality Details (to show what they picked)
        $speciality = $this->specialityModel->find($specialityId);
        if (!$speciality) {
            die("Invalid Speciality");
        }

        // Fetch Doctors for this speciality (Only Available ones ideally, but let's show all with badges)
        $doctors = $this->doctorModel->findBySpeciality($specialityId);

        include __DIR__ . '/../views/patient/booking/step2_doctors.php';
    }

    // STEP 3: Select Date & Time Slot
    public function step3() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $patientId = $_SESSION['patient_id'] ?? 0;
        if (!$patientId) {
            header("Location: " . (defined('BASE_PATH') ? BASE_PATH : '') . "/index.php?route=auth/login");
            exit;
        }

        $doctorId = (int)($_GET['doctor_id'] ?? 0);
        $specialityId = (int)($_GET['speciality_id'] ?? 0);
        $date = $_GET['date'] ?? date('Y-m-d');

        if (!$doctorId || !$specialityId) {
            header("Location: " . (defined('BASE_PATH') ? BASE_PATH : '') . "/index.php?route=patient/book/step2&speciality_id=$specialityId");
            exit;
        }

        // Validate date (no past dates)
        if ($date < date('Y-m-d')) $date = date('Y-m-d');

        // Fetch Data
        $doctor = $this->doctorModel->find($doctorId);
        $speciality = $this->specialityModel->find($specialityId); // Needed for Duration
        
        if (!$doctor || !$speciality) die("Invalid Data");

        $duration = (int)$speciality['Consultation_Duration']; // e.g. 30 mins
        if ($duration < 5) $duration = 30; // Safety fallback

        // 1. Get Doctor's Schedule
        $dayName = date('D', strtotime($date)); // 'Mon', 'Tue'...
        $allAvail = $this->doctorModel->getAvailability($doctorId);
        $todaysAvail = array_filter($allAvail, function($row) use ($dayName) {
            return stripos($row['Available_Day'], $dayName) !== false; // Case-insensitive check
        });

        // 2. Get Occupied Slots
        $existing = $this->appointmentModel->getAppointmentsByDoctorAndDate($doctorId, $date);
        $occupied = array_column($existing, 'Appointment_Time'); // ['09:00:00', ...]

        // 3. Generate Slots
        $slots = [];
        foreach ($todaysAvail as $period) {
            $startTs = strtotime($date . ' ' . $period['Start_Time']);
            $endTs = strtotime($date . ' ' . $period['End_Time']);

            // If today, filter out passed time
            $nowTs = time();
            
            while (($startTs + ($duration * 60)) <= $endTs) {
                // Formatting
                $slotTime = date('H:i:s', $startTs);
                $displayTime = date('h:i A', $startTs);

                // Validation:
                // a) Not in past (if today)
                if ($date === date('Y-m-d') && $startTs < $nowTs) {
                    $startTs += ($duration * 60);
                    continue; 
                }

                // b) Not occupied
                if (!in_array($slotTime, $occupied)) {
                    $slots[] = [
                        'time' => $slotTime,
                        'display' => $displayTime
                    ];
                }

                $startTs += ($duration * 60);
            }
        }
        
        // Sort slots just in case
        usort($slots, function($a, $b) {
            return strcmp($a['time'], $b['time']);
        });

        include __DIR__ . '/../views/patient/booking/step3_slots.php';
    }

    // STEP 4: Checkout / Payment Simulation
    public function step4() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $patientId = $_SESSION['patient_id'] ?? 0;
        if (!$patientId) {
            header("Location: " . (defined('BASE_PATH') ? BASE_PATH : '') . "/index.php?route=auth/login");
            exit;
        }

        $doctorId = (int)($_GET['doctor_id'] ?? 0);
        $date = $_GET['date'] ?? '';
        $time = $_GET['time'] ?? '';

        if (!$doctorId || !$date || !$time) {
            die("Missing booking information");
        }

        $doctor = $this->doctorModel->find($doctorId);
        $speciality = $this->specialityModel->find($doctor['Speciality_Id']);
        
        include __DIR__ . '/../views/patient/booking/step4_checkout.php';
    }

    // COMPLETE: Process Booking
    public function complete() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $patientId = $_SESSION['patient_id'] ?? 0;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Invalid Request");
        }

        // Validate Inputs
        $doctorId = (int)($_POST['doctor_id'] ?? 0);
        $date = $_POST['date'] ?? '';
        $time = $_POST['time'] ?? '';
        $amount = (float)($_POST['amount'] ?? 0);
        $method = $_POST['payment_method'] ?? 'Card';
        $visitType = $_POST['visit_type'] ?? 'Online Consultation';
        $description = $_POST['symptoms'] ?? 'Regular Checkup';

        // 1. Simulate Payment Processing
        require_once __DIR__ . '/../models/PaymentModel.php';
        $paymentModel = new PaymentModel($this->db);
        
        // Generate Fake TXN ID
        $txnId = 'TXN' . strtoupper(uniqid()) . rand(100, 999);
        $paymentData = [
            'patient_id' => $patientId,
            'amount' => $amount,
            'method' => $method,
            'status' => 'APPROVED', // Simulated Success
            'transaction_id' => $txnId
        ];
        
        // We need a create method in PaymentModel. 
        // I will assume it exists or I'll need to create it.
        // Checking PaymentModel... it might not have 'create'. I'll add it if needed.
        // For now, let's write the code assuming I'll add 'create' next.
        $paymentId = $paymentModel->create($paymentData);

        if (!$paymentId) {
            die("Payment Failed");
        }

        // 2. Create Appointment
        $apptId = $this->appointmentModel->createManual(
            $patientId,
            $doctorId,
            $date,
            $time,
            $visitType,
            $description,
            $paymentId,
            'Pending' // Initial status
        );

        if ($apptId) {
            // Redirect to Success
            header("Location: " . (defined('BASE_PATH') ? BASE_PATH : '') . "/index.php?route=patient/book/success&id=$apptId");
        } else {
            die("Booking Error");
        }
    }
    
    // Success Page
    public function success() {
        $apptId = (int)($_GET['id'] ?? 0);
        include __DIR__ . '/../views/patient/booking/success.php';
    }
}
