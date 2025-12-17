<?php
// views/patient/appointments/index.php
// Expects: $appointments (array), $page, $perPage, $total, $totalPages, $startIndex

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>

<?php $pageTitle = 'MediConnect - My History';
include __DIR__ . '/../../includes/patientNavbar.php'; ?>

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
    .modern-card { background:#fff;border:none;border-radius:16px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); overflow:hidden; }
    .table-custom thead th { background-color:#f8fafc; color:#475569; font-size:0.75rem; text-transform:uppercase; padding:16px 24px; white-space:nowrap; }
    .table-custom tbody td { padding:24px 24px; vertical-align:top; border-bottom:1px solid #f1f5f9; color:var(--text-secondary); font-size:0.95rem; }
    
    .action-btn-group { display:flex; gap:8px; justify-content:flex-end; align-items:center; }
    .btn-icon { width:40px; height:40px; display:flex; align-items:center; justify-content:center; border-radius:8px; border:none; background:transparent; color:var(--text-secondary); transition:all 0.15s; }
    .btn-icon.view { background: #dbeafe; color: #0369a1; }
    .btn-icon.view:hover { background: #0ea5e9; color: #fff; transform:translateY(-2px); box-shadow:0 6px 14px rgba(14,165,233,0.2); }
    
    .empty-state-modern { padding: 60px 20px; text-align:center; }
    .empty-icon-circle { width:100px; height:100px; background:linear-gradient(180deg,#eef2ff,#f8fafc); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 18px; font-size:36px; color:var(--primary-color); box-shadow:0 6px 18px rgba(15,23,42,0.04); }
    .pagination-wrapper { padding: 20px; border-top: 1px solid #f3f4f6; }
    .page-link { border: none; color: #6b7280; margin: 0 4px; border-radius: 8px; font-weight: 500; transition: all 0.2s; display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; padding: 0; }
    .page-link:hover { background-color: #f3f4f6; color: #111827; }
    .page-item.active .page-link { background-color: var(--primary-color); color: white; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3); }
    
    .status-badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:0.75rem; font-weight:600; }
    .status-pending { background-color:var(--warning-bg); color:var(--warning-color); }
    .status-approved { background-color:var(--success-color); color:white; } /* User preferred green for approved */
    .status-completed { background-color:var(--success-bg); color:var(--success-color); }
    .status-rejected { background-color:var(--danger-bg); color:var(--danger-color); }
    .status-missed { background-color:#f1f5f9; color:#64748b; border: 1px solid #e2e8f0; }
</style>

<div class="container container-wide py-3">
    <div class="d-flex justify-content-between align-items-end mb-4 page-header">
        <div>
            <h4 class="mb-1">My Appointments</h4>
            <p class="text-muted mb-0 small">Track your consultation history and upcoming visits.</p>
        </div>
        <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/book/start" class="btn btn-primary fw-medium px-4">
            <i class="bi bi-plus-lg me-2"></i>Book New
        </a>
    </div>

    <div class="card modern-card">
        <div class="card-body p-0">
            <?php if (empty($appointments)): ?>
                <div class="empty-state-modern">
                    <div class="empty-icon-circle"><i class="bi bi-calendar-x"></i></div>
                    <h5 class="fw-bold text-dark">No appointments found</h5>
                    <p class="mb-3 text-muted">You haven't booked any appointments yet.</p>
                    <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/book/start" class="btn btn-primary px-4">Book Your First Visit</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th style="width:60px">#</th>
                                <th>Doctor</th>
                                <th>Speciality</th>
                                <th style="width:180px">Date & Time</th>
                                <th style="width:130px">Status</th>
                                <th style="width:100px" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $i => $a): ?>
                                <tr>
                                    <td class="text-secondary small fw-bold"><?= h($startIndex + $i) ?></td>
                                    <td>
                                        <?php 
                                            // Dr Prefix Logic
                                            $dName = $a['Doctor_Name'] ?? '';
                                            $prefix = (stripos(trim($dName), 'Dr.') === 0 || stripos(trim($dName), 'Dr ') === 0) ? '' : 'Dr. ';
                                        ?>
                                        <div class="fw-bold text-dark"><?= $prefix . h($dName) ?></div>
                                        <div class="text-muted small"><?= h($a['Visit_Type'] ?? 'General') ?></div>
                                    </td>
                                    <td>
                                        <div class="text-dark fw-medium"><?= h($a['Speciality_Name'] ?? '-') ?></div>
                                    </td>
                                    <td>
                                        <?php 
                                            $dateObj = new DateTime($a['Appointment_Date'] . ' ' . $a['Appointment_Time']);
                                            echo '<div class="fw-medium">' . $dateObj->format('M d, Y') . '</div>';
                                            echo '<div class="text-muted small">' . $dateObj->format('h:i A') . '</div>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            $s = strtolower($a['Status']);
                                            $class = 'status-pending';
                                            if(strpos($s, 'approv')!==false || strpos($s, 'confirm')!==false) $class='status-approved';
                                            if(strpos($s, 'complet')!==false) $class='status-completed';
                                            if(strpos($s, 'reject')!==false || strpos($s, 'cancel')!==false) $class='status-rejected';
                                            if(strpos($s, 'miss')!==false) $class='status-missed';
                                        ?>
                                        <span class="status-badge <?= $class ?>"><?= h($a['Status']) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <div class="action-btn-group">
                                            <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/appointments/view&id=<?= (int)$a['Appointment_Id'] ?>&page=<?= $page ?>" class="btn-icon view" title="View Details"><i class="bi bi-eye-fill"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination-wrapper d-flex justify-content-center">
                        <nav aria-label="appointments-pagination">
                            <ul class="pagination mb-0">
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/appointments&page=<?= max(1, $page-1) ?>"><i class="bi bi-chevron-left"></i></a>
                                </li>
                                <?php
                                $visible = 5;
                                $start = max(1, $page - floor($visible/2));
                                $end = min($totalPages, $start + $visible - 1);
                                if ($end - $start + 1 < $visible) {
                                    $start = max(1, $end - $visible + 1);
                                }
                                for ($p = $start; $p <= $end; $p++): ?>
                                    <li class="page-item <?= ($p === $page) ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/appointments&page=<?= $p ?>"><?= $p ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/appointments&page=<?= min($totalPages, $page+1) ?>"><i class="bi bi-chevron-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</div>
 <div style="height:50px;"></div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
