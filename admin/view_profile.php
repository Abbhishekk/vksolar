<?php
// admin/view_profile.php
require_once 'connect/auth_middleware.php';
$auth->requireAuth();

$title = "view_profile";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require('include/head.php'); ?>
  <title>My Profile - VK Solar</title>
  <style>
    .profile-header {
        background: linear-gradient(135deg, #2e8b57, #3cb371);
        color: white;
        padding: 2rem;
        border-radius: 10px;
        margin-bottom: 2rem;
    }
    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
    }
    .profile-stats {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
    }
    .stat-item {
        text-align: center;
        padding: 1rem;
    }
    .stat-number {
        font-size: 1.5rem;
        font-weight: bold;
        color: #2e8b57;
    }
    .stat-label {
        font-size: 0.875rem;
        color: #6c757d;
    }
    .info-card {
        border-left: 4px solid #2e8b57;
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
            <h4 class="mb-1">My Profile</h4>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="./">Dashboard</a></li>
                <li class="breadcrumb-item active">My Profile</li>
              </ol>
            </nav>
          </div>
          <a href="edit_profile" class="btn btn-success">
            <i class="bi bi-pencil me-2"></i>Edit Profile
          </a>
        </div>

        <!-- Message Alert -->
        <div id="messageAlert" class="alert d-none">
          <i id="messageIcon" class="bi me-2"></i>
          <span id="messageText"></span>
        </div>

        <div id="loadingState" class="text-center py-4">
          <div class="spinner-border text-success" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2 text-muted">Loading profile...</p>
        </div>

        <div id="profileContent" class="d-none">
          <!-- Profile content will be loaded here -->
        </div>
      </div>
    </main>
  </div>


  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const profileContent = document.getElementById('profileContent');
      const loadingState = document.getElementById('loadingState');
      const messageAlert = document.getElementById('messageAlert');

      // Load profile data
      loadProfile();

      function loadProfile() {
        fetch('api/user_api?action=get_my_profile')
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              renderProfile(data.data);
              loadingState.style.display = 'none';
              profileContent.classList.remove('d-none');
            } else {
              showMessage('error', data.message);
              loadingState.innerHTML = '<p class="text-danger">Failed to load profile</p>';
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showMessage('error', 'Failed to load profile');
            loadingState.innerHTML = '<p class="text-danger">Failed to load profile</p>';
          });
      }

      function renderProfile(profile) {
        const html = `
          <div class="profile-header">
            <div class="row align-items-center">
              <div class="col-auto">
                <div class="profile-avatar bg-light d-flex align-items-center justify-content-center">
                  <i class="bi bi-person-fill fs-1 text-muted"></i>
                </div>
              </div>
              <div class="col">
                <h3 class="mb-1">${profile.full_name}</h3>
                <p class="mb-1">
                  <span class="badge bg-light text-dark">${profile.role ? formatRole(profile.role) : ''}</span>
                </p>
                <p class="mb-0">
                  <i class="bi bi-envelope me-2"></i>${profile.email}
                  ${profile.phone ? `<i class="bi bi-telephone ms-3 me-2"></i>${profile.phone}` : ''}
                </p>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-8">
              <div class="card mb-4 info-card">
                <div class="card-header bg-light">
                  <h5 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i>Personal Information</h5>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-6">
                      <table class="table table-borderless">
                        <tr>
                          <td><strong>Username:</strong></td>
                          <td>${profile.username}</td>
                        </tr>
                        <tr>
                          <td><strong>Email:</strong></td>
                          <td>${profile.email}</td>
                        </tr>
                        <tr>
                          <td><strong>Phone:</strong></td>
                          <td>${profile.phone || 'Not provided'}</td>
                        </tr>
                      </table>
                    </div>
                    <div class="col-md-6">
                      <table class="table table-borderless">
                        <tr>
                          <td><strong>Role:</strong></td>
                          <td>${profile.role ? formatRole(profile.role) : ''}</td>
                        </tr>
                        <tr>
                          <td><strong>Status:</strong></td>
                          <td><span class="badge ${profile.is_active ? 'bg-success' : 'bg-danger'}">${profile.is_active ? 'Active' : 'Inactive'}</span></td>
                        </tr>
                        <tr>
                          <td><strong>Last Login:</strong></td>
                          <td>${profile.last_login ? formatDateTime(profile.last_login) : 'Never'}</td>
                        </tr>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="card info-card">
                <div class="card-header bg-light">
                  <h5 class="card-title mb-0"><i class="bi bi-clock me-2"></i>Account Information</h5>
                </div>
                <div class="card-body">
                  <table class="table table-borderless">
                    <tr>
                      <td><strong>Member since:</strong></td>
                      <td>${formatDateTime(profile.created_at)}</td>
                    </tr>
                    <tr>
                      <td><strong>Account created by:</strong></td>
                      <td>${profile.created_by_name || 'System'}</td>
                    </tr>
                    <tr>
                      <td><strong>Last updated:</strong></td>
                      <td>${formatDateTime(profile.updated_at)}</td>
                    </tr>
                  </table>
                </div>
              </div>

              <div class="card mt-4 info-card">
                <div class="card-header bg-light">
                  <h5 class="card-title mb-0"><i class="bi bi-shield-check me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                  <div class="d-grid gap-2">
                    <a href="edit_profile" class="btn btn-outline-success">
                      <i class="bi bi-pencil me-2"></i>Edit Profile
                    </a>
                    <button class="btn btn-outline-primary" onclick="changePassword()">
                      <i class="bi bi-key me-2"></i>Change Password
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        `;

        profileContent.innerHTML = html;
      }

      function changePassword() {
        const newPassword = prompt('Enter new password (minimum 6 characters):');
        if (newPassword && newPassword.length >= 6) {
          const formData = new FormData();
          formData.append('action', 'change_my_password');
          formData.append('new_password', newPassword);

          fetch('api/user_api', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showMessage('success', data.message);
            } else {
              showMessage('error', data.message);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showMessage('error', 'Failed to change password');
          });
        } else if (newPassword) {
          showMessage('error', 'Password must be at least 6 characters long');
        }
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