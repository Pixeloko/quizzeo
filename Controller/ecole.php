<?php
declare(strict_types=1);
require_once './Model/function_quizz.php'; // Inclut les fonctions existantes et ajoutées
require_once './Model/function_question.php'; // Assume un fichier pour les questions (non fourni, mais nécessaire pour lister/ajouter des questions)

class ecoleController
{
    public function dashboard()
    {
        $quizzes = getQuizzesByUser($_SESSION['user_id']); // par ex.
        require_once __DIR__ . '/../View/ecole/dashboard.php';
    }



    public function createForm()
    {
        // Formulaire de création de quiz (vide au départ)
        require_once './View/ecole/create_quiz.php';
    }


    public function edit(int $quizz_id)
    {
        $quiz = getQuizzById($quizz_id);
        if (!$quiz || $quiz['user_id'] != $_SESSION['user_id']) {
            header('Location: /ecole/dashboard');
            exit;
        }

        $questions = getQuestionsByQuizz($quizz_id);
        // Assume une fonction getAllQuestions() pour lister toutes les questions disponibles
        $allQuestions = getAllQuestions(); // À implémenter si nécessaire

        require_once './views/ecole/edit_quiz.php';
    }

    public function update(int $quizz_id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /ecole/dashboard');
            exit;
        }

        $quiz = getQuizzById($quizz_id);
        if (!$quiz || $quiz['user_id'] != $_SESSION['user_id']) {
            header('Location: /ecole/dashboard');
            exit;
        }

        // Ajouter/supprimer des questions (logique simplifiée)
        if (isset($_POST['add_question'])) {
            $question_id = (int) $_POST['question_id'];
            addQuestionToQuizz($quizz_id, $question_id);
        }
        if (isset($_POST['remove_question'])) {
            $question_id = (int) $_POST['question_id'];
            removeQuestionFromQuizz($quizz_id, $question_id);
        }

        // Lancer ou terminer le quiz
        if (isset($_POST['launch'])) {
            updateQuizzStatus($quizz_id, 'launched');
        } elseif (isset($_POST['finish'])) {
            updateQuizzStatus($quizz_id, 'finished');
        }

        header("Location: /ecole/edit/$quizz_id");
        exit;
    }

    public function show(int $quizz_id)
    {
        $quiz = getQuizzById($quizz_id);
        if (!$quiz || $quiz['user_id'] != $_SESSION['user_id'] || $quiz['status'] !== 'finished') {
            header('Location: /ecole/dashboard');
            exit;
        }

        $results = getQuizzResults($quizz_id);
        require_once './views/ecole/show_results.php';
    }

    private function determineStatus(array $quiz, array $questions): string
    {
        if ($quiz['status'] === 'finished') {
            return 'terminé';
        } elseif ($quiz['status'] === 'launched' || count($questions) > 0) {
            return 'lancé';
        } else {
            return 'en cours d\'écriture';
        }
    }
}