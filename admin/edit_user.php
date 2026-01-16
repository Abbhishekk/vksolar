<?php
// admin/edit_user.php
require_once 'connect/auth_middleware.php';
$auth->requireAuth();
$auth->requireAnyRole(['super_admin', 'admin']);
$auth->checkPermission('user_management', 'edit');

$title = "edit_user";

// Get user ID from URL
$user_id = $_GET['id'] ?? 0;
if ($user_id <= 0) {
    header('Location: view_users');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require('include/head.php'); ?>
  <title>Edit User - VK Solar</title>
  <style>
    .form-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .section-title {
        color: #2e8b57;
        border-bottom: 2px solid #2e8b57;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }
    .password-strength {
        height: 5px;
        border-radius: 5px;
        margin-top: 5px;
        transition: all 0.3s ease;
    }
    .strength-weak { background: #dc3545; width: 25%; }
    .strength-fair { background: #fd7e14; width: 50%; }
    .strength-good { background: #ffc107; width: 75%; }
    .strength-strong { background: #198754; width: 100%; }
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
                <li class="breadcrumb-item active">Edit User</li>
              </ol>
            </nav>
          </div>
          <a href="view_users" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Users
          </a>
        </div>

        <!-- Message Alert -->
        <div id="messageAlert" class="alert d-none">
          <i id="messageIcon" class="bi me-2"></i>
          <span id="messageText"></span>
        </div>

        <div class="card">
          <div class="card-header bg-warning text-dark">
            <h5 class="card-title mb-0"><i class="bi bi-person-gear me-2"></i>Edit User</h5>
          </div>
          <div class="card-body">
            <div id="loadingState" class="text-center py-4">
              <div class="spinner-border text-warning" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2 text-muted">Loading user data...</p>
            </div>

            <form id="userForm" method="POST" class="d-none">
              <input type="hidden" id="user_id" name="id" value="<?php echo $user_id; ?>">
              
              <!-- Account Information Section -->
              <div class="form-section">
                <h6 class="section-title"><i class="bi bi-person-vcard me-2"></i>Account Information</h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label for="username" class="form-label">Username *</label>
                    <input type="text" class="form-control" id="username" name="username" required readonly>
                    <div class="form-text">Username cannot be changed</div>
                  </div>
                  <div class="col-md-6">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                  </div>
                  <div class="col-md-6">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="password" name="password">
                    <div class="password-strength" id="passwordStrength"></div>
                    <div class="form-text">Leave blank to keep current password</div>
                  </div>
                  <div class="col-md-6">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    <div class="form-text" id="passwordMatch"></div>
                  </div>
                </div>
              </div>

              <!-- Personal Information Section -->
              <div class="form-section">
                <h6 class="section-title"><i class="bi bi-person-lines-fill me-2"></i>Personal Information</h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label for="full_name" class="form-label">Full Name *</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                  </div>
                  <div class="col-md-6">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone">
                  </div>
                </div>
              </div>

              <!-- Role & Status Section -->
              <div class="form-section">
                <h6 class="section-title"><i class="bi bi-shield-check me-2"></i>Role & Status</h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label for="role" class="form-label">System Role *</label>
                    <select class="form-select" id="role" name="role" required>
                      <option value="">Select Role</option>
                      <option value="office_staff">Office Staff</option>
                      <option value="sales_marketing">Sales & Marketing</option>
                      <option value="warehouse_staff">Warehouse Staff</option>
                      <?php if ($auth->getCurrentRole() === 'super_admin'): ?>
                      <option value="admin">Admin</option>
                      <?php endif; ?>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label for="is_active" class="form-label">Status</label>
                    <select class="form-select" id="is_active" name="is_active">
                      <option value="1">Active</option>
                      <option value="0">Inactive</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Submit Button -->
              <div class="text-center mt-4">
                <button type="submit" class="btn btn-warning px-5">
                  <i class="bi bi-person-check me-2"></i>Update User
                </button>
                <button type="button" class="btn btn-outline-secondary ms-2" onclick="window.history.back()">
                  <i class="bi bi-x-circle me-2"></i>Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('userForm');
      const messageAlert = document.getElementById('messageAlert');
      const loadingState = document.getElementById('loadingState');
      const passwordInput = document.getElementById('password');
      const confirmPasswordInput = document.getElementById('confirm_password');
      const passwordStrength = document.getElementById('passwordStrength');
      const passwordMatch = document.getElementById('passwordMatch');
      const userId = <?php echo $user_id; ?>;

      // Load user data
      loadUserData();

      // Password strength indicator
      passwordInput.addEventListener('input', function() {
        const password = this.value;
        if (password === '') {
          passwordStrength.style.display = 'none';
          return;
        }
        
        passwordStrength.style.display = 'block';
        let strength = 'weak';
        
        if (password.length >= 8) strength = 'fair';
        if (password.length >= 10 && /[A-Z]/.test(password) && /[0-9]/.test(password)) strength = 'good';
        if (password.length >= 12 && /[A-Z]/.test(password) && /[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password)) strength = 'strong';
        
        passwordStrength.className = 'password-strength strength-' + strength;
      });

      // Password match indicator
      confirmPasswordInput.addEventListener('input', function() {
        const password = passwordInput.value;
        const confirm = this.value;
        
        if (confirm === '') {
          passwordMatch.textContent = '';
          passwordMatch.className = 'form-text';
        } else if (password === confirm) {
          passwordMatch.textContent = 'Passwords match';
          passwordMatch.className = 'form-text text-success';
        } else {
          passwordMatch.textContent = 'Passwords do not match';
          passwordMatch.className = 'form-text text-danger';
        }
      });

      function loadUserData() {
        fetch(`api/user_api?action=get_user&id=${userId}`)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              populateForm(data.data);
              loadingState.style.display = 'none';
              form.classList.remove('d-none');
            } else {
              showMessage('error', data.message);
              loadingState.innerHTML = '<p class="text-danger">Failed to load user data</p>';
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showMessage('error', 'Failed to load user data');
            loadingState.innerHTML = '<p class="text-danger">Failed to load user data</p>';
          });
      }

      function populateForm(user) {
        document.getElementById('username').value = user.username;
        document.getElementById('email').value = user.email;
        document.getElementById('full_name').value = user.full_name;
        document.getElementById('phone').value = user.phone || '';
        document.getElementById('role').value = user.role;
        document.getElementById('is_active').value = user.is_active;
      }

      // Form submission
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate passwords match if provided
        if (passwordInput.value && passwordInput.value !== confirmPasswordInput.value) {
          showMessage('error', 'Passwords do not match');
          return;
        }

        // Validate password length if provided
        if (passwordInput.value && passwordInput.value.length < 6) {
          showMessage('error', 'Password must be at least 6 characters long');
          return;
        }

        const formData = new FormData(form);
        formData.append('action', 'update_user');

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Updating...';
        submitBtn.disabled = true;

        fetch('api/user_api', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showMessage('success', data.message);
            // Reload user data to show updated information
            setTimeout(() => loadUserData(), 1000);
          } else {
            showMessage('error', data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showMessage('error', 'An error occurred while updating user');
        })
        .finally(() => {
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
        });
      });

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
  </script>
</body>
</html>