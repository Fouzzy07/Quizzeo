<?php
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_username($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username);
}

function validate_password($password) {
    return strlen($password) >= 8 && 
           preg_match('/[A-Z]/', $password) && 
           preg_match('/[a-z]/', $password) && 
           preg_match('/[0-9]/', $password);
}

function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

function check_auth() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit();
    }
}

function check_role($allowed_roles) {
    check_auth();
    if (!in_array($_SESSION['user_role'], $allowed_roles)) {
        header('Location: ' . BASE_URL . 'index.php');
        exit();
    }
}

function prevent_sql_injection($pdo, $query, $params = []) {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt;
}

function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

function log_activity($pdo, $user_id, $action, $details = '') {
    $ip = get_client_ip();
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $action, $details, $ip]);
}

function check_login_attempts($pdo, $username) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as attempts FROM activity_logs WHERE details = ? AND action = 'failed_login' AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->execute([$username, LOCKOUT_TIME]);
    $result = $stmt->fetch();
    return $result['attempts'] < MAX_LOGIN_ATTEMPTS;
}

function rate_limit($key, $max_requests = 10, $time_window = 60) {
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }
    
    $current_time = time();
    
    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = ['count' => 1, 'start_time' => $current_time];
        return true;
    }
    
    $elapsed_time = $current_time - $_SESSION['rate_limit'][$key]['start_time'];
    
    if ($elapsed_time > $time_window) {
        $_SESSION['rate_limit'][$key] = ['count' => 1, 'start_time' => $current_time];
        return true;
    }
    
    if ($_SESSION['rate_limit'][$key]['count'] < $max_requests) {
        $_SESSION['rate_limit'][$key]['count']++;
        return true;
    }
    
    return false;
}

function escape_json($data) {
    return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

function validate_file_upload($file, $allowed_types = ['image/jpeg', 'image/png', 'image/gif'], $max_size = 5242880) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    if ($file['size'] > $max_size) {
        return false;
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    return in_array($mime, $allowed_types);
}