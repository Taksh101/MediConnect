<?php
require_once __DIR__ . '/../config/csrf.php';
session_start();
session_unset();
session_destroy();
header('Location: /MediConnect/index.php?route=auth/login');
exit;
