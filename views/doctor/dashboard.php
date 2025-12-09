<?php
// views/doctor/dashboard.php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/DoctorModel.php';

// Ensure user is logged in as doctor
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['doctor_id']) || $_SESSION['role'] !== 'DOCTOR') {
    header('Location: /MediConnect/index.php?route=auth/login');
    exit;
}

global $db;
$model = new DoctorModel($db);
$doctorId = $_SESSION['doctor_id'];

// Configuration
const APPOINTMENTS_LIMIT = 3;

// Fetch stats
$totalPatients = $model->countMyUniquePatients($doctorId);
$totalAppointments = $model->countMyAppointments($doctorId);
$todaysCount = $model->countMyTodaysAppointments($doctorId);
$pendingCount = $model->countMyPendingAppointments($doctorId);

// Fetch today's appointments (Limit 3 directly from logic or slice)
// Efficiently fetching more to show "view all" context if needed, but display only 3
$todays = $model->getMyTodaysAppointments($doctorId, 10); 
$displayAppointments = array_slice($todays, 0, APPOINTMENTS_LIMIT);

// Helpers
function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function route_url_local($r){ $base = defined('BASE_PATH') ? rtrim(BASE_PATH,'/') : ''; return $base . '/index.php?route=' . ltrim($r,'/'); }

function get_status_badge($status) {
    $class = 'bg-secondary';
    $text = 'text-white';
    
    switch ($status) {
        case 'Approved': 
            $class = 'bg-primary bg-opacity-10'; 
            $text = 'text-primary'; 
            break;
        case 'Pending': 
            $class = 'bg-warning bg-opacity-10'; 
            $text = 'text-warning'; 
            break;
        case 'Completed': 
            $class = 'bg-success bg-opacity-10'; 
            $text = 'text-success'; 
            break;
        case 'Rejected':
        case 'Cancelled':
            $class = 'bg-danger bg-opacity-10';
            $text = 'text-danger';
            break;
    }
    
    // Using subtle badges for premium look
    return "<span class=\"badge {$class} {$text} border border-opacity-10\">{$status}</span>";
}
$pageTitle = 'MediConnect - Doctor Dashboard';
?>

<?php include __DIR__ . '/../includes/doctorNavbar.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
/* Dashboard Specific Styles */
body { background: #f8fafc; }
.container-wide { max-width: 1200px; margin-top: 30px; margin-bottom: 80px; }

/* Cards */
.modern-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
    height: 100%;
}
.stat-card {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }

