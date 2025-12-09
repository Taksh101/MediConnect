<?php
// views/doctor/profile.php
function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function route_url_local($r){ $base = defined('BASE_PATH') ? rtrim(BASE_PATH,'/') : ''; return $base . '/index.php?route=' . ltrim($r,'/'); }

$initial = isset($doctor['Name']) && !empty($doctor['Name']) ? strtoupper($doctor['Name'][0]) : 'D';
?>
<?php $pageTitle = 'MediConnect - My Profile';
include __DIR__ . '/../includes/doctorNavbar.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    body { background-color: #f5f7fb; font-family: 'Inter', sans-serif; }
    .container-wide { max-width: 1000px; }
    
    .profile-header {
        background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
        height: 150px;
        border-radius: 16px 16px 0 0;
        margin-bottom: -75px;
    }
    
    .profile-card {
        background: #fff;
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    
    .avatar-circle {
        width: 150px; height: 150px;
        background: #fff;
        border: 5px solid #fff;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 3.5rem; color: #4f46e5; font-weight: 700;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        margin: 0 auto;
    }
    
    .info-label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; display: block; margin-bottom: 0.25rem; }
    .info-value { font-size: 1rem; color: #1e293b; font-weight: 500; }
    .status-badge { padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 0.85rem; }
</style>

<div class="container container-wide py-5">
    
    <div class="profile-card">
        <div class="profile-header"></div>
        
        <div class="px-4 pb-5">
            <div class="text-center">
                <div class="avatar-circle mb-3">
                    <?= $initial ?>
                </div>
                <h2 class="fw-bold text-dark mb-1"><?= h($doctor['Name']) ?></h2>
                <p class="text-muted fs-5 mb-2"><?= h($doctor['Speciality_Name'] ?? 'General Practitioner') ?></p>
                <span class="badge bg-success-subtle text-success status-badge border border-success-subtle">
                    <?= h($doctor['Status']) ?>
                </span>
            </div>
            
            <hr class="my-5 opacity-10">
            
            <div class="row g-5 justify-content-center">
                <div class="col-lg-8">
                    <h5 class="fw-bold mb-4 border-bottom pb-2">Professional Information</h5>
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <span class="info-label"><i class="bi bi-envelope me-2"></i>Email Address</span>
                            <div class="info-value"><?= h($doctor['Email']) ?></div>
                        </div>
                        <div class="col-md-6">
                            <span class="info-label"><i class="bi bi-telephone me-2"></i>Contact Phone</span>
                            <div class="info-value"><?= h($doctor['Phone']) ?></div>
                        </div>
                    </div>
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <span class="info-label"><i class="bi bi-mortarboard me-2"></i>Qualification</span>
                            <div class="info-value"><?= h($doctor['Qualification']) ?></div>
                        </div>
                        <div class="col-md-6">
                            <span class="info-label"><i class="bi bi-briefcase me-2"></i>Experience</span>
                            <div class="info-value"><?= h($doctor['Experience_Years']) ?> Years</div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                         <span class="info-label"><i class="bi bi-person-badge me-2"></i>Biography</span>
                         <p class="text-muted mt-2" style="line-height: 1.6;"><?= nl2br(h($doctor['Bio'])) ?></p>
                    </div>
                    
                    <div class="alert alert-info d-flex align-items-center mt-5" role="alert">
                        <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                        <div>
                            To update your profile information or change your password, please contact the system administrator.
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
<div style="height:40px;"></div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
