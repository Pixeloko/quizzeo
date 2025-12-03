<?php
// Controller/delete_question.php

session_start();

// Vérifier l'authentification
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "ecole") {
    header("Location: /quizzeo/?url=login");
    exit;
}

// Récupérer l'ID de la question et du quiz
$question_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

if ($question_id <= 0 || $quiz_id <= 0) {
    $_SESSION['error'] = "Paramètres invalides";
    header("Location: /quizzeo/?url=ecole");
    exit;
}

// Inclure les modèles
require_once __DIR__ . "/../Model/function_quizz.php";
require_once __DIR__ . "/../Model/function_question.php";

// Vérifier que la question appartient au quiz
$question = getQuestionById($question_id);
if (!$question || $question['quizz_id'] != $quiz_id) {
    $_SESSION['error'] = "Question non trouvée";
    header("Location: /quizzeo/View/ecole/edit_quiz.php?id=" . $quiz_id);
    exit;
}

// Vérifier que l'utilisateur est le propriétaire du quiz
$quiz = getQuizzById($quiz_id);
if (!$quiz || $quiz['user_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = "Accès non autorisé";
    header("Location: /quizzeo/?url=ecole");
    exit;
}

// Supprimer la question
try {
    deleteQuestion($question_id);
    $_SESSION['success'] = "Question supprimée avec succès";
} catch (Exception $e) {
    $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
}

// Rediriger vers l'édition du quiz
header("Location: /quizzeo/View/ecole/edit_quiz.php?id=" . $quiz_id);
exit;