<?php
require_once("./includes/header.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Récupérer les informations utilisateur
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
$stmt->execute(["id" => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$username = $user['username'];

$successMessage = '';
$errorMessage = '';

// Changer le username
if (isset($_POST['change_username'])) {
    $newUsername = trim($_POST['username']);
    if ($newUsername !== '') {
        $stmt = $pdo->prepare("UPDATE users SET username = :username WHERE id = :id");
        $stmt->execute(['username' => $newUsername, 'id' => $user_id]);
        $successMessage = "Username mis à jour !";
        $username = $newUsername;
    } else {
        $errorMessage = "Le username ne peut pas être vide.";
    }
}


// Supprimer le compte
if (isset($_POST['delete_account'])) {
    // Supprimer la photo si existante
    if ($user['profile_pic'] && file_exists($user['profile_pic'])) {
        unlink($user['profile_pic']);
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);

    session_destroy();
    header("Location: goodbye.php"); // page d’au revoir
    exit;
}
?>

<h1>Profil de <?= htmlspecialchars($username) ?></h1>

<?php if ($successMessage) echo "<p style='color:green;'>$successMessage</p>"; ?>
<?php if ($errorMessage) echo "<p style='color:red;'>$errorMessage</p>"; ?>


<!-- Formulaire changement username -->
<form method="post">
    <label>Nouveau username :</label>
    <input type="text" name="username" value="<?= htmlspecialchars($username) ?>">
    <button type="submit" name="change_username">Changer</button>
</form>
<br>

<!-- Formulaire suppression compte -->
<form method="post" onsubmit="return confirm('Voulez-vous vraiment supprimer votre compte ? Cette action est irréversible !');">
    <button type="submit" name="delete_account" style="background-color:red;color:white;">Supprimer mon compte</button>
</form>
