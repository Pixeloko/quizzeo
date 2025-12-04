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

--Ajouts
ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) DEFAULT NULL;

ALTER TABLE questions
ADD COLUMN type ENUM('qcm', 'free') NOT NULL DEFAULT 'qcm';

-- Utilisateur admin
INSERT INTO users(role, firstname, lastname, email, password)
VALUES ('admin', 'Admin', 'admin','admin@gmail.com', '$2y$10$Wh19n.Mm.65eWU4ZfmlexOqPHJvNV7jMJlb1h0PLpEjl.GtgR8pWC');

-- Écoles
INSERT INTO users (role, firstname, lastname, email, password, is_active) VALUES
('ecole', 'Lycée', 'Descartes', 'lycee.descartes@edu.fr', '$2y$10$Wh19n.Mm.65eWU4ZfmlexOqPHJvNV7jMJlb1h0PLpEjl.GtgR8pWC', 1),
('ecole', 'Université', 'Paris-Sorbonne', 'universite.paris@edu.fr', '$2y$10$Wh19n.Mm.65eWU4ZfmlexOqPHJvNV7jMJlb1h0PLpEjl.GtgR8pWC', 1),
('ecole', 'Collège', 'Victor Hugo', 'college.victorhugo@edu.fr', '$2y$10$Wh19n.Mm.65eWU4ZfmlexOqPHJvNV7jMJlb1h0PLpEjl.GtgR8pWC', 1)

-- Étudiants (mot de passe pour tous : Etudiant123!)
INSERT INTO users (role, firstname, lastname, email, password, is_active) VALUES
('user', 'Marie', 'Dupont', 'marie.dupont@etudiant.fr', '$2y$10$Wh19n.Mm.65eWU4ZfmlexOqPHJvNV7jMJlb1h0PLpEjl.GtgR8pWC', 1),
('user', 'Jean', 'Martin', 'jean.martin@etudiant.fr', '$2y$10$Wh19n.Mm.65eWU4ZfmlexOqPHJvNV7jMJlb1h0PLpEjl.GtgR8pWC', 1),
('user', 'Sophie', 'Bernard', 'sophie.bernard@etudiant.fr', '$2y$10$Wh19n.Mm.65eWU4ZfmlexOqPHJvNV7jMJlb1h0PLpEjl.GtgR8pWC', 1),
('user', 'Thomas', 'Petit', 'thomas.petit@etudiant.fr', '$2y$10$Wh19n.Mm.65eWU4ZfmlexOqPHJvNV7jMJlb1h0PLpEjl.GtgR8pWC', 1),
('user', 'Julie', 'Robert', 'julie.robert@etudiant.fr', '$2y$10$Wh19n.Mm.65eWU4ZfmlexOqPHJvNV7jMJlb1h0PLpEjl.GtgR8pWC', 1)

INSERT INTO quizz (name, user_id, status, is_active, created_at) VALUES
-- Quiz de l'école 2 (Lycée Descartes)
('Histoire de France - Niveau Terminale', 2, 'launched', 1, DATE_SUB(NOW(), INTERVAL 10 DAY)),
('Mathématiques - Géométrie', 2, 'launched', 1, DATE_SUB(NOW(), INTERVAL 7 DAY)),
('Sciences Physiques - Électricité', 2, 'draft', 1, DATE_SUB(NOW(), INTERVAL 3 DAY)),

-- Quiz de l'école 3 (Université Paris-Sorbonne)
('Philosophie - Les grands courants', 3, 'launched', 1, DATE_SUB(NOW(), INTERVAL 8 DAY)),
('Littérature Française - XIXe siècle', 3, 'finished', 1, DATE_SUB(NOW(), INTERVAL 15 DAY)),
('Histoire de lArt - Renaissance', 3, 'launched', 1, DATE_SUB(NOW(), INTERVAL 5 DAY)),

-- Quiz de l'école 4 (Collège Victor Hugo)
('Grammaire Française - 4ème', 4, 'launched', 1, DATE_SUB(NOW(), INTERVAL 6 DAY)),
('Géographie - Capitales du monde', 4, 'launched', 1, DATE_SUB(NOW(), INTERVAL 4 DAY)),
('Sciences de la Vie - Le corps humain', 4, 'draft', 1, DATE_SUB(NOW(), INTERVAL 2 DAY))

-- Questions pour le quiz 1 (Histoire de France)
INSERT INTO questions (quizz_id, title, point, type) VALUES
(1, 'En quelle année a eu lieu la Révolution Française ?', 2, 'qcm'),
(1, 'Qui était le roi de France au moment de la Révolution ?', 1, 'qcm'),
(1, 'Quel traité a mis fin à la Première Guerre mondiale ?', 1, 'qcm'),
(1, 'Citez deux inventions importantes du XIXe siècle en France :', 3, 'free');

-- Questions pour le quiz 2 (Mathématiques)
INSERT INTO questions (quizz_id, title, point, type) VALUES
(2, 'Quelle est la formule de l\'aire d\'un cercle ?', 2, 'qcm'),
(2, 'Un triangle rectangle a un angle de 90 degrés.', 1, 'qcm'),
(2, 'Calculez 15% de 200 :', 2, 'qcm')

-- Réponses pour la question 1 (Révolution Française)
INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(1, '1789', 1),
(1, '1799', 0),
(1, '1776', 0),
(1, '1815', 0);

-- Réponses pour la question 2 (Roi de France)
INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(2, 'Louis XVI', 1),
(2, 'Louis XIV', 0),
(2, 'Napoléon Bonaparte', 0),
(2, 'Charles de Gaulle', 0);

-- Réponses pour la question 3 (Traité WWI)
INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(3, 'Traité de Versailles', 1),
(3, 'Traité de Rome', 0),
(3, 'Traité de Maastricht', 0),
(3, 'Traité de Westphalie', 0);

-- Réponses pour la question 5 (Aire du cercle)
INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(5, 'πr²', 1),
(5, '2πr', 0),
(5, '4/3πr³', 0),
(5, 'πd', 0);

-- Réponses pour la question 6 (Triangle rectangle)
INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(6, 'Vrai', 1),
(6, 'Faux', 0);

-- Réponses pour la question 7 (Pourcentage)
INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(7, '30', 1),
(7, '15', 0),
(7, '20', 0),
(7, '25', 0);

--Résultats
-- Marie Dupont (user_id 5) a répondu au quiz 1 et 2
INSERT INTO quizz_user (quizz_id, user_id, score, completed_at) VALUES
(1, 5, 3, DATE_SUB(NOW(), INTERVAL 2 DAY)), -- 3/7 points
(2, 5, 5, DATE_SUB(NOW(), INTERVAL 1 DAY)); -- 5/5 points

-- Jean Martin (user_id 6) a répondu au quiz 1, 4 et 7
INSERT INTO quizz_user (quizz_id, user_id, score, completed_at) VALUES
(1, 6, 6, DATE_SUB(NOW(), INTERVAL 3 DAY)),  -- 6/7 points
(4, 6, 2, DATE_SUB(NOW(), INTERVAL 2 DAY)),  -- Philosophie
(7, 6, 8, DATE_SUB(NOW(), INTERVAL 1 DAY));  -- Grammaire

-- Sophie Bernard (user_id 7) a répondu au quiz 1
INSERT INTO quizz_user (quizz_id, user_id, score, completed_at) VALUES
(1, 7, 4, DATE_SUB(NOW(), INTERVAL 1 DAY)); -- 4/7 points