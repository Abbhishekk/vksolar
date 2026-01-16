<?php

$title = "add-product";

require('../../database/connection.php');
require('../../database/function.php');

$conn = new connection();
$db = $conn->my_connect();

$fun = new fun($db);

$message = "";
$messageType = "";

if(isset($_POST['submit']))
{
    $productName = $_POST['productName'];
    $priceMin = $_POST['priceMin'];
    $priceMax = $_POST['priceMax'];
    $productWarranty = $_POST['productWarranty'];
    $productDescription = $_POST['productDescription'];

    // Image upload
    if (!empty($_FILES['productImage']['name'])) {
        $productimg_name = basename($_FILES['productImage']['name']);
        $targetDir = "../../database/db_images/";
        $img_location = $targetDir . $projectimg_name;
        $tempName = $_FILES['productImage']['tmp_name'];
        $img_type = pathinfo($img_location, PATHINFO_EXTENSION);

        $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array(strtolower($img_type), $allowedTypes)) {
            if (move_uploaded_file($tempName, $img_location)) {
                
            }
        }
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <?php require('../include/head.php');
    $title="maindash"; ?>
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
          <div class="container py-5">
      <div class="card p-4">
        <h2 class="text-center mb-4">Add New Solar Product</h2>

        <form action="" method="POST" id="productForm" enctype="multipart/form-data">
          <div class="row g-4">
            <!-- Left Section -->
            <div class="col-md-6">
              <!-- Product Name -->
              <div class="mb-3">
                <label for="productName" class="form-label"
                  >Product Name *</label
                >
                <input
                  type="text"
                  class="form-control"
                  id="productName"
                  name="productName"
                  placeholder="Enter product name"
                  required
                />
              </div>

              <!-- Price Range -->
              <div class="mb-3">
                <label class="form-label">Price Range (₹)*</label>
                <div class="input-group">
                  <span class="input-group-text bg-success text-white">₹</span>
                  <input
                    type="number"
                    class="form-control"
                    id="priceMin"
                    placeholder="Min Price"
                    name="priceMin"
                    min="0"
                    required
                  />
                  <span class="input-group-text">–</span>
                  <input
                    type="number"
                    class="form-control"
                    id="priceMax"
                    name="priceMax"
                    placeholder="Max Price"
                    min="0"
                    required
                  />
                </div>
                <small class="text-muted">Example: 2000 – 30000 INR</small>
              </div>

              <!-- Warranty -->
              <div class="mb-3">
                <label for="productWarranty" class="form-label"
                  >Warranty *</label
                >
                <input
                  type="text"
                  class="form-control"
                  id="productWarranty"
                  name="productWarranty"
                  placeholder="e.g., 2 Years, 5 Years"
                  required
                />
              </div>
            </div>

            <!-- Right Section (Image Upload) -->
            <div class="col-md-6">
              <label class="form-label">Product Image *</label>
              <div class="file-upload-area" id="uploadArea">
                <div class="upload-icon mb-2">
                  <i class="bi bi-cloud-arrow-up fs-1 text-success"></i>
                </div>
                <h6 class="text-secondary">
                  Click or drag image here to upload
                </h6>
                <input
                  type="file"
                  class="d-none"
                  id="productImage"
                  name="productImage"
                  accept="image/*"
                  required
                />

                <div class="image-preview" id="imagePreview">
                  <div class="position-relative d-inline-block">
                    <img src="" alt="Product Preview" class="img-fluid" />
                    <button type="button" class="remove-btn" id="removeBtn">
                      <i class="bi bi-x-lg text-danger"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Description -->
            <div class="col-12">
              <div class="mb-3">
                <label for="productDescription" class="form-label"
                  >Product Description *</label
                >
                <textarea
                  class="form-control"
                  id="productDescription"
                  name="productDescription"
                  rows="3"
                  placeholder="Enter short product description"
                  required
                ></textarea>
              </div>
            </div>

            <!-- Submit -->
            <div class="col-12 text-center">
              <button type="submit" name="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-circle me-2"></i> Add Product
              </button>
            </div>
          </div>
        </form>

        <div id="alertBox" class="alert mt-4 d-none text-center"></div>
      </div>
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
  <script src="../css&js/view-project.js"></script>
  
  </body>
</html>
<?php }
?>