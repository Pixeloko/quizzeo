<?php 
require_once '../config/config.php';


session_start();
require_once './Controller/create_account'
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$errors = $errors ?? [];
$firstname = $firstname ?? "";
$lastname = $lastname ?? "";
$email = $email ?? "";
$role = $role ?? "user";
?>

<main>
    <h1>Création de votre compte</h1>
    <?php if (isset($errors["general"])): ?>
        <div style="color:red"><?= $errors["general"] ?></div>
    <?php endif; ?>

    <form method="POST">
        <h3>Entrez vos informations :</h3>

        <p>
            <label for="firstname">Prénom :</label>
            <input type="text" id="firstname" name="firstname" value="<?= htmlspecialchars($firstname) ?>">
            <?php if (isset($errors["firstname"])): ?>
                <span style="color: red;"><?= $errors["firstname"] ?></span>
            <?php endif ?>
        </p>

        <p>
            <label for="lastname">Nom :</label>
            <input type="text" id="lastname" name="lastname" value="<?= htmlspecialchars($lastname) ?>">
            <?php if (isset($errors["lastname"])): ?>
                <span style="color: red;"><?= $errors["lastname"] ?></span>
            <?php endif ?>
        </p>

        <p>
            <label for="email">Email :</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="monemail@exemple.com">
            <?php if (isset($errors["email"])): ?>
                <span style="color: red;"><?= $errors["email"] ?></span>
            <?php endif ?>
        </p>

        <p>
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" value="<?= htmlspecialchars($password) ?>">
            <?php if (isset($errors["password"])): ?>
                <span style="color: red;"><?= $errors["password"] ?></span>
            <?php endif ?>
        </p>

        <div>
            <label for="role">Votre rôle :</label>
            <select name="role" id="role">
                <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>user</option>
                <option value="ecole" <?= $role === 'ecole' ? 'selected' : '' ?>>école</option>
                <option value="entreprise" <?= $role === 'entreprise' ? 'selected' : '' ?>>entreprise</option>
            </select>
            <?php if (isset($errors["role"])): ?>
            <p style="color: #780000;\"><?= $errors["role"] ?></p>
                <span style="color: red;"><?= $errors["role"] ?></span>
            <?php endif ?>
            </div>

        <input type="submit" value="Créer">
    </form>
</main>

<?php require_once("footer.php") ?>