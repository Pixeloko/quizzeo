<?php
// Controller/store_entreprise.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Vérifier la session entreprise
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "entreprise") {
    $_SESSION["error"] = "Accès non autorisé";
    header('Location: /quizzeo/?url=login');
    exit;
}

// Inclure les modèles
require_once __DIR__ . '/../Model/function_quizz.php';
require_once __DIR__ . '/../Model/function_question.php';
require_once __DIR__ . '/../Model/function_quizz_question.php'; // table pivot

$errors = [];
$formData = $_POST;

// Vérifier POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $questions = $_POST['questions'] ?? [];

    // Validation du nom
    if ($name === '') $errors['name'] = "Le nom du quiz est obligatoire";
    if (empty($questions)) $errors['questions'] = "Ajoutez au moins une question";

    // Valider chaque question
    foreach ($questions as $index => $q_data) {
        $question_text = trim($q_data['title'] ?? '');
        $type = $q_data['type'] ?? 'qcm';

        if ($question_text === '') {
            $errors["question_{$index}"] = "La question " . ($index + 1) . " est vide";
        }

        // Pour QCM, vérifier qu'il y a au moins une réponse
        if ($type === 'qcm') {
            $hasAnswers = false;
            foreach ($q_data['answers'] ?? [] as $answer) {
                if (!empty(trim($answer['text'] ?? ''))) {
                    $hasAnswers = true;
                    break;
                }
            }
            if (!$hasAnswers) {
                $errors["answers_{$index}"] = "Ajoutez au moins une réponse pour la question " . ($index + 1);
            }
        }
    }

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $formData;
        header('Location: /quizzeo/?url=entreprise/create');
        exit;
    }

    // Créer le quiz
    try {
        $user_id = (int)$_SESSION['user_id'];
        $quiz_id = createQuizz($name, $user_id);
        if (!$quiz_id) throw new Exception("Erreur lors de la création du quiz");

        foreach ($questions as $q_index => $q_data) {
            $question_text = trim($q_data['title'] ?? '');
            $type = $q_data['type'] ?? 'qcm';
            if ($question_text === '') continue;

            $question_id = createQuestionEnt($quiz_id, $question_text, $type);
            if (!$question_id) throw new Exception("Erreur à la création de la question #" . ($q_index+1));

            // Ajouter les réponses seulement si QCM
            if ($type === 'qcm') {
                foreach ($q_data['answers'] ?? [] as $answer) {
                    $answer_text = trim($answer['text'] ?? '');
                    if ($answer_text === '') continue;
                    addAnswerToQuestion($question_id, $answer_text); // pas de bonne/mauvaise réponse
                }
            }
        }

        $_SESSION['success'] = "✅ Quiz créé avec succès !";
        header('Location: /quizzeo/?url=entreprise');
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
        $_SESSION['form_data'] = $formData;
        header('Location: /quizzeo/?url=entreprise/create');
        exit;
    }

} else {
    $_SESSION['error'] = "Méthode non autorisée";
    header('Location: /quizzeo/?url=entreprise');
    exit;
}
