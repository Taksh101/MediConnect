<?php
// views/doctor/patients/view.php
// expects: $patient, $medical (from controller)

if (!function_exists('h')) { function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); } }

// Helper to show NA if empty
function showVal($val) {
    if ($val === null || $val === '' || $val === '-') return 'NA';
    return h($val);
}

$initial = isset($patient['Name']) && !empty($patient['Name']) ? strtoupper($patient['Name'][0]) : 'P';
$mp = $medical ?? []; // rename to align with admin logic

$pageTitle = 'MediConnect - Patient Medical Record';
include __DIR__ . '/../../includes/doctorNavbar.php';
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    body {
        background-color: #f8f9fa;
        color: #344767;
        font-family: 'Inter', sans-serif;
    }
    
    .row-equal { align-items: stretch; }

    /* Profile Card & Left Column */
    .profile-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        background: #fff;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .profile-header-bg {
        height: 110px;
        background: linear-gradient(135deg, #4f46e5 0%, #818cf8 100%);
        border-radius: 16px 16px 0 0;
        flex-shrink: 0;
    }
    
    .avatar-wrapper {
        margin-top: -60px;
        text-align: center;
        position: relative;
        flex-shrink: 0;
    }
    
    .avatar-circle {
        width: 100px;
        height: 100px;
        background: #fff;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        font-size: 2.5rem;
        font-weight: 700;
        color: #4f46e5;
        border: 4px solid rgba(255,255,255,0.8);
    }

    .patient-details-section {
        padding: 0 1.5rem 1.5rem 1.5rem;
        flex-grow: 1;
    }

    /* Right Side / Medical Profile */
    .right-column-wrapper {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .bio-card-header {
        padding: 1.5rem;
        border-bottom: 1px solid #f0f2f5;
    }

    .bio-card-body {
        padding: 1.5rem;
    }

    .medical-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr); 
        gap: 1.5rem;
    }
    
    @media (max-width: 768px) {
        .medical-grid { grid-template-columns: 1fr; }
    }

    .medical-item {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem;
        border: 1px solid #e2e8f0;
    }

    .label-small { font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600; margin-bottom: 4px; display: block; }
    .value-text { font-weight: 600; color: #334155; font-size: 0.95rem; }
    
    /* Make text areas nicer */
    .detail-box {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 0.5rem;
        border: 1px solid #e9ecef;
        min-height: 80px;
        font-size: 0.9rem;
    }
</style>

<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <!-- Breadcrumb or Back Button -->
            <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>

    <div class="row g-4 row-equal">
        
        <!-- LEFT COLUMN: Basic Info -->
        <div class="col-lg-4">
            <div class="profile-card">
                <div class="profile-header-bg"></div>
                
                <div class="avatar-wrapper">
                    <div class="avatar-circle"><?= $initial ?></div>
                </div>

                <div class="text-center mt-3 px-4 flex-shrink-0">
                    <h4 class="mb-1 fw-bold"><?= h($patient['Name']) ?></h4>
                    <p class="text-muted mb-3 text-sm">Patient ID: #<?= str_pad((string)($patient['Patient_Id']??0), 4, '0', STR_PAD_LEFT) ?></p>
                </div>

                <hr class="horizontal dark mx-4 my-3">

                <div class="patient-details-section">
                    <div class="row g-3">
                        <div class="col-12">
                            <span class="label-small"><i class="bi bi-envelope me-1"></i> Email</span>
                            <div class="value-text text-truncate"><?= showVal($patient['Email']) ?></div>
                        </div>
                        <div class="col-12">
                            <span class="label-small"><i class="bi bi-telephone me-1"></i> Phone</span>
                            <div class="value-text"><?= showVal($patient['Phone']) ?></div>
                        </div>
                        <div class="col-6">
                            <span class="label-small">Gender</span>
                            <div class="value-text"><?= showVal($patient['Gender']) ?></div>
                        </div>
                        <div class="col-6">
                            <span class="label-small">DOB</span>
                            <div class="value-text"><?= showVal($patient['DOB']) ?></div>
                        </div>
                        <div class="col-12">
                            <span class="label-small">Address</span>
                            <div class="value-text"><?= showVal($patient['Address']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Medical Profile -->
        <div class="col-lg-8">
            <div class="profile-card">
                <div class="bio-card-header d-flex align-items-center">
                    <div class="bg-primary-subtle text-primary p-2 rounded-3 me-3">
                        <i class="bi bi-activity fs-5"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">Medical Profile</h5>
                        <p class="text-muted small mb-0">Health metrics and history</p>
                    </div>
                </div>
                
                <div class="bio-card-body">
                    <?php if (empty($mp)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-clipboard-x display-4 opacity-50 mb-3"></i>
                            <p>No medical profile data available.</p>
                        </div>
                    <?php else: ?>
                        <div class="medical-grid">
                            <!-- Row 1 -->
                            <div class="medical-item">
                                <span class="label-small">Blood Group</span>
                                <div class="value-text text-primary"><?= showVal($mp['Blood_Group'] ?? '') ?></div>
                            </div>
                            <div class="medical-item">
                                <span class="label-small">BMI</span>
                                <div class="value-text"><?= showVal($mp['BMI'] ?? '') ?></div>
                            </div>
                            <div class="medical-item">
                                <span class="label-small">Weight / Height</span>
                                <div class="value-text"><?= showVal($mp['Weight_KG'] ?? '') ?> kg / <?= showVal($mp['Height_CM'] ?? '') ?> cm</div>
                            </div>

                            <!-- Row 2 -->
                            <div class="medical-item">
                                <span class="label-small">Diabetes</span>
                                <div class="value-text"><?= showVal($mp['Diabetes'] ?? '') ?></div>
                            </div>
                            <div class="medical-item">
                                <span class="label-small">Blood Pressure</span>
                                <div class="value-text"><?= showVal($mp['Blood_Pressure'] ?? '') ?></div>
                            </div>
                             <div class="medical-item">
                                <span class="label-small">Heart Condition</span>
                                <div class="value-text"><?= showVal($mp['Heart_Conditions'] ?? '') ?></div>
                            </div>
                            
                            <!-- Row 3 -->
                            <div class="medical-item">
                                <span class="label-small">Respiratory Issues</span>
                                <div class="value-text"><?= showVal($mp['Respiratory_Issues'] ?? '') ?></div>
                            </div>
                            <div class="medical-item">
                                <span class="label-small">Smoker</span>
                                <div class="value-text"><?= showVal($mp['Smoker'] ?? '') ?></div>
                            </div>
                            <div class="medical-item">
                                <span class="label-small">Alcohol Consumption</span>
                                <div class="value-text"><?= showVal($mp['Alcohol_Consumption'] ?? '') ?></div>
                            </div>
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        <!-- Detailed Text Fields -->
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-2 small text-uppercase text-muted">Allergies</h6>
                                <div class="detail-box"><?= nl2br(showVal($mp['Allergies'] ?? '')) ?></div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-2 small text-uppercase text-muted">Current Medication</h6>
                                <div class="detail-box"><?= nl2br(showVal($mp['Ongoing_Medication'] ?? '')) ?></div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-2 small text-uppercase text-muted">Past Surgeries</h6>
                                <div class="detail-box"><?= nl2br(showVal($mp['Past_Surgeries'] ?? '')) ?></div>
                            </div>
                             <div class="col-md-6">
                                <h6 class="fw-bold mb-2 small text-uppercase text-muted">Chronic Illnesses</h6>
                                <div class="detail-box"><?= nl2br(showVal($mp['Chronic_Illnesses'] ?? '')) ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>
<div style="height:60px;"></div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
