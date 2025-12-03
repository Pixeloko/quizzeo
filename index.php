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
        require __DIR__ . '/View/dashboard.php';
        break;

    case 'user':
        require __DIR__ . '/View/user.php';
        break;

    case 'logout':
        require __DIR__ . '/Controller/logout.php';
        break;

    case 'admin':
        require __DIR__ .'/View/admin.php';
        break;


}
?>

<?php
require_once __DIR__ . '/Model/functions_quizz.php';
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

