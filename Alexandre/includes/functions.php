<?php
function get_user_by_id($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function get_user_by_email($pdo, $email) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function get_user_by_username($pdo, $username) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

function create_user($pdo, $username, $email, $password, $role, $first_name = '', $last_name = '') {
    $hashed_password = hash_password($password);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, first_name, last_name) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$username, $email, $hashed_password, $role, $first_name, $last_name]);
}

function update_user($pdo, $user_id, $data) {
    $allowed_fields = ['first_name', 'last_name', 'email', 'password'];
    $fields = [];
    $values = [];
    
    foreach ($data as $key => $value) {
        if (in_array($key, $allowed_fields)) {
            if ($key === 'password') {
                $value = hash_password($value);
            }
            $fields[] = "$key = ?";
            $values[] = $value;
        }
    }
    
    if (empty($fields)) {
        return false;
    }
    
    $values[] = $user_id;
    $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($values);
}

function toggle_user_status($pdo, $user_id, $status) {
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $user_id]);
}

function get_all_users($pdo, $role = null) {
    if ($role) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE role = ? ORDER BY created_at DESC");
        $stmt->execute([$role]);
    } else {
        $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    }
    return $stmt->fetchAll();
}

function create_quiz($pdo, $creator_id, $title, $description, $quiz_type) {
    $share_link = generate_token(16);
    $stmt = $pdo->prepare("INSERT INTO quizzes (creator_id, title, description, quiz_type, share_link) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$creator_id, $title, $description, $quiz_type, $share_link]);
    return $pdo->lastInsertId();
}

function get_quiz_by_id($pdo, $quiz_id) {
    $stmt = $pdo->prepare("SELECT q.*, u.username as creator_name FROM quizzes q JOIN users u ON q.creator_id = u.id WHERE q.id = ?");
    $stmt->execute([$quiz_id]);
    return $stmt->fetch();
}

function get_quiz_by_link($pdo, $link) {
    $stmt = $pdo->prepare("SELECT q.*, u.username as creator_name FROM quizzes q JOIN users u ON q.creator_id = u.id WHERE q.share_link = ?");
    $stmt->execute([$link]);
    return $stmt->fetch();
}

function get_quizzes_by_creator($pdo, $creator_id) {
    $stmt = $pdo->prepare("SELECT q.*, COUNT(DISTINCT qr.id) as response_count FROM quizzes q LEFT JOIN quiz_responses qr ON q.id = qr.quiz_id WHERE q.creator_id = ? GROUP BY q.id ORDER BY q.created_at DESC");
    $stmt->execute([$creator_id]);
    return $stmt->fetchAll();
}

function get_all_quizzes($pdo) {
    $stmt = $pdo->query("SELECT q.*, u.username as creator_name, COUNT(DISTINCT qr.id) as response_count FROM quizzes q LEFT JOIN users u ON q.creator_id = u.id LEFT JOIN quiz_responses qr ON q.id = qr.quiz_id GROUP BY q.id ORDER BY q.created_at DESC");
    return $stmt->fetchAll();
}

function update_quiz_status($pdo, $quiz_id, $status) {
    $stmt = $pdo->prepare("UPDATE quizzes SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $quiz_id]);
}

function toggle_quiz_status($pdo, $quiz_id, $status) {
    $stmt = $pdo->prepare("UPDATE quizzes SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $quiz_id]);
}

function add_question($pdo, $quiz_id, $question_text, $question_type, $points, $order) {
    $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, question_type, points, question_order) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$quiz_id, $question_text, $question_type, $points, $order]);
    return $pdo->lastInsertId();
}

function get_questions_by_quiz($pdo, $quiz_id) {
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY question_order ASC");
    $stmt->execute([$quiz_id]);
    return $stmt->fetchAll();
}

function delete_question($pdo, $question_id) {
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    return $stmt->execute([$question_id]);
}

function add_answer($pdo, $question_id, $answer_text, $is_correct = false) {
    $is_correct_int = $is_correct ? 1 : 0;
    $stmt = $pdo->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
    return $stmt->execute([$question_id, $answer_text, $is_correct_int]);
}

function get_answers_by_question($pdo, $question_id) {
    $stmt = $pdo->prepare("SELECT * FROM answers WHERE question_id = ?");
    $stmt->execute([$question_id]);
    return $stmt->fetchAll();
}

