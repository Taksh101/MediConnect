<?php
// views/patient/booking/step4_checkout.php
// Expects: $doctor, $speciality, $date, $time

if (!function_exists('h')) { function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); } }
$formattedDate = date('l, F j, Y', strtotime($date));
$formattedTime = date('h:i A', strtotime($date . ' ' . $time));
$fee = (float)$speciality['Consultation_Fee'];
$tax = $fee * 0.0; 
$total = $fee + $tax;
?>

<?php $pageTitle = 'MediConnect - Confirm Booking';
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
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.95rem; }
    .summary-total { border-top: 1px solid #e5e7eb; padding-top: 15px; margin-top: 15px; font-weight: 700; font-size: 1.1rem; }
    
    .btn-pay { background: var(--primary-color); color: white; border: none; padding: 14px 30px; border-radius: 8px; font-weight: 600; font-size: 1rem; width: 100%; margin-top: 20px; transition: all 0.2s; }
    .btn-pay:hover { background: #4338ca; transform: translateY(-1px); }

    /* Validation Styles */
    .is-invalid { border-color: #dc3545 !important; background-image: none !important; }
    .is-valid { border-color: #198754 !important; background-image: none !important; }
    .invalid-feedback { font-size: 0.8rem; margin-top: 4px; display: none; width: 100%; color: #dc3545; }
    .is-invalid ~ .invalid-feedback { display: block; }
    
    /* Input Group Icons Validation */
    .input-group-text.is-valid-icon { border-color: #198754; color: #198754; background: #f0fdf4; }
    .input-group-text.is-invalid-icon { border-color: #dc3545; color: #dc3545; background: #fef2f2; }
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
                <div class="step completed"><i class="bi bi-check"></i></div>
                <div class="mt-2 text-dark small fw-medium">Time</div>
            </div>
            
            <!-- Step 4 -->
            <div class="d-flex flex-column align-items-center position-relative" style="z-index: 1; width: 60px;">
                <div class="step active">4</div>
                <div class="mt-2 fw-bold text-dark small">Confirm</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Summary -->
        <div class="col-lg-5 order-lg-2">
            <div class="card-block bg-light border">
                <h5 class="fw-bold mb-4">Booking Summary</h5>
                <div class="d-flex align-items-center mb-4">
                    <div style="width:50px; height:50px; background:#e0e7ff; color:#4338ca; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; margin-right:15px;">
                        <?= strtoupper(substr($doctor['Name'], 0, 1)) ?>
                    </div>
                    <div>
                        <?php 
                            $dName = $doctor['Name'];
                            $displayDr = (stripos(trim($dName), 'Dr.') === 0 || stripos(trim($dName), 'Dr ') === 0) ? '' : 'Dr. ';
                        ?>
                        <div class="fw-bold"><?= $displayDr . h($dName) ?></div>
                        <div class="small text-muted"><?= h($speciality['Speciality_Name']) ?></div>
                    </div>
                </div>
                
                 <div class="summary-row">
                    <span class="text-muted">Date</span>
                    <span class="fw-medium"><?= $formattedDate ?></span>
                </div>
                <div class="summary-row">
                    <span class="text-muted">Time</span>
                    <span class="fw-medium"><?= $formattedTime ?></span>
                </div>
                <hr class="text-muted opacity-25">
                <div class="summary-row summary-total">
                    <span>Total Amount</span>
                    <span class="text-primary">₹<?= number_format($total, 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Checkout Form -->
        <div class="col-lg-7 order-lg-1">
            <div class="card-block">
                <h4 class="fw-bold mb-4">Details & Payment</h4>
                
                <form id="paymentForm" action="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/book/complete" method="POST" novalidate>
                    <input type="hidden" name="doctor_id" value="<?= h($doctor['Doctor_Id']) ?>">
                    <input type="hidden" name="date" value="<?= h($date) ?>">
                    <input type="hidden" name="time" value="<?= h($time) ?>">
                    <input type="hidden" name="amount" value="<?= $total ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Visit Type</label>
                        <select class="form-select" name="visit_type" required>
                            <option value="Online Consultation">Online Consultation</option>
                            <option value="Physical Visit">Physical Visit (In-Clinic)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Reason for Visit / Symptoms</label>
                        <textarea class="form-control" id="symptoms" name="symptoms" rows="2" placeholder="Briefly describe your problem..." required></textarea>
                        <div class="invalid-feedback">Description required (5-200 chars).</div>
                    </div>

                    <h6 class="fw-bold mb-3 border-bottom pb-2">Payment Method</h6>
                    
                    <div class="mb-4">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" id="payCard" value="Card" checked onchange="togglePayment('card')">
                            <label class="form-check-label fw-medium" for="payCard"><i class="bi bi-credit-card me-2"></i>Credit / Debit Card</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="payUPI" value="UPI" onchange="togglePayment('upi')">
                            <label class="form-check-label fw-medium" for="payUPI"><i class="bi bi-phone me-2"></i>UPI / VPA</label>
                        </div>
                    </div>
                    
                    <!-- Card Section -->
                    <div id="cardSection">
                        <div class="mb-3">
                            <label class="form-label">Card Number</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-white border-end-0" id="cardIcon"><i class="bi bi-credit-card"></i></span>
                                <input type="text" class="form-control border-start-0" id="cardNumber" name="card_number" placeholder="0000 0000 0000 0000" maxlength="19">
                                <div class="invalid-feedback">Invalid card number (16 digits).</div>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label">Expiry Date</label>
                                <input type="text" class="form-control" id="cardExpiry" name="card_expiry" placeholder="MM/YY" maxlength="5">
                                <div class="invalid-feedback" id="expiryFeedback">Invalid date.</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">CVV</label>
                                <input type="password" class="form-control" id="cardCvv" name="card_cvv" placeholder="123" maxlength="3">
                                <div class="invalid-feedback">Required (3 digits).</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Card Holder Name</label>
                            <input type="text" class="form-control" id="cardName" name="card_name" placeholder="Name on Card">
                            <div class="invalid-feedback">Alphabets only.</div>
                        </div>
                    </div>

                    <!-- UPI Section -->
                    <div id="upiSection" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">UPI ID / VPA</label>
                            <input type="text" class="form-control" id="upiId" placeholder="username@bank">
                            <div class="invalid-feedback">Invalid UPI ID format (e.g. user@ybl).</div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-pay shadow-sm" id="submitBtn">
                        Pay ₹<?= number_format($total, 0) ?> & Confirm
                    </button>
                    <div class="text-center mt-3 small text-muted"><i class="bi bi-lock-fill me-1"></i> Secure Simulated Transaction</div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
    function togglePayment(method) {
        if(method === 'card') {
            document.getElementById('cardSection').style.display = 'block';
            document.getElementById('upiSection').style.display = 'none';
        } else {
            document.getElementById('cardSection').style.display = 'none';
            document.getElementById('upiSection').style.display = 'block';
        }
        validateForm(); 
    }

    const cardInput = document.getElementById('cardNumber');
    const cardIcon = document.getElementById('cardIcon');
    const expiryInput = document.getElementById('cardExpiry');
    const nameInput = document.getElementById('cardName');
    const cvvInput = document.getElementById('cardCvv');
    const upiInput = document.getElementById('upiId');
    const symptomsInput = document.getElementById('symptoms');
    
    // Card Formatting & Icon Color
    cardInput.addEventListener('input', function(e) {
        let v = e.target.value.replace(/\D/g, '').substring(0,16);
        let m = v.match(/.{1,4}/g);
        e.target.value = m ? m.join(' ') : v;
        
        let isValid = v.length === 16;
        validateField(this, isValid);
        updateIconColor(cardIcon, isValid, this.value.length > 0);
    });
    
    function updateIconColor(icon, isValid, hasValue) {
        icon.classList.remove('is-valid-icon', 'is-invalid-icon');
        if(isValid) icon.classList.add('is-valid-icon');
        else if(hasValue) icon.classList.add('is-invalid-icon'); // Only red if user typed something
    }

    // Expiry Realtime
    expiryInput.addEventListener('input', function(e) {
        let v = e.target.value.replace(/\D/g, '').substring(0,4);
        if(v.length > 2) v = v.substring(0,2) + '/' + v.substring(2);
        e.target.value = v;
        
        if(v.length >= 3) { 
           validateExpiry(this); 
        } else {
            this.classList.remove('is-invalid', 'is-valid');
        }
    });

    nameInput.addEventListener('input', function(e) {
        let v = e.target.value.toUpperCase().replace(/[^A-Z\s]/g, '');
        e.target.value = v;
        validateField(this, v.length >= 2);
    });
    
    cvvInput.addEventListener('input', function(e){
        let v = e.target.value.replace(/\D/g, '').substring(0,3);
        e.target.value = v;
        validateField(this, v.length === 3);
    });

    symptomsInput.addEventListener('input', function(e){
        let len = e.target.value.trim().length;
        validateField(this, len >= 5 && len <= 200);
    });
    
    upiInput.addEventListener('input', function(e) {
        const regex = /^[a-zA-Z0-9.\-_]{2,49}@[a-zA-Z._]{2,49}$/;
        validateField(this, regex.test(e.target.value));
    });

    function validateField(el, condition) {
        if(condition) {
            el.classList.remove('is-invalid');
            el.classList.add('is-valid');
            return true;
        } else {
            el.classList.remove('is-valid');
            if(el.value.length > 0) el.classList.add('is-invalid');
            return false;
        }
    }
    
    function validateExpiry(el) {
        const val = el.value;
        if(val.length !== 5) {
             el.classList.remove('is-valid'); 
             if(val.length > 0) el.classList.add('is-invalid');
             return false;
        }
        const [mm, yy] = val.split('/').map(num => parseInt(num, 10));
        
        const now = new Date();
        const curM = now.getMonth() + 1;
        const curY = parseInt(now.getFullYear().toString().substring(2)); 
        
        let valid = false;
        let msg = "Invalid date";
        
        if(mm >= 1 && mm <= 12 && yy >= curY) {
            if(yy === curY) {
                if(mm >= curM) valid = true;
                else msg = "Card expired";
            } else {
                valid = true;
            }
        } else {
            if(mm < 1 || mm > 12) msg = "Invalid month";
            else if(yy < curY) msg = "Card expired";
        }
        
        const fb = document.getElementById('expiryFeedback');
        if(fb) fb.innerText = msg;
        
        if(valid) {
            el.classList.remove('is-invalid'); el.classList.add('is-valid');
            return true;
        } else {
            el.classList.remove('is-valid'); el.classList.add('is-invalid');
            return false;
        }
    }

    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        let valid = true;
        const method = document.querySelector('input[name="payment_method"]:checked').value;
        
        if(!validateField(symptomsInput, symptomsInput.value.trim().length >= 5)) valid = false;
        
        if(method === 'Card') {
            if(!validateField(cardInput, cardInput.value.replace(/\s/g,'').length === 16)) valid = false;
            updateIconColor(cardIcon, cardInput.value.replace(/\s/g,'').length === 16, true);
            
            if(!validateExpiry(expiryInput)) valid = false;
            if(!validateField(nameInput, nameInput.value.trim().length >= 2)) valid = false;
            if(!validateField(cvvInput, cvvInput.value.length === 3)) valid = false;
        } else {
            const upiRegex = /^[a-zA-Z0-9.\-_]{2,49}@[a-zA-Z._]{2,49}$/;
            if(!validateField(upiInput, upiRegex.test(upiInput.value))) valid = false;
        }
        
        if(!valid) {
            e.preventDefault();
        }
    });
</script>
