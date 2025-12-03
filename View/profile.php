<?php
require_once('../config/config.php');
require_once('./includes/header.php');
require_once __DIR__ . "/../Model/function_user.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../View/login.php");
    exit;
}

$user_id = (int) $_SESSION["user_id"];
$user = getUserById($user_id);

if (!$user) {
    $_SESSION["error"] = "Utilisateur introuvable.";
    header("Location: ../index.php");
    exit;
}

$successMessage = "";
$errorMessage = "";

// 🔹 Mise à jour du profil (surname, email)
if (isset($_POST["update_profile"])) {
    $firstname = trim($_POST["firstname"]);
    $lastname = trim($_POST["lastname"]);
    $email = trim($_POST["email"]);

    if ($firstname === "" || $lastname === "" || $email === "") {
        $errorMessage = "Tous les champs doivent être remplis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Email invalide.";
    } else {
        // Vérifier si email déjà utilisé par un autre user
        $check = getUserByEmail($email);
        if ($check && $check["id"] != $user_id) {
            $errorMessage = "Email déjà utilisé.";
        } else {
            updateUser($user_id, $firstname, $lastname, $email, null);
            $successMessage = "Profil mis à jour !";
            $user = getUserById($user_id); // refresh infos
        }
    }
}

// 🔹 Changer mot de passe
if (isset($_POST["change_password"])) {
    $current = $_POST["current_password"];
    $new = $_POST["new_password"];
    $confirm = $_POST["confirm_password"];

    if ($current === "" || $new === "" || $confirm === "") {
        $errorMessage = "Tous les champs doivent être remplis.";
    } elseif (!password_verify($current, $user["password"])) {
        $errorMessage = "Mot de passe actuel incorrect.";
    } elseif ($new !== $confirm) {
        $errorMessage = "Les mots de passe ne correspondent pas.";
    } else {
        updateUser($user_id, $user["firstname"], $user["lastname"], $user["email"], $new);
        $successMessage = "Mot de passe mis à jour !";
    }
}

// 🔹 Supprimer le compte
if (isset($_POST["delete_account"])) {
    deleteUser($user_id);
    session_destroy();
    header("Location: ../index.php");
    exit;
}
?>

<div class="profile-container">

    <h1>Profil de <?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></h1>

    <?php if ($successMessage): ?>
        <p class="profile-success"><?= $successMessage ?></p>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <p class="profile-error"><?= $errorMessage ?></p>
    <?php endif; ?>

    <!-- Modifier informations et photo -->
    <form method="post" enctype="multipart/form-data">
        <h2>Modifier mes informations</h2>

        <label>Prénom :</label>
        <input type="text" name="firstname" value="<?= htmlspecialchars($user['firstname']) ?>">

        <label>Nom :</label>
        <input type="text" name="lastname" value="<?= htmlspecialchars($user['lastname']) ?>">

        <label>Email :</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">

        <label>Photo de profil :</label>
        <input type="file" name="profile_photo" accept="image/*">
        <?php if (!empty($user['profile_photo'])): ?>
            <div>
                <img src="../uploads/<?= htmlspecialchars($user['profile_photo']) ?>" alt="Photo de profil" class="profile-photo" style="width:100px;height:100px;border-radius:50%;margin-top:10px;">
            </div>
        <?php endif; ?>

        <button type="submit" name="change_profile">Mettre à jour</button>
    </form>

    <!-- Changer mot de passe -->
    <form method="post">
        <h2>Changer mon mot de passe</h2>

        <label>Mot de passe actuel :</label>
        <input type="password" name="current_password">

        <label>Nouveau mot de passe :</label>
        <input type="password" name="new_password">

        <label>Confirmer le nouveau mot de passe :</label>
        <input type="password" name="confirm_password">

        <button type="submit" name="change_password">Changer le mot de passe</button>
    </form>

    <!-- Supprimer compte -->
    <form method="post" onsubmit="return confirm('Voulez-vous vraiment supprimer votre compte ? Cette action est irréversible !');">
        <button type="submit" name="delete_account" class="delete-btn">Supprimer mon compte</button>
    </form>

</div>
