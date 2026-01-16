<?php
// admin/view_employees.php (now in admin root)
require_once 'connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('employee_management', 'view');

$title = "view_employees";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require('include/head.php'); ?>
  <title>View Employees - VK Solar</title>
  <style>
    .employee-avatar {
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
    .action-btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .table-card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .filter-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1.5rem;
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
            <h4 class="mb-1">Employee Management</h4>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="./">Dashboard</a></li>
                <li class="breadcrumb-item active">View Employees</li>
              </ol>
            </nav>
          </div>
          <a href="add_employee" class="btn btn-success">
            <i class="bi bi-person-plus me-2"></i>Add New Employee
          </a>
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
                  <h6 class="text-white-50">Total Employees</h6>
                  <h3 id="totalEmployees" class="mb-0">0</h3>
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
                  <h6 class="text-white-50">Active Employees</h6>
                  <h3 id="activeEmployees" class="mb-0">0</h3>
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
                  <h6 class="text-white-50">Office Staff</h6>
                  <h3 id="officeStaff" class="mb-0">0</h3>
                </div>
                <div class="align-self-center">
                  <i class="bi bi-briefcase fs-1 text-white-50"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
              <div class="d-flex justify-content-between">
                <div>
                  <h6 class="text-white-50">Sales Team</h6>
                  <h3 id="salesTeam" class="mb-0">0</h3>
                </div>
                <div class="align-self-center">
                  <i class="bi bi-graph-up fs-1 text-white-50"></i>
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
                <option value="office_staff">Office Staff</option>
                <option value="sales_marketing">Sales & Marketing</option>
                <option value="warehouse_staff">Warehouse Staff</option>
                <option value="admin">Admin</option>
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
              <label for="searchInput" class="form-label">Search Employees</label>
              <input type="text" class="form-control" id="searchInput" placeholder="Search by name, email, or ID...">
            </div>
            <div class="col-md-2">
              <button type="button" class="btn btn-outline-secondary w-100" id="resetFilters">
                <i class="bi bi-arrow-clockwise me-2"></i>Reset
              </button>
            </div>
          </div>
        </div>

        <!-- Employees Table -->
        <div class="card table-card">
          <div class="card-header bg-light">
            <h5 class="card-title mb-0"><i class="bi bi-people me-2"></i>Employees List</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover" id="employeesTable">
                <thead>
                  <tr>
                    <th>Photo</th>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Joining Date</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="employeesTableBody">
                  <!-- Employees will be loaded here via JavaScript -->
                </tbody>
              </table>
            </div>
            
            <!-- Loading State -->
            <div id="loadingState" class="text-center py-4">
              <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2 text-muted">Loading employees...</p>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="text-center py-5 d-none">
              <i class="bi bi-people fs-1 text-muted"></i>
              <h5 class="mt-3 text-muted">No employees found</h5>
              <p class="text-muted">Get started by adding your first employee.</p>
              <a href="add_employee" class="btn btn-success">
                <i class="bi bi-person-plus me-2"></i>Add Employee
              </a>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const employeesTableBody = document.getElementById('employeesTableBody');
      const loadingState = document.getElementById('loadingState');
      const emptyState = document.getElementById('emptyState');
      const messageAlert = document.getElementById('messageAlert');
      const roleFilter = document.getElementById('roleFilter');
      const statusFilter = document.getElementById('statusFilter');
      const searchInput = document.getElementById('searchInput');
      const resetFilters = document.getElementById('resetFilters');

      let allEmployees = [];
      let filteredEmployees = [];

      // Load employees on page load
      loadEmployees();

      // Filter events
      roleFilter.addEventListener('change', filterEmployees);
      statusFilter.addEventListener('change', filterEmployees);
      searchInput.addEventListener('input', filterEmployees);
      resetFilters.addEventListener('click', resetAllFilters);

      function loadEmployees() {
        showLoadingState();
        
        // FIXED PATH: Using correct API path without .php extension
        fetch('api/employee_api?action=get_employees')
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.json();
          })
          .then(data => {
            console.log('API Response:', data); // Debug log
            if (data.success) {
              allEmployees = data.data;
              filteredEmployees = [...allEmployees];
              updateStatistics();
              renderEmployees();
              hideLoadingState();
            } else {
              showMessage('error', data.message || 'Failed to load employees');
              hideLoadingState();
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showMessage('error', 'Failed to load employees: ' + error.message);
            hideLoadingState();
          });
      }

      function updateStatistics() {
        const total = allEmployees.length;
        const active = allEmployees.filter(emp => emp.is_active).length;
        const officeStaff = allEmployees.filter(emp => emp.role === 'office_staff').length;
        const salesTeam = allEmployees.filter(emp => emp.role === 'sales_marketing').length;

        document.getElementById('totalEmployees').textContent = total;
        document.getElementById('activeEmployees').textContent = active;
        document.getElementById('officeStaff').textContent = officeStaff;
        document.getElementById('salesTeam').textContent = salesTeam;
      }

      function filterEmployees() {
        const roleValue = roleFilter.value;
        const statusValue = statusFilter.value;
        const searchValue = searchInput.value.toLowerCase();

        filteredEmployees = allEmployees.filter(employee => {
          const matchesRole = !roleValue || employee.role === roleValue;
          const matchesStatus = !statusValue || employee.is_active.toString() === statusValue;
          const matchesSearch = !searchValue || 
            employee.full_name.toLowerCase().includes(searchValue) ||
            employee.employee_id.toLowerCase().includes(searchValue) ||
            employee.email.toLowerCase().includes(searchValue);

          return matchesRole && matchesStatus && matchesSearch;
        });

        renderEmployees();
      }

      function resetAllFilters() {
        roleFilter.value = '';
        statusFilter.value = '';
        searchInput.value = '';
        filterEmployees();
      }

      function renderEmployees() {
        if (filteredEmployees.length === 0) {
          employeesTableBody.innerHTML = '';
          emptyState.classList.remove('d-none');
          return;
        }

        emptyState.classList.add('d-none');
        
        employeesTableBody.innerHTML = filteredEmployees.map(employee => `
          <tr>
            <td>
              ${employee.profile_picture ? 
                `<img src="${employee.profile_picture}" alt="${employee.full_name}" class="employee-avatar" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiBmaWxsPSIjNkM3NTg0IiByeD0iMjAiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEyIDEyQzE0LjIwOTEgMTIgMTYgMTAuMjA5MSAxNiA4QzE2IDUuNzkwODYgMTQuMjA5MSA0IDEyIDRDOS43OTA4NiA0IDggNS43OTA4NiA4IDhDOCAxMC4yMDkxIDkuNzkwODYgMTIgMTIgMTJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTQuNUM3Ljk5MTg3IDE0LjUgNC42NjY2NyAxNy4yMzMzIDQgMjBIMjBDMTkuMzMzMyAxNy4yMzMzIDE2LjAwODEgMTQuNSAxMiAxNC41WiIgZmlsbD0id2hpdGUiLz4KPC9zdmc+Cjwvc3ZnPgo='">` :
                `<div class="employee-avatar bg-secondary d-flex align-items-center justify-content-center text-white">
                  <i class="bi bi-person"></i>
                </div>`
              }
            </td>
            <td>
              <strong>${employee.employee_id}</strong>
            </td>
            <td>
              <div class="fw-semibold">${employee.full_name}</div>
              <small class="text-muted">${employee.email}</small>
            </td>
            <td>
              <span class="badge bg-light text-dark">${formatRole(employee.role)}</span>
            </td>
            <td>${employee.department || '-'}</td>
            <td>${employee.phone || '-'}</td>
            <td>
              <span class="status-badge ${employee.is_active ? 'status-active' : 'status-inactive'}">
                ${employee.is_active ? 'Active' : 'Inactive'}
              </span>
            </td>
            <td>${employee.joining_date ? formatDate(employee.joining_date) : '-'}</td>
            <td>
                <div class="btn-group btn-group-sm gap-2">

                    <button type="button" class="btn btn-outline-success action-btn" onclick="editEmployee(${employee.id})" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-outline-warning action-btn" onclick="toggleStatus(${employee.id}, ${employee.is_active})" title="${employee.is_active ? 'Deactivate' : 'Activate'}">
                        <i class="bi ${employee.is_active ? 'bi-person-x' : 'bi-person-check'}"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger action-btn" onclick="deleteEmployee(${employee.id}, '${employee.full_name}')" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
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

      function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-IN');
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

      // Action functions (to be implemented)
    //  window.viewEmployee = function(employeeId) {
     //   alert('View employee: ' + employeeId);
        // Will implement in next step
    //  };

    //  window.editEmployee = function(employeeId) {
    //    alert('Edit employee: ' + employeeId);
        // Will implement in next step
   //   };


    });
    // Add these functions to the existing view_employees.js


