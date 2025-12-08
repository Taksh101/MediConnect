<?php
// views/admin/doctors/availability_edit.php
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['old'], $_SESSION['form_errors']);

$avail = $availability;
$doctorName = $doctor['Name'] ?? ('Doctor #' . (int)$avail['Doctor_Id']);
$days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
?>

<?php include __DIR__ . '/../../includes/adminNavbar.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
  :root {
    --primary: #4f46e5;
    --primary-2: #eef2ff;
    --bg: #f8fafc;
    --edit-bg: #e0e7ff;
    --muted: #6b7280;
  }
  body { font-family: 'Inter', sans-serif; background-color: var(--bg); }
  .modern-card { background: #fff; border-radius: 14px; padding: 24px; box-shadow: 0 6px 20px rgba(15,23,42,0.06); border: none; }
  .form-control { height: 44px; border-radius: 8px; border: 1px solid #e5e7eb; }
  .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
  .form-label { font-weight: 500; color: #111827; font-size: 0.95rem; margin-bottom: 8px; }
  .btn-primary { background: var(--primary); border: none; padding: 10px 20px; border-radius: 8px; font-weight: 500; transition: all 0.2s; }
  .btn-primary:hover { background: #4338ca; transform: translateY(-1px); }
  .btn-outline-secondary { border-radius: 8px; font-weight: 500; }
  #updateError { display: none; }
</style>

<div class="container container-wide py-4 d-flex justify-content-center align-items-center" style="min-height:calc(100vh - 160px);">
  <div class="modern-card" style="width:100%; max-width:500px;">
    <h4 class="mb-1">Edit Availability</h4>
    <p class="text-muted small mb-4">Doctor: <strong><?= h($doctorName) ?></strong></p>
    
    <?php if (!empty($_SESSION['flash_error'])): ?>
      <div class="alert alert-danger"><?php echo h($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>
    <div id="updateError" class="alert alert-warning d-none mb-3" role="alert"></div>
    
    <form id="editAvailForm" method="post" action="<?= (defined('BASE_PATH')?BASE_PATH:'') ?>/index.php?route=admin/doctors/availability/update" novalidate>
      <?php if (file_exists(__DIR__ . '/../../../config/csrf.php')) { require_once __DIR__ . '/../../../config/csrf.php'; $csrf = csrf_token(); echo '<input type="hidden" name="csrf_token" value="'.h($csrf).'">'; } ?>

      <input type="hidden" name="Availability_Id" value="<?= (int)$avail['Availability_Id'] ?>">
      <input type="hidden" name="Doctor_Id" value="<?= (int)$avail['Doctor_Id'] ?>">

      <div class="mb-3">
        <label class="form-label">Day of week</label>
        <select id="daySelect" name="Available_Day" class="form-control <?= isset($errors['Available_Day']) ? 'is-invalid' : '' ?>" required>
          <?php $val = $old['Available_Day'] ?? $avail['Available_Day']; ?>
          <?php foreach($days as $d): ?>
            <option value="<?= h($d) ?>" <?= ($val === $d) ? 'selected' : '' ?>><?= h($d) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['Available_Day'])): ?><div class="invalid-feedback"><?= h($errors['Available_Day']) ?></div><?php endif; ?>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Start time</label>
          <?php $startVal = $old['Start_Time'] ?? $avail['Start_Time']; $startVal = $startVal ? date('H:i', strtotime($startVal)) : ''; ?>
          <input type="time" id="startTime" name="Start_Time" class="form-control <?= isset($errors['Start_Time']) ? 'is-invalid' : '' ?>" value="<?= h($startVal) ?>" required>
          <?php if (!empty($errors['Start_Time'])): ?><div class="invalid-feedback"><?= h($errors['Start_Time']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label">End time</label>
          <?php $endVal = $old['End_Time'] ?? $avail['End_Time']; $endVal = $endVal ? date('H:i', strtotime($endVal)) : ''; ?>
          <input type="time" id="endTime" name="End_Time" class="form-control <?= isset($errors['End_Time']) ? 'is-invalid' : '' ?>" value="<?= h($endVal) ?>" required>
          <?php if (!empty($errors['End_Time'])): ?><div class="invalid-feedback"><?= h($errors['End_Time']) ?></div><?php endif; ?>
        </div>
      </div>

      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-primary" id="saveBtn" type="submit">Save changes</button>
        <a class="btn btn-outline-secondary pt-2" href="<?= (defined('BASE_PATH')?BASE_PATH:'') ?>/index.php?route=admin/doctors/availability&doctor_id=<?= (int)$avail['Doctor_Id'] ?>">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
// Client-side validation
const currentAvailability = {
  day: <?= json_encode($avail['Available_Day']) ?>,
  id: <?= (int)$avail['Availability_Id'] ?>
};

// Get all availabilities for this doctor to check overlaps
const allAvailabilities = <?= json_encode($availabilities) ?>;

function timeToMinutes(timeStr) {
  if (!timeStr) return 0;
  const [h, m] = timeStr.split(':').map(Number);
  return (h || 0) * 60 + (m || 0);
}

function checkOverlaps(day, startTime, endTime) {
  const startMin = timeToMinutes(startTime);
  const endMin = timeToMinutes(endTime);
  
  // Two ranges overlap if:
  // - They're on the same day
  // - And the new range's start is before the existing range's end
  // - And the new range's end is after the existing range's start
  
  return allAvailabilities.some(slot => {
    // Skip the current slot we're editing
    if (slot.Availability_Id === currentAvailability.id) return false;
    // Only check same day
    if (slot.Available_Day !== day) return false;
    
    const existStart = timeToMinutes(slot.Start_Time);
    const existEnd = timeToMinutes(slot.End_Time);
    
    // Ranges overlap if: start1 < end2 AND start2 < end1
    return (startMin < existEnd && endMin > existStart);
  });
}

function validateForm() {
  const day = document.getElementById('daySelect').value;
  const startTime = document.getElementById('startTime').value;
  const endTime = document.getElementById('endTime').value;
  const errorDiv = document.getElementById('updateError');
  
  if (!day || !startTime || !endTime) {
    errorDiv.classList.add('d-none');
    return true; // Let form submission handle required fields
  }
  
  // Check start < end
  if (startTime >= endTime) {
    errorDiv.textContent = '❌ Start time must be before end time';
    errorDiv.classList.remove('d-none');
    return false;
  }
  
  // Check overlaps
  if (checkOverlaps(day, startTime, endTime)) {
    errorDiv.textContent = '⚠️ This time slot overlaps with an existing one. Please adjust the times.';
    errorDiv.classList.remove('d-none');
    return false;
  }
  
  errorDiv.classList.add('d-none');
  return true;
}

document.getElementById('editAvailForm').addEventListener('submit', function(e) {
  if (!validateForm()) {
    e.preventDefault();
  }
});

// Real-time validation on input change
['daySelect', 'startTime', 'endTime'].forEach(id => {
  const elem = document.getElementById(id);
  if (elem) {
    elem.addEventListener('change', validateForm);
    elem.addEventListener('input', validateForm);
  }
});
</script>
