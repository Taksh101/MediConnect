<?php
require_once __DIR__ . '/../../config/auth.php';
require_doctor_login();
?>

<!doctype html>
<html>
<head>
    <title>Doctor Dashboard</title>
</head>
<body>
    <h2>Doctor Dashboard</h2>
    <p>Welcome Doctor: <?= $_SESSION['doctor_id'] ?></p>
</body>
</html>
