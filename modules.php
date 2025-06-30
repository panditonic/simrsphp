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
    case 'role':
        require_once __DIR__ . '/modules/roles/index.php';
        break;
    case 'permission':
        require_once __DIR__ . '/modules/permissions/index.php';
        break;
    case 'doctor':
        require_once __DIR__ . '/modules/doctors/index.php';
        break;
    case 'kunjungan':
        require_once __DIR__ . '/modules/kunjungans/index.php';
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
