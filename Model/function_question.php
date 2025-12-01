<?php
declare(strict_types=1);
require_once './config/config.php';

/**
 * Récupère une question par son ID
 */
function getQuestById(int $id): ?array
{
    $pdo = getDatabase();

    $stmt = $pdo->prepare("SELECT * FROM questions WHERE id = :id");
    $stmt->execute(['id' => $id]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Récupère toutes les questions
 */
function getQuest(): array
{
    $pdo = getDatabase();

    $stmt = $pdo->query("SELECT * FROM questions");
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Crée une question
 */
function createQuest(string $title, string $answer, int $point): int 
{
    $conn = getDatabase();
    $errors = [];

    $title = trim($title);
    $answer = trim($answer);

    if (!$title) {
        $errors["title"] = "Titre requis";
    }

    if (!$answer) {
        $errors["answer"] = "Réponse requise";
    }

    if ($point <= 0) {
        $errors["point"] = "Les points doivent être supérieurs à 0";
    }

    // Vérifier si le titre existe déjà
    $verif = $conn->prepare("SELECT id FROM questions WHERE title = :title");
    $verif->execute(['title' => $title]);

    if ($verif->fetch()) {
        $errors["title"] = "Titre déjà utilisé";
    }

    if (!empty($errors)) {
        throw new InvalidArgumentException(json_encode($errors));
    }

    $stmt = $conn->prepare("INSERT INTO questions (title, answer, point) VALUES (:title, :answer, :point)");
    $stmt->execute([
        'title' => $title,
        'answer' => $answer,
        'point' => $point
    ]);

    return (int) $conn->lastInsertId();
}

/**
 * Supprime une question
 */
function deleteQuest(int $id): bool
{
    $pdo = getDatabase();

    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = :id");
    $stmt->execute(['id' => $id]);

    return $stmt->rowCount() > 0;
}
