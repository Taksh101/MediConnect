<?php
define('DB_HOST','127.0.0.1');
define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','mediconnect');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) {
    error_log("DB connect error: ".$mysqli->connect_error);
    exit('Database connection failed.');
}
$mysqli->set_charset('utf8mb4');