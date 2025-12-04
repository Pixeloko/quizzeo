<?php
function index() {
    require_once __DIR__ . '/../Model/function_user.php';
    
    // Récupérer tous les quiz actifs
    $quizzes = getAllActiveQuizzes();
    
    // Charger la vue de la home
    require_once __DIR__ . '/../View/home.php';
}