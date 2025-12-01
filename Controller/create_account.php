<?php
require_once("./includes/header.php");
require_once("./config/config.php");
require_once("includes/functions.php");

$errors = [];
$email = "";
$username = "";
$password = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST["email"]);
  $username = trim($_POST["username"]);
  $password = trim($_POST["password"]);
  
  if (empty($email)) {
    $errors["email"] = "L'email est requis";
  }

  if (empty($username)) {
    $errors["username"] = "Vous n'avez pas de nom de héro ?";
  }
  
  if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors["email"] = "Veuilez entrer un email valide";
  }

  if (empty($password)) {
    $errors["password"] = "Le mot de passe est requis";
  }

// hasher le password + l'enregistrer dans la bdd avec ma foncction createUser()
  if(empty($errors)) {
    $password = password_hash($password, PASSWORD_DEFAULT);
    $user = createUser($username, $email, $password);
    $_SESSION["message"] = "Connexion réussie !";
    $_SESSION["user_id"] = $user["id"];
    header("Location: dashboard.php");
    exit;
  }
}

?>
