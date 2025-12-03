<?php
session_start();
require_once __DIR__ . "/../Model/function_quizz.php";
require_once __DIR__ . "/../Model/function_question.php";

$quiz_id = (int)($_GET['id'] ?? 0);
$quiz = getQuizzById($quiz_id);  // tu devras créer cette fonction
$questions = getQuestionsByQuizz($quiz_id);
$allQuestions = getAllQuestions();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_question'])) {
        $question_id = (int)$_POST['question_id'];
        // ici tu peux soit créer une nouvelle question pour le quiz
        // soit mettre à jour le quizz_id de la question existante
        addQuestionToQuizz($quiz_id, $question_id); // fonction à créer
    }

    if (isset($_POST['remove_question'])) {
        $question_id = (int)$_POST['question_id'];
        removeQuestionFromQuizz($question_id); // fonction à créer
    }

    if (isset($_POST['launch'])) {
        updateQuizzStatus($quiz_id, 'launched'); // fonction à créer
    }

    if (isset($_POST['finish'])) {
        updateQuizzStatus($quiz_id, 'finished'); // fonction à créer
    }

    header("Location: /quizzeo/index.php?url=ecole/edit_quizz&id=$quiz_id");
    exit;
}
