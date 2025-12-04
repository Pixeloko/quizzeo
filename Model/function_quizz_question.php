<?php
declare(strict_types=1);


/**
 * Ajoute une question dans un quizz
 */
function addQuestionToQuizz(int $quizz_id, int $question_id): bool
{
    $pdo = getDatabase();

    // Vérifier duplicata
    $check = $pdo->prepare("SELECT id FROM quizz_questions WHERE quizz_id = :q AND question_id = :qi");
    $check->execute(['q' => $quizz_id, 'qi' => $question_id]);

    if ($check->fetch()) {
        return false; // Déjà présent
    }

    $stmt = $pdo->prepare("
        INSERT INTO quizz_questions (quizz_id, question_id)
        VALUES (:q, :qi)
    ");

    return $stmt->execute(['q' => $quizz_id, 'qi' => $question_id]);
}

/**
 * Récupère toutes les questions d’un quizz
 */
function getQuestionsByQuizz(int $quizz_id): array
{
    $pdo = getDatabase();

    $stmt = $pdo->prepare("SELECT q.* FROM questions q INNER JOIN quizz_questions qq ON qq.question_id = q.id
                            WHERE qq.quizz_id = :id");

    $stmt->execute(['id' => $quizz_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function GetQuestionsByQuizz_ecole(int $quizz_id): array {
    $conn = getDatabase();
    $stmt = $conn->prepare("SELECT * FROM questions WHERE quizz_id = :qid ORDER BY id ASC");
    $stmt->execute(['qid' => $quizz_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($questions as &$question) {
        $stmt2 = $conn->prepare("SELECT * FROM answers WHERE question_id = :qid ORDER BY id ASC");
        $stmt2->execute(['qid' => $question['id']]);
        $question['answers'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }

    return $questions ?: [];
}


/**
 * Supprime une question d’un quizz
 */
function removeQuestionFromQuizz(int $quizz_id, int $question_id): bool
{
    $pdo = getDatabase();

    $stmt = $pdo->prepare("DELETE FROM quizz_questions WHERE quizz_id = :q AND question_id = :qi");

    $stmt->execute(['q' => $quizz_id, 'qi' => $question_id]);
    return $stmt->rowCount() > 0;
}

/**
 * Supprime toutes les questions d’un quizz (utile quand on delete un quizz)
 */
function deleteQuizzQuestions(int $quizz_id): bool
{
    $pdo = getDatabase();

    $stmt = $pdo->prepare("
        DELETE FROM quizz_questions
        WHERE quizz_id = :id
    ");

    $stmt->execute(['id' => $quizz_id]);
    return $stmt->rowCount() > 0;
}

