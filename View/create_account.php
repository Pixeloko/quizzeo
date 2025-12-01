<?php require_once("header.php"); ?>

<main>
    <h1>Inscription</h1>

    <form class="sub-form" method="POST" action="./Controller/create_account.php">
        <?php if (isset($errors["general"])): ?>
        <div style="color: #780000"><?= $errors["general"] ?></div>
        <?php endif ?>

        <div>
            <label for="username">Nom d'utilisateur :</label>
            <input type="username" name="username" id="username" value="<?= htmlspecialchars($username) ?>" />
            <?php if (isset($errors["username"])): ?>
            <p style="color: #780000;"><?= $errors["username"] ?></p>
            <?php endif ?>
        </div>

        <div>
            <label for="email">Email :</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" />
            <?php if (isset($errors["email"])): ?>
            <p style="color: #780000;"><?= $errors["email"] ?></p>
            <?php endif ?>
        </div>

        <div>
            <label for="password">Mot de passe :</label>
            <input type="password" name="password" id="password" value="<?= htmlspecialchars($password) ?>" />
            <?php if (isset($errors["password"])): ?>
            <p style="color: #780000;"><?= $errors["password"] ?></p>
            <?php endif ?>
        </div>
        <!-- Choix du type d'utilisateur : école, entreprise, user-->
        <div>
            <label for="role">Votre rôle :</label>
            <select name="role" id="role" value="<?= htmlspecialchars($role) ?>">
            <option value="user">user</option>
            <option value="ecole">école</option>
            <option value="entreprise">entreprise</option>
            </select>
            <?php if (isset($errors["role"])): ?>
            <p style="color: #780000;"><?= $errors["role"] ?></p>
            <?php endif ?>
        </div>


        <button>Se connecter</button>
    </form>
</main>

<?php require_once("footer.php") ?>