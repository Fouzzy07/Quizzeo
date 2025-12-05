<?php
define('SITE_NAME', 'Quizzeo');

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';

$script_name = $_SERVER['SCRIPT_NAME'];
$project_root = '';

if (strpos($script_name, '/school/') !== false) {
    $parts = explode('/school/', $script_name);
    $project_root = $parts[0];
} elseif (strpos($script_name, '/company/') !== false) {
    $parts = explode('/company/', $script_name);
    $project_root = $parts[0];
} elseif (strpos($script_name, '/user/') !== false) {
    $parts = explode('/user/', $script_name);
    $project_root = $parts[0];
} elseif (strpos($script_name, '/admin/') !== false) {
    $parts = explode('/admin/', $script_name);
    $project_root = $parts[0];
} elseif (strpos($script_name, '/auth/') !== false) {
    $parts = explode('/auth/', $script_name);
    $project_root = $parts[0];
} else {
    $project_root = dirname($script_name);
}

$base_path = ($project_root === '/' || $project_root === '' || $project_root === '.') ? '/' : rtrim($project_root, '/') . '/';
define('BASE_URL', $protocol . '://' . $host . $base_path);

define('SESSION_LIFETIME', 3600);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900);

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Strict');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Europe/Paris');