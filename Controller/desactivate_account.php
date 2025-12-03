<?php
require_once "../Model/function_user.php";
 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
if ($_SESSION["role"] !== "admin") {
    header("Location: logout.php");
    exit;
}
 
$userId = $_SESSION["user_id"];
 
try {
    $desactivate =  setInactiveUser($userId);
    $_SESSION["message"] = "✅ Utilisateur activé";
    header("Location: ../View/admin.php");
    exit;
} catch (Exception $e) {
    $_SESSION["message"] = "❌ Erreur : " . $e->getMessage();
    header("Location: dashboard.php");
    exit;
}