<?php
require_once __DIR__ . '/../config/config.php';

function createQuestion(int $quizz_id, string $question_text, int $point = 1): int {
    $conn = getDatabase();
    $stmt = $conn->prepare("INSERT INTO questions (quizz_id, title, point)
        VALUES (:quizz_id, :title, :point)
    ");
    $stmt->execute([
        'quizz_id' => $quizz_id,
        'title' => $question_text,
        'point' => $point
    ]);
    return (int)$conn->lastInsertId();
}

function createQuestionEnt(int $quizz_id, string $title, string $type = 'qcm'): int
{
    $pdo = getDatabase();
    $stmt = $pdo->prepare("INSERT INTO questions (quizz_id, title, type) VALUES (:qid, :title, :type)");
    $stmt->execute([
        'qid' => $quizz_id,
        'title' => $title,
        'type' => $type
    ]);
    return (int)$pdo->lastInsertId();
}

function addAnswerToQuestion(int $question_id, string $answer_text, bool $is_correct = false): int {
    $conn = getDatabase();
    $stmt = $conn->prepare("INSERT INTO answers(question_id, answer_text, is_correct) VALUES (:question_id, :answer_text, :is_correct)");
    $stmt->execute([
        'question_id' => $question_id,
        'answer_text' => $answer_text,
        'is_correct' => $is_correct ? 1 : 0
    ]);
    return (int)$conn->lastInsertId();
}


function getAllQuestions(): array
{
    $pdo = getDatabase();

    // Récupère toutes les questions
    $stmt = $pdo->prepare("SELECT * FROM questions ORDER BY id ASC");
    $stmt->execute();
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pour chaque question, récupérer ses réponses
    foreach ($questions as &$question) {
        $stmt2 = $pdo->prepare("SELECT * FROM answers WHERE question_id = :qid ORDER BY id ASC");
        $stmt2->execute(['qid' => $question['id']]);
        $question['answers'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }

    return $questions;
}

/**
 * Récupérer une question par son ID avec ses réponses
 */
function getQuestionById(int $id): ?array {
    $conn = getDatabase();
    $stmt = $conn->prepare("SELECT * FROM questions WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $question = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($question) {
        $stmt2 = $conn->prepare("SELECT * FROM answers WHERE question_id = :question_id ORDER BY id");
        $stmt2->execute(['question_id' => $id]);
        $question['answers'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }
    
    return $question ?: null;
}

/**
 * Récupérer les réponses d'une question
 */
function getAnswersByQuestion(int $question_id): array {
    $conn = getDatabase();
    $stmt = $conn->prepare("SELECT * FROM answers WHERE question_id = :question_id ORDER BY id");
    $stmt->execute(['question_id' => $question_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Mettre à jour une question
 */
function updateQuestion(int $question_id, string $title, int $point): bool {
    $conn = getDatabase();
    $stmt = $conn->prepare("UPDATE questions SET title = :title, point = :point WHERE id = :id");
    return $stmt->execute([
        'title' => $title,
        'point' => $point,
        'id' => $question_id
    ]);
}

/**
 * Mettre à jour une réponse
 */
function updateAnswer(int $answer_id, string $answer_text, bool $is_correct): bool {
    $conn = getDatabase();
    $stmt = $conn->prepare("UPDATE answers SET answer_text = :answer_text, is_correct = :is_correct WHERE id = :id");
    return $stmt->execute([
        'answer_text' => $answer_text,
        'is_correct' => $is_correct ? 1 : 0,
        'id' => $answer_id
    ]);
}

/**
 * Supprimer une réponse
 */
function deleteAnswer(int $answer_id): bool {
    $conn = getDatabase();
    $stmt = $conn->prepare("DELETE FROM answers WHERE id = :id");
    return $stmt->execute(['id' => $answer_id]);
}

/**
 * Supprimer une question et toutes ses réponses
 */
function deleteQuestion(int $question_id): bool {
    $conn = getDatabase();
    
    // Supprimer d'abord les réponses
    $stmt1 = $conn->prepare("DELETE FROM answers WHERE question_id = :id");
    $stmt1->execute(['id' => $question_id]);
    
    // Puis supprimer la question
    $stmt2 = $conn->prepare("DELETE FROM questions WHERE id = :id");
    return $stmt2->execute(['id' => $question_id]);
}

/**
 * Compter le nombre de questions dans un quiz
 */
function countQuestionsInQuiz(int $quiz_id): int {
    $conn = getDatabase();
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM questions WHERE quizz_id = :quiz_id");
    $stmt->execute(['quiz_id' => $quiz_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int)($result['count'] ?? 0);
}
