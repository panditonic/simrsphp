<?php

include_once __DIR__ . '/../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $pdo = $db->openConnection();

    $action = $_GET['action'] ?? '';

    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
        $stmt->execute([
            ':name' => $_POST['name'],
            ':email' => $_POST['email'],
            ':password' => password_hash($_POST['password'], PASSWORD_DEFAULT)
        ]);
        echo json_encode(['success' => true]);
        exit;
    } elseif ($action === 'update') {
        // If password is empty, don't update it
        if (!empty($_POST['password'])) {
            $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, password = :password WHERE id = :id");
            $stmt->execute([
                ':name' => $_POST['name'],
                ':email' => $_POST['email'],
                ':password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                ':id' => $_POST['id']
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email WHERE id = :id");
            $stmt->execute([
                ':name' => $_POST['name'],
                ':email' => $_POST['email'],
                ':id' => $_POST['id']
            ]);
        }
        echo json_encode(['success' => true]);
        exit;
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $_POST['id']]);
        echo json_encode(['success' => true]);
        exit;
    }

    // DataTables logic
    $start = intval($_POST['start'] ?? 0);
    $length = intval($_POST['length'] ?? 10);
    $search = $_POST['search']['value'] ?? '';

    $totalQuery = $pdo->query("SELECT COUNT(*) FROM users");
    $recordsTotal = $totalQuery->fetchColumn();

    $where = '';
    $params = [];
    if ($search) {
        $where = "WHERE name LIKE :search OR email LIKE :search";
        $params[':search'] = "%$search%";
    }

    $stmt = $pdo->prepare("SELECT id, name, email FROM users $where LIMIT :start, :length");
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Query filtered count
    if ($where) {
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users $where");
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

<button onclick="showForm()" class="mb-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Tambah User</button>
<table id="usersTable" class="display" style="width:100%">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Email</th>
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
    if (data && Object.keys(data).length) {
        for (let k in data) {
            if (k !== 'password') $('#userForm [name="' + k + '"]').val(data[k]);
        }
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
    ajax: {
        url: window.location.pathname,
        type: 'POST'
    },
    columns: [
        { data: 'name' },
        { data: 'email' },
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