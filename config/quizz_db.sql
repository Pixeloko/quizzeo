-- USERS
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role ENUM('user', 'ecole', 'entreprise', 'admin') DEFAULT 'user',
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_active BOOL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- QUIZZ
CREATE TABLE quizz (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    user_id INT NOT NULL,
    status ENUM('draft', 'launched', 'finished') DEFAULT 'draft',
    is_active BOOL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- QUESTIONS
CREATE TABLE questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quizz_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    point INT DEFAULT 1,
    FOREIGN KEY (quizz_id) REFERENCES quizz(id) ON DELETE CASCADE
);

-- ANSWERS (QCM ou libre)
CREATE TABLE answers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question_id INT NOT NULL,
    answer_text VARCHAR(255) NOT NULL,
    is_correct BOOL DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

-- QUIZZ RESPONSES (pour les statistiques / corrections)
CREATE TABLE quizz_user (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quizz_id INT NOT NULL,
    user_id INT NOT NULL,
    score INT DEFAULT 0,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (quizz_id) REFERENCES quizz(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(quizz_id, user_id)
);

-- USER ANSWERS
CREATE TABLE user_answers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quizz_user_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_id INT NULL,
    given_answer VARCHAR(255) NULL, -- pour les réponses libres
    is_correct BOOL DEFAULT NULL,
    FOREIGN KEY (quizz_user_id) REFERENCES quizz_user(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    FOREIGN KEY (answer_id) REFERENCES answers(id) ON DELETE CASCADE
);
select * from users;
INSERT INTO users(role, firstname, lastname, email, password)
VALUES ('admin', 'Admin', 'admin','admin@gmail.com', '$2y$10$Wh19n.Mm.65eWU4ZfmlexOqPHJvNV7jMJlb1h0PLpEjl.GtgR8pWC');
ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) DEFAULT NULL;

ALTER TABLE questions
ADD COLUMN type ENUM('qcm', 'free') NOT NULL DEFAULT 'qcm';

SELECT *FROM users;
SELECT *FROM quizz;