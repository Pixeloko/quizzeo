<?php

require_once("./Model/function_question.php");

$errors = [];
$title = "";
$answer = "";
$point = 0;

if ($_SERVER["REQUEST_METHOD"]  === "POST") {
    $title = trim($_POST["title"]); 
    $answer = trim($_POST["answer"]);
    $point = isset($_POST["point"]);
 
  if (empty($title)) {
    $errors["title"] = "La question est requise";
  }
  if (empty($answer)) {
    $errors["answer"] = "La réponse est requise";
  }
  if (empty($point)) {
    $errors["point"] = "Entrer le nombre de point";
  }
 
    if(empty($errors)) {
        createQuest($title, $answer, $point);
    }

    if(empty($errors)) {
        $_SESSION["message"] = "Envoi réussi !";

        header('Location : ./View/dashboard_e.php');
        exit;    
    }
}

?>