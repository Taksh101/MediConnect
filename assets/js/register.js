// MediConnect/assets/js/register.js
(function () {
  // base route prefix -> adjust if needed
  const BASE_PATH = '/MediConnect';
  // injected BASE_PATH from PHP (guaranteed correct)
const ROUTE = (r) => '/MediConnect/index.php?route=' + r;



  const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
  const PASS_RE  = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#^._-]).{8,64}$/;

  // helper to find the form(s) we need
  function $$(sel, root=document) { return Array.from(root.querySelectorAll(sel)); }
  function $(sel, root=document) { return root.querySelector(sel); }

  function setError(input, msg) {
    if (!input) return;
    input.classList.add('is-invalid');
    const fb = input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback') ? input.nextElementSibling : null;
    if (fb) fb.textContent = msg || 'Invalid value';
  }
  function clearError(input) {
    if (!input) return;
    input.classList.remove('is-invalid');
    const fb = input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback') ? input.nextElementSibling : null;
    if (fb) fb.textContent = '';
  }

  // numeric-only enforcement
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

  function debounce(fn, ms) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(()=>fn(...args), ms); };
  }

  // server email check
  const emailCheck = debounce(async (email, input) => {
    if (!email || !input) return;
    try {
      const res = await fetch(ROUTE('auth/check-email'), {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ email })
      });
      const json = await res.json();
      if (json && json.ok && json.exists) setError(input, 'Email already registered.');
      else clearError(input);
    } catch (err) {
      setError(input, 'Server error, try later.');
    }
  }, 350);

  // per-field validation
  function validate(input, force=false) {
    if (!input) return true;
    const name = input.name;
    const val = (input.value || '').trim();
    const touched = force || input.dataset.touched === '1';
    if (!touched) return true;

    // required
    if (input.hasAttribute('required') && !val) {
      setError(input, 'This field is required.');
      return false;
    }

    // rules
    if (name === 'email') {
      if (val && !EMAIL_RE.test(val)) { setError(input, 'Enter a valid email.'); return false; }
      // if format ok -> server check
      if (val) emailCheck(val, input);
    }

    if (name === 'password') {
      if (val && !PASS_RE.test(val)) { setError(input, 'Min 8 chars: upper, lower, number & symbol.'); return false; }
    }

    if (name === 'confirm_password') {
      const pass = input.form ? input.form.querySelector('input[name="password"]') : null;
      if (pass && pass.value !== input.value) { setError(input, 'Passwords do not match.'); return false; }
    }

    if (name === 'phone') {
      if (val && !/^\d{10}$/.test(val)) { setError(input, 'Enter 10 digits.'); return false; }
    }

    if (name === 'name') {
      if (val && (val.length < 2 || val.length > 100)) { setError(input, 'Enter 2–100 characters.'); return false; }
    }

    if (name === 'dob') {
      if (val) {
        const d = new Date(val + 'T00:00:00');
        const today = new Date(); today.setHours(0,0,0,0);
        if (isNaN(d.getTime()) || d > today) { setError(input, 'Enter a valid past date.'); return false; }
      }
    }

    clearError(input);
    return true;
  }

  // attach behavior to forms with data-validate="auth"
  function attachForm(form) {
    if (!form) return;

    // enforce digits on phone
    const phone = form.querySelector('input[name="phone"]');
    if (phone) enforceDigits(phone, 10);

    // attach input/blur for all controls
    $$( 'input, select, textarea', form ).forEach((inp) => {
      inp.addEventListener('input', () => {
        if (!inp.dataset.touched) inp.dataset.touched = '1';
        validate(inp, true);
      });
      inp.addEventListener('blur', () => {
        inp.dataset.touched = '1';
        validate(inp, true);
      });
    });

    // password toggle logic (safe, avoids errors)
    const tp = form.querySelector('#togglePassword');
    const tc = form.querySelector('#toggleConfirm');
    if (tp) {
      tp.addEventListener('click', () => {
        const p = form.querySelector('input[name="password"]');
        if (!p) return;
        if (p.type === 'password') { p.type = 'text'; tp.innerHTML = '🙈'; }
        else { p.type = 'password'; tp.innerHTML = '👁️'; }
        p.focus();
      });
    }
    if (tc) {
      tc.addEventListener('click', () => {
        const p = form.querySelector('input[name="confirm_password"]');
        if (!p) return;
        if (p.type === 'password') { p.type = 'text'; tc.innerHTML = '🙈'; }
        else { p.type = 'password'; tc.innerHTML = '👁️'; }
        p.focus();
      });
    }

    // on submit: validate all, if ok -> AJAX
    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      let ok = true;
      $$( 'input, select, textarea', form ).forEach((inp) => {
        // skip optional empty
        if (!inp.hasAttribute('required') && !inp.value.trim()) { clearError(inp); return; }
        if (!validate(inp, true)) ok = false;
      });

      if (!ok) return;

      const fd = new FormData(form);
      const payload = {};
      fd.forEach((v,k) => payload[k] = v);

      const submitBtn = form.querySelector('button[type="submit"]');
      const oldTxt = submitBtn ? submitBtn.textContent : null;
      if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Registering...'; }

      try {
        const res = await fetch(ROUTE('auth/register-action'), {
          method: 'POST',
          headers: {'Content-Type':'application/json','Accept':'application/json'},
          body: JSON.stringify(payload)
        });
        if (!res.ok) {
          // try to parse json error body
          const txt = await res.text();
          try {
            const j = JSON.parse(txt);
            handleServerErrors(j, form);
          } catch (err) {
            alert('Server error');
          }
          return;
        }
        const json = await res.json();
        if (json.ok) {
          if (json.redirect) window.location.href = json.redirect;
          else window.location.reload();
        } else {
          handleServerErrors(json, form);
        }
      } catch (err) {
        alert('Network/server error');
      } finally {
        if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = oldTxt || 'Register'; }
      }
    });
  }

  function handleServerErrors(json, form) {
    if (!json) return;
    if (json.errors) {
      Object.keys(json.errors).forEach((field) => {
        const el = form.querySelector(`[name="${field}"]`);
        if (el) setError(el, json.errors[field]);
      });
    } else if (json.msg) {
      alert(json.msg);
    } else {
      alert('Registration failed');
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    $$( 'form[data-validate="auth"]' ).forEach(attachForm);
  });
})();
