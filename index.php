<?php
$url = $_GET['url'] ?? '';

switch ($url) {
    case 'login':
        require __DIR__ . '/View/login.php';
        break;

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
            header('Location: /?url=login');
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

    case 'ecole/update':
        require_once __DIR__ . '/Controller/ecole/update.php';
        break;




    default:
        http_response_code(404);
        break;
}
?>

<?php
require_once __DIR__ . '/Model/function_quizz.php';
require_once __DIR__ . '/Model/function_user.php';
include __DIR__ . '/View/includes/header.php';


// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure les fonctions utilisateurs


// Récupérer les quizz actifs
try {
    $quizz = getActiveQuizz();
} catch (PDOException $e) {
    echo "Erreur lors de la récupération des quiz : " . $e->getMessage();
    $quizz = [];
}

?>

<main>
    <section>

        <h1>Quizz disponibles</h1>

        <?php if (!empty($quizz)): ?>
            <?php foreach ($quizz as $q): ?>
                <article>
                    <div>
                        <time datetime="<?= htmlspecialchars($q["created_at"]) ?>">
                            <?= htmlspecialchars(formatDate($q["created_at"])) ?>
                        </time>
                    </div>
                    <h3><?= htmlspecialchars($q["title"]) ?></h3>
                    <a href="index.php?url=quizz&id=<?= (int)$q['quizz_id'] ?>">Répondre au quizz</a>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun quizz disponible pour le moment.</p>
        <?php endif; ?>
    </section>
</main>
<?php include __DIR__ . '/View/includes/footer.php'; ?>

