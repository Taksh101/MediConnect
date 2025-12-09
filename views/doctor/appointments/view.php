<?php
// views/doctor/appointments/view.php
function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function route_url_local($r){ $base = defined('BASE_PATH') ? rtrim(BASE_PATH,'/') : ''; return $base . '/index.php?route=' . ltrim($r,'/'); }

// CSRF is already required by controller now, but good practice to ensure availability
if (!function_exists('csrf_token')) require_once __DIR__ . '/../../../config/csrf.php';

$a = $appt; // Convenience var

// --- Time & State Logic ---
date_default_timezone_set('Asia/Kolkata'); // Match User Local Time
$now = time();
$durationMinutes = (int)($a['Consultation_Duration'] ?? 60); // Default 60 if missing
$durationSeconds = $durationMinutes * 60;

$start = strtotime($a['Appointment_Date'] . ' ' . $a['Appointment_Time']);
$end = $start + $durationSeconds; 

// Determine Visual State
$state = 'UNKNOWN';
if ($a['Status'] === 'Pending') {
    $state = 'PENDING';
} elseif (in_array($a['Status'], ['Rejected', 'Cancelled'])) {
    $state = 'REJECTED';
} elseif ($a['Status'] === 'Completed') {
    $state = 'COMPLETED';
} elseif ($a['Status'] === 'Missed') {
    $state = 'MISSED';
} elseif ($a['Status'] === 'Approved') {
    if ($now < $start) {
        $state = 'UPCOMING';
    } elseif ($now >= $start && $now <= $end) {
        $state = 'ACTIVE';
    } else {
        $state = 'MISSED'; // Approved but time passed and not completed
    }
}

// Calculate Age
$age = 'N/A';
if (!empty($a['DOB'])) {
    $dob = new DateTime($a['DOB']);
    $today = new DateTime();
    $age = $dob->diff($today)->y;
}
?>
<?php
$pageTitle = 'MediConnect - Manage Appointment';
include __DIR__ . '/../../includes/doctorNavbar.php';
?>

<!-- Google Fonts: Outfit for Modern Premium Feel -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root {
        --brand-primary: #4f46e5;       /* Indigo 600 */
        --brand-secondary: #4338ca;     /* Indigo 700 */
        --brand-accent: #818cf8;        /* Indigo 400 */
        --bg-body: #f1f5f9;             /* Slate 100 */
        --bg-card: #ffffff;
        --text-main: #0f172a;           /* Slate 900 */
        --text-muted: #64748b;          /* Slate 500 */
        --border-color: #e2e8f0;        /* Slate 200 */
    }

    body {
        background-color: var(--bg-body);
        font-family: 'Outfit', sans-serif;
        color: var(--text-main);
    }
    
    .main-wrapper {
        margin-top: 90px;
        min-height: 90vh;
        padding-bottom: 60px;
    }

    /* Premium Cards */
    .glass-card {
        background: var(--bg-card);
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 20px;
        box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .glass-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.08);
    }

    /* Hero Header Area */
    .page-header {
        position: relative;
        padding-bottom: 2rem;
    }
    .back-btn {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        background: white;
        color: var(--text-muted);
        border-radius: 50px;
        font-weight: 500;
        font-size: 0.9rem;
        text-decoration: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        transition: all 0.2s;
    }
    .back-btn:hover {
        background: var(--brand-primary);
        color: white;
        transform: translateX(-5px);
    }

    /* Avatar & Patient Info */
    .patient-avatar-lg {
        width: 100px;
        height: 100px;
        border-radius: 30px;
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        color: var(--brand-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 800;
        box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.2);
        margin: 0 auto;
    }
    .info-grid-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        color: var(--text-muted);
        font-weight: 700;
        margin-bottom: 4px;
    }
    .info-grid-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-main);
    }

    /* Action Areas */
    .status-card-inner {
        padding: 3rem 2rem;
        text-align: center;
    }
    .bg-grid-pattern {
        background-image: radial-gradient(#6366f1 1px, transparent 1px);
        background-size: 20px 20px;
        opacity: 0.03;
    }
    
    /* Form Refinements */
    .form-control-lg-custom {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        font-size: 1rem;
        background: #f8fafc;
        transition: all 0.2s;
    }
    .form-control-lg-custom:focus {
        background: white;
        border-color: var(--brand-primary);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }
    label.form-label {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--text-main);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
    }
    label.form-label i { margin-right: 8px; color: var(--brand-primary); }

    /* Animations */
    .animate-in-up { animation: fadeInUp 0.6s ease-out forwards; opacity: 0; }
    @keyframes fadeInUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    .animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
</style>

