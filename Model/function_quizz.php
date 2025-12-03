<?php
declare(strict_types=1);
require_once __DIR__ . "/function_user.php";

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
