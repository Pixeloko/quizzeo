<?php
require_once('../config/config.php');
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
$stmt = $pdo->prepare("SELECT firstname, lastname, email, role, password, profile_photo FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();

$successMessage = '';
$errorMessage = '';

// --- Changer prénom, nom, email et photo ---
if (isset($_POST['change_profile'])) {
    $newFirstname = trim($_POST['firstname']);
    $newLastname = trim($_POST['lastname']);
    $newEmail = trim($_POST['email']);

    if ($newFirstname === '' || $newLastname === '' || $newEmail === '') {
        $errorMessage = "Prénom, nom et email ne peuvent pas être vides.";
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Email invalide.";
    } else {
        // Vérifier si email déjà utilisé
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
        $stmt->execute(['email' => $newEmail, 'id' => $user_id]);

        if ($stmt->fetch()) {
            $errorMessage = "Cet email est déjà utilisé.";
        } else {
            // Gestion upload photo
            $profilePhotoName = $user['profile_photo']; // garder l'ancienne si pas de nouvelle
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $tmpName = $_FILES['profile_photo']['tmp_name'];
                $originalName = basename($_FILES['profile_photo']['name']);
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                // Vérifier extension autorisée
                $allowed = ['jpg','jpeg','png','gif'];
                if (!in_array($ext, $allowed)) {
                    $errorMessage = "Format de fichier non autorisé. (jpg, jpeg, png, gif)";
                } else {
                    // Générer un nom unique
                    $newFileName = uniqid('profile_', true) . '.' . $ext;
                    if (move_uploaded_file($tmpName, $uploadDir . $newFileName)) {
                        $profilePhotoName = $newFileName;
                    } else {
                        $errorMessage = "Erreur lors de l'upload de l'image.";
                    }
                }
            }

            if (!$errorMessage) {
                $stmt = $pdo->prepare("UPDATE users SET firstname = :firstname, lastname = :lastname, email = :email, profile_photo = :profile_photo WHERE id = :id");
                $stmt->execute([
                    'firstname' => $newFirstname,
                    'lastname' => $newLastname,
                    'email' => $newEmail,
                    'profile_photo' => $profilePhotoName,
                    'id' => $user_id
                ]);

                $successMessage = "Profil mis à jour !";
                $user['firstname'] = $newFirstname;
                $user['lastname'] = $newLastname;
                $user['email'] = $newEmail;
                $user['profile_photo'] = $profilePhotoName;
            }
        }
    }
}

// --- Changer le mot de passe ---
if (isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $errorMessage = "Tous les champs doivent être remplis.";
    } elseif (!password_verify($currentPassword, $user['password'])) {
        $errorMessage = "Mot de passe actuel incorrect.";
    } elseif ($newPassword !== $confirmPassword) {
        $errorMessage = "Les mots de passe ne correspondent pas.";
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
