<?php 
    require_once("Controller/login.php");
    require_once("View/includes/header.php");
   
?>

<main>
    <form action="?url=login" method="POST">
 
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

        <button  type="submit">Se connecter</button>
    </form>
    <a href="View/create_account.php">S'inscrire</a>
</main>

<?php require_once("footer.php"); ?>