<?php
declare(strict_types=1);
require_once __DIR__ . '/../Model/function_quizz.php';
require_once __DIR__ . '/../Model/function_question.php';

class entrepriseController
{
    public function dashboard()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'entreprise') {
            header('Location: /quizzeo/?url=login');
            exit;
        }

        $quizzes = getQuizzesByUser($_SESSION['user_id']);
        require_once __DIR__ . '/../View/entreprise/dashboard.php';
    }

    public function createForm()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'entreprise') {
            header('Location: /quizzeo/?url=login');
            exit;
        }

        require_once __DIR__ . '/../View/entreprise/create_quiz.php';
    }

    public function edit(int $quizz_id)
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'entreprise') {
            header('Location: /quizzeo/?url=login');
            exit;
        }

        $quiz = getQuizzById($quizz_id);
        if (!$quiz || $quiz['user_id'] != $_SESSION['user_id']) {
            header('Location: /quizzeo/View/entreprise/dashboard.php');
            exit;
        }

        $questions = getQuestionsByQuizz($quizz_id);
        // $allQuestions = getAllQuestions(); // à implémenter si nécessaire

        require_once __DIR__ . '/../View/entreprise/edit_quiz.php';
    }

    public function update(int $quizz_id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /quizzeo/View/entreprise/dashboard.php');
            exit;
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'entreprise') {
            header('Location: /quizzeo/?url=login');
            exit;
        }

        $quiz = getQuizzById($quizz_id);
        if (!$quiz || $quiz['user_id'] != $_SESSION['user_id']) {
            header('Location: /quizzeo/View/entreprise/dashboard.php');
            exit;
        }

        if (isset($_POST['add_question'])) {
            $question_id = (int) $_POST['question_id'];
            addQuestionToQuizz($quizz_id, $question_id);
        }

        if (isset($_POST['remove_question'])) {
            $question_id = (int) $_POST['question_id'];
            removeQuestionFromQuizz($quizz_id, $question_id);
        }

        if (isset($_POST['launch'])) {
            updateQuizzStatus($quizz_id, 'launched');
        } elseif (isset($_POST['finish'])) {
            updateQuizzStatus($quizz_id, 'finished');
        }

        header("Location: /quizzeo/View/entreprise/edit_quiz.php?id=$quizz_id");
        exit;
    }

    public function show(int $quizz_id)
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'entreprise') {
            header('Location: /quizzeo/?url=login');
            exit;
        }

        $quiz = getQuizzById($quizz_id);
        if (!$quiz || $quiz['user_id'] != $_SESSION['user_id'] || $quiz['status'] !== 'finished') {
            header('Location: /quizzeo/View/entreprise/dashboard.php');
            exit;
        }

        $results = getQuizzResults($quizz_id);
        require_once __DIR__ . '/../View/entreprise/show_results.php';
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
