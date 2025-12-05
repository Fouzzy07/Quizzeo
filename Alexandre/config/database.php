<?php
define('DB_NAME', 'quizzeo');
define('DB_USER', 'root');
define('DB_PASS', 'root123');
define('DB_CHARSET', 'utf8mb4');

$possible_sockets = [
    '/tmp/mysql.sock',
    '/var/run/mysqld/mysqld.sock',
    '/usr/local/var/mysql/mysql.sock',
    '/var/mysql/mysql.sock'
];

$socket = null;
foreach ($possible_sockets as $s) {
    if (file_exists($s)) {
        $socket = $s;
        break;
    }
}

try {
    if ($socket) {
        $dsn = "mysql:unix_socket=$socket;dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    } else {
        $dsn = "mysql:host=127.0.0.1;dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    }
    
    $pdo = new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}