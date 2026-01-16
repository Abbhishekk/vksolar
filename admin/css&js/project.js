

      // --- Project Form JavaScript ---
      document.addEventListener('DOMContentLoaded', function() {
        const projectForm = document.getElementById('projectForm');
        if (!projectForm) return;
        
        const uploadArea = document.getElementById('uploadArea');
        const projectImage = document.getElementById('projectImage');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = imagePreview.querySelector('img');
        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');
        const successMessage = document.getElementById('successMessage');
        const errorMessage = document.getElementById('errorMessage');
        const progressBar = document.getElementById('progressBar');
        const progressFill = document.getElementById('progressFill');
        const fileInfo = document.getElementById('fileInfo');
        const removeBtn = document.getElementById('removeBtn');
        const submitBtn = document.getElementById('submitBtn');
        const projectsGrid = document.getElementById('projectsGrid');

        // File upload area click event
        uploadArea.addEventListener('click', function(e) {
          if (e.target !== removeBtn && !removeBtn.contains(e.target)) {
            projectImage.click();
          }
        });

        // Drag and drop functionality
        uploadArea.addEventListener('dragover', function(e) {
          e.preventDefault();
          uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', function() {
          uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', function(e) {
          e.preventDefault();
          uploadArea.classList.remove('dragover');
          
          if (e.dataTransfer.files.length) {
            projectImage.files = e.dataTransfer.files;
            handleFileSelect(e.dataTransfer.files[0]);
          }
        });

        // File input change event
        projectImage.addEventListener('change', function(e) {
          if (e.target.files.length) {
            handleFileSelect(e.target.files[0]);
          }
        });

        // Remove image preview
        removeBtn.addEventListener('click', function(e) {
          e.stopPropagation();
          imagePreview.style.display = 'none';
          projectImage.value = '';
          progressBar.style.display = 'none';
          fileInfo.style.display = 'none';
        });

        // Handle file selection and preview
        function handleFileSelect(file) {
          // Validate file type
          const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml'];
          if (!validTypes.includes(file.type)) {
            showError('Please select a valid image file (JPEG, PNG, GIF, SVG)');
            return;
          }

          // Validate file size (5MB max)
          if (file.size > 5 * 1024 * 1024) {
            showError('File size must be less than 5MB');
            return;
          }

          // Show file info
          fileInfo.textContent = ${file.name} (${formatFileSize(file.size)});
          fileInfo.style.display = 'block';

          // Show progress bar and simulate upload
          progressBar.style.display = 'block';
          simulateUpload();

          // Create preview
          const reader = new FileReader();
          reader.onload = function(e) {
            previewImg.src = e.target.result;
            imagePreview.style.display = 'block';
            hideAlerts();
          };
          reader.readAsDataURL(file);
        }

        // Simulate file upload progress
        function simulateUpload() {
          let width = 0;
          const interval = setInterval(() => {
            if (width >= 100) {
              clearInterval(interval);
            } else {
              width += Math.random() * 15;
              if (width > 100) width = 100;
              progressFill.style.width = width + '%';
            }
          }, 200);
        }

        // Format file size
        function formatFileSize(bytes) {
          if (bytes === 0) return '0 Bytes';
          const k = 1024;
          const sizes = ['Bytes', 'KB', 'MB', 'GB'];
          const i = Math.floor(Math.log(bytes) / Math.log(k));
          return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Form submission - NOTE: This part will not work without the rest of the page structure
        // (like a 'projectStatus' dropdown and a 'projectsGrid' element).
        // I've kept it as is from your original file.
        projectForm.addEventListener('submit', function(e) {
          e.preventDefault();
          
          const projectName = document.getElementById('projectName').value.trim();
          const projectSummary = document.getElementById('projectSummary').value.trim();
          
          // Assuming 'projectStatus' and 'projectsGrid' would exist elsewhere
          // For now, let's just show success without adding to a grid.
          // const projectStatus = document.getElementById('projectStatus').value; 
          
          if (!projectName || !projectSummary) {
            showError('Please fill in all required fields');
            return;
          }

          // Disable submit button and show loading state
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<i class="bi bi-arrow-repeat spinner me-2"></i> Adding Project...';

          // Simulate form submission
          setTimeout(function() {
            showSuccess('Project added successfully!');
            
            // Reset form
            projectForm.reset();
            projectImage.value = '';
            imagePreview.style.display = 'none';
            progressBar.style.display = 'none';
            fileInfo.style.display = 'none';
            
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-plus-circle me-2"></i> Add Project';
          }, 1500);
        });

        // Alert functions
        function showSuccess(message) {
          successMessage.textContent = message;
          successAlert.classList.remove('d-none');
          errorAlert.classList.add('d-none');
          
          // Auto hide success message after 5 seconds
          setTimeout(() => {
            successAlert.classList.add('d-none');
          }, 5000);
        }

        function showError(message) {
          errorMessage.textContent = message;
          errorAlert.classList.remove('d-none');
          successAlert.classList.add('d-none');
        }

        function hideAlerts() {
          successAlert.classList.add('d-none');
          errorAlert.classList.add('d-none');
        }
      });

      
