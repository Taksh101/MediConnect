<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/csrf.php';

// simple helper
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
