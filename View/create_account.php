<?php 
session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
require_once '../config/config.php';

$errors = $errors ?? [];
$firstname = $firstname ?? "";
$lastname = $lastname ?? "";
$email = $email ?? "";
$role = $role ?? "user";
?>

<main>
    <h1>Inscription</h1>

    <form class="sub-form" method="POST" action="create">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
        <?php if (isset($errors["general"])): ?>
        <div style="color: #780000"><?= $errors["general"] ?></div>
        <?php endif ?>
        <div>
            <label for="firstname">Prénom :</label>
            <input type="text" name="firstname" id="firstname" value="<?= htmlspecialchars($firstname) ?>" required />
            <?php if (isset($errors["firstname"])): ?>
            <p style="color: #780000;"><?= $errors["firstname"] ?></p>
            <?php endif ?>
        </div>

        <div>
            <label for="lastname">Nom :</label>
            <input type="text" name="lastname" id="lastname" value="<?= htmlspecialchars($lastname) ?>" required />
            <?php if (isset($errors["lastname"])): ?>
            <p style="color: #780000;"><?= $errors["lastname"] ?></p>
            <?php endif ?>
        </div>


        <div>
            <label for="email">Email :</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" required />
            <?php if (isset($errors["email"])): ?>
            <p style="color: #780000;"><?= $errors["email"] ?></p>
            <?php endif ?>
        </div>

        <div>
            <label for="password">Mot de passe :</label>
            <input type="password" name="password" id="password" required />
            <?php if (isset($errors["password"])): ?>
            <p style="color: #780000;"><?= $errors["password"] ?></p>
            <?php endif ?>
        </div>

        <div>
            <label for="role">Votre rôle :</label>
            <select name="role" id="role">
                <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>user</option>
                <option value="ecole" <?= $role === 'ecole' ? 'selected' : '' ?>>école</option>
                <option value="entreprise" <?= $role === 'entreprise' ? 'selected' : '' ?>>entreprise</option>
            </select>
            <?php if (isset($errors["role"])): ?>
            <p style=\"color: #780000;\"><?= $errors["role"] ?></p>
            <?php endif ?>
        </div>

        <button>S'inscrire</button>
    </form>
</main>

<?php require_once("footer.php") ?>
