<?php
// views/admin/doctors/availability.php
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
include __DIR__ . '/../../includes/adminNavbar.php';
$days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];

// Group availabilities by day
$byDay = [];
foreach($availabilities as $a) {
    $day = $a['Available_Day'];
    if (!isset($byDay[$day])) $byDay[$day] = [];
    $byDay[$day][] = $a;
}
// Sort by day order
$sortedByDay = [];
foreach($days as $d) {
    if (isset($byDay[$d])) $sortedByDay[$d] = $byDay[$d];
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
  :root{ --primary:#4f46e5; --primary-2:#eef2ff; --bg:#f8fafc; --muted:#6b7280; --danger:#ef4444; --danger-bg:#fee2e2; }
  body{font-family:'Inter',sans-serif}
  .modern-card{background:#fff;border-radius:14px;padding:24px;box-shadow:0 6px 20px rgba(15,23,42,0.06)}
  .form-slim .form-control{height:44px;border-radius:8px}
  .btn-create{background:var(--primary);color:#fff;border:none;padding:10px 16px;border-radius:8px;transition:all 0.2s}
  .btn-create:hover{background:#4338ca;transform:translateY(-1px)}
  .add-form-row{gap:12px;padding-bottom:24px;border-bottom:1px solid #f1f5f9}
  .availability-hero{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
  .availability-hero h4{font-weight:700;color:#000}
  
  /* Day cards */
  .day-cards-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(380px,1fr));gap:16px;margin-top:24px}
  .day-card{background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:18px;transition:all 0.2s}
  .day-card:hover{border-color:var(--primary);box-shadow:0 4px 12px rgba(79,70,229,0.08)}
  .day-card-header{display:flex;align-items:center;gap:8px;margin-bottom:14px;border-bottom:2px solid #e5e7eb;padding-bottom:10px}
  .day-badge{background:var(--primary-2);color:var(--primary);font-weight:700;padding:4px 10px;border-radius:6px;font-size:0.85rem}
  .day-card-title{font-weight:700;color:#0f172a;font-size:1rem}
  .avail-slot{display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f3f4f6}
  .avail-slot:last-child{border:none}
  .time-slot{display:flex;align-items:center;gap:8px}
  .time-text{color:var(--muted);font-weight:600;font-size:0.9rem}
  .slot-actions{display:flex;gap:6px}
  .btn-icon{width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:6px;border:none;background:#f3f4f6;color:#374151;cursor:pointer;transition:all 0.15s}
  .btn-icon.view{background:#f0f4ff;color:#4338ca}
  .btn-icon.view:hover{background:#e0e7ff;color:var(--primary);transform:translateY(-1px)}
  .btn-icon.avail{background:#f0f9ff;color:#0369a1}
  .btn-icon.avail:hover{background:#e0f2fe;color:#0369a1;transform:translateY(-1px)}
  .btn-icon.edit{background:#e0e7ff;color:var(--primary)}
  .btn-icon.edit:hover{background:var(--primary);color:#fff;transform:translateY(-1px)}
  .btn-icon.delete{background:var(--danger-bg);color:var(--danger);cursor:pointer}
  .btn-icon.delete:hover{background:var(--danger);color:#fff;transform:translateY(-1px)}
  .empty-state{text-align:center;padding:40px 20px;color:var(--muted)}
  .empty-icon{font-size:48px;margin-bottom:12px;opacity:0.5}
</style>

<div class="container container-wide py-5">
  <div class="availability-hero">
    <div>
      <h4 class="mb-1">Availability — <?= h($doctor['Name']) ?></h4>
      <p class="text-muted mb-0 small">Manage weekly time ranges. Add, edit or delete slots by day.</p>
    </div>
    <div>
      <a class="btn btn-outline-secondary" href="<?= (defined('BASE_PATH')?BASE_PATH:'') ?>/index.php?route=admin/doctors">Back</a>
    </div>
  </div>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger"><?= h($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
  <?php endif; ?>

  <div class="modern-card">
    <form id="addAvailForm" action="<?= (defined('BASE_PATH')?BASE_PATH:'') ?>/index.php?route=admin/doctors/availability" method="post" class="row form-slim add-form-row">
      <?php if (file_exists(__DIR__ . '/../../config/csrf.php')) { require_once __DIR__ . '/../../config/csrf.php'; $csrfToken = csrf_token(); } else $csrfToken = ''; ?>
      <input type="hidden" name="action" value="add">
      <input type="hidden" name="doctor_id" value="<?= (int)$doctor['Doctor_Id'] ?>">
      <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
      <div id="overlapError" class="alert alert-warning d-none mb-3" role="alert"></div>

      <div class="col-md-3">
        <label class="form-label small mb-1">Day</label>
        <select id="daySelect" name="Available_Day" class="form-control" required>
          <option value="">Select day</option>
          <?php foreach($days as $d): ?><option value="<?= $d ?>"><?= $d ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-1">Start Time</label>
        <input type="time" id="startTime" name="Start_Time" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-1">End Time</label>
        <input type="time" id="endTime" name="End_Time" class="form-control" required>
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <button type="submit" class="btn-create w-100"><i class="bi bi-plus-lg me-2"></i>Add Slot</button>
      </div>
    </form>

    <!-- Day-card grid -->
    <?php if (!empty($sortedByDay)): ?>
      <div class="day-cards-grid">
        <?php foreach($sortedByDay as $day => $slots): ?>
          <div class="day-card">
            <div class="day-card-header">
              <span class="day-badge"><?= h($day) ?></span>
            </div>
            <div>
              <?php foreach($slots as $a): ?>
                <div class="avail-slot">
                  <div class="time-slot">
                    <i class="bi bi-clock" style="color:var(--primary)"></i>
                    <span class="time-text"><?= h(!empty($a['Start_Time']) ? date('g:i A', strtotime($a['Start_Time'])) : '') ?> - <?= h(!empty($a['End_Time']) ? date('g:i A', strtotime($a['End_Time'])) : '') ?></span>
                  </div>
                  <div class="slot-actions">
                    <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=admin/doctors/availability/edit&id=<?= (int)$a['Availability_Id'] ?>&doctor_id=<?= (int)$doctor['Doctor_Id'] ?>" class="btn-icon edit" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                    <button class="btn-icon delete btn-delete-slot" data-avail-id="<?= (int)$a['Availability_Id'] ?>" data-doctor-id="<?= (int)$doctor['Doctor_Id'] ?>" title="Delete"><i class="bi bi-trash-fill"></i></button>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <div class="empty-icon"><i class="bi bi-calendar-x"></i></div>
        <p>No availability slots set. Add one above to get started.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <div style="width:60px;height:60px;background:#fee2e2;color:#dc2626;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:24px;">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-2">Delete Availability?</h5>
                <p class="text-muted mb-4">Are you sure you want to delete this time slot? This action cannot be undone.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-medium" data-bs-dismiss="modal" style="min-width:100px;">Cancel</button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger px-4 fw-medium" style="min-width:100px;">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Client-side overlap detection
const availabilityData = <?= json_encode($availabilities) ?>;

function timeToMinutes(timeStr) {
  const [h, m] = timeStr.split(':').map(Number);
  return h * 60 + m;
}

function checkOverlaps(day, startTime, endTime, excludeId = null) {
  const startMin = timeToMinutes(startTime);
  const endMin = timeToMinutes(endTime);
  
  return availabilityData.some(slot => {
    if (slot.Available_Day !== day) return false;
    if (excludeId && slot.Availability_Id === excludeId) return false;
    
    const existStart = timeToMinutes(slot.Start_Time);
    const existEnd = timeToMinutes(slot.End_Time);
    
    // No overlap if: new ends before existing starts, or new starts after existing ends
    return !(endMin <= existStart || startMin >= existEnd);
  });
}

document.getElementById('addAvailForm').addEventListener('submit', function(e) {
  const day = document.getElementById('daySelect').value;
  const startTime = document.getElementById('startTime').value;
  const endTime = document.getElementById('endTime').value;
  const errorDiv = document.getElementById('overlapError');
  
  if (!day || !startTime || !endTime) {
    errorDiv.classList.add('d-none');
    return;
  }
  
  // Check start < end
  if (startTime >= endTime) {
    e.preventDefault();
    errorDiv.textContent = '❌ Start time must be before end time';
    errorDiv.classList.remove('d-none');
    return;
  }
  
  // Check overlaps
  if (checkOverlaps(day, startTime, endTime)) {
    e.preventDefault();
    errorDiv.textContent = '⚠️ This time slot overlaps with an existing one. Please adjust the times.';
    errorDiv.classList.remove('d-none');
    return;
  }
  
  errorDiv.classList.add('d-none');
});

// Clear error when user changes inputs
['daySelect', 'startTime', 'endTime'].forEach(id => {
  document.getElementById(id).addEventListener('change', () => {
    document.getElementById('overlapError').classList.add('d-none');
  });
});

// Delete modal logic
let deleteSlotId = null;
let deleteDoctorId = null;
const deleteCsrfToken = <?= json_encode($csrfToken) ?>;

document.addEventListener('click', (e) => {
  const btn = e.target.closest('.btn-delete-slot');
  if (btn) {
    deleteSlotId = btn.dataset.availId;
    deleteDoctorId = btn.dataset.doctorId;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
  }
});

document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
  if (!deleteSlotId || !deleteDoctorId) return;
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '<?= (defined("BASE_PATH") ? BASE_PATH : "") ?>/index.php?route=admin/doctors/availability';
  
  const fields = {
    'action': 'delete',
    'doctor_id': deleteDoctorId,
    'Availability_Id': deleteSlotId,
    'csrf_token': deleteCsrfToken
  };
  
  Object.entries(fields).forEach(([key, val]) => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = key;
    input.value = val;
    form.appendChild(input);
  });
  
  document.body.appendChild(form);
  form.submit();
});

</script>

