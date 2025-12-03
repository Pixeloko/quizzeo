<?php
    require_once __DIR__ . "/../Controller/dashboard_pro.php";
    require_once __DIR__ . "/includes/header.php";
?>

<main>
    <h1>Dashboard Pro </h1>

    <?php if (isset($_SESSION["message"])): ?>
        <div style="color: green"><?=  htmlspecialchars($_SESSION["message"]) ?></div>
        <?php unset($_SESSION["message"]) ?>
    <?php endif ?>

    <h2>Bienvenue sur votre dashboard professionnel.</h2>

    <h2>Vos quizz :</h2>
    <?php if (!empty($quizzes)): ?>
        <ul>
            <?php foreach ($quizzes as $quizz): ?>
                <li><?= htmlspecialchars($quizz['name']) ?></li>
            <?php endforeach ?>
        </ul>
    <?php else: ?>
        <p>Vous n'avez encore créé aucun quizz.</p>
    <?php endif ?>

    <a href="create_quizz.php"><button>Créer un nouveau quizz</button></a>
</main>

<?php require_once __DIR__ . "/includes/footer.php"; 
