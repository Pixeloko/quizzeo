<?php
// View/user/available_quizzes.php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "user") {
    header("Location: /quizzeo/?url=login");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Disponibles - Quizzeo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
    .quiz-card { transition: transform 0.3s; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .quiz-card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0,0,0,0.15); }
    .difficulty-easy { border-left: 4px solid #28a745; }
    .difficulty-medium { border-left: 4px solid #ffc107; }
    .difficulty-hard { border-left: 4px solid #dc3545; }
    .filter-badge { cursor: pointer; }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Navigation -->
        <nav class="navbar navbar-light bg-white rounded-3 shadow-sm mb-4">
            <