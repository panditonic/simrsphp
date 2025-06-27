<?php

include_once __DIR__ . '/../../db.php';

$db = new Database();
$pdo = $db->openConnection();

include_once __DIR__ . '/../../modules/middlewares/RolePermissionChecker.php';

?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="https://cdn.tailwindcss.com"></script>

<div class="mx-auto bg-white p-8 rounded shadow">
  <h2 class="text-2xl font-bold mb-6 text-gray-800">Roles Management</h2>
  <button class="mb-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded transition" id="addRoleBtn">Add Role</button>
  <div class="overflow-x-auto">
    <table id="rolesTable" class="display w-full text-sm text-left text-gray-700">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2">ID</th>
          <th class="px-4 py-2">Name</th>
          <th class="px-4 py-2">Description</th>
          <th class="px-4 py-2">Permissions</th>
          <th class="px-4 py-2">Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Modal (Tailwind) -->
<div id="roleModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
    <form id="roleForm">
      <div class="flex justify-between items-center px-6 py-4 border-b">
        <h5 class="text-lg font-semibold" id="roleModalLabel">Add Role</h5>
        <button type="button" class="text-gray-400 hover:text-gray-700 text-2xl font-bold focus:outline-none" id="closeRoleModal">&times;</button>
      </div>
      <div class="px-6 py-4">
        <input type="hidden" id="roleId" name="id">
        <div class="mb-4">
          <label for="roleName" class="block text-gray-700 font-semibold mb-2">Name</label>
          <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:border-blue-400" id="roleName" name="name" required>
        </div>
        <div class="mb-4">
          <label for="roleDesc" class="block text-gray-700 font-semibold mb-2">Description</label>
          <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:border-blue-400" id="roleDesc" name="description" required>
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 font-semibold mb-2">Permissions</label>
          <div id="permissionsCheckboxes" class="flex flex-wrap gap-2">
            <!-- Permissions checkboxes will be loaded here -->
          </div>
        </div>
      </div>
      <div class="flex justify-end gap-2 px-6 py-4 border-t">
        <button type="button" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded" id="cancelRoleModal">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded" id="saveRoleBtn">Save</button>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
  // Modal open/close helpers
  function openRoleModal() {
    $('#roleModal').removeClass('hidden');
  }

  function closeRoleModal() {
    $('#roleModal').addClass('hidden');
  }

  // Load all permissions as checkboxes
  function loadPermissions(selected = []) {
    $.get('modules/roles/services.php', {
      action: 'permissions'
    }, function(data) {
      var permissions = JSON.parse(data);
      var html = '';
      permissions.forEach(function(perm) {
        // Compare by name, since value is perm.name and DB stores names in permissions array
        var checked = selected.includes(perm.name) ? 'checked' : '';
        html += `<label class="inline-flex items-center">
        <input type="checkbox" name="permissions[]" value="${perm.name}" class="mr-2" ${checked}>
        ${perm.name}
      </label>`;
      });
      $('#permissionsCheckboxes').html(html);
    });
  }

  $(document).ready(function() {
    var table = $('#rolesTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: 'modules/roles/datatable.php',
      columns: [{
          data: 'id'
        },
        {
          data: 'name'
        },
        {
          data: 'description'
        },
        {
          data: 'permissions',
          orderable: false,
          render: function(data, type, row) {
            // If permissions is a JSON string, parse it
            let perms = [];
            if (typeof data === 'string') {
              try {
                perms = JSON.parse(data);
              } catch (e) {
                perms = [];
              }
            } else if (Array.isArray(data)) {
              perms = data;
            }
            if (!perms || perms.length === 0) return '';
            return perms.map(function(p) {
              return `<span class="inline-block bg-gray-200 rounded px-2 py-1 mr-1 mb-1">${p}</span>`;
            }).join('');
          }
        },
        {
          data: null,
          orderable: false,
          render: function(data, type, row) {
            return `
              <button class="editRole px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded mr-2" data-id="${row.id}">Edit</button>
              <button class="deleteRole px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded" data-id="${row.id}">Delete</button>
            `;
          }
        }
      ]
    });

    // Modal open/close events
    $('#addRoleBtn').click(function() {
      $('#roleForm')[0].reset();
      $('#roleId').val('');
      $('#roleModalLabel').text('Add Role');
      loadPermissions([]);
      openRoleModal();
    });
    $('#closeRoleModal, #cancelRoleModal').click(function() {
      closeRoleModal();
    });

    // Edit
    $('#rolesTable').on('click', '.editRole', function() {
      var id = $(this).data('id');
      $.get('modules/roles/services.php', {
        action: 'get',
        id: id
      }, function(data) {
        var role = JSON.parse(data);
        $('#roleId').val(role.id);
        $('#roleName').val(role.name);
        $('#roleDesc').val(role.description);
        loadPermissions(role.permissions || []);
        $('#roleModalLabel').text('Edit Role');
        openRoleModal();
      });
    });

    // Delete
    $('#rolesTable').on('click', '.deleteRole', function() {
      if (confirm('Delete this role?')) {
        var id = $(this).data('id');
        $.post('modules/roles/services.php', {
          action: 'delete',
          id: id
        }, function() {
          table.ajax.reload();
        });
      }
    });

    // Submit
    $('#roleForm').submit(function(e) {
      e.preventDefault();
      var formData = $(this).serialize() + '&action=' + ($('#roleId').val() ? 'update' : 'create');
      $.post('modules/roles/services.php', formData, function() {
        closeRoleModal();
        table.ajax.reload();
      });
    });

    // ESC to close modal
    $(document).on('keydown', function(e) {
      if (e.key === "Escape") closeRoleModal();
    });
  });
</script>