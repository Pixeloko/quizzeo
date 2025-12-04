<?php
declare(strict_types=1);
require_once __DIR__ . "/function_user.php";
require_once __DIR__ . '/../config/config.php';

/**
 * Récupère les quizz actifs
 *
 * @return array
 */
function getActiveQuizz(): array {
    $conn = getDatabase();
    $stmt = $conn->prepare("SELECT id AS quizz_id, name AS title, created_at FROM quizz WHERE is_active = 1 ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getQuizzById(int $quizz_id): ?array {
    $conn = getDatabase();
    $stmt = $conn->prepare("SELECT * FROM quizz WHERE id = :id");
    $stmt->execute(['id' => $quizz_id]);
    $quizz = $stmt->fetch();
    return $quizz ?: null;
}

/**
 * Récupère tous les quizz 
 * @return array avec tous les quizz ou array vide
 */
function getAllQuizz(): array
{
    $pdo = getDatabase();

    $stmt = $pdo->prepare("SELECT * FROM quizz ORDER BY created_at DESC");
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}


function getQuizzByUserId(int $user_id): ?array
{
    $pdo = getDatabase();

    $stmt = $pdo->prepare("SELECT * FROM quizz WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);

    $book = $stmt->fetchAll();

    return $book ?: null;
}

function getQuizz(): ?array
{
    $pdo = getDatabase();

    $stmt = $pdo->prepare("SELECT * FROM quizz");
    $stmt->execute();

    return $stmt->fetchAll() ?: [];
}

function createQuizz(string $name, int $user_id): int {

    $conn = getDatabase();

    $stmt = $conn->prepare("INSERT INTO quizz(name, user_id) VALUES (:name, :user_id)");

    $stmt->execute([
        'name' => $name,
        'user_id' => $user_id,
    ]);

    return (int) $conn->lastInsertId();
}

/**
 * Supprime un quizz 
 * @param $Id L'id du quizz
 * @return bool True si action accomplie || False si échec
 */
function deleteQuizz(int $Id): bool
{
    $pdo = getDatabase();

    $stmt = $pdo->prepare("DELETE quizz WHERE id = :id");

    $stmt->execute(['id' => $$Id]);

    return $stmt->rowCount() > 0;
}


/**
 * Formater une date
 *
 * @param string $date Date au format Y-m-d H:i:s
 * @param string $format Format de sortie
 * @return string Date formatée
 */
function formatDate(string $date, string $format = 'd/m/Y'): string
{
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

// Add to function_quizz.php
function getQuizStatus(int $quizz_id): string {
    $conn = getDatabase();
    $stmt = $conn->prepare("SELECT status FROM quizz WHERE id = :id");
    $stmt->execute(['id' => $quizz_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['status'] ?? 'draft';
}

/**
 * Met à jour l'état actif/inactif d'un quiz
 * @param int $quizz_id ID du quizz
 * @param bool $is_active État actif
 * @return bool True si mis à jour
 */
function updateQuizzActiveStatus(int $quizz_id, bool $is_active): bool
{
    $pdo = getDatabase();
    $stmt = $pdo->prepare("UPDATE quizz SET is_active = :is_active WHERE id = :id");
    return $stmt->execute(['is_active' => $is_active ? 1 : 0, 'id' => $quizz_id]);
}

/**
 * Compte le nombre total de points possibles pour un quiz
 * @param int $quizz_id ID du quizz
 * @return int Total des points
 */
function getQuizTotalPoints(int $quizz_id): int
{
    $pdo = getDatabase();
    $stmt = $pdo->prepare("SELECT SUM(point) as total_points FROM questions WHERE quizz_id = :quizz_id");
    $stmt->execute(['quizz_id' => $quizz_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int) ($result['total_points'] ?? 0);
}

/**
 * Récupère le score moyen d'un quiz
 * @param int $quizz_id ID du quizz
 * @return float Score moyen en pourcentage
 */
function getQuizAverageScore(int $quizz_id): float
{
    $pdo = getDatabase();
    
    // Récupérer tous les scores
    $stmt = $pdo->prepare("SELECT score FROM quizz_user WHERE quizz_id = :quizz_id");
    $stmt->execute(['quizz_id' => $quizz_id]);
    $scores = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($scores)) {
        return 0.0;
    }
    
    // Total des points possibles
    $total_points = getQuizTotalPoints($quizz_id);
    
    if ($total_points <= 0) {
        return 0.0;
    }
    
    // Calculer la moyenne des pourcentages
    $total_percentage = 0;
    foreach ($scores as $score) {
        $total_percentage += ($score / $total_points) * 100;
    }
    
    return round($total_percentage / count($scores), 2);
}

/**
 * Mettre à jour le statut d'un quiz
 */
function updateQuizzStatus($quiz_id, $status) {
    $pdo = getDatabase();
    $sql = "UPDATE quizz SET status = :status WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        'status' => $status,
        'id' => $quiz_id
    ]);
}

/**
 * Récupérer les questions d'un quiz
 */
function getQuestionsByQuizzId($quiz_id) {
    $pdo = getDatabase();
    $sql = "SELECT * FROM questions WHERE quizz_id = :quiz_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['quiz_id' => $quiz_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les réponses pour chaque question
    foreach ($questions as &$question) {
        $sql_answers = "SELECT * FROM answers WHERE question_id = :question_id";
        $stmt_answers = $pdo->prepare($sql_answers);
        $stmt_answers->execute(['question_id' => $question['id']]);
        $question['answers'] = $stmt_answers->fetchAll(PDO::FETCH_ASSOC);
    }
    
    return $questions;
}

/**
 * Met à jour le nom d'un quiz
 */
function updateQuizName(int $quizz_id, string $name): bool {
    $conn = getDatabase();
    $stmt = $conn->prepare("UPDATE quizz SET name = :name WHERE id = :id");
    return $stmt->execute(['name' => $name, 'id' => $quizz_id]);
}

function countSubmissions(int $quizz_id): int {
    $conn = getDatabase();
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM quizz_user WHERE quizz_id = :id");
    $stmt->execute(['id' => $quizz_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int) ($result['count'] ?? 0);
}

/**
 * Récupère les résultats d'un quiz (tous les utilisateurs qui ont répondu)
 * @param int $quizz_id ID du quizz
 * @return array
 */
function getQuizzResults(int $quizz_id): array
{
    $pdo = getDatabase();
    
    $stmt = $pdo->prepare("
        SELECT 
            u.id as user_id,
            u.firstname,
            u.lastname,
            u.email,
            qu.score,
            qu.completed_at,
            (SELECT SUM(point) FROM questions WHERE quizz_id = qu.quizz_id) as total_points
        FROM quizz_user qu
        INNER JOIN users u ON qu.user_id = u.id
        WHERE qu.quizz_id = :quizz_id
        ORDER BY qu.score DESC, qu.completed_at ASC
    ");
    $stmt->execute(['quizz_id' => $quizz_id]);
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculer les pourcentages et formater les dates
    foreach ($results as &$result) {
        $total_points = (int) $result['total_points'];
        $score = (int) $result['score'];
        
        if ($total_points > 0) {
            $result['percentage'] = round(($score / $total_points) * 100, 2);
        } else {
            $result['percentage'] = 0;
        }
        
        $result['score_display'] = "{$score}/{$total_points}";
        
        // Formater la date
        if ($result['completed_at']) {
            $result['completed_formatted'] = date('d/m/Y H:i', strtotime($result['completed_at']));
        } else {
            $result['completed_formatted'] = 'Non terminé';
        }
    }
    
    return $results ?: [];
}

// Model/function_quizz.php

/**
 * Activer un quiz
 */
function activateQuiz($quiz_id) {
    $pdo = getDatabase();
    $sql = "UPDATE quizz SET is_active = 1 WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['id' => $quiz_id]);
}

/**
 * Désactiver un quiz
 */
function deactivateQuiz($quiz_id) {
    $pdo = getDatabase();
    $sql = "UPDATE quizz SET is_active = 0 WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['id' => $quiz_id]);
}


// Model/function_quizz.php

/**
 * Récupérer tous les quiz
 */
function fetchQuizzes() {
    $pdo = getDatabase();
    $sql = "SELECT q.*, u.firstname, u.lastname 
            FROM quizz q 
            LEFT JOIN users u ON q.user_id = u.id 
            ORDER BY q.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}