CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role ENUM('user', 'ecole', 'entreprise', 'admin') DEFAULT 'user',
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    answer VARCHAR(255) NOT NULL,
    point INT NOT NULL
);

CREATE TABLE quizz (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE quizz_questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quizz_id INT NOT NULL,
    question_id INT NOT NULL,
    FOREIGN KEY (quizz_id) REFERENCES quizz(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    UNIQUE(quizz_id, question_id) -- Evite doublons mêmes questions dans un quizz
);

INSERT INTO users(role, username, email, password)
VALUES ('admin', 'Admin', 'admin@gmail.com', '$2y$10$Wh19n.Mm.65eWU4ZfmlexOqPHJvNV7jMJlb1h0PLpEjl.GtgR8pWC');