window.editEmployee = function(employeeId) {
    console.log('Edit clicked for ID:', employeeId);
    const editUrl = `edit_employee?id=${employeeId}`;
    console.log('Attempting to navigate to:', editUrl);
    window.location.href = editUrl;
};
// Simple but reliable version - PAGE RELOAD
window.deleteEmployee = function(employeeId, employeeName) {
    if (confirm(`Are you sure you want to delete employee "${employeeName}"? This action cannot be undone.`)) {
        const formData = new FormData();
        formData.append('action', 'delete_employee');
        formData.append('id', employeeId);

        fetch('api/employee_api', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('success', data.message);
                // Reload the entire page after 1.5 seconds
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showMessage('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('error', 'Failed to delete employee');
        });
    }
};

window.toggleStatus = function(employeeId, currentStatus) {
    if (confirm(`Are you sure you want to ${currentStatus ? 'deactivate' : 'activate'} this employee?`)) {
        const formData = new FormData();
        formData.append('action', 'toggle_status');
        formData.append('id', employeeId);
        formData.append('status', currentStatus ? 0 : 1);

        fetch('api/employee_api', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('success', data.message);
                // Reload the entire page after 1.5 seconds
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showMessage('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('error', 'Failed to update employee status');
        });
    }
};


  </script>
  
    <!-- Custom JavaScript -->
    <script src="css&js/script.js"></script>
</body>
</html>