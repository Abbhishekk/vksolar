<?php
$title = "carosel";
require('../../database/connection.php');
require('../../database/function.php');

$conn = new connection();
$db = $conn->my_connect();

$fun = new fun($db);

$result = $fun->carousel_fetch_all_data();


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
        <div class="carousel-container px-0 ">
          <!-- Existing Slides Section -->
          <div class="existing-slides-section">
            <div class="card">
              <div class="card-header">
                <h2 class="section-title">Existing Slides</h2>
                <p class="section-subtitle">
                  Manage your current carousel slides
                </p>
              </div>
              <div class="card-body">
                <div
                  class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="fw-semibold mb-0">Your Slides</h5>
                  <button
                    type="button"
                    class="btn btn-primary"
                    id="addNewSlideBtn">
                    <i class="bi bi-plus-circle me-2"></i> Add New Slide
                  </button>
                </div>
                <div class="existing-slides" id="existingSlidesContainer">

                  <!--  slides Cards-->
                  <?php foreach ($result as $key => $arr) { ?>
                    <div class="slide-card">
                      <div class="slide-card-header">
                        <h3 class="slide-card-title">
                          <?php echo $arr['slide_title']; ?>
                        </h3>
                        <div class="slide-card-order"><?php echo ++$key; ?></div>
                      </div>
                      <div class="slide-thumbnail">
                        <img
                          src="<?php echo $arr['bg_img_path']; ?>"
                          alt="<?php echo $arr['bg_img_path']; ?>" />
                      </div>
                      <div class="slide-info">
                        <div class="slide-content">
                          <?php echo $arr['slide_content']; ?>
                        </div>
                      </div>
                      <div class="slide-actions">
                        <a
                          href="../pages/edit_carousel.php?id=<?php echo $arr['slide_id']; ?>"
                          class="btn-edit btn-success w-100 d-flex align-items-center justify-content-center text-white text-decoration-none"
                          data-slide-id="1">
                          <i class="bi bi-pencil"></i>&nbsp;Edit
                        </a>

                      </div>
                    </div>
                  <?php } ?>
                  
                </div>
              </div>
            </div>
          </div>

          <!-- Add New Slide Form -->
          <div class="carousel-form-section" id="addSlideFormSection">
            <div class="card-header">
              <h2 class="section-title">Add New Slide</h2>
              <p class="section-subtitle">
                Fill in the details to add a new slide
              </p>
            </div>
            <div class="card-body">
              <form
                action="../../database/new-slide-insert.php"
                method="POST"
                id="addSlideForm"
                enctype="multipart/form-data">
                <div class="form-group mb-3">
                  <label for="newSlideTitle" class="form-label fw-semibold">Slide Title *</label>
                  <input
                    type="text"
                    class="form-control"
                    id="newSlideTitle"
                    name="slide_title"
                    placeholder="Enter slide title"
                    required />
                </div>

                <div class="form-group mb-3">
                  <label for="newSlideContent" class="form-label fw-semibold">Slide Content *</label>
                  <textarea
                    class="form-control"
                    id="newSlideContent"
                    name="slide_content"
                    placeholder="Enter slide content"
                    rows="3"
                    required></textarea>
                </div>

                <div class="form-row mb-3">
                  <div class="form-group">
                    <label class="form-label fw-semibold">Background Image *</label>
                    <div class="upload-container">
                      <div class="upload-preview" id="newBackgroundPreview">
                        <i
                          class="bi bi-image text-muted"
                          style="font-size: 2rem"></i>
                      </div>
                      <input
                        type="file"
                        id="newBackgroundImage"
                        name="background_image"
                        accept="image/*"
                        class="d-none"
                        required />
                      <button
                        type="button"
                        class="btn btn-outline-success"
                        onclick="document.getElementById('newBackgroundImage').click()">
                        <i class="bi bi-upload me-1"></i> Upload Background
                      </button>
                      <small class="text-muted">Recommended: 1200x600px JPG, PNG</small>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="form-label fw-semibold">Slide Logo</label>
                    <div class="upload-container">
                      <div class="logo-preview" id="newLogoPreview">
                        <i class="bi bi-image text-muted"></i>
                      </div>
                      <input
                        type="file"
                        id="newSlideLogo"
                        name="slide_logo"
                        accept="image/*"
                        class="d-none" />
                      <button
                        type="button"
                        class="btn btn-outline-success"
                        onclick="document.getElementById('newSlideLogo').click()">
                        <i class="bi bi-upload me-1"></i> Upload Logo
                      </button>
                      <small class="text-muted">Recommended: 120x80px PNG with transparency</small>
                    </div>
                  </div>
                </div>

                <div class="carousel-actions">
                  <button type="submit" class="btn btn-success" name="submit">
                    <i class="bi bi-check-circle me-2"></i> Add Slide
                  </button>
                  <button
                    type="button"
                    class="btn btn-outline-danger"
                    id="cancelAddFormBtn">
                    <i class="bi bi-x-circle me-2"></i> Cancel
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Edit Slide Form -->
          <div class="carousel-form-section" id="editSlideFormSection">
            <div class="card-header">
              <h2 class="section-title">Edit Slide <?php echo $id; ?></h2>
              <p class="section-subtitle">
                Edit details of the selected slide
              </p>
            </div>
            <div class="card-body">
              <form
                action=""
                method="POST"
                id="editSlideForm"
                enctype="multipart/form-data">
                <input
                  type="hidden"
                  id="editSlideId"
                  name="slide_id"
                  value="" />

                <div class="form-group mb-3">
                  <label for="editSlideTitle" class="form-label fw-semibold">Slide Title *</label>
                  <input
                    type="text"
                    class="form-control"
                    id="editSlideTitle"
                    name="edit_slide_title"
                    placeholder="Enter slide title"
                    required />
                </div>

                <div class="form-group mb-3">
                  <label for="editSlideContent" class="form-label fw-semibold">Slide Content *</label>
                  <textarea
                    class="form-control"
                    id="editSlideContent"
                    name="edit_slide_content"
                    placeholder="Enter slide content"
                    rows="3"
                    required></textarea>
                </div>

                <div class="form-row mb-3">
                  <div class="form-group">
                    <label class="form-label fw-semibold">Background Image *</label>
                    <div class="upload-container">
                      <div class="upload-preview" id="editBackgroundPreview">
                        <i
                          class="bi bi-image text-muted"
                          style="font-size: 2rem"></i>
                      </div>
                      <input
                        type="file"
                        id="editBackgroundImage"
                        name="Edit_background_image"
                        accept="image/*"
                        class="d-none" />
                      <button
                        type="button"
                        class="btn btn-outline-success"
                        onclick="document.getElementById('editBackgroundImage').click()">
                        <i class="bi bi-upload me-1"></i> Upload Background
                      </button>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="form-label fw-semibold">Slide Logo</label>
                    <div class="upload-container">
                      <div class="logo-preview" id="editLogoPreview">
                        <i class="bi bi-image text-muted"></i>
                      </div>
                      <input
                        type="file"
                        id="editSlideLogo"
                        name="edit_slide_logo"
                        accept="image/*"
                        class="d-none" />
                      <button
                        type="button"
                        class="btn btn-outline-success"
                        onclick="document.getElementById('editSlideLogo').click()">
                        <i class="bi bi-upload me-1"></i> Upload Logo
                      </button>
                    </div>
                  </div>
                </div>

                <div class="carousel-actions">
                  <button type="submit" class="btn btn-success" name="update">
                    <i class="bi bi-check-circle me-2"></i> Update Slide
                  </button>
                  <button
                    type="button"
                    class="btn btn-outline-danger"
                    id="cancelEditFormBtn">
                    <i class="bi bi-x-circle me-2"></i> Cancel
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <!-- footer  -->
         <?php require('../include/footer.php') ?>
      </div>
    </main>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../css&js/script.js"></script>
  <script>
    // ==========================
    // Show/Hide Forms
    // ==========================
    const addSlideFormSection = document.getElementById(
      "addSlideFormSection"
    );
    const editSlideFormSection = document.getElementById(
      "editSlideFormSection"
    );

    function toggleForm(formType) {
      addSlideFormSection.classList.remove("active");
      editSlideFormSection.classList.remove("active");

      if (formType === "add") addSlideFormSection.classList.add("active");
      if (formType === "edit") editSlideFormSection.classList.add("active");
    }

    // Add New Slide Button
    document
      .getElementById("addNewSlideBtn")
      .addEventListener("click", () => {
        document.getElementById("addSlideForm").reset();
        toggleForm("add");
      });

    // Edit Buttons
    document.querySelectorAll(".btn-edit").forEach((button) => {
      button.addEventListener("click", () => {
        document.getElementById("editSlideForm").reset();
        toggleForm("edit");
      });
    });

    // Cancel Buttons
    document
      .getElementById("cancelAddFormBtn")
      .addEventListener("click", () => toggleForm());
    document
      .getElementById("cancelEditFormBtn")
      .addEventListener("click", () => toggleForm());

    // ==========================
    // File Upload Previews
    // ==========================
    function setupPreview(inputId, previewId) {
      document
        .getElementById(inputId)
        .addEventListener("change", function(e) {
          const file = e.target.files[0];
          if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
              const preview = document.getElementById(previewId);
              preview.innerHTML = "";
              const img = document.createElement("img");
              img.src = e.target.result;
              img.classList.add("img-fluid");
              preview.appendChild(img);
            };
            reader.readAsDataURL(file);
          }
        });
    }

    setupPreview("newBackgroundImage", "newBackgroundPreview");
    setupPreview("newSlideLogo", "newLogoPreview");
    setupPreview("editBackgroundImage", "editBackgroundPreview");
    setupPreview("editSlideLogo", "editLogoPreview");
  </script>
</body>

</html>