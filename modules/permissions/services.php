<?php

include_once __DIR__ . '/../../db.php';

$db = new Database();
$pdo = $db->openConnection();

$action = $_REQUEST['action'] ?? '';

if ($action === 'get') {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM permissions WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $role = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($role);
    exit;
}

if ($action === 'create') {
    $name = $_POST['name'] ?? '';
    $desc = $_POST['description'] ?? '';
    $stmt = $pdo->prepare("INSERT INTO permissions (name, description) VALUES (:name, :desc)");
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':desc', $desc, PDO::PARAM_STR);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'update') {
    $id = intval($_POST['id'] ?? 0);
    $name = $_POST['name'] ?? '';
    $desc = $_POST['description'] ?? '';
    $stmt = $pdo->prepare("UPDATE permissions SET name = :name, description = :desc WHERE id = :id");
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':desc', $desc, PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM permissions WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
}

// If no valid action
http_response_code(400);
echo json_encode(['error' => 'Invalid action']);