function submit_quiz_response($pdo, $quiz_id, $user_id, $answers) {
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO quiz_responses (quiz_id, user_id) VALUES (?, ?)");
        $stmt->execute([$quiz_id, $user_id]);
        $response_id = $pdo->lastInsertId();
        
        $total_score = 0;
        
        foreach ($answers as $question_id => $answer_data) {
            $question = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
            $question->execute([$question_id]);
            $q = $question->fetch();
            
            $points_earned = 0;
            $is_correct = 0;
            $answer_id = null;
            $answer_text = null;
            
            if ($q['question_type'] === 'mcq') {
                $answer_id = $answer_data;
                $correct_answer = $pdo->prepare("SELECT * FROM answers WHERE id = ? AND is_correct = 1");
                $correct_answer->execute([$answer_id]);
                if ($correct_answer->fetch()) {
                    $is_correct = 1;
                    $points_earned = $q['points'];
                }
            } else {
                $answer_text = $answer_data;
            }
            
            $stmt = $pdo->prepare("INSERT INTO user_answers (response_id, question_id, answer_id, answer_text, is_correct, points_earned) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$response_id, $question_id, $answer_id, $answer_text, $is_correct, $points_earned]);
            
            $total_score += $points_earned;
        }
        
        $stmt = $pdo->prepare("UPDATE quiz_responses SET total_score = ? WHERE id = ?");
        $stmt->execute([$total_score, $response_id]);
        
        $pdo->commit();
        return $response_id;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

function get_quiz_responses($pdo, $quiz_id) {
    $stmt = $pdo->prepare("SELECT qr.*, u.username, u.first_name, u.last_name FROM quiz_responses qr JOIN users u ON qr.user_id = u.id WHERE qr.quiz_id = ? ORDER BY qr.completed_at DESC");
    $stmt->execute([$quiz_id]);
    return $stmt->fetchAll();
}

function get_user_quiz_responses($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT qr.*, q.title, q.quiz_type FROM quiz_responses qr JOIN quizzes q ON qr.quiz_id = q.id WHERE qr.user_id = ? ORDER BY qr.completed_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function get_response_details($pdo, $response_id) {
    $stmt = $pdo->prepare("SELECT ua.*, q.question_text, q.question_type, a.answer_text as correct_answer FROM user_answers ua JOIN questions q ON ua.question_id = q.id LEFT JOIN answers a ON ua.answer_id = a.id WHERE ua.response_id = ? ORDER BY q.question_order");
    $stmt->execute([$response_id]);
    return $stmt->fetchAll();
}

function get_quiz_statistics($pdo, $quiz_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_responses, AVG(total_score) as average_score, MAX(total_score) as max_score, MIN(total_score) as min_score FROM quiz_responses WHERE quiz_id = ?");
    $stmt->execute([$quiz_id]);
    return $stmt->fetch();
}

function get_answer_statistics($pdo, $quiz_id) {
    $stmt = $pdo->prepare("
        SELECT q.id, q.question_text, a.id as answer_id, a.answer_text, 
               COUNT(ua.id) as selection_count,
               (COUNT(ua.id) * 100.0 / (SELECT COUNT(*) FROM quiz_responses WHERE quiz_id = ?)) as percentage
        FROM questions q
        JOIN answers a ON q.id = a.question_id
        LEFT JOIN user_answers ua ON a.id = ua.answer_id
        WHERE q.quiz_id = ?
        GROUP BY q.id, a.id
        ORDER BY q.question_order, a.id
    ");
    $stmt->execute([$quiz_id, $quiz_id]);
    return $stmt->fetchAll();
}

function format_date($date) {
    return date('d/m/Y H:i', strtotime($date));
}

function get_dashboard_stats($pdo) {
    $stats = [];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $stats['total_users'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
    $stats['active_users'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM quizzes");
    $stats['total_quizzes'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM quizzes WHERE status = 'active'");
    $stats['active_quizzes'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM quiz_responses");
    $stats['total_responses'] = $stmt->fetch()['total'];
    
    return $stats;
}

function check_user_completed_quiz($pdo, $user_id, $quiz_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM quiz_responses WHERE user_id = ? AND quiz_id = ?");
    $stmt->execute([$user_id, $quiz_id]);
    $result = $stmt->fetch();
    return $result['count'] > 0;
}
?>