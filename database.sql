CREATE DATABASE IF NOT EXISTS quizzeo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quizzeo;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS user_answers;
DROP TABLE IF EXISTS quiz_responses;
DROP TABLE IF EXISTS answers;
DROP TABLE IF EXISTS questions;
DROP TABLE IF EXISTS quizzes;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'school', 'company', 'user') NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    creator_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    quiz_type ENUM('school', 'company') NOT NULL,
    status ENUM('draft', 'active', 'completed', 'inactive') DEFAULT 'draft',
    share_link VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_creator (creator_id),
    INDEX idx_status (status),
    INDEX idx_share_link (share_link),
    INDEX idx_quiz_type (quiz_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('mcq', 'free_text') NOT NULL,
    points INT DEFAULT 0,
    question_order INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    INDEX idx_quiz (quiz_id),
    INDEX idx_order (question_order),
    INDEX idx_type (question_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    answer_text TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    INDEX idx_question (question_id),
    INDEX idx_correct (is_correct)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE quiz_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    user_id INT NOT NULL,
    total_score DECIMAL(5,2) DEFAULT 0,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_quiz (quiz_id),
    INDEX idx_user (user_id),
    INDEX idx_completed (completed_at),
    UNIQUE KEY unique_user_quiz (quiz_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    response_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_id INT,
    answer_text TEXT,
    is_correct BOOLEAN DEFAULT FALSE,
    points_earned DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (response_id) REFERENCES quiz_responses(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    FOREIGN KEY (answer_id) REFERENCES answers(id) ON DELETE SET NULL,
    INDEX idx_response (response_id),
    INDEX idx_question (question_id),
    INDEX idx_answer (answer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at),
    INDEX idx_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (username, email, password, role, first_name, last_name, status) VALUES
('admin', 'admin@quizzeo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin', 'Quizzeo', 'active');

INSERT INTO users (username, email, password, role, first_name, last_name, status) VALUES
('ecole_test', 'ecole@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'school', 'École', 'Test', 'active'),
('entreprise_test', 'entreprise@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'company', 'Entreprise', 'Test', 'active'),
('utilisateur_test', 'user@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'Utilisateur', 'Test', 'active');

INSERT INTO quizzes (creator_id, title, description, quiz_type, status, share_link) VALUES
(2, 'Quiz de Mathématiques', 'Quiz de test sur les mathématiques niveau collège', 'school', 'active', 'abc123def456');

INSERT INTO questions (quiz_id, question_text, question_type, points, question_order) VALUES
(1, 'Combien font 2 + 2 ?', 'mcq', 5, 1),
(1, 'Quelle est la capitale de la France ?', 'mcq', 5, 2);

INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(1, '3', FALSE),
(1, '4', TRUE),
(1, '5', FALSE),
(1, '6', FALSE);

INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(2, 'Lyon', FALSE),
(2, 'Paris', TRUE),
(2, 'Marseille', FALSE),
(2, 'Bordeaux', FALSE);

INSERT INTO quizzes (creator_id, title, description, quiz_type, status, share_link) VALUES
(3, 'Satisfaction Clients', 'Questionnaire de satisfaction produit', 'company', 'active', 'xyz789uvw012');

INSERT INTO questions (quiz_id, question_text, question_type, points, question_order) VALUES
(2, 'Comment évaluez-vous notre service ?', 'mcq', 0, 1),
(2, 'Avez-vous des suggestions d\'amélioration ?', 'free_text', 0, 2);

INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(3, 'Très satisfait', FALSE),
(3, 'Satisfait', FALSE),
(3, 'Neutre', FALSE),
(3, 'Insatisfait', FALSE);