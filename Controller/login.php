<?php 
    require_once "View/header.php";
    require_once "Model/function_user.php";

$errors = [];
$email ="";
$password ="";

if ($_SERVER["REQUEST_METHOD"] === "POST"){

    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (!$email) {
        $errors["email"] = "Email requis";
    }

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Veuiller entrer un email valide";
    }

    if (!$password) {
        $errors["password"] = "Mot de passe requis";
    }

    $user = getUserByEmail($email);

    if (!$user) {
        $errors["general"] = "Identifiants invalides";
    } else {
        $validpassword = password_verify($password, $user["password"]);

        if (!$validpassword) {
            $errors["general"] = "Identifiant invalides";
        }
    }

    if (empty($errors)) {
        $_SESSION["message"] = "✅ Connexion réussie";
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["admin"] = (int) $user["admin"];
        header("Location: index.php");
        exit;
    }
}
?>