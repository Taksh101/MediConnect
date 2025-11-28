<?php
// MediConnect/views/auth/register.php
require_once __DIR__ . '/../../config/csrf.php';
$csrf = csrf_token();
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Register - MediConnect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #0b63ff;
            --muted: #6b7280;
            --danger: #d9534f;
            --radius: 14px;
        }

        body {
            background: #f5f7fb;
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial;
        }

        .wrap {
    max-width: 650px;
    margin: 0 auto;
    padding: 40px 12px;
    min-height: 100vh;
    display: grid;
    place-items: center;
}


        .card {
            border: 0;
            border-radius: var(--radius);
            background: #fff;
            padding: 32px;
            box-shadow:
                0 4px 12px rgba(0, 0, 0, 0.05),
                0 12px 28px rgba(0, 0, 0, 0.08);
        }

        .heading-box {
            margin-bottom: 28px;
            padding-bottom: 12px;
            /* border-bottom: 2px solid #e5e7eb; */
        }

        .heading-box h2 {
            text-align: center;
            margin: 0;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: #1f2937;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
            font-size: 15px;
        }

        .form-control,
        .form-select,
        textarea {
            border-radius: var(--radius);
            padding: 10px 12px;
            border: 1px solid #d1d5db;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(11, 99, 255, 0.15);
        }

        .input-group-text {
            background: #fff;
            border-radius: var(--radius);
            border-right: 0;
        }

        .btn-primary {
            background: var(--primary);
            border-radius: var(--radius);
            padding: 12px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 6px 18px rgba(11, 99, 255, 0.25);
        }

        .btn-primary:hover {
            background: #084ccd;
        }

        .invalid-feedback {
            font-size: 13px;
            margin-top: 3px;
        }

        @media (max-width: 575px) {
            .wrap {
                margin: 25px 12px;
            }

            .card {
                padding: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="card">
            <div class="px-4 py-2">
                <div class="heading-box">
                    <h2>Register</h2>
                </div>

                <div class="card-body-custom">


                    <!-- form action="#" prevents accidental native navigation; JS will handle AJAX to proper route -->
                    <form id="registerForm" data-validate="auth"
                        action="/MediConnect/index.php?route=auth/register-action" method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                        <!-- Full Name -->
                        <div class="mb-3">
                            <label for="id-name" class="form-label">Full Name</label>
                            <input id="id-name" name="name" type="text" class="form-control"
                                placeholder="Enter your full name" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="id-email" class="form-label" id="id-label-email">Email</label>
                            <div class="input-group">
                                <span class="input-group-text input-icon" aria-hidden="true">
                                    <!-- outlined envelope icon -->
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                        <rect x="3" y="5" width="18" height="14" rx="2" stroke="#374151"
                                            stroke-width="1" />
                                        <path d="M3 7l9 6 9-6" stroke="#374151" stroke-width="1" />
                                    </svg>
                                </span>
                                <input id="id-email" name="email" type="email" class="form-control"
                                    placeholder="you@example.com" required>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Passwords -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label for="id-password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input id="id-password" name="password" type="password" class="form-control"
                                        placeholder="Create password" required aria-describedby="togglePassword">
                                    <button type="button" id="togglePassword" class="input-group-text btn-icon"
                                        aria-label="Toggle password">
                                        <!-- eye icon (initial) -->
                                        <svg id="togglePasswordIcon" width="16" height="16" viewBox="0 0 24 24"
                                            fill="none">
                                            <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" stroke="#374151"
                                                stroke-width="1" />
                                            <circle cx="12" cy="12" r="3" stroke="#374151" stroke-width="1" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="id-confirm" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <input id="id-confirm" name="confirm_password" type="password" class="form-control"
                                        placeholder="Confirm password" required aria-describedby="toggleConfirm">
                                    <button type="button" id="toggleConfirm" class="input-group-text btn-icon"
                                        aria-label="Toggle confirm password">
                                        <svg id="toggleConfirmIcon" width="16" height="16" viewBox="0 0 24 24"
                                            fill="none">
                                            <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" stroke="#374151"
                                                stroke-width="1" />
                                            <circle cx="12" cy="12" r="3" stroke="#374151" stroke-width="1" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label for="id-phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text input-icon" aria-hidden="true">
                                    <!-- phone icon -->
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                        <path
                                            d="M22 16.92V21a1 1 0 0 1-1.11 1 19 19 0 0 1-8.63-3.07 19 19 0 0 1-6-6A19 19 0 0 1 2 3.11 1 1 0 0 1 3 2h4.09a1 1 0 0 1 1 .75 12 12 0 0 0 .7 2.81 1 1 0 0 1-.24 1.02L7.91 8.09a14 14 0 0 0 6 6l1.51-1.51a1 1 0 0 1 1.02-.24 12 12 0 0 0 2.81.7 1 1 0 0 1 .75 1V22z"
                                            stroke="#374151" stroke-width="1" />
                                    </svg>
                                </span>
                                <input id="id-phone" name="phone" type="tel" class="form-control" maxlength="10"
                                    inputmode="numeric" placeholder="10-digit phone" required>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Gender + DOB -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label for="id-gender" class="form-label">Gender</label>
                                <select id="id-gender" name="gender" class="form-select" required>
                                    <option value="">Choose...</option>
                                    <option value="MALE">Male</option>
                                    <option value="FEMALE">Female</option>
                                    <option value="OTHER">Other</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="id-dob" class="form-label">Date of Birth</label>
                                <input id="id-dob" name="dob" type="date" class="form-control" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="mb-3">
                            <label for="id-address" class="form-label">Address (Optional)</label>
                            <textarea id="id-address" name="address" class="form-control" rows="2" maxlength="255"
                                placeholder="Street, City, State"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="d-grid mb-2">
                            <button id="submitBtn" type="submit" class="btn btn-primary">Register</button>
                        </div>

                        <div class="text-center">
                            <small class="text-muted">Already have an account? <a
                                    href="/MediConnect/index.php?route=auth/login">
                                    Login</a></small>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

        <!-- INLINE JS: validation, password toggle, numeric enforcement, AJAX submit -->
        <script>
            (function () {
                const ROUTE = (r) => window.location.origin + '/MediConnect/index.php?route=' + r;

                const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
                const NAME_RE = /^[A-Za-z ]{3,30}$/;
                const PHONE_RE = /^[6-9]\d{9}$/;
                const PASS_RE = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#^._-]).{8,50}$/;

                const ICONS = {
                    eye: `
      <svg width="18" height="18" viewBox="0 0 16 16" fill="none">
        <path d="M1 8C1 8 3.5 3.5 8 3.5C12.5 3.5 15 8 15 8C15 8 12.5 12.5 8 12.5C3.5 12.5 1 8 1 8Z"
              stroke="#374151" stroke-width="1.4"/>
        <circle cx="8" cy="8" r="2.3" stroke="#374151" stroke-width="1.4"/>
      </svg>
    `,
                    eyeSlash: `
      <svg width="18" height="18" viewBox="0 0 16 16" fill="none">
        <path d="M3 3L13 13" stroke="#374151" stroke-width="1.4" />
        <path d="M1 8C1 8 3.5 3.5 8 3.5C10 3.5 11.8 4.3 13 5.3"
              stroke="#374151" stroke-width="1.4"/>
        <path d="M8 12.5C5.7 12.5 3.8 11.3 2.5 10"
              stroke="#374151" stroke-width="1.4" />
      </svg>
    `
                };

                function $(s, root = document) { return root.querySelector(s); }
                function $$(s, root = document) { return Array.from(root.querySelectorAll(s)); }

                function findFeedback(input) {
                    if (!input) return null;

                    if (input.nextElementSibling?.classList.contains('invalid-feedback'))
                        return input.nextElementSibling;

                    const parent = input.parentElement;
                    if (parent?.nextElementSibling?.classList.contains('invalid-feedback'))
                        return parent.nextElementSibling;

                    const cont = input.closest('.mb-3') || input.closest('.row') || input.closest('form');
                    return cont ? cont.querySelector('.invalid-feedback') : null;
                }

                function setError(input, msg) {
                    input.classList.add('is-invalid');
                    const fb = findFeedback(input);
                    if (fb) fb.textContent = msg;
                }

                function clearError(input) {
                    input.classList.remove('is-invalid');
                    const fb = findFeedback(input);
                    if (fb) fb.textContent = '';
                }

                function validateOne(input, force = false) {
                    const val = input.value.trim();
                    const touched = force || input.dataset.touched === '1';

                    if (!touched) return true;

                    if (input.hasAttribute('required') && !val) {
                        setError(input, "This field is required.");
                        return false;
                    }

                    switch (input.name) {
                        case "name":
                            if (!NAME_RE.test(val)) { setError(input, "Must be 3–30 letters (A–Z)."); return false; }
                            break;

                        case "email":
                            if (!EMAIL_RE.test(val)) { setError(input, "Enter a valid email."); return false; }
                            break;

                        case "password":
                            if (!PASS_RE.test(val)) {
                                setError(input, "Password must be 8–50 characters long, include uppercase, lowercase, number & symbol.");
                                return false;
                            }
                            break;

                        case "confirm_password":
                            const pass = input.form.querySelector('input[name="password"]');
                            if (pass.value !== val) { setError(input, "Passwords do not match."); return false; }
                            break;

                        case "phone":
                            if (!PHONE_RE.test(val)) { setError(input, "Must start with 6–9 and be 10 digits."); return false; }
                            break;

                        case "dob":
                            if (val) {
                                const d = new Date(val + "T00:00:00");
                                const now = new Date(); now.setHours(0, 0, 0, 0);
                                if (isNaN(d.getTime()) || d > now) {
                                    setError(input, "Enter a valid past date.");
                                    return false;
                                }
                            }
                            break;
                    }

                    clearError(input);
                    return true;
                }

                function attachForm(form) {
                    const phone = form.querySelector('input[name="phone"]');
                    if (phone) {
                        phone.addEventListener('input', () => phone.value = phone.value.replace(/\D/g, '').slice(0, 10));
                    }

                    // input + blur validation
                    $$("input,select,textarea", form).forEach(inp => {
                        inp.addEventListener('input', () => { inp.dataset.touched = '1'; validateOne(inp, true); });
                        inp.addEventListener('blur', () => { inp.dataset.touched = '1'; validateOne(inp, true); });
                    });

                    // toggle icons
                    const tp = $('#togglePassword'), tc = $('#toggleConfirm');
                    if (tp) {
                        tp.innerHTML = ICONS.eye;
                        tp.addEventListener('click', () => {
                            const p = $('input[name="password"]');
                            if (p.type === "password") { p.type = "text"; tp.innerHTML = ICONS.eyeSlash; }
                            else { p.type = "password"; tp.innerHTML = ICONS.eye; }
                        });
                    }
                    if (tc) {
                        tc.innerHTML = ICONS.eye;
                        tc.addEventListener('click', () => {
                            const c = $('input[name="confirm_password"]');
                            if (c.type === "password") { c.type = "text"; tc.innerHTML = ICONS.eyeSlash; }
                            else { c.type = "password"; tc.innerHTML = ICONS.eye; }
                        });
                    }

                    // submit
                    form.addEventListener('submit', async (e) => {
                        e.preventDefault();

                        let ok = true;
                        $$("input,select,textarea", form).forEach(inp => {
                            if (!validateOne(inp, true)) ok = false;
                        });
                        if (!ok) return;

                        // CHECK EMAIL EXISTENCE HERE ONLY (NOT WHILE TYPING)
                        const email = $('input[name="email"]').value.trim();
                        const emailInput = $('input[name="email"]');
                        try {
                            const chk = await fetch(ROUTE('auth/check-email'), {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ email })
                            });
                            const j = await chk.json();
                            if (j.ok && j.exists) {
                                setError(emailInput, "Email already exists.");
                                return;
                            }
                        } catch {
                            setError(emailInput, "Server error, try again.");
                            return;
                        }

                        // everything valid → submit to register-action
                        const fd = new FormData(form);
                        const data = {};
                        fd.forEach((v, k) => data[k] = v);

                        const btn = $('#submitBtn');
                        const old = btn.textContent;
                        btn.disabled = true;
                        btn.textContent = "Registering...";

                        try {
                            const res = await fetch(ROUTE('auth/register-action'), {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                                body: JSON.stringify(data)
                            });
                            const txt = await res.text();
                            let j = null;
                            try { j = JSON.parse(txt); } catch { }

                            if (j?.ok) {
                                if (j.redirect) location.href = j.redirect;
                                else location.reload();
                            } else if (j?.errors) {
                                Object.keys(j.errors).forEach(f => {
                                    const el = form.querySelector(`[name="${f}"]`);
                                    if (el) setError(el, j.errors[f]);
                                });
                            } else {
                                alert("Registration failed");
                            }
                        } catch {
                            alert("Network/server error");
                        } finally {
                            btn.disabled = false;
                            btn.textContent = old;
                        }
                    });
                }

                document.addEventListener('DOMContentLoaded', () => {
                    document.querySelectorAll('form[data-validate="auth"]').forEach(attachForm);
                });
            })();
        </script>
</body>

</html>