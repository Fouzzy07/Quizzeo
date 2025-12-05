<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/security.php';
require_once '../includes/functions.php';

check_role(['admin']);

$stats = get_dashboard_stats($pdo);
$all_users = get_all_users($pdo);
$all_quizzes = get_all_quizzes($pdo);

if (isset($_GET['action']) && isset($_GET['type']) && isset($_GET['id'])) {
    if ($_GET['action'] === 'toggle') {
        $id = (int)$_GET['id'];
        $status = $_GET['status'];
        
        if ($_GET['type'] === 'user' && in_array($status, ['active', 'inactive'])) {
            toggle_user_status($pdo, $id, $status);
            log_activity($pdo, $_SESSION['user_id'], 'toggle_user_status', "User ID: $id, Status: $status");
        } elseif ($_GET['type'] === 'quiz' && in_array($status, ['active', 'inactive'])) {
            toggle_quiz_status($pdo, $id, $status);
            log_activity($pdo, $_SESSION['user_id'], 'toggle_quiz_status', "Quiz ID: $id, Status: $status");
        }
        
        header('Location: dashboard.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <a href="dashboard.php" class="navbar-brand"><img src="../assets/images/logo.webp" alt="Quizzeo" class="navbar-logo"></a>
            <div class="navbar-menu">
                <a href="dashboard.php" class="navbar-link">Dashboard</a>
                <span class="navbar-link" style="cursor: default;">Admin: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="../auth/logout.php" class="navbar-link">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 style="margin: 2rem 0;">Tableau de bord Administrateur</h1>
        
        <div class="stats-grid">
            <div class="stat-card stat-card-purple fade-in">
                <div class="stat-value"><?php echo $stats['total_users']; ?></div>
                <div class="stat-label">Utilisateurs Totaux</div>
            </div>
            
            <div class="stat-card stat-card-pink fade-in">
                <div class="stat-value"><?php echo $stats['active_users']; ?></div>
                <div class="stat-label">Utilisateurs Actifs</div>
            </div>
            
            <div class="stat-card stat-card-orange fade-in">
                <div class="stat-value"><?php echo $stats['total_quizzes']; ?></div>
                <div class="stat-label">Quiz Créés</div>
            </div>
            
            <div class="stat-card stat-card-purple fade-in">
                <div class="stat-value"><?php echo $stats['active_quizzes']; ?></div>
                <div class="stat-label">Quiz Actifs</div>
            </div>
            
            <div class="stat-card stat-card-pink fade-in">
                <div class="stat-value"><?php echo $stats['total_responses']; ?></div>
                <div class="stat-label">Réponses Totales</div>
            </div>
        </div>

        <div class="card fade-in">
            <div class="card-header">
                <h2 class="card-title">Gestion des Utilisateurs</h2>
            </div>
            
            <div class="form-group">
                <input type="text" id="search-users" class="form-control" placeholder="Rechercher un utilisateur..." onkeyup="filterTable('search-users', 'users-table')">
            </div>
            
            <div style="overflow-x: auto;">
                <table class="table" id="users-table">
                    <thead>
                        <tr>
                            <th onclick="sortTable('users-table', 0)">ID</th>
                            <th onclick="sortTable('users-table', 1)">Utilisateur</th>
                            <th onclick="sortTable('users-table', 2)">Email</th>
                            <th onclick="sortTable('users-table', 3)">Rôle</th>
                            <th onclick="sortTable('users-table', 4)">Statut</th>
                            <th onclick="sortTable('users-table', 5)">Date création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_users as $user): ?>
                            <?php if ($user['role'] !== 'admin'): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php
                                        $role_labels = [
                                            'school' => 'École',
                                            'company' => 'Entreprise',
                                            'user' => 'Utilisateur'
                                        ];
                                        echo $role_labels[$user['role']] ?? $user['role'];
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $user['status']; ?>">
                                            <?php echo $user['status'] === 'active' ? 'Actif' : 'Inactif'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo format_date($user['created_at']); ?></td>
                                    <td>
                                        <button class="btn <?php echo $user['status'] === 'active' ? 'btn-danger' : 'btn-success'; ?>" 
                                                onclick="toggleStatus('user', <?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')">
                                            <?php echo $user['status'] === 'active' ? 'Désactiver' : 'Activer'; ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card fade-in">
            <div class="card-header">
                <h2 class="card-title">Gestion des Quiz</h2>
            </div>
            
            <div class="form-group">
                <input type="text" id="search-quizzes" class="form-control" placeholder="Rechercher un quiz..." onkeyup="filterTable('search-quizzes', 'quizzes-table')">
            </div>
            
            <div style="overflow-x: auto;">
                <table class="table" id="quizzes-table">
                    <thead>
                        <tr>
                            <th onclick="sortTable('quizzes-table', 0)">ID</th>
                            <th onclick="sortTable('quizzes-table', 1)">Titre</th>
                            <th onclick="sortTable('quizzes-table', 2)">Créateur</th>
                            <th onclick="sortTable('quizzes-table', 3)">Type</th>
                            <th onclick="sortTable('quizzes-table', 4)">Statut</th>
                            <th onclick="sortTable('quizzes-table', 5)">Réponses</th>
                            <th onclick="sortTable('quizzes-table', 6)">Date création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_quizzes as $quiz): ?>
                            <tr>
                                <td><?php echo $quiz['id']; ?></td>
                                <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                                <td><?php echo htmlspecialchars($quiz['creator_name']); ?></td>
                                <td>
                                    <?php echo $quiz['quiz_type'] === 'school' ? 'École' : 'Entreprise'; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $quiz['status']; ?>">
                                        <?php
                                        $status_labels = [
                                            'draft' => 'Brouillon',
                                            'active' => 'Actif',
                                            'completed' => 'Terminé',
                                            'inactive' => 'Inactif'
                                        ];
                                        echo $status_labels[$quiz['status']] ?? $quiz['status'];
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo $quiz['response_count']; ?></td>
                                <td><?php echo format_date($quiz['created_at']); ?></td>
                                <td>
                                    <?php if ($quiz['status'] !== 'draft'): ?>
                                        <button class="btn <?php echo $quiz['status'] === 'active' ? 'btn-danger' : 'btn-success'; ?>" 
                                                onclick="toggleStatus('quiz', <?php echo $quiz['id']; ?>, '<?php echo $quiz['status'] === 'active' ? 'inactive' : 'active'; ?>')">
                                            <?php echo $quiz['status'] === 'active' ? 'Désactiver' : 'Activer'; ?>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>