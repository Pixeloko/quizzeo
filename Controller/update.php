<?php
session_start();

require_once __DIR__ . "/../../Model/function_quizz.php";
require_once __DIR__ . "/../../Model/function_question.php";

// Récupère l'ID du quiz depuis l'URL et sécurise
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($quiz_id <= 0) {
    die("Quiz non trouvé");
}

// Récupère les données du quiz et des questions
$quiz = getQuizzById($quiz_id);
if (!$quiz) {
    die("Quiz non trouvé");
}

$questions = getQuestionsByQuizz_ecole($quiz_id);
$allQuestions = getAllQuestions();

// Traite le POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Ajouter une question au quiz
    if (isset($_POST['add_question']) && isset($_POST['question_id'])) {
        $question_id = (int)$_POST['question_id'];
        addQuestionToQuizz($quiz_id, $question_id);
    }

    // Retirer une question du quiz
    if (isset($_POST['remove_question']) && isset($_POST['question_id'])) {
        $question_id = (int)$_POST['question_id'];
        removeQuestionFromQuizz($quiz_id, $question_id); // modification pour passer le quiz_id
    }

    // Lancer le quiz
    if (isset($_POST['launch'])) {
        updateQuizzStatus($quiz_id, 'launched');
    }

    // Terminer le quiz
    if (isset($_POST['finish'])) {
        updateQuizzStatus($quiz_id, 'finished');
    }

    // Redirection après POST
    header("Location: /quizzeo/index.php?url=ecole/update&id=$quiz_id");
    exit;
}

// Inclut la vue pour GET
require_once __DIR__ . "/../../View/ecole/edit_quizz.php";
