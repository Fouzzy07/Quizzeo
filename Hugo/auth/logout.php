<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/security.php';
require_once '../includes/functions.php';

if (isset($_SESSION['user_id'])) {
    log_activity($pdo, $_SESSION['user_id'], 'logout', 'Déconnexion');
}

session_unset();
session_destroy();
session_start();
$_SESSION = array();

header('Location: login.php');
exit();