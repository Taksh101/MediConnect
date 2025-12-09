<?php
// views/admin/specialities/index.php
// Expects: $specialities (array), optionally $page, $perPage, $total, $totalPages

// --- LOGIC SECTION (UNTOUCHED) ---
$page = isset($page) ? (int)$page : max(1, (int)($_GET['page'] ?? 1));
$perPage = isset($perPage) ? (int)$perPage : 10;
$total = isset($total) ? (int)$total : (is_array($specialities) ? count($specialities) : 0);
$totalPages = isset($totalPages) ? (int)$totalPages : (int)max(1, ceil($total / max(1,$perPage)));
$startIndex = ($page - 1) * $perPage + 1;

// CSRF token for JS delete (if available)
$csrfTokenForJs = '';
if (file_exists(__DIR__ . '/../../config/csrf.php')) {
    require_once __DIR__ . '/../../config/csrf.php';
    $csrfTokenForJs = csrf_token();
}

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// If $specialities not provided, load inline (fallback)
if (!isset($specialities)) {
    global $db;
    require_once __DIR__ . '/../../../models/SpecialityModel.php';
    $model = new SpecialityModel($db);
    // if pagination variables passed, use paginate; otherwise use all()
    if (isset($perPage) && isset($page) && isset($total) && $total > $perPage) {
        $offset = ($page - 1) * $perPage;
        $specialities = $model->paginate($perPage, $offset);
    } else {
        $specialities = $model->all();
    }
}
?>

