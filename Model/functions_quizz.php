<?php
declare(strict_types=1);
require_once './config/config.php';

function getActiveQuizz() {
    $pdo = getDatabase();

    $stmt = $pdo->prepare("SELECT * FROM quizz WHERE is_active = 1");
    $stmt->execute();

    return $stmt->fetchAll() ?: [];
}

/**
 * Retourne les données (ou une array vide) via l'id du quizz
 *
 * @param string $id L'id du quizz
 * @return ?array array avec les clés-valeurs pour ce quizz || array vide si quizz non trouvé
 */
function getQuizzById(int $Id): ?array
{
    $pdo = getDatabase();

    $stmt = $pdo->prepare("SELECT * FROM quizz WHERE id = :id");
    $stmt->execute(['id' => $Id]);

    $book = $stmt->fetch();

    return $book ?: null;
}

/**
 * Récupère tous les quizz 
 * @return array avec tous les quizz
 * 
 *  || array vide si quizz non trouvé
 */
function getQuizz(): ?array
{
    $pdo = getDatabase();

    $stmt = $pdo->prepare("SELECT * FROM quizz");
    $stmt->execute();

    return $stmt->fetchAll() ?: [];
}

function createQuizz(string $name, string $user_id): int {
    
    $errors = [];

    $title = trim($name);

    if (!$title) {
        $errors["name"] = "Nom requis";
    }

    $conn = getDatabase();

    $verif = $conn->prepare("SELECT id FROM quizz WHERE name = :name ");
    $verif->execute([
        "name" => $name, 
    ]);

    if ($verif->fetch()) {
        $errors["name"] = "Nom déjà utilisé";
    }

    if (!empty($errors)) {
        throw new InvalidArgumentException(json_encode($errors));
    }

    $stmt = $conn->prepare("INSERT INTO quizz(name, user_id) VALUES (:title, :user_id");

    $stmt->execute([
        'name' => $title,
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

