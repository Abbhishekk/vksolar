document.addEventListener("DOMContentLoaded", function () {
  const logoUpload = document.getElementById("logoUpload");
  const logoPreview = document.getElementById("logoPreview");

  // Handle logo upload
  logoUpload.addEventListener("change", function (e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        // Update preview
        logoPreview.innerHTML = `<img src="${e.target.result}" alt="Logo Preview">`;
      };
      reader.readAsDataURL(file);
    }
  });
});
