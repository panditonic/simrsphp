<?php

include_once __DIR__ . '/services.php';

$doctorService = new DoctorService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';

    if ($action === 'create') {
        $doctorService->create($_POST, $_FILES);
        echo json_encode(['success' => true]);
        exit;
    } elseif ($action === 'update') {
        $doctorService->update($_POST, $_FILES);
        echo json_encode(['success' => true]);
        exit;
    } elseif ($action === 'delete') {
        $doctorService->delete($_POST['id']);
        echo json_encode(['success' => true]);
        exit;
    }

    // DataTables logic
    $result = $doctorService->getDataTable($_POST);
    echo json_encode($result);
    die();
}
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<button onclick="showForm()" class="mb-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Tambah Dokter</button>
<table id="doctorsTable" class="display" style="width:100%">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Spesialisasi</th>
            <th>Nomor STR</th>
            <th>Jenis Kelamin</th>
            <th>Tanggal Lahir</th>
            <th>Alamat</th>
            <th>Telepon</th>
            <th>Email</th>
            <th>Foto</th>
            <th>Aksi</th>
        </tr>
    </thead>
</table>

<!-- Modal Backdrop and Modal -->
<div id="modalBackdrop" class="fixed inset-0 bg-black bg-opacity-40 z-40 hidden"></div>
<div id="formModal" class="fixed inset-0 flex items-start justify-start z-50 hidden">
    <div class="bg-white h-full w-full md:w-1/2 max-w-lg shadow-lg p-6 relative overflow-y-auto">
        <form id="doctorForm" class="space-y-3" enctype="multipart/form-data">
            <input type="hidden" name="id" id="id">
            <input type="text" name="nama" placeholder="Nama" required class="w-full border rounded px-3 py-2" />
            <input type="text" name="spesialisasi" placeholder="Spesialisasi" required class="w-full border rounded px-3 py-2" />
            <input type="text" name="nomor_str" placeholder="Nomor STR" class="w-full border rounded px-3 py-2" />
            <select name="jenis_kelamin" class="w-full border rounded px-3 py-2" required>
                <option value="">Pilih Jenis Kelamin</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
            </select>
            <input type="date" name="tanggal_lahir" placeholder="Tanggal Lahir" class="w-full border rounded px-3 py-2" />
            <input type="text" name="alamat" placeholder="Alamat" class="w-full border rounded px-3 py-2" />
            <input type="text" name="telepon" placeholder="Telepon" class="w-full border rounded px-3 py-2" />
            <input type="email" name="email" placeholder="Email" class="w-full border rounded px-3 py-2" />
            <input type="file" name="foto" id="foto" accept="image/*" class="w-full border rounded px-3 py-2" />
            <div class="flex justify-end gap-2 pt-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan</button>
                <button type="button" onclick="hideForm()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function showForm(data = {}) {
    $('#doctorForm')[0].reset();
    $('#doctorForm input[type=hidden], #doctorForm input[type=text], #doctorForm input[type=email], #doctorForm input[type=date]').val('');
    $('#doctorForm select[name=jenis_kelamin]').val('');
    $('#foto').val('');
    if (data && Object.keys(data).length) {
        for (let k in data) {
            if (k !== 'foto') $('#doctorForm [name="' + k + '"]').val(data[k]);
            if (k === 'jenis_kelamin') $('#doctorForm select[name=jenis_kelamin]').val(data[k]);
        }
    }
    $('#modalBackdrop').removeClass('hidden');
    $('#formModal').removeClass('hidden');
}

function hideForm() {
    $('#modalBackdrop').addClass('hidden');
    $('#formModal').addClass('hidden');
}

let table = $('#doctorsTable').DataTable({
    processing: true,
    serverSide: true,
    ordering: false, // <--- Tambahkan baris ini untuk disable sorting
    ajax: {
        url: window.location.pathname,
        type: 'POST'
    },
    columns: [
        { data: 'nama' },
        { data: 'spesialisasi' },
        { data: 'nomor_str' },
        { data: 'jenis_kelamin', render: d => d === 'L' ? 'Laki-laki' : (d === 'P' ? 'Perempuan' : '') },
        { data: 'tanggal_lahir' },
        { data: 'alamat' },
        { data: 'telepon' },
        { data: 'email' },
        {
            data: 'foto',
            render: d => d ? `<img src="${d}" width="40">` : ''
        },
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
$('#doctorForm').submit(function(e) {
    e.preventDefault();
    let id = $('#id').val();
    let action = id ? 'update' : 'create';
    let formData = new FormData(this);
    if (!id) formData.delete('id');
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
    if (confirm('Hapus dokter ini?')) {
        $.post(window.location.pathname + '?action=delete', { id }, function() {
            table.ajax.reload();
        }, 'json');
    }
}
</script>