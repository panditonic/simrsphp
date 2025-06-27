<?php

include_once __DIR__ . '/../../db.php';

$db = new Database();
$pdo = $db->openConnection();

include_once __DIR__ . '/../../modules/middlewares/RolePermissionChecker.php';

?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="https://cdn.tailwindcss.com"></script>

<div class="mx-auto bg-white p-8 rounded shadow">
  <h2 class="text-2xl font-bold mb-6 text-gray-800">Permissions Management</h2>
  <button class="mb-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded transition" id="addPermissionBtn">Add Permission</button>
  <div class="overflow-x-auto">
    <table id="permissionsTable" class="display w-full text-sm text-left text-gray-700">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2">ID</th>
          <th class="px-4 py-2">Name</th>
          <th class="px-4 py-2">Description</th>
          <th class="px-4 py-2">Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Modal (Tailwind) -->
<div id="permissionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
    <form id="permissionForm">
      <div class="flex justify-between items-center px-6 py-4 border-b">
        <h5 class="text-lg font-semibold" id="permissionModalLabel">Add Permission</h5>
        <button type="button" class="text-gray-400 hover:text-gray-700 text-2xl font-bold focus:outline-none" id="closePermissionModal">&times;</button>
      </div>
      <div class="px-6 py-4">
        <input type="hidden" id="permissionId" name="id">
        <div class="mb-4">
          <label for="permissionName" class="block text-gray-700 font-semibold mb-2">Name</label>
          <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:border-blue-400" id="permissionName" name="name" required>
        </div>
        <div class="mb-4">
          <label for="permissionDesc" class="block text-gray-700 font-semibold mb-2">Description</label>
          <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:border-blue-400" id="permissionDesc" name="description" required>
        </div>
      </div>
      <div class="flex justify-end gap-2 px-6 py-4 border-t">
        <button type="button" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded" id="cancelPermissionModal">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded" id="savePermissionBtn">Save</button>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
  // Modal open/close helpers
  function openPermissionModal() {
    $('#permissionModal').removeClass('hidden');
  }
  function closePermissionModal() {
    $('#permissionModal').addClass('hidden');
  }

  $(document).ready(function() {
    var table = $('#permissionsTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: 'modules/permissions/datatable.php',
      columns: [
        { data: 'id' },
        { data: 'name' },
        { data: 'description' },
        {
          data: null,
          orderable: false,
          render: function(data, type, row) {
            return `
              <button class="editPermission px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded mr-2" data-id="${row.id}">Edit</button>
              <button class="deletePermission px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded" data-id="${row.id}">Delete</button>
            `;
          }
        }
      ]
    });

    // Modal open/close events
    $('#addPermissionBtn').click(function() {
      $('#permissionForm')[0].reset();
      $('#permissionId').val('');
      $('#permissionModalLabel').text('Add Permission');
      openPermissionModal();
    });
    $('#closePermissionModal, #cancelPermissionModal').click(function() {
      closePermissionModal();
    });

    // Edit
    $('#permissionsTable').on('click', '.editPermission', function() {
      var id = $(this).data('id');
      $.get('modules/permissions/services.php', { action: 'get', id: id }, function(data) {
        var permission = JSON.parse(data);
        $('#permissionId').val(permission.id);
        $('#permissionName').val(permission.name);
        $('#permissionDesc').val(permission.description);
        $('#permissionModalLabel').text('Edit Permission');
        openPermissionModal();
      });
    });

    // Delete
    $('#permissionsTable').on('click', '.deletePermission', function() {
      if (confirm('Delete this permission?')) {
        var id = $(this).data('id');
        $.post('modules/permissions/services.php', { action: 'delete', id: id }, function() {
          table.ajax.reload();
        });
      }
    });

    // Submit
    $('#permissionForm').submit(function(e) {
      e.preventDefault();
      var formData = $(this).serialize() + '&action=' + ($('#permissionId').val() ? 'update' : 'create');
      $.post('modules/permissions/services.php', formData, function() {
        closePermissionModal();
        table.ajax.reload();
      });
    });

    // ESC to close modal
    $(document).on('keydown', function(e) {
      if (e.key === "Escape") closePermissionModal();
    });
  });
</script>