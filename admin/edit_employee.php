<?php
// admin/edit_employee.php
require_once __DIR__ . '/connect/auth_middleware.php';
$auth->requireAuth();
$auth->requireAnyRole(['super_admin', 'admin']);

$title = "edit_employee";

// Get employee ID from URL
$employee_id = $_GET['id'] ?? 0;
if ($employee_id <= 0) {
    header('Location: view_employees');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require('include/head.php'); ?>
  <title>Edit Employee - VK Solar</title>
  <style>
    .file-upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 10px;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f8f9fa;
        min-height: 200px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .file-upload-area:hover {
        border-color: #2e8b57;
        background: #f0f9f4;
    }
    .file-upload-area.dragover {
        border-color: #2e8b57;
        background: #e8f5e8;
    }
    .image-preview {
        display: none;
        margin-top: 1rem;
        text-align: center;
    }
    .image-preview img {
        max-width: 150px;
        max-height: 150px;
        border-radius: 8px;
        border: 2px solid #2e8b57;
    }
    .remove-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        background: white;
        border: 1px solid #dc3545;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 12px;
    }
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
    .file-requirements {
        font-size: 0.8rem;
        color: #dc3545;
        margin-top: 0.5rem;
    }
    .upload-icon {
        color: #2e8b57;
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
    .current-image {
        max-width: 150px;
        max-height: 150px;
        border-radius: 8px;
        border: 2px solid #2e8b57;
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
                <li class="breadcrumb-item"><a href="view_employees">Employees</a></li>
                <li class="breadcrumb-item active">Edit Employee</li>
              </ol>
            </nav>
          </div>
          <a href="view_employees" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Employees
          </a>
        </div>

        <!-- Message Alert -->
        <div id="messageAlert" class="alert d-none">
          <i id="messageIcon" class="bi me-2"></i>
          <span id="messageText"></span>
        </div>

        <div class="card">
          <div class="card-header bg-warning text-dark">
            <h5 class="card-title mb-0"><i class="bi bi-person-gear me-2"></i>Edit Employee</h5>
          </div>
          <div class="card-body">
            <div id="loadingState" class="text-center py-4">
              <div class="spinner-border text-warning" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2 text-muted">Loading employee data...</p>
            </div>

            <form id="employeeForm" method="POST" enctype="multipart/form-data" class="d-none">
              <input type="hidden" id="employee_id" name="id" value="<?php echo $employee_id; ?>">
              
              <!-- Profile Image Section -->
              <div class="form-section">
                <h6 class="section-title"><i class="bi bi-image me-2"></i>Profile Picture</h6>
                <div class="row">
                  <div class="col-md-6">
                    <label class="form-label">Current Photo</label>
                    <div class="mb-3" id="currentImageContainer">
                      <!-- Current image will be loaded here -->
                    </div>
                    <label class="form-label">Update Photo (Optional)</label>
                    <div class="file-upload-area" id="uploadArea">
                      <div class="upload-icon">
                        <i class="bi bi-cloud-arrow-up"></i>
                      </div>
                      <h6 class="text-secondary mb-2">Click or drag image here to upload</h6>
                      <p class="text-muted small">Supported: JPG, PNG | Max: 1MB</p>
                      <input type="file" class="d-none" id="profile_picture" name="profile_picture" accept="image/jpeg, image/png">
                      
                      <div class="image-preview" id="imagePreview">
                        <div class="position-relative d-inline-block">
                          <img src="" alt="Profile Preview" class="img-fluid">
                          <button type="button" class="remove-btn" id="removeBtn">
                            <i class="bi bi-x-lg"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                    <div class="file-requirements" id="fileRequirements"></div>
                  </div>
                </div>
              </div>

              <!-- Personal Information Section -->
              <div class="form-section">
                <h6 class="section-title"><i class="bi bi-person-vcard me-2"></i>Personal Information</h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label for="full_name" class="form-label">Full Name *</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                  </div>
                  <div class="col-md-6">
                    <label for="employee_id" class="form-label">Employee ID *</label>
                    <input type="text" class="form-control" id="employee_id_display" name="employee_id" required readonly>
                    <small class="text-muted">Auto-generated unique identifier</small>
                  </div>
                  <div class="col-md-6">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                  </div>
                  <div class="col-md-6">
                    <label for="phone" class="form-label">Phone Number *</label>
                    <input type="tel" class="form-control" id="phone" name="phone" required>
                  </div>
                </div>
              </div>

              <!-- Employment Details Section -->
              <div class="form-section">
                <h6 class="section-title"><i class="bi bi-briefcase me-2"></i>Employment Details</h6>
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
                    <label for="department" class="form-label">Department</label>
                    <input type="text" class="form-control" id="department" name="department">
                  </div>
                  <div class="col-md-6">
                    <label for="position" class="form-label">Position/Designation</label>
                    <input type="text" class="form-control" id="position" name="position">
                  </div>
                  <div class="col-md-6">
                    <label for="salary" class="form-label">Salary (₹)</label>
                    <input type="number" class="form-control" id="salary" name="salary" step="0.01">
                  </div>
                  <div class="col-md-6">
                    <label for="joining_date" class="form-label">Joining Date</label>
                    <input type="date" class="form-control" id="joining_date" name="joining_date">
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

              <!-- Address Information Section -->
              <div class="form-section">
                <h6 class="section-title"><i class="bi bi-geo-alt me-2"></i>Address Information</h6>
                <div class="row g-3">
                  <div class="col-12">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                  </div>
                  <div class="col-md-4">
                    <label for="city" class="form-label">City</label>
                    <input type="text" class="form-control" id="city" name="city">
                  </div>
                  <div class="col-md-4">
                    <label for="state" class="form-label">State</label>
                    <input type="text" class="form-control" id="state" name="state">
                  </div>
                  <div class="col-md-4">
                    <label for="pincode" class="form-label">Pincode</label>
                    <input type="text" class="form-control" id="pincode" name="pincode">
                  </div>
                </div>
              </div>

              <!-- Emergency Contact Section -->
              <div class="form-section">
                <h6 class="section-title"><i class="bi bi-telephone me-2"></i>Emergency Contact</h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label for="emergency_contact_name" class="form-label">Contact Person Name</label>
                    <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name">
                  </div>
                  <div class="col-md-6">
                    <label for="emergency_contact" class="form-label">Emergency Contact Number</label>
                    <input type="tel" class="form-control" id="emergency_contact" name="emergency_contact">
                  </div>
                </div>
              </div>

              <!-- Submit Button -->
              <div class="text-center mt-4">
                <button type="submit" class="btn btn-warning px-5">
                  <i class="bi bi-person-check me-2"></i>Update Employee
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
      const form = document.getElementById('employeeForm');
      const messageAlert = document.getElementById('messageAlert');
      const uploadArea = document.getElementById('uploadArea');
      const fileInput = document.getElementById('profile_picture');
      const imagePreview = document.getElementById('imagePreview');
      const previewImg = imagePreview.querySelector('img');
      const removeBtn = document.getElementById('removeBtn');
      const fileRequirements = document.getElementById('fileRequirements');
      const loadingState = document.getElementById('loadingState');
      const employeeId = <?php echo $employee_id; ?>;

      // Load employee data
      loadEmployeeData();

      // File upload functionality (same as add_employee)
      uploadArea.addEventListener('click', () => fileInput.click());
      
      uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
      });
      
      uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
      });
      
      uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
          fileInput.files = e.dataTransfer.files;
          handleFileSelect(e.dataTransfer.files[0]);
        }
      });

      fileInput.addEventListener('change', (e) => {
        if (e.target.files.length) {
          handleFileSelect(e.target.files[0]);
        }
      });

      removeBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        fileInput.value = '';
        imagePreview.style.display = 'none';
        fileRequirements.textContent = '';
      });

      function handleFileSelect(file) {
        const validTypes = ['image/jpeg', 'image/png'];
        if (!validTypes.includes(file.type)) {
          fileRequirements.textContent = 'Error: Only JPG and PNG images are allowed.';
          fileInput.value = '';
          return;
        }

        if (file.size > 1048576) {
          fileRequirements.textContent = 'Error: Image size must be less than 1MB.';
          fileInput.value = '';
          return;
        }

        fileRequirements.textContent = '';

        const reader = new FileReader();
        reader.onload = (e) => {
          previewImg.src = e.target.result;
          imagePreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      }

      function loadEmployeeData() {
        fetch(`api/employee_api?action=get_employee&id=${employeeId}`)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              populateForm(data.data);
              loadingState.style.display = 'none';
              form.classList.remove('d-none');
            } else {
              showMessage('error', data.message);
              loadingState.innerHTML = '<p class="text-danger">Failed to load employee data</p>';
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showMessage('error', 'Failed to load employee data');
            loadingState.innerHTML = '<p class="text-danger">Failed to load employee data</p>';
          });
      }

      function populateForm(employee) {
        document.getElementById('employee_id_display').value = employee.employee_id;
        document.getElementById('full_name').value = employee.full_name;
        document.getElementById('email').value = employee.email;
        document.getElementById('phone').value = employee.phone;
        document.getElementById('role').value = employee.role;
        document.getElementById('department').value = employee.department || '';
        document.getElementById('position').value = employee.position || '';
        document.getElementById('salary').value = employee.salary || '';
        document.getElementById('joining_date').value = employee.joining_date || '';
        document.getElementById('is_active').value = employee.is_active;
        document.getElementById('address').value = employee.address || '';
        document.getElementById('city').value = employee.city || '';
        document.getElementById('state').value = employee.state || '';
        document.getElementById('pincode').value = employee.pincode || '';
        document.getElementById('emergency_contact_name').value = employee.emergency_contact_name || '';
        document.getElementById('emergency_contact').value = employee.emergency_contact || '';

        // Display current profile picture
        if (employee.profile_picture) {
          const currentImageContainer = document.getElementById('currentImageContainer');
          currentImageContainer.innerHTML = `
            <img src="${employee.profile_picture}" alt="${employee.full_name}" class="current-image">
            <div class="mt-2">
              <small class="text-muted">Current profile picture</small>
            </div>
          `;
        }
      }

      // Form submission
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        formData.append('action', 'update_employee');

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Updating...';
        submitBtn.disabled = true;

        fetch('api/employee_api', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showMessage('success', data.message);
            // Reload employee data to show updated information
            setTimeout(() => loadEmployeeData(), 1000);
          } else {
            showMessage('error', data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showMessage('error', 'An error occurred while updating employee');
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
  
    <!-- Custom JavaScript -->
    <script src="css&js/script.js"></script>
</body>
</html>