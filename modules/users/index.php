<?php

include_once __DIR__ . '/../../modules/middlewares/RolePermissionChecker.php';
include_once __DIR__ . '/services.php';

$userService = new UserService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';

    if ($action === 'create') {
        $userService->create($_POST);
        echo json_encode(['success' => true]);
        exit;
    } elseif ($action === 'update') {
        $userService->update($_POST);
        echo json_encode(['success' => true]);
        exit;
    } elseif ($action === 'delete') {
        $userService->delete($_POST['id']);
        echo json_encode(['success' => true]);
        exit;
    }

    // DataTables logic
    $result = $userService->getDataTable($_POST);
    echo json_encode($result);
    die();
}

// Fetch all roles for the select dropdown
$roles = $userService->getRoles();
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<button onclick="showForm()" class="mb-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Tambah User</button>
<table id="usersTable" class="display" style="width:100%">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>Role</th>
            <th>Aksi</th>
        </tr>
    </thead>
</table>

<!-- Modal Backdrop and Modal -->
<div id="modalBackdrop" class="fixed inset-0 bg-black bg-opacity-40 z-40 hidden"></div>
<div id="formModal" class="fixed inset-0 flex items-start justify-start z-50 hidden">
    <div class="bg-white h-full w-full md:w-1/2 max-w-lg shadow-lg p-6 relative overflow-y-auto">
        <form id="userForm" class="space-y-3">
            <input type="hidden" name="id" id="id">
            <input type="text" name="name" placeholder="Nama" required class="w-full border rounded px-3 py-2" />
            <input type="email" name="email" placeholder="Email" required class="w-full border rounded px-3 py-2" />
            <input type="password" name="password" placeholder="Password" class="w-full border rounded px-3 py-2" autocomplete="new-password" />
            <select name="role" id="role" required class="w-full border rounded px-3 py-2">
                <option value="">Pilih Role</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= htmlspecialchars($role['id']) ?>"><?= htmlspecialchars($role['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="flex justify-end gap-2 pt-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan</button>
                <button type="button" onclick="hideForm()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Notification Popup -->
<div id="notifPopup" class="fixed top-4 right-4 z-50 hidden px-4 py-2 rounded shadow text-white"></div>

<script>
function showForm(data = {}) {
    $('#userForm')[0].reset();
    $('#userForm input[type=hidden], #userForm input[type=text], #userForm input[type=email]').val('');
    $('#userForm input[type=password]').val('');
    $('#role').val('');
    if (data && Object.keys(data).length) {
        for (let k in data) {
            if (k !== 'password') $('#userForm [name="' + k + '"]').val(data[k]);
        }
        if (data.role) $('#role').val(data.role);
    }
    $('#modalBackdrop').removeClass('hidden');
    $('#formModal').removeClass('hidden');
}

function hideForm() {
    $('#modalBackdrop').addClass('hidden');
    $('#formModal').addClass('hidden');
}

function showNotif(message, type = 'success') {
    const notif = $('#notifPopup');
    notif.removeClass('hidden bg-green-600 bg-red-600');
    notif.addClass(type === 'success' ? 'bg-green-600' : 'bg-red-600');
    notif.text(message);
    notif.fadeIn(200);

    setTimeout(() => {
        notif.fadeOut(400, function() {
            notif.addClass('hidden');
        });
    }, 2500);
}

let table = $('#usersTable').DataTable({
    processing: true,
    serverSide: true,
    ordering: false, // <--- Tambahkan baris ini untuk disable sorting
    ajax: {
        url: window.location.pathname,
        type: 'POST'
    },
    columns: [
        { data: 'name' },
        { data: 'email' },
        { data: 'role_name', defaultContent: '-' },
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

$('#userForm').submit(function(e) {
    e.preventDefault();
    let id = $('#id').val();
    let action = id ? 'update' : 'create';
    let formData = $(this).serialize();
    if (!id) formData = formData.replace(/id=[^&]*&?/, ''); // Remove id if creating
    $.ajax({
        url: window.location.pathname + '?action=' + action,
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(res) {
            table.ajax.reload();
            hideForm();
            showNotif('Data berhasil disimpan!', 'success');
        },
        error: function() {
            showNotif('Gagal menyimpan data!', 'error');
        }
    });
});

window.editRow = function(row) {
    showForm(row);
}

window.deleteRow = function(id) {
    if (confirm('Hapus user ini?')) {
        $.post(window.location.pathname + '?action=delete', { id }, function() {
            table.ajax.reload();
            showNotif('User berhasil dihapus!', 'success');
        }, 'json').fail(function() {
            showNotif('Gagal menghapus user!', 'error');
        });
    }
}
</script>