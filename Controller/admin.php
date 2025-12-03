<?php
require_once(__DIR__ . "/../Model/function_user.php");
require_once(__DIR__ . "/../Model/functions_quizz.php");


/**
 * Récupère tous les utilisateurs (hors admins)
 * @return array Toujours un tableau, même vide
 */
function fetchUsers(): array {
    $users = getUsers(); // getUsers() dans userModel.php
    return $users ?: [];
}

/**
 * Récupère tous les quizzes
 * @return array Toujours un tableau, même vide
 */
function fetchQuizzes(): array {
    $quizzes = getQuizz(); // getQuizz() dans quizModel.php
    return $quizzes ?: [];
}

/**
 * Active un utilisateur
 */
function activateUser(int $userId) {
    activateUserModel($userId); // fonction à définir dans userModel.php
}

/**
 * Désactive un utilisateur
 */
function deactivateUser(int $userId) {
    deactivateUserModel($userId); // fonction à définir dans userModel.php
}

/**
 * Active un quiz
 */
function activateQuiz(int $quizId) {
    activateQuizModel($quizId); // fonction à définir dans quizModel.php
}

/**
 * Désactive un quiz
 */
function deactivateQuiz(int $quizId) {
    deactivateQuizModel($quizId); // fonction à définir dans quizModel.php
}

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
