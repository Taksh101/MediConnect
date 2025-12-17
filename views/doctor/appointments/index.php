<?php
// views/doctor/appointments/index.php
function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function route_url_local($r){ $base = defined('BASE_PATH') ? rtrim(BASE_PATH,'/') : ''; return $base . '/index.php?route=' . ltrim($r,'/'); }

function getStatusBadge($status) {
    // Logic for "Refunded" display (Virtual Status)
    if (in_array($status, ['Rejected', 'Cancelled'])) {
        return 'status-rejected'; // Will label as "Refunded" in the view logic if needed, or just style it red
    }
    if ($status === 'Approved') return 'status-approved';
    if ($status === 'Pending') return 'status-pending';
    if ($status === 'Completed') return 'status-completed';
    if ($status === 'Missed') return 'status-missed';
    return 'bg-secondary';
}

$tab = $_GET['tab'] ?? 'today';
$pageTitle = 'MediConnect - My Schedule';
include __DIR__ . '/../../includes/doctorNavbar.php';
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root {
        --primary-color: #4f46e5;
        --primary-hover: #4338ca;
        --bg-color: #f3f4f6;
        --text-main: #111827;
        --text-secondary: #4b5563;
        --success-color: #10b981;
        --success-bg: #d1fae5;
        --warning-color: #f59e0b;
        --warning-bg: #fef3c7;
        --danger-color: #ef4444;
        --danger-bg: #fee2e2;
        --info-color: #3b82f6;
        --info-bg: #dbeafe;
    }
    body { background-color: var(--bg-color); font-family: 'Inter', sans-serif; color: var(--text-main); }
    .container-wide { max-width: 1200px; padding-top: 28px; padding-bottom: 28px; }
    .page-header h4 { font-weight:700; letter-spacing:-0.5px; color:#000; }
    
    /* Modern Card & Table */
    .modern-card { background:#fff;border:none;border-radius:16px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); overflow:hidden; }
    .table-custom thead th { background-color:#f8fafc; color:#475569; font-size:0.75rem; text-transform:uppercase; padding:16px 24px; white-space:nowrap; }
    .table-custom tbody td { padding:24px 24px; vertical-align:middle; border-bottom:1px solid #f1f5f9; color:var(--text-secondary); font-size:0.95rem; }
    
    .status-badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:0.75rem; font-weight:600; }
    .status-pending { background-color:var(--warning-bg); color:var(--warning-color); }
    .status-approved { background-color:var(--success-bg); color:var(--success-color); }
    .status-completed { background-color:var(--success-bg); color:var(--success-color); }
    .status-rejected { background-color:var(--danger-bg); color:var(--danger-color); }
    .status-missed { background-color:#f1f5f9; color:#64748b; border: 1px solid #e2e8f0; }

    .btn-icon-soft { 
        width: 36px; height: 36px; 
        display: inline-flex; align-items: center; justify-content: center; 
        border-radius: 8px; 
        background-color: var(--info-bg); color: var(--info-color);
        transition: all 0.2s;
        border: none;
    }
    .btn-icon-soft:hover { background-color: var(--primary-color); color: #fff; transform: translateY(-2px); }

    /* Custom Tabs */
    .nav-pills .nav-link {
        color: var(--text-secondary);
        font-weight: 500;
        padding: 0.6rem 1.2rem;
        border-radius: 10px;
        margin-right: 0.5rem;
    }
    .nav-pills .nav-link.active {
        background-color: var(--primary-color);
        color: #fff;
    }
    
    .empty-state { padding: 4rem 2rem; text-align: center; }
    .empty-icon { font-size: 3.5rem; color: #cbd5e1; margin-bottom: 1rem; }
    
    /* Responsive tweaks */
    @media (max-width: 768px) {
        .table-responsive { border: 0; }
    }

    /* Consistent Pagination Styles */
    .pagination-wrapper { padding: 20px 0; }
    .page-link { border: none; color: #6b7280; margin: 0 4px; border-radius: 8px; font-weight: 500; transition: all 0.2s; display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; padding: 0; }
    .page-link:hover { background-color: #f3f4f6; color: #111827; }
    .page-item.active .page-link { background-color: var(--primary-color); color: white; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3); }
</style>

<div class="container container-wide">
    <div class="d-flex justify-content-between align-items-end mb-4 page-header">
        <div>
            <h4 class="mb-1">My Appointments</h4>
            <p class="text-muted mb-0 small">Manage your schedule and patient consultations.</p>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-pills mb-4" id="apptTabs">
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'today' ? 'active' : '' ?>" href="<?= route_url_local('doctor/appointments&tab=today') ?>">Today</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'upcoming' ? 'active' : '' ?>" href="<?= route_url_local('doctor/appointments&tab=upcoming') ?>">Upcoming</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'pending' ? 'active' : '' ?>" href="<?= route_url_local('doctor/appointments&tab=pending') ?>">Pending Requests</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'history' ? 'active' : '' ?>" href="<?= route_url_local('doctor/appointments&tab=history') ?>">History</a>
        </li>
    </ul>

    <div class="card modern-card">
        <div class="card-body p-0">
            <?php if (empty($appointments)): ?>
                <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-calendar-range"></i></div>
                    <h5 class="fw-bold text-dark">No appointments found</h5>
                    <p class="text-muted mb-0">There are no appointments in this category.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width:250px">Date & Time</th>
                                <th>Patient</th>
                                <th>Details</th>
                                <th>Status</th>
                                <th class="pe-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $a): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?= h(date('M d, Y', strtotime($a['Appointment_Date']))) ?></div>
                                        <div class="text-muted small"><?= h(date('h:i A', strtotime($a['Appointment_Time']))) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= h($a['Patient_Name']) ?></div>
                                        <div class="text-muted small"><?= h($a['Patient_Phone']) ?></div>
                                    </td>
                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 180px;">
                                            <?= h($a['Visit_Description']) ?>
                                        </span>
                                        <div class="text-muted small"><?= h($a['Visit_Type']) ?></div>
                                    </td>
                                    <td>
                                        <?php 
                                            // Status Badge
                                            $dispStatus = $a['Status'];
                                            $badgeClass = getStatusBadge($a['Status']);
                                        ?>
                                        <span class="status-badge <?= $badgeClass ?>">
                                            <?= h($dispStatus) ?>
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="<?= route_url_local('doctor/appointments/view&id=' . $a['Appointment_Id'] . '&tab=' . $tab . '&page=' . $page) ?>" class="btn-icon-soft" title="View Details">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Pagination -->
    <?php if (isset($totalPages) && $totalPages > 1): ?>
    <div class="pagination-wrapper d-flex justify-content-center">
        <nav aria-label="Page navigation">
            <ul class="pagination mb-0">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= route_url_local('doctor/appointments&tab=' . $tab . '&page=' . max(1, $page - 1)) ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                <?php
                $visible = 5;
                $start = max(1, $page - floor($visible/2));
                $end = min($totalPages, $start + $visible - 1);
                if ($end - $start + 1 < $visible) {
                    $start = max(1, $end - $visible + 1);
                }
                for ($p = $start; $p <= $end; $p++): ?>
                    <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="<?= route_url_local('doctor/appointments&tab=' . $tab . '&page=' . $p) ?>">
                            <?= $p ?>
                        </a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= route_url_local('doctor/appointments&tab=' . $tab . '&page=' . min($totalPages, $page + 1)) ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
<div style="height:80px;"></div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
