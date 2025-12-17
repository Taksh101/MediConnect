<?php
// views/admin/specialities/form.php
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
$editing = !empty($speciality);
$action = $editing ? ((defined('BASE_PATH')?BASE_PATH:'') . '/index.php?route=admin/specialities/update') : ((defined('BASE_PATH')?BASE_PATH:'') . '/index.php?route=admin/specialities/store');
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['old'], $_SESSION['form_errors']);

$pageTitle = ($editing ? 'Edit' : 'Add') . ' Speciality - MediConnect';
$page = $_GET['page'] ?? 1;
include __DIR__ . '/../../includes/adminNavbar.php';
?>

<style>
  :root { --primary:#0b63ff; --radius:12px; }
  body { background:#f5f7fb; font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial; }
  
  /* Layout adjustments for fixed navbar */
  .main-content {
      margin-top: 80px; /* Space for fixed navbar */
      padding-bottom: 80px; /* Space for footer */
      min-height: calc(100vh - 140px);
      display: flex;
      justify-content: center;
      padding-left: 15px;
      padding-right: 15px;
  }
  
  .form-card { 
      border-radius: var(--radius); 
      padding: 28px; 
      border: 0; 
      background: #fff; 
      box-shadow: 0 6px 20px rgba(12,24,60,0.06); 
      width: 100%;
      max-width: 760px;
      height: fit-content;
  }
  
  .form-label { font-weight:600; margin-bottom:6px; }
  .form-control, textarea { border-radius:10px; padding:10px 12px; border:1px solid #dfe7f6; }
  .invalid-feedback { display:block; }
  .btn-primary { background:var(--primary); border-radius:10px; padding:10px 14px; font-weight:600; border: none; }
  .btn-primary:hover { background: #0056e0; }
  .btn-outline-secondary { border-radius:10px; padding:10px 14px; font-weight:600; display: inline-flex; align-items: center; justify-content: center; }
  .small-muted { color:#6b7280; }
</style>

<div class="main-content">
  <div class="form-card">
    <div class="py-2">
      <h3 class="mb-1"><?= $editing ? 'Edit Speciality' : 'Add Speciality' ?></h3>
      <p class="small-muted mb-3"><?= $editing ? 'Update the speciality details.' : 'Create a new speciality (consultation duration and fee).' ?></p>

      <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= h($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
      <?php endif; ?>

      <form id="specialityForm" action="<?= h($action) ?>" method="post" novalidate>
        <?php if ($editing): ?>
          <input type="hidden" name="Speciality_Id" value="<?= (int)$speciality['Speciality_Id'] ?>">
        <?php endif; ?>

        <?php if (file_exists(__DIR__ . '/../../config/csrf.php')): ?>
          <?php require_once __DIR__ . '/../../config/csrf.php'; $csrfToken = csrf_token(); ?>
          <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
        <?php else: $csrfToken = ''; endif; ?>

        <div class="mb-3">
          <label class="form-label">Name</label>
          <input name="Speciality_Name" id="Speciality_Name" class="form-control" value="<?= h($old['Speciality_Name'] ?? $speciality['Speciality_Name'] ?? '') ?>" required>
          <div class="invalid-feedback"><?= h($errors['Speciality_Name'] ?? '') ?></div>
        </div>

        <div class="mb-3 row g-2">
          <div class="col-md-6">
            <label class="form-label">Consultation Duration (minutes)</label>
            <input name="Consultation_Duration" id="Consultation_Duration" type="number" min="5" max="45" step="1" class="form-control"
              value="<?= h($old['Consultation_Duration'] ?? $speciality['Consultation_Duration'] ?? '') ?>" required>
            <div class="invalid-feedback"><?= h($errors['Consultation_Duration'] ?? '') ?></div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Consultation Fee</label>
            <input name="Consultation_Fee" id="Consultation_Fee" type="number" min="0" step="1" class="form-control"
value="<?= h($old['Consultation_Fee'] ?? (isset($speciality['Consultation_Fee']) ? (int)$speciality['Consultation_Fee'] : '') ) ?>" required>

            <div class="invalid-feedback"><?= h($errors['Consultation_Fee'] ?? '') ?></div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea name="Description" id="Description" rows="4" class="form-control" required><?= h($old['Description'] ?? $speciality['Description'] ?? '') ?></textarea>
          <div class="invalid-feedback"><?= h($errors['Description'] ?? '') ?></div>
        </div>

        <div class="d-flex gap-2">
          <button id="submitBtn" type="submit" class="btn btn-primary"><?= $editing ? 'Update' : 'Add Speciality' ?></button>
          <a class="btn btn-outline-secondary" href="<?= (defined('BASE_PATH')?BASE_PATH:'') ?>/index.php?route=admin/specialities&page=<?= $page ?? 1 ?>">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // Make csrfToken available to client script
  const SPECIALITY_CSRF = <?= json_encode($csrfToken) ?>;
</script>
<script src="<?= (defined('BASE_PATH')?BASE_PATH:'') ?>/assets/js/speciality.js"></script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
