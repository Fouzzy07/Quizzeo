<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/security.php';
require_once '../includes/functions.php';

check_role(['user']);

$user_responses = get_user_quiz_responses($pdo, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
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

    <div class="container">
        <h1 style="margin: 2rem 0;">Mes Quiz Complétés</h1>

        <?php if (empty($user_responses)): ?>
            <div class="empty-state fade-in">
                <div class="empty-state-icon">📝</div>
                <h2>Aucun quiz complété</h2>
                <p>Vous n'avez encore répondu à aucun quiz. Demandez un lien de quiz à votre école ou entreprise.</p>
            </div>
        <?php else: ?>
            <div class="quiz-list">
                <?php foreach ($user_responses as $response): ?>
                    <div class="quiz-item fade-in">
                        <div class="quiz-info">
                            <h3><?php echo htmlspecialchars($response['title']); ?></h3>
                            <div class="quiz-meta">
                                <span class="badge badge-completed">Complété</span>
                                <?php if ($response['quiz_type'] === 'school'): ?>
                                    <span>📊 Note: <strong><?php echo number_format($response['total_score'], 2); ?></strong></span>
                                <?php endif; ?>
                                <span>📅 <?php echo format_date($response['completed_at']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>