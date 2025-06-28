<?php

include_once __DIR__ . '/../../modules/middlewares/RolePermissionChecker.php';
include_once __DIR__ . '/services.php';

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../observers/SatusehatObserver.php';

$patientService = new PatientService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';

    if ($action === 'cek_nik') {
        $result = $patientService->cekBPJSByNoNik($_POST['no_nik']);
        echo json_encode($result);
        exit;
    }

    if ($action === 'cek_satusehat') {
        $observer = new SatusehatObserver();
        $satusehatId = $observer->searchSatusehatIdByNik($_POST['nik']);
        echo json_encode($satusehatId);
        exit;
    } elseif ($action === 'cek_bpjs') {
        $result = $patientService->cekBPJSByNoBpjs($_POST['no_bpjs']);
        echo json_encode($result);
        exit;
    } elseif ($action === 'create') {
        $patientService->create($_POST, $_FILES);
        echo json_encode(['success' => true]);
        exit;
    } elseif ($action === 'update') {
        $patientService->update($_POST, $_FILES);
        echo json_encode(['success' => true]);
        exit;
    } elseif ($action === 'delete') {
        $patientService->delete($_POST['id']);
        echo json_encode(['success' => true]);
        exit;
    }

    // DataTables logic
    $result = $patientService->getDataTable($_POST);
    echo json_encode($result);
    die();
}
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<button onclick="showForm()" class="mb-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Tambah Pasien</button>

<table id="pasienTable" class="display" style="width:100%">
    <thead>
        <tr>
            <th>No RM</th>
            <th>No NIK</th> <!-- Added -->
            <th>No BPJS</th> <!-- Added -->
            <th>No SatuSehat</th> <!-- Added -->
            <th>Nama Lengkap</th>
            <th>Alamat</th>
            <th>Telepon</th>
            <th>Foto</th>
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
            <div class="flex gap-2">
                <input type="text" name="no_nik" id="no_nik" placeholder="No NIK" class="w-full border rounded px-3 py-2" />
                <button type="button" onclick="cekNIK()" class="bg-blue-500 text-white px-2 py-1 rounded">Cek NIK</button>
            </div>
            <div class="flex gap-2">
                <input type="text" name="no_bpjs" id="no_bpjs" placeholder="No BPJS" class="w-full border rounded px-3 py-2" />
                <!-- <button type="button" onclick="cekBPJS()" class="bg-blue-500 text-white px-2 py-1 rounded">Cek BPJS</button> -->
            </div>
            <div class="flex gap-2">
                <input type="text" name="satusehat" id="satusehat" placeholder="No SatuSehat" class="w-full border rounded px-3 py-2" />
            </div>
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
    function cekNIK() {
        let no_nik = $('#no_nik').val();
        if (!no_nik) {
            alert('Masukkan No NIK terlebih dahulu!');
            return;
        }
        $.post(window.location.pathname + '?action=cek_nik', {
            no_nik
        }, function(res) {
            // If response is nested, adjust accordingly
            // Example assumes res.peserta exists
            if (res && res.peserta) {
                $('#no_nik').val(res.peserta.nik || '');
                $('#no_bpjs').val(res.peserta.noKartu || '');
                $('[name="nama_lengkap"]').val(res.peserta.nama || '');
                $('[name="alamat"]').val(res.peserta.alamat || '');
                $('[name="telepon"]').val(res.peserta.mr && res.peserta.mr.noTelepon ? res.peserta.mr.noTelepon : '');
                // Add more fields as needed
            } else {
                alert('Data BPJS tidak ditemukan atau format salah!');
            }
        }, 'json');

        getSatusehatIdByNik(no_nik);
    }

    function getSatusehatIdByNik(nik) {
        $.ajax({
            url: window.location.pathname + '?action=cek_satusehat', // Buat endpoint PHP ini
            type: 'POST',
            data: {
                nik: nik
            },
            dataType: 'json',
            success: function(res) {
                if (res && res.id) {
                    $('#satusehat').val(res.id);
                } else {
                    $('#satusehat').val('');
                    alert('No SatuSehat tidak ditemukan untuk NIK ini.');
                }
            },
            error: function() {
                $('#satusehat').val('');
                alert('Gagal mengambil data SatuSehat.');
            }
        });
    }

    function cekBPJS() {
        let no_bpjs = $('#no_bpjs').val();
        if (!no_bpjs) {
            alert('Masukkan No BPJS terlebih dahulu!');
            return;
        }
        $.post(window.location.pathname + '?action=cek_bpjs', {
            no_bpjs
        }, function(res) {
            console.log(res)
        }, 'json');
    }

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
        responsive: true, // <-- Add this line
        ordering: false, // <--- Tambahkan baris ini untuk disable sorting
        ajax: {
            url: window.location.pathname,
            type: 'POST'
        },
        columns: [{
                data: 'no_rm'
            },
            {
                data: 'no_nik'
            }, // Added
            {
                data: 'no_bpjs'
            }, // Added
            {
                data: 'satusehat'
            },
            {
                data: 'nama_lengkap'
            },
            {
                data: 'alamat'
            },
            {
                data: 'telepon'
            },
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
            $.post(window.location.pathname + '?action=delete', {
                id
            }, function() {
                table.ajax.reload();
            }, 'json');
        }
    }
</script>