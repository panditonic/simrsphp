<?php

class DoctorService {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->openConnection();
    }

    private function handleUpload($file, $oldPath = '') {
        if (isset($file['foto']) && $file['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($file['foto']['name'], PATHINFO_EXTENSION);
            $targetDir = dirname(__DIR__, 2) . '/uploads/doctors/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            $filename = uniqid('dokter_') . '.' . $ext;
            $targetFile = $targetDir . $filename;
            if (move_uploaded_file($file['foto']['tmp_name'], $targetFile)) {
                return 'uploads/doctors/' . $filename;
            }
        }
        return $oldPath;
    }

    public function create($data, $files) {
        $fotoPath = $this->handleUpload($files);
        $stmt = $this->pdo->prepare("INSERT INTO doctors 
            (nama, spesialisasi, nomor_str, jenis_kelamin, tanggal_lahir, alamat, telepon, email, foto) 
            VALUES (:nama, :spesialisasi, :nomor_str, :jenis_kelamin, :tanggal_lahir, :alamat, :telepon, :email, :foto)");
        $stmt->execute([
            ':nama' => $data['nama'],
            ':spesialisasi' => $data['spesialisasi'],
            ':nomor_str' => $data['nomor_str'],
            ':jenis_kelamin' => $data['jenis_kelamin'],
            ':tanggal_lahir' => $data['tanggal_lahir'],
            ':alamat' => $data['alamat'],
            ':telepon' => $data['telepon'],
            ':email' => $data['email'],
            ':foto' => $fotoPath
        ]);
        return true;
    }

    public function update($data, $files) {
        $fotoPath = $data['foto'] ?? '';
        if (empty($fotoPath) && !empty($data['id'])) {
            $stmt = $this->pdo->prepare("SELECT foto FROM doctors WHERE id = :id");
            $stmt->execute([':id' => $data['id']]);
            $fotoPath = $stmt->fetchColumn();
        }
        $fotoPath = $this->handleUpload($files, $fotoPath);

        $stmt = $this->pdo->prepare("UPDATE doctors SET 
            nama = :nama,
            spesialisasi = :spesialisasi,
            nomor_str = :nomor_str,
            jenis_kelamin = :jenis_kelamin,
            tanggal_lahir = :tanggal_lahir,
            alamat = :alamat,
            telepon = :telepon,
            email = :email,
            foto = :foto
            WHERE id = :id");
        $stmt->execute([
            ':nama' => $data['nama'],
            ':spesialisasi' => $data['spesialisasi'],
            ':nomor_str' => $data['nomor_str'],
            ':jenis_kelamin' => $data['jenis_kelamin'],
            ':tanggal_lahir' => $data['tanggal_lahir'],
            ':alamat' => $data['alamat'],
            ':telepon' => $data['telepon'],
            ':email' => $data['email'],
            ':foto' => $fotoPath,
            ':id' => $data['id']
        ]);
        return true;
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM doctors WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return true;
    }

    public function getDataTable($params) {
        $start = intval($params['start'] ?? 0);
        $length = intval($params['length'] ?? 10);
        $search = $params['search']['value'] ?? '';

        $totalQuery = $this->pdo->query("SELECT COUNT(*) FROM doctors");
        $recordsTotal = $totalQuery->fetchColumn();

        $where = '';
        $bindParams = [];
        if ($search) {
            $where = "WHERE nama LIKE :search OR spesialisasi LIKE :search OR nomor_str LIKE :search OR telepon LIKE :search OR email LIKE :search";
            $bindParams[':search'] = "%$search%";
        }

        $stmt = $this->pdo->prepare("SELECT * FROM doctors $where ORDER BY id DESC LIMIT :start, :length");
        foreach ($bindParams as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':start', $start, \PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, \PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Query filtered count
        if ($where) {
            $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM doctors $where");
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
}