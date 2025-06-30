<?php

class KunjunganService {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->openConnection();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO kunjungans 
            (dokter_id, pasien_id, tanggal_waktu, diagnosa, keluhan, tindakan, catatan) 
            VALUES (:dokter_id, :pasien_id, :tanggal_waktu, :diagnosa, :keluhan, :tindakan, :catatan)");
        $stmt->execute([
            ':dokter_id' => $data['dokter_id'],
            ':pasien_id' => $data['pasien_id'],
            ':tanggal_waktu' => $data['tanggal_waktu'],
            ':diagnosa' => $data['diagnosa'],
            ':keluhan' => $data['keluhan'] ?? null,
            ':tindakan' => $data['tindakan'] ?? null,
            ':catatan' => $data['catatan'] ?? null
        ]);
        return true;
    }

    public function update($data) {
        $stmt = $this->pdo->prepare("UPDATE kunjungans SET 
            dokter_id = :dokter_id,
            pasien_id = :pasien_id,
            tanggal_waktu = :tanggal_waktu,
            diagnosa = :diagnosa,
            keluhan = :keluhan,
            tindakan = :tindakan,
            catatan = :catatan
            WHERE id = :id");
        $stmt->execute([
            ':dokter_id' => $data['dokter_id'],
            ':pasien_id' => $data['pasien_id'],
            ':tanggal_waktu' => $data['tanggal_waktu'],
            ':diagnosa' => $data['diagnosa'],
            ':keluhan' => $data['keluhan'] ?? null,
            ':tindakan' => $data['tindakan'] ?? null,
            ':catatan' => $data['catatan'] ?? null,
            ':id' => $data['id']
        ]);
        return true;
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM kunjungans WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return true;
    }

    public function getDataTable($params) {
        $start = intval($params['start'] ?? 0);
        $length = intval($params['length'] ?? 10);
        $search = $params['search']['value'] ?? '';

        $where = '';
        $bindParams = [];
        if ($search) {
            $where = "WHERE k.diagnosa LIKE :search OR k.keluhan LIKE :search OR d.nama LIKE :search OR p.nama_lengkap LIKE :search";
            $bindParams[':search'] = "%$search%";
        }

        // Hitung total
        $totalQuery = $this->pdo->query("SELECT COUNT(*) FROM kunjungans");
        $recordsTotal = $totalQuery->fetchColumn();

        // Query data dengan join
        $sql = "SELECT 
                    k.*, 
                    d.nama AS dokter_nama, 
                    p.nama_lengkap AS pasien_nama 
                FROM kunjungans k
                LEFT JOIN doctors d ON k.dokter_id = d.id
                LEFT JOIN patients p ON k.pasien_id = p.id
                $where
                ORDER BY k.id DESC
                LIMIT :start, :length";
        $stmt = $this->pdo->prepare($sql);
        foreach ($bindParams as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':start', $start, \PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, \PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Hitung filtered
        if ($where) {
            $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM kunjungans k
                LEFT JOIN doctors d ON k.dokter_id = d.id
                LEFT JOIN patients p ON k.pasien_id = p.id
                $where");
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