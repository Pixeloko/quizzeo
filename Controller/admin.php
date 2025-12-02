<?php 
    require_once("./Model/function_user.php");
    require_once("./Model/function_quizz.php");

    try {
        $users = getUsers();
        $Quizz = getQuizz()
    } catch (PDOException $e) {
        $errors["general"] = "Impossible de récupérer les tâches : " . $e->getMessage();
    }
    
?>