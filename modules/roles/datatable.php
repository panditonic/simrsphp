<?php

include_once __DIR__ . '/../../db.php';

$db = new Database();
$pdo = $db->openConnection();

// DataTables parameters
$draw = intval($_GET['draw'] ?? 1);
$start = intval($_GET['start'] ?? 0);
$length = intval($_GET['length'] ?? 10);
$searchValue = $_GET['search']['value'] ?? '';
$orderColumnIdx = intval($_GET['order'][0]['column'] ?? 0);
$orderDir = $_GET['order'][0]['dir'] ?? 'asc';

// Columns mapping
$columns = ['id', 'name', 'description'];
$orderColumn = $columns[$orderColumnIdx] ?? 'id';

// Total records
$totalRecordsStmt = $pdo->query("SELECT COUNT(*) as cnt FROM roles");
$totalRecords = $totalRecordsStmt->fetch(PDO::FETCH_ASSOC)['cnt'];

// Search filter
$where = '';
$params = [];
if ($searchValue !== '') {
    $where = "WHERE name LIKE :search OR description LIKE :search";
    $params[':search'] = "%$searchValue%";
}

// Filtered records
if ($where) {
    $filteredStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM roles $where");
    $filteredStmt->execute($params);
    $filteredRecords = $filteredStmt->fetch(PDO::FETCH_ASSOC)['cnt'];
} else {
    $filteredRecords = $totalRecords;
}

// Fetch data
$sql = "SELECT * FROM roles";
if ($where) $sql .= " $where";
$sql .= " ORDER BY $orderColumn $orderDir LIMIT :start, :length";
$stmt = $pdo->prepare($sql);

// Bind search param if needed
if ($where) {
    $stmt->bindValue(':search', "%$searchValue%", PDO::PARAM_STR);
}
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':length', $length, PDO::PARAM_INT);

$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output
echo json_encode([
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $filteredRecords,
    "data" => $data
]);