.stat-value { font-size: 2.25rem; font-weight: 700; color: #1e293b; line-height: 1; }
.stat-label { font-size: 0.875rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
.stat-icon { position: absolute; right: 1.5rem; top: 1.5rem; font-size: 2rem; opacity: 0.2; }

/* Colors */
.border-l-primary { border-left: 4px solid #4f46e5; }
.border-l-success { border-left: 4px solid #10b981; }
.border-l-warning { border-left: 4px solid #f59e0b; }
.border-l-info { border-left: 4px solid #0ea5e9; }

/* Table */
.table-custom thead th {
    background: #f8fafc;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}
.table-custom tbody td {
    padding: 1rem 1.5rem;
    vertical-align: middle;
    color: #334155;
    font-weight: 500;
    border-bottom: 1px solid #f1f5f9;
}
.table-custom tbody tr:last-child td { border-bottom: none; }
.table-custom tbody tr:hover { background-color: #f8fafc; }

/* Quick Actions */
.quick-btn {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    color: #475569;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
}
.quick-btn:hover {
    background: #f1f5f9;
    color: #4f46e5;
    transform: translateX(5px);
}
.quick-btn i { font-size: 1.25rem; margin-right: 1rem; color: #64748b; }
.quick-btn:hover i { color: #4f46e5; }
</style>

<div class="container container-wide">
    
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h2 class="fw-bold text-dark mb-1">Doctor Dashboard</h2>
            <p class="text-secondary mb-0">Overview of your practice and schedule.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-white text-dark border shadow-sm py-2 px-3 fw-normal">
                <i class="bi bi-calendar3 me-2"></i> <?= date('l, F j, Y') ?>
            </span>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="modern-card stat-card border-l-primary">
                <div>
                    <div class="stat-label mb-2">My Patients</div>
                    <div class="stat-value text-primary"><?= $totalPatients ?></div>
                </div>
                <i class="bi bi-people-fill stat-icon text-primary"></i>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="modern-card stat-card border-l-success">
                <div>
                    <div class="stat-label mb-2">Total Appts</div>
                    <div class="stat-value text-success"><?= $totalAppointments ?></div>
                </div>
                <i class="bi bi-calendar-check-fill stat-icon text-success"></i>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="modern-card stat-card border-l-warning">
                <div>
                    <div class="stat-label mb-2">Today's Load</div>
                    <div class="stat-value text-warning"><?= $todaysCount ?></div>
                </div>
                <i class="bi bi-clock-history stat-icon text-warning"></i>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="modern-card stat-card border-l-info">
                <div>
                    <div class="stat-label mb-2">Pending Req.</div>
                    <div class="stat-value text-info"><?= $pendingCount ?></div>
                </div>
                <i class="bi bi-hourglass-split stat-icon text-info"></i>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Today's Schedule -->
        <div class="col-lg-8">
            <div class="modern-card h-100">
                <div class="card-header bg-white px-4 py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-1">Today's Schedule</h5>
                        <p class="text-muted small mb-0">Your next <span class="fw-bold text-dark"><?= count($displayAppointments) ?></span> scheduled appointments</p>
                    </div>
                    <a href="<?= route_url_local('doctor/appointments&tab=today') ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        View All
                    </a>
                </div>
                
                <div class="table-responsive">
                    <?php if (empty($displayAppointments)): ?>
                        <div class="text-center py-5">
                            <div class="mb-3 text-muted opacity-25">
                                <i class="bi bi-calendar-x display-1"></i>
                            </div>
                            <h6 class="text-muted fw-bold">No appointments for today</h6>
                            <p class="small text-secondary">Enjoy your free time!</p>
                        </div>
                    <?php else: ?>
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th class="py-2">Time</th>
                                    <th class="py-2">Patient Name</th>
                                    <th class="py-2">Status</th>
                                    <th class="text-end py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($displayAppointments as $a): ?>
                                    <tr>
                                        <td class="py-3">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-clock text-muted me-2"></i>
                                                <span class="fw-bold text-dark">
                                                    <?= date('h:i A', strtotime($a['Appointment_Time'])) ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <div class="fw-semibold"><?= h($a['patient_name'] ?? 'Unknown') ?></div>
                                        </td>
                                        <td class="py-3">
                                            <?= get_status_badge($a['Status']) ?>
                                        </td>
                                        <td class="text-end py-3">
                                            <a href="<?= route_url_local('doctor/appointments/view&id=' . $a['Appointment_Id']) ?>" class="btn btn-sm btn-light border text-primary">
                                                Manage
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Access -->
        <div class="col-lg-4">
            <div class="modern-card p-4 h-100">
                <h5 class="fw-bold mb-4">Quick Actions</h5>
                <div class="d-grid gap-3">
                    <a href="<?= route_url_local('doctor/appointments&tab=pending') ?>" class="quick-btn">
                        <i class="bi bi-person-check-fill"></i>
                        <span>Review Pending Requests</span>
                    </a>
                    <a href="<?= route_url_local('doctor/appointments&tab=upcoming') ?>" class="quick-btn">
                        <i class="bi bi-calendar-event"></i>
                        <span>View Upcoming Appointments</span>
                    </a>
                    <a href="<?= route_url_local('doctor/profile') ?>" class="quick-btn">
                        <i class="bi bi-person-circle"></i>
                        <span>View Profile</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
