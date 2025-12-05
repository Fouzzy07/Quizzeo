<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/security.php';
require_once '../includes/functions.php';

check_role(['user']);

if (!isset($_GET['link'])) {
    header('Location: dashboard.php');
    exit();
}

$quiz = get_quiz_by_link($pdo, $_GET['link']);

if (!$quiz) {
    $error = "Quiz introuvable.";
} elseif ($quiz['status'] !== 'active') {
    $error = "Ce quiz n'est plus disponible.";
} elseif (check_user_completed_quiz($pdo, $_SESSION['user_id'], $quiz['id'])) {
    $error = "Vous avez déjà répondu à ce quiz.";
}

if (isset($error)) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erreur - <?php echo SITE_NAME; ?></title>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>
    <body>
        <div class="container" style="max-width: 600px; margin-top: 5rem;">
            <div class="card fade-in">
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <a href="dashboard.php" class="btn btn-primary">Retour au dashboard</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

$questions = get_questions_by_quiz($pdo, $quiz['id']);

$questions_with_answers = [];
foreach ($questions as $question) {
    $answers = get_answers_by_question($pdo, $question['id']);
    $questions_with_answers[] = [
        'question' => $question,
        'answers' => $answers
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_answers = [];
    
    foreach ($_POST['answers'] as $question_id => $answer) {
        $user_answers[$question_id] = $answer;
    }
    
    $response_id = submit_quiz_response($pdo, $quiz['id'], $_SESSION['user_id'], $user_answers);
    
    if ($response_id) {
        log_activity($pdo, $_SESSION['user_id'], 'complete_quiz', "Quiz ID: {$quiz['id']}");
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Erreur lors de la soumission.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <a href="dashboard.php" class="navbar-brand"><img src="../assets/images/logo.webp" alt="Quizzeo" class="navbar-logo"></a>
            <div class="navbar-menu">
                <span class="navbar-link" style="cursor: default;"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="../auth/logout.php" class="navbar-link">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container" style="max-width: 900px;">
        <div class="card fade-in" style="margin-top: 2rem;">
            <div class="card-header">
                <h2 class="card-title"><?php echo htmlspecialchars($quiz['title']); ?></h2>
                <?php if ($quiz['description']): ?>
                    <p style="color: var(--text-light); margin-top: 0.5rem;"><?php echo htmlspecialchars($quiz['description']); ?></p>
                <?php endif; ?>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <?php foreach ($questions_with_answers as $index => $q_data): ?>
                    <div class="question-block fade-in">
                        <div class="question-header">
                            <h4>Question <?php echo $index + 1; ?></h4>
                            <?php if ($quiz['quiz_type'] === 'school' && $q_data['question']['points'] > 0): ?>
                                <span class="badge badge-active"><?php echo $q_data['question']['points']; ?> points</span>
                            <?php endif; ?>
                        </div>
                        
                        <p style="font-size: 1.1rem; margin-bottom: 1.5rem;"><?php echo htmlspecialchars($q_data['question']['question_text']); ?></p>
                        
                        <?php if ($q_data['question']['question_type'] === 'mcq'): ?>
                            <?php foreach ($q_data['answers'] as $answer): ?>
                                <label class="answer-option">
                                    <input type="radio" name="answers[<?php echo $q_data['question']['id']; ?>]" value="<?php echo $answer['id']; ?>" required>
                                    <span><?php echo htmlspecialchars($answer['answer_text']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <textarea name="answers[<?php echo $q_data['question']['id']; ?>]" class="form-control" rows="4" required placeholder="Votre réponse..."></textarea>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="padding: 1rem 3rem; font-size: 1.1rem;">Soumettre mes réponses</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>