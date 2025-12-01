<?php 
    require_once("header.php");
    require_once("../Model/function_user.php")
?>

<main>
    <form action='login.php' method="POST">
        <?php if (isset($errors["general"])): ?>
            <div style="color: red"><?= htmlspecialchars($errors["general"]) ?></div>
        <?php endif ?>

        <h1>Connexion</h1>

        <div>
            <label for="email">Email (requis) :</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" required />
            <?php if (isset($errors["email"])): ?>
                <p style="color: red"><?= htmlspecialchars($errors["email"]) ?></p>
            <?php endif ?>
        </div>

        <div>
            <label for="password">Mot de passe (requis) :</label>
            <input type="password" name="password" id="password" required />
            <?php if (isset($errors["password"])): ?>
                <p style="color: red"><?= htmlspecialchars($errors["password"]) ?></p>
            <?php endif ?>
        </div>

        <button type="submit">Se connecter</button>
    </form>
</main>

<?php require_once("footer.php"); ?>