<?php
// views/patient/booking/step3_slots.php
// Expects: $doctor, $speciality, $slots (array of ['time'=>.., 'display'=>..]), $date

if (!function_exists('h')) { function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); } }
$routeBase = (defined('BASE_PATH') ? BASE_PATH : '') . '/index.php?route=patient/book/step3&speciality_id=' . $speciality['Speciality_Id'] . '&doctor_id=' . $doctor['Doctor_Id'];
?>

<?php $pageTitle = 'MediConnect - Select Slot';
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

    /* Slot Pills */
    .slot-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 15px; }
    .slot-pill {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 10px;
        text-align: center;
        font-weight: 500;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
        background: white;
        color: var(--text-dark);
    }
    .slot-pill:hover { border-color: #a5b4fc; background: #f5f7ff; }
    .slot-pill.selected { background: var(--primary-color); color: white; border-color: var(--primary-color); box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3); }
    
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
                <div class="step completed"><i class="bi bi-check"></i></div>
                <div class="mt-2 text-dark small fw-medium">Doctor</div>
            </div>
            
            <!-- Step 3 -->
            <div class="d-flex flex-column align-items-center position-relative" style="z-index: 1; width: 60px;">
                <div class="step active">3</div>
                <div class="mt-2 fw-bold text-dark small">Time</div>
            </div>
            
            <!-- Step 4 -->
            <div class="d-flex flex-column align-items-center position-relative" style="z-index: 1; width: 60px;">
                <div class="step">4</div>
                <div class="mt-2 text-muted small fw-medium">Confirm</div>
            </div>
        </div>
    </div>

    <div class="card-block">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 border-bottom pb-4">
            <div>
                <div class="badge bg-light text-primary border mb-2"><?= h($speciality['Speciality_Name']) ?></div>
                <?php 
                    $dName = $doctor['Name'];
                    $displayDr = (stripos(trim($dName), 'Dr.') === 0 || stripos(trim($dName), 'Dr ') === 0) ? '' : 'Dr. ';
                ?>
                <h4 class="fw-bold mb-1"><?= $displayDr . h($dName) ?></h4>
                <div class="text-muted small">Consultation Fee: <span class="fw-bold text-dark">₹<?= number_format((float)$speciality['Consultation_Fee'], 0) ?></span></div>
            </div>
            <div class="mt-3 mt-md-0">
                <label class="form-label small fw-bold text-muted mb-1">Select Date</label>
                <input type="date" id="dateSelector" class="form-control" 
                       value="<?= $date ?>" 
                       min="<?= date('Y-m-d') ?>" 
                       style="max-width: 180px;">
            </div>
        </div>
        
        <h5 class="fw-bold mb-3">Available Slots <span class="fw-normal text-muted fs-6">(<?= count($slots) ?>)</span></h5>
        
        <form id="bookingForm" action="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php" method="GET">
            <input type="hidden" name="route" value="patient/book/step4">
            <input type="hidden" name="speciality_id" value="<?= h($speciality['Speciality_Id']) ?>">
            <input type="hidden" name="doctor_id" value="<?= h($doctor['Doctor_Id']) ?>">
            <input type="hidden" name="date" value="<?= h($date) ?>">
            <input type="hidden" name="time" id="timeInput" required>
            
            <?php if (empty($slots)): ?>
                <div class="text-center py-5 bg-light rounded-3">
                    <div class="text-muted mb-2"><i class="bi bi-calendar-x fs-1"></i></div>
                    <p class="mb-0">No slots available on this date.</p>
                    <small class="text-muted">Try selecting a different date above.</small>
                </div>
            <?php else: ?>
                <div class="slot-grid mb-4">
                    <?php foreach ($slots as $slot): ?>
                        <div class="slot-pill" onclick="selectSlot(this, '<?= $slot['time'] ?>')">
                            <?= $slot['display'] ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-4">
                <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/book/step2&speciality_id=<?= (int)$speciality['Speciality_Id'] ?>" class="text-decoration-none text-muted fw-medium"><i class="bi bi-arrow-left me-1"></i> Back</a>
                <button type="submit" id="nextBtn" class="btn-next" disabled>
                    Proceed to Payment <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<div style="height: 40px;"></div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
    // Realtime Date Change
    const dateInput = document.getElementById('dateSelector');
    dateInput.addEventListener('change', function() {
        if(this.value) {
            window.location.href = '<?= $routeBase ?>&date=' + this.value;
        }
    });

    // Slot Selection
    function selectSlot(el, time) {
        // Deselect all
        document.querySelectorAll('.slot-pill').forEach(s => s.classList.remove('selected'));
        // Select clicked
        el.classList.add('selected');
        // Update Input
        document.getElementById('timeInput').value = time;
        // Enable Next
        document.getElementById('nextBtn').removeAttribute('disabled');
    }
    
    // Safety
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        if (!document.getElementById('timeInput').value) {
            e.preventDefault();
            alert('Please select a time slot.');
        }
    });
</script>
