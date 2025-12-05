<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/security.php';
require_once '../includes/functions.php';

check_role(['user']);

$user = get_user_by_id($pdo, $_SESSION['user_id']);
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $update_data = [];
    
    if (!empty($_POST['first_name'])) {
        $update_data['first_name'] = sanitize_input($_POST['first_name']);
    }
    
    if (!empty($_POST['last_name'])) {
        $update_data['last_name'] = sanitize_input($_POST['last_name']);
    }
    
    if (!empty($_POST['email']) && $_POST['email'] !== $user['email']) {
        $email = sanitize_input($_POST['email']);
        if (!validate_email($email)) {
            $error = "Email invalide.";
        } elseif (get_user_by_email($pdo, $email)) {
            $error = "Cet email est déjà utilisé.";
        } else {
            $update_data['email'] = $email;
        }
    }
    
    if (!empty($_POST['new_password'])) {
        if (!verify_password($_POST['current_password'], $user['password'])) {
            $error = "Mot de passe actuel incorrect.";
        } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
            $error = "Les nouveaux mots de passe ne correspondent pas.";
        } elseif (!validate_password($_POST['new_password'])) {
            $error = "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.";
        } else {
            $update_data['password'] = $_POST['new_password'];
        }
    }
    
    if (empty($error) && !empty($update_data)) {
        if (update_user($pdo, $_SESSION['user_id'], $update_data)) {
            log_activity($pdo, $_SESSION['user_id'], 'update_profile', 'Profil mis à jour');
            $success = "Profil mis à jour avec succès !";
            $user = get_user_by_id($pdo, $_SESSION['user_id']);
        } else {
            $error = "Erreur lors de la mise à jour.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <a href="dashboard.php" class="navbar-brand"><img src="../assets/images/logo.webp" alt="Quizzeo" class="navbar-logo"></a>
            <div class="navbar-menu">
                <a href="dashboard.php" class="navbar-link">Mes Réponses</a>
                <a href="profile.php" class="navbar-link">Profil</a>
                <span class="navbar-link" style="cursor: default;"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="../auth/logout.php" class="navbar-link">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container" style="max-width: 800px;">
        <h1 style="margin: 2rem 0;">Mon Profil</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="card fade-in">
            <div class="card-header">
                <h3 class="card-title">Informations du compte</h3>
            </div>
            
            <div class="profile-info">
                <div class="info-item">
                    <div class="info-label">Nom d'utilisateur</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Rôle</div>
                    <div class="info-value">Utilisateur</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Statut</div>
                    <div class="info-value">
                        <span class="badge badge-<?php echo $user['status']; ?>">
                            <?php echo $user['status'] === 'active' ? 'Actif' : 'Inactif'; ?>
                        </span>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Membre depuis</div>
                    <div class="info-value"><?php echo format_date($user['created_at']); ?></div>
                </div>
            </div>
        </div>

        <div class="card fade-in">
            <div class="card-header">
                <h3 class="card-title">Modifier mes informations</h3>
            </div>
            
            <form method="POST" action="">
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nom</label>
                        <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
                
                <hr style="margin: 2rem 0;">
                
                <h4 style="margin-bottom: 1rem;">Changer le mot de passe</h4>
                
                <div class="form-group">
                    <label class="form-label">Mot de passe actuel</label>
                    <input type="password" name="current_password" class="form-control">
                </div>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Nouveau mot de passe</label>
                        <input type="password" name="new_password" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Confirmer le nouveau mot de passe</label>
                        <input type="password" name="confirm_password" class="form-control">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>