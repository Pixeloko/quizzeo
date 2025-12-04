# Quizzeo

## Description
Plateforme de quiz éducatifs avec système de création et participation. Permet aux écoles de créer des quiz et aux étudiants d'y répondre.

## Architecture MVC

```
quizzeo/
├── index.php                    
├── Controller/                  
│   ├── submit_quiz.php         
│   ├── admin.php         
│   ├── create_account.php         
│   ├── ecole.php         
│   ├── entreprise.php         
│   ├── home.php         
│   ├── question.php         
│   ├── store_entreprise.php         
│   ├── store.php         
│   └── create_quiz.php         
├── Model/                     
│   ├── function_quizz.php     
│   ├── functions_quizz.php     
│   ├── function_question.php   
│   ├── function_quizz_question.php   
│   ├── function_user.php       
└── config/
│       └── config.php    
└── uploads     
└── assets    
│       └── style.css  
│       └── default-profile.png  
│       └── logo.png  
└── View/                       
│    ├── ecole/                  
│    │   ├── dashboard.php      
│    │   ├── create_quizz.php    
│    │   ├── edit_question.php    
│    │   ├── edit_quiz.php      
│    │   └── results.php         
│    ├── user/                   
│    │   ├── dashboard.php      
│    │   ├── available_quiz.php      #non utilisé 
│    │   └──play_quiz.php           #non utilisé                           
│    ├── login.php
│    ├── admin.php
│    ├── create_account.php
│    ├── create_question.php
│    ├── create_quizz.php
│    ├── home.php
│    ├── profile.php
│    ├── question.php
│    └── quizz.php
│
└──.gitignore
└──.htaccess

```

## Installation

### 1. Cloner le projet
```bash
git clone https://github.com/Pixeloko/quizzeo
cd quizzeo
```

### 2. Configurer la base de données

#### Créer la base et les tables :
```sql
CREATE DATABASE quizzeo_db;
USE quizzeo_db;

-- USERS
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role ENUM('user', 'ecole', 'admin') DEFAULT 'user',
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
    type ENUM('qcm', 'free') DEFAULT 'qcm',
    FOREIGN KEY (quizz_id) REFERENCES quizz(id) ON DELETE CASCADE
);

-- ANSWERS
CREATE TABLE answers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question_id INT NOT NULL,
    answer_text VARCHAR(255) NOT NULL,
    is_correct BOOL DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

-- QUIZZ RESPONSES
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
    given_answer VARCHAR(255) NULL,
    is_correct BOOL DEFAULT NULL,
    FOREIGN KEY (quizz_user_id) REFERENCES quizz_user(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    FOREIGN KEY (answer_id) REFERENCES answers(id) ON DELETE CASCADE
);
```

#### Insérer un administrateur :
```sql
INSERT INTO users (role, firstname, lastname, email, password)
VALUES ('admin', 'Admin', 'Admin', 'admin@quizzeo.com', '$2y$10$VotreHashPassword');
```

### 3. Configurer l'application

Copier le fichier de configuration :
```bash
cp Model/config/config.example.php Model/config/config.php
```

Éditer `Model/config/config.php` avec vos informations :
```php
<?php
function getDatabase() {
    $host = 'localhost';
    $dbname = 'quizzeo_db';
    $username = 'root';      // ← Modifier si nécessaire
    $password = 'root';      // ← Modifier si nécessaire (MAMP)
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Erreur de connexion : " . $e->getMessage());
    }
}
```

### 4. Accéder à l'application

Ouvrir dans le navigateur :
```
http://localhost/quizzeo/
```

## Rôles et Accès

### Comptes par défaut (après import des données de test) :

**Administrateur :**
- Email : `admin@quizzeo.com`
- Mot de passe : `Admin123!`

**École (Lycée Descartes) :**
- Email : `lycee.descartes@edu.fr`
- Mot de passe : `Admin123!`

**Étudiant :**
- Email : `marie.dupont@etudiant.fr`
- Mot de passe : `Etudiant123!`

## Fonctionnalités

### Pour les Écoles
- ✅ Création de quiz avec questions/réponses
- ✅ Gestion des statuts (brouillon/lancé/terminé)
- ✅ Partage de quiz via lien unique
- ✅ Visualisation des résultats
- ✅ Export CSV des résultats

### Pour les Étudiants
- ✅ Inscription/Connexion
- ✅ Liste des quiz disponibles
- ✅ Participation aux quiz
- ✅ Tableau de bord avec statistiques
- ✅ Historique des scores
