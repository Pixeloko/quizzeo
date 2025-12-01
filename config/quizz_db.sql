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

CREATE TABLE questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL UNIQUE,
    answer VARCHAR(255) NOT NULL,
    point INT NOT NULL DEFAULT 1
);

CREATE TABLE quizz (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    is_active BOOL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE quizz_questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quizz_id INT NOT NULL,
    question_id INT NOT NULL,
    FOREIGN KEY (quizz_id) REFERENCES quizz(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    UNIQUE(quizz_id, question_id)
);

CREATE TABLE quizz_user (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quizz_id INT NOT NULL,
    user_id INT NOT NULL,
    score INT DEFAULT 0,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (quizz_id) REFERENCES quizz(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(quizz_id, user_id)
);


INSERT INTO users(role, firstname, lastname, email, password)
VALUES ('admin', 'Admin', 'admin','admin@gmail.com', '$2y$10$Wh19n.Mm.65eWU4ZfmlexOqPHJvNV7jMJlb1h0PLpEjl.GtgR8pWC');