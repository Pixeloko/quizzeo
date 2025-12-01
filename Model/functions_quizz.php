<?php
declare(strict_types=1);
require_once './config/config.php';

/**
 * Retourne un quizz selon son ID
 */
function getQuizzById(int $id): ?array
{
    $pdo = getDatabase();

    $stmt = $pdo->prepare("SELECT * FROM quizz WHERE id = :id");
    $stmt->execute(['id' => $id]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Récupère tous les quizz
 */
function getQuizz(): array
{
    $pdo = getDatabase();

    $stmt = $pdo->query("SELECT * FROM quizz");
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Création d'un quizz
 */
function createQuizz(string $name, int $user_id): int 
{
    $conn = getDatabase();
    $errors = [];

    $title = trim($name);

    if (!$title) {
        $errors["name"] = "Nom requis";
    }

    // Vérification du nom
    $verif = $conn->prepare("SELECT id FROM quizz WHERE name = :name");
    $verif->execute(["name" => $title]);

    if ($verif->fetch()) {
        $errors["name"] = "Nom déjà utilisé";
    }

    if (!empty($errors)) {
        throw new InvalidArgumentException(json_encode($errors));
    }

    $stmt = $conn->prepare("INSERT INTO quizz (name, user_id) VALUES (:name, :user_id)");
    $stmt->execute([
        'name' => $title,
        'user_id' => $user_id
    ]);

    return (int) $conn->lastInsertId();
}

/**
 * Supprime un quizz
 */
function deleteQuizz(int $id): bool
{
    $pdo = getDatabase();

    $stmt = $pdo->prepare("
        DELETE FROM quizz
        WHERE id = :id
    ");

    $stmt->execute(['id' => $id]);

    return $stmt->rowCount() > 0;
}
