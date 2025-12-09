<?php
// views/admin/patients/appointments.php
// expects: $appointments, $patient, $page, $perPage, $total, $totalPages
if (!function_exists('h')) { function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

// normalize pagination vars
$page = isset($page) ? (int)$page : max(1, (int)($_GET['page'] ?? 1));
$perPage = isset($perPage) ? (int)$perPage : 10;
$total = isset($total) ? (int)$total : (is_array($appointments) ? count($appointments) : 0);
$totalPages = isset($totalPages) ? (int)$totalPages : (int)max(1, ceil($total / max(1, $perPage)));

include __DIR__ . '/../../includes/adminNavbar.php';
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
:root {
    --primary-color: #4f46e5;
    --bg-color: #f3f4f6;
    --text-main: #111827;
    --text-secondary: #4b5563;
}
body { background:var(--bg-color); font-family:'Inter',sans-serif; color:var(--text-main); }
.container-wide{max-width:1200px;padding-top:20px;padding-bottom:20px;}
.modern-card{background:#fff;border:none;border-radius:12px;box-shadow:0 4px 10px rgba(0,0,0,0.03);}
.table-custom thead th{background:#f8fafc;color:#475569;font-size:0.75rem;padding:14px 24px;text-transform:uppercase;}
.table-custom tbody td{padding:18px;border-bottom:1px solid #f1f5f9;color:var(--text-secondary);}
.btn-icon.view{background:#dbeafe;color:#0369a1;border-radius:8px;padding:8px 10px;}
.table-scroll-wrapper { max-height: 600px; overflow-y: auto; overflow-x: hidden; }
</style>

<div class="container container-wide">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Appointments — <?= h($patient['Name'] ?? 'Patient') ?> (ID: <?= (int)$patient['Patient_Id'] ?>)</h4>
            <p class="text-muted small mb-0">All appointments for this patient.</p>
        </div>
        <div>
            <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/patients" class="btn btn-outline-dark btn-sm px-4">Back to Patients</a>
        </div>
    </div>

    <div class="card modern-card">
        <div class="card-body p-0">
            <?php if (empty($appointments)): ?>
                <div class="empty-state-modern" style="padding: 60px 20px; text-align:center;">
                    <div style="width:100px; height:100px; background:linear-gradient(180deg,#eef2ff,#f8fafc); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 18px; font-size:36px; color:var(--primary-color); box-shadow:0 6px 18px rgba(15,23,42,0.04);">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                    <h5 class="fw-bold text-dark">No appointments found</h5>
                    <p class="mb-3 text-muted">This patient has no appointment history.</p>
                </div>
            <?php else: ?>
                <div class="table-scroll-wrapper">
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th style="width:60px">#</th>
                                    <th>Date / Time</th>
                                    <th>Doctor</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th style="width:140px" class="text-nowrap">Payment Amount</th>
                                    <th style="width:120px" class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $i => $a): ?>
                                    <tr>
                                        <td class="text-secondary small fw-bold"><?= ($page-1)*$perPage + $i + 1 ?></td>
                                        <td>
                                            <div class="fw-semibold"><?= (new DateTime($a['Appointment_Date']))->format('d/m/Y') ?></div>
                                            <div class="text-muted small"><?= (new DateTime($a['Appointment_Time']))->format('h:i A') ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= h($a['Doctor_Name'] ?? '-') ?></div>
                                            <div class="text-muted small"><?= h($a['Speciality_Name'] ?? '-') ?></div>
                                        </td>
                                        <td><?= h($a['Visit_Type'] ?? '-') ?></td>
                                        <td>
                                            <?php
                                                $s = $a['Status'] ?? 'Pending';
                                                $lowerS = strtolower($s);
                                                $badgeClass = 'bg-secondary';
                                                
                                                if ($s === 'Approved') $badgeClass = 'bg-success';
                                                elseif ($s === 'Pending') $badgeClass = 'bg-warning text-dark';
                                                elseif ($s === 'Completed') $badgeClass = 'bg-success';
                                                elseif (in_array($s, ['Rejected', 'Cancelled'])) $badgeClass = 'bg-danger';
                                                elseif ($s === 'Missed') $badgeClass = 'bg-secondary';
                                            ?>
                                            <span class="badge rounded-pill <?= $badgeClass ?>"><?= h($s) ?></span>
                                        </td>
                                        <td class="text-nowrap">
                                            <?php if (!is_null($a['Payment_Amount'])): ?>
                                                ₹ <?= number_format((float)$a['Payment_Amount'], 2) ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/appointments/view&id=<?= (int)$a['Appointment_Id'] ?>" class="btn-icon view" title="View"><i class="bi bi-eye-fill"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination-wrapper d-flex justify-content-center p-3">
                        <nav aria-label="appointments-pagination">
                            <ul class="pagination mb-0">
                                <li class="page-item <?= ($page <= 1) ? 'disabled':'' ?>">
                                    <a class="page-link" href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/patients/appointments&patient_id=<?= (int)$patient['Patient_Id'] ?>&page=<?= max(1, $page-1) ?>"><i class="bi bi-chevron-left"></i></a>
                                </li>

                                <?php
                                $visible = 5;
                                $start = max(1, $page - floor($visible/2));
                                $end = min($totalPages, $start + $visible - 1);
                                if ($end - $start + 1 < $visible) $start = max(1, $end - $visible + 1);
                                for ($p = $start; $p <= $end; $p++): ?>
                                    <li class="page-item <?= ($p === $page) ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/patients/appointments&patient_id=<?= (int)$patient['Patient_Id'] ?>&page=<?= $p ?>"><?= $p ?></a>
                                    </li>
                                <?php endfor; ?>

                                <li class="page-item <?= ($page >= $totalPages) ? 'disabled':'' ?>">
                                    <a class="page-link" href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/patients/appointments&patient_id=<?= (int)$patient['Patient_Id'] ?>&page=<?= min($totalPages, $page+1) ?>"><i class="bi bi-chevron-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
