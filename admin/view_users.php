<?php
// admin/view_users.php
require_once 'connect/auth_middleware.php';
$auth->requireAuth();
$auth->requireAnyRole(['super_admin', 'admin']);

$auth->requirePermission('quotation_management', 'view');

$title = "view_users";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require('include/head.php'); ?>
  <title>User Management - VK Solar</title>
  <style>
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    .status-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .status-active {
        background: #d1fae5;
        color: #065f46;
    }
    .status-inactive {
        background: #fee2e2;
        color: #7f1d1d;
    }
    .role-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .role-super_admin {
        background: #fef3c7;
        color: #92400e;
    }
    .role-admin {
        background: #dbeafe;
        color: #1e40af;
    }
    .role-office_staff {
        background: #dcfce7;
        color: #166534;
    }
    .action-btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .stats-card {
        background: linear-gradient(135deg, #2e8b57, #3cb371);
        color: white;
        border-radius: 10px;
        padding: 1rem;
    }
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
                <li class="breadcrumb-item active">System Users</li>
              </ol>
            </nav>
          </div>
          <?php if ($auth->checkPermission('user_management', 'create')): ?>
          <a href="add_user" class="btn btn-success">
            <i class="bi bi-person-plus me-2"></i>Add New User
          </a>
          <?php endif; ?>
        </div>

        <!-- Message Alert -->
        <div id="messageAlert" class="alert d-none">
          <i id="messageIcon" class="bi me-2"></i>
          <span id="messageText"></span>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
              <div class="d-flex justify-content-between">
                <div>
                  <h6 class="text-success-50">Total Users</h6>
                  <h3 id="totalUsers" class="mb-0">0</h3>
                </div>
                <div class="align-self-center">
                  <i class="bi bi-people-fill fs-1 text-white-50"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
              <div class="d-flex justify-content-between">
                <div>
                  <h6 class="text-white-50">Active Users</h6>
                  <h3 id="activeUsers" class="mb-0">0</h3>
                </div>
                <div class="align-self-center">
                  <i class="bi bi-person-check fs-1 text-white-50"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
              <div class="d-flex justify-content-between">
                <div>
                  <h6 class="text-white-50">Admins</h6>
                  <h3 id="adminUsers" class="mb-0">0</h3>
                </div>
                <div class="align-self-center">
                  <i class="bi bi-shield-check fs-1 text-white-50"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
              <div class="d-flex justify-content-between">
                <div>
                  <h6 class="text-white-50">Staff Users</h6>
                  <h3 id="staffUsers" class="mb-0">0</h3>
                </div>
                <div class="align-self-center">
                  <i class="bi bi-briefcase fs-1 text-white-50"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters Section -->
        <div class="filter-section">
          <div class="row g-3 align-items-end">
            <div class="col-md-3">
              <label for="roleFilter" class="form-label">Filter by Role</label>
              <select class="form-select" id="roleFilter">
                <option value="">All Roles</option>
                <option value="super_admin">Super Admin</option>
                <option value="admin">Admin</option>
                <option value="office_staff">Office Staff</option>
                <option value="sales_marketing">Sales & Marketing</option>
                <option value="warehouse_staff">Warehouse Staff</option>
              </select>
            </div>
            <div class="col-md-3">
              <label for="statusFilter" class="form-label">Filter by Status</label>
              <select class="form-select" id="statusFilter">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="searchInput" class="form-label">Search Users</label>
              <input type="text" class="form-control" id="searchInput" placeholder="Search by username, email, or name...">
            </div>
            <div class="col-md-2">
              <button type="button" class="btn btn-outline-secondary w-100" id="resetFilters">
                <i class="bi bi-arrow-clockwise me-2"></i>Reset
              </button>
            </div>
          </div>
        </div>

        <!-- Users Table -->
        <div class="card table-card">
          <div class="card-header bg-light">
            <h5 class="card-title mb-0 text-white"><i class="bi bi-people me-2"></i>System Users</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover" id="usersTable">
                <thead>
                  <tr>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Created</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="usersTableBody">
                  <!-- Users will be loaded here via JavaScript -->
                </tbody>
              </table>
            </div>
            
            <!-- Loading State -->
            <div id="loadingState" class="text-center py-4">
              <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2 text-muted">Loading users...</p>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="text-center py-5 d-none">
              <i class="bi bi-people fs-1 text-muted"></i>
              <h5 class="mt-3 text-muted">No users found</h5>
              <p class="text-muted">Get started by adding your first system user.</p>
              <a href="add_user" class="btn btn-success">
                <i class="bi bi-person-plus me-2"></i>Add User
              </a>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Bootstrap 5 JS Bundle -->


  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const usersTableBody = document.getElementById('usersTableBody');
      const loadingState = document.getElementById('loadingState');
      const emptyState = document.getElementById('emptyState');
      const messageAlert = document.getElementById('messageAlert');
      const roleFilter = document.getElementById('roleFilter');
      const statusFilter = document.getElementById('statusFilter');
      const searchInput = document.getElementById('searchInput');
      const resetFilters = document.getElementById('resetFilters');

      let allUsers = [];
      let filteredUsers = [];

      // Load users on page load
      loadUsers();

      // Filter events
      roleFilter.addEventListener('change', filterUsers);
      statusFilter.addEventListener('change', filterUsers);
      searchInput.addEventListener('input', filterUsers);
      resetFilters.addEventListener('click', resetAllFilters);

      function loadUsers() {
        showLoadingState();
        
        fetch('api/user_api?action=get_users')
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.json();
          })
          .then(data => {
            console.log('Users API Response:', data);
            if (data.success) {
              allUsers = data.data;
              filteredUsers = [...allUsers];
              updateStatistics();
              renderUsers();
              hideLoadingState();
            } else {
              showMessage('error', data.message || 'Failed to load users');
              hideLoadingState();
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showMessage('error', 'Failed to load users: ' + error.message);
            hideLoadingState();
          });
      }

      function updateStatistics() {
        const total = allUsers.length;
        const active = allUsers.filter(user => user.is_active).length;
        const admins = allUsers.filter(user => user.role === 'admin' || user.role === 'super_admin').length;
        const staff = allUsers.filter(user => user.role === 'office_staff' || user.role === 'sales_marketing' || user.role === 'warehouse_staff').length;

        document.getElementById('totalUsers').textContent = total;
        document.getElementById('activeUsers').textContent = active;
        document.getElementById('adminUsers').textContent = admins;
        document.getElementById('staffUsers').textContent = staff;
      }

      function filterUsers() {
        const roleValue = roleFilter.value;
        const statusValue = statusFilter.value;
        const searchValue = searchInput.value.toLowerCase();

        filteredUsers = allUsers.filter(user => {
          const matchesRole = !roleValue || user.role === roleValue;
          const matchesStatus = !statusValue || user.is_active.toString() === statusValue;
          const matchesSearch = !searchValue || 
            user.username.toLowerCase().includes(searchValue) ||
            user.full_name.toLowerCase().includes(searchValue) ||
            user.email.toLowerCase().includes(searchValue);

          return matchesRole && matchesStatus && matchesSearch;
        });

        renderUsers();
      }

      function resetAllFilters() {
        roleFilter.value = '';
        statusFilter.value = '';
        searchInput.value = '';
        filterUsers();
      }

      function renderUsers() {
        if (filteredUsers.length === 0) {
          usersTableBody.innerHTML = '';
          emptyState.classList.remove('d-none');
          return;
        }

        emptyState.classList.add('d-none');
        
        usersTableBody.innerHTML = filteredUsers.map(user => `
          <tr>
            <td>
              <div class="fw-semibold">${user.username}</div>
            </td>
            <td>${user.full_name}</td>
            <td>${user.email}</td>
            <td>
              <span class="role-badge role-${user.role}">
                ${formatRole(user.role)}
              </span>
            </td>
            <td>${user.phone || '-'}</td>
            <td>
              <span class="status-badge ${user.is_active ? 'status-active' : 'status-inactive'}">
                ${user.is_active ? 'Active' : 'Inactive'}
              </span>
            </td>
            <td>${user.last_login ? formatDateTime(user.last_login) : 'Never'}</td>
            <td>${formatDateTime(user.created_at)}</td>
            <td>
                <div class="btn-group btn-group-sm gap-2">
                    <button type="button" class="btn btn-outline-success action-btn" onclick="editUser(${user.id})" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-outline-warning action-btn" onclick="toggleUserStatus(${user.id}, ${user.is_active})" title="${user.is_active ? 'Deactivate' : 'Activate'}">
                        <i class="bi ${user.is_active ? 'bi-person-x' : 'bi-person-check'}"></i>
                    </button>
                    <?php if ($auth->checkPermission('user_management', 'delete')): ?>
                    <button type="button" class="btn btn-outline-danger action-btn" onclick="deleteUser(${user.id}, '${user.username}')" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </td>
          </tr>
        `).join('');
      }

      function formatRole(role) {
        const roleMap = {
          'super_admin': 'Super Admin',
          'admin': 'Admin',
          'office_staff': 'Office Staff',
          'sales_marketing': 'Sales & Marketing',
          'warehouse_staff': 'Warehouse Staff'
        };
        return roleMap[role] || role;
      }

      function formatDateTime(dateTimeString) {
        if (!dateTimeString) return '-';
        const date = new Date(dateTimeString);
        return date.toLocaleString('en-IN');
      }

      function showLoadingState() {
        loadingState.style.display = 'block';
        emptyState.classList.add('d-none');
      }

      function hideLoadingState() {
        loadingState.style.display = 'none';
      }

      function showMessage(type, message) {
        const icon = messageAlert.querySelector('#messageIcon');
        const text = messageAlert.querySelector('#messageText');
        
        messageAlert.className = `alert alert-${type === 'success' ? 'success' : 'danger'} d-block`;
        icon.className = `bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} me-2`;
        text.textContent = message;
        
        setTimeout(() => {
          messageAlert.classList.add('d-none');
        }, 5000);
      }

    });

    // Action functions
    window.editUser = function(userId) {
        console.log('Edit clicked for user ID:', userId);
        const editUrl = `edit_user?id=${userId}`;
        console.log('Attempting to navigate to:', editUrl);
        window.location.href = editUrl;
    };

    window.toggleUserStatus = function(userId, currentStatus) {
        if (confirm(`Are you sure you want to ${currentStatus ? 'deactivate' : 'activate'} this user?`)) {
            const formData = new FormData();
            formData.append('action', 'toggle_user_status');
            formData.append('id', userId);
            formData.append('status', currentStatus ? 0 : 1);

            fetch('api/user_api', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('success', data.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showMessage('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('error', 'Failed to update user status');
            });
        }
    };

    window.deleteUser = function(userId, username) {
        if (confirm(`Are you sure you want to delete user "${username}"? This action cannot be undone.`)) {
            const formData = new FormData();
            formData.append('action', 'delete_user');
            formData.append('id', userId);

            fetch('api/user_api', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('success', data.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showMessage('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('error', 'Failed to delete user');
            });
        }
    };
  </script>


    <!-- Custom JavaScript -->
    <script src="css&js/script.js"></script>
</body>
</html>