<?php $pageTitle = 'MediConnect - Specialities';
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

    body {
        background-color: var(--bg-color);
        font-family: 'Inter', sans-serif;
        color: var(--text-main);
    }

    .container-wide { max-width: 1200px; }

    .page-header h4 {
        font-weight: 700;
        letter-spacing: -0.5px;
        color: #000;
    }
    
    .btn-create {
        background: var(--primary-color);
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.1), 0 2px 4px -1px rgba(79, 70, 229, 0.06);
        transition: all 0.2s;
    }
    .btn-create:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
    }

    .modern-card {
        background: #ffffff;
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }

    .table-custom {
        margin-bottom: 0;
    }
    .table-custom thead th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        padding: 16px 24px;
        border-bottom: 1px solid #e2e8f0;
        /* FIX 1: Prevent headers from wrapping */
        white-space: nowrap;
    }
    .table-custom tbody td {
        padding: 24px 24px;
        vertical-align: top; 
        border-bottom: 1px solid #f1f5f9;
        color: var(--text-secondary);
        font-size: 0.95rem;
    }
    .table-custom tbody tr:last-child td {
        border-bottom: none;
    }
    .table-custom tbody tr:hover {
        background-color: #fafafa;
    }

    .speciality-name {
        font-weight: 600;
        color: #0f172a;
        font-size: 1rem;
    }
    
    .badge-fee {
        background-color: #dcfce7;
        color: #15803d;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.85rem;
    }

    /* FIX 2: Better alignment for badge */
    .badge-duration {
        background-color: #e0f2fe;
        color: #0369a1;
        font-weight: 600;
        padding: 6px 12px;
        margin-top:-7px;
        border-radius: 8px;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 6px; /* Space between icon and text */
        white-space: nowrap; /* Prevent wrapping onto new line */
    }

    /* Description styling */
    .desc-wrapper {
        position: relative;
    }
    .desc-text {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        transition: max-height 0.3s ease;
        line-height: 1.5;
        color: #4b5563;
    }
    .desc-text.expanded {
        -webkit-line-clamp: unset;
        overflow: visible;
    }
    .read-more-btn {
        background: none;
        border: none;
        padding: 0;
        margin-top: 4px;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--primary-color);
        cursor: pointer;
        text-decoration: none;
    }
    .read-more-btn:hover {
        text-decoration: underline;
    }

    /* Action Buttons */
    .action-btn-group {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
    .btn-icon {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: none;
        transition: all 0.2s;
    }
    .btn-icon.edit {
        background-color: var(--edit-bg);
        color: var(--primary-color);
    }
    .btn-icon.edit:hover {
        background-color: var(--primary-color);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
    }
    .btn-icon.delete {
        background-color: var(--danger-bg);
        color: var(--danger-color);
    }
    .btn-icon.delete:hover {
        background-color: var(--danger-color);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(239, 68, 68, 0.2);
    }

    /* Pagination */
    /* Pagination */
    .pagination-wrapper { padding: 20px; border-top: 1px solid #f3f4f6; }
    
    .page-link {
        border: none;
        color: #6b7280;
        margin: 0 4px;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.2s;
        
        /* --- START OF FIX --- */
        display: flex;           /* Makes the box a flex container */
        align-items: center;     /* Vertically centers content */
        justify-content: center; /* Horizontally centers content */
        width: 38px;            /* Fixed width */
        height: 38px;           /* Fixed height */
        padding: 0;             /* Remove default padding so centering works */
        /* --- END OF FIX --- */
    }

    .page-link:hover {
        background-color: #f3f4f6;
        color: #111827;
    }
    .page-item.active .page-link {
        background-color: var(--primary-color);
        color: white;
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3);
    }
    
    /* Empty State */
    .empty-state-modern {
        padding: 80px 20px;
        text-align: center;
    }
    .empty-icon-circle {
        width: 80px;
        height: 80px;
        background: #f3f4f6;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        font-size: 32px;
    }
</style>

<div class="container container-wide py-5">
    
    <div class="d-flex justify-content-between align-items-end mb-4 page-header">
        <div>
            <h4 class="mb-1">Specialities Management</h4>
            <p class="text-muted mb-0 small">Configure consultation fees, durations, and details.</p>
        </div>
        <div>
            <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/specialities/create" class="btn btn-primary btn-create">
                <i class="bi bi-plus-lg me-2"></i>New Speciality
            </a>
        </div>
    </div>

    <div class="card modern-card">
        <div class="card-body p-0">

            <?php if (empty($specialities)): ?>

                <div class="empty-state-modern">
                    <div class="empty-icon-circle">
                        <i class="bi bi-folder2-open text-muted"></i>
                    </div>
                    <h5 class="fw-bold text-dark">No specialities found</h5>
                    <p class="text-muted mb-0">Get started by adding your first medical speciality above.</p>
                </div>

            <?php else: ?>

                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th style="width:60px">#</th>
                                <th>Speciality Name</th>
                                <th style="width:150px">Duration</th>
                                <th style="width:180px">Consultation Fee</th>
                                <th style="width:35%">Description</th>
                                <th style="width:130px" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($specialities as $i => $s): ?>
                                <tr>
                                    <td class="text-secondary small fw-bold"><?= h($startIndex + $i) ?></td>
                                    <td>
                                        <div class="speciality-name"><?= h($s['Speciality_Name']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge-duration">
                                            <i class="bi bi-clock"></i><?= h($s['Consultation_Duration']) ?> min
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-fee">
                                            ₹<?= h((int)$s['Consultation_Fee']) ?>
                                        </span>
                                    </td>
                                    <td>
    <?php $desc = $s['Description'] ?? ''; ?>
    <div class="desc-wrapper">
        <div class="desc-text">
            <?= h($desc ?: '-') ?>
        </div>
        <button class="read-more-btn" style="display: none;" onclick="toggleDesc(this)">Read More</button>
    </div>
</td>
                                    <td>
                                        <div class="action-btn-group justify-content-end">
                                            <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/specialities/edit&id=<?= (int)$s['Speciality_Id'] ?>" 
                                               class="btn-icon edit" title="Edit">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <button class="btn-icon delete btn-delete" 
                                                    data-id="<?= (int)$s['Speciality_Id'] ?>" title="Delete">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination-wrapper d-flex justify-content-center">
                        <nav aria-label="specialities-pagination">
                            <ul class="pagination mb-0">
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/specialities&page=<?= max(1, $page-1) ?>" style="margin-right:4px;">
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
                                    <li class="page-item <?= ($p === $page) ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/specialities&page=<?= $p ?>"><?= $p ?></a>
                                    </li>
                                <?php endfor; ?>

                                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>" style="margin-left:4px;">
                                    <a class="page-link" href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/specialities&page=<?= min($totalPages, $page+1) ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
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

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <div style="width: 60px; height: 60px; background: #fee2e2; color: #dc2626; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; font-size: 24px;">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-2">Delete Speciality?</h5>
                <p class="text-muted mb-4">Are you sure you want to delete this speciality? This action cannot be undone.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-medium" data-bs-dismiss="modal" style="min-width: 100px;">Cancel</button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger px-4 fw-medium" style="min-width: 100px;">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// --- READ MORE LOGIC (AUTOMATIC DETECTION) ---
document.addEventListener('DOMContentLoaded', () => {
    // Check every description box
    document.querySelectorAll('.desc-text').forEach(el => {
        // If the full text height is greater than the visible clamped height
        if (el.scrollHeight > el.clientHeight) {
            // Find the "Read More" button right after it and show it
            const btn = el.nextElementSibling;
            if (btn) btn.style.display = 'block'; 
        }
    });
});

function toggleDesc(btn) {
    const textField = btn.previousElementSibling;
    textField.classList.toggle('expanded');
    
    if (textField.classList.contains('expanded')) {
        btn.textContent = 'Show Less';
    } else {
        btn.textContent = 'Read More';
    }
}

// --- DELETE LOGIC (Unchanged) ---
const SPECIALITY_CSRF = <?= json_encode($csrfTokenForJs) ?>;
let deleteId = null;
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-delete');
    if (btn) {
        deleteId = btn.dataset.id;
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }
});

document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
    if (!deleteId) return;
    const body = new URLSearchParams();
    body.append('id', deleteId);
    if (SPECIALITY_CSRF) body.append('csrf_token', SPECIALITY_CSRF);

    fetch('<?= (defined("BASE_PATH") ? BASE_PATH : "") ?>/index.php?route=admin/specialities/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString()
    })
    .then(r => r.json())
    .then(j => {
        if (j.ok) location.reload();
        else alert(j.error || 'Delete failed');
    })
    .catch(() => alert('Delete failed'));

    deleteId = null;
});
</script>