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
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['captcha_input']) || !isset($_POST['captcha_value'])) {
        $error = "Erreur CAPTCHA.";
    } elseif ($_POST['captcha_input'] !== $_POST['captcha_value']) {
        $error = "CAPTCHA incorrect.";
    } else {
        $username = sanitize_input($_POST['username']);
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $role = sanitize_input($_POST['role']);
        $first_name = sanitize_input($_POST['first_name']);
        $last_name = sanitize_input($_POST['last_name']);
        
        if (empty($username) || empty($email) || empty($password) || empty($role)) {
            $error = "Veuillez remplir tous les champs obligatoires.";
        } elseif (!validate_username($username)) {
            $error = "Le nom d'utilisateur doit contenir entre 3 et 50 caractères alphanumériques.";
        } elseif (!validate_email($email)) {
            $error = "Email invalide.";
        } elseif (!validate_password($password)) {
            $error = "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.";
        } elseif ($password !== $confirm_password) {
            $error = "Les mots de passe ne correspondent pas.";
        } elseif (!in_array($role, ['school', 'company', 'user'])) {
            $error = "Rôle invalide.";
        } elseif (get_user_by_username($pdo, $username)) {
            $error = "Ce nom d'utilisateur existe déjà.";
        } elseif (get_user_by_email($pdo, $email)) {
            $error = "Cet email est déjà utilisé.";
        } else {
            if (create_user($pdo, $username, $email, $password, $role, $first_name, $last_name)) {
                $success = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
            } else {
                $error = "Erreur lors de la création du compte.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 600px; margin-top: 3rem;">
        <div class="card fade-in">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-size: 3rem; background: linear-gradient(135deg, var(--primary-purple), var(--primary-pink), var(--primary-orange)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">QUIZZEO</h1>
            </div>
            
            <div class="card-header">
                <h2 class="card-title">Créer un compte</h2>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="first_name" class="form-control" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nom</label>
                        <input type="text" name="last_name" class="form-control" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nom d'utilisateur *</label>
                    <input type="text" name="username" class="form-control" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Rôle *</label>
                    <select name="role" class="form-select" required>
                        <option value="">Sélectionnez un rôle</option>
                        <option value="school" <?php echo (isset($_POST['role']) && $_POST['role'] === 'school') ? 'selected' : ''; ?>>École</option>
                        <option value="company" <?php echo (isset($_POST['role']) && $_POST['role'] === 'company') ? 'selected' : ''; ?>>Entreprise</option>
                        <option value="user" <?php echo (isset($_POST['role']) && $_POST['role'] === 'user') ? 'selected' : ''; ?>>Utilisateur</option>
                    </select>
                </div>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Mot de passe *</label>
                        <input type="password" name="password" class="form-control" required>
                        <small style="color: var(--text-light);">Min. 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Confirmer le mot de passe *</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                
                <div class="captcha-box">
                    <p>Recopiez le texte ci-dessous :</p>
                    <div id="captcha-display" class="captcha-text"></div>
                    <input type="hidden" id="captcha-value" name="captcha_value">
                    <input type="text" id="captcha-input" name="captcha_input" class="form-control" style="max-width: 200px; margin: 1rem auto;" required>
                    <button type="button" class="btn btn-outline" onclick="displayCaptcha()">Nouveau CAPTCHA</button>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Créer mon compte</button>
            </form>
            
            <div style="text-align: center; margin-top: 1.5rem;">
                <p>Déjà un compte ? <a href="login.php" style="color: var(--primary-purple); font-weight: 600;">Se connecter</a></p>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>