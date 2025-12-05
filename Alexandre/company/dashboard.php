<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/security.php';
require_once '../includes/functions.php';

check_role(['company']);

$user_quizzes = get_quizzes_by_creator($pdo, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Entreprise - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <a href="dashboard.php" class="navbar-brand"><img src="../assets/images/logo.webp" alt="Quizzeo" class="navbar-logo"></a>
            <div class="navbar-menu">
                <a href="dashboard.php" class="navbar-link">Mes Questionnaires</a>
                <a href="create_quiz.php" class="navbar-link">Créer un Questionnaire</a>
                <span class="navbar-link" style="cursor: default;"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="../auth/logout.php" class="navbar-link">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin: 2rem 0;">
            <h1>Mes Questionnaires de Satisfaction</h1>
            <a href="create_quiz.php" class="btn btn-primary">Créer un questionnaire</a>
        </div>

        <?php if (empty($user_quizzes)): ?>
            <div class="empty-state fade-in">
                <div class="empty-state-icon">📋</div>
                <h2>Aucun questionnaire créé</h2>
                <p>Commencez par créer votre premier questionnaire de satisfaction.</p>
                <a href="create_quiz.php" class="btn btn-primary">Créer mon premier questionnaire</a>
            </div>
        <?php else: ?>
            <div class="quiz-list">
                <?php foreach ($user_quizzes as $quiz): ?>
                    <div class="quiz-item fade-in">
                        <div class="quiz-info">
                            <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                            <p style="color: var(--text-light); margin: 0.5rem 0;"><?php echo htmlspecialchars($quiz['description']); ?></p>
                            <div class="quiz-meta">
                                <span class="badge badge-<?php echo $quiz['status']; ?>">
                                    <?php
                                    $status_labels = [
                                        'draft' => 'Brouillon',
                                        'active' => 'Actif',
                                        'completed' => 'Terminé',
                                        'inactive' => 'Inactif'
                                    ];
                                    echo $status_labels[$quiz['status']];
                                    ?>
                                </span>
                                <span>📊 <?php echo $quiz['response_count']; ?> réponses</span>
                                <span>📅 <?php echo format_date($quiz['created_at']); ?></span>
                            </div>
                        </div>
                        
                        <div class="quiz-actions">
                            <?php if ($quiz['status'] === 'draft'): ?>
                                <a href="edit_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-secondary">Modifier</a>
                            <?php else: ?>
                                <a href="view_results.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary">Voir résultats</a>
                                <button class="btn btn-outline" onclick="copyToClipboard('<?php echo BASE_URL; ?>user/join.php?link=<?php echo $quiz['share_link']; ?>')">Copier le lien</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>