<?php
// MediConnect/views/auth/register.php
require_once __DIR__ . '/../../config/auth.php';
require_guest();
require_once __DIR__ . '/../../config/csrf.php';
$csrf = csrf_token();
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>MediConnect - Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png">
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
            display: block;
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
                                        placeholder="Create Password" required aria-describedby="togglePassword">
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
                                        placeholder="Confirm Password" required aria-describedby="toggleConfirm">
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
                                    inputmode="numeric" placeholder="10 Digit Phone Number" required>
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

        <script src="<?= htmlspecialchars(BASE_PATH . '/assets/js/register.js') ?>"></script>
</body>

</html>