<?php
// views/admin/dashboard.php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/AdminModel.php';
require_admin_login();
global $db;
$model = new AdminModel($db);

// Configuration for table display: CHANGED TO 3
const APPOINTMENTS_PER_PAGE = 3;

// counts + list
$totalPatients = $model->countPatients();
$totalDoctors = $model->countDoctors();
$totalSpecialities = $model->countSpecialities();
$todaysCount = $model->countTodaysAppointments();
$todays = $model->getTodaysAppointments();

// Logic for table display limit
$displayAppointments = array_slice($todays, 0, APPOINTMENTS_PER_PAGE);
$showingCount = min(APPOINTMENTS_PER_PAGE, count($todays));

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function route_url_local($r){ $base = defined('BASE_PATH') ? rtrim(BASE_PATH,'/') : ''; return $base . '/index.php?route=' . ltrim($r,'/'); }

// Helper function to format status badges
function get_status_badge($status) {
    // Cleaned up function body to remove hidden characters
    $status = h($status);
    $class = 'bg-secondary';
    switch ($status) {
        case 'Approved':
            $class = 'bg-success';
            break;
        case 'Pending':
            $class = 'bg-warning text-dark';
            break;
        case 'Cancelled':
            $class = 'bg-danger';
            break;
    }
    return "<span class=\"badge {$class}\">{$status}</span>";
}
?>

<?php include __DIR__ . '/../includes/adminNavbar.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root {
    --color-primary: #0d6efd;
    --card-radius: 12px;
}

/* FIX: Remove all fixed height/overflow constraints, allow natural scrolling */
html, body { 
    /* height: auto; 
    overflow-y: auto;  */
}
body { 
    background: #f5f7fb; 
    padding-top: 0 !important; 
    padding-bottom: 0 !important; 
    margin-top: 0 !important; 
    overflow-y: auto; 
} 
/* FIX: Ensure content starts correctly after the Navbar */
.container.container-wide {
    margin-top: 20px; /* Increased margin-top to account for the sticky Navbar height (60px) + space */
    margin-bottom: 20px;
    max-width:1200px;
}


