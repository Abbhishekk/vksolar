<?php
$title = "logo";
require('../../database/connection.php');
require('../../database/function.php');

$conn = new connection();
$db = $conn->my_connect();
$fun = new fun($db);

$message = "";
$messageType = ""; // success or error
if (isset($_POST['submit'])) {
  $brandName = $_POST['brandName'];
  $fileName = basename($_FILES['logo']['name']);
  $targetDir = '../../database/db_images/';
  $img_location = $targetDir . $fileName;
  $tempName = $_FILES['logo']['tmp_name'];
  $img_type = pathinfo($img_location, PATHINFO_EXTENSION);

  $allowedTypes = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

  if (!empty($fileName)) {
    if (in_array(strtolower($img_type), $allowedTypes)) {
      if (move_uploaded_file($tempName, $img_location)) {
        $fun->deleteLogo();
        $LogoInsert = $fun->logoInsert($fileName, $img_location, $img_type, $brandName);

        if ($LogoInsert) {
          $message = "Logo updated successfully!";
          $messageType = "success";
        } else {
          $message = "Database insertion failed. Please try again.";
          $messageType = "error";
        }
      } else {
        $message = "File upload failed. Please check folder permissions.";
        $messageType = "error";
      }
    } else {
      $message = "Invalid image type! Please upload JPG, PNG, GIF, WEBP, or SVG.";
      $messageType = "error";
    }
  } else {
    $message = "Please select a logo image to upload.";
    $messageType = "error";
  }
}

$fetchLogo = $fun->fetchLogo();
if ($fetchLogo && $fetchLogo->num_rows > 0) {
  $arr = $fetchLogo->fetch_assoc();
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
        <!-- ✅ Place this just above your form -->
        <?php if (!empty($message)) : ?>
          <div id="messageAlert"
            class="alert alert-<?php echo ($messageType === 'success') ? 'success' : 'danger'; ?>"
            style="margin: 10px 0; font-weight: 500; border-radius: 8px; transition: opacity 0.5s;">
            <i class="bi <?php echo ($messageType === 'success') ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i>
            <?php echo $message; ?>
          </div>
        <?php endif; ?>


        <div>
          <!-- Clean Current Logo & Brand Name Display -->
          <div class="current-brand-display" id="currentBrandDisplay">
            <div class="current-brand-card">
              <div class="current-brand-header">
                <h2 class="section-title">Current Brand Identity</h2>
                <p class="section-subtitle">
                  Your current logo and brand name display
                </p>
              </div>
              <div class="current-brand-body">
                <!-- Brand Name Item -->
                <div class="current-brand-item">
                  <div class="brand-item-header">
                    <h3 class="brand-item-title">
                      <i class="bi bi-tag-fill"></i>
                      Brand Name
                    </h3>
                  </div>
                  <div class="current-brand-name" id="currentBrandName">
                    <?php echo $arr['brand_name']; ?>
                  </div>
                </div>

                <!-- Logo Item -->
                <div class="current-brand-item">
                  <div class="brand-item-header">
                    <h3 class="brand-item-title">
                      <i class="bi bi-image-fill"></i>
                      Company Logo
                    </h3>
                  </div>
                  <div class="current-logo" id="currentLogoPreview">
                    <img src="<?php echo $arr['logo_path']; ?>" alt="<?php echo $arr['logo_path']; ?>">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Combined Logo & Brand Management Form -->
          <form action="" method="POST" enctype="multipart/form-data" id="brandManagementForm">
            <div class="form-header">
              <h2 class="section-title">Update Brand Identity</h2>
              <p class="section-subtitle">Modify your logo and brand name</p>
            </div>
            <div class="form-body">
              <!-- Brand Name Input -->
              <div class="form-group brandBox">
                <label for="brandNameInput" class="form-label">
                  <i class="bi bi-pencil-square"></i>
                  Brand Name
                </label>
                <input
                  type="text"
                  class="form-control brandNameInput"
                  id="brandNameInput"
                  name="brandName"
                  placeholder="Enter your brand name"
                  value="<?php echo $arr['brand_name']; ?>" />
              </div>

              <!-- Logo Upload Section -->
              <div class="form-group">
                <label class="form-label">
                  <i class="bi bi-card-image"></i>
                  Company Logo
                </label>
                <div class="logo-preview-container">
                  <div class="logo-preview" id="logoPreview">
                    <?php if (!empty($arr['logo_path'])): ?>
                      <img src="<?php echo $arr['logo_path']; ?>" alt="logo Image">
                    <?php else: ?>
                      <i class="bi bi-image text-muted" style="font-size: 2rem"></i>
                    <?php endif; ?>
                  </div>

                  <div class="logo-actions">
                    <input
                      type="file"
                      id="logoUpload"
                      name="logo"
                      class="d-none" />
                    <button
                      type="button"
                      class="btn btn-primary"
                      onclick="document.getElementById('logoUpload').click()">
                      <i class="bi bi-upload"></i> Upload Logo
                    </button>
                  </div>

                  <div class="mt-3">
                    <p class="text-muted small">
                      <i class="bi bi-info-circle me-1"></i>
                      Recommended size: 200x200 pixels. Supported formats: jpg, jpeg, png, webp, svg
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Single Save Button -->
            <div
              class="text-center"
              style="padding: 20px; border-top: 1px solid var(--border)">
              <input type="submit" class="modern-btn" value="Save Changes" name="submit" />
            </div>
          </form>
        </div>
        <!-- <footer> -->
                <?php require('../include/footer.php') ?>
      </div>
    </main>
  </div>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Custom JavaScript -->
  <script src="../css&js/script.js"></script>
  <script src="../css&js/logo&brand.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const alertBox = document.getElementById('messageAlert');
      if (alertBox) {
        // Keep it fully visible for 20 seconds
        alertBox.style.opacity = '1';
        setTimeout(() => {
          // fade out smoothly
          alertBox.style.opacity = '0';
          setTimeout(() => alertBox.remove(), 500); // remove after fade
        }, 10000); // 20 seconds
      }
    });
  </script>


</body>

</html>