<?php
require_once(__DIR__ . "/../Model/function_user.php");
require_once(__DIR__ . "/../Model/function_quizz.php");


/**
 * Traite le POST pour utilisateurs et quizzes
 */
function handleAdminPost(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("CSRF token invalide !");
        }

        // Utilisateurs
        if (!empty($_POST['user_id']) && !empty($_POST['action'])) {
            $userId = (int)$_POST['user_id'];
            if ($_POST['action'] === 'activate') activateUser($userId);
            elseif ($_POST['action'] === 'deactivate') deactivateUser($userId);
            $_SESSION['message'] = "Utilisateur mis à jour !";
        }

        // Quizzes
        if (!empty($_POST['quiz_id']) && !empty($_POST['action'])) {
            $quizId = (int)$_POST['quiz_id'];
            if ($_POST['action'] === 'activate') activateQuiz($quizId);
            elseif ($_POST['action'] === 'deactivate') deactivateQuiz($quizId);
            $_SESSION['message'] = "Quiz mis à jour !";
        }

        // Redirection unique
        header("Location: /quizzeo/View/admin.php");
        exit;
    }
}
