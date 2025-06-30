<?php

include_once __DIR__ . '/../../modules/middlewares/RolePermissionChecker.php';
include_once __DIR__ . '/services.php';

// Ambil data dokter dan pasien untuk dropdown
$db = new Database();
$pdo = $db->openConnection();

// Endpoint AJAX untuk select2 dokter
if (isset($_GET['ajax']) && $_GET['ajax'] === 'dokter') {
    $term = $_GET['term'] ?? '';
    $stmt = $pdo->prepare("SELECT id, nama FROM doctors WHERE nama LIKE :term LIMIT 20");
    $stmt->execute([':term' => "%$term%"]);
    $results = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $results[] = ['id' => $row['id'], 'text' => $row['nama']];
    }
    echo json_encode(['results' => $results]);
    exit;
}

// Endpoint AJAX untuk select2 pasien
if (isset($_GET['ajax']) && $_GET['ajax'] === 'pasien') {
    $term = $_GET['term'] ?? '';
    $stmt = $pdo->prepare("SELECT id, nama_lengkap as nama FROM patients WHERE nama_lengkap LIKE :term LIMIT 20");
    $stmt->execute([':term' => "%$term%"]);
    $results = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $results[] = ['id' => $row['id'], 'text' => $row['nama']];
    }
    echo json_encode(['results' => $results]);
    exit;
}

$kunjunganService = new KunjunganService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';

    if ($action === 'create') {
        $kunjunganService->create($_POST);
        echo json_encode(['success' => true]);
        exit;
    } elseif ($action === 'update') {
        $kunjunganService->update($_POST);
        echo json_encode(['success' => true]);
        exit;
    } elseif ($action === 'delete') {
        $kunjunganService->delete($_POST['id']);
        echo json_encode(['success' => true]);
        exit;
    }

    // DataTables logic
    $result = $kunjunganService->getDataTable($_POST);
    echo json_encode($result);
    die();
}
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<button onclick="showForm()" class="mb-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Tambah Kunjungan</button>
<table id="kunjunganTable" class="display" style="width:100%">
    <thead>
        <tr>
            <th>Dokter</th>
            <th>Pasien</th>
            <th>Tanggal & Waktu</th>
            <th>Diagnosa</th>
            <th>Keluhan</th>
            <th>Tindakan</th>
            <th>Catatan</th>
            <th>Aksi</th>
        </tr>
    </thead>
</table>

<!-- Modal Backdrop and Modal -->
<div id="modalBackdrop" class="fixed inset-0 bg-black bg-opacity-40 z-40 hidden"></div>
<div id="formModal" class="fixed inset-0 flex items-start justify-start z-50 hidden">
    <div class="bg-white h-full w-full md:w-1/2 max-w-lg shadow-lg p-6 relative overflow-y-auto">
        <form id="kunjunganForm" class="space-y-3">
            <input type="hidden" name="id" id="id">
            <!-- ...existing code... -->
            <select style="width: 100%;" name="dokter_id" id="dokter_id" required class="w-full border rounded px-3 py-2 select2"></select>
            <select style="width: 100%;" name="pasien_id" id="pasien_id" required class="w-full border rounded px-3 py-2 select2"></select>
            <!-- ...existing code... -->
            <input type="datetime-local" name="tanggal_waktu" placeholder="Tanggal & Waktu" required class="w-full border rounded px-3 py-2" />
            <input type="text" name="diagnosa" placeholder="Diagnosa" required class="w-full border rounded px-3 py-2" />
            <input type="text" name="keluhan" placeholder="Keluhan" class="w-full border rounded px-3 py-2" />
            <input type="text" name="tindakan" placeholder="Tindakan" class="w-full border rounded px-3 py-2" />
            <textarea name="catatan" placeholder="Catatan" class="w-full border rounded px-3 py-2"></textarea>
            <div class="flex justify-end gap-2 pt-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan</button>
                <button type="button" onclick="hideForm()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
    // $(document).ready(function() {
    //     $('.select2').select2({
    //         dropdownParent: $('#formModal')
    //     });
    // });

    $(document).ready(function() {
        $('#dokter_id').select2({
            dropdownParent: $('#formModal'),
            placeholder: 'Pilih Dokter',
            allowClear: true,
            ajax: {
                url: window.location.pathname + '?ajax=dokter',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term
                    };
                },
                processResults: function(data) {
                    return data;
                },
                cache: true
            }
        });
        $('#pasien_id').select2({
            dropdownParent: $('#formModal'),
            placeholder: 'Pilih Pasien',
            allowClear: true,
            ajax: {
                url: window.location.pathname + '?ajax=pasien',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term
                    };
                },
                processResults: function(data) {
                    return data;
                },
                cache: true
            }
        });
    });

    // Saat edit, preload option terpilih
    function showForm(data = {}) {
        $('#kunjunganForm')[0].reset();
        $('#kunjunganForm input, #kunjunganForm textarea').val('');
        $('#dokter_id').val('').trigger('change');
        $('#pasien_id').val('').trigger('change');
        if (data && Object.keys(data).length) {
            for (let k in data) {
                $('#kunjunganForm [name="' + k + '"]').val(data[k]);
            }
            // Preload dokter
            if (data.dokter_id && data.dokter_nama) {
                let dokterOption = new Option(data.dokter_nama, data.dokter_id, true, true);
                $('#dokter_id').append(dokterOption).trigger('change');
            }
            // Preload pasien
            if (data.pasien_id && data.pasien_nama) {
                let pasienOption = new Option(data.pasien_nama, data.pasien_id, true, true);
                $('#pasien_id').append(pasienOption).trigger('change');
            }
        }
        $('#modalBackdrop').removeClass('hidden');
        $('#formModal').removeClass('hidden');
    }

    function hideForm() {
        $('#modalBackdrop').addClass('hidden');
        $('#formModal').addClass('hidden');
    }

    let table = $('#kunjunganTable').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        ajax: {
            url: window.location.pathname,
            type: 'POST'
        },
        columns: [
            { data: 'dokter_nama' },   // tampilkan nama dokter
            { data: 'pasien_nama' },   // tampilkan nama pasien
            { data: 'tanggal_waktu' },
            { data: 'diagnosa' },
            { data: 'keluhan' },
            { data: 'tindakan' },
            { data: 'catatan' },
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

    $('#kunjunganForm').submit(function(e) {
        e.preventDefault();
        let id = $('#id').val();
        let action = id ? 'update' : 'create';
        let formData = $(this).serialize();
        $.ajax({
            url: window.location.pathname + '?action=' + action,
            type: 'POST',
            data: formData,
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
        if (confirm('Hapus kunjungan ini?')) {
            $.post(window.location.pathname + '?action=delete', {
                id
            }, function() {
                table.ajax.reload();
            }, 'json');
        }
    }
</script>