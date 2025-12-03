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

