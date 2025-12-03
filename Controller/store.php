<?php
// Controller/store.php

// Activer le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Vérifier la session
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "ecole") {
    $_SESSION["error"] = "Accès non autorisé";
    header('Location: /quizzeo/?url=login');
    exit;
}

// Inclure les modèles
require_once __DIR__ . '/../Model/function_quizz.php';
require_once __DIR__ . '/../Model/function_question.php';

// Initialiser les variables
$errors = [];
$success = false;

// Vérifier si c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Récupérer les données du formulaire
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
        // Valider chaque question
        foreach ($questions as $index => $q_data) {
            $question_text = trim($q_data['title'] ?? '');
            $point = (int)($q_data['point'] ?? 1);
            
            if (empty($question_text)) {
                $errors["question_{$index}"] = "La question " . ($index + 1) . " est vide";
            }
            
            if ($point < 1 || $point > 10) {
                $errors["point_{$index}"] = "Les points doivent être entre 1 et 10 pour la question " . ($index + 1);
            }
            
            // Valider les réponses
            $hasAnswers = false;
            foreach ($q_data['answers'] as $answer) {
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

    // Si pas d'erreurs, créer le quiz
    if (empty($errors)) {
        try {
            $user_id = $_SESSION['user_id'];

            // 1️⃣ Créer le quiz
            $quiz_id = createQuizz($name, $user_id);
            
            if (!$quiz_id) {
                throw new Exception("Erreur lors de la création du quiz");
            }

            // 2️⃣ Créer les questions et réponses
            foreach ($questions as $q_data) {
                $question_text = trim($q_data['title'] ?? '');
                $point = (int)($q_data['point'] ?? 1);
                $correct_answer_id = (int)($q_data['correct_answer'] ?? 0);

                // Créer la question
                $question_id = createQuestion($quiz_id, $question_text, $point);
                
                if (!$question_id) {
                    throw new Exception("Erreur lors de la création de la question");
                }

                // Créer les réponses
                $answer_index = 0;
                foreach ($q_data['answers'] as $answer) {
                    $answer_text = trim($answer['text'] ?? '');
                    
                    // Ne pas créer de réponses vides
                    if (!empty($answer_text)) {
                        $is_correct = ($answer['id'] == $correct_answer_id);
                        $result = addAnswerToQuestion($question_id, $answer_text, $is_correct);
                        
                        if (!$result) {
                            throw new Exception("Erreur lors de l'ajout de la réponse");
                        }
                        $answer_index++;
                    }
                }
            }

            // Succès
            $_SESSION["success"] = "✅ Quiz créé avec succès !";
            $_SESSION["quiz_created"] = $quiz_id;
            
            // Redirection vers le dashboard
            header('Location: /quizzeo/?url=ecole');
            exit;

        } catch (Exception $e) {
            $errors['general'] = "❌ Erreur : " . $e->getMessage();
            $_SESSION["error"] = $errors['general'];
            
            // Stocker les données pour réaffichage
            $_SESSION["form_data"] = [
                'name' => $name,
                'questions' => $questions
            ];
            
            // Rediriger vers le formulaire avec les erreurs
            header('Location: /quizzeo/?url=ecole/create');
            exit;
        }
    } else {
        // Stocker les erreurs et données en session
        $_SESSION["form_errors"] = $errors;
        $_SESSION["form_data"] = [
            'name' => $name,
            'questions' => $questions
        ];
        
        // Rediriger vers le formulaire
        header('Location: /quizzeo/?url=ecole/create');
        exit;
    }
} else {
    // Si accès direct sans POST, rediriger
    $_SESSION["error"] = "Méthode non autorisée";
    header('Location: /quizzeo/?url=ecole');
    exit;
}