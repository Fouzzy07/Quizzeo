<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/security.php';
require_once '../includes/functions.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } elseif (!check_login_attempts($pdo, $username)) {
        $error = "Trop de tentatives de connexion. Veuillez réessayer dans 15 minutes.";
    } else {
        $user = get_user_by_username($pdo, $username);
        
        if (!$user) {
            $user = get_user_by_email($pdo, $username);
        }
        
        if ($user && verify_password($password, $user['password'])) {
            if ($user['status'] === 'inactive') {
                $error = "Votre compte a été désactivé. Veuillez contacter l'administrateur.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['username'] = $user['username'];
                
                log_activity($pdo, $user['id'], 'login', 'Connexion réussie');
                
                header('Location: ../index.php');
                exit();
            }
        } else {
            log_activity($pdo, null, 'failed_login', $username);
            $error = "Identifiants incorrects.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 500px; margin-top: 5rem;">
        <div class="card fade-in">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-size: 3rem; background: linear-gradient(135deg, var(--primary-purple), var(--primary-pink), var(--primary-orange)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">QUIZZEO</h1>
            </div>
            
            <div class="card-header">
                <h2 class="card-title">Connexion</h2>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Nom d'utilisateur ou Email</label>
                    <input type="text" name="username" class="form-control" required autofocus>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Se connecter</button>
            </form>
            
            <div style="text-align: center; margin-top: 1.5rem;">
                <p>Pas encore de compte ? <a href="register.php" style="color: var(--primary-purple); font-weight: 600;">Créer un compte</a></p>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>