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
function updateUser($id, $firstname, $lastname, $email, $password = null, $profile_photo = null) {
    $pdo = getDatabase();

    $sql = "UPDATE users SET firstname = :firstname, lastname = :lastname, email = :email";
    
    $params = [
        ':firstname' => $firstname,
        ':lastname' => $lastname,
        ':email' => $email,
        ':id' => $id
    ];

    if ($password) {
        $sql .= ", password = :password";
        $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    if ($profile_photo) {
        $sql .= ", profile_photo = :profile_photo";
        $params[':profile_photo'] = $profile_photo;
    }

    $sql .= " WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
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


// Dans Model/function_user.php - version corrigée
function getUserQuizResults($user_id) {
    try {
        require_once __DIR__ . '/../config/config.php';
        $pdo = getDatabase(); // ou getDatabase() selon votre fonction
        
        $sql = "SELECT 
                    q.id as quiz_id,
                    q.name as quiz_title,
                    qu.score,
                    qu.completed_at,
                    (SELECT SUM(point) FROM questions WHERE quizz_id = q.id) as total_points
                FROM quizz_user qu
                JOIN quizz q ON qu.quizz_id = q.id
                WHERE qu.user_id = :user_id
                ORDER BY qu.completed_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculer les pourcentages
        foreach ($results as &$result) {
            if ($result['total_points'] > 0) {
                $result['percentage'] = round(($result['score'] / $result['total_points']) * 100, 2);
            } else {
                $result['percentage'] = 0;
            }
        }
        
        return $results ?: [];
    } catch (Exception $e) {
        error_log("Erreur getUserQuizResults: " . $e->getMessage());
        return [];
    }
}


// Model/function_user.php

/**
 * Récupérer les quiz déjà répondu par l'utilisateur
 */
function getAnsweredQuizzes($user_id) {
    try {
        $pdo = getDatabase();
        
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
            $sql_questions = "SELECT COUNT(*) as count FROM questions WHERE quizz_id = :quiz_id";
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

// Model/function_user.php

/**
 * Récupérer tous les quiz actifs (pour la page d'accueil)
 */
function getAllActiveQuizzes() {
    try {
        $pdo = getDatabase();
        
        $sql = "SELECT q.*, 
                       COUNT(quest.id) as question_count,
                       u.firstname as creator_firstname,
                       u.lastname as creator_lastname
                FROM quizz q
                LEFT JOIN questions quest ON q.id = quest.quizz_id
                LEFT JOIN users u ON q.user_id = u.id
                WHERE q.status = 'launched'
                AND q.is_active = 1
                GROUP BY q.id
                HAVING question_count > 0
                ORDER BY q.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Erreur dans getAllActiveQuizzes: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère les quiz disponibles (non répondu) pour l'utilisateur
 * @param int $user_id
 * @return array
 */
function getAvailableQuizzes($user_id) {
    try {
        $pdo = getDatabase();
        
        $sql = "SELECT 
                    q.id,
                    q.name,
                    q.description,
                    q.created_at,
                    (SELECT COUNT(*) FROM questions WHERE quizz_id = q.id) as question_count,
                    u.firstname as creator_firstname,
                    u.lastname as creator_lastname
                FROM quizz q
                JOIN users u ON q.user_id = u.id
                WHERE q.status = 'launched'
                AND q.is_active = 1
                AND q.id NOT IN (
                    SELECT quizz_id 
                    FROM quizz_user 
                    WHERE user_id = :user_id
                )
                ORDER BY q.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Erreur getAvailableQuizzes: " . $e->getMessage());
        return [];
    }
}

function getQuizCountByUser($user_id) {
    try {
        $pdo = getDatabase();
        
        $sql = "SELECT COUNT(*) as quiz_count 
                FROM quizz_user 
                WHERE user_id = :user_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['quiz_count'] ?? 0;
    } catch (Exception $e) {
        error_log("Erreur getQuizCountByUser: " . $e->getMessage());
        return 0;
    }
}

/**
 * Vérifie si un utilisateur a déjà répondu à un quiz
 * @param int $user_id
 * @param int $quiz_id
 * @return bool
 */
function hasUserAnsweredQuiz($user_id, $quiz_id) {
    try {
        require_once __DIR__ . '/../config/config.php';
        $pdo = getDatabase();
        
        $sql = "SELECT COUNT(*) as count 
                FROM quizz_user 
                WHERE user_id = :user_id 
                AND quizz_id = :quiz_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $user_id,
            'quiz_id' => $quiz_id
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return ($result['count'] ?? 0) > 0;
    } catch (Exception $e) {
        error_log("Erreur hasUserAnsweredQuiz: " . $e->getMessage());
        return false;
    }
}

/**
 * Compte le nombre total de réponses d'un utilisateur
 * @param int $user_id
 * @return int
 */
function getTotalAnswersCount($user_id) {
    try {
        require_once __DIR__ . '/../config/database.php';
        $pdo = getDatabase();
        
        $sql = "SELECT COUNT(*) as total_answers 
                FROM user_answers ua
                JOIN quizz_user qu ON ua.quizz_user_id = qu.id
                WHERE qu.user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total_answers'] ?? 0;
    } catch (Exception $e) {
        error_log("Erreur getTotalAnswersCount: " . $e->getMessage());
        return 0;
    }
}

/**
 * Récupère les statistiques complètes de l'utilisateur
 * @param int $user_id
 * @return array
 */
function getUserStats($user_id) {
    return [
        'quiz_count' => getQuizCountByUser($user_id),
        'total_answers' => getTotalAnswersCount($user_id),
        'average_score' => getAverageScore($user_id) // Si vous avez cette fonction
    ];
}

/**
 * Récupère le dernier quiz répondu par l'utilisateur
 * @param int $user_id
 * @return array|null
 */
function getLastQuizScore($user_id) {
    try {
        $pdo = getDatabase();
        
        $sql = "SELECT 
                    q.name as quiz_title,
                    qu.score,
                    qu.completed_at,
                    (SELECT SUM(point) FROM questions WHERE quizz_id = q.id) as total_points
                FROM quizz_user qu
                JOIN quizz q ON qu.quizz_id = q.id
                WHERE qu.user_id = :user_id
                ORDER BY qu.completed_at DESC
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Calculer le pourcentage
            if ($result['total_points'] > 0) {
                $result['percentage'] = round(($result['score'] / $result['total_points']) * 100, 2);
            } else {
                $result['percentage'] = 0;
            }
            
            // Formater la date
            $result['formatted_date'] = date('d/m/Y', strtotime($result['completed_at']));
        }
        
        return $result ?: null;
    } catch (Exception $e) {
        error_log("Erreur getLastQuizScore: " . $e->getMessage());
        return null;
    }
}