<?php
// views/patient/appointments/view.php
// Expects: $appointment (array with join data and 'notes')

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$status = $appointment['Status'];
$s_lower = strtolower($status);
$statusClass = 'bg-warning text-dark';
$statusIcon = 'bi-hourglass-split';

if (strpos($s_lower, 'approv') !== false) {
    $statusClass = 'bg-success text-white';
    $statusIcon = 'bi-check-circle-fill';
} elseif (strpos($s_lower, 'complet') !== false) {
    $statusClass = 'bg-success text-white';
    $statusIcon = 'bi-check-all';
} elseif (strpos($s_lower, 'miss') !== false) {
    $statusClass = 'bg-secondary text-white';
    $statusIcon = 'bi-calendar-x';
} elseif (strpos($s_lower, 'reject') !== false || strpos($s_lower, 'cancel') !== false) {
    $statusClass = 'bg-danger text-white';
    $statusIcon = 'bi-x-circle-fill';
}

$dateObj = new DateTime($appointment['Appointment_Date']);
$timeObj = new DateTime($appointment['Appointment_Time']);
?>
<?php $pageTitle = 'MediConnect - Appointment Summary';
include __DIR__ . '/../../includes/patientNavbar.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    body { background-color: #f3f4f6; font-family: 'Inter', sans-serif; color: #111827; }
    .container-wide { max-width: 1000px; padding-top: 2rem; padding-bottom: 3rem; }
    .back-link { text-decoration: none; color: #6b7280; font-weight: 500; font-size: 0.9rem; display: inline-flex; align-items: center; margin-bottom: 1rem; transition: color 0.15s; }
    .back-link:hover { color: #111827; }
    
    .card-section { background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 0; padding: 24px; height: 100%; }
    .section-title { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; font-weight: 700; margin-bottom: 16px; border-bottom: 1px solid #f3f4f6; padding-bottom: 10px; }
    
    .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; }
    .info-item label { display: block; font-size: 0.75rem; color: #6b7280; margin-bottom: 4px; font-weight: 500; }
    .info-item div { font-size: 0.95rem; font-weight: 600; color: #1f2937; }
    
    .status-banner { display: flex; align-items: center; gap: 12px; padding: 16px 24px; border-radius: 12px; margin-bottom: 24px; }
    .status-banner i { font-size: 1.5rem; }
    .status-banner h4 { margin: 0; font-weight: 700; font-size: 1.1rem; }
    
    .notes-box { background: #f8fafc; border-radius: 12px; padding: 16px; border: 1px solid #e2e8f0; }
    .note-line { margin-bottom: 12px; }
    .note-line:last-child { margin-bottom: 0; }
    .note-label { font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 2px; }
    .note-content { font-size: 0.95rem; color: #334155; line-height: 1.5; }
    
    .payment-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; background: #e0e7ff; color: #3730a3; }
    .payment-badge.approved { background: #dcfce7; color: #166534; }
    .payment-badge.failed { background: #fee2e2; color: #991b1b; }
    .payment-badge.refunded { background: #dbeafe; color: #1e40af; }
</style>

<div class="container container-wide">
    <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/appointments" class="back-link">
        <i class="bi bi-arrow-left me-1"></i> Back to My Appointments
    </a>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold m-0">Appointment #<?= h($appointment['Appointment_Id']) ?></h3>
            <span class="text-muted small">Booked on <?= h((new DateTime($appointment['Created_At']))->format('M j, Y h:i A')) ?></span>
        </div>
    </div>

    <div class="status-banner <?= $statusClass ?>">
        <i class="bi <?= $statusIcon ?>"></i>
        <div>
            <div style="font-size:0.8rem; opacity:0.9;">Current Status</div>
            <h4><?= h($status) ?></h4>
        </div>
    </div>

    <!-- Balanced Grid Layout -->
    <div class="row g-4 mb-4">
        <!-- Appointment Details -->
        <div class="col-md-6">
            <div class="card-section">
                <div class="section-title"><i class="bi bi-calendar-event me-2"></i>Appointment Details</div>
                <div class="info-grid mb-4">
                    <div class="info-item">
                        <label>Date</label>
                        <div><?= $dateObj->format('l, F j, Y') ?></div>
                    </div>
                    <div class="info-item">
                        <label>Time</label>
                        <div><?= $timeObj->format('h:i A') ?></div>
                    </div>
                    <div class="info-item">
                        <label>Visit Type</label>
                        <div class="mt-2">
                            <span class="badge bg-light text-dark border px-3 py-2" style="font-size: 0.95rem; font-weight: 500;margin-left:-5px;">
                                <?= h($appointment['Visit_Type']) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="info-item">
                    <label>Reason for Visit</label>
                    <div style="font-weight:400;"><?= h($appointment['Visit_Description']) ?></div>
                </div>
            </div>
        </div>

        <!-- Patient Details (Restored for grid balance) -->
        <div class="col-md-6">
            <div class="card-section">
                <div class="section-title"><i class="bi bi-person me-2"></i>My Information</div>
                <div class="d-flex align-items-center mb-4">
                    <div style="width:40px;height:40px;background:#e0e7ff;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#4f46e5;font-weight:bold;margin-right:12px;">
                        <?= strtoupper(substr($appointment['Patient_Name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div>
                        <div class="fw-bold"><?= h($appointment['Patient_Name']) ?></div>
                        <div class="small text-muted">ID: <?= h($appointment['Patient_Id']) ?></div>
                    </div>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Email</label>
                        <div style="font-weight:400; font-size:0.9rem;"><?= h($appointment['Patient_Email'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-item">
                        <label>Phone</label>
                        <div style="font-weight:400; font-size:0.9rem;"><?= h($appointment['Patient_Phone'] ?? 'N/A') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Doctor Details -->
        <div class="col-md-6">
            <div class="card-section">
                <div class="section-title"><i class="bi bi-heart-pulse me-2"></i>Doctor</div>
                <?php 
                    $dName = $appointment['Doctor_Name'];
                    $displayDr = (stripos(trim($dName), 'Dr.') === 0 || stripos(trim($dName), 'Dr ') === 0) ? '' : 'Dr. ';
                ?>
                <div class="fw-bold fs-5 mb-1"><?= $displayDr . h($dName) ?></div>
                <div class="text-primary small mb-3"><?= h($appointment['Speciality_Name']) ?></div>
                <div class="info-item">
                    <label>Qualification</label>
                    <div style="font-weight:400; font-size:0.9rem;"><?= h($appointment['Qualification']) ?></div>
                </div>
            </div>
        </div>

        <!-- Payment Details -->
        <div class="col-md-6">
            <div class="card-section">
                <div class="section-title"><i class="bi bi-credit-card me-2"></i>Payment Details</div>
                <?php if ($appointment['Payment_Amount']): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted small">Total Paid</span>
                        <span class="fs-5 fw-bold text-success">₹<?= number_format($appointment['Payment_Amount'], 2) ?></span>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Status</label>
                            <div>
                                <?php 
                                    $pStatus = strtolower($appointment['Payment_Status']);
                                    $apptStatus = strtolower($appointment['Status']);
                                    $pClass = '';
                                    $displayStatus = $appointment['Payment_Status'];
                                    
                                    if(strpos($pStatus, 'approv')!==false) $pClass='approved';
                                    if(strpos($pStatus, 'fail')!==false) $pClass='failed';
                                    
                                    // Override for Refunded
                                    if(strpos($apptStatus, 'reject')!==false || strpos($apptStatus, 'miss')!==false || strpos($apptStatus, 'cancel')!==false) {
                                        $pClass = 'approved'; // Use green or specific refunded class? User asked for Blue/Green previously. Let's stick to a clean look or add a new .refunded class.
                                        // Actually user complained about Red previously. Let's use 'info' style locally or just reuse approved (green) as requested "green or blue".
                                        // Patient view styles: .approved (green), .failed (red). I'll add .refunded (blue).
                                        $displayStatus = 'Refunded'; 
                                        $pClass = 'refunded';
                                    }
                                ?>
                                <span class="payment-badge <?= $pClass ?>"><?= h($displayStatus) ?></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <label>Method</label>
                            <div style="font-weight:400;"><?= h($appointment['Payment_Method']) ?></div>
                        </div>
                    </div>
                    <div class="info-item mt-3">
                        <label>Transaction ID</label>
                        <div style="font-weight:400; font-family:monospace; font-size:0.85rem; letter-spacing:-0.5px;"><?= h($appointment['Transaction_Id']) ?></div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-credit-card opacity-50 mb-2" style="font-size:1.5rem;"></i>
                        <p class="small mb-0">No payment record found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Consultation Notes (Full Width) -->
         <div class="col-12">
            <div class="card-section">
                <div class="section-title"><i class="bi bi-journal-medical me-2"></i>Doctor's Notes</div>
                <?php if (!empty($appointment['notes'])): ?>
                    <div class="notes-box">
                        <div class="row">
                            <div class="col-md-4 note-line">
                                <div class="note-label">Symptoms</div>
                                <div class="note-content"><?= h($appointment['notes']['Symptoms'] ?? 'N/A') ?></div>
                            </div>
                             <div class="col-md-4 note-line">
                                <div class="note-label">Diagnosis</div>
                                <div class="note-content"><?= h($appointment['notes']['Diagnosis'] ?? 'N/A') ?></div>
                            </div>
                             <div class="col-md-4 note-line">
                                <div class="note-label">Advice / Prescription</div>
                                <div class="note-content"><?= h($appointment['notes']['Advice'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-file-earmark-x" style="font-size:2rem; opacity:0.5;"></i>
                        <p class="mt-2 mb-0">No consultation notes available yet.</p>
                        <p class="small text-muted">Notes will appear here after the doctor completes the consultation.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
 <div style="height:50px;"></div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
