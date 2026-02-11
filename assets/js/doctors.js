// assets/js/doctors.js
// Live validation for doctor form + delete handling + password toggle (icon button)

(function () {
  document.addEventListener('DOMContentLoaded', function () {
    // DELETE (index)
    let deleteId = null;
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.btn-delete');
      if (!btn) return;
      deleteId = btn.dataset.id;
      const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
      modal.show();
    });

    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (confirmBtn) {
      confirmBtn.addEventListener('click', function () {
        if (!deleteId) return;
        const body = new URLSearchParams();
        body.append('id', deleteId);
        if (typeof DOCTORS_CSRF !== 'undefined' && DOCTORS_CSRF) body.append('csrf_token', DOCTORS_CSRF);

        fetch((typeof BASE_PATH !== 'undefined' ? BASE_PATH : '') + '/index.php?route=admin/doctors/delete', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: body.toString()
        }).then(async (r) => {
          const ct = r.headers.get('content-type') || '';
          // If server returned JSON, parse it
          if (ct.indexOf('application/json') !== -1) {
            const j = await r.json();
            if (j.ok) return location.reload();
            alert(j.error || 'Delete failed');
            return;
          }

          // Otherwise try to read plain text (useful for DB errors or HTML redirects)
          const text = await r.text();
          try {
            const j = JSON.parse(text);
            if (j.ok) return location.reload();
            alert(j.error || text || 'Delete failed');
            return;
          } catch (e) {
            // Not JSON: show raw text (helps debugging)
            const msg = text && text.length < 1000 ? text : 'Delete failed';
            alert(msg);
          }
        }).catch((err) => { console.error('Delete request error', err); alert('Delete failed'); });
      });
    }

    // FORM VALIDATION (live)
    const form = document.getElementById('doctorForm');
    if (!form) return;

    // helpers
    function qs(name) { return form.querySelector('[name="' + name + '"]'); }
    const nameEl = qs('Name'), emailEl = qs('Email'), phoneEl = qs('Phone'), specEl = qs('Speciality_Id'), passwdEl = qs('Password'), expEl = qs('Experience_Years'), qualEl = qs('Qualification'), bioEl = qs('Bio'), statusEl = qs('Status');

    function ensureFb(i) {
      if (!i) return { el: null, fb: { textContent: '' } };
      let fb = i.parentElement.querySelector('.invalid-feedback');
      if (!fb) { fb = document.createElement('div'); fb.className = 'invalid-feedback'; i.parentElement.appendChild(fb); }
      return { el: i, fb: fb };
    }
    const fbName = ensureFb(nameEl), fbEmail = ensureFb(emailEl), fbPhone = ensureFb(phoneEl), fbSpec = ensureFb(specEl), fbPass = ensureFb(passwdEl), fbExp = ensureFb(expEl), fbQual = ensureFb(qualEl), fbBio = ensureFb(bioEl), fbStatus = ensureFb(statusEl);

    function setErr(i, fb, msg) { if (!i) return; fb.textContent = msg || ''; i.classList.add('is-invalid'); i.setAttribute('aria-invalid', 'true'); }
    function clearErr(i, fb) { if (!i) return; fb.textContent = ''; i.classList.remove('is-invalid'); i.removeAttribute('aria-invalid'); }

    // PHONE: allow digits only while typing
    if (phoneEl) {
      phoneEl.addEventListener('input', function (e) {
        const cleaned = this.value.replace(/\D+/g, '');
        this.value = cleaned.slice(0, 10); // max 10
      });
    }

    function validateName() { if (!nameEl.value.trim()) { setErr(nameEl, fbName.fb, 'Name is required'); return false } clearErr(nameEl, fbName.fb); return true; }

    function validateEmail() { const v = emailEl.value.trim(); if (!v || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) { setErr(emailEl, fbEmail.fb, 'Valid email required'); return false } clearErr(emailEl, fbEmail.fb); return true; }

    function validatePhone() { const v = phoneEl.value.trim(); if (!/^[6-9][0-9]{9}$/.test(v)) { setErr(phoneEl, fbPhone.fb, 'Phone must be 10 digits and start with 6-9'); return false } clearErr(phoneEl, fbPhone.fb); return true; }

    function validateSpec() { if (!specEl.value) { setErr(specEl, fbSpec.fb, 'Select speciality'); return false } clearErr(specEl, fbSpec.fb); return true; }

    function validateExp() { const v = expEl.value.trim(); if (!/^\d+$/.test(v) || Number(v) < 0) { setErr(expEl, fbExp.fb, 'Enter valid years'); return false } clearErr(expEl, fbExp.fb); return true; }

    function validateQualification() { const v = qualEl.value.trim(); if (!v || v.length < 2 || v.length > 100) { setErr(qualEl, fbQual.fb, 'Qualification is required (2-100 chars)'); return false } clearErr(qualEl, fbQual.fb); return true; }

    function validateBio() { const v = bioEl.value.trim(); if (!v || v.length < 10 || v.length > 500) { setErr(bioEl, fbBio.fb, 'Bio required (10-500 chars)'); return false } clearErr(bioEl, fbBio.fb); return true; }

    function validateStatus() { if (!['AVAILABLE', 'UNAVAILABLE'].includes(statusEl.value)) { setErr(statusEl, fbStatus.fb, 'Invalid status'); return false } clearErr(statusEl, fbStatus.fb); return true; }

    function validatePassword() {
      const v = passwdEl.value || '';
      // if editing and empty -> ok
      if (form.querySelector('[name="Doctor_Id"]') && v === '') { clearErr(passwdEl, fbPass.fb); return true; }
      // required when creating
      if (!form.querySelector('[name="Doctor_Id"]') && v === '') { setErr(passwdEl, fbPass.fb, 'Password required'); return false; }

      if (v.length < 8) { setErr(passwdEl, fbPass.fb, 'Password must be at least 8 characters'); return false; }
      if (!/[A-Z]/.test(v)) { setErr(passwdEl, fbPass.fb, 'Include at least one uppercase letter'); return false; }
      if (!/[a-z]/.test(v)) { setErr(passwdEl, fbPass.fb, 'Include at least one lowercase letter'); return false; }
      if (!/[0-9]/.test(v)) { setErr(passwdEl, fbPass.fb, 'Include at least one digit'); return false; }
      if (!/[^A-Za-z0-9]/.test(v)) { setErr(passwdEl, fbPass.fb, 'Include at least one symbol'); return false; }
      clearErr(passwdEl, fbPass.fb);
      return true;
    }

    // Attach live listeners
    [[nameEl, validateName], [emailEl, validateEmail], [phoneEl, validatePhone], [specEl, validateSpec], [expEl, validateExp], [qualEl, validateQualification], [bioEl, validateBio], [statusEl, validateStatus], [passwdEl, validatePassword]].forEach(pair => {
      const elRef = pair[0], fn = pair[1];
      if (!elRef) return;
      elRef.addEventListener('input', fn);
      elRef.addEventListener('blur', fn);
    });

    // Password toggle button (icon swap)
    const pwdToggle = document.getElementById('pwdToggle');
    if (pwdToggle && passwdEl) {
      pwdToggle.addEventListener('click', function () {
        const eye = document.getElementById('pwdEyeIcon');

        if (passwdEl.type === 'password') {
          passwdEl.type = 'text';
          eye.innerHTML = `
      <path d="M1 8C1 8 3.5 3.5 8 3.5C12.5 3.5 15 8 15 8C15 8 12.5 12.5 8 12.5C3.5 12.5 1 8 1 8Z"
            stroke="#374151" stroke-width="1.4"/>
      <circle cx="8" cy="8" r="2.3" stroke="#374151" stroke-width="1.4"/>
      <line x1="3" y1="3" x2="13" y2="13" stroke="#374151" stroke-width="1.6"/>
    `;
        } else {
          passwdEl.type = 'password';
          eye.innerHTML = `
      <path d="M1 8C1 8 3.5 3.5 8 3.5C12.5 3.5 15 8 15 8C15 8 12.5 12.5 8 12.5C3.5 12.5 1 8 1 8Z"
            stroke="#374151" stroke-width="1.4"/>
      <circle cx="8" cy="8" r="2.3" stroke="#374151" stroke-width="1.4"/>
    `;
        }

        passwdEl.focus();
      });

    }

    form.addEventListener('submit', function (ev) {
      let ok = true;
      if (!validateName()) ok = false;
      if (!validateEmail()) ok = false;
      if (!validatePhone()) ok = false;
      if (!validateSpec()) ok = false;
      if (!validateExp()) ok = false;
      if (!validateQualification()) ok = false;
      if (!validateBio()) ok = false;
      if (!validateStatus()) ok = false;
      if (!validatePassword()) ok = false;

      if (!ok) { ev.preventDefault(); const first = form.querySelector('.is-invalid'); if (first) first.focus(); return false; }

      // attach csrf token if available globally
      if (typeof DOCTOR_CSRF !== 'undefined' && DOCTOR_CSRF) {
        let input = form.querySelector('[name="csrf_token"]');
        if (!input) {
          input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'csrf_token';
          form.appendChild(input);
        }
        input.value = DOCTOR_CSRF;
      }
      return true;
    });

    // mark server errors if present
    try {
      if (fbName.fb.textContent.trim()) nameEl.classList.add('is-invalid');
      if (fbEmail.fb.textContent.trim()) emailEl.classList.add('is-invalid');
      if (fbPhone.fb.textContent.trim()) phoneEl.classList.add('is-invalid');
      if (fbSpec.fb.textContent.trim()) specEl.classList.add('is-invalid');
      if (fbExp.fb.textContent.trim()) expEl.classList.add('is-invalid');
      if (fbQual.fb.textContent.trim()) qualEl.classList.add('is-invalid');
      if (fbBio.fb.textContent.trim()) bioEl.classList.add('is-invalid');
      if (fbStatus.fb.textContent.trim()) statusEl.classList.add('is-invalid');
      if (fbPass.fb.textContent.trim()) passwdEl.classList.add('is-invalid');
    } catch (e) { }
  });
})();
