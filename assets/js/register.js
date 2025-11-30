// MediConnect/assets/js/register.js
// Clean, robust validation + password toggle + AJAX submit for registration form
(function () {
  // Route helper (relative) — keeps routing simple and portable
  const ROUTE = (r) => 'index.php?route=' + r;

  // Regexes / rules
  const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
  const NAME_RE  = /^[A-Za-z ]{3,30}$/;
  const PHONE_RE = /^[6-9]\d{9}$/;
  const PASS_RE  = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#^._-]).{8,50}$/;

  // Small SVG icons (kept consistent with register UI)
  const ICON_EYE = `
    <svg width="18" height="18" viewBox="0 0 16 16" fill="none">
      <path d="M1 8C1 8 3.5 3.5 8 3.5C12.5 3.5 15 8 15 8C15 8 12.5 12.5 8 12.5C3.5 12.5 1 8 1 8Z"
            stroke="#374151" stroke-width="1.4"/>
      <circle cx="8" cy="8" r="2.3" stroke="#374151" stroke-width="1.4"/>
    </svg>`;
  const ICON_EYE_SLASH = `
    <svg width="18" height="18" viewBox="0 0 16 16" fill="none">
      <path d="M3 3L13 13" stroke="#374151" stroke-width="1.4"/>
      <path d="M1 8C1 8 3.5 3.5 8 3.5C10 3.5 11.8 4.3 13 5.3"
            stroke="#374151" stroke-width="1.4"/>
      <path d="M8 12.5C5.7 12.5 3.8 11.3 2.5 10"
            stroke="#374151" stroke-width="1.4"/>
    </svg>`;

  // DOM helpers
  function $(sel, root = document) { return root.querySelector(sel); }
  function $$(sel, root = document) { return Array.from((root || document).querySelectorAll(sel)); }

  // Ensure every control has a proper .invalid-feedback and aria-describedby
 // ---------- FEEDBACK / ERROR HELPERS (REPLACE OLD ONES) --------------
// REPLACE the assignFeedbackIds function with this (keeps everything else intact)
function assignFeedbackIds(form) {
  if (!form) return;
  const controls = form.querySelectorAll('input,select,textarea');
  controls.forEach((ctrl) => {
    // skip hidden controls and CSRF field explicitly
    const t = (ctrl.getAttribute('type') || '').toLowerCase();
    if (t === 'hidden' || ctrl.name === 'csrf_token') return;

    if (!ctrl.name && !ctrl.id) return;

    // if already wired to a valid invalid-feedback via aria-describedby, keep it
    const existingDesc = ctrl.getAttribute('aria-describedby');
    if (existingDesc) {
      const el = document.getElementById(existingDesc);
      if (el && el.classList.contains('invalid-feedback')) return;
    }

    // find the nearest block for feedback
    const block = ctrl.closest('.mb-3') || ctrl.closest('.col-md-6') || ctrl.closest('.row') || form;
    if (!block) return;

    // Prefer specific sibling feedback nodes (input-group case or direct sibling)
    let fb = null;
    if (ctrl.parentElement && ctrl.parentElement.classList.contains('input-group') &&
        ctrl.parentElement.nextElementSibling && ctrl.parentElement.nextElementSibling.classList.contains('invalid-feedback')) {
      fb = ctrl.parentElement.nextElementSibling;
    } else if (ctrl.nextElementSibling && ctrl.nextElementSibling.classList.contains('invalid-feedback')) {
      fb = ctrl.nextElementSibling;
    } else {
      // fallback: any .invalid-feedback inside same block
      fb = block.querySelector('.invalid-feedback');
    }

    // create if missing
    if (!fb) {
      fb = document.createElement('div');
      fb.className = 'invalid-feedback';
      if (ctrl.parentElement && ctrl.parentElement.classList.contains('input-group')) {
        ctrl.parentElement.insertAdjacentElement('afterend', fb);
      } else if (ctrl.nextElementSibling) {
        ctrl.parentElement.insertBefore(fb, ctrl.nextElementSibling);
      } else {
        block.appendChild(fb);
      }
    }

    // ensure unique id and set aria-describedby
    if (!fb.id) {
      const base = (ctrl.id || ctrl.name || 'ctrl').replace(/\s+/g,'_');
      let candidate = base + '-fb';
      let i = 0;
      while (document.getElementById(candidate)) { i++; candidate = `${base}-fb-${i}`; }
      fb.id = candidate;
    }
    ctrl.setAttribute('aria-describedby', fb.id);
  });
}

function findFeedback(input) {
  if (!input) return null;
  // 1) aria-describedby
  const desc = input.getAttribute('aria-describedby');
  if (desc) {
    const el = document.getElementById(desc);
    if (el && el.classList.contains('invalid-feedback')) return el;
  }
  // 2) sibling (direct)
  if (input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback')) return input.nextElementSibling;
  // 3) input-group next sibling
  const parent = input.parentElement;
  if (parent && parent.nextElementSibling && parent.nextElementSibling.classList.contains('invalid-feedback')) return parent.nextElementSibling;
  // 4) nearest .mb-3 block
  const block = input.closest('.mb-3') || input.closest('.col-md-6') || input.closest('form');
  if (block) {
    const fb = block.querySelector('.invalid-feedback');
    if (fb) return fb;
  }
  return null;
}

function setError(input, msg) {
  if (!input) return;
  // add invalid class on control (and input-group children)
  input.classList.add('is-invalid');
  const ig = input.closest('.input-group');
  if (ig) ig.querySelectorAll('.form-control').forEach(el => el.classList.add('is-invalid'));

  // find feedback node and set text
  const fb = findFeedback(input);
  if (fb) {
    fb.textContent = msg || 'Invalid value';
    input.setAttribute('aria-invalid', 'true');
    fb.setAttribute('role','alert');
  } else {
    // fallback: create a visible message after the control
    const tmp = document.createElement('div');
    tmp.className = 'invalid-feedback';
    tmp.textContent = msg || 'Invalid value';
    if (input.parentElement && input.parentElement.classList.contains('input-group')) {
      input.parentElement.insertAdjacentElement('afterend', tmp);
    } else {
      input.insertAdjacentElement('afterend', tmp);
    }
    // ensure aria-describedby
    if (!tmp.id) {
      tmp.id = (input.id || input.name || 'tmp') + '-fb-temp';
    }
    input.setAttribute('aria-describedby', tmp.id);
    input.setAttribute('aria-invalid', 'true');
  }
}

function clearError(input) {
  if (!input) return;
  input.classList.remove('is-invalid');
  const ig = input.closest('.input-group');
  if (ig) ig.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));

  const fb = findFeedback(input);
  if (fb) {
    fb.textContent = '';
    fb.removeAttribute('role');
  }
  input.removeAttribute('aria-invalid');
}

function safeClear(input) {
  // allow clearing immediately when field becomes valid
  clearError(input);
}

  // numeric-only enforcement utility
  function enforceDigits(el, maxLen) {
    if (!el) return;
    const sanitize = () => { el.value = el.value.replace(/\D+/g, '').slice(0, maxLen || 99); };
    const keyguard = (e) => {
      const allowed = ['Backspace','Delete','ArrowLeft','ArrowRight','Home','End','Tab','Enter'];
      if (allowed.includes(e.key)) return;
      if (!/\d/.test(e.key)) e.preventDefault();
    };
    el.addEventListener('keydown', keyguard);
    el.addEventListener('input', sanitize);
    el.addEventListener('paste', (e) => {
      e.preventDefault();
      const t = (e.clipboardData || window.clipboardData).getData('text');
      el.value = (el.value + t).replace(/\D+/g, '').slice(0, maxLen || 99);
      el.dispatchEvent(new Event('input'));
    });
  }

  // Debounce helper
  function debounce(fn, ms) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(()=>fn(...args), ms); };
  }

  // Per-field validation logic
  function validateOne(input, force = false) {
    if (!input) return true;
    const val = (input.value || '').trim();
    const touched = force || input.dataset.touched === '1';
    if (!touched) return true;

    // required check first
    if (input.hasAttribute('required') && !val) {
      setError(input, 'This field is required.');
      return false;
    }

    switch (input.name) {
      case 'name':
        if (!NAME_RE.test(val)) { setError(input, 'Must be 3–30 letters (A–Z).'); return false; }
        break;

      case 'email':
        if (!EMAIL_RE.test(val)) { setError(input, 'Enter a valid email.'); return false; }
        break;

      case 'password':
        if (!PASS_RE.test(val)) { setError(input, 'Password must be 8–50 chars with upper, lower, number & symbol.'); return false; }
        break;

      case 'confirm_password':
        const pass = input.form ? input.form.querySelector('input[name="password"]') : null;
        if (pass && pass.value !== val) { setError(input, 'Passwords do not match.'); return false; }
        break;

      case 'phone':
        if (!PHONE_RE.test(val)) { setError(input, 'Must start with 6–9 and be 10 digits.'); return false; }
        break;

      case 'dob':
        if (val) {
          const d = new Date(val + 'T00:00:00');
          const today = new Date(); today.setHours(0,0,0,0);
          if (isNaN(d.getTime()) || d > today) { setError(input, 'Enter a valid past date.'); return false; }
        }
        break;
    }

    // If this input already had an error and now passes the checks, clear it.
    // We use safeClear so we don't overwrite an error set elsewhere unexpectedly.
    safeClear(input);
    return true;
  }

  // Attach behavior to the form
  function attachForm(form) {
    if (!form) return;

    // make sure feedback nodes exist and aria wired
    assignFeedbackIds(form);

    // enforce digits on phone input
    const phone = form.querySelector('input[name="phone"]');
    if (phone) enforceDigits(phone, 10);

    // input & blur handlers: set touched & validate
    $$( 'input, select, textarea', form ).forEach((inp) => {
      inp.addEventListener('input', () => {
        if (!inp.dataset.touched) inp.dataset.touched = '1';
        validateOne(inp, true);
      });
      inp.addEventListener('blur', () => {
        inp.dataset.touched = '1';
        validateOne(inp, true);
      });
    });

    // password toggle wiring (works for both password & confirm fields)
    const tp = form.querySelector('#togglePassword');
    const tc = form.querySelector('#toggleConfirm');
    if (tp) { tp.innerHTML = ICON_EYE; tp.addEventListener('click', () => {
      const p = form.querySelector('input[name="password"]'); if (!p) return;
      if (p.type === 'password') { p.type = 'text'; tp.innerHTML = ICON_EYE_SLASH; }
      else { p.type = 'password'; tp.innerHTML = ICON_EYE; }
      p.focus();
    }); }
    if (tc) { tc.innerHTML = ICON_EYE; tc.addEventListener('click', () => {
      const p = form.querySelector('input[name="confirm_password"]'); if (!p) return;
      if (p.type === 'password') { p.type = 'text'; tc.innerHTML = ICON_EYE_SLASH; }
      else { p.type = 'password'; tc.innerHTML = ICON_EYE; }
      p.focus();
    }); }

    // submit handler
    form.addEventListener('submit', async (ev) => {
      ev.preventDefault();

      // full validation pass
      let ok = true;
      $$( 'input, select, textarea', form ).forEach((inp) => {
        // skip optional empty fields
        if (!inp.hasAttribute('required') && !(inp.value||'').trim()) { safeClear(inp); return; }
        if (!validateOne(inp, true)) ok = false;
      });
      if (!ok) return;

      // only now check duplicate email on server
      const emailInput = form.querySelector('input[name="email"]');
      if (emailInput && EMAIL_RE.test((emailInput.value||'').trim())) {
        try {
          const res = await fetch(ROUTE('auth/check-email'), {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ email: (emailInput.value||'').trim() })
          });
          const j = await res.json();
          if (j && j.ok && j.exists) {
            setError(emailInput, 'Email already exists.');
            return;
          }
        } catch (err) {
          setError(emailInput, 'Server error while checking email.');
          return;
        }
      }

      // gather form data and submit to register action
      const fd = new FormData(form);
      const payload = {};
      fd.forEach((v,k) => payload[k]=v);

      const submitBtn = form.querySelector('button[type="submit"]');
      const oldTxt = submitBtn ? submitBtn.textContent : null;
      if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Registering...'; }

      try {
        const res = await fetch(ROUTE('auth/register-action'), {
          method: 'POST',
          headers: {'Content-Type': 'application/json','Accept':'application/json'},
          body: JSON.stringify(payload)
        });
        const text = await res.text();
        let json = null;
        try { json = JSON.parse(text); } catch(e) { json = null; }

        if (json && json.ok) {
          if (json.redirect) location.href = json.redirect;
          else location.reload();
          return;
        }

        // show server-side errors
        if (json && json.errors) {
          Object.keys(json.errors).forEach((f) => {
            const el = form.querySelector(`[name="${f}"]`);
            if (el) setError(el, json.errors[f]);
          });
        } else if (json && json.msg) {
          alert(json.msg);
        } else {
          alert('Registration failed');
        }
      } catch (err) {
        alert('Network/server error');
      } finally {
        if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = oldTxt || 'Register'; }
      }
    });
  }

  // Initialize on DOM ready
  document.addEventListener('DOMContentLoaded', () => {
    $$( 'form[data-validate="auth"]' ).forEach(attachForm);
  });

})();