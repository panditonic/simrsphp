<?php

// echo $_SESSION['user']['role_name'];exit;

// Ambil role user saat ini
$userRoleId = $_SESSION['user']['role'];

// Ambil permissions dari tabel roles
$stmt = $pdo->prepare("SELECT permissions FROM roles WHERE id = :role_id");
$stmt->execute([':role_id' => $userRoleId]);
$role = $stmt->fetch(PDO::FETCH_ASSOC);

$permissions = [];
if ($role && !empty($role['permissions'])) {
    // Asumsikan permissions disimpan dalam format JSON di DB
    $permissions = json_decode($role['permissions'], true);
}
// echo json_encode($permissions);exit;

// Cek apakah user punya izin untuk mengakses modul berdasarkan path yang dikunjungi
$visitedModule = isset($_GET['module']) ? $_GET['module'] : 'dasbor';
// die(json_encode($permissions));
if (
    !$permissions ||
    !array_filter($permissions, function($perm) use ($visitedModule) {
        // Izinkan wildcard atau regex pada permission, misal: users, users.*, modul.*
        return preg_match('#^' . str_replace(['*', '.'], ['.*', '\.'], $perm) . '$#i', $visitedModule);
    })
) {
    http_response_code(403);
    echo "Forbidden: Anda tidak memiliki izin untuk mengakses modul ini.";
    exit;
}

?>