<?php     
require_once __DIR__ . '/../config/config.php';

// ---------- FONCTIONS UTILISATEURS ----------

function getUserByRole(string $role): ?array {
    $conn = getDatabase();
    $stmt = $conn->prepare("SELECT * FROM users WHERE role = :role");
    $stmt->execute(["role" => $role]);
    return $stmt->fetchAll() ?: null;
}

function getUserById(int $id): ?array {
    $conn = getDatabase();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(["id" => $id]);
    return $stmt->fetch() ?: null ;
}

// Model/function_user.php

/**
 * Récupérer tous les utilisateurs
 */
function fetchUsers() {
    $pdo = getDatabase();
    $sql = "SELECT id, firstname, lastname, email, role, is_active, created_at 
            FROM users 
            ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserByEmail(string $email): ?array {
    $conn = getDatabase();
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(["email" => $email]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function createUser(string $role, string $firstname, string $lastname, string $email, string $password): int {
    $conn = getDatabase();
    $stmt = $conn->prepare("INSERT INTO users(role, firstname, lastname, email, password, created_at) 
                            VALUES (:role, :firstname, :lastname, :email, :password, NOW())");
    $stmt->execute([
        'role' => $role,
        'firstname' => $firstname,
        'lastname' => $lastname,
        'email'     => $email,
        'password'  => password_hash($password, PASSWORD_DEFAULT)
    ]);
    return (int) $conn->lastInsertId();
}

function updateUser(int $id, string $firstname, string $lastname, string $email, ?string $password): bool {
    $conn = getDatabase();
    if ($password) {
        $stmt = $conn->prepare("UPDATE users SET firstname = :firstname, lastname = :lastname, email = :email, password = :password WHERE id = :id");
        $stmt->execute([
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'id'       => $id
        ]);
    } else {
        $stmt = $conn->prepare("UPDATE users SET firstname = :firstname, email = :email WHERE id = :id");
        $stmt->execute([
            'firstname' => $firstname,
            'email'    => $email,
            'id'       => $id
        ]);
    }
    return $stmt->rowCount() > 0;
}

function deleteUser(int $id): bool {
    $conn = getDatabase();
    $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(["id" => $id]);
    return $stmt->rowCount() > 0;
}

/**
 * Activer un utilisateur
 */
function activateUser($user_id) {
    $pdo = getDatabase();
    $sql = "UPDATE users SET is_active = 1 WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['id' => $user_id]);
}

/**
 * Désactiver un utilisateur
 */
function deactivateUser($user_id) {
    $pdo = getDatabase();
    $sql = "UPDATE users SET is_active = 0 WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['id' => $user_id]);
}


// Model/function_user.php - Ajoutez cette fonction

/**
 * Récupérer les résultats d'un quiz pour un utilisateur
 */
function getUserQuizResults($user_id, $quiz_id) {
    $pdo = getConnexion();
    
    // Vérifier si l'utilisateur a répondu à ce quiz
    $sql_check = "SELECT id, score, completed_at FROM quizz_user 
                  WHERE user_id = :user_id AND quizz_id = :quiz_id";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute(['user_id' => $user_id, 'quiz_id' => $quiz_id]);
    $attempt = $stmt_check->fetch();
    
    if (!$attempt) {
        return null;
    }
    
    // Récupérer les détails des réponses
    $sql = "SELECT 
                q.title as question_text,
                a.answer_text as selected_answer_text,
                ca.answer_text as correct_answer_text,
                ua.is_correct,
                q.point as question_points
            FROM user_answers ua
            JOIN question q ON ua.question_id = q.id
            JOIN answers a ON ua.answer_id = a.id
            LEFT JOIN answers ca ON ca.question_id = q.id AND ca.is_correct = 1
            WHERE ua.quizz_user_id = :quizz_user_id
            ORDER BY ua.id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['quizz_user_id' => $attempt['id']]);
    $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculer les totaux
    $total_points = 0;
    $earned_points = 0;
    $correct_count = 0;
    
    foreach ($answers as $answer) {
        $total_points += $answer['question_points'];
        if ($answer['is_correct']) {
            $earned_points += $answer['question_points'];
            $correct_count++;
        }
    }
    
    $percentage = $total_points > 0 ? round(($earned_points / $total_points) * 100, 1) : 0;
    
    return [
        'quiz_id' => $quiz_id,
        'total_questions' => count($answers),
        'total_points' => $total_points,
        'earned_points' => $earned_points,
        'score_percentage' => $percentage,
        'correct_count' => $correct_count,
        'answers' => $answers,
        'completed_at' => $attempt['completed_at']
    ];
}


// Model/function_user.php

/**
 * Récupérer les quiz déjà répondu par l'utilisateur
 */
function getAnsweredQuizzes($user_id) {
    try {
        $pdo = getConnexion();
        
        // Version simple pour commencer
        $sql = "SELECT DISTINCT q.* 
                FROM quizz q
                JOIN quizz_user qu ON q.id = qu.quizz_id
                WHERE qu.user_id = :user_id
                ORDER BY qu.completed_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        
        $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Pour chaque quiz, ajouter des statistiques
        foreach ($quizzes as &$quiz) {
            // Nombre de questions
            $sql_questions = "SELECT COUNT(*) as count FROM question WHERE quizz_id = :quiz_id";
            $stmt_q = $pdo->prepare($sql_questions);
            $stmt_q->execute(['quiz_id' => $quiz['id']]);
            $result = $stmt_q->fetch();
            $quiz['question_count'] = $result['count'];
            
            // Score
            $sql_score = "SELECT score FROM quizz_user 
                          WHERE quizz_id = :quiz_id AND user_id = :user_id";
            $stmt_s = $pdo->prepare($sql_score);
            $stmt_s->execute(['quiz_id' => $quiz['id'], 'user_id' => $user_id]);
            $score_result = $stmt_s->fetch();
            $quiz['score'] = $score_result ? $score_result['score'] : 0;
        }
        
        return $quizzes;
        
    } catch (Exception $e) {
        // En cas d'erreur, retourner un tableau vide
        error_log("Erreur dans getAnsweredQuizzes: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupérer les quiz disponibles pour un utilisateur
 */
function getAvailableQuizzesForUser($user_id) {
    try {
        $pdo = getConnexion();
        
        $sql = "SELECT q.*, 
                       COUNT(quest.id) as question_count
                FROM quizz q
                LEFT JOIN question quest ON q.id = quest.quizz_id
                WHERE q.status = 'launched'
                AND q.is_active = 1
                AND q.id NOT IN (
                    SELECT quizz_id FROM quizz_user WHERE user_id = :user_id
                )
                GROUP BY q.id
                HAVING question_count > 0
                ORDER BY q.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Erreur dans getAvailableQuizzesForUser: " . $e->getMessage());
        return [];
    }
}