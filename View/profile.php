<?php
require_once('../config/config.php'); // inclusion de la fonction getDatabase()
require_once('./includes/header.php');

$pdo = getDatabase();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Récupérer les informations utilisateur
$stmt = $pdo->prepare("SELECT firstname, lastname, email, role, password FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();

$successMessage = '';
$errorMessage = '';

// --- Changer prénom, nom et email ---
if (isset($_POST['change_profile'])) {
    $newFirstname = trim($_POST['firstname']);
    $newLastname = trim($_POST['lastname']);
    $newEmail = trim($_POST['email']);

    if ($newFirstname === '' || $newLastname === '' || $newEmail === '') {
        $errorMessage = "Prénom, nom et email ne peuvent pas être vides.";
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Email invalide.";
    } else {
        // Vérifier si email déjà utilisé par un autre utilisateur
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
        $stmt->execute(['email' => $newEmail, 'id' => $user_id]);
        if ($stmt->fetch()) {
            $errorMessage = "Cet email est déjà utilisé.";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET firstname = :firstname, lastname = :lastname, email = :email WHERE id = :id");
            $stmt->execute([
                'firstname' => $newFirstname,
                'lastname' => $newLastname,
                'email' => $newEmail,
                'id' => $user_id
            ]);
            $successMessage = "Profil mis à jour !";
            $user['firstname'] = $newFirstname;
            $user['lastname'] = $newLastname;
            $user['email'] = $newEmail;
        }
    }
}

// --- Changer le mot de passe ---
if (isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $errorMessage = "Tous les champs de mot de passe doivent être remplis.";
    } elseif (!password_verify($currentPassword, $user['password'])) {
        $errorMessage = "Mot de passe actuel incorrect.";
    } elseif ($newPassword !== $confirmPassword) {
        $errorMessage = "Le nouveau mot de passe et sa confirmation ne correspondent pas.";
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->execute([
            'password' => $hashedPassword,
            'id' => $user_id
        ]);
        $successMessage = "Mot de passe mis à jour !";
    }
}

// --- Supprimer le compte ---
if (isset($_POST['delete_account'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);

    session_destroy();
    header("Location: ../index.php");
    exit;
}
?>

<h1>Profil de <?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></h1>

<?php if ($successMessage) echo "<p style='color:green;'>$successMessage</p>"; ?>
<?php if ($errorMessage) echo "<p style='color:red;'>$errorMessage</p>"; ?>

<!-- Formulaire pour modifier prénom, nom et email -->
<form method="post">
    <h2>Modifier mes informations</h2>
    <label>Prénom :</label>
    <input type="text" name="firstname" value="<?= htmlspecialchars($user['firstname']) ?>"><br>
    <label>Nom :</label>
    <input type="text" name="lastname" value="<?= htmlspecialchars($user['lastname']) ?>"><br>
    <label>Email :</label>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"><br>
    <button type="submit" name="change_profile">Mettre à jour</button>
</form>

<br>

<!-- Formulaire pour changer le mot de passe -->
<form method="post">
    <h2>Changer mon mot de passe</h2>
    <label>Mot de passe actuel :</label>
    <input type="password" name="current_password"><br>
    <label>Nouveau mot de passe :</label>
    <input type="password" name="new_password"><br>
    <label>Confirmer le nouveau mot de passe :</label>
    <input type="password" name="confirm_password"><br>
    <button type="submit" name="change_password">Changer le mot de passe</button>
</form>

<br>

<!-- Formulaire suppression compte -->
<form method="post" onsubmit="return confirm('Voulez-vous vraiment supprimer votre compte ? Cette action est irréversible !');">
    <button type="submit" name="delete_account" style="background-color:red;color:white;">Supprimer mon compte</button>
</form>
