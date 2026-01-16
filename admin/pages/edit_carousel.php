<?php
$title = "edit_carousel";
require('../../database/connection.php');
require('../../database/function.php');

$conn = new connection();
$db = $conn->my_connect();
$fun = new fun($db);

$message = "";
$messageType = "";

// Fetch single slide
$arr = null;
if (isset($_GET['id'])) {
    $res = $fun->carousel_fetch_single_data($_GET['id']);
    if ($res && $res->num_rows > 0) {
        $arr = $res->fetch_assoc(); // single row as associative array
    } else {
        $message = "Slide not found!";
        $messageType = "error";
    }
}

// Handle form submission
if (isset($_POST['update']) && $arr) {
    $id = $_POST['slide_id'];
    $SlideTitle = $_POST['edit_slide_title'];
    $SlideContent = $_POST['edit_slide_content'];

    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

    // Background image
    if (!empty($_FILES['Edit_background_image']['name'])) {
        $imgName = basename($_FILES['Edit_background_image']['name']);
        $targetDir = "../../database/db_images/";
        $img_location = $targetDir . $imgName;
        $tempName = $_FILES['Edit_background_image']['tmp_name'];
        $img_type = strtolower(pathinfo($img_location, PATHINFO_EXTENSION));

        if (in_array($img_type, $allowedTypes)) {
            $bg_image = move_uploaded_file($tempName, $img_location);
            if (!$bg_image) {
                $message = "Failed to upload background image.";
                $messageType = "error";
            }
        } else {
            $message = "Invalid background image type! Allowed: jpg, jpeg, png, gif, webp, svg.";
            $messageType = "error";
            $bg_image = false;
        }
    } else {
        $imgName = $arr['bg_img_name'];
        $img_location = $arr['bg_img_path'];
        $img_type = $arr['bg_img_type'];
        $bg_image = true;
    }

    // Logo image
    if (!empty($_FILES['edit_slide_logo']['name'])) {
        $logoName = basename($_FILES['edit_slide_logo']['name']);
        $logoTargetDir = "../../database/db_images/";
        $Logo_location = $logoTargetDir . $logoName;
        $logoTempName = $_FILES['edit_slide_logo']['tmp_name'];
        $logo_type = strtolower(pathinfo($Logo_location, PATHINFO_EXTENSION));

        if (in_array($logo_type, $allowedTypes)) {
            $logo_image = move_uploaded_file($logoTempName, $Logo_location);
            if (!$logo_image) {
                $message = "Failed to upload logo image.";
                $messageType = "error";
            }
        } else {
            $message = "Invalid logo image type! Allowed: jpg, jpeg, png, gif, webp, svg.";
            $messageType = "error";
            $logo_image = false;
        }
    } else {
        $logoName = $arr['logo_name'];
        $Logo_location = $arr['logo_path'];
        $logo_type = $arr['logo_type'];
        $logo_image = true;
    }

    // Update database if images are ok
    if ($bg_image && $logo_image && empty($message)) {
        $updateResult = $fun->updateCarousel($id, $SlideTitle, $SlideContent, $imgName, $img_location, $img_type, $logoName, $Logo_location, $logo_type);
        if ($updateResult) {
            $message = "Slide updated successfully!";
            $messageType = "success";

            header("location:carousel.php");
        } else {
            $message = "Something went wrong with update!";
            $messageType = "error";
        }
        // Refresh arr after update
        $arr = $fun->carousel_fetch_single_data($id)->fetch_assoc();
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <?php require('../include/head.php') ?>
    <style>
        /* Hide both forms initially */
        .carousel-form-section {
            display: none;
            margin-top: 2rem;
        }

        .carousel-form-section.active {
            display: block;
        }

        .upload-preview img {
            max-width: 100%;
            height: auto;
        }

        /* ===========================
   Responsive Styles
   =========================== */

        /* Tablet Devices (max-width: 992px) */
        @media (max-width: 992px) {
            main {
                padding: 1rem;
            }

            .carousel-form-section .card-body {
                padding: 1rem;
            }

            .form-row {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }

            .form-group {
                width: 100%;
            }

            .carousel-actions {
                display: flex;
                flex-direction: column;
                gap: 0.8rem;
            }

            .carousel-actions button {
                width: 100%;
            }

            .upload-preview img,
            .logo-preview img {
                max-width: 100%;
                height: auto;
                border-radius: 10px;
            }

            .section-title {
                font-size: 1.5rem;
            }

            .section-subtitle {
                font-size: 0.9rem;
            }
        }

        /* Mobile Devices (max-width: 576px) */
        @media (max-width: 576px) {
            body {
                padding: 0 0.5rem;
            }

            .carousel-container {
                padding: 0;
            }

            .card-header {
                text-align: center;
            }

            .section-title {
                font-size: 1.25rem;
            }

            .section-subtitle {
                font-size: 0.85rem;
                color: #777;
            }

            .form-group label {
                font-size: 0.9rem;
            }

            .form-control,
            textarea {
                font-size: 0.9rem;
                padding: 0.5rem;
            }

            .btn {
                font-size: 0.9rem;
                padding: 0.6rem 1rem;
            }

            .upload-container {
                text-align: center;
            }

            .upload-preview img,
            .logo-preview img {
                width: 90%;
                height: auto;
                margin: 0 auto;
                display: block;
            }

            .carousel-actions {
                flex-direction: column;
                align-items: center;
            }

            .carousel-actions button {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <?php require('../pages/sidebar.php') ?>

    <!-- Main Content -->
    <div id="main-content">
        <div class="sidebar-overlay"></div>

        <!-- Fixed Header -->
        <?php require('../include/navbar.php') ?>

        <main>
            <div class="row g-4">
                <!-- Carousel Management -->
                <div class="carousel-container px-0">
                    <!-- Message Alert -->
                    <div id="messageAlert" class="alert"
                        style="margin: 10px 0; font-weight: 500; border-radius: 8px; display: none; transition: opacity 0.5s;">
                        <i id="messageIcon" class="bi me-2"></i>
                        <span id="messageText"></span>
                    </div>


                    <!-- Edit Slide Form -->
                    <div class="carousel-form-section d-block" id="editSlideFormSection">
                        <div class="card-header">
                            <h2 class="section-title">Edit Slide</h2>
                            <p class="section-subtitle">
                                Edit details of the selected slide
                            </p>
                        </div>
                        <div class="card-body">
                            <?php if ($arr): ?>
                                <form action="" method="POST" id="editSlideForm" enctype="multipart/form-data">
                                    <input type="hidden" name="slide_id" value="<?php echo $arr['slide_id']; ?>" />

                                    <div class="form-group mb-3">
                                        <label for="editSlideTitle" class="form-label fw-semibold">Slide Title *</label>
                                        <input type="text" class="form-control" id="editSlideTitle" name="edit_slide_title" placeholder="Enter slide title" value="<?php echo $arr['slide_title']; ?>" required />
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="editSlideContent" class="form-label fw-semibold">Slide Content *</label>
                                        <textarea class="form-control" id="editSlideContent" name="edit_slide_content" placeholder="Enter slide content" rows="3" required><?php echo $arr['slide_content']; ?></textarea>
                                    </div>

                                    <div class="form-row mb-3">
                                        <div class="form-group">
                                            <label class="form-label fw-semibold">Background Image *</label>
                                            <div class="upload-container">
                                                <div class="upload-preview" id="editBackgroundPreview">
                                                    <?php if (!empty($arr['bg_img_path'])): ?>
                                                        <img src="<?php echo $arr['bg_img_path']; ?>" alt="Background Image">
                                                    <?php else: ?>
                                                        <i class="bi bi-image text-muted" style="font-size: 2rem"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <input type="file" id="editBackgroundImage" name="Edit_background_image" accept="image/*" class="d-none" />
                                                <button type="button" class="btn btn-outline-success" onclick="document.getElementById('editBackgroundImage').click()">
                                                    <i class="bi bi-upload me-1"></i> Upload Background
                                                </button>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label fw-semibold">Slide Logo</label>
                                            <div class="upload-container">
                                                <div class="logo-preview" id="editLogoPreview">
                                                    <?php if (!empty($arr['logo_path'])): ?>
                                                        <img src="<?php echo $arr['logo_path']; ?>" alt="Slide Logo">
                                                    <?php else: ?>
                                                        <i class="bi bi-image text-muted" style="font-size: 2rem"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <input type="file" id="editSlideLogo" name="edit_slide_logo" accept="image/*" class="d-none" />
                                                <button type="button" class="btn btn-outline-success" onclick="document.getElementById('editSlideLogo').click()">
                                                    <i class="bi bi-upload me-1"></i> Upload Logo
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="carousel-actions">
                                        <button type="submit" class="btn btn-success" name="update">
                                            <i class="bi bi-check-circle me-2"></i> Update Slide
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <p class="text-danger">Slide data not found.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
                <!-- <footer> -->
                <?php require('../include/footer.php') ?>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../css&js/script.js"></script>
    <script>
        // Image Preview Function
        function setupPreview(inputId, previewId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);

            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        preview.innerHTML = '';
                        const img = document.createElement('img');
                        img.src = event.target.result;
                        preview.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                } else {
                    preview.innerHTML = '<i class="bi bi-image" style="font-size: 2rem; color: #ccc;"></i>';
                }
            });
        }

        // Setup Previews
        setupPreview("editBackgroundImage", "editBackgroundPreview");
        setupPreview("editSlideLogo", "editLogoPreview");

        // Show Message Alert
        function showMessage(message, type) {
            if (!message) return;

            const alertBox = document.getElementById('messageAlert');
            const messageText = document.getElementById('messageText');
            const messageIcon = document.getElementById('messageIcon');

            alertBox.style.display = 'block';
            alertBox.style.opacity = '1';
            messageText.textContent = message;

            if (type === 'success') {
                alertBox.classList.remove('alert-danger');
                alertBox.classList.add('alert-success');
                messageIcon.className = 'bi bi-check-circle-fill me-2';
            } else {
                alertBox.classList.remove('alert-success');
                alertBox.classList.add('alert-danger');
                messageIcon.className = 'bi bi-exclamation-triangle-fill me-2';
            }

            // Hide after 10 seconds
            setTimeout(() => {
                alertBox.style.opacity = '0';
                setTimeout(() => alertBox.style.display = 'none', 500);
            }, 10000);
        }

        // Call showMessage with PHP values
        document.addEventListener('DOMContentLoaded', function() {
            const phpMessage = "<?php echo $message; ?>";
            const phpType = "<?php echo $messageType; ?>";
            showMessage(phpMessage, phpType);
        });
    </script>
</body>

</html>