/* Stat Card Styles (Retained) */
.stat-card {
    border-radius: 12px;
    padding: 25px; 
    transition: transform 0.2s, box-shadow 0.2s;
    text-decoration: none;
    color: inherit; 
    min-height: 120px; 
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.05);
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1); }
.stat-card .card-label { font-size: 0.9rem; font-weight: 500; margin-bottom: 5px; color: #4a5568; } 
.stat-card .card-value { font-size: 2.2rem; font-weight: 700; line-height: 1.1; color: #1a202c; } 
.stat-card.bg-blue { border-left: 5px solid var(--color-primary); background: #fff; }
.stat-card.bg-green { border-left: 5px solid var(--color-success); background: #fff; }
.stat-card.bg-cyan { border-left: 5px solid var(--color-info); background: #fff; }
.stat-card.bg-orange { border-left: 5px solid var(--color-warning); background: #fff; } /* CORRECTED: 5mpx -> 5px */


/* Main Content/Table Card */
.content-card {
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    border: 1px solid #e2e8f0;
}

/* 4. Natural Symmetry and Scrollable Table Area */
.main-content-row {
    display: flex; 
    align-items: stretch; /* Ensures columns stretch to max content height (Symmetry) */
}
.card.h-100 { display: flex; flex-direction: column; } 

.table-area { 
    /* Reduced max-height for better fit when data is present */
    max-height: 240px;
    overflow-y: auto; 
    padding: 0;
    flex-grow: 1; /* Allows table area to push the footer */
}

/* FIX: Force empty state div to be tall to push the card footer down */
.empty-content-box {
    min-height: 240px; /* This height will force the Appointment card to be tall */
    display: flex;
    flex-direction: column;
    justify-content: center; /* Vertically centers the text inside the tall box */
    flex-grow: 1; /* This is key to pushing the footer down when empty */
}

/* Quick Links Symmetry Fix (To Match Table Height) */
.quick-links-body {
    flex-grow: 1; /* This ensures the quick links card body stretches */
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    padding-top: 1rem;
    padding-bottom: 1rem;
}

/* Added flex-grow: 0 to the card-footer to ensure it stays at the bottom */
.card-footer {
    flex-shrink: 0;
}


.quick-btn {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    border-radius: var(--card-radius);
    background: #fff;
    color: #1a202c;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    transition: background 0.15s, border-color 0.15s;
    font-weight: 500;
}
.quick-btn:hover {
    background: #f0f4f9;
    border-color: #d1d9e4;
    color: var(--color-primary);
}
</style>

<div class="container container-wide py-4">

    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h1 class="mb-0 fw-bolder text-dark">Admin Dashboard</h1>
            <p class="text-muted mb-0">Welcome back, Admin. Here's your system overview.</p>
            </div>
    </div>
    
    <div class="row g-4 mb-4"> 
        
        <div class="col-xl-3 col-md-6">
            <a href="<?= h(route_url_local('admin/patients')) ?>" class="stat-card bg-blue d-block">
                <div class="card-label text-primary">TOTAL PATIENTS</div>
                <div class="card-value"><?= h($totalPatients) ?></div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="<?= h(route_url_local('admin/doctors')) ?>" class="stat-card bg-green d-block">
                <div class="card-label text-success">TOTAL DOCTORS</div>
                <div class="card-value"><?= h($totalDoctors) ?></div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="<?= h(route_url_local('admin/specialities')) ?>" class="stat-card bg-cyan d-block">
                <div class="card-label text-info">SPECIALITIES</div>
                <div class="card-value"><?= h($totalSpecialities) ?></div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="<?= h(route_url_local('admin/appointments')) ?>" class="stat-card bg-orange d-block">
                <div class="card-label text-warning">TODAY'S APPOINTMENTS</div>
                <div class="card-value"><?= h($todaysCount) ?></div>
            </a>
        </div>
    </div>
    
    <div class="row g-4 main-content-row mb-4">
        
        <div class="col-lg-8">
            <div class="card content-card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-semibold text-dark">Today's Appointments</h5>
                    <!-- <a href="<?= h(route_url_local('admin/appointments')) ?>" class="btn btn-sm btn-outline-primary">View All</a> -->
                </div>

                <div class="table-area">
                    <?php if (empty($todays)): ?>
                        <div class="p-5 text-center empty-content-box">
                            <h4 class="text-muted">No Appointments Today</h4>
                            <p class="small text-secondary">The schedule is clear. This dashboard shows the top <?= APPOINTMENTS_PER_PAGE ?> appointments.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-borderless mb-0">
                                <thead class="small">
                                    <tr>
                                        <th style="width:5%">#</th>
                                        <th style="width:15%">Time</th>
                                        <th style="width:30%">Patient</th>
                                        <th style="width:30%">Doctor</th>
                                        <th style="width:20%">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    foreach($displayAppointments as $i => $a): 
                                    ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td class="fw-medium text-primary"><?= h(substr($a['Appointment_Time'] ?? '',0,5)) ?></td>
                                            <td><?= h($a['patient_name'] ?? '—') ?></td>
                                            <td><?= h($a['doctor_name'] ?? '—') ?></td>
                                            <td><?= get_status_badge($a['Status'] ?? '—') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center">
                    <div class="small text-muted">Showing <?= $showingCount ?> of <?= $todaysCount ?> total appointments today.</div>
                    <a href="<?= h(route_url_local('admin/appointments')) ?>" class="small fw-semibold text-decoration-none">View All &rarr;</a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card content-card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold text-dark">Quick Access</h5>
                </div>
                <div class="card-body quick-links-body">
                    <div class="d-grid gap-3">
                        <a class="quick-btn" href="<?= h(route_url_local('admin/specialities')) ?>">
                            <span class="me-2 text-info">&#x272A;</span> Manage Specialities
                        </a>
                        <a class="quick-btn" href="<?= h(route_url_local('admin/doctors')) ?>">
                            <span class="me-2 text-success">&#x2b;</span> Doctor Management
                        </a>
                        <a class="quick-btn" href="<?= h(route_url_local('admin/patients')) ?>">
                            <span class="me-2 text-primary">&#x1f464;</span> Patient Records
                        </a>
                        <a class="quick-btn" href="<?= h(route_url_local('admin/payments')) ?>">
                            <span class="me-2 text-warning">&#x1f4b8;</span> View Payments
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>