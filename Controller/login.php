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

        if (empty($errors)) {

        $user = getUserByEmail($email);

        if (!$user || !password_verify($password, $user["password"])) {
            $errors["general"] = "Identifiants invalides";
        } else {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["role"] = $user["role"];

            $_SESSION["message"] = "Connexion réussie !";

            if ($user["role"] === "admin") {
                header("Location: ../View/admin.php");
            } elseif ($user["role"] === "ecole" || $user["role"] === "entreprise") {
                header("Location: /quizzeo/ecole");
            } else {
                header("Location: ../View/user.php");
            }
            exit;
        }
        }
    }
?>