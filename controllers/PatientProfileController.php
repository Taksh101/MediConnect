<?php
// MediConnect/controllers/PatientProfileController.php

if (session_status() === PHP_SESSION_NONE) session_start();

class PatientProfileController
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * Show form (GET). Loads profile (if any) and includes the view.
     */
    public function show()
    {
        // include CSRF helper and model safely
        $csrfPath = __DIR__ . '/../config/csrf.php';
        if (!file_exists($csrfPath)) {
            // friendly error page
            http_response_code(500);
            echo "<h1>500</h1><p>Missing file: " . htmlspecialchars($csrfPath) . "</p>";
            exit;
        }
        require_once $csrfPath;

        $modelPath = __DIR__ . '/../models/PatientMedicalProfile.php';
        if (file_exists($modelPath)) require_once $modelPath;

        $profile = null;
        if (!empty($_SESSION['patient_id']) && class_exists('PatientMedicalProfile')) {
            $pm = new PatientMedicalProfile($this->db);
            $profile = $pm->getByPatient(intval($_SESSION['patient_id']));
        }

        // include the view (view should call csrf_field())
        $viewPath = __DIR__ . '/../views/patient/medical_profile.php';
        if (file_exists($viewPath)) {
            require $viewPath;
            return;
        }

        http_response_code(500);
        echo "<h1>500</h1><p>Missing view: " . htmlspecialchars($viewPath) . "</p>";
        exit;
    }

    /**
     * Save form (POST) — returns JSON.
     */
    public function save()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            // include CSRF helper
            $csrfPath = __DIR__ . '/../config/csrf.php';
            if (!file_exists($csrfPath)) {
                echo json_encode(['status'=>'error','message'=>'Server misconfigured (csrf missing)']);
                return;
            }
            require_once $csrfPath;

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['status'=>'error','message'=>'Invalid method']);
                return;
            }

            if (!csrf_verify($_POST['csrf_token'] ?? '')) {
                echo json_encode(['status'=>'error','message'=>'Invalid CSRF token']);
                return;
            }

            if (empty($_SESSION['patient_id'])) {
                echo json_encode(['status'=>'error','message'=>'Not authenticated']);
                return;
            }

            // simple helper
            $get = function($k, $d='') { return isset($_POST[$k]) ? trim((string)$_POST[$k]) : $d; };

            // allowed lists
            $allowed_bg = ['A+','A-','B+','B-','AB+','AB-','O+','O-','Unknown'];
            $allowed_yesno = ['Yes','No'];
            $allowed_bp = ['Normal','Low','High'];
            $allowed_smoker = ['YES','NO','FORMER'];
            $allowed_alcohol = ['YES','NO','Occasional'];

            // read + validate
            $blood_group = $get('blood_group');
            if (!in_array($blood_group, $allowed_bg, true)) { echo json_encode(['status'=>'error','message'=>'Invalid blood group']); return; }

            $diabetes = $get('diabetes');
            if (!in_array($diabetes, $allowed_yesno, true)) { echo json_encode(['status'=>'error','message'=>'Invalid diabetes']); return; }

            $bp = $get('blood_pressure');
            if (!in_array($bp, $allowed_bp, true)) { echo json_encode(['status'=>'error','message'=>'Invalid blood pressure']); return; }

            $heart = $get('heart_conditions');
            if (!in_array($heart, $allowed_yesno, true)) { echo json_encode(['status'=>'error','message'=>'Invalid heart conditions']); return; }

            $resp = $get('respiratory_issues');
            if (!in_array($resp, $allowed_yesno, true)) { echo json_encode(['status'=>'error','message'=>'Invalid respiratory issues']); return; }

            $smoker = $get('smoker');
            if (!in_array($smoker, $allowed_smoker, true)) { echo json_encode(['status'=>'error','message'=>'Invalid smoker']); return; }

            $alcohol = $get('alcohol');
            if (!in_array($alcohol, $allowed_alcohol, true)) { echo json_encode(['status'=>'error','message'=>'Invalid alcohol']); return; }

            // optional text fields - max 255
            $allergies = substr($get('allergies',''), 0, 255);
            $med = substr($get('medication',''), 0, 255);
            $surgeries = substr($get('surgeries',''), 0, 255);
            $illnesses = substr($get('illnesses',''), 0, 255);

            // height & weight optional
            $height_raw = $get('height_cm','');
            $weight_raw = $get('weight_kg','');
            $height = null; $weight = null;

            if ($height_raw !== '') {
                if (!is_numeric($height_raw)) { echo json_encode(['status'=>'error','message'=>'Invalid height']); return; }
                $height = round(floatval($height_raw), 2);
                if ($height < 30.00 || $height > 300.00) { echo json_encode(['status'=>'error','message'=>'Height out of range']); return; }
            }

            if ($weight_raw !== '') {
                if (!is_numeric($weight_raw)) { echo json_encode(['status'=>'error','message'=>'Invalid weight']); return; }
                $weight = round(floatval($weight_raw), 2);
                if ($weight < 1.00 || $weight > 200.00) { echo json_encode(['status'=>'error','message'=>'Weight out of range']); return; }
            }

            $bmi = null;
            if ($height !== null && $weight !== null) {
                $h = $height / 100.0;
                if ($h > 0) $bmi = round($weight / ($h * $h), 2);
            }

            // load model
            $modelPath = __DIR__ . '/../models/PatientMedicalProfile.php';
            if (!file_exists($modelPath)) {
                echo json_encode(['status'=>'error','message'=>'Server misconfigured (model missing)']);
                return;
            }
            require_once $modelPath;
            $pm = new PatientMedicalProfile($this->db);

            $data = [
                'Patient_Id' => intval($_SESSION['patient_id']),
                'Blood_Group' => $blood_group,
                'Diabetes' => $diabetes,
                'Blood_Pressure' => $bp,
                'Heart_Conditions' => $heart,
                'Respiratory_Issues' => $resp,
                'Allergies' => $allergies,
                'Ongoing_Medication' => $med,
                'Past_Surgeries' => $surgeries,
                'Chronic_Illnesses' => $illnesses,
                'Smoker' => $smoker,
                'Alcohol_Consumption' => $alcohol,
                'Height_CM' => $height,
                'Weight_KG' => $weight,
                'BMI' => $bmi
            ];

            $ok = $pm->save($data);
            if ($ok) {
                // mark DB as complete (existing)
$stmt = $this->db->prepare("UPDATE Patients SET Is_Profile_Complete = 1 WHERE Patient_Id = ?");
$pid = intval($_SESSION['patient_id']);
$stmt->bind_param("i", $pid);
$stmt->execute();

// also update session flag so further routing checks see this immediately
// use the exact session key your app relies on for profile-complete checks.
// below uses 'is_profile_complete' — change if your auth uses a different key.
$_SESSION['is_profile_complete'] = 1;

// respond success
echo json_encode(['status' => 'success']);
return;

            }

            echo json_encode(['status'=>'error','message'=>'DB save failed']);
            return;

        } catch (Throwable $e) {
            // dev-friendly message — in production hide details
            http_response_code(500);
            echo json_encode(['status'=>'error','message'=>'Server error','debug'=>$e->getMessage()]);
            return;
        }
    }
}
