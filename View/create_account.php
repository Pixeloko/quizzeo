<?php 
require_once("header.php"); 
require_once("./Controller/create_account.php"); 

// Initialisation
$errors = $errors ?? [];
$username = $username ?? '';
$email = $email ?? '';
$role = $role ?? 'user';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<main>
    <h1>Inscription</h1>

    <form class="sub-form" method="POST" action="./Controller/create_account.php">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
        <?php if (isset($errors["general"])): ?>
        <div style="color: #780000"><?= $errors["general"] ?></div>
        <?php endif ?>

        <div>
            <label for="username">Nom d'utilisateur :</label>
            <input type="text" name="username" id="username" value="<?= htmlspecialchars($username) ?>" required />
            <?php if (isset($errors["username"])): ?>
            <p style="color: #780000;"><?= $errors["username"] ?></p>
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
