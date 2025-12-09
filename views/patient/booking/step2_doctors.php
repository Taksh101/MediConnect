<?php
// views/patient/booking/step2_doctors.php
// Expects: $speciality (array), $doctors (array)

if (!function_exists('h')) { function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); } }
?>

<?php $pageTitle = 'MediConnect - Select Doctor';
include __DIR__ . '/../../includes/patientNavbar.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root {
        --primary-color: #4f46e5;
        --card-radius: 12px;
        --text-dark: #1f2937;
    }
    html, body {
        background-color: #f5f7fb;
        font-family: 'Inter', sans-serif;
        color: var(--text-dark);
        margin: 0;
        padding: 0;
    }
    .container-wide {
        max-width: 900px;
        margin-top: 60px;
        margin-bottom: 40px;
    }
    
    /* Progress Bar */
    .step-indicator { display: flex; justify-content: space-between; margin-bottom: 30px; position: relative; }
    .step-indicator::before { content: ''; position: absolute; top: 15px; left: 0; right: 0; height: 3px; background: #e5e7eb; z-index: 0; }
    .step { position: relative; z-index: 1; background: #fff; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #9ca3af; border: 2px solid #e5e7eb; transition: all 0.3s; }
    .step.completed { background: #10b981; color: white; border-color: #10b981; }
    .step.active { background: var(--primary-color); color: white; border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.2); }
    
    .card-block { background: white; border-radius: 16px; padding: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    
    /* Doctor Cards */
    .doctor-card {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.2s;
        height: 100%;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .doctor-card:hover { border-color: #a5b4fc; transform: translateY(-2px); }
    .doctor-card.selected { border-color: var(--primary-color); background-color: #eef2ff; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15); }
    
    .check-icon { position: absolute; top: 15px; right: 15px; font-size: 1.2rem; color: var(--primary-color); opacity: 0; transform: scale(0.5); transition: all 0.2s; }
    .doctor-card.selected .check-icon { opacity: 1; transform: scale(1); }
    
    .doc-avatar { width: 80px; height: 80px; background: #e0e7ff; color: #4338ca; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; margin-bottom: 15px; }
    .doctor-card.selected .doc-avatar { background: white; color: var(--primary-color); }

    .btn-next { background: var(--primary-color); color: white; border: none; padding: 12px 30px; border-radius: 8px; font-weight: 600; font-size: 1rem; transition: all 0.2s; }
    .btn-next:hover { background: #4338ca; transform: translateY(-1px); }
    .btn-next:disabled { background: #9ca3af; transform: none; cursor: not-allowed; opacity: 0.7; }
</style>

<div class="container container-wide pb-4">
    
    <!-- Progress (Perfectly Aligned) -->
    <div class="px-3 mb-5">
        <div class="d-flex justify-content-between position-relative">
            <!-- Line behind -->
            <div style="position: absolute; top: 15px; left: 0; right: 0; height: 3px; background: #e5e7eb; z-index: 0;"></div>
            
            <!-- Step 1 -->
            <div class="d-flex flex-column align-items-center position-relative" style="z-index: 1; width: 60px;">
                <div class="step completed"><i class="bi bi-check"></i></div>
                <div class="mt-2 text-dark small fw-medium">Speciality</div>
            </div>
            
            <!-- Step 2 -->
            <div class="d-flex flex-column align-items-center position-relative" style="z-index: 1; width: 60px;">
                <div class="step active">2</div>
                <div class="mt-2 fw-bold text-dark small">Doctor</div>
            </div>
            
            <!-- Step 3 -->
            <div class="d-flex flex-column align-items-center position-relative" style="z-index: 1; width: 60px;">
                <div class="step">3</div>
                <div class="mt-2 text-muted small fw-medium">Time</div>
            </div>
            
            <!-- Step 4 -->
            <div class="d-flex flex-column align-items-center position-relative" style="z-index: 1; width: 60px;">
                <div class="step">4</div>
                <div class="mt-2 text-muted small fw-medium">Confirm</div>
            </div>
        </div>
    </div>

    <div class="card-block">
        <div class="mb-4 text-center">
            <span class="badge bg-light text-primary border mb-2 px-3 py-2 fs-6 rounded-pill"><?= h($speciality['Speciality_Name']) ?></span>
            <h3 class="fw-bold mb-1">Select Doctor</h3>
            <p class="text-muted">Choose a specialist for your consultation.</p>
        </div>
                
        <form id="bookingForm" action="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php" method="GET">
            <input type="hidden" name="route" value="patient/book/step3">
            <input type="hidden" name="speciality_id" value="<?= h($speciality['Speciality_Id']) ?>">
            <input type="hidden" name="doctor_id" id="doctorInput" required>
            
            <div class="row g-3 mb-4 justify-content-center">
                <?php if (empty($doctors)): ?>
                    <div class="col-12 text-center py-5">
                        <div class="text-muted mb-2"><i class="bi bi-person-x fs-1"></i></div>
                        <p class="lead">No doctors available for this speciality at the moment.</p>
                        <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/book/start" class="btn btn-outline-primary btn-sm mt-3">Choose another speciality</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($doctors as $d): ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="doctor-card" onclick="selectDoctor(this, <?= $d['Doctor_Id'] ?>)">
                                <i class="bi bi-check-circle-fill check-icon"></i>
                                <div class="doc-avatar">
                                    <?= strtoupper(substr($d['Name'], 0, 1)) ?>
                                </div>
                                <?php 
                                    $dName = $d['Name'];
                                    $displayDr = (stripos(trim($dName), 'Dr.') === 0 || stripos(trim($dName), 'Dr ') === 0) ? '' : 'Dr. ';
                                ?>
                                <h5 class="fw-bold fs-6 mb-1 text-dark"><?= $displayDr . h($dName) ?></h5>
                                <div class="text-primary small fw-semibold mb-2"><?= h($d['Qualification']) ?></div>
                                <div class="small text-muted mb-3" style="font-size: 0.85rem;">
                                    <?= $d['Experience_Years'] ?>+ Years Exp.
                                </div>
                                <!-- Bio snippet? -->
                                <!-- <div class="small text-muted fst-italic">"<?= h(substr($d['Bio']??'', 0, 40)) ?>..."</div> -->
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($doctors)): ?>
            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/book/start" class="text-decoration-none text-muted fw-medium"><i class="bi bi-arrow-left me-1"></i> Back</a>
                <button type="submit" id="nextBtn" class="btn-next" disabled>
                    Next Step <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div style="height: 40px;"></div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
    function selectDoctor(card, id) {
        document.querySelectorAll('.doctor-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        document.getElementById('doctorInput').value = id;
        document.getElementById('nextBtn').removeAttribute('disabled');
    }
    
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        if (!document.getElementById('doctorInput').value) {
            e.preventDefault();
            alert('Please select a doctor to proceed.');
        }
    });
</script>
