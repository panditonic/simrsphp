<?php

include_once __DIR__ . '/../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $pdo = $db->openConnection();

    $action = $_GET['action'] ?? '';
    $action = $_GET['action'] ?? '';

    // Handle file upload for create & update
    $fotoPath = $_POST['foto'] ?? '';
    if (($action === 'create' || $action === 'update') && isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        // Change upload directory to rootdir/uploads/patients/
        $targetDir = dirname(__DIR__, 2) . '/uploads/patients/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $filename = uniqid('foto_') . '.' . $ext;
        $targetFile = $targetDir . $filename;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
            // Save relative path from web root
            $fotoPath = 'uploads/patients/' . $filename;
        }
    }

    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO patients 
            (no_rm, nama_lengkap, nama_panggilan, alamat, telepon, foto, nama_ayah, nama_ibu, nama_penanggung_jawab, kontak_penanggung_jawab) 
            VALUES (:no_rm, :nama_lengkap, :nama_panggilan, :alamat, :telepon, :foto, :nama_ayah, :nama_ibu, :nama_penanggung_jawab, :kontak_penanggung_jawab)");
        $stmt->execute([
            ':no_rm' => $_POST['no_rm'],
            ':nama_lengkap' => $_POST['nama_lengkap'],
            ':nama_panggilan' => $_POST['nama_panggilan'],
            ':alamat' => $_POST['alamat'],
            ':telepon' => $_POST['telepon'],
            ':foto' => $fotoPath,
            ':nama_ayah' => $_POST['nama_ayah'],
            ':nama_ibu' => $_POST['nama_ibu'],
            ':nama_penanggung_jawab' => $_POST['nama_penanggung_jawab'],
            ':kontak_penanggung_jawab' => $_POST['kontak_penanggung_jawab'],
        ]);
        echo json_encode(['success' => true]);
        exit;
    } elseif ($action === 'update') {
        // If no new file uploaded, keep old foto
        if (!$fotoPath && isset($_POST['id'])) {
            $stmt = $pdo->prepare("SELECT foto FROM patients WHERE id = :id");
            $stmt->execute([':id' => $_POST['id']]);
            $fotoPath = $stmt->fetchColumn();
        }
        $stmt = $pdo->prepare("UPDATE patients SET 
            no_rm = :no_rm,
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
            ':no_rm' => $_POST['no_rm'],
            ':nama_lengkap' => $_POST['nama_lengkap'],
            ':nama_panggilan' => $_POST['nama_panggilan'],
            ':alamat' => $_POST['alamat'],
            ':telepon' => $_POST['telepon'],
            ':foto' => $fotoPath,
            ':nama_ayah' => $_POST['nama_ayah'],
            ':nama_ibu' => $_POST['nama_ibu'],
            ':nama_penanggung_jawab' => $_POST['nama_penanggung_jawab'],
            ':kontak_penanggung_jawab' => $_POST['kontak_penanggung_jawab'],
            ':id' => $_POST['id'],
        ]);
        echo json_encode(['success' => true]);
        exit;
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM patients WHERE id = :id");
        $stmt->execute([':id' => $_POST['id']]);
        echo json_encode(['success' => true]);
        exit;
    }

    // DataTables logic
    $start = intval($_POST['start'] ?? 0);
    $length = intval($_POST['length'] ?? 10);
    $search = $_POST['search']['value'] ?? '';

    $totalQuery = $pdo->query("SELECT COUNT(*) FROM patients");
    $recordsTotal = $totalQuery->fetchColumn();

    $where = '';
    $params = [];
    if ($search) {
        $where = "WHERE nama_lengkap LIKE :search OR nama_panggilan LIKE :search OR alamat LIKE :search";
        $params[':search'] = "%$search%";
    }

    $stmt = $pdo->prepare("SELECT * FROM patients $where LIMIT :start, :length");
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Query filtered count
    if ($where) {
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM patients $where");
        foreach ($params as $k => $v) $countStmt->bindValue($k, $v);
        $countStmt->execute();
        $recordsFiltered = $countStmt->fetchColumn();
    } else {
        $recordsFiltered = $recordsTotal;
    }

    echo json_encode([
        "draw" => intval($_POST['draw'] ?? 1),
        "recordsTotal" => $recordsTotal,
        "recordsFiltered" => $recordsFiltered,
        "data" => $data
    ]);
    die();
}
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<button onclick="showForm()" class="mb-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Tambah Pasien</button>
<table id="pasienTable" class="display" style="width:100%">
    <thead>
        <tr>
            <th>No RM</th>
            <th>Nama Lengkap</th>
            <th>Nama Panggilan</th>
            <th>Alamat</th>
            <th>Telepon</th>
            <th>Foto</th>
            <th>Nama Ayah</th>
            <th>Nama Ibu</th>
            <th>Nama Penanggung Jawab</th>
            <th>Kontak Penanggung Jawab</th>
            <th>Aksi</th>
        </tr>
    </thead>
