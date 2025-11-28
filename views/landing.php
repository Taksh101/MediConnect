<?php require_once __DIR__ . '/includes/header.php'; ?>

<section class="hero-image-hero d-flex align-items-center">
  <div class="hero-overlay"></div>

  <div class="container text-center hero-content">
    <h1 class="hero-title">MediConnect</h1>

    <p class="hero-sub">
      Hospital appointment management made easy. <br> Book, track and manage your medical visits all in one place.
    </p>

    <div class="d-flex justify-content-center gap-3 mt-3 flex-wrap">
      <a href="/MediConnect/views/auth/register.php" class="btn btn-hero-primary">Get Started</a>
      <a href="/MediConnect/views/auth/login.php" class="btn btn-hero-outline">Login</a>
    </div>
  </div>
</section>




<section class="features py-4">
  <div class="container">
    <h2 class="features-title text-center">Features</h2>
    <br>
    <div class="row g-4">

      <!-- CARD 1 -->
      <div class="col-6 col-md-3">
        <div class="feature-card text-center p-4">
          <i class="bi bi-calendar-check-fill feature-icon"></i>
          <h6 class="mt-3 fw-semibold">Easy Appointment</h6>
          <p class="small text-muted mb-0">
            Book a doctor visit in <br> a few simple steps.
          </p>
        </div>
      </div>

      <!-- CARD 2 -->
      <div class="col-6 col-md-3">
        <div class="feature-card text-center p-4">
          <i class="bi bi-clock-history feature-icon"></i>
          <h6 class="mt-3 fw-semibold">Live Availability</h6>
          <p class="small text-muted mb-0">
            View doctor timings <br> and available slots.
          </p>
        </div>
      </div>

      <!-- CARD 3 -->
      <div class="col-6 col-md-3">
  <div class="feature-card text-center p-4">
    <i class="bi bi-list-check feature-icon"></i>
    <h6 class="mt-3 fw-semibold">Appointment Tracking</h6>
    <p class="small text-muted mb-0">
      Monitor your appointment <br> progress in one place.
    </p>
  </div>
</div>


      <!-- CARD 4 -->
      <div class="col-6 col-md-3">
        <div class="feature-card text-center p-4">
          <i class="bi bi-journal-medical feature-icon"></i>
          <h6 class="mt-3 fw-semibold">Visit Records</h6>
          <p class="small text-muted mb-0">
            Access past visits and <br> doctor consultation notes.
          </p>
        </div>
      </div>

    </div>
  </div>
</section>



</body>
</html>
