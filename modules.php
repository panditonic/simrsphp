<?php

// Simple PHP modules router

$module = isset($_GET['module']) ? $_GET['module'] : 'dasbor';

ob_start();
switch ($module) {
    case 'dasbor':
        require_once 'home.php';
        break;
    case 'user':
        require_once __DIR__ . '/modules/users/index.php';
        break;
    case 'patient':
        require_once __DIR__ . '/modules/patients/index.php';
        break;
    case 'logout':
        require_once __DIR__ . '/logout.php';
        break;
    default:
        http_response_code(404);
        echo "Module not found.";
        break;
}
$module_content = ob_get_clean();

include 'dasbor.php';