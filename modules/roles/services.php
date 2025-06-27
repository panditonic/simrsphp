<?php

include_once __DIR__ . '/../../db.php';

$db = new Database();
$pdo = $db->openConnection();

$action = $_REQUEST['action'] ?? '';

// Load all permissions from permissions table only
if ($action === 'permissions') {
    $stmt = $pdo->query("SELECT id, name FROM permissions ORDER BY name ASC");
    $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($permissions);
    exit;
}

if ($action === 'get') {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $role = $stmt->fetch(PDO::FETCH_ASSOC);
    // Decode permissions JSON to array
    $role['permissions'] = $role['permissions'] ? json_decode($role['permissions'], true) : [];
    echo json_encode($role);
    exit;
}

if ($action === 'create') {
    $name = $_POST['name'] ?? '';
    $desc = $_POST['description'] ?? '';
    $permissions = $_POST['permissions'] ?? [];
    $permissionsJson = json_encode($permissions);
    $stmt = $pdo->prepare("INSERT INTO roles (name, description, permissions) VALUES (:name, :desc, :permissions)");
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':desc', $desc, PDO::PARAM_STR);
    $stmt->bindValue(':permissions', $permissionsJson, PDO::PARAM_STR);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'update') {
    $id = intval($_POST['id'] ?? 0);
    $name = $_POST['name'] ?? '';
    $desc = $_POST['description'] ?? '';
    $permissions = $_POST['permissions'] ?? [];
    $permissionsJson = json_encode($permissions);
    $stmt = $pdo->prepare("UPDATE roles SET name = :name, description = :desc, permissions = :permissions WHERE id = :id");
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':desc', $desc, PDO::PARAM_STR);
    $stmt->bindValue(':permissions', $permissionsJson, PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM roles WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
}

// If no valid action
http_response_code(400);
echo json_encode(['error' => 'Invalid action']);