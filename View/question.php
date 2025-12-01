<?php require_once("header.php"); ?>

<main>
    <h1>Créer un article</h1>

    <form method="POST">
        <?php if (isset($errors["general"])):?>
        <div style="color:red;"><?= $errors["general"];?></div>
        <?php endif ?>
        <div>
            <label for="title">Titre (requis) :</label>
            <input type="text" name="title" id="title" value="<?= htmlspecialchars($title) ?>">
            <?php if (isset($errors["title"])): ?>
            <p style="color: red;"><?= $errors["title"] ?></p>
            <?php endif ?>
        </div>

        <div>
            <label for="content">Contenu :</label>
            <textarea name="content" id="content" rows="10" cols="50"><?= htmlspecialchars($content) ?>
      </textarea>
        </div>

        <div>
            <label for="published">Publié : </label>
            <input type="checkbox" name="published" id="published" <?php $published ? "checked" : ""
            // Permet de retourner un bool avec ce qui est entré ?> />
        </div>

        <button>Créer</button>
    </form>
</main>

<?php require_once("./includes/footer.php") ?>

<?php require_once("footer.php"); ?>