</table>

<!-- Modal Backdrop and Modal -->
<div id="modalBackdrop" class="fixed inset-0 bg-black bg-opacity-40 z-40 hidden"></div>
<div id="formModal" class="fixed inset-0 flex items-start justify-start z-50 hidden">
    <div class="bg-white h-full w-full md:w-1/2 max-w-lg shadow-lg p-6 relative overflow-y-auto">
        <form id="pasienForm" class="space-y-3" enctype="multipart/form-data">
            <input type="hidden" name="id" id="id">
            <input type="text" name="no_rm" placeholder="No RM" required class="w-full border rounded px-3 py-2" />
            <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" required class="w-full border rounded px-3 py-2" />
            <input type="text" name="nama_panggilan" placeholder="Nama Panggilan" class="w-full border rounded px-3 py-2" />
            <input type="text" name="alamat" placeholder="Alamat" class="w-full border rounded px-3 py-2" />
            <input type="text" name="telepon" placeholder="Telepon" class="w-full border rounded px-3 py-2" />
            <!-- File input for foto -->
            <input type="file" name="foto" id="foto" accept="image/*" class="w-full border rounded px-3 py-2" />
            <input type="text" name="nama_ayah" placeholder="Nama Ayah" class="w-full border rounded px-3 py-2" />
            <input type="text" name="nama_ibu" placeholder="Nama Ibu" class="w-full border rounded px-3 py-2" />
            <input type="text" name="nama_penanggung_jawab" placeholder="Nama Penanggung Jawab" class="w-full border rounded px-3 py-2" />
            <input type="text" name="kontak_penanggung_jawab" placeholder="Kontak Penanggung Jawab" class="w-full border rounded px-3 py-2" />
            <div class="flex justify-end gap-2 pt-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan</button>
                <button type="button" onclick="hideForm()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function showForm(data = {}) {
    $('#pasienForm')[0].reset();
    $('#pasienForm input[type=hidden], #pasienForm input[type=text]').val('');
    $('#foto').val('');
    if (data && Object.keys(data).length) {
        for (let k in data) {
            if (k !== 'foto') $('#pasienForm [name="' + k + '"]').val(data[k]);
        }
    }
    $('#modalBackdrop').removeClass('hidden');
    $('#formModal').removeClass('hidden');
}

function hideForm() {
    $('#modalBackdrop').addClass('hidden');
    $('#formModal').addClass('hidden');
}

let table = $('#pasienTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: window.location.pathname,
        type: 'POST'
    },
    columns: [
        { data: 'no_rm' },
        { data: 'nama_lengkap' },
        { data: 'nama_panggilan' },
        { data: 'alamat' },
        { data: 'telepon' },
        {
            data: 'foto',
            render: d => d ? `<img src="${d}" width="40">` : ''
        },
        { data: 'nama_ayah' },
        { data: 'nama_ibu' },
        { data: 'nama_penanggung_jawab' },
        { data: 'kontak_penanggung_jawab' },
        {
            data: null,
            render: function(data, type, row) {
                return `<button onclick='editRow(${JSON.stringify(row)})' class="bg-yellow-400 px-2 py-1 rounded mr-1">Edit</button>
                        <button onclick='deleteRow(${row.id})' class="bg-red-500 text-white px-2 py-1 rounded">Hapus</button>`;
            },
            orderable: false
        }
    ]
});

// Use FormData for file upload
$('#pasienForm').submit(function(e) {
    e.preventDefault();
    let id = $('#id').val();
    let action = id ? 'update' : 'create';
    let formData = new FormData(this);
    if (!id) formData.delete('id'); // Remove id if creating
    $.ajax({
        url: window.location.pathname + '?action=' + action,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function() {
            table.ajax.reload();
            hideForm();
        }
    });
});

window.editRow = function(row) {
    showForm(row);
}

window.deleteRow = function(id) {
    if (confirm('Hapus data ini?')) {
        $.post(window.location.pathname + '?action=delete', { id }, function() {
            table.ajax.reload();
        }, 'json');
    }
}
</script>