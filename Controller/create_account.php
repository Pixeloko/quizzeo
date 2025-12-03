<?php
require_once __DIR__ . "/../Model/function_user.php";


$errors = [];
$firstname = "";
$lastname = "";
$email = "";
$password = "";
$role = "user";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $captcha = $_POST['g-recaptcha-response'] ?? '';

if (!$captcha) {
    $errors["general"] = "Veuillez valider le captcha.";
} else {
    $secretKey = "6Lcv2h8sAAAAAOQbiiU37VVzckA-4XD6S5gs35ez";
    $response = file_get_contents(
        "https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$captcha}"
    );
    $responseKeys = json_decode($response, true);

    if (!$responseKeys["success"]) {
        $errors["general"] = "Captcha invalide. Veuillez réessayer.";
    }
}


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
            header("Location: ./login.php");
            exit;
        } catch (Exception $e) {
            $errors["general"] = "❌ Impossible de créer un compte : " . $e->getMessage();
        }
    }
}
?>