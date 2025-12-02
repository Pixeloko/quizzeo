<?php 
    require_once("./Model/function_user.php");
    require_once("./Model/functions_quizz.php");

session_start();
    try {
        $users = getUsers();
        $Quizz = getQuizz();
    } catch (PDOException $e) {
        $errors["general"] = "Impossible de récupérer les tâches : " . $e->getMessage();
    }

// Mise à jour du statut du user

if (!isset($_POST['user_id'], $_POST['action'])) {
    $_SESSION['error'] = "Requête invalide.";
    header("Location: admin.php");
    exit();
}

$userId = (int)$_POST['user_id'];
$action = $_POST['action'];

if ($action === "activate") {
    setActiveUser($userId);
} elseif ($action === "deactivate") {
    setInactiveUser($userId);
} else {
    $_SESSION["error"] = "Action inconnue";
}

header("Location: admin.php");
exit();

?>