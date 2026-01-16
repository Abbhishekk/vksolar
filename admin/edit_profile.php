<?php
// admin/edit_profile.php
require_once 'connect/auth_middleware.php';
$auth->requireAuth();

$title = "edit_profile";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require('include/head.php'); ?>
  <title>Edit Profile - VK Solar</title>
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
            <h4 class="mb-1">My Profile</h4>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="./">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="view_profile">My Profile</a></li>
                <li class="breadcrumb-item active">Edit Profile</li>
              </ol>
            </nav>
          </div>
          <a href="view_profile" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Profile
          </a>
        </div>

        <!-- Message Alert -->
        <div id="messageAlert" class="alert d-none">
          <i id="messageIcon" class="bi me-2"></i>
          <span id="messageText"></span>
        </div>

        <div class="card">
          <div class="card-header bg-warning text-dark">
            <h5 class="card-title mb-0"><i class="bi bi-person-gear me-2"></i>Edit Profile</h5>
          </div>
          <div class="card-body">
            <div id="loadingState" class="text-center py-4">
              <div class="spinner-border text-warning" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2 text-muted">Loading profile data...</p>
            </div>

            <form id="profileForm" method="POST" class="d-none">
              <!-- Personal Information Section -->
              <div class="form-section">
                <h6 class="section-title"><i class="bi bi-person-vcard me-2"></i>Personal Information</h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" readonly>
                    <div class="form-text">Username cannot be changed</div>
                  </div>
                  <div class="col-md-6">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                  </div>
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

              <!-- Change Password Section -->
              <div class="form-section">
                <h6 class="section-title"><i class="bi bi-shield-lock me-2"></i>Change Password</h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="current_password" name="current_password">
                    <div class="form-text">Required to change password</div>
                  </div>
                  <div class="col-md-6">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password">
                    <div class="password-strength" id="passwordStrength"></div>
                    <div class="form-text">Minimum 6 characters</div>
                  </div>
                  <div class="col-md-6">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    <div class="form-text" id="passwordMatch"></div>
                  </div>
                </div>
                <small class="text-muted">Leave password fields blank if you don't want to change password</small>
              </div>

              <!-- Submit Button -->
              <div class="text-center mt-4">
                <button type="submit" class="btn btn-warning px-5">
                  <i class="bi bi-person-check me-2"></i>Update Profile
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


  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('profileForm');
      const messageAlert = document.getElementById('messageAlert');
      const loadingState = document.getElementById('loadingState');
      const currentPasswordInput = document.getElementById('current_password');
      const newPasswordInput = document.getElementById('new_password');
      const confirmPasswordInput = document.getElementById('confirm_password');
      const passwordStrength = document.getElementById('passwordStrength');
      const passwordMatch = document.getElementById('passwordMatch');

      // Load profile data
      loadProfile();

      // Password strength indicator
      newPasswordInput.addEventListener('input', function() {
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
        const password = newPasswordInput.value;
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

      function loadProfile() {
        fetch('api/user_api?action=get_my_profile')
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              populateForm(data.data);
              loadingState.style.display = 'none';
              form.classList.remove('d-none');
            } else {
              showMessage('error', data.message);
              loadingState.innerHTML = '<p class="text-danger">Failed to load profile data</p>';
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showMessage('error', 'Failed to load profile data');
            loadingState.innerHTML = '<p class="text-danger">Failed to load profile data</p>';
          });
      }

      function populateForm(profile) {
        document.getElementById('username').value = profile.username;
        document.getElementById('email').value = profile.email;
        document.getElementById('full_name').value = profile.full_name;
        document.getElementById('phone').value = profile.phone || '';
      }

      // Form submission
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate new passwords if provided
        if (newPasswordInput.value) {
          if (!currentPasswordInput.value) {
            showMessage('error', 'Current password is required to change password');
            return;
          }
          
          if (newPasswordInput.value !== confirmPasswordInput.value) {
            showMessage('error', 'New passwords do not match');
            return;
          }

          if (newPasswordInput.value.length < 6) {
            showMessage('error', 'New password must be at least 6 characters long');
            return;
          }
        }

        const formData = new FormData(form);
        formData.append('action', 'update_my_profile');

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
            // Redirect to view profile after 2 seconds
            setTimeout(() => {
              window.location.href = 'view_profile';
            }, 2000);
          } else {
            showMessage('error', data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showMessage('error', 'An error occurred while updating profile');
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