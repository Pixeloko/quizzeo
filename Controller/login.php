<?php 
   require_once __DIR__ . "/../View/includes/header.php";
  require_once __DIR__ . "/../Model/function_user.php";


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
        $_SESSION["user_role"] = $user["role"]; // stocker le rôle en session

            // redirection selon le rôle
            if ($user["role"] === "admin") {
                header("Location: View/admin.php"); 
                exit;
            } if ($user["role"] === "ecole" || $user["role"] === "entreprise") {
                header("Location: dashboard_pro.php"); 
                exit;
            } else {
                header("Location: home.php"); 
                exit;
            }
        
    }
}

// Redirection
?>