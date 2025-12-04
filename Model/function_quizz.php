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

function getQuizzResults(int $quizz_id): array {
    $conn = getDatabase();
    $stmt = $conn->prepare("
        SELECT u.id as user_id, u.firstname, u.lastname, u.email, s.score, s.submitted_at 
        FROM submissions s 
        INNER JOIN users u ON s.user_id = u.id 
        WHERE s.quizz_id = :id 
        ORDER BY s.score DESC, s.submitted_at ASC
    ");
    $stmt->execute(['id' => $quizz_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format names
    foreach ($results as &$result) {
        $result['name'] = $result['firstname'] . ' ' . $result['lastname'];
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