<?php
require_once __DIR__ . "/../config/config.php";

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
