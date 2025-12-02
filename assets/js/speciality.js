// assets/js/speciality.js
// Robust real-time validation for speciality form with graceful fallback

(function () {
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('specialityForm') || document.querySelector('form[data-speciality-form]') || document.querySelector('form');
    if (!form) return;

    // helper to find by id or name
    function el(id, name) {
      return document.getElementById(id) || form.querySelector('[name="' + name + '"]') || form.querySelector('[name="' + id + '"]');
    }

    const nameEl = el('Speciality_Name', 'Speciality_Name');
    const durationEl = el('Consultation_Duration', 'Consultation_Duration');
    const feeEl = el('Consultation_Fee', 'Consultation_Fee');
    const descEl = el('Description', 'Description');

    // ensure elements exist
    if (!nameEl || !durationEl || !feeEl || !descEl) {
      // still attach submit guard to show a console warning if form incomplete
      form.addEventListener('submit', function (e) {
        // let server handle it
      });
      return;
    }

    function ensureFeedback(elInput) {
      let fb = elInput.parentElement.querySelector('.invalid-feedback');
      if (!fb) {
        fb = document.createElement('div');
        fb.className = 'invalid-feedback';
        elInput.parentElement.appendChild(fb);
      }
      return fb;
    }

    // prepare feedback nodes
    const fbName = ensureFeedback(nameEl);
    const fbDuration = ensureFeedback(durationEl);
    const fbFee = ensureFeedback(feeEl);
    const fbDesc = ensureFeedback(descEl);

    function setError(elInput, fb, msg) {
      fb.textContent = msg || '';
      elInput.classList.add('is-invalid');
      elInput.setAttribute('aria-invalid', 'true');
    }
    function clearError(elInput, fb) {
      fb.textContent = '';
      elInput.classList.remove('is-invalid');
      elInput.removeAttribute('aria-invalid');
    }

    // Determine if engine supports Unicode property escapes
    let unicodeLetterNumberRegex = null;
    try {
      // will throw on older engines
      unicodeLetterNumberRegex = new RegExp('^[\\p{L}\\p{N}\\s\\.\\,\\;:\\\'"\\(\\)\\-\\!\\?\\/\\%]+$', 'u');
    } catch (err) {
      unicodeLetterNumberRegex = null;
    }

    // Fallback regex (Latin + common punctuation). Wider support but not all scripts.
    const fallbackDescRegex = /^[A-Za-z0-9\u00C0-\u024F\s\.\,\;\:\'\"\(\)\-\!\?\/\%]+$/;

    // validators
    function validateName() {
      const v = String(nameEl.value || '').trim();
      if (!v) {
        setError(nameEl, fbName, 'Name is required');
        return false;
      }
      clearError(nameEl, fbName);
      return true;
    }

    function validateDuration() {
      const raw = String(durationEl.value || '').trim();
      if (raw === '') {
        setError(durationEl, fbDuration, 'Duration is required');
        return false;
      }
      // integer check
      if (!/^\d+$/.test(raw)) {
        setError(durationEl, fbDuration, 'Duration must be a whole number');
        return false;
      }
      const v = Number(raw);
      if (!Number.isInteger(v) || v < 5 || v > 45) {
        setError(durationEl, fbDuration, 'Duration must be a whole number between 5 and 45');
        return false;
      }
      clearError(durationEl, fbDuration);
      return true;
    }

    function validateFee() {
      const raw = String(feeEl.value || '').trim();
      if (raw === '') {
        setError(feeEl, fbFee, 'Fee is required');
        return false;
      }
      // integer >= 0
      if (!/^\d+$/.test(raw)) {
        setError(feeEl, fbFee, 'Fee must be a whole number (0 or greater)');
        return false;
      }
      const v = Number(raw);
      if (!Number.isInteger(v) || v < 0) {
        setError(feeEl, fbFee, 'Fee must be 0 or greater');
        return false;
      }
      clearError(feeEl, fbFee);
      return true;
    }

    function normalizeInvisibleChars(s) {
      // remove zero-width/invisible chars, turn NBSP to normal space, trim
      return String(s || '')
        .replace(/[\u200B-\u200F\uFEFF\u2060]/g, '')
        .replace(/\u00A0/g, ' ')
        .trim();
    }

    // Reject control characters (allow whitespace like space, tab, newline)
    function containsControlChars(s) {
      return /[\u0000-\u0008\u000B\u000C\u000E-\u001F\u007F]/.test(s);
    }

    function validateDesc() {
      const v = normalizeInvisibleChars(descEl.value);
      if (!v) {
        setError(descEl, fbDesc, 'Description is required');
        return false;
      }

      const len = v.length;
      if (len < 5 || len > 200) {
        setError(descEl, fbDesc, 'Description must be between 5 and 200 characters');
        return false;
      }

      if (containsControlChars(v)) {
        setError(descEl, fbDesc, 'Description contains invalid characters');
        return false;
      }

      // optional hard cap in characters to avoid abuse
      if (v.length > 4000) {
        setError(descEl, fbDesc, 'Description is too long');
        return false;
      }

      clearError(descEl, fbDesc);
      return true;
    }

    // Real-time listeners (input + blur for better UX)
    nameEl.addEventListener('input', validateName);
    nameEl.addEventListener('blur', validateName);

    durationEl.addEventListener('input', validateDuration);
    durationEl.addEventListener('blur', validateDuration);

    feeEl.addEventListener('input', validateFee);
    feeEl.addEventListener('blur', validateFee);

    descEl.addEventListener('input', validateDesc);
    descEl.addEventListener('blur', validateDesc);

    // On form submit run all validators
    form.addEventListener('submit', function (ev) {
      let ok = true;
      if (!validateName()) ok = false;
      if (!validateDuration()) ok = false;
      if (!validateFee()) ok = false;
      if (!validateDesc()) ok = false;

      if (!ok) {
        ev.preventDefault();
        // focus first invalid
        const firstInvalid = form.querySelector('.is-invalid');
        if (firstInvalid) firstInvalid.focus();
        return false;
      }

      // attach CSRF token if available globally
      if (typeof SPECIALITY_CSRF !== 'undefined' && SPECIALITY_CSRF) {
        let input = form.querySelector('[name="csrf_token"]');
        if (!input) {
          input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'csrf_token';
          form.appendChild(input);
        }
        input.value = SPECIALITY_CSRF;
      }

      return true;
    });

    // If server rendered errors are present inside .invalid-feedback, make fields show is-invalid on load
    function markServerErrors() {
      try {
        if (fbName && fbName.textContent.trim()) nameEl.classList.add('is-invalid');
        if (fbDuration && fbDuration.textContent.trim()) durationEl.classList.add('is-invalid');
        if (fbFee && fbFee.textContent.trim()) feeEl.classList.add('is-invalid');
        if (fbDesc && fbDesc.textContent.trim()) descEl.classList.add('is-invalid');
      } catch (e) { /* ignore */ }
    }
    markServerErrors();
  });
})();
