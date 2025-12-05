<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/security.php';
require_once '../includes/functions.php';

if (!isset($_GET['link'])) {
    header('Location: ../index.php');
    exit();
}

$share_link = sanitize_input($_GET['link']);
$quiz = get_quiz_by_link($pdo, $share_link);

if (!$quiz || $quiz['status'] !== 'active') {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Quiz introuvable - <?php echo SITE_NAME; ?></title>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>
    <body>
        <div class="container" style="display: flex; justify-content: center; align-items: center; min-height: 100vh;">
            <div class="card fade-in" style="max-width: 500px; text-align: center;">
                <div class="empty-state-icon" style="font-size: 4rem;">🔒</div>
                <h2>Quiz introuvable</h2>
                <p style="color: var(--text-gray); margin: 1rem 0;">Ce quiz n'existe pas ou n'est plus disponible.</p>
                <a href="../index.php" class="btn btn-primary">Retour à l'accueil</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

if (isset($_SESSION['user_id'])) {
    if (check_user_completed_quiz($pdo, $_SESSION['user_id'], $quiz['id'])) {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Déjà complété - <?php echo SITE_NAME; ?></title>
            <link rel="stylesheet" href="../assets/css/style.css">
        </head>
        <body>
            <div class="container" style="display: flex; justify-content: center; align-items: center; min-height: 100vh;">
                <div class="card fade-in" style="max-width: 500px; text-align: center;">
                    <div class="empty-state-icon" style="font-size: 4rem;">✓</div>
                    <h2>Quiz déjà complété</h2>
                    <p style="color: var(--text-gray); margin: 1rem 0;">Vous avez déjà répondu à ce quiz.</p>
                    <a href="dashboard.php" class="btn btn-primary">Retour au dashboard</a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
    
    header('Location: take_quiz.php?link=' . $share_link);
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .quiz-join-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem;
        }
        
        .quiz-join-card {
            max-width: 600px;
            width: 100%;
            text-align: center;
            animation: slideUp 0.6s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .quiz-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .quiz-title {
            font-size: 2.5rem;
            font-weight: 900;
            margin: 1rem 0;
            background: linear-gradient(135deg, var(--kahoot-purple), var(--kahoot-blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .quiz-description {
            color: var(--text-gray);
            font-size: 1.1rem;
            margin: 1rem 0 2rem 0;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .big-btn {
            padding: 1.25rem 3rem;
            font-size: 1.2rem;
            font-weight: 900;
            border-radius: 16px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }
        
        .big-btn:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: var(--shadow-hover);
        }
    </style>
</head>
<body>
    <div class="quiz-join-container">
        <div class="quiz-join-card card">
            <div class="quiz-icon">🎯</div>
            <h1 class="quiz-title"><?php echo htmlspecialchars($quiz['title']); ?></h1>
            <?php if ($quiz['description']): ?>
                <p class="quiz-description"><?php echo htmlspecialchars($quiz['description']); ?></p>
            <?php endif; ?>
            
            <div class="stat-card stat-card-purple" style="max-width: 300px; margin: 2rem auto;">
                <div class="stat-label">Type de quiz</div>
                <div class="stat-value" style="font-size: 1.5rem;">
                    <?php echo $quiz['quiz_type'] === 'school' ? 'Quiz noté' : 'Questionnaire'; ?>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="../auth/login.php?redirect=<?php echo urlencode('user/join.php?link=' . $share_link); ?>" class="big-btn btn-primary">
                    Connexion
                </a>
                <a href="../auth/register.php?redirect=<?php echo urlencode('user/join.php?link=' . $share_link); ?>" class="big-btn btn-secondary">
                    Inscription
                </a>
            </div>
            
            <p style="margin-top: 2rem; color: var(--text-gray); font-size: 0.9rem;">
                Connectez-vous ou inscrivez-vous pour commencer le quiz
            </p>
        </div>
    </div>
</body>
</html>
