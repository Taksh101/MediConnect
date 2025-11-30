<?php
require_once __DIR__ . '/../../config/auth.php';
require_admin_login();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Admin Dashboard</title></head>
<body style="font-family:system-ui,Segoe UI,Roboto,Arial;padding:24px;">
  <h2>Admin Dashboard</h2>
  <p>Welcome, <?= htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['admin_id']) ?></p>
  <p><a href="/MediConnect/index.php?route=auth/logout">Logout</a></p>
</body>
</html>
