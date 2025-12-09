<?php
// views/patient/booking/step1_speciality.php
// Expects: $specialities (array)

if (!function_exists('h')) { function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); } }
?>

<?php $pageTitle = 'MediConnect - Book Appointment';
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
        max-width: 900px; /* Narrower for focus */
        margin-top: 60px;
        margin-bottom: 40px;
    }
    
    /* Progress Bar */
    .step-indicator { display: flex; justify-content: space-between; margin-bottom: 30px; position: relative; }
    .step-indicator::before { content: ''; position: absolute; top: 15px; left: 0; right: 0; height: 3px; background: #e5e7eb; z-index: 0; }
    .step { position: relative; z-index: 1; background: #fff; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #9ca3af; border: 2px solid #e5e7eb; transition: all 0.3s; }
    .step.active { background: var(--primary-color); color: white; border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.2); }
    .step.completed { background: #10b981; color: white; border-color: #10b981; }
    
    .card-block { background: white; border-radius: 16px; padding: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    
    /* Speciality Radio Cards */
    .speciality-card {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.2s;
        height: 100%;
        position: relative;
    }
    .speciality-card:hover { border-color: #a5b4fc; transform: translateY(-2px); }
    .speciality-card.selected { border-color: var(--primary-color); background-color: #eef2ff; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15); }
    
    .check-icon { position: absolute; top: 15px; right: 15px; font-size: 1.2rem; color: var(--primary-color); opacity: 0; transform: scale(0.5); transition: all 0.2s; }
    .speciality-card.selected .check-icon { opacity: 1; transform: scale(1); }
    
    .spec-icon { width: 48px; height: 48px; background: #f3f4f6; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #4b5563; margin-bottom: 15px; transition: all 0.2s; }
    .speciality-card.selected .spec-icon { background: white; color: var(--primary-color); }

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
                <div class="step active">1</div>
                <div class="mt-2 fw-bold text-dark small">Speciality</div>
            </div>
            
            <!-- Step 2 -->
            <div class="d-flex flex-column align-items-center position-relative" style="z-index: 1; width: 60px;">
                <div class="step">2</div>
                <div class="mt-2 text-muted small fw-medium">Doctor</div>
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
        <h3 class="fw-bold mb-1">Select Speciality</h3>
        <p class="text-muted mb-4">Choose the medical department for your consultation.</p>
        
        <form id="bookingForm" action="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php" method="GET">
            <input type="hidden" name="route" value="patient/book/step2">
            <input type="hidden" name="speciality_id" id="specialityInput" required>
            
            <div class="row g-3 mb-4">
                <?php if (empty($specialities)): ?>
                    <div class="col-12 text-center py-5">
                        <div class="text-muted mb-2"><i class="bi bi-emoji-frown fs-1"></i></div>
                        <p>No specialities found. Please contact admin.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($specialities as $s): ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="speciality-card h-100 d-flex flex-column" onclick="selectSpeciality(this, <?= $s['Speciality_Id'] ?>)">
                                <i class="bi bi-check-circle-fill check-icon"></i>
                                <div class="spec-icon">
                                    <i class="bi bi-hospital"></i>
                                </div>
                                <h5 class="fw-bold fs-6 mb-2 text-dark"><?= h($s['Speciality_Name']) ?></h5>
                                
                                <div class="small text-muted mb-3 flex-grow-1 position-relative">
                                    <div class="desc-content" style="font-size: 0.85rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="<?= h($s['Description'] ?? '') ?>">
                                        <?= h($s['Description'] ?? 'No description available') ?>
                                    </div>
                                    <?php if (strlen($s['Description'] ?? '') > 60): ?>
                                        <div class="mt-1">
                                            <span class="text-primary fw-semibold" style="font-size: 0.75rem; cursor: pointer;" onclick="toggleReadMore(event, this)">Read More</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-flex align-items-center gap-2 mt-auto">
                                    <span class="badge bg-light text-success border border-success-subtle fw-medium">
                                        <i class="bi bi-currency-rupee"></i><?= number_format((float)$s['Consultation_Fee'], 0) ?>
                                    </span>
                                    <span class="badge bg-light text-primary border border-primary-subtle fw-medium">
                                        <i class="bi bi-clock me-1"></i><?= $s['Consultation_Duration'] ?>m
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/dashboard" class="text-decoration-none text-muted fw-medium">Cancel</a>
                <button type="submit" id="nextBtn" class="btn-next" disabled>
                    Next Step <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<div style="height: 40px;"></div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
    function toggleReadMore(e, el) {
        e.stopPropagation(); // Don't select card
        const content = el.closest('.position-relative').querySelector('.desc-content');
        if (content.style.webkitLineClamp === '2') {
            content.style.webkitLineClamp = 'unset';
            content.style.overflow = 'visible';
            el.innerText = 'Read Less';
        } else {
            content.style.webkitLineClamp = '2';
            content.style.overflow = 'hidden';
            el.innerText = 'Read More';
        }
    }

    function selectSpeciality(card, id) {
        // Remove selected class from all
        document.querySelectorAll('.speciality-card').forEach(c => c.classList.remove('selected'));
        
        // Add to clicked
        card.classList.add('selected');
        
        // Update input
        document.getElementById('specialityInput').value = id;
        
        // Enable Next Button
        document.getElementById('nextBtn').removeAttribute('disabled');
    }
    
    // Safety check just in case (Realtime validation on submit prevention)
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        if (!document.getElementById('specialityInput').value) {
            e.preventDefault();
            alert('Please select a speciality to proceed.');
        }
    });
</script>