<div class="main-wrapper container">
    
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 bg-success text-white mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= h($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 bg-danger text-white mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= h($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4 animate-in-up">
        <div>
            <a href="<?= route_url_local('doctor/appointments') ?>" class="back-btn mb-3">
                <i class="bi bi-arrow-left me-2"></i> All Appointments
            </a>
            <h2 class="fw-bold mb-0">Consultation Room</h2>
            <p class="text-muted small">ID #<?= str_pad($a['Appointment_Id'], 6, '0', STR_PAD_LEFT) ?> • <?= date('F j, Y', $start) ?></p>
        </div>
        
        <!-- Quick Status Badge -->
        <div>
             <?php 
                $badgeClass = 'bg-secondary text-white';
                if($state === 'PENDING') $badgeClass = 'bg-warning text-dark';
                elseif($state === 'ACTIVE') $badgeClass = 'bg-primary text-white';
                elseif($state === 'COMPLETED') $badgeClass = 'bg-success text-white';
                elseif($state === 'REJECTED') $badgeClass = 'bg-danger text-white';
                elseif($state === 'MISSED') $badgeClass = 'bg-secondary text-white';
             ?>
             <div class="px-4 py-2 rounded-pill fw-bold shadow-sm <?= $badgeClass ?>" style="font-size: 0.9rem;">
                 <?= $state ?>
             </div>
        </div>
    </div>

    <div class="row g-4 animate-in-up" style="animation-delay: 0.1s;">
        
        <!-- LEFT SIDEBAR: Patient Ident -->
        <div class="col-lg-4">
            <div class="glass-card position-relative h-100">
                <div class="bg-grid-pattern position-absolute top-0 start-0 w-100 h-100"></div>
                
                <div class="card-body p-4 position-relative">
                    <div class="text-center mb-4">
                        <div class="patient-avatar-lg mb-3">
                            <?= strtoupper($a['Name'][0] ?? '?') ?>
                        </div>
                        <h4 class="fw-bold mb-1"><?= h($a['Name']) ?></h4>
                        <div class="badge bg-light text-secondary border px-3 py-1 rounded-pill">
                            <?= h($a['Gender']) ?>, <?= h($age) ?> yrs
                        </div>
                    </div>

                    <div class="vstack gap-3 px-2">
                        <div class="d-flex align-items-center bg-white p-3 rounded-4 border shadow-sm">
                            <div class="me-3 text-primary fs-4"><i class="bi bi-telephone-fill"></i></div>
                            <div>
                                <div class="info-grid-label">Contact Number</div>
                                <div class="info-grid-value"><?= h($a['Phone']) ?></div>
                            </div>
                        </div>
                         <div class="d-flex align-items-center bg-white p-3 rounded-4 border shadow-sm">
                            <div class="me-3 text-primary fs-4"><i class="bi bi-envelope-fill"></i></div>
                            <div style="overflow:hidden;">
                                <div class="info-grid-label">Email Address</div>
                                <div class="info-grid-value text-truncate"><?= h($a['Email']) ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-grid">
                        <a href="<?= route_url_local('doctor/patients/view&patient_id=' . $a['Patient_Id']) ?>" class="btn btn-outline-primary fw-semibold py-2 rounded-pill">
                            <i class="bi bi-file-earmark-medical me-2"></i> View Medical Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT MAIN CONTENT -->
        <div class="col-lg-8">
            <div class="glass-card h-100">
                <div class="card-body p-5">
                    
                    <!-- Appointment Time Context -->
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-4 border mb-5">
                       <div class="d-flex align-items-center">
                            <div class="me-3 text-primary fs-4">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div>
                                <div class="info-grid-label">Appointment Time</div>
                                <div class="fw-bold text-dark fs-5">
                                    <?= date('h:i A', $start) ?> - <?= date('h:i A', $end) ?>
                                    <span class="text-muted fw-normal fs-6 ms-1">(<?= $durationMinutes ?> mins)</span>
                                </div>
                            </div>
                       </div>
                       <div class="text-end ps-3 border-start">
                            <div class="info-grid-label">Visit Type</div>
                            <div class="fw-bold text-primary"><?= h($a['Visit_Type']) ?></div>
                       </div>
                    </div>

                    <!-- DYNAMIC STATE CONTENT -->
                    
                    <?php if ($state === 'PENDING'): ?>
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-clipboard-data display-1 text-warning opacity-50"></i>
                            </div>
                            <h2 class="fw-bold mb-2">Request Pending</h2>
                            <p class="text-muted mb-4 mx-auto" style="max-width:400px;">The patient has requested a consultation. Review their profile and approve the slot if available.</p>
                            
                            <div class="d-flex justify-content-center gap-3">
                                <form method="POST" action="<?= route_url_local('doctor/appointments/update_status') ?>">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="appointment_id" value="<?= $a['Appointment_Id'] ?>">
                                    <button type="submit" name="status" value="Approved" class="btn btn-success rounded-pill px-5 py-3 fw-bold shadow-lg">
                                        <i class="bi bi-check-lg me-2"></i> Approve Request
                                    </button>
                                </form>
                                <form method="POST" action="<?= route_url_local('doctor/appointments/update_status') ?>">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="appointment_id" value="<?= $a['Appointment_Id'] ?>">
                                    <button type="submit" name="status" value="Rejected" class="btn btn-light border rounded-pill px-5 py-3 fw-bold text-danger hover-danger">
                                        <i class="bi bi-x-lg me-2"></i> Reject
                                    </button>
                                </form>
                            </div>
                        </div>

                    <?php elseif ($state === 'UPCOMING'): ?>
                         <div class="text-center py-5">
                            <div class="display-1 text-primary mb-4 animate-pulse">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                            <h2 class="fw-bold text-primary mb-2">Upcoming Consultation</h2>
                            <p class="text-muted fs-5 mb-4">Please wait for the scheduled start time.</p>
                            <div class="p-3 bg-blue-50 text-primary rounded-4 d-inline-block border border-primary-subtle">
                                Starts at <strong><?= date('h:i A', $start) ?></strong>
                            </div>
                        </div>

                    <?php elseif ($state === 'ACTIVE'): ?>
                        <div class="position-relative">
                            <div class="text-uppercase fw-bold text-primary small mb-3 tracking-wide">
                                <i class="bi bi-record-circle-fill me-2 animate-pulse"></i> Live Consultation Mode
                            </div>
                            
                            <form method="POST" action="<?= route_url_local('doctor/appointments/save_notes') ?>">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="appointment_id" value="<?= $a['Appointment_Id'] ?>">

                                <div class="mb-4">
                                    <label class="form-label"><i class="bi bi-thermometer-high"></i> Symptoms</label>
                                    <textarea name="symptoms" class="form-control form-control-lg-custom" rows="3" placeholder="What symptoms is the patient experiencing?" required><?= h($notes['Symptoms'] ?? '') ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label"><i class="bi bi-clipboard-pulse"></i> Diagnosis</label>
                                    <input type="text" name="diagnosis" class="form-control form-control-lg-custom" placeholder="Clinical diagnosis..." value="<?= h($notes['Diagnosis'] ?? '') ?>" required>
                                </div>

                                <div class="mb-5">
                                    <label class="form-label"><i class="bi bi-prescription2"></i> Prescription / Plan</label>
                                    <textarea name="advice" class="form-control form-control-lg-custom" rows="5" placeholder="Medications, advice, and next steps..." required><?= h($notes['Advice'] ?? '') ?></textarea>
                                </div>

                                <div class="d-flex justify-content-end pt-3 border-top">
                                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-lg">
                                        <i class="bi bi-check2-circle me-2"></i> Complete & Save
                                    </button>
                                </div>
                            </form>
                        </div>

                    <?php elseif ($state === 'COMPLETED'): ?>
                        <div class="text-center mb-5">
                            <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle mb-3" style="width:64px; height:64px; font-size:2rem;">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <h2 class="fw-bold text-success">Consultation Completed</h2>
                            <p class="text-muted">Summary of the visit</p>
                        </div>
                        
                        <div class="bg-light p-4 rounded-4 border mb-3">
                            <h6 class="text-uppercase text-muted fw-bold small mb-2">Diagnosis</h6>
                            <p class="fs-5 fw-bold text-dark mb-0"><?= h($notes['Diagnosis'] ?? 'No Diagnosis Recorded') ?></p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="bg-white p-4 rounded-4 border h-100">
                                    <h6 class="text-uppercase text-muted fw-bold small mb-2"><i class="bi bi-thermometer-half me-1"></i> Symptoms</h6>
                                    <p class="mb-0 text-secondary"><?= nl2br(h($notes['Symptoms'] ?? 'None')) ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-white p-4 rounded-4 border h-100">
                                    <h6 class="text-uppercase text-muted fw-bold small mb-2"><i class="bi bi-capsule me-1"></i> Prescription</h6>
                                    <p class="mb-0 text-secondary"><?= nl2br(h($notes['Advice'] ?? 'None')) ?></p>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($state === 'REJECTED'): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-x-circle display-1 text-danger mb-3"></i>
                            <h2 class="fw-bold text-danger">Appointment Rejected</h2>
                            <p class="text-muted mb-4">You have rejected this request.</p>
                            <div class="badge bg-info-subtle text-primary border border-info-subtle px-4 py-2 rounded-pill fs-6">
                                Payment Status: Refunded
                            </div>
                        </div>

                    <?php elseif ($state === 'MISSED'): ?>
                        <div class="text-center py-5">
                             <i class="bi bi-calendar-x display-1 text-secondary mb-3"></i>
                             <h2 class="fw-bold text-secondary">Appointment Missed</h2>
                             <p class="text-muted mb-4">You did not attend this appointment.</p>
                             <div class="badge bg-info-subtle text-primary border border-info-subtle px-4 py-2 rounded-pill fs-6">
                                Payment Status: Refunded
                             </div>
                        </div>

                    <?php else: ?>
                        <!-- MISSED -->
                         <div class="text-center py-5 opacity-75">
                            <i class="bi bi-slash-circle display-1 text-secondary mb-3"></i>
                            <h2 class="fw-bold text-dark">Missed Appointment</h2>
                            <p class="text-muted">The time slot passed without activity.</p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
