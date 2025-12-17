<?php
// views/admin/doctors/form.php
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
$editing = !empty($doctor);
$action = $editing ? ((defined('BASE_PATH')?BASE_PATH:'') . '/index.php?route=admin/doctors/update') : ((defined('BASE_PATH')?BASE_PATH:'') . '/index.php?route=admin/doctors/store');
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['old'], $_SESSION['form_errors']);
$page = $_GET['page'] ?? 1;
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= $editing ? 'Edit' : 'Add' ?> Doctor - MediConnect</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    :root { --primary:#0b63ff; --radius:12px; --bg:#f5f7fb; }
    html,body { height:100%; }
    body { background:var(--bg); font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial; margin:0; }
    /* Center the form and avoid sticking to header/footer */
    .outer {
      min-height: calc(100vh - 140px); /* leave room for navbar/footer */
      display: grid;
      place-items: center;
      padding: 32px 12px;
    }
    .card {
      border-radius: var(--radius);
      padding:20px;
      border:0;
      background:#fff;
      box-shadow: 0 6px 20px rgba(12,24,60,0.06);
      width: 100%;
      max-width: 920px;
    }
    .form-label { font-weight:600; margin-bottom:6px; }
    .form-control, textarea { border-radius:8px; padding:10px 12px; border:1px solid #dfe7f6; }
    .invalid-feedback { display:block; }
    .btn-primary { background:var(--primary); border-radius:10px; padding:10px 14px; font-weight:600; }
    .small-muted { color:#6b7280; }
    .password-note { font-size:0.85rem; color:#6b7280; margin-top:6px; }
    /* make the input-group border match design */
    .input-group .form-control {
      border-top-right-radius: 0;
      border-bottom-right-radius: 0;
    }
    .input-group .btn {
      border-top-left-radius: 0;
      border-bottom-left-radius: 0;
      border-left: 1px solid #e6eefc;
    }
    body{
        padding:70px 0px;
    }
  </style>
</head>
<?php include __DIR__ . '/../../includes/adminNavbar.php'; ?>
<body>
  <div class="outer">
    <div class="card">

      <div class="py-2">
        <h3 class="mb-1"><?= $editing ? 'Edit Doctor' : 'Add Doctor' ?></h3>
        <p class="small-muted mb-3"><?= $editing ? 'Update doctor details.' : 'Create a new doctor account.' ?></p>

        <?php if (!empty($_SESSION['flash_error'])): ?>
          <div class="alert alert-danger"><?= h($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <form id="doctorForm" action="<?= h($action) ?>" method="post" novalidate>
          <?php if ($editing): ?>
            <input type="hidden" name="Doctor_Id" value="<?= (int)$doctor['Doctor_Id'] ?>">
          <?php endif; ?>

          <?php if (file_exists(__DIR__ . '/../../config/csrf.php')): ?>
            <?php require_once __DIR__ . '/../../config/csrf.php'; $csrfToken = csrf_token(); ?>
            <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
          <?php else: $csrfToken = ''; endif; ?>

          <div class="row g-3">
            <div class="col-12">
              <div class="mb-3">
                <label class="form-label">Name</label>
                <input name="Name" id="Name" class="form-control" value="<?= h($old['Name'] ?? $doctor['Name'] ?? '') ?>" required>
                <div class="invalid-feedback"><?= h($errors['Name'] ?? '') ?></div>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input name="Email" id="Email" type="email" class="form-control" value="<?= h($old['Email'] ?? $doctor['Email'] ?? '') ?>" required>
              <div class="invalid-feedback"><?= h($errors['Email'] ?? '') ?></div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input name="Phone" id="Phone" inputmode="numeric" pattern="[0-9]*" maxlength="10" class="form-control" value="<?= h($old['Phone'] ?? $doctor['Phone'] ?? '') ?>" required>
              <div class="invalid-feedback"><?= h($errors['Phone'] ?? '') ?></div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Speciality</label>
              <select name="Speciality_Id" id="Speciality_Id" class="form-control" required>
                <option value="">Select speciality</option>
                <?php foreach($specialities as $s): ?>
                  <option value="<?= (int)$s['Speciality_Id'] ?>" <?= ((int)($old['Speciality_Id'] ?? $doctor['Speciality_Id'] ?? 0) === (int)$s['Speciality_Id']) ? 'selected' : '' ?>><?= h($s['Speciality_Name']) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback"><?= h($errors['Speciality_Id'] ?? '') ?></div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Experience (years)</label>
              <input name="Experience_Years" id="Experience_Years" type="number" min="0" class="form-control" value="<?= h($old['Experience_Years'] ?? $doctor['Experience_Years'] ?? '') ?>" required>
              <div class="invalid-feedback"><?= h($errors['Experience_Years'] ?? '') ?></div>
            </div>

            <div class="col-12">
              <label class="form-label">Qualification</label>
              <input name="Qualification" id="Qualification" class="form-control" value="<?= h($old['Qualification'] ?? $doctor['Qualification'] ?? '') ?>" required>
              <div class="invalid-feedback"><?= h($errors['Qualification'] ?? '') ?></div>
            </div>

            <div class="col-12">
              <label class="form-label">Bio</label>
              <textarea name="Bio" id="Bio" rows="4" class="form-control" required><?= h($old['Bio'] ?? $doctor['Bio'] ?? '') ?></textarea>
              <div class="invalid-feedback"><?= h($errors['Bio'] ?? '') ?></div>
            </div>

            <div class="col-md-6">
              <label class="form-label"><?= $editing ? 'Password (leave blank to keep current)' : 'Password' ?></label>
              <div class="input-group">
                <input name="Password" id="Password" type="password" class="form-control" <?= $editing ? '' : 'required' ?> autocomplete="new-password" aria-describedby="pwdHelp">
                <button type="button" id="pwdToggle" class="input-group-text btn-icon" style="cursor:pointer;">
  <svg id="pwdEyeIcon" width="18" height="18" viewBox="0 0 16 16" fill="none">
    <path d="M1 8C1 8 3.5 3.5 8 3.5C12.5 3.5 15 8 15 8C15 8 12.5 12.5 8 12.5C3.5 12.5 1 8 1 8Z"
          stroke="#374151" stroke-width="1.4"/>
    <circle cx="8" cy="8" r="2.3" stroke="#374151" stroke-width="1.4"/>
  </svg>
</button>

              </div>
              <div id="pwdHelp" class="password-note">Min 8 chars; include uppercase, lowercase, digit and symbol.</div>
              <div class="invalid-feedback"><?= h($errors['Password'] ?? '') ?></div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select name="Status" id="Status" class="form-control" required>
                <?php $sel = $old['Status'] ?? $doctor['Status'] ?? 'AVAILABLE'; ?>
                <option value="AVAILABLE" <?= $sel === 'AVAILABLE' ? 'selected' : '' ?>>AVAILABLE</option>
                <option value="UNAVAILABLE" <?= $sel === 'UNAVAILABLE' ? 'selected' : '' ?>>UNAVAILABLE</option>
              </select>
              <div class="invalid-feedback"><?= h($errors['Status'] ?? '') ?></div>
            </div>

            <div class="col-12 d-flex gap-2 mt-2">
              <button id="submitBtn" type="submit" class="btn btn-primary"><?= $editing ? 'Update' : 'Add Doctor' ?></button>
              <a class="btn btn-outline-secondary" href="<?= (defined('BASE_PATH')?BASE_PATH:'') ?>/index.php?route=admin/doctors&page=<?= $page ?? 1 ?>">Cancel</a>
            </div>
          </div>
        </form>
      </div>
      <?php include __DIR__ . '/../../includes/footer.php'; ?>
    </div>
</div>
<div style="height:60px;"></div>             

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>const DOCTOR_CSRF = <?= json_encode($csrfToken) ?>;</script>
  <script src="<?= (defined('BASE_PATH')?BASE_PATH:'') ?>/assets/js/doctors.js"></script>
</body>
</html>
