const uploadArea = document.getElementById("uploadArea");
const fileInput = document.getElementById("productImage");
const imagePreview = document.getElementById("imagePreview");
const previewImg = imagePreview.querySelector("img");
const removeBtn = document.getElementById("removeBtn");
const alertBox = document.getElementById("alertBox");

// File Upload Preview
uploadArea.addEventListener("click", () => fileInput.click());
fileInput.addEventListener("change", () => {
  const file = fileInput.files[0];
  if (file && file.type.startsWith("image/")) {
    const reader = new FileReader();
    reader.onload = (e) => {
      previewImg.src = e.target.result;
      imagePreview.style.display = "block";
    };
    reader.readAsDataURL(file);
  } else {
    alert("Please upload a valid image file!");
  }
});

removeBtn.addEventListener("click", (e) => {
  e.stopPropagation();
  fileInput.value = "";
  previewImg.src = "";
  imagePreview.style.display = "none";
});

// Drag and Drop
uploadArea.addEventListener("dragover", (e) => {
  e.preventDefault();
  uploadArea.classList.add("drag-over");
});
uploadArea.addEventListener("dragleave", () =>
  uploadArea.classList.remove("drag-over")
);
uploadArea.addEventListener("drop", (e) => {
  e.preventDefault();
  uploadArea.classList.remove("drag-over");
  const file = e.dataTransfer.files[0];
  fileInput.files = e.dataTransfer.files;
  const reader = new FileReader();
  reader.onload = (ev) => {
    previewImg.src = ev.target.result;
    imagePreview.style.display = "block";
  };
  reader.readAsDataURL(file);
});

// Submit form demo
document.getElementById("productForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const min = parseInt(document.getElementById("priceMin").value);
  const max = parseInt(document.getElementById("priceMax").value);

  if (min >= max) {
    alertBox.className = "alert alert-danger text-center";
    alertBox.textContent =
      "Maximum price should be greater than minimum price!";
    return;
  }

  alertBox.className = "alert alert-success text-center";
  alertBox.innerHTML =
    '<i class="bi bi-check-circle-fill me-2"></i>Product added successfully!';
  this.reset();
  imagePreview.style.display = "none";
});
