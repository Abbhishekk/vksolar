<?php
// admin/view_permissions.php
require_once 'connect/auth_middleware.php';
$auth->requireAuth();
$auth->requireAnyRole(['super_admin', 'admin']);
$auth->checkPermission('user_management', 'edit');

$title = "view_permissions";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require('include/head.php'); ?>
  <title>User Permissions - VK Solar</title>
  <style>
    .permission-card {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }
    .permission-card:hover {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .permission-header {
        background: #f8f9fa;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #dee2e6;
        border-radius: 10px 10px 0 0;
    }
    .permission-body {
        padding: 1.5rem;
    }
    .module-title {
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #2e8b57;
    }
    .permission-checkboxes {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
    }
    .form-check {
        margin-bottom: 0.5rem;
        min-width: 80px;
    }
    .form-check-input:checked {
        background-color: #2e8b57;
        border-color: #2e8b57;
    }
    .role-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 500;
    }
    .role-super_admin { background: #fef3c7; color: #92400e; }
    .role-admin { background: #dbeafe; color: #1e40af; }
    .role-office_staff { background: #dcfce7; color: #166534; }
    .role-sales_marketing { background: #f3e8ff; color: #7e22ce; }
    .role-warehouse_staff { background: #ffedd5; color: #c2410c; }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <?php require('include/sidebar.php') ?>

  <!-- Main Content -->
  <div id="main-content">
    <div class="sidebar-overlay"></div>

    <!-- Fixed Header -->
    <?php require('include/navbar.php') ?>

    <!-- Main Content -->
    <main>
      <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="mb-1">User Management</h4>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="./">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="view_users">Users</a></li>
                <li class="breadcrumb-item active">User Permissions</li>
              </ol>
            </nav>
          </div>
          <button type="button" class="btn btn-success" style="position: fixed;z-index:1060;right: 30px" onclick="saveAllPermissions()">
            <i class="bi bi-check-circle me-2"></i>Save All Changes
          </button>
        </div>

        <!-- Message Alert -->
        <div id="messageAlert" class="alert d-none">
          <i id="messageIcon" class="bi me-2"></i>
          <span id="messageText"></span>
        </div>

        <div class="card">
          <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0"><i class="bi bi-shield-check me-2"></i>Role-Based Permissions Management</h5>
          </div>
          <div class="card-body">
            <div id="loadingState" class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2 text-muted">Loading permissions...</p>
            </div>

            <div id="permissionsContainer" class="d-none">
              <!-- Permissions will be loaded here -->
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>


 <script>
document.addEventListener('DOMContentLoaded', function() {
  const permissionsContainer = document.getElementById('permissionsContainer');
  const loadingState = document.getElementById('loadingState');
  const messageAlert = document.getElementById('messageAlert');

  let allPermissions = [];
  let pendingChanges = new Map(); // ✅ FIXED

  loadPermissions();

  function loadPermissions() {
    fetch('api/user_api?action=get_permissions')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          allPermissions = data.data;
          renderPermissions();
          loadingState.style.display = 'none';
          permissionsContainer.classList.remove('d-none');
        } else {
          showMessage('error', data.message);
        }
      })
      .catch(() => showMessage('error', 'Failed to load permissions'));
  }

  function renderPermissions() {
    const permissionsByRole = {};
    allPermissions.forEach(perm => {
      if (!permissionsByRole[perm.role]) permissionsByRole[perm.role] = [];
      permissionsByRole[perm.role].push(perm);
    });

    let html = '';

    for (const [role, modules] of Object.entries(permissionsByRole)) {
      html += `
        <div class="permission-card">
          <div class="permission-header d-flex justify-content-between align-items-center">
            <h6><span class="role-badge role-${role}">${formatRole(role)}</span></h6>
          </div>
          <div class="permission-body">
            <div class="row">
      `;

      modules.forEach(module => {
        html += `
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="module-title">${formatModule(module.module)}</div>
            <div class="permission-checkboxes">
              ${permissionCheckbox(role, module.module, 'view', module.can_view)}
              ${permissionCheckbox(role, module.module, 'create', module.can_create)}
              ${permissionCheckbox(role, module.module, 'edit', module.can_edit)}
              ${permissionCheckbox(role, module.module, 'delete', module.can_delete)}
            </div>
          </div>
        `;
      });

      html += `</div></div></div>`;
    }

    permissionsContainer.innerHTML = html;

    permissionsContainer
      .querySelectorAll('.permission-checkbox')
      .forEach(cb => cb.addEventListener('change', onPermissionChange));
  }

  function permissionCheckbox(role, module, type, checked) {
    return `
      <div class="form-check form-check-inline">
        <input class="form-check-input permission-checkbox"
          type="checkbox"
          data-role="${role}"
          data-module="${module}"
          data-type="${type}"
          ${checked ? 'checked' : ''}>
        <label class="form-check-label">${type.charAt(0).toUpperCase() + type.slice(1)}</label>
      </div>
    `;
  }

  function onPermissionChange(e) {
    const cb = e.target;
    const key = `${cb.dataset.role}|${cb.dataset.module}|${cb.dataset.type}`;

    pendingChanges.set(key, {
      role: cb.dataset.role,
      module: cb.dataset.module,
      permission_type: cb.dataset.type,
      value: cb.checked ? 1 : 0 // ✅ grant or revoke
    });

    updateSaveButtonState();
  }

  function updateSaveButtonState() {
    const btn = document.querySelector('button[onclick="saveAllPermissions()"]');
    const count = pendingChanges.size;

    btn.innerHTML = count
      ? `<i class="bi bi-check-circle me-2"></i>Save Changes (${count})`
      : `<i class="bi bi-check-circle me-2"></i>Save All Changes`;

    btn.classList.toggle('btn-warning', count > 0);
    btn.classList.toggle('btn-success', count === 0);
  }

  window.saveAllPermissions = function() {
    if (!pendingChanges.size) return showMessage('info', 'No changes to save');

    const updates = Array.from(pendingChanges.values());

    const formData = new FormData();
    formData.append('action', 'update_permissions_bulk');
    formData.append('updates', JSON.stringify(updates));

    fetch('api/user_api', { method: 'POST', body: formData })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          showMessage('success', res.message);
          pendingChanges.clear();
          updateSaveButtonState();
          loadPermissions();
        } else {
          showMessage('error', res.message);
        }
      })
      .catch(() => showMessage('error', 'Save failed'));
  };

  function formatRole(role) {
    return role.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
  }

  function formatModule(module) {
    return module.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
  }

  function showMessage(type, msg) {
    messageAlert.className = `alert alert-${type === 'error' ? 'danger' : type} d-block`;
    messageAlert.querySelector('#messageText').textContent = msg;
    setTimeout(() => messageAlert.classList.add('d-none'), 4000);
  }
});
</script>

</body>
</html>