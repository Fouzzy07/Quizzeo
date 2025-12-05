<?php
echo "<h1>Test MySQL</h1>";

echo "<h2>1. Vérification PHP MySQL</h2>";
if (extension_loaded('pdo_mysql')) {
    echo "Extension PDO MySQL chargée<br>";
} else {
    echo "Extension PDO MySQL manquante<br>";
}

echo "<h2>2. Recherche du socket MySQL</h2>";
$possible_sockets = [
    '/tmp/mysql.sock',
    '/var/run/mysqld/mysqld.sock',
    '/usr/local/var/mysql/mysql.sock',
    '/var/mysql/mysql.sock'
];

$socket_found = null;
foreach ($possible_sockets as $socket) {
    if (file_exists($socket)) {
        echo "Socket trouvé : $socket<br>";
        $socket_found = $socket;
        break;
    } else {
        echo "Pas de socket à : $socket<br>";
    }
}

if ($socket_found) {
    echo "<h2>3. Test connexion via socket</h2>";
    try {
        $pdo = new PDO(
            "mysql:unix_socket=$socket_found;dbname=quizzeo;charset=utf8mb4",
            "root",
            "",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "<strong>CONNEXION RÉUSSIE via socket !</strong><br>";
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $result = $stmt->fetch();
        echo "Utilisateurs dans la base : " . $result['total'] . "<br>";
        
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage() . "<br>";
    }
}

echo "<h2>4. Test connexion via 127.0.0.1</h2>";
try {
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=quizzeo;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<strong>CONNEXION RÉUSSIE via 127.0.0.1 !</strong><br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $result = $stmt->fetch();
    echo "Utilisateurs dans la base : " . $result['total'] . "<br>";
    
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "<br>";
}

echo "<h2>5. Test connexion via localhost</h2>";
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=quizzeo;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo " <strong>CONNEXION RÉUSSIE via localhost !</strong><br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $result = $stmt->fetch();
    echo "Utilisateurs dans la base : " . $result['total'] . "<br>";
    
} catch (PDOException $e) {
    echo " Erreur : " . $e->getMessage() . "<br>";
}
?>