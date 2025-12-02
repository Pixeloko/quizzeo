<?php
session_start();

$url = $_GET['url'] ?? '';

switch ($url) {

    case '':
        require __DIR__ . '/View/home.php';
        break;

    case 'login':
        require __DIR__ . '/View/login.php';
        break;

    case 'create':
        require __DIR__ . '/View/create_account.php';
        break;

    case 'dashboard':
        require __DIR__ . '/View/dashboard.php';
        break;

    case 'user':
        require __DIR__ . '/View/user.php';
        break;

    case 'logout':
        require __DIR__ . '/Controller/logout.php';
        break;

    case 'admin':
        require __DIR__ .'/View/admin.php';
        break;


    default:
        http_response_code(404);
        echo "Page non trouvée";
}