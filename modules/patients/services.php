<?php

class PatientService
{
    private $pdo;

    public function __construct()
    {
        $db = new Database();
        $this->pdo = $db->openConnection();
    }

    public function handleUpload($file, $oldPath = '')
    {
        if (isset($file['foto']) && $file['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($file['foto']['name'], PATHINFO_EXTENSION);
            $targetDir = dirname(__DIR__, 2) . '/uploads/patients/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            $filename = uniqid('foto_') . '.' . $ext;
            $targetFile = $targetDir . $filename;
            if (move_uploaded_file($file['foto']['tmp_name'], $targetFile)) {
                return 'uploads/patients/' . $filename;
            }
        }
        return $oldPath;
    }

    public function create($data, $files)
    {
        $fotoPath = $this->handleUpload($files);
        $stmt = $this->pdo->prepare("INSERT INTO patients 
            (no_rm, no_nik, no_bpjs, nama_lengkap, nama_panggilan, alamat, telepon, foto, nama_ayah, nama_ibu, nama_penanggung_jawab, kontak_penanggung_jawab) 
            VALUES (:no_rm, :no_nik, :no_bpjs, :nama_lengkap, :nama_panggilan, :alamat, :telepon, :foto, :nama_ayah, :nama_ibu, :nama_penanggung_jawab, :kontak_penanggung_jawab)");
        $stmt->execute([
            ':no_rm' => $data['no_rm'],
            ':no_nik' => $data['no_nik'] ?? '',
            ':no_bpjs' => $data['no_bpjs'] ?? '',
            ':nama_lengkap' => $data['nama_lengkap'],
            ':nama_panggilan' => $data['nama_panggilan'],
            ':alamat' => $data['alamat'],
            ':telepon' => $data['telepon'],
            ':foto' => $fotoPath,
            ':nama_ayah' => $data['nama_ayah'],
            ':nama_ibu' => $data['nama_ibu'],
            ':nama_penanggung_jawab' => $data['nama_penanggung_jawab'],
            ':kontak_penanggung_jawab' => $data['kontak_penanggung_jawab'],
        ]);
        return true;
    }

    public function update($data, $files)
    {
        // Get old foto if not uploading new
        $fotoPath = $data['foto'] ?? '';
        if (empty($fotoPath) && !empty($data['id'])) {
            $stmt = $this->pdo->prepare("SELECT foto FROM patients WHERE id = :id");
            $stmt->execute([':id' => $data['id']]);
            $fotoPath = $stmt->fetchColumn();
        }
        $fotoPath = $this->handleUpload($files, $fotoPath);

        $stmt = $this->pdo->prepare("UPDATE patients SET 
            no_rm = :no_rm,
            no_nik = :no_nik,
            no_bpjs = :no_bpjs,
            nama_lengkap = :nama_lengkap,
            nama_panggilan = :nama_panggilan,
            alamat = :alamat,
            telepon = :telepon,
            foto = :foto,
            nama_ayah = :nama_ayah,
            nama_ibu = :nama_ibu,
            nama_penanggung_jawab = :nama_penanggung_jawab,
            kontak_penanggung_jawab = :kontak_penanggung_jawab
            WHERE id = :id");
        $stmt->execute([
            ':no_rm' => $data['no_rm'],
            ':no_nik' => $data['no_nik'] ?? '',
            ':no_bpjs' => $data['no_bpjs'] ?? '',
            ':nama_lengkap' => $data['nama_lengkap'],
            ':nama_panggilan' => $data['nama_panggilan'],
            ':alamat' => $data['alamat'],
            ':telepon' => $data['telepon'],
            ':foto' => $fotoPath,
            ':nama_ayah' => $data['nama_ayah'],
            ':nama_ibu' => $data['nama_ibu'],
            ':nama_penanggung_jawab' => $data['nama_penanggung_jawab'],
            ':kontak_penanggung_jawab' => $data['kontak_penanggung_jawab'],
            ':id' => $data['id'],
        ]);
        return true;
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM patients WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return true;
    }

    public function getDataTable($params)
    {
        $start = intval($params['start'] ?? 0);
        $length = intval($params['length'] ?? 10);
        $search = $params['search']['value'] ?? '';

        $totalQuery = $this->pdo->query("SELECT COUNT(*) FROM patients");
        $recordsTotal = $totalQuery->fetchColumn();

        $where = '';
        $bindParams = [];
        if ($search) {
            $where = "WHERE nama_lengkap LIKE :search OR nama_panggilan LIKE :search OR alamat LIKE :search";
            $bindParams[':search'] = "%$search%";
        }

        $stmt = $this->pdo->prepare("SELECT * FROM patients $where LIMIT :start, :length");
        foreach ($bindParams as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':start', $start, \PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, \PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Query filtered count
        if ($where) {
            $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM patients $where");
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

    public function cekBPJSByNoNik($no_nik)
    {
        require_once dirname(__DIR__, 2) . '/observers/BpjsObserver.php';
        $bpjs = new \BpjsObserver();

        $cons_id = $_ENV['BPJS_CONS_ID'] ?? '';
        $secret_key = $_ENV['BPJS_SECRET_KEY'] ?? '';
        $timestamp = $bpjs->getTimestamp();
        $tglSep = date('Y-m-d');

        try {
            $result = $bpjs->cariPesertaByNoNik($no_nik, $tglSep);

            if (empty($result) || !isset($result['response'])) {
                throw new \Exception('Data tidak ditemukan');
            }

            $key = $cons_id . $secret_key . $timestamp;
            $response = $result['response'];

            $decompress = $bpjs->decompress($bpjs->stringDecrypt($key, $response));
            $data = json_decode($decompress);

            if (empty($data) || !isset($data->peserta)) {
                throw new \Exception('Data peserta tidak ditemukan');
            }

            return $data;
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public function cekBPJSByNoBpjs($no_bpjs) {}
}
