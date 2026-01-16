<?php
$title = "projectForm";
require('../../database/connection.php');
require('../../database/function.php');

$conn = new connection();
$db = $conn->my_connect();
$fun = new fun($db);

if (isset($_POST['submit'])) {
    $projectname = $_POST['project_name'];
    $projectsummary = $_POST['project_summary'];

    // Image upload
    if (!empty($_FILES['project_image']['name'])) {
        $projectimg_name = basename($_FILES['project_image']['name']);
        $targetDir = "../../database/db_images/";
        $img_location = $targetDir . $projectimg_name;
        $tempName = $_FILES['project_image']['tmp_name'];
        $img_type = pathinfo($img_location, PATHINFO_EXTENSION);

        $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array(strtolower($img_type), $allowedTypes)) {
            if (move_uploaded_file($tempName, $img_location)) {
                $result = $fun->projectInsert($projectname, $projectsummary, $projectimg_name, $img_location, $img_type);
                if ($result) {
                    header("location:view-project.php");
                    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.getElementById('successAlert');
            successAlert.classList.remove('d-none');
            successAlert.classList.add('show');
            setTimeout(() => {
                successAlert.classList.remove('show');
            }, 3000);
        });
    </script>";
                } else {
                    // ❌ Database insertion failed
                    echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const errorAlert = document.getElementById('errorAlert');
                            const errorMessage = document.getElementById('errorMessage');
                            errorMessage.textContent = 'Database insertion failed. Please try again.';
                            errorAlert.classList.remove('d-none');
                            errorAlert.classList.add('show');
                            setTimeout(() => { errorAlert.classList.remove('show'); }, 4000);
                        });
                    </script>";
                }
            } else {
                // ❌ File upload failed
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const errorAlert = document.getElementById('errorAlert');
                        const errorMessage = document.getElementById('errorMessage');
                        errorMessage.textContent = 'File upload failed. Please check file permissions.';
                        errorAlert.classList.remove('d-none');
                        errorAlert.classList.add('show');
                        setTimeout(() => { errorAlert.classList.remove('show'); }, 4000);
                    });
                </script>";
            }
        } else {
            // ❌ Invalid image type
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    const errorAlert = document.getElementById('errorAlert');
                    const errorMessage = document.getElementById('errorMessage');
                    errorMessage.textContent = 'Invalid image type! Please upload JPG, PNG, GIF, or WEBP.';
                    errorAlert.classList.remove('d-none');
                    errorAlert.classList.add('show');
                    setTimeout(() => { errorAlert.classList.remove('show'); }, 4000);
                });
            </script>";
        }
    } else {
        // ❌ No image selected
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                const errorAlert = document.getElementById('errorAlert');
                const errorMessage = document.getElementById('errorMessage');
                errorMessage.textContent = 'Please select an image to upload.';
                errorAlert.classList.remove('d-none');
                errorAlert.classList.add('show');
                setTimeout(() => { errorAlert.classList.remove('show'); }, 4000);
            });
        </script>";
    }
}
?>





<!DOCTYPE html>
<html lang="en">

<head>
    <?php require('../include/head.php') ?>
</head>

<body>
    <!-- Sidebar -->
    <?php require('../pages/sidebar.php') ?>

    <!-- Main Content -->
    <div id="main-content">
        <div class="sidebar-overlay"></div>

        <!-- Fixed Header -->
        <?php require('../include/navbar.php') ?>

        <!-- Main Dashboard Content -->
        <main>
            <div class="row g-4">
                <!-- project section -->
                <div id="projects-section" class="content-section active">
                    <div class="projects-section">
                        <div class="section-title">
                            <h1>Our Solar Projects</h1>
                            <p>Manage and track all your solar energy projects</p>
                        </div>

                        <div class="add-project-form">
                            <h3 class="mb-4">Add New Project</h3>

                            <div class="alert alert-success d-none" id="successAlert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <span id="successMessage">Project added successfully!</span>
                            </div>

                            <div class="alert alert-danger d-none" id="errorAlert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <span id="errorMessage">Please check the form for errors.</span>
                            </div>

                            <form action="" method="POST" enctype="multipart/form-data" id="projectForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="projectName" class="form-label">
                                                <i class="bi bi-building"></i> Project Name
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="projectName"
                                                placeholder="Enter project name"
                                                name="project_name"
                                                required>
                                        </div>

                                        <div class="form-group">
                                            <label for="projectSummary" class="form-label">
                                                <i class="bi bi-text-paragraph"></i> Project Summary
                                            </label>
                                            <textarea
                                                class="form-control"
                                                id="projectSummary"
                                                rows="3"
                                                placeholder="Brief description of the project (2-3 lines)"
                                                name="project_summary"
                                                required></textarea>
                                        </div>


                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">
                                                <i class="bi bi-image"></i> Project Image
                                            </label>
                                            <div class="file-upload-area" id="uploadArea">
                                                <div class="upload-icon">
                                                    <i class="bi bi-cloud-arrow-up"></i>
                                                </div>
                                                <h5>Drop project image here</h5>
                                                <p class="text-muted">or click to browse files</p>
                                                <input type="file" class="file-input" id="projectImage" name="project_image" accept="image/*">

                                                <div class="image-preview" id="imagePreview">
                                                    <div class="preview-actions">
                                                        <button type="button" class="preview-btn" id="removeBtn" title="Remove">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    </div>
                                                    <img src="" alt="Project Preview">
                                                </div>

                                                <div class="progress-bar" id="progressBar">
                                                    <div class="progress-fill" id="progressFill"></div>
                                                </div>

                                                <div class="file-info" id="fileInfo"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" name="submit" class="btn btn-brand-primary" id="submitBtn">
                                            <i class="bi bi-plus-circle me-2"></i> Add Project
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>


                    </div>
                </div>
                <!-- <footer> -->
                <?php require('../include/footer.php') ?>
            </div>

    </div>
    </main>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Custom JavaScript -->
    <script src="../css&js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadArea = document.getElementById('uploadArea');
            const projectImage = document.getElementById('projectImage');
            const imagePreview = document.getElementById('imagePreview');
            const previewImg = imagePreview.querySelector('img');
            const removeBtn = document.getElementById('removeBtn');
            const progressBar = document.getElementById('progressBar');
            const progressFill = document.getElementById('progressFill');
            const fileInfo = document.getElementById('fileInfo');
            const errorAlert = document.getElementById('errorAlert');
            const errorMessage = document.getElementById('errorMessage');

            // 🟢 1️⃣ Click area opens file dialog
            uploadArea.addEventListener('click', (e) => {
                if (e.target !== removeBtn && !removeBtn.contains(e.target)) {
                    projectImage.click();
                }
            });

            // 🟢 2️⃣ File selected → show preview
            projectImage.addEventListener('change', (e) => {
                if (e.target.files.length) handleFileSelect(e.target.files[0]);
            });

            // 🟢 3️⃣ Handle drag & drop
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
                    projectImage.files = e.dataTransfer.files;
                    handleFileSelect(e.dataTransfer.files[0]);
                }
            });

            // 🟢 4️⃣ Remove preview
            removeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                previewImg.src = "";
                imagePreview.style.display = "none";
                projectImage.value = "";
                progressBar.style.display = "none";
                fileInfo.style.display = "none";
            });

            // 🟢 5️⃣ Handle File Preview
            function handleFileSelect(file) {
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    showError("Please select a valid image file (JPEG, PNG, GIF, WEBP)");
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    showError("File size must be less than 5MB");
                    return;
                }

                fileInfo.textContent = `${file.name} (${formatFileSize(file.size)})`;
                fileInfo.style.display = "block";

                progressBar.style.display = "block";
                simulateUpload();

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.style.display = "block";
                    hideError();
                };
                reader.readAsDataURL(file);
            }

            // 🟢 6️⃣ Simulated Upload Progress
            function simulateUpload() {
                let width = 0;
                const interval = setInterval(() => {
                    width += Math.random() * 20;
                    if (width >= 100) {
                        width = 100;
                        clearInterval(interval);
                    }
                    progressFill.style.width = width + "%";
                }, 200);
            }

            // 🟢 7️⃣ File size formatter
            function formatFileSize(bytes) {
                if (bytes === 0) return "0 Bytes";
                const k = 1024;
                const sizes = ["Bytes", "KB", "MB", "GB"];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
            }

            // 🟢 8️⃣ Error handling
            function showError(message) {
                errorMessage.textContent = message;
                errorAlert.classList.remove('d-none');
            }

            function hideError() {
                errorAlert.classList.add('d-none');
            }
        });
    </script>

</body>

</html>