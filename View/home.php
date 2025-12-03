<?php
require_once __DIR__ . '/../Model/function_quizz.php';
require_once __DIR__ . '/../config/config.php';


// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure les fonctions utilisateurs
require_once __DIR__ . '/../Model/function_user.php';

// Récupérer les quizz actifs
try {
    $quizz = getActiveQuizz();
} catch (PDOException $e) {
    echo "Erreur lors de la récupération des quiz : " . $e->getMessage();
    $quizz = [];
}

// Inclure le header
include __DIR__ . '/includes/header.php';
?>

<header>
    <h1>Quizz disponibles</h1>
</header>

<section>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
