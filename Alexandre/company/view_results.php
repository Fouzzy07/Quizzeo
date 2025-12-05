<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/security.php';
require_once '../includes/functions.php';

check_role(['school']);

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$quiz_id = (int)$_GET['id'];
$quiz = get_quiz_by_id($pdo, $quiz_id);

if (!$quiz || $quiz['creator_id'] != $_SESSION['user_id']) {
    header('Location: dashboard.php');
    exit();
}

$responses = get_quiz_responses($pdo, $quiz_id);
$statistics = get_quiz_statistics($pdo, $quiz_id);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <a href="dashboard.php" class="navbar-brand"><img src="../assets/images/logo.webp" alt="Quizzeo" class="navbar-logo"></a>
            <div class="navbar-menu">
                <a href="dashboard.php" class="navbar-link">Dashboard</a>
                <a href="mes_quiz.php" class="navbar-link">Mes Quiz</a>
                <a href="create_quiz.php" class="navbar-link">Créer un Quiz</a>
                <span class="navbar-link" style="cursor: default;"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="../auth/logout.php" class="navbar-link">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div style="margin: 2rem 0;">
            <a href="mes_quiz.php" class="btn btn-outline">← Retour</a>
        </div>

        <div class="card fade-in">
            <div class="card-header">
                <h2 class="card-title"><?php echo htmlspecialchars($quiz['title']); ?></h2>
                <p style="color: var(--text-light);"><?php echo htmlspecialchars($quiz['description']); ?></p>
            </div>
            
            <div class="link-box">
                <span class="link-text"><?php echo BASE_URL; ?>user/join.php?link=<?php echo $quiz['share_link']; ?></span>
                <button class="copy-btn" onclick="copyToClipboard('<?php echo BASE_URL; ?>user/join.php?link=<?php echo $quiz['share_link']; ?>')">Copier</button>
            </div>
        </div>

        <?php if ($statistics['total_responses'] > 0): ?>
            <div class="stats-grid">
                <div class="stat-card stat-card-purple fade-in">
                    <div class="stat-value"><?php echo $statistics['total_responses']; ?></div>
                    <div class="stat-label">Réponses totales</div>
                </div>
                
                <div class="stat-card stat-card-pink fade-in">
                    <div class="stat-value"><?php echo number_format($statistics['average_score'], 2); ?></div>
                    <div class="stat-label">Note moyenne</div>
                </div>
                
                <div class="stat-card stat-card-orange fade-in">
                    <div class="stat-value"><?php echo number_format($statistics['max_score'], 2); ?></div>
                    <div class="stat-label">Meilleure note</div>
                </div>
                
                <div class="stat-card stat-card-purple fade-in">
                    <div class="stat-value"><?php echo number_format($statistics['min_score'], 2); ?></div>
                    <div class="stat-label">Note la plus basse</div>
                </div>
            </div>

            <div class="card fade-in">
                <div class="card-header">
                    <h3 class="card-title">Résultats des étudiants</h3>
                </div>
                
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Utilisateur</th>
                                <th>Note</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($responses as $response): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($response['last_name'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($response['first_name'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($response['username']); ?></td>
                                    <td><strong><?php echo number_format($response['total_score'], 2); ?></strong></td>
                                    <td><?php echo format_date($response['completed_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state fade-in">
                <div class="empty-state-icon">📊</div>
                <h2>Aucune réponse pour le moment</h2>
                <p>Partagez le lien du quiz avec vos étudiants pour commencer à recevoir des réponses.</p>
                <div style="margin-top: 2rem;">
                    <button class="btn btn-primary" onclick="copyToClipboard('<?php echo BASE_URL; ?>user/join.php?link=<?php echo $quiz['share_link']; ?>')">
                        📋 Copier le lien du quiz
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>