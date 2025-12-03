<?php
session_start();
require_once __DIR__ . "/../Model/function_quizz.php";
require_once __DIR__ . "/../Model/function_question.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $questions = $_POST['questions'] ?? [];

    $errors = [];

    // Validation du nom du quiz
    if (empty($name)) {
        $errors['name'] = "Le nom du quiz est obligatoire";
    }

    if (empty($errors)) {
        $user_id = $_SESSION['user_id'];

        // 1️⃣ Créer le quiz
        $quiz_id = createQuizz($name, $user_id);

        // 2️⃣ Créer les questions et réponses
        foreach ($questions as $q_data) {
            $question_text = $q_data['title'] ?? '';
            $point = (int)($q_data['point'] ?? 1);
            $correct_answer_id = (int)($q_data['correct_answer'] ?? 0);

            // Créer la question
            $question_id = createQuestion($quiz_id, $question_text, $point);

            // Créer les réponses
            foreach ($q_data['answers'] as $answer) {
                $answer_text = $answer['text'] ?? '';
                $is_correct = ($answer['id'] == $correct_answer_id);
                addAnswerToQuestion($question_id, $answer_text, $is_correct);
            }
        }

        // Redirection vers la liste des quizzes
        header('Location: /quizzeo/index.php?url=ecole/quizzes');
        exit;
    }
}
