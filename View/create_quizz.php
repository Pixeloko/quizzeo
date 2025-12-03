<?php 
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/../Controller/create_quizz.php";
?>

<main>
    <h1>Créer un nouveau quiz</h1>

    <?php if (!empty($errors["name"])): ?>
        <p style="color:red"><?= htmlspecialchars($errors["name"]) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="name">Titre du quiz :</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>">
        <button type="submit">Créer le quiz</button>
    </form>
</main>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
