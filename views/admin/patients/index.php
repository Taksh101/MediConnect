<?php
// views/admin/patients/index.php
// Expects: $patients (array), optionally $page, $perPage, $total, $totalPages

$page = isset($page) ? (int)$page : max(1, (int)($_GET['page'] ?? 1));
$perPage = isset($perPage) ? (int)$perPage : 10;
$total = isset($total) ? (int)$total : (is_array($patients) ? count($patients) : 0);
$totalPages = isset($totalPages) ? (int)$totalPages : (int)max(1, ceil($total / max(1,$perPage)));
$startIndex = ($page - 1) * $perPage + 1;

$csrfTokenForJs = '';
if (file_exists(__DIR__ . '/../../config/csrf.php')) {
    require_once __DIR__ . '/../../config/csrf.php';
    $csrfTokenForJs = csrf_token();
}

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

if (!isset($patients)) {
    global $db;
    require_once __DIR__ . '/../../../models/PatientModel.php';
    $model = new PatientModel($db);
    $offset = ($page - 1) * $perPage;
    $patients = $model->paginate($perPage, $offset);
}

?>

<?php $pageTitle = 'MediConnect - Manage Patients';
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
    .btn-create:hover{ background: var(--primary-hover); transform: translateY(-1px); box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2); }
    .modern-card { background:#fff;border:none;border-radius:16px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); overflow:hidden; }
    .table-custom thead th { background-color:#f8fafc; color:#475569; font-size:0.75rem; text-transform:uppercase; padding:16px 24px; white-space:nowrap; }
    .table-custom tbody td { padding:24px 24px; vertical-align:top; border-bottom:1px solid #f1f5f9; color:var(--text-secondary); font-size:0.95rem; }
    .patient-name { font-weight:600; color:#0f172a; }
    .action-btn-group { display:flex; gap:8px; justify-content:flex-end; align-items:center; }
    .btn-icon { width:40px; height:40px; display:flex; align-items:center; justify-content:center; border-radius:8px; border:none; background:transparent; color:var(--text-secondary); transition:all 0.15s; }
    .btn-icon.view { background: #dbeafe; color: #0369a1; }
    .btn-icon.view:hover { background: #0ea5e9; color: #fff; transform:translateY(-2px); box-shadow:0 6px 14px rgba(14,165,233,0.2); }
    .btn-icon.delete { background-color: var(--danger-bg); color: var(--danger-color); }
    .btn-icon.delete:hover { background-color: var(--danger-color); color: #fff; transform: translateY(-2px); box-shadow: 0 6px 14px rgba(239,68,68,0.14); }
    .empty-state-modern { padding: 60px 20px; text-align:center; }
    .empty-icon-circle { width:100px; height:100px; background:linear-gradient(180deg,#eef2ff,#f8fafc); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 18px; font-size:36px; color:var(--primary-color); box-shadow:0 6px 18px rgba(15,23,42,0.04); }
    .empty-state-modern h5 { font-size:1.125rem; margin-bottom:8px; }
    .empty-state-modern p { color:#6b7280; margin-bottom:14px; }
    .pagination-wrapper { padding: 20px; border-top: 1px solid #f3f4f6; }
    .page-link { border: none; color: #6b7280; margin: 0 4px; border-radius: 8px; font-weight: 500; transition: all 0.2s; display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; padding: 0; }
    .page-link:hover { background-color: #f3f4f6; color: #111827; }
    .page-item.active .page-link { background-color: var(--primary-color); color: white; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3); }
    .table-scroll-wrapper { max-height: 600px; overflow-y: auto; overflow-x: hidden; }
    .table-scroll-wrapper::-webkit-scrollbar { width: 8px; }
    .table-scroll-wrapper::-webkit-scrollbar-track { background: #f1f5f9; }
    .table-scroll-wrapper::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .table-scroll-wrapper::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<div class="container container-wide py-3">
    <div class="d-flex justify-content-between align-items-end mb-4 page-header">
        <div>
            <h4 class="mb-1">Patients</h4>
            <p class="text-muted mb-0 small">Manage registered patients and their records.</p>
        </div>
    </div>

    <div class="card modern-card">
        <div class="card-body p-0">
            <?php if (empty($patients)): ?>
                <div class="empty-state-modern">
                    <div class="empty-icon-circle"><i class="bi bi-people-fill"></i></div>
                    <h5 class="fw-bold text-dark">No patients found</h5>
                    <p class="mb-3">There are no registered patients yet. They will appear here once they register or are added.</p>
                </div>
            <?php else: ?>
                <div class="table-scroll-wrapper">
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th style="width:60px">#</th>
                                    <th>Patient</th>
                                    <th style="width:220px">Contact</th>
                                    <th style="width:120px">Gender</th>
                                    <th style="width:120px">DOB</th>
                                    <th style="width:130px" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($patients as $i => $p): ?>
                                    <tr>
                                        <td class="text-secondary small fw-bold"><?= h($startIndex + $i) ?></td>
                                        <td>
                                            <div class="patient-name"><?= h($p['Name']) ?></div>
                                            <div class="text-muted small"><?= h($p['Address'] ?? '') ?></div>
                                        </td>
                                        <td>
                                            <div><?= h($p['Email'] ?? '-') ?></div>
                                            <div class="text-muted small"><?= h($p['Phone'] ?? '-') ?></div>
                                        </td>
                                        <td><?= h($p['Gender'] ?? '-') ?></td>
                                        <td><?= h($p['DOB'] ?? '-') ?></td>
                                        <td class="align-middle">
                                            <div class="action-btn-group">
                                                <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/patients/view&id=<?= (int)$p['Patient_Id'] ?>" class="btn-icon view" title="View Profile"><i class="bi bi-person-lines-fill"></i></a>
                                                <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/patients/appointments&patient_id=<?= (int)$p['Patient_Id'] ?>" class="btn-icon view" title="Appointments"><i class="bi bi-calendar3"></i></a>
                                                <button class="btn-icon delete btn-delete" data-id="<?= (int)$p['Patient_Id'] ?>" title="Delete"><i class="bi bi-trash-fill"></i></button>
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
                        <nav aria-label="patients-pagination">
                            <ul class="pagination mb-0">
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/patients&page=<?= max(1, $page-1) ?>"><i class="bi bi-chevron-left"></i></a>
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
                                        <a class="page-link" href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/patients&page=<?= $p ?>"><?= $p ?></a>
                                    </li>
                                <?php endfor; ?>

                                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/patients&page=<?= min($totalPages, $page+1) ?>"><i class="bi bi-chevron-right"></i></a>
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
                <h5 class="fw-bold mb-2">Delete Patient?</h5>
                <p class="text-muted mb-4">Are you sure you want to delete this patient? This action cannot be undone.</p>
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
const PATIENTS_CSRF = <?= json_encode($csrfTokenForJs) ?>;

document.addEventListener('DOMContentLoaded', function() {
    const deleteModalEl = document.getElementById('deleteModal');
    if (!deleteModalEl) return;
    const deleteModal = new bootstrap.Modal(deleteModalEl);
    let deleteId = null;

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            deleteId = this.dataset.id;
            deleteModal.show();
        });
    });

    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (!deleteId) return;
        
        // Construct the delete URL or perform a fetch request
        // Assuming a standard GET delete for now or a form post. 
        // Based on typical admin panels, here is a fetch implementation:
        
        fetch(`${BASE_PATH}/index.php?route=admin/patients/delete&id=${deleteId}`, {
            method: 'POST', // or GET depending on route
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `csrf_token=${encodeURIComponent(PATIENTS_CSRF)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert(data.message || 'Error deleting patient');
            }
        })
        .catch(err => {
            console.error(err);
            // Fallback if it's not JSON API but a direct link action
             window.location.href = `${BASE_PATH}/index.php?route=admin/patients/delete&id=${deleteId}`;
        });
        
        deleteModal.hide();
    });
});
</script>

