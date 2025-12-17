<?php
// views/admin/payments/index.php
// Expects: $payments (array), $page, $perPage, $total, $totalPages, $startIndex

if (!function_exists('h')) { function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); } }
?>

<?php $pageTitle = 'MediConnect - Payments';
include __DIR__ . '/../../includes/adminNavbar.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root {
        --primary-color: #4f46e5;
        --bg-color: #f3f4f6;
        --text-main: #111827;
        --text-secondary: #4b5563;
        --success-bg: #d1fae5;
        --success-color: #10b981;
        --warning-bg: #fef3c7;
        --warning-color: #f59e0b;
        --danger-bg: #fee2e2;
        --danger-color: #ef4444;
    }
    body { background-color: var(--bg-color); font-family: 'Inter', sans-serif; color: var(--text-main); }
    .container-wide { max-width: 1200px; padding-top: 28px; padding-bottom: 28px; }
    .page-header h4 { font-weight:700; letter-spacing:-0.5px; color:#000; }
    .modern-card { background:#fff;border:none;border-radius:16px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); overflow:hidden; }
    .table-custom thead th { background-color:#f8fafc; color:#475569; font-size:0.75rem; text-transform:uppercase; padding:16px 24px; white-space:nowrap; }
    .table-custom tbody td { padding:24px 24px; vertical-align:top; border-bottom:1px solid #f1f5f9; color:var(--text-secondary); font-size:0.95rem; }
    
    .status-badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:0.75rem; font-weight:600; }
    .status-approved { background-color:var(--success-bg); color:var(--success-color); }
    .status-pending { background-color:var(--warning-bg); color:var(--warning-color); }
    .status-rejected { background-color:var(--danger-bg); color:var(--danger-color); }
    .status-refunded { background-color: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }

    .btn-icon { width:40px; height:40px; display:flex; align-items:center; justify-content:center; border-radius:8px; border:none; background:transparent; color:var(--text-secondary); transition:all 0.15s; }
    .btn-icon.view { background: #dbeafe; color: #0369a1; }
    .btn-icon.view:hover { background: #0ea5e9; color: #fff; transform:translateY(-2px); box-shadow:0 6px 14px rgba(14,165,233,0.2); }
    
    .empty-state-modern { padding: 60px 20px; text-align:center; }
    .pagination-wrapper { padding: 20px; border-top: 1px solid #f3f4f6; }
    .page-link { border: none; color: #6b7280; margin: 0 4px; border-radius: 8px; font-weight: 500; transition: all 0.2s; display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; padding: 0; }
    .page-link:hover { background-color: #f3f4f6; color: #111827; }
    .page-item.active .page-link { background-color: var(--primary-color); color: white; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3); }
    .table-scroll-wrapper { max-height: 600px; overflow-y: auto; overflow-x: hidden; }
</style>

<div class="container container-wide py-3">
    <div class="d-flex justify-content-between align-items-end mb-4 page-header">
        <div>
            <h4 class="mb-1">Payments</h4>
            <p class="text-muted mb-0 small">Transaction history and payment status.</p>
        </div>
    </div>

    <div class="card modern-card">
        <div class="card-body p-0">
            <?php if (empty($payments)): ?>
                <div class="empty-state-modern">
                    <div style="width:100px; height:100px; background:linear-gradient(180deg,#eef2ff,#f8fafc); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 18px; font-size:36px; color:var(--primary-color); box-shadow:0 6px 18px rgba(15,23,42,0.04);">
                        <i class="bi bi-credit-card"></i>
                    </div>
                    <h5 class="fw-bold text-dark">No payments found</h5>
                    <p class="mb-3">No transactions have been recorded yet.</p>
                </div>
            <?php else: ?>
                <div class="table-scroll-wrapper">
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th style="width:60px">#</th>
                                    <th>Patient</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th style="width:100px" class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $i => $p): ?>
                                    <tr>
                                        <td class="text-secondary small fw-bold"><?= h($startIndex + $i) ?></td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= h($p['Patient_Name'] ?? 'Unknown') ?></div>
                                            <div class="text-muted small"><?= h($p['Patient_Email'] ?? '-') ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">₹ <?= number_format((float)$p['Amount'], 2) ?></div>
                                        </td>
                                        <td><?= h($p['Method']) ?></td>
                                        <td>
                                            <?php
                                                $s = strtolower($p['Status']);
                                                $apptStatus = $p['Appointment_Status'] ?? '';
                                                $class = 'status-pending';
                                                
                                                if(strpos($s, 'approv')!==false || strpos($s, 'success')!==false) $class='status-approved';
                                                if(strpos($s, 'reject')!==false || strpos($s, 'fail')!==false) $class='status-rejected';
                                                
                                                // Override for Refunded based on Appointment Status
                                                if(in_array($apptStatus, ['Rejected', 'Missed', 'Cancelled'])) {
                                                    $class = 'status-refunded';
                                                    // Display 'REFUNDED' text
                                                    $p['Status'] = 'REFUNDED';
                                                }
                                            ?>
                                            <span class="status-badge <?= $class ?>"><?= h($p['Status']) ?></span>
                                        </td>
                                        <td>
                                            <?php 
                                                $d = new DateTime($p['Paid_At']);
                                                echo '<div class="fw-medium">' . $d->format('M d, Y') . '</div>';
                                                echo '<div class="text-muted small">' . $d->format('h:i A') . '</div>';
                                            ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end">
                                                <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/payments/view&id=<?= (int)$p['Payment_Id'] ?>&page=<?= $page ?>" class="btn-icon view" title="View Details"><i class="bi bi-eye-fill"></i></a>
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
                        <nav aria-label="payments-pagination">
                            <ul class="pagination mb-0">
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/payments&page=<?= max(1, $page-1) ?>"><i class="bi bi-chevron-left"></i></a>
                                </li>
                                <?php
                                $visible = 5;
                                $start = max(1, $page - floor($visible/2));
                                $end = min($totalPages, $start + $visible - 1);
                                if ($end - $start + 1 < $visible) $start = max(1, $end - $visible + 1);
                                for ($pg = $start; $pg <= $end; $pg++): ?>
                                    <li class="page-item <?= ($pg === $page) ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/payments&page=<?= $pg ?>"><?= $pg ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/payments&page=<?= min($totalPages, $page+1) ?>"><i class="bi bi-chevron-right"></i></a>
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
