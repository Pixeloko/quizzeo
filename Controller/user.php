<?php
// Controller/user.php

class UserController
{
    public function dashboard()
    {
        session_start();
        
        // Vérifier l'authentification
        if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "user") {
            header("Location: /quizzeo/?url=login");
            exit;
        }
        
        require_once __DIR__ . '/../Model/function_user.php';
        require_once __DIR__ . '/../Model/function_quizz.php';
        
        $user_id = $_SESSION['user_id'];
        
        // Récupérer les quiz déjà répondu
        $answered_quizzes = getAnsweredQuizzes($user_id);
        
        // Récupérer les quiz disponibles
        $available_quizzes = getAvailableQuizzesForUser($user_id);
        
        // Charger la vue
        require_once __DIR__ . '/../View/user/dashboard.php';
    }
    
    public function availableQuizzes()
    {
        session_start();
        
        if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "user") {
            header("Location: /quizzeo/?url=login");
            exit;
        }
        
        require_once __DIR__ . '/../Model/function_user.php';
        require_once __DIR__ . '/../Model/function_quizz.php';
        
        $user_id = $_SESSION['user_id'];
        $available_quizzes = getAvailableQuizzesForUser($user_id);
        
        require_once __DIR__ . '/../View/user/available_quizzes.php';
    }
    
    public function accessQuiz()
    {
        session_start();
        
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "user") {
            // Rediriger vers la connexion avec un message
            $_SESSION['error'] = "Veuillez vous connecter pour accéder au quiz";
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header("Location: /quizzeo/?url=login");
            exit;
        }
        
        $quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($quiz_id <= 0) {
            $_SESSION['error'] = "Quiz non trouvé";
            header("Location: /quizzeo/?url=user");
            exit;
        }
        
        require_once __DIR__ . '/../Model/function_quizz.php';
        require_once __DIR__ . '/../Model/function_question.php';
        
        // Récupérer le quiz
        $quiz = getQuizzById($quiz_id);
        
        if (!$quiz || $quiz['status'] !== 'launched') {
            $_SESSION['error'] = "Ce quiz n'est pas disponible";
            header("Location: /quizzeo/?url=user");
            exit;
        }
        
        // Récupérer les questions
        $questions = getQuestionsByQuizzId($quiz_id);
        
        if (empty($questions)) {
            $_SESSION['error'] = "Ce quiz n'a pas de questions";
            header("Location: /quizzeo/?url=user");
            exit;
        }
        
        // Vérifier si l'utilisateur a déjà répondu
        require_once __DIR__ . '/../Model/function_user.php';
        $has_answered = false;
        
        // Charger la vue du quiz
        require_once __DIR__ . '/../View/user/play_quiz.php';
    }
    
    public function submitQuiz()
    {
        session_start();
        
        if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "user") {
            header("Location: /quizzeo/?url=login");
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /quizzeo/?url=user");
            exit;
        }
        
        $quiz_id = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;
        $answers = $_POST['answers'] ?? [];
        
        require_once __DIR__ . '/../Model/function_user.php';
        require_once __DIR__ . '/../Model/function_quizz.php';
        
        $user_id = $_SESSION['user_id'];
        
        // Sauvegarder chaque réponse
        foreach ($answers as $question_id => $answer_id) {
            if ($answer_id > 0) {
                saveUserAnswer($user_id, $question_id, $answer_id);
            }
        }
        
        $_SESSION['success'] = "Vos réponses ont été enregistrées !";
        header("Location: /quizzeo/?url=user&action=quiz_results&id=" . $quiz_id);
        exit;
    }
    
    public function quizResults()
    {
        session_start();
        
        if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "user") {
            header("Location: /quizzeo/?url=login");
            exit;
        }
        
        $quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($quiz_id <= 0) {
            header("Location: /quizzeo/?url=user");
            exit;
        }
        
        require_once __DIR__ . '/../Model/function_user.php';
        require_once __DIR__ . '/../Model/function_quizz.php';
        
        $user_id = $_SESSION['user_id'];
        $results = getUserQuizResults($user_id, $quiz_id);
        $quiz = getQuizzById($quiz_id);
        
        require_once __DIR__ . '/../View/user/quiz_results.php';
    }
    
    public function profile()
    {
        session_start();
        
        if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "user") {
            header("Location: /quizzeo/?url=login");
            exit;
        }
        
        require_once __DIR__ . '/../Model/function_user.php';
        
        $user_id = $_SESSION['user_id'];
        $profile = getUserProfile($user_id);
        
        require_once __DIR__ . '/../View/user/profile.php';
    }
    
    public function updateProfile()
    {
        session_start();
        
        if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "user") {
            header("Location: /quizzeo/?url=login");
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /quizzeo/?url=user&action=profile");
            exit;
        }
        
        require_once __DIR__ . '/../Model/function_user.php';
        
        $user_id = $_SESSION['user_id'];
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? ''
        ];
        
        // Validation
        $errors = [];
        
        if (empty($data['first_name'])) {
            $errors['first_name'] = "Le prénom est obligatoire";
        }
        
        if (empty($data['last_name'])) {
            $errors['last_name'] = "Le nom est obligatoire";
        }
        
        if (empty($data['email'])) {
            $errors['email'] = "L'email est obligatoire";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Email invalide";
        }
        
        // Vérifier si l'email existe déjà pour un autre utilisateur
        if (empty($errors['email'])) {
            $pdo = getConnexion();
            $sql = "SELECT id FROM users WHERE email = :email AND id != :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['email' => $data['email'], 'id' => $user_id]);
            
            if ($stmt->fetch()) {
                $errors['email'] = "Cet email est déjà utilisé";
            }
        }
        
        // Si un nouveau mot de passe est fourni
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                $errors['password'] = "Le mot de passe doit contenir au moins 6 caractères";
            }
        }
        
        if (empty($errors)) {
            // Si pas de nouveau mot de passe, on le retire
            if (empty($data['password'])) {
                unset($data['password']);
            }
            
            $success = updateUserProfile($user_id, $data);
            
            if ($success) {
                $_SESSION['success'] = "Profil mis à jour avec succès";
                // Mettre à jour la session si l'email a changé
                if (isset($data['email'])) {
                    $_SESSION['email'] = $data['email'];
                }
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour du profil";
            }
        } else {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $data;
        }
        
        header("Location: /quizzeo/?url=user&action=profile");
        exit;
    }
}