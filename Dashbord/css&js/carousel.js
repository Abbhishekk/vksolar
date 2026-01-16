// Show/hide carousel form section
function toggleCarouselForm(show = true) {
  const formSection = document.getElementById("carouselFormSection");
  if (show) {
    formSection.classList.add("active");
    formSection.scrollIntoView({ behavior: "smooth" });
  } else {
    formSection.classList.remove("active");
  }
}

// Reset the form fields and previews
function resetForm() {
  const form = document.getElementById("carouselForm");
  form.reset();

  // Reset hidden slide ID
  document.getElementById("slideId").value = "";

  // Reset previews
  document.getElementById("backgroundPreview").innerHTML =
    '<i class="bi bi-image text-muted" style="font-size: 2rem;"></i>';
  document.getElementById("logoPreview").innerHTML =
    '<i class="bi bi-image text-muted"></i>';
}

// Optional: show status message (can be styled in CSS)
function showMessage(message, type = "info") {
  console.log(`[${type.toUpperCase()}] ${message}`);
}

// ==========================
// File Upload Previews
// ==========================

document
  .getElementById("backgroundImage")
  .addEventListener("change", function (e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        const preview = document.getElementById("backgroundPreview");
        preview.innerHTML = "";
        const img = document.createElement("img");
        img.src = e.target.result;
        img.classList.add("img-fluid");
        preview.appendChild(img);
      };
      reader.readAsDataURL(file);
    }
  });

document.getElementById("slideLogo").addEventListener("change", function (e) {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      const preview = document.getElementById("logoPreview");
      preview.innerHTML = "";
      const img = document.createElement("img");
      img.src = e.target.result;
      img.classList.add("img-fluid");
      preview.appendChild(img);
    };
    reader.readAsDataURL(file);
  }
});

// ==========================
// Button Event Listeners
// ==========================

// Add New Slide
document
  .getElementById("addNewSlideBtn")
  .addEventListener("click", function () {
    resetForm();
    toggleCarouselForm(true);
    showMessage("Creating new slide", "success");
  });

// Edit Slide (show form only, do not fill values)
document.querySelectorAll(".btn-edit").forEach((button) => {
  button.addEventListener("click", function () {
    toggleCarouselForm(true);
    showMessage("Editing slide (form ready)", "success");
  });
});

// Cancel Form Button
document.getElementById("cancelFormBtn").addEventListener("click", function () {
  toggleCarouselForm(false);
  showMessage("Slide form closed", "info");
});

<script>
  // Sidebar toggle functionality
  const sidebarToggle = document.getElementById("sidebarToggle");
  const sidebar = document.getElementById("sidebar");
  const mainContent = document.getElementById("main-content");

  if (sidebarToggle && sidebar && mainContent) {
    sidebarToggle.addEventListener("click", () => {
      sidebar.classList.toggle("active");
      mainContent.classList.toggle("shifted");
    });
  }
</script>
