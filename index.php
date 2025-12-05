<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/security.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    switch ($_SESSION['user_role']) {
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        case 'school':
            header('Location: school/dashboard.php');
            break;
        case 'company':
            header('Location: company/dashboard.php');
            break;
        case 'user':
            header('Location: user/dashboard.php');
            break;
        default:
            header('Location: auth/login.php');
    }
} else {
    header('Location: auth/login.php');
}
exit();