<?php
// views/patient/booking/success.php
// Expects: $apptId

if (!function_exists('h')) { function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); } }
?>

<?php $pageTitle = 'MediConnect - Booking Confirmed';
include __DIR__ . '/../../includes/patientNavbar.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root {
        --primary-color: #4f46e5;
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
        max-width: 600px;
        margin-top: 60px;
        margin-bottom: 40px;
        text-align: center;
    }
    
    .success-card { background: white; border-radius: 16px; padding: 50px 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .success-icon { 
        width: 80px; height: 80px; background: #dcfce7; color: #16a34a; 
        border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; 
        font-size: 3rem; margin-bottom: 24px; animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
    }
    
    @keyframes popIn {
        0% { transform: scale(0); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    
    .btn-dashboard { background: var(--primary-color); color: white; border: none; padding: 12px 30px; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-block; transition: all 0.2s; }
    .btn-dashboard:hover { background: #4338ca; color: white; transform: translateY(-1px); }
</style>

<div class="container container-wide pb-4">
    <div class="success-card">
        <div class="success-icon">
            <i class="bi bi-check-lg"></i>
        </div>
        <h2 class="fw-bold mb-2">Booking Confirmed!</h2>
        <p class="text-muted mb-4">Your appointment has been successfully scheduled. <br>The doctor will review your request shortly.</p>
        
        <div class="bg-light p-3 rounded mb-4 d-inline-block text-center" style="min-width: 250px;">
            <div class="small text-muted text-uppercase fw-bold mb-1">Booking Reference</div>
            <div class="font-monospace fs-5">#<?= h($apptId) ?></div>
        </div>
        
        <div>
            <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/dashboard" class="btn-dashboard">Go to Dashboard</a>
            <div class="mt-3">
                <a href="<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/index.php?route=patient/appointments" class="text-muted small text-decoration-none">View My Appointments</a>
            </div>
        </div>
    </div>
</div>

<div style="height: 40px;"></div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
