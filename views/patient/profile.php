<?php
// views/patient/profile.php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/csrf.php';

// Ensure proper session/auth check if not already done by controller
if (session_status() === PHP_SESSION_NONE) session_start();

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// Profile Helper
function showVal($val) {
    if ($val === null || $val === '' || $val === '-') return 'NA';
    return h($val);
}

// Data from controller
$initial = isset($patient['Name']) && !empty($patient['Name']) ? strtoupper($patient['Name'][0]) : 'P';
$mp = $profile; // Alias
?>
<?php $pageTitle = 'MediConnect - My Profile';
include __DIR__ . '/../includes/patientNavbar.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    body { background-color: #f8f9fa; color: #344767; font-family: 'Inter', sans-serif; }
    .row-equal { align-items: stretch; }
    
    /* Icons & Buttons */
    .btn-primary { background-color: #4f46e5; border-color: #4f46e5; }
    .btn-primary:hover { background-color: #4338ca; border-color: #4338ca; }
    .text-primary { color: #4f46e5 !important; }
    .bg-primary-subtle { background-color: #e0e7ff !important; color: #4f46e5 !important; }

    /* Profile Card */
    .profile-card {
        border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        background: #fff; width: 100%; height: 100%; display: flex; flex-direction: column;
    }
    .profile-header-bg {
        height: 110px; background: linear-gradient(135deg, #4f46e5 0%, #818cf8 100%);
        border-radius: 16px 16px 0 0; flex-shrink: 0;
    }
    .avatar-wrapper {
        margin-top: -60px; text-align: center; position: relative; flex-shrink: 0;
    }
    .avatar-circle {
        width: 100px; height: 100px; background: #fff; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1); font-size: 2.5rem; font-weight: 700;
        color: #4f46e5; border: 4px solid rgba(255,255,255,0.8);
    }
    .patient-details-section { padding: 0 1.5rem 1.5rem 1.5rem; flex-grow: 1; }
    
    .bio-card-header { padding: 1.5rem; border-bottom: 1px solid #f0f2f5; }
    .bio-card-body { padding: 1.5rem; }
    
    .medical-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
    @media (max-width: 768px) { .medical-grid { grid-template-columns: 1fr; } }
    
    .medical-item { background: #f8fafc; border-radius: 12px; padding: 1rem; border: 1px solid #e2e8f0; }
    .label-small { font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600; margin-bottom: 6px; display: block; }
    .value-text { font-weight: 600; color: #334155; font-size: 0.95rem; min-height: 24px;}
    
    /* Edit Mode Styles */
    /* .edit-mode hidden by JS init using d-none class */
    
    .form-control-edit, .form-select-edit {
        border: 1px solid #d1d5db; border-radius: 6px; padding: 6px 10px;
        font-size: 0.9rem; width: 100%;
    }
    .form-control-edit:focus, .form-select-edit:focus {
        border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); outline: none;
    }
    
    .detail-box-view {
        background-color: #f8f9fa; padding: 10px; border-radius: 8px; border: 1px solid #e9ecef;
        min-height: 60px; font-size: 0.9rem; color: #334155;
    }

</style>

<div class="container py-5">
    <!-- Main Form handles the save -->
    <form id="profileForm">
        <?= csrf_field(); ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">My Profile</h4>
                <p class="text-muted mb-0 small">Manage your personal information and health records.</p>
            </div>
            <div>
                <!-- View Mode Buttons -->
                <button type="button" id="startEditBtn" class="btn btn-outline-primary px-4 view-mode">
                    <i class="bi bi-pencil-square me-2"></i>Edit Profile
                </button>
                
                <!-- Edit Mode Buttons -->
                <div class="edit-mode d-flex gap-2">
                    <button type="button" id="cancelEditBtn" class="btn btn-outline-secondary px-3">Cancel</button>
                    <button type="submit" id="saveBtn" class="btn btn-primary px-4">
                        <i class="bi bi-check2-circle me-2"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>

        <div id="alertBox"></div>

        <div class="row g-4 row-equal">
            <!-- LEFT COLUMN: Basic Info -->
            <div class="col-lg-4">
                <div class="profile-card">
                    <div class="profile-header-bg"></div>
                    
                    <div class="avatar-wrapper">
                        <div class="avatar-circle"><?= $initial ?></div>
                    </div>

                    <div class="text-center mt-3 px-4 flex-shrink-0">
                        <!-- Name -->
                        <div class="view-mode">
                            <h4 class="mb-1 fw-bold"><?= h($patient['Name']) ?></h4>
                        </div>
                        <div class="edit-mode mb-2">
                            <input type="text" name="name" class="form-control-edit text-center fw-bold fs-5" value="<?= h($patient['Name']) ?>" required maxlength="100">
                        </div>
                        
                        <p class="text-muted mb-3 text-sm">Patient ID: #<?= str_pad((string)($patient['Patient_Id']??0), 4, '0', STR_PAD_LEFT) ?></p>
                    </div>

                    <hr class="horizontal dark mx-4 my-3">

                    <div class="patient-details-section">
                        <div class="row g-3">
                            <!-- Fixed Fields (No Edit Mode needed) -->
                            <div class="col-12">
                                <span class="label-small"><i class="bi bi-envelope me-1"></i> Email</span>
                                <div class="value-text text-truncate"><?= showVal($patient['Email']) ?></div>
                            </div>
                            <div class="col-12">
                                <span class="label-small"><i class="bi bi-telephone me-1"></i> Phone</span>
                                <div class="value-text"><?= showVal($patient['Phone']) ?></div>
                            </div>
                            <div class="col-6">
                                <span class="label-small">Gender</span>
                                <div class="value-text"><?= showVal($patient['Gender']) ?></div>
                            </div>
                            <div class="col-6">
                                <span class="label-small">DOB</span>
                                <div class="value-text"><?= showVal($patient['DOB']) ?></div>
                            </div>
                            
                            <!-- Address (Editable) -->
                            <div class="col-12">
                                <span class="label-small">Address</span>
                                <div class="view-mode value-text"><?= showVal($patient['Address']) ?></div>
                                <div class="edit-mode">
                                    <textarea name="address" class="form-control-edit" rows="2" maxlength="255"><?= h($patient['Address'] ?? '') ?></textarea>
                                </div>
                            </div>
                            
                            <div class="col-12 pt-2">
                                 <span class="label-small">Joined</span>
                                 <div class="value-text"><?= showVal($patient['Created_At']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Medical Profile -->
            <div class="col-lg-8">
                <div class="profile-card">
                    <div class="bio-card-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary-subtle text-primary p-2 rounded-3 me-3">
                                <i class="bi bi-activity fs-5"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Medical Profile</h5>
                                <p class="text-muted small mb-0">Health metrics and history</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bio-card-body">
                        <?php if (empty($mp) && empty($_GET['route'])): /* Should allow adding if empty too */ endif; ?>
                        
                        <div class="medical-grid">
                            <!-- Blood Group -->
                            <div class="medical-item">
                                <span class="label-small">Blood Group</span>
                                <div class="view-mode value-text text-primary"><?= showVal($mp['Blood_Group'] ?? '') ?></div>
                                <div class="edit-mode">
                                    <select name="blood_group" class="form-select-edit" required>
                                        <option value="">Select</option>
                                        <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-','Unknown'] as $bg): ?>
                                            <option value="<?= $bg ?>" <?= ($mp['Blood_Group']??'')===$bg?'selected':'' ?>><?= $bg ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- BMI -->
                            <div class="medical-item">
                                <span class="label-small">BMI</span>
                                <div class="value-text"><?= showVal($mp['BMI'] ?? '') ?></div>
                            </div>
                            
                            <!-- Weight/Height -->
                            <div class="medical-item">
                                <span class="label-small">Weight / Height</span>
                                <div class="view-mode value-text"><?= showVal($mp['Weight_KG'] ?? '') ?> kg / <?= showVal($mp['Height_CM'] ?? '') ?> cm</div>
                                <div class="edit-mode d-flex gap-2">
                                    <input type="number" name="weight_kg" class="form-control-edit" placeholder="kg" value="<?= h($mp['Weight_KG'] ?? '') ?>" step="0.1" min="1" max="200">
                                    <input type="number" name="height_cm" class="form-control-edit" placeholder="cm" value="<?= h($mp['Height_CM'] ?? '') ?>" step="0.1" min="30" max="300">
                                </div>
                            </div>

                            <!-- Diabetes -->
                            <div class="medical-item">
                                <span class="label-small">Diabetes</span>
                                <div class="view-mode value-text"><?= showVal($mp['Diabetes'] ?? '') ?></div>
                                <div class="edit-mode">
                                    <select name="diabetes" class="form-select-edit" required>
                                        <option value="">Select</option>
                                        <?php foreach(['Yes','No'] as $opt): ?>
                                            <option value="<?= $opt ?>" <?= ($mp['Diabetes']??'')===$opt?'selected':'' ?>><?= $opt ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- BP -->
                            <div class="medical-item">
                                <span class="label-small">Blood Pressure</span>
                                <div class="view-mode value-text"><?= showVal($mp['Blood_Pressure'] ?? '') ?></div>
                                <div class="edit-mode">
                                    <select name="blood_pressure" class="form-select-edit" required>
                                        <option value="">Select</option>
                                        <?php foreach(['Normal','Low','High'] as $opt): ?>
                                            <option value="<?= $opt ?>" <?= ($mp['Blood_Pressure']??'')===$opt?'selected':'' ?>><?= $opt ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Heart -->
                             <div class="medical-item">
                                <span class="label-small">Heart Condition</span>
                                <div class="view-mode value-text"><?= showVal($mp['Heart_Conditions'] ?? '') ?></div>
                                <div class="edit-mode">
                                    <select name="heart_conditions" class="form-select-edit" required>
                                        <option value="">Select</option>
                                        <?php foreach(['Yes','No'] as $opt): ?>
                                            <option value="<?= $opt ?>" <?= ($mp['Heart_Conditions']??'')===$opt?'selected':'' ?>><?= $opt ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Respiratory -->
                            <div class="medical-item">
                                <span class="label-small">Respiratory Issues</span>
                                <div class="view-mode value-text"><?= showVal($mp['Respiratory_Issues'] ?? '') ?></div>
                                <div class="edit-mode">
                                    <select name="respiratory_issues" class="form-select-edit" required>
                                        <option value="">Select</option>
                                        <?php foreach(['Yes','No'] as $opt): ?>
                                            <option value="<?= $opt ?>" <?= ($mp['Respiratory_Issues']??'')===$opt?'selected':'' ?>><?= $opt ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Smoker -->
                            <div class="medical-item">
                                <span class="label-small">Smoker</span>
                                <div class="view-mode value-text"><?= showVal($mp['Smoker'] ?? '') ?></div>
                                <div class="edit-mode">
                                    <select name="smoker" class="form-select-edit" required>
                                        <option value="">Select</option>
                                        <?php foreach(['YES'=>'Yes','NO'=>'No','FORMER'=>'Former'] as $val=>$display): ?>
                                            <option value="<?= $val ?>" <?= ($mp['Smoker']??'')===$val?'selected':'' ?>><?= $display ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Alcohol -->
                            <div class="medical-item">
                                <span class="label-small">Alcohol Consumption</span>
                                <div class="view-mode value-text"><?= showVal($mp['Alcohol_Consumption'] ?? '') ?></div>
                                <div class="edit-mode">
                                    <select name="alcohol" class="form-select-edit" required>
                                        <option value="">Select</option>
                                        <?php foreach(['YES'=>'Yes','NO'=>'No','Occasional'=>'Occasional'] as $val=>$display): ?>
                                            <option value="<?= $val ?>" <?= ($mp['Alcohol_Consumption']??'')===$val?'selected':'' ?>><?= $display ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        <!-- Detailed Text Fields -->
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-2 small text-uppercase text-muted">Allergies</h6>
                                <div class="view-mode detail-box-view"><?= nl2br(showVal($mp['Allergies'] ?? '')) ?></div>
                                <div class="edit-mode">
                                    <textarea name="allergies" class="form-control-edit" rows="3" placeholder="List any allergies..."><?= h($mp['Allergies'] ?? '') ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-2 small text-uppercase text-muted">Current Medication</h6>
                                <div class="view-mode detail-box-view"><?= nl2br(showVal($mp['Ongoing_Medication'] ?? '')) ?></div>
                                <div class="edit-mode">
                                    <textarea name="medication" class="form-control-edit" rows="3" placeholder="Ongoing medications..."><?= h($mp['Ongoing_Medication'] ?? '') ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-2 small text-uppercase text-muted">Past Surgeries</h6>
                                <div class="view-mode detail-box-view"><?= nl2br(showVal($mp['Past_Surgeries'] ?? '')) ?></div>
                                <div class="edit-mode">
                                    <textarea name="surgeries" class="form-control-edit" rows="3" placeholder="Surgical history..."><?= h($mp['Past_Surgeries'] ?? '') ?></textarea>
                                </div>
                            </div>
                             <div class="col-md-6">
                                <h6 class="fw-bold mb-2 small text-uppercase text-muted">Chronic Illnesses</h6>
                                <div class="view-mode detail-box-view"><?= nl2br(showVal($mp['Chronic_Illnesses'] ?? '')) ?></div>
                                <div class="edit-mode">
                                    <textarea name="illnesses" class="form-control-edit" rows="3" placeholder="Chronic conditions..."><?= h($mp['Chronic_Illnesses'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
<div style="height:60px;"></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('profileForm');
    const alertBox = document.getElementById('alertBox');
    const saveBtn = document.getElementById('saveBtn');
    const startEditBtn = document.getElementById('startEditBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    
    // Toggle Mode Function
    const toggleEditMode = (showEdit) => {
        const viewEls = document.querySelectorAll('.view-mode');
        const editEls = document.querySelectorAll('.edit-mode');
        
        if (showEdit) {
            viewEls.forEach(el => el.classList.add('d-none'));
            // d-flex needed for button container to keep alignment, else block
            editEls.forEach(el => {
                el.classList.remove('d-none');
                // restore flex if it had d-flex in class (like buttons)
                if(el.classList.contains('d-flex-on-edit')) el.classList.add('d-flex');
            });
        } else {
            editEls.forEach(el => el.classList.add('d-none'));
            viewEls.forEach(el => el.classList.remove('d-none'));
        }
    };
    
    // Init: Ensure correct state
    toggleEditMode(false);

    startEditBtn.addEventListener('click', () => toggleEditMode(true));
    cancelEditBtn.addEventListener('click', () => {
        toggleEditMode(false);
        form.reset(); // Reset changes
        alertBox.innerHTML = '';
    });

    // Validation Rules
    const validate = (input) => {
        const val = input.value.trim();
        const name = input.name;
        let valid = true;
        let msg = '';

        input.classList.remove('is-invalid');

        if (input.hasAttribute('required') && !val) {
            valid = false;
            msg = 'This field is required';
        }

        if (name === 'height_cm' && val) {
            if (val < 30 || val > 300) { valid = false; msg = 'Height must be 30-300 cm'; }
        }
        if (name === 'weight_kg' && val) {
            if (val < 1 || val > 200) { valid = false; msg = 'Weight must be 1-200 kg'; }
        }

        if (!valid) {
            input.classList.add('is-invalid');
            // Try to find feedback div next to it, or parent
            let fb = input.nextElementSibling;
            if (!fb || !fb.classList.contains('invalid-feedback')) {
                 // Check parent for flexible inputs
                 fb = input.parentElement.querySelector('.invalid-feedback');
                 // If not found, create it dynamically for edit mode
                 if (!fb) {
                     fb = document.createElement('div');
                     fb.className = 'invalid-feedback';
                     input.parentElement.appendChild(fb);
                 }
            }
            if (fb) fb.textContent = msg;
        }

        return valid;
    };

    // Real-time validation
    form.querySelectorAll('input, select, textarea').forEach(el => {
        el.addEventListener('input', () => validate(el));
        el.addEventListener('blur', () => validate(el));
    });

    // Submit Handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        alertBox.innerHTML = '';
        
        let isValid = true;
        form.querySelectorAll('.edit-mode input, .edit-mode select, .edit-mode textarea').forEach(el => {
            if (!validate(el)) isValid = false;
        });

        if (!isValid) {
            alertBox.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>Please fix the errors below.</div>`;
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }

        const originalBtnText = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Saving...`;

        const fd = new FormData(form);

        fetch('<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/medical-save', {
            method: 'POST',
            body: fd
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alertBox.innerHTML = `<div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i>Profile updated successfully!</div>`;
                window.scrollTo({ top: 0, behavior: 'smooth' });
                // Reload to switch back to view mode with new data
                setTimeout(() => window.location.reload(), 800);
            } else {
                throw new Error(data.message || 'Save failed');
            }
        })
        .catch(err => {
            alertBox.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>${err.message}</div>`;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalBtnText;
        });
    });
});
</script>
