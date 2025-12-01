<?php
require_once("./config/config.php");
require_once("./Model/function_users.php");

$errors = [];
$email = "";
$username = "";
$password = "";
$role = "user";


if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST["email"]);
  $username = trim($_POST["username"]);
  $password = trim($_POST["password"]);
  $password = $_POST["user"];
  
  if (empty($email)) {
    $errors["email"] = "L'email est requis";
  }

  if (empty($username)) {
    $errors["username"] = "Entrer un nom";
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
    if ($role === "user"){
      header("Location: ./View/user.php");
      exit;
    }
    if ($role === "entreprise"){
      header("Location: ./View/entreprise.php");
      exit;
    }
    if ($role === "ecole"){
      header("Location: ./View/ecole.php");
      exit;
    }
  }
}

?>
