<?php
require_once("./Model/function_user.php");


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Vérification du token généré dans la view
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token validation failed");
    }

    $email = trim($_POST["email"] ?? '');
    $firstname = trim($_POST["firstname"] ?? '');
    $lastname = trim($_POST["lastname"] ?? '');
    $password = trim($_POST["password"] ?? '');
    
    // Validation des inputs
    if (empty($email)) {
        $errors["email"] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Veuillez entrer un email valide";
    }

    if (empty($firstname || empty($lastname))) {
        $errors["firstname"] = "Entrer un prénom";
        $errors["lastname"] = "Entrer un nom";
    }

    if (empty($password)) {
        $errors["password"] = "Le mot de passe est requis";
    } elseif (strlen($password) < 8) {
        $errors["password"] = "Le mot de passe doit contenir au moins 8 caractères";
    }

    // Si pas d'erreur
    if(empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $user = createUser($role, $firstname, $lastname, $email, $password_hash);
            
            $_SESSION["message"] = "Connexion réussie !";
            $_SESSION["user_id"] = $user["id"];
            
            // Redirection en fonction du rôle
            switch ($role) {
                case "entreprise":
                  case "ecole":
                      header("Location: dashboard");
                      break;
                case "user":
                default:
                    header("Location: user");
                    break;
            }
            exit;
            
        } catch (Exception $e) {
            $errors[] = "Une erreur est survenue lors de la création du compte: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>
