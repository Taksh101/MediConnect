<?php
// views/patient/dashboard.php
// Expects: $patientName, $totalAppointments, $upcomingCount, $nextAppointment

if (!function_exists('h')) { function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); } }
$pageTitle = 'MediConnect - Patient Dashboard';
?>

<?php include __DIR__ . '/../includes/patientNavbar.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root {
        --primary-color: #4f46e5;
        --secondary-color: #f3f4f6;
        --card-radius: 12px;
        --text-dark: #1f2937;
        --text-muted: #6b7280;
    }
    
    html, body {
        /* Aligning with admin dashboard basics */
        background-color: #f5f7fb;
        font-family: 'Inter', sans-serif;
        color: var(--text-dark);
        margin: 0;
        padding: 0;
    }

    /* Content Layout */
    .container-wide {
        max-width: 1200px;
        margin-top: 60px; /* Exact Navbar height */
        margin-bottom: 20px;
    }

    /* Welcome Banner - Keeping the Patient feel but simplified structure */
    .welcome-banner {
        background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
        color: white;
        border-radius: var(--card-radius);
        padding: 30px;
        margin-bottom: 24px;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        position: relative;
        overflow: hidden;
    }
    .welcome-decoration { position: absolute; right: -10px; bottom: -30px; opacity: 0.1; font-size: 8rem; }
    
    /* Stat Cards - Matching Admin Structure */
    .stat-card {
        background: white;
        border-radius: var(--card-radius);
        padding: 24px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
        text-decoration: none;
        color: inherit;
        display: block;
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1); }
    .stat-label { font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em; color: var(--text-muted); margin-bottom: 8px; }
    .stat-value { font-size: 2rem; font-weight: 700; color: var(--text-dark); line-height: 1.1; }
    
    .card-left-border-blue { border-left: 5px solid #3b82f6; }
    .card-left-border-purple { border-left: 5px solid #8b5cf6; }
    
    /* Content Cards */
    .content-card {
        background: white;
        border-radius: var(--card-radius);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        border: 1px solid #e2e8f0;
        overflow: hidden;
        height: 100%;
    }
    .card-header-custom {
        background: white;
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .card-header-title { font-weight: 600; font-size: 1.1rem; margin: 0; color: var(--text-dark); }
    
    /* Quick Actions */
    .quick-list { padding: 0; margin: 0; list-style: none; }
    .quick-item { display: block; padding: 16px 24px; border-bottom: 1px solid #f1f5f9; text-decoration: none; color: var(--text-dark); transition: background 0.15s; display: flex; align-items: center; }
    .quick-item:last-child { border-bottom: none; }
    .quick-item:hover { background-color: #f8fafc; color: var(--primary-color); }
    .quick-icon { width: 36px; height: 36px; background: #eef2ff; color: var(--primary-color); border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 16px; font-size: 1.1rem; }
    
    /* Next Appt specific */
    .appt-date-box { background: #eff6ff; color: #1e40af; border-radius: 8px; padding: 10px; text-align: center; min-width: 70px; }
    .appt-date-day { font-size: 1.5rem; font-weight: 700; line-height: 1; }
    .appt-date-month { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
</style>

<div class="container container-wide pb-4">

    <!-- Welcome Section -->
    <div class="welcome-banner">
        <div class="d-flex justify-content-between align-items-center position-relative" style="z-index: 2;">
            <div>
                <h2 class="fw-bold mb-1">Hello, <?= h($patientName) ?></h2>
                <p class="mb-0 opacity-75">Welcome to your patient dashboard.</p>
            </div>
            <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/book/start" class="btn btn-light text-primary fw-bold shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> New Appointment
            </a>
        </div>
        <i class="bi bi-activity welcome-decoration"></i>
    </div>

    <div class="row g-4 mb-4">
        <!-- Stats Row -->
        <div class="col-md-6 col-lg-6">
            <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/appointments" class="stat-card card-left-border-blue">
                <div class="stat-label">Upcoming Appointments</div>
                <div class="stat-value"><?= $upcomingCount ?></div>
            </a>
        </div>
        <div class="col-md-6 col-lg-6">
            <div class="stat-card card-left-border-purple">
                <div class="stat-label">Total Visits</div>
                <div class="stat-value"><?= $totalAppointments ?></div>
            </div>
        </div>
    </div>

    <div class="row g-4 main-content-row">
        <!-- Next Appointment Column -->
        <div class="col-lg-8">
            <div class="content-card">
                <div class="card-header-custom">
                    <h5 class="card-header-title">Next Scheduled Visit</h5>
                    <?php if ($nextAppointment): 
                        $nStatus = $nextAppointment['Status'];
                        $nClass = 'bg-warning text-dark';
                        if (stripos($nStatus, 'approv') !== false || stripos($nStatus, 'confirm') !== false) {
                            $nClass = 'bg-success text-white';
                        }
                    ?>
                         <span class="badge <?= $nClass ?> rounded-pill px-3"><?= h($nStatus) ?></span>
                    <?php endif; ?>
                </div>
                <div class="p-4">
                    <?php if ($nextAppointment): ?>
                        <div class="d-flex align-items-center mb-4">
                            <div class="appt-date-box me-4" style="min-width: 80px; padding: 15px;">
                                <div class="appt-date-day" style="font-size: 1.8rem;"><?= (new DateTime($nextAppointment['Appointment_Date']))->format('d') ?></div>
                                <div class="appt-date-month"><?= (new DateTime($nextAppointment['Appointment_Date']))->format('M') ?></div>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-1"><?= h($nextAppointment['Visit_Type'] ?? 'Consultation') ?></h4>
                                <div class="d-flex align-items-center text-muted mb-2">
                                    <i class="bi bi-person-circle fs-5 me-2"></i>
                                    <?php 
                                        $dName = $nextAppointment['Doctor_Name'] ?? '';
                                        // Check if name already starts with Dr. (case insensitive)
                                        $displayDr = (stripos(trim($dName), 'Dr.') === 0 || stripos(trim($dName), 'Dr ') === 0) ? '' : 'Dr. ';
                                    ?>
                                    <span class="fw-medium text-dark"><?= $displayDr . h($dName) ?></span>
                                    <span class="mx-2">&bull;</span>
                                    <span><?= h($nextAppointment['Speciality_Name'] ?? '') ?></span>
                                </div>
                                <div class="text-muted small">
                                    <i class="bi bi-clock me-2"></i>
                                    <?= (new DateTime($nextAppointment['Appointment_Time']))->format('h:i A') ?> 
                                    <span class="mx-2">|</span> 
                                    <i class="bi bi-geo-alt me-2"></i>
                                    <?= h($nextAppointment['Visit_Type'] ?? 'Consultation') ?>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/appointments/view&id=<?= $nextAppointment['Appointment_Id'] ?>" class="btn btn-primary px-4">
                                View Details
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <div class="text-muted mb-3" style="font-size: 3rem; opacity:0.3;"><i class="bi bi-calendar-x"></i></div>
                            <h6 class="fw-bold">No upcoming appointments</h6>
                            <p class="text-muted small mb-3">You are all caught up! Need to see a doctor?</p>
                            <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/book/start" class="btn btn-primary btn-sm">Book Now</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions Column -->
        <div class="col-lg-4">
            <div class="content-card">
                <div class="card-header-custom">
                    <h5 class="card-header-title">Quick Actions</h5>
                </div>
                <div class="quick-list">
                    <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/book/start" class="quick-item">
                        <div class="quick-icon"><i class="bi bi-calendar-plus"></i></div>
                        <div>
                            <div class="fw-bold">Book Appointment</div>
                            <div class="small text-muted">Schedule a new visit</div>
                        </div>
                    </a>
                    <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/appointments" class="quick-item">
                        <div class="quick-icon" style="background:#f0fdf4; color:#16a34a;"><i class="bi bi-calendar-check"></i></div>
                        <div>
                            <div class="fw-bold">View Appointments</div>
                            <div class="small text-muted">View all visits</div>
                        </div>
                    </a>
                    <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/profile" class="quick-item">
                        <div class="quick-icon" style="background:#fff7ed; color:#ea580c;"><i class="bi bi-person-gear"></i></div>
                        <div>
                            <div class="fw-bold">Profile</div>
                            <div class="small text-muted">Manage your details</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<div style="height: 40px;"></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
