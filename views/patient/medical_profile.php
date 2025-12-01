<?php
require_once __DIR__ . '/../../config/auth.php';
require_patient_login();
block_if_profile_completed();
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/csrf.php';
$profile = $profile ?? [];
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Complete Medical Profile — MediConnect</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root { --primary:#0b63ff; --radius:12px; }
    body{ background:#f5f7fb; font-family:Inter,system-ui,Roboto,Arial; }
    .card{ border:0; border-radius:12px; padding:24px; box-shadow:0 8px 24px rgba(10,20,40,0.04); background:#fff;}
    .optional { font-size:12px; color:#6b7280; margin-left:6px; }
    .form-label { font-weight:600; color:#374151; margin-bottom:6px; }
    .invalid-feedback { font-size:13px; }
    .wrap { max-width:820px; margin:28px auto; padding:18px; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h3 class="mb-3">Complete your medical profile</h3>

      <form id="mpForm" novalidate>
        <?= csrf_field(); ?>

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Blood group</label>
            <select name="blood_group" class="form-select" required>
              <option value="">Select</option>
              <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-','Unknown'] as $bg): ?>
                <option value="<?= $bg ?>" <?= ($profile['Blood_Group'] ?? '')==$bg?'selected':'' ?>><?= $bg ?></option>
              <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Choose blood group</div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Diabetes</label>
            <select name="diabetes" class="form-select" required>
              <option value="">Select</option>
              <option value="Yes" <?= ($profile['Diabetes'] ?? '')=='Yes'?'selected':'' ?>>Yes</option>
              <option value="No" <?= ($profile['Diabetes'] ?? '')=='No'?'selected':'' ?>>No</option>
            </select>
            <div class="invalid-feedback">Select diabetes status</div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Blood pressure</label>
            <select name="blood_pressure" class="form-select" required>
              <option value="">Select</option>
              <option value="Normal" <?= ($profile['Blood_Pressure'] ?? '')=='Normal'?'selected':'' ?>>Normal</option>
              <option value="Low" <?= ($profile['Blood_Pressure'] ?? '')=='Low'?'selected':'' ?>>Low</option>
              <option value="High" <?= ($profile['Blood_Pressure'] ?? '')=='High'?'selected':'' ?>>High</option>
            </select>
            <div class="invalid-feedback">Select blood pressure</div>
          </div>
        </div>

        <div class="row g-3 mt-3">
          <div class="col-md-4">
            <label class="form-label">Heart conditions</label>
            <select name="heart_conditions" class="form-select" required>
              <option value="">Select</option>
              <option value="Yes" <?= ($profile['Heart_Conditions'] ?? '')=='Yes'?'selected':'' ?>>Yes</option>
              <option value="No" <?= ($profile['Heart_Conditions'] ?? '')=='No'?'selected':'' ?>>No</option>
            </select>
            <div class="invalid-feedback">Select</div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Respiratory issues</label>
            <select name="respiratory_issues" class="form-select" required>
              <option value="">Select</option>
              <option value="Yes" <?= ($profile['Respiratory_Issues'] ?? '')=='Yes'?'selected':'' ?>>Yes</option>
              <option value="No" <?= ($profile['Respiratory_Issues'] ?? '')=='No'?'selected':'' ?>>No</option>
            </select>
            <div class="invalid-feedback">Select</div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Smoker</label>
            <select name="smoker" class="form-select" required>
              <option value="">Select</option>
              <option value="YES" <?= ($profile['Smoker'] ?? '')=='YES'?'selected':'' ?>>Yes</option>
              <option value="NO" <?= ($profile['Smoker'] ?? '')=='NO'?'selected':'' ?>>No</option>
              <option value="FORMER" <?= ($profile['Smoker'] ?? '')=='FORMER'?'selected':'' ?>>Former</option>
            </select>
            <div class="invalid-feedback">Select</div>
          </div>
        </div>

        <div class="row g-3 mt-3">
          <div class="col-md-6">
            <label class="form-label">Allergies <span class="optional">(Optional)</span></label>
            <input type="text" name="allergies" class="form-control" maxlength="255" value="<?= htmlspecialchars($profile['Allergies'] ?? '') ?>">
            <div class="invalid-feedback">Too long (max 255 chars)</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Ongoing medication <span class="optional">(Optional)</span></label>
            <input type="text" name="medication" class="form-control" maxlength="255" value="<?= htmlspecialchars($profile['Ongoing_Medication'] ?? '') ?>">
            <div class="invalid-feedback">Too long (max 255 chars)</div>
          </div>
        </div>

        <div class="row g-3 mt-3">
          <div class="col-md-6">
            <label class="form-label">Past surgeries <span class="optional">(Optional)</span></label>
            <input type="text" name="surgeries" class="form-control" maxlength="255" value="<?= htmlspecialchars($profile['Past_Surgeries'] ?? '') ?>">
            <div class="invalid-feedback">Too long (max 255 chars)</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Chronic illnesses <span class="optional">(Optional)</span></label>
            <input type="text" name="illnesses" class="form-control" maxlength="255" value="<?= htmlspecialchars($profile['Chronic_Illnesses'] ?? '') ?>">
            <div class="invalid-feedback">Too long (max 255 chars)</div>
          </div>
        </div>

        <div class="row g-3 mt-3">
          <div class="col-md-4">
            <label class="form-label">Alcohol consumption</label>
            <select name="alcohol" class="form-select" required>
              <option value="">Select</option>
              <option value="YES" <?= ($profile['Alcohol_Consumption'] ?? '')=='YES'?'selected':'' ?>>Yes</option>
              <option value="NO" <?= ($profile['Alcohol_Consumption'] ?? '')=='NO'?'selected':'' ?>>No</option>
              <option value="Occasional" <?= ($profile['Alcohol_Consumption'] ?? '')=='Occasional'?'selected':'' ?>>Occasional</option>
            </select>
            <div class="invalid-feedback">Select</div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Height (cm) <span class="optional">(Optional)</span></label>
            <input type="number" step="0.01" name="height_cm" id="height_cm" class="form-control" min="30" max="300" value="<?= htmlspecialchars($profile['Height_CM'] ?? '') ?>">
            <div class="invalid-feedback">Must be between 30 and 300</div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Weight (kg) <span class="optional">(Optional)</span></label>
            <input type="number" step="0.01" name="weight_kg" id="weight_kg" class="form-control" min="1" max="200" value="<?= htmlspecialchars($profile['Weight_KG'] ?? '') ?>">
            <div class="invalid-feedback">Must be between 1 and 200</div>
          </div>
        </div>

        <div class="row mt-4">
          <div class="col d-flex justify-content-end">
            <button id="mpSaveBtn" type="submit" class="btn btn-primary">Save & continue</button>
          </div>
        </div>
      </form>

      <div id="mpAlert" class="mt-3"></div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  (function () {
    const form = document.getElementById('mpForm');
    const alertEl = document.getElementById('mpAlert');

    // small helper to show error inline
    function setError(input, msg) {
      input.classList.add('is-invalid');
      const fb = input.parentElement.querySelector('.invalid-feedback');
      if (fb) fb.textContent = msg;
    }
    function clearError(input) {
      input.classList.remove('is-invalid');
      const fb = input.parentElement.querySelector('.invalid-feedback');
      if (fb) fb.textContent = '';
    }

    // validate one field
    function validateOne(input) {
      clearError(input);
      const name = input.name;
      const val = (input.value || '').trim();

      if (input.hasAttribute('required') && !val) {
        setError(input, 'This field is required');
        return false;
      }

      if (name === 'allergies' || name === 'medication' || name === 'surgeries' || name === 'illnesses') {
        if (val.length > 255) { setError(input, 'Too long (max 255)'); return false; }
      }

      if (name === 'height_cm' && val !== '') {
        const n = parseFloat(val);
        if (isNaN(n) || n < 30 || n > 300) { setError(input, 'Must be 30–300'); return false; }
      }
      if (name === 'weight_kg' && val !== '') {
        const n = parseFloat(val);
        if (isNaN(n) || n < 1 || n > 200) { setError(input, 'Must be 1–200'); return false; }
      }

      return true;
    }

    // attach simple validation events
    Array.from(form.querySelectorAll('input,select,textarea')).forEach(el => {
      el.addEventListener('input', () => { clearError(el); });
      el.addEventListener('blur', () => validateOne(el));
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();

      // validate all
      let ok = true;
      Array.from(form.querySelectorAll('input,select,textarea')).forEach(el => {
        if (!validateOne(el)) ok = false;
      });
      if (!ok) return;

      // submit via fetch
      const fd = new FormData(form);

      fetch('<?= htmlspecialchars(BASE_PATH . "/index.php?route=patient/medical-save") ?>', {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
      })
      .then(r => r.json())
      .then(res => {
        if (res.status === 'success') {
          // alertEl.innerHTML = '<div class="alert alert-success">Saved. Redirecting...</div>';
          setTimeout(() => { window.location.href = '<?= htmlspecialchars(BASE_PATH . "/index.php?route=patient/dashboard") ?>'; }, 700);
        } else {
          alertEl.innerHTML = '<div class="alert alert-danger">' + (res.message || 'Save failed') + '</div>';
        }
      })
      .catch(() => {
        alertEl.innerHTML = '<div class="alert alert-danger">Network error</div>';
      });
    });
  })();
  </script>
</body>
</html>
