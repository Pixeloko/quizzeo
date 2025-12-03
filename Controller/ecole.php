<?php
declare(strict_types=1);
require_once './functions_quizz.php'; // Inclut les fonctions existantes et ajoutées
require_once './function_question.php'; // Assume un fichier pour les questions (non fourni, mais nécessaire pour lister/ajouter des questions)

class SchoolController
{
    public function dashboard()
    {
        // Vérifier le rôle
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ecole') {
            header('Location: /login');
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $quizzes = getQuizzByUser($user_id);

        // Enrichir les données pour chaque quiz
        foreach ($quizzes as &$quiz) {
            $questions = getQuestionsByQuizz($quiz['id']);
            $quiz['status'] = $this->determineStatus($quiz, $questions);
            $quiz['response_count'] = countSubmissions($quiz['id']);
        }

        // Passer à la vue
        require_once './views/school/dashboard.php';
    }

    public function createForm()
    {
        // Formulaire de création de quiz (vide au départ)
        require_once './views/school/create_quiz.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /school/dashboard');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $user_id = $_SESSION['user_id'];

        try {
            $quizz_id = createQuizz($name, $user_id);
            // Rediriger vers l'édition du quiz
            header("Location: /school/edit/$quizz_id");
            exit;
        } catch (InvalidArgumentException $e) {
            $errors = json_decode($e->getMessage(), true);
            require_once './views/school/create_quiz.php';
        }
    }

    public function edit(int $quizz_id)
    {
        $quiz = getQuizzById($quizz_id);
        if (!$quiz || $quiz['user_id'] != $_SESSION['user_id']) {
            header('Location: /school/dashboard');
            exit;
        }

        $questions = getQuestionsByQuizz($quizz_id);
        // Assume une fonction getAllQuestions() pour lister toutes les questions disponibles
        $allQuestions = getAllQuestions(); // À implémenter si nécessaire

        require_once './views/school/edit_quiz.php';
    }

    public function update(int $quizz_id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /school/dashboard');
            exit;
        }

        $quiz = getQuizzById($quizz_id);
        if (!$quiz || $quiz['user_id'] != $_SESSION['user_id']) {
            header('Location: /school/dashboard');
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

        header("Location: /school/edit/$quizz_id");
        exit;
    }

    public function show(int $quizz_id)
    {
        $quiz = getQuizzById($quizz_id);
        if (!$quiz || $quiz['user_id'] != $_SESSION['user_id'] || $quiz['status'] !== 'finished') {
            header('Location: /school/dashboard');
            exit;
        }

        $results = getQuizzResults($quizz_id);
        require_once './views/school/show_results.php';
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