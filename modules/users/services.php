<?php

class UserService
{
    private $pdo;

    public function __construct()
    {
        $db = new Database();
        $this->pdo = $db->openConnection();
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':role' => $data['role']
        ]);
        return true;
    }

    public function update($data)
    {
        if (!empty($data['password'])) {
            $stmt = $this->pdo->prepare("UPDATE users SET name = :name, email = :email, password = :password, role = :role WHERE id = :id");
            $stmt->execute([
                ':name' => $data['name'],
                ':email' => $data['email'],
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':role' => $data['role'],
                ':id' => $data['id']
            ]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE users SET name = :name, email = :email, role = :role WHERE id = :id");
            $stmt->execute([
                ':name' => $data['name'],
                ':email' => $data['email'],
                ':role' => $data['role'],
                ':id' => $data['id']
            ]);
        }
        return true;
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return true;
    }

    public function getDataTable($params)
    {
        $start = intval($params['start'] ?? 0);
        $length = intval($params['length'] ?? 10);
        $search = $params['search']['value'] ?? '';

        $totalQuery = $this->pdo->query("SELECT COUNT(*) FROM users");
        $recordsTotal = $totalQuery->fetchColumn();

        $where = '';
        $bindParams = [];
        if ($search) {
            $where = "WHERE users.name LIKE :search OR users.email LIKE :search";
            $bindParams[':search'] = "%$search%";
        }

        $stmt = $this->pdo->prepare("SELECT users.id, users.name, users.email, users.role, roles.name AS role_name FROM users LEFT JOIN roles ON users.role = roles.id $where LIMIT :start, :length");
        foreach ($bindParams as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Query filtered count
        if ($where) {
            $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM users $where");
            foreach ($bindParams as $k => $v) $countStmt->bindValue($k, $v);
            $countStmt->execute();
            $recordsFiltered = $countStmt->fetchColumn();
        } else {
            $recordsFiltered = $recordsTotal;
        }

        return [
            "draw" => intval($params['draw'] ?? 1),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ];
    }

    public function getRoles()
    {
        return $this->pdo->query("SELECT id, name FROM roles ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
}
