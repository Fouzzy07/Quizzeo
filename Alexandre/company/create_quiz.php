<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/security.php';
require_once '../includes/functions.php';

check_role(['company']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    
    if (empty($title)) {
        $error = "Le titre est obligatoire.";
    } else {
        $quiz_id = create_quiz($pdo, $_SESSION['user_id'], $title, $description, 'company');
        
        if ($quiz_id && isset($_POST['questions'])) {
            foreach ($_POST['questions'] as $index => $question_data) {
                $question_text = sanitize_input($question_data['text']);
                $question_type = $question_data['type'];
                $points = 0;
                
                $question_id = add_question($pdo, $quiz_id, $question_text, $question_type, $points, $index);
                
                if ($question_type === 'mcq' && isset($question_data['answers'])) {
                    foreach ($question_data['answers'] as $answer_index => $answer_text) {
                        add_answer($pdo, $question_id, sanitize_input($answer_text), 0);
                    }
                }
            }
            
            if (isset($_POST['publish'])) {
                update_quiz_status($pdo, $quiz_id, 'active');
            }
            
            log_activity($pdo, $_SESSION['user_id'], 'create_quiz', "Quiz ID: $quiz_id");
            header('Location: mes_quiz.php?created=1');
            exit();
        } else {
            $error = "Erreur lors de la création du questionnaire.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Questionnaire - <?php echo SITE_NAME; ?></title>
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
        <h1 style="margin: 2rem 0;">Créer un Questionnaire de Satisfaction</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card fade-in">
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Titre du questionnaire *</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <hr style="margin: 2rem 0;">
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3>Questions</h3>
                    <button type="button" class="btn btn-secondary" onclick="addQuestion()">Ajouter une question</button>
                </div>
                
                <div id="questions-container"></div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" name="draft" class="btn btn-outline">Sauvegarder en brouillon</button>
                    <button type="submit" name="publish" class="btn btn-primary">Publier le questionnaire</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            addQuestion();
        });
    </script>
</body>
</html>