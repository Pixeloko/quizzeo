<?php
require_once "./Model/function_user.php"; 
require_once "./View/header.php";

$errors = [];
$firstname = "";
$lastname = "";
$email = "";
$password = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $firstname = trim($_POST["firstname"] ?? "");
    $lastname  = trim($_POST["lastname"] ?? "");
    $email     = trim($_POST["email"] ?? "");
    $password  = trim($_POST["password"] ?? "");

    if (!$firstname) {
        $errors["firstname"] = "Prénom requis";
    }

    if (!$lastname) {
        $errors["lastname"] = "Nom requis";
    }

    if (!$email) {
        $errors["email"] = "Email requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Veuillez entrer un email valide";
    }

    if (!$password) {
        $errors["password"] = "Mot de passe requis";
    }

    if (empty($errors)) {
        try {
            createUser($role, $firstname, $lastname, $email, $password);
            $_SESSION["message"] = "✅ Compte créé avec succès !";
            header("Location: ./View/login.php");
            exit;
        } catch (Exception $e) {
            $errors["general"] = "❌ Impossible de créer un compte : " . $e->getMessage();
        }
    }
}
?>