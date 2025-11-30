<?php
require_once __DIR__ . '/../../config/csrf.php';
$csrf = csrf_token();
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Login - MediConnect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #0b63ff;
            --radius: 14px;
        }

        body {
            background: #f5f7fb;
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial;
        }

        .wrap {
            max-width: 650px;
            margin: 60px auto;
        }

        .card {
            border: 0;
            border-radius: var(--radius);
            background: #fff;
            padding: 32px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05),
                        0 12px 28px rgba(0, 0, 0, 0.08);
        }

        .heading-box {
            margin-bottom: 28px;
            padding-bottom: 12px;
        }

        .heading-box h2 {
            text-align: center;
            margin: 0;
            font-size: 32px;
            font-weight: 700;
            color: #1f2937;
        }

        .form-label {
            font-weight: 600;
        }

        .form-control,
        .form-select {
            border-radius: var(--radius);
        }

        .invalid-feedback {
            font-size: 13px;
            display: block !important;
        }

        .btn-primary {
            background: var(--primary);
            border-radius: var(--radius);
            padding: 12px;
            font-weight: 600;
            font-size: 16px;
        }
    </style>
</head>

<body>

<div class="wrap">
    <div class="card">

        <div class="heading-box">
            <h2>Login</h2>
        </div>

        <form id="loginForm" data-validate="auth"
              action="/MediConnect/index.php?route=auth/login-action"
              method="POST" novalidate>

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

            
            <!-- Email -->
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input id="emailInput" type="email" name="email" class="form-control"
              placeholder="you@example.com" required>
                <div class="invalid-feedback"></div>
            </div>
            
            <!-- Password -->
            <div class="mb-3">
  <label class="form-label">Password</label>
  <div class="input-group">
      <input id="passwordInput" type="password" name="password" class="form-control"
             placeholder="Enter password" required>
      <button type="button" id="togglePassword" class="input-group-text btn-icon">
        <!-- default eye icon -->
        <svg width="18" height="18" viewBox="0 0 16 16" fill="none">
          <path d="M1 8C1 8 3.5 3.5 8 3.5C12.5 3.5 15 8 15 8C15 8 12.5 12.5 8 12.5C3.5 12.5 1 8 1 8Z"
                stroke="#374151" stroke-width="1.4"/>
          <circle cx="8" cy="8" r="2.3" stroke="#374151" stroke-width="1.4"/>
        </svg>
      </button>
  </div>
  <div class="invalid-feedback"></div>
</div>

            <!-- Role -->
            <div class="mb-3">
                <label class="form-label">Select Role</label>
                <select name="role" class="form-select" required>
                    <option value="">Choose...</option>
                    <option value="PATIENT">Patient</option>
                    <option value="DOCTOR">Doctor</option>
                    <option value="ADMIN">Admin</option>
                </select>
                <div class="invalid-feedback"></div>
            </div>
            
            <div class="d-grid mb-3">
                <button class="btn btn-primary" id="loginBtn" type="submit">Login</button>
            </div>

            <div class="text-center">
                <small class="text-muted">Don’t have an account? 
                    <a href="/MediConnect/index.php?route=auth/register">Register</a>
                </small>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

// SVG icons
const ICON_EYE = `
<svg width="18" height="18" viewBox="0 0 16 16" fill="none">
  <path d="M1 8C1 8 3.5 3.5 8 3.5C12.5 3.5 15 8 15 8C15 8 12.5 12.5 8 12.5C3.5 12.5 1 8 1 8Z"
        stroke="#374151" stroke-width="1.4"/>
  <circle cx="8" cy="8" r="2.3" stroke="#374151" stroke-width="1.4"/>
</svg>
`;

const ICON_EYE_SLASH = `
<svg width="18" height="18" viewBox="0 0 16 16" fill="none">
  <path d="M3 3L13 13" stroke="#374151" stroke-width="1.4"/>
  <path d="M1 8C1 8 3.5 3.5 8 3.5C10 3.5 11.8 4.3 13 5.3"
        stroke="#374151" stroke-width="1.4"/>
  <path d="M8 12.5C5.7 12.5 3.8 11.3 2.5 10"
        stroke="#374151" stroke-width="1.4"/>
</svg>
`;

function setErr(input, msg) {
    // ALWAYS find the .invalid-feedback inside same .mb-3 block
    const block = input.closest('.mb-3');
    const fb = block ? block.querySelector('.invalid-feedback') : null;

    input.classList.add("is-invalid");
    if (fb) fb.textContent = msg;
}

function clearErr(input) {
    const block = input.closest('.mb-3');
    const fb = block ? block.querySelector('.invalid-feedback') : null;

    input.classList.remove("is-invalid");
    if (fb) fb.textContent = "";
}




document.addEventListener("DOMContentLoaded", () => {

    const form = document.getElementById("loginForm");
    const email = document.getElementById("emailInput");
    const password = document.getElementById("passwordInput");
    const role = form.querySelector("select[name='role']");
    const btn = document.getElementById("loginBtn");
    const toggle = document.getElementById("togglePassword");

    // ---- Password toggle ----
    if (toggle) {
        toggle.addEventListener("click", () => {
            if (password.type === "password") {
                password.type = "text";
                toggle.innerHTML = ICON_EYE_SLASH;
            } else {
                password.type = "password";
                toggle.innerHTML = ICON_EYE;
            }
        });
    }

    // ---- Email live validation ----
    email.addEventListener("input", () => {
        if (!EMAIL_RE.test(email.value.trim())) {
            setErr(email, "Invalid Email ID");
        } else clearErr(email);
    });

    // ---- Submit handler ----
    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        let ok = true;

        if (!EMAIL_RE.test(email.value.trim())) {
            setErr(email, "Invalid Email ID");
            ok = false;
        } else clearErr(email);

        if (!password.value.trim()) {
            setErr(password, "Password is required");
            ok = false;
        } else clearErr(password);

        if (!role.value.trim()) {
            setErr(role, "Please select a role");
            ok = false;
        } else clearErr(role);

        if (!ok) return;

        btn.disabled = true;
        btn.textContent = "Logging in...";

        // Prepare data
        const fd = new FormData(form);
        const payload = {};
        fd.forEach((v, k) => payload[k] = v);

        // AJAX request
        const res = await fetch(form.action, {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify(payload)
        });

        const json = await res.json();

        if (json.ok) {
            window.location.href = json.redirect;
            return;
        }

        // Show backend errors correctly
        if (json.errors) {
            if (json.errors.email) setErr(email, json.errors.email);
            if (json.errors.password) setErr(password, json.errors.password);
        }

        btn.disabled = false;
        btn.textContent = "Login";
    });
});
</script>





</body>
</html>
