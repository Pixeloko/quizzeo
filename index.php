<?php

$url = $_GET['url'] ?? '';

switch ($url) {
    case '':
        require __DIR__ . '/View/login.php';
        break;
        
    case 'create':

        require __DIR__ . '/View/create_account.php';
        break;
    
    case 'login':

        require __DIR__ . '/View/login.php';
        break;

    case 'user':

        require __DIR__ . '/View/user.php';
        break;

    case 'dashboard':

        require __DIR__ . '/View/dashboard_e.php';
        break;


    default:
        http_response_code(404);
        echo 'Page non trouvée';
        break;
}