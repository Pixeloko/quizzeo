<?php
// Controller/delete_answer.php

session_start();

// Vérifier l'authentification
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "ecole") {
    header("Location: /quizzeo/?url=login");
    exit;
}

// Récupérer l'ID de la réponse
$answer_id = isset($_POST['delete_answer']) ? (int)$_POST['delete_answer'] : 0;
$question_id = isset($_GET['question_id']) ? (int)$_GET['question_id'] : 0;

if ($answer_id <= 0) {
    $_SESSION['error'] = "Paramètres invalides";
    header("Location: /quizzeo/?url=ecole");
    exit;
}

// Inclure les modèles
require_once __DIR__ . "/../Model/function_quizz.php";
require_once __DIR__ . "/../Model/function_question.php";

// Récupérer la réponse et sa question associée
$answer = getAnswerById($answer_id);
if (!$answer) {
    $_SESSION['error'] = "Réponse non trouvée";
    
    // Rediriger vers la page appropriée
    if ($question_id > 0) {
        header("Location: /quizzeo/View/ecole/edit_question.php?id=" . $question_id);
    } else {
        header("Location: /quizzeo/?url=ecole");
    }
    exit;
}

// Récupérer la question
$question = getQuestionById($answer['question_id']);
if (!$question) {
    $_SESSION['error'] = "Question non trouvée";
    header("Location: /quizzeo/?url=ecole");
    exit;
}

// Récupérer le quiz
$quiz = getQuizzById($question['quizz_id']);
if (!$quiz) {
    $_SESSION['error'] = "Quiz non trouvé";
    header("Location: /quizzeo/?url=ecole");
    exit;
}

// Vérifier que l'utilisateur est le propriétaire
if ($quiz['user_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = "Accès non autorisé";
    header("Location: /quizzeo/?url=ecole");
    exit;
}

// Vérifier qu'il reste au moins 2 réponses pour cette question
$answers_count = countAnswersForQuestion($answer['question_id']);
if ($answers_count <= 2) {
    $_SESSION['error'] = "Une question doit avoir au moins 2 réponses";
    header("Location: /quizzeo/View/ecole/edit_question.php?id=" . $answer['question_id']);
    exit;
}

// Supprimer la réponse
try {
    deleteAnswer($answer_id);
    $_SESSION['success'] = "Réponse supprimée avec succès";
} catch (Exception $e) {
    $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
}

// Rediriger vers l'édition de la question
header("Location: /quizzeo/View/ecole/edit_question.php?id=" . $answer['question_id']);
exit;