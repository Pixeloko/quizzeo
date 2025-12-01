<?php require_once("header.php"); ?>

<main>
    <h1>Créer une nouvelle question </h1>

    <form method="POST">
        <?php if (isset($errors["general"])):?>
        <div style="color:red;"><?= $errors["general"];?></div>
        <?php endif ?>
        <div>
            <label for="title">La question :</label>
            <input type="text" name="title" id="title" value="<?= htmlspecialchars($title) ?>">
            <?php if (isset($errors["title"])): ?>
            <p style="color: red;"><?= $errors["title"] ?></p>
            <?php endif ?>
        </div>

        <div>
            <label for="answer">Réponse :</label>
            <input name="answer" id="answer" value="<?= htmlspecialchars($answer) ?>">
        </div>

        <div>
            <label for="point">Points : </label>
            <input type="number" name="point" id="point" value="<?php $point?>">
        </div>

        <button>Créer</button>
    </form>
</main>

<?php require_once("./includes/footer.php") ?>

<?php require_once("footer.php"); ?>


