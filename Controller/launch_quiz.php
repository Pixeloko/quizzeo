<?php
// Controller/launch_quiz.php

session_start();

// Vérifier l'authentification
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "ecole") {
    header("Location: /quizzeo/?url=login");
    exit;
}

// Récupérer l'ID du quiz
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($quiz_id <= 0) {
    $_SESSION['error'] = "Quiz non trouvé";
    header("Location: /quizzeo/?url=ecole");
    exit;
}

// Inclure les modèles
require_once __DIR__ . "/../Model/function_quizz.php";

// Récupérer le quiz
$quiz = getQuizzById($quiz_id);
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

// Lancer le quiz
try {
    // Vérifier s'il y a des questions
    require_once __DIR__ . "/../Model/function_question.php";
    $questions = GetQuestionsByQuizz_ecole($quiz_id);
    
    if (empty($questions)) {
        $_SESSION['error'] = "Impossible de lancer un quiz sans questions";
        header("Location: /quizzeo/View/ecole/edit_quiz.php?id=" . $quiz_id);
        exit;
    }
    
    // Mettre à jour le statut
    updateQuizzStatus($quiz_id, 'launched');
    
    $_SESSION['success'] = "Quiz lancé avec succès ! Les étudiants peuvent maintenant y répondre.";
    header("Location: /quizzeo/View/ecole/edit_quiz.php?id=" . $quiz_id);
    exit;
    
} catch (Exception $e) {
    $_SESSION['error'] = "Erreur: " . $e->getMessage();
    header("Location: /quizzeo/?url=ecole");
    exit;
}