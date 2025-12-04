<?php
// Controller/store_entreprise.php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier session
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "entreprise") {
    $_SESSION["error"] = "Accès non autorisé";
    header('Location: /quizzeo/?url=login');
    exit;
}

// Inclure les modèles
require_once __DIR__ . '/../Model/function_quizz.php';
require_once __DIR__ . '/../Model/function_question.php';

$errors = [];
$formData = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $questions = $_POST['questions'] ?? [];

    // Validation du nom
    if (empty($name)) {
        $errors['name'] = "Le nom du quiz est obligatoire";
    } elseif (strlen($name) < 3) {
        $errors['name'] = "Le nom doit contenir au moins 3 caractères";
    }

    // Validation des questions
    if (empty($questions)) {
        $errors['questions'] = "Ajoutez au moins une question";
    } else {
        foreach ($questions as $index => $q_data) {
            $title = trim($q_data['title'] ?? '');
            if (empty($title)) {
                $errors["question_{$index}"] = "La question " . ($index+1) . " est vide";
            }

            // Pour QCM, vérifier qu’il y a au moins une réponse
            if (($q_data['type'] ?? 'qcm') === 'qcm') {
                $hasAnswer = false;
                foreach ($q_data['answers'] as $answer) {
                    if (!empty(trim($answer['text'] ?? ''))) {
                        $hasAnswer = true;
                        break;
                    }
                }
                if (!$hasAnswer) {
                    $errors["answers_{$index}"] = "Ajoutez au moins une réponse pour la question " . ($index+1);
                }
            }
        }
    }

    // Si erreurs, renvoyer au formulaire
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $formData;
        header('Location: /quizzeo/?url=entreprise/create');
        exit;
    }

    try {
        $user_id = $_SESSION['user_id'];

        // Créer le quiz
        $quiz_id = createQuizz($name, $user_id);
        if (!$quiz_id) throw new Exception("Erreur lors de la création du quiz");

        // Créer les questions et réponses
        foreach ($questions as $q_data) {
            $title = trim($q_data['title'] ?? '');
            $type = $q_data['type'] ?? 'qcm';

            // Points = 0 pour entreprise (option 1)
            $question_id = createQuestion($quiz_id, $title, 0);
            if (!$question_id) throw new Exception("Erreur lors de la création de la question");

            if ($type === 'qcm') {
                foreach ($q_data['answers'] as $answer) {
                    $text = trim($answer['text'] ?? '');
                    if (!empty($text)) {
                        addAnswerToQuestion($question_id, $text, 0); // pas de bonne/mauvaise réponse
                    }
                }
            }
            // Réponse libre → pas de réponse ajoutée, juste stocker lors de la participation
        }

        $_SESSION['success'] = "Quiz créé avec succès !";
        header('Location: /quizzeo/?url=entreprise');
        exit;

    } catch (Exception $e) {
        $_SESSION['form_errors'] = ['general' => $e->getMessage()];
        $_SESSION['form_data'] = $formData;
        header('Location: /quizzeo/?url=entreprise/create');
        exit;
    }

} else {
    $_SESSION['error'] = "Méthode non autorisée";
    header('Location: /quizzeo/?url=entreprise');
    exit;
}
