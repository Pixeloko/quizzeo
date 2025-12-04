<?php

//Vérifier les erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);


$url = $_GET['url'] ?? '';

switch ($url) {
    case 'login':
        require __DIR__ . '/View/login.php';
        break;

    case '':
        header('Location: ./View/home.php');
        exit;

    case 'create':
        require __DIR__ . '/View/create_account.php';
        break;

    case 'dashboard':
        if (!isset($_SESSION['user_id'])) {
            header('Location: /?url=login');
            exit;
        }
        require __DIR__ . '/View/dashboard.php';
        break;

    case 'user':
        if (!isset($_SESSION['user_id'])) {
            require __DIR__ . '/View/user.php';
            exit;
        }
        require __DIR__ . '/View/user.php';
        break;

    case 'logout':
        require __DIR__ . '/Controller/logout.php';
        break;

    case 'admin':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /?url=login');
            exit;
        }
        require __DIR__ . '/View/admin.php';
        break;
    
    case 'ecole':
        require_once __DIR__ . '/View/ecole/dashboard.php';
        break;

    case 'ecole/create':
        require_once __DIR__ . '/View/ecole/create_quizz.php';
        break;

    case 'ecole/store':
        require_once __DIR__ . '/Controller/store.php';
        break;
    
    // Redirection utilisateur
    if ($url === 'user') {
        require_once './Controller/user.php';
        $userController = new UserController();
        
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            switch ($action) {
                case 'dashboard':
                    $userController->dashboard();
                    break;
                case 'profile':
                    $userController->profile();
                    break;
                case 'update_profile':
                    $userController->updateProfile();
                    break;
                case 'available_quizzes':
                    $userController->availableQuizzes();
                    break;
                case 'quiz_results':
                    $userController->quizResults();
                    break;
                default:
                    $userController->dashboard();
            }
        } else {
            $userController->dashboard();
        }
        exit;
    }

    // ROUTE POUR ACCÉDER À UN QUIZ VIA LIEN
    if ($url === 'quiz' && isset($_GET['id'])) {
        require_once './Controller/user.php';
        $userController = new UserController();
        $userController->accessQuiz();
        exit;
    }

    default:
        http_response_code(404);
        break;
}
?>

