(function () {
  try {
    if (window.MC_USER_LOGGED_IN) {
      // change destination as per role if server sets a role variable
      var role = window.MC_USER_ROLE || 'patient';
      if (role === 'admin') location.replace('/MediConnect/views/admin/dashboard.php');
      else if (role === 'doctor') location.replace('/MediConnect/views/doctor/dashboard.php');
      else location.replace('/MediConnect/views/patient/dashboard.php');
    }
  } catch (e) {}
  if (window.location && window.location.protocol.indexOf('http') === 0) {
    history.replaceState(null, document.title, window.location.href);
    window.addEventListener('pageshow', function (evt) {
      if (evt.persisted) { location.reload(); }
    });
  }
})();
