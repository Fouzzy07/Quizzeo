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
    <title>Mes Questionnaires - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <a href="dashboard.php" class="navbar-brand"><img src="../assets/images/logo.webp" alt="Quizzeo" class="navbar-logo"></a>
            <div class="navbar-menu">
                <a href="dashboard.php" class="navbar-link">Dashboard</a>
                <a href="mes_quiz.php" class="navbar-link">Mes Questionnaires</a>
                <a href="create_quiz.php" class="navbar-link">Créer un Questionnaire</a>
                <span class="navbar-link" style="cursor: default;"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="../auth/logout.php" class="navbar-link">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if (isset($_GET['created'])): ?>
            <div class="alert alert-success fade-in" style="margin-top: 2rem;">
                ✅ Questionnaire créé avec succès ! Vous pouvez maintenant le partager avec vos clients.
            </div>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; align-items: center; margin: 2rem 0;">
            <h1>Tous mes Questionnaires</h1>
            <a href="create_quiz.php" class="btn btn-primary">➕ Créer un nouveau questionnaire</a>
        </div>

        <?php if (empty($user_quizzes)): ?>
            <div class="empty-state fade-in">
                <div class="empty-state-icon">📋</div>
                <h2>Aucun questionnaire créé</h2>
                <p>Commencez par créer votre premier questionnaire de satisfaction.</p>
                <a href="create_quiz.php" class="btn btn-primary">Créer mon premier questionnaire</a>
            </div>
        <?php else: ?>
            <div class="card fade-in" style="margin-bottom: 2rem;">
                <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                    <div class="form-group" style="flex: 1; margin: 0;">
                        <input type="text" id="search-quiz" class="form-control" placeholder="🔍 Rechercher un questionnaire..." onkeyup="filterQuizzes()">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <select id="filter-status" class="form-select" onchange="filterQuizzes()">
                            <option value="">Tous les statuts</option>
                            <option value="draft">Brouillon</option>
                            <option value="active">Actif</option>
                            <option value="completed">Terminé</option>
                            <option value="inactive">Inactif</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="stats-grid">
                <?php
                $total_quizzes = count($user_quizzes);
                $active_quizzes = count(array_filter($user_quizzes, fn($q) => $q['status'] === 'active'));
                $draft_quizzes = count(array_filter($user_quizzes, fn($q) => $q['status'] === 'draft'));
                $total_responses = array_sum(array_column($user_quizzes, 'response_count'));
                ?>
                
                <div class="stat-card stat-card-purple fade-in">
                    <div class="stat-value"><?php echo $total_quizzes; ?></div>
                    <div class="stat-label">Questionnaires créés</div>
                </div>
                
                <div class="stat-card stat-card-pink fade-in">
                    <div class="stat-value"><?php echo $active_quizzes; ?></div>
                    <div class="stat-label">Questionnaires actifs</div>
                </div>
                
                <div class="stat-card stat-card-orange fade-in">
                    <div class="stat-value"><?php echo $draft_quizzes; ?></div>
                    <div class="stat-label">Brouillons</div>
                </div>
                
                <div class="stat-card stat-card-purple fade-in">
                    <div class="stat-value"><?php echo $total_responses; ?></div>
                    <div class="stat-label">Réponses totales</div>
                </div>
            </div>

            <div class="quiz-list" id="quiz-list">
                <?php foreach ($user_quizzes as $quiz): ?>
                    <div class="quiz-item fade-in" data-status="<?php echo $quiz['status']; ?>" data-title="<?php echo htmlspecialchars($quiz['title']); ?>">
                        <div class="quiz-info">
                            <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                            <p style="color: var(--text-light); margin: 0.5rem 0;"><?php echo htmlspecialchars($quiz['description'] ?: 'Pas de description'); ?></p>
                            <div class="quiz-meta">
                                <span class="badge badge-<?php echo $quiz['status']; ?>">
                                    <?php
                                    $status_labels = [
                                        'draft' => '📝 Brouillon',
                                        'active' => '✅ Actif',
                                        'completed' => '🏁 Terminé',
                                        'inactive' => '❌ Inactif'
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
                                <a href="edit_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-secondary">✏️ Modifier</a>
                            <?php else: ?>
                                <a href="view_results.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary">📊 Résultats</a>
                                <button class="btn btn-outline" onclick="copyToClipboard('<?php echo BASE_URL; ?>user/join.php?link=<?php echo $quiz['share_link']; ?>')">📋 Copier lien</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        function filterQuizzes() {
            const searchValue = document.getElementById('search-quiz').value.toLowerCase();
            const statusFilter = document.getElementById('filter-status').value;
            const quizItems = document.querySelectorAll('.quiz-item');
            
            quizItems.forEach(item => {
                const title = item.getAttribute('data-title').toLowerCase();
                const status = item.getAttribute('data-status');
                
                const matchesSearch = title.includes(searchValue);
                const matchesStatus = !statusFilter || status === statusFilter;
                
                if (matchesSearch && matchesStatus) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>