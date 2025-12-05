<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/security.php';
require_once '../includes/functions.php';

check_role(['company']);

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$quiz_id = (int)$_GET['id'];
$quiz = get_quiz_by_id($pdo, $quiz_id);

if (!$quiz || $quiz['creator_id'] != $_SESSION['user_id'] || $quiz['status'] !== 'draft') {
    header('Location: dashboard.php');
    exit();
}

$questions = get_questions_by_quiz($pdo, $quiz_id);
$questions_with_answers = [];

foreach ($questions as $question) {
    $answers = get_answers_by_question($pdo, $question['id']);
    $questions_with_answers[] = [
        'question' => $question,
        'answers' => $answers
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    
    $stmt = $pdo->prepare("UPDATE quizzes SET title = ?, description = ? WHERE id = ?");
    $stmt->execute([$title, $description, $quiz_id]);
    
    if (isset($_POST['publish'])) {
        update_quiz_status($pdo, $quiz_id, 'active');
        header('Location: dashboard.php');
        exit();
    } else {
        header('Location: edit_quiz.php?id=' . $quiz_id);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Questionnaire - <?php echo SITE_NAME; ?></title>
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
        <div style="margin: 2rem 0;">
            <a href="dashboard.php" class="btn btn-outline">← Retour</a>
        </div>

        <div class="card fade-in">
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Titre du questionnaire *</label>
                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($quiz['title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($quiz['description']); ?></textarea>
                </div>
                
                <hr style="margin: 2rem 0;">
                
                <h3>Questions existantes</h3>
                
                <?php foreach ($questions_with_answers as $index => $q_data): ?>
                    <div class="question-block">
                        <h4>Question <?php echo $index + 1; ?></h4>
                        <p><?php echo htmlspecialchars($q_data['question']['question_text']); ?></p>
                        
                        <?php if ($q_data['question']['question_type'] === 'mcq'): ?>
                            <ul style="margin-top: 1rem;">
                                <?php foreach ($q_data['answers'] as $answer): ?>
                                    <li style="margin: 0.5rem 0;">
                                        <?php echo htmlspecialchars($answer['answer_text']); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p style="color: var(--text-light); font-style: italic;">Réponse libre</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" name="update" class="btn btn-outline">Sauvegarder les modifications</button>
                    <button type="submit" name="publish" class="btn btn-primary">Publier le questionnaire</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>