<?php


// Démarrage de session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// URL de base pour les redirections
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/quizzeo';

// Debug
echo "<!-- DEBUG: url = " . ($_GET['url'] ?? 'none') . " -->\n";
echo "<!-- DEBUG: method = " . $_SERVER['REQUEST_METHOD'] . " -->\n";

// Récupérer l'URL demandée
$url = $_GET['url'] ?? '';

// Router les requêtes
switch ($url) {
    // PAGE D'ACCUEIL
    case '':
    case 'home':
        require __DIR__ . '/View/home.php';
        break;

    // AUTHENTIFICATION
    case 'login':
        require __DIR__ . '/View/login.php';
        break;

    case 'create_account':
        require __DIR__ . '/View/create_account.php';
        break;

    case 'logout':
        require __DIR__ . '/Controller/logout.php';
        break;

    // DASHBOARD UTILISATEUR
    case 'user':
        if (!isset($_SESSION['user_id'])) {
            header("Location: $base_url/?url=login");
            exit;
        }
        // Vérifier l'action demandée
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            switch ($action) {
                case 'dashboard':
                    require __DIR__ . '/View/user/dashboard.php';
                    break;
                case 'profile':
                    require __DIR__ . '/View/user/profile.php';
                    break;
                case 'available_quizzes':
                    require __DIR__ . '/View/user/available_quiz.php';
                    break;
                case 'quiz_results':
                    require __DIR__ . '/View/user/quiz_result.php';
                    break;
                default:
                    require __DIR__ . '/View/user/dashboard.php';
            }
        } else {
            require __DIR__ . '/View/user/dashboard.php';
        }
        break;

    // JOUER À UN QUIZ
    case 'play_quiz':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
            header("Location: $base_url/?url=login");
            exit;
        }
        require __DIR__ . '/View/user/play_quiz.php';
        break;

    // SOUMETTRE UN QUIZ
    case 'submit_quiz':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
            header("Location: $base_url/?url=login");
            exit;
        }
        require __DIR__ . '/Controller/submit_quiz.php';
        break;

    // ADMIN
    case 'admin':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header("Location: $base_url/?url=login");
            exit;
        }
        require __DIR__ . '/View/admin.php';
        break;

    // ÉCOLE
    case 'ecole':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ecole') {
            header("Location: $base_url/?url=login");
            exit;
        }
        require __DIR__ . '/View/ecole/dashboard.php';
        break;

    case 'ecole/create':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ecole') {
            header("Location: $base_url/?url=login");
            exit;
        }
        require __DIR__ . '/View/ecole/create_quizz.php';
        break;

    case 'ecole/store':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ecole') {
            header("Location: $base_url/?url=login");
            exit;
        }
        require __DIR__ . '/Controller/store.php';
        break;

    // ENTREPRISE
    case 'entreprise':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'entreprise') {
            header("Location: $base_url/?url=login");
            exit;
        }
        require __DIR__ . '/View/entreprise/dashboard.php';
        break;

    case 'entreprise/create':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'entreprise') {
            header("Location: $base_url/?url=login");
            exit;
        }
        require __DIR__ . '/View/entreprise/create_quizz.php';
        break;

    case 'entreprise/store':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'entreprise') {
            header("Location: $base_url/?url=login");
            exit;
        }
        require __DIR__ . '/Controller/store_entreprise.php';
        break;

    case 'quiz':
        if (isset($_GET['id'])) {
            // Jouer à un quiz spécifique
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
                header("Location: $base_url/?url=login");
                exit;
            }
            require __DIR__ . '/View/quizz.php';  // C'est ici que ça devrait pointer
        } else {
            // Liste des quiz
            require __DIR__ . '/View/quizz.php';
        }
        break;

    // PAGE 404
    default:
        http_response_code(404);
        echo "<h1>404 - Page non trouvée</h1>";
        echo "<p>La page '$url' n'existe pas.</p>";
        echo "<a href='$base_url/'>Retour à l'accueil</a>";
        break;
}
?>