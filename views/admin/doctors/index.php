<?php
// views/admin/doctors/index.php
// Expects: $doctors (array), optionally $page, $perPage, $total, $totalPages

$page = isset($page) ? (int)$page : max(1, (int)($_GET['page'] ?? 1));
$perPage = isset($perPage) ? (int)$perPage : 4;
$total = isset($total) ? (int)$total : (is_array($doctors) ? count($doctors) : 0);
$totalPages = isset($totalPages) ? (int)$totalPages : (int)max(1, ceil($total / max(1,$perPage)));
$startIndex = ($page - 1) * $perPage + 1;

$csrfTokenForJs = '';
if (file_exists(__DIR__ . '/../../config/csrf.php')) {
    require_once __DIR__ . '/../../config/csrf.php';
    $csrfTokenForJs = csrf_token();
}

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

if (!isset($doctors)) {
    global $db;
    require_once __DIR__ . '/../../../models/DoctorModel.php';
    $model = new DoctorModel($db);
    if (isset($perPage) && isset($page) && isset($total) && $total > $perPage) {
        $offset = ($page - 1) * $perPage;
        $doctors = $model->paginate($perPage, $offset);
    } else {
        $doctors = $model->paginate($perPage, 0);
    }
}
?>

<?php $pageTitle = 'MediConnect - Manage Doctors';
include __DIR__ . '/../../includes/adminNavbar.php'; ?>

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
        --danger-color: #ef4444;
        --danger-bg: #fee2e2;
        --edit-bg: #e0e7ff;
    }
    body { background-color: var(--bg-color); font-family: 'Inter', sans-serif; color: var(--text-main); }
    .container-wide { max-width: 1200px; padding-top: 28px; padding-bottom: 28px; }
    .page-header h4 { font-weight:700; letter-spacing:-0.5px; color:#000; }
    .btn-create { background:var(--primary-color); border:none; padding:10px 20px; border-radius:8px; font-weight:500; }
    .btn-create:hover{ background: var(--primary-hover);
        transform: translateY(-1px);
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
        }
    .modern-card { background:#fff;border:none;border-radius:16px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); overflow:hidden; }
    .table-custom thead th { background-color:#f8fafc; color:#475569; font-size:0.75rem; text-transform:uppercase; padding:16px 24px; white-space:nowrap; }
    .table-custom tbody td { padding:24px 24px; vertical-align:top; border-bottom:1px solid #f1f5f9; color:var(--text-secondary); font-size:0.95rem; }
    .doctor-name { font-weight:600; color:#0f172a; }
    .badge-status { padding:6px 10px; border-radius:8px; font-weight:600; display:inline-flex; align-items:center; }
    .badge-active { background:#dcfce7;color:#15803d; }
    .badge-inactive { background:#fee2e2;color:#dc2626; }
    .action-btn-group { display:flex; gap:8px; justify-content:flex-end; align-items:center; }
    .btn-icon { width:40px; height:40px; display:flex; align-items:center; justify-content:center; border-radius:8px; border:none; background:transparent; color:var(--text-secondary); transition:all 0.15s; }
    .btn-icon.view { background: #dbeafe; color: #0369a1; }
    .btn-icon.view:hover { background: #0ea5e9; color: #fff; transform:translateY(-2px); box-shadow:0 6px 14px rgba(14,165,233,0.2); }
    .btn-icon.edit { background-color: var(--edit-bg); color: var(--primary-color); }
    .btn-icon.edit:hover { background-color: var(--primary-color); color: #fff; transform: translateY(-2px); box-shadow: 0 6px 14px rgba(79,70,229,0.14); }
    .btn-icon.avail { background: #eef2ff; color: #3730a3; }
    .btn-icon.avail:hover { background: #e0e7ff; transform:translateY(-2px); }
    .btn-icon.delete { background-color: var(--danger-bg); color: var(--danger-color); }
    .btn-icon.delete:hover { background-color: var(--danger-color); color: #fff; transform: translateY(-2px); box-shadow: 0 6px 14px rgba(239,68,68,0.14); }
    /* improved empty state */
    .empty-state-modern { padding: 60px 20px; text-align:center; }
    .empty-icon-circle { width:100px; height:100px; background:linear-gradient(180deg,#eef2ff,#f8fafc); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 18px; font-size:36px; color:var(--primary-color); box-shadow:0 6px 18px rgba(15,23,42,0.04); }
    .empty-state-modern h5 { font-size:1.125rem; margin-bottom:8px; }
    .empty-state-modern p { color:#6b7280; margin-bottom:14px; }
    /* Pagination styling copied from specialities to match UI exactly */
    .pagination-wrapper { padding: 20px; border-top: 1px solid #f3f4f6; }
    .page-link {
        border: none;
        color: #6b7280;
        margin: 0 4px;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        padding: 0;
    }
    .page-link:hover { background-color: #f3f4f6; color: #111827; }
    .page-item.active .page-link { background-color: var(--primary-color); color: white; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3); }
    /* Scrollable table wrapper */
    .table-scroll-wrapper { max-height: 600px; overflow-y: auto; overflow-x: hidden; }
    .table-scroll-wrapper::-webkit-scrollbar { width: 8px; }
    .table-scroll-wrapper::-webkit-scrollbar-track { background: #f1f5f9; }
    .table-scroll-wrapper::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .table-scroll-wrapper::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<div class="container container-wide py-3">
    <div class="d-flex justify-content-between align-items-end mb-4 page-header">
        <div>
            <h4 class="mb-1">Doctors Management</h4>
            <p class="text-muted mb-0 small">Manage doctors, their specialities and availability.</p>
        </div>
        <div>
            <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/doctors/create" class="btn btn-primary btn-create">
                <i class="bi bi-plus-lg me-2"></i>New Doctor
            </a>
        </div>
    </div>

    <div class="card modern-card">
        <div class="card-body p-0">
            <?php if (empty($doctors)): ?>
                <div class="empty-state-modern">
                    <div class="empty-icon-circle"><i class="bi bi-person-plus-fill"></i></div>
                    <h5 class="fw-bold text-dark">No doctors yet</h5>
                    <p class="mb-3">You haven't added any doctors. Click New Doctor to add one and configure availability.</p>
                    <!-- <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/doctors/create" class="btn btn-primary">Add your first doctor</a> -->
                </div>
            <?php else: ?>
                <div class="table-scroll-wrapper">
                    <div class="table-responsive">
                        <table class="table table-custom">
                        <thead>
                            <tr>
                                <th style="width:60px">#</th>
                                <th>Doctor</th>
                                <th style="width:180px">Contact</th>
                                <th style="width:180px">Speciality</th>
                                <th style="width:120px">Experience</th>
                                <th style="width:120px" class="text-center">Status</th>
                                <th style="width:130px" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($doctors as $i => $d): ?>
                                <tr>
                                    <td class="text-secondary small fw-bold"><?= h($startIndex + $i) ?></td>
                                    <td>
                                        <div class="doctor-name"><?= h($d['Name']) ?></div>
                                        <div class="text-muted small"><?= h($d['Qualification'] ?? '-') ?></div>
                                    </td>
                                    <td>
                                        <div><?= h($d['Email']) ?></div>
                                        <div class="text-muted small"><?= h($d['Phone'] ?? '-') ?></div>
                                    </td>
                                    <td>
                                        <div><?= h($d['Speciality_Name'] ?? '-') ?></div>
                                    </td>
                                    <td>
                                        <?= (int)($d['Experience_Years'] ?? 0) ?> yrs
                                    </td>
                                        <td class="text-center align-middle">
                                            <?php if (strtoupper($d['Status'] ?? '') === 'AVAILABLE'): ?>
                                                <span class="badge-status badge-active">Available</span>
                                            <?php else: ?>
                                                <span class="badge-status badge-inactive">Unavailable</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle">
                                            <div class="action-btn-group">
                                                <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/doctors/view&id=<?= (int)$d['Doctor_Id'] ?>&page=<?= $page ?>" class="btn-icon view" title="View"><i class="bi bi-person-lines-fill"></i></a>
                                                <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/doctors/availability&doctor_id=<?= (int)$d['Doctor_Id'] ?>&page=<?= $page ?>" class="btn-icon avail" title="Availability"><i class="bi bi-calendar-week"></i></a>
                                                <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/doctors/edit&id=<?= (int)$d['Doctor_Id'] ?>&page=<?= $page ?>" class="btn-icon edit" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                                                <button class="btn-icon delete btn-delete" data-id="<?= (int)$d['Doctor_Id'] ?>" title="Delete"><i class="bi bi-trash-fill"></i></button>
                                            </div>
                                        </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination-wrapper d-flex justify-content-center">
                        <nav aria-label="doctors-pagination">
                            <ul class="pagination mb-0">
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/doctors&page=<?= max(1, $page-1) ?>"><i class="bi bi-chevron-left"></i></a>
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
                                        <a class="page-link" href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/doctors&page=<?= $p ?>"><?= $p ?></a>
                                    </li>
                                <?php endfor; ?>

                                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/doctors&page=<?= min($totalPages, $page+1) ?>"><i class="bi bi-chevron-right"></i></a>
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

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <div style="width:60px;height:60px;background:#fee2e2;color:#dc2626;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:24px;">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-2">Delete Doctor?</h5>
                <p class="text-muted mb-4">Are you sure you want to delete this doctor? This action cannot be undone.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-medium" data-bs-dismiss="modal" style="min-width:100px;">Cancel</button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger px-4 fw-medium" style="min-width:100px;">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const BASE_PATH = <?= json_encode((defined('BASE_PATH') ? BASE_PATH : '')) ?>;
const DOCTORS_CSRF = <?= json_encode($csrfTokenForJs) ?>;
</script>
<script src="<?= (defined('BASE_PATH')?BASE_PATH:'') ?>/assets/js/doctors.js"></script>
