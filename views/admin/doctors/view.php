<?php
// views/admin/doctors/view.php

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

function getStatusBadge($status) {
    $s = strtolower($status);
    if($s === 'available') return 'bg-success-subtle text-success';
    if($s === 'unavailable') return 'bg-danger-subtle text-danger';
    return 'bg-secondary-subtle text-secondary';
}

$initial = isset($doctor['Name']) && !empty($doctor['Name']) ? strtoupper($doctor['Name'][0]) : 'D';

include __DIR__ . '/../../includes/adminNavbar.php';
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    body {
        background-color: #f8f9fa;
        color: #344767;
    }
    
    /* 1. Force the ROW to stretch columns to equal height */
    .row-equal {
        align-items: stretch; 
    }

    /* 2. Styling for the Card Container */
    .profile-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        background: #fff;
        width: 100%;
    }

    /* LEFT SIDE STYLES */
    .left-card {
        height: 100%; /* Fills the entire column height */
        display: flex;
        flex-direction: column;
    }

    .profile-header-bg {
        height: 110px;
        background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
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
        color: #3a416f;
        border: 4px solid rgba(255,255,255,0.8);
    }

    /* RIGHT SIDE STYLES */
    .right-column-wrapper {
        height: 100%;
        display: flex;
        flex-direction: column;
        gap: 2.2rem; /* REMOVED GAP: Elements will touch vertically */
    }

    /* Stat Cards (Fixed Height) */
    .stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.02);
        display: flex;
        align-items: center;
        height: 100%; 
        /* Optional: Flatten bottom corners if you want them to look merged */
        /* border-bottom-left-radius: 0; */
        /* border-bottom-right-radius: 0; */
    }
    
    /* Wrapper for the stat row to ensure no bottom margin interferes */
    .stats-row-wrapper {
        margin-bottom: 0; /* Ensures 0 gap */
        padding-bottom: 0;
        flex-shrink: 0;
    }

    .stat-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    /* Bio Card (Dynamic Height) */
    .bio-card {
        flex-grow: 1; /* Takes ALL remaining space */
        display: flex;
        flex-direction: column;
        overflow: hidden; 
        min-height: 200px; 
        /* Optional: Flatten top corners if you want them to look merged with top cards */
        /* border-top-left-radius: 0; */
        /* border-top-right-radius: 0; */
        /* Add a small top border to separate visually if they touch exactly */
        /* border-top: 1px solid #f0f2f5; */
        margin-top: 1rem; /* Tiny margin to prevent shadow overlap ugliness, set to 0 if you want literal touch */
    }
    
    /* OVERRIDING MARGIN FOR EXACT TOUCH: */
    .bio-card.touching {
        margin-top: 0 !important;
        border-top-left-radius: 4px; /* Slightly reducing corner radius where they meet looks better */
        border-top-right-radius: 4px;
    }

    .bio-card-header {
        padding: 1.5rem 1.5rem 1rem 1.5rem;
        flex-shrink: 0;
    }

    .bio-card-body {
        padding: 0 1.5rem 1.5rem 1.5rem;
        flex-grow: 1; 
        overflow-y: auto; 
        min-height: 0; 
    }

    /* Custom Scrollbar */
    .bio-card-body::-webkit-scrollbar { width: 6px; }
    .bio-card-body::-webkit-scrollbar-track { background: transparent; }
    .bio-card-body::-webkit-scrollbar-thumb { background: #d1d7dc; border-radius: 4px; }

    .label-small { font-size: 0.75rem; text-transform: uppercase; color: #8392ab; font-weight: 600; }
    .value-text { font-weight: 500; color: #344767; }
</style>

<div class="container py-5">
    
    <div class="row g-4 row-equal">
        
        <div class="col-lg-4">
            <div class="profile-card left-card pb-4">
                <div class="profile-header-bg"></div>
                
                <div class="avatar-wrapper">
                    <div class="avatar-circle"><?= $initial ?></div>
                </div>

                <div class="text-center mt-3 px-4 flex-shrink-0">
                    <h4 class="mb-1 fw-bold"><?= h($doctor['Name']) ?></h4>
                    <p class="text-muted mb-2 text-sm"><?= h($doctor['Qualification'] ?? '') ?></p>
                    
                    <span class="badge rounded-pill <?= getStatusBadge($doctor['Status'] ?? '') ?> px-3 py-2 mb-4">
                        <?= h(strtoupper($doctor['Status'] ?? '-')) ?>
                    </span>

                    <div class="d-flex gap-2 justify-content-center mb-4">
                        <a href="<?= (defined('BASE_PATH')?BASE_PATH:'') ?>/index.php?route=admin/doctors/edit&id=<?= (int)$doctor['Doctor_Id'] ?>" class="btn btn-sm btn-dark px-4 shadow-sm">Edit</a>
                        <a href="<?= (defined('BASE_PATH')?BASE_PATH:'') ?>/index.php?route=admin/doctors/availability&doctor_id=<?= (int)$doctor['Doctor_Id'] ?>" class="btn btn-sm btn-outline-dark px-4">Availability</a>
                    </div>
                </div>

                <hr class="horizontal dark mx-4 my-3 flex-shrink-0">

                <div class="px-4 flex-grow-1">
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <span class="label-small">Doctor ID</span>
                            <span class="value-text">#<?= str_pad((string)$doctor['Doctor_Id'], 4, '0', STR_PAD_LEFT) ?></span>
                        </div>
                        <div class="col-6">
                            <span class="label-small">Joined</span>
                            <span class="value-text"><?= h($doctor['Created_At'] ?? date('M Y')) ?></span>
                        </div>
                    </div>

                    <h6 class="text-uppercase text-muted fs-7 fw-bold mb-3 mt-4">Contact Info</h6>
                    
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-envelope text-primary me-3 fs-5"></i>
                        <div class="overflow-hidden">
                            <span class="text-truncate d-block text-sm fw-bold text-dark"><?= h($doctor['Email']) ?></span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <i class="bi bi-telephone text-primary me-3 fs-5"></i>
                        <div>
                            <span class="text-sm fw-bold text-dark"><?= h($doctor['Phone'] ?? '-') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="right-column-wrapper">
                
                <div class="row g-4 stats-row-wrapper mb-0">
                    <div class="col-md-6">
                        <div class="stat-card">
                            <div class="stat-icon-box bg-primary-subtle text-primary me-3">
                                <i class="bi bi-heart-pulse-fill"></i>
                            </div>
                            <div>
                                <span class="label-small">Speciality</span>
                                <h5 class="mb-0 fw-bold"><?= h($doctor['Speciality_Name'] ?? 'General') ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stat-card">
                            <div class="stat-icon-box bg-warning-subtle text-warning me-3">
                                <i class="bi bi-award-fill"></i>
                            </div>
                            <div>
                                <span class="label-small">Experience</span>
                                <h5 class="mb-0 fw-bold"><?= (int)($doctor['Experience_Years'] ?? 0) ?> Years</h5>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-card bio-card touching">
                    <div class="bio-card-header d-flex align-items-center">
                        <i class="bi bi-person-lines-fill text-primary me-2 fs-5"></i>
                        <h5 class="mb-0 fw-bold">About Doctor</h5>
                    </div>
                    
                    <div class="bio-card-body">
                        <p class="text-muted leading-relaxed mb-0" style="line-height: 1.8;">
                            <?php if(!empty($doctor['Bio'])): ?>
                                <?= nl2br(h($doctor['Bio'])) ?>
                            <?php else: ?>
                                <span class="text-muted fst-italic opacity-50">No biography information available.</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<div style="height:60px;"></div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>