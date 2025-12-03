<?php
    require_once ('includes/header.php');
?>

<main>
    <h1>Dashboard Pro </h1>

    <h2>Bienvenue sur votre dashboard professionnel.</h2>

    <h2>Vos quizz :</h2>
    <?php if (!empty($quizzes)): ?>
        <ul>
            <?php foreach ($quizzes as $quizz): ?>
                <li><?= htmlspecialchars($quizz['title']) ?></li>
            <?php endforeach ?>
        </ul>
    <?php else: ?>
        <p>Vous n'avez encore créé aucun quizz.</p>
    <?php endif ?>

    <a href="question.php"><button>Créer un nouveau quizz</button></a>
</main>

<?php require_once __DIR__ . "/includes/footer.php"; 
