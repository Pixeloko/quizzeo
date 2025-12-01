<?php
declare(strict_types=1);
require_once './config/config.php';

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

    $stmt = $pdo->prepare("SELECT * FROM quizz;");
    $stmt->execute();

    return $stmt->fetchAll() ?: [];
}


/**
 * Supprime un quizz 
 * @param $Id L'id du quizz
 * @return bool True si action accomplie || False si échec
 */
function deleteQuizz(int $Id): bool
{
    $pdo = getDatabase();

    $stmt = $pdo->prepare("
        DELETE quizz
        WHERE id = :id;
    ");

    $stmt->execute(['id' => $$Id]);

    // Compte les modifications par la dernière requête
    return $stmt->rowCount() > 0;
}

