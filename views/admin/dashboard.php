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
    $status = h($status);
    $class = 'bg-secondary';
    switch ($status) {
        case 'Approved': $class = 'bg-success'; break;
        case 'Pending': $class = 'bg-warning text-dark'; break;
        case 'Completed': $class = 'bg-success'; break;
        case 'Rejected': case 'Cancelled': $class = 'bg-danger'; break;
        case 'Missed': $class = 'bg-secondary'; break;
    }
    return "<span class=\"badge {$class} rounded-pill px-3 py-1 fw-normal\">{$status}</span>";
}
$pageTitle = 'MediConnect - Admin Dashboard';
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
        <!-- Main Content Area -->
        <div class="col-lg-8">
            <div class="modern-card h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Today's Appointments</h5>
                        <small class="text-muted">Today's system-wide appointments</small>
                    </div>
                     <!-- <a href="<?= route_url_local('admin/appointments') ?>" class="btn btn-sm btn-light border text-primary fw-medium">View All</a> -->
                </div>
                
                <div class="table-responsive">
                    <?php if (empty($displayAppointments)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-check text-muted opacity-25" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No appointments scheduled for today.</p>
                        </div>
                    <?php else: ?>
                        <table class="table table-custom mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 text-uppercase text-muted small fw-bold" style="width: 30%;">Time</th>
                                    <th class="text-uppercase text-muted small fw-bold" style="width: 25%;">Patient</th>
                                    <th class="text-uppercase text-muted small fw-bold" style="width: 25%;">Doctor</th>
                                    <th class="text-uppercase text-muted small fw-bold" style="width: 20%;">Status</th>
                                    <th class="text-end pe-4 text-uppercase text-muted small fw-bold">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($displayAppointments as $a): ?>
                                    <tr>
                                        <!-- Time Column -->
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded p-2 me-3 text-center" style="min-width: 50px;">
                                                    <div class="fw-bold text-dark"><?= date('h:i', strtotime($a['Appointment_Time'])) ?></div>
                                                    <div class="small text-muted text-uppercase"><?= date('A', strtotime($a['Appointment_Time'])) ?></div>
                                                </div>
                                                <div>
                                                    <div class="small text-muted">Duration</div>
                                                    <div class="fw-semibold text-dark">60 min</div>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <!-- Patient Column -->
                                        <td>
                                            <div class="fw-bold text-dark"><?= h($a['patient_name']) ?></div>
                                            <div class="small text-muted"><?= h($a['patient_phone']) ?></div>
                                        </td>

                                        <!-- Doctor Column -->
                                        <td>
                                            <div class="fw-bold text-dark"><?= h($a['doctor_name']) ?></div>
                                            <div class="small text-muted">Specialist</div>
                                        </td>

                                        <!-- Status Column -->
                                        <td>
                                            <?= get_status_badge($a['Status']) ?>
                                        </td>
                                        
                                        <!-- Action Column -->
                                        <td class="text-end pe-4">
                                             <a href="<?= route_url_local('admin/appointments/view&id=' . $a['Appointment_Id']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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