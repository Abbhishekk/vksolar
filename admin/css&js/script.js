// --- Sidebar Toggle ---
function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  const mainContent = document.getElementById("main-content");
  const overlay = document.querySelector(".sidebar-overlay");

  if (window.innerWidth > 991) {
    sidebar.classList.toggle("collapsed");
    mainContent.classList.toggle("expanded");
  } else {
    sidebar.classList.toggle("active");
    if (overlay) {
      overlay.style.display = sidebar.classList.contains("active")
        ? "block"
        : "none";
    }
  }
}

// --- Close Sidebar ---
function closeSidebar() {
  const sidebar = document.getElementById("sidebar");
  const overlay = document.querySelector(".sidebar-overlay");

  sidebar.classList.remove("active");
  if (overlay) overlay.style.display = "none";
}

// --- DOMContentLoaded ---
document.addEventListener("DOMContentLoaded", function () {
  const overlay = document.querySelector(".sidebar-overlay");

  if (overlay) overlay.addEventListener("click", closeSidebar);

  // Close sidebar only when clicking real page links
  const allLinks = document.querySelectorAll(".sidebar-nav a.nav-link");
  allLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      const isDropdownToggle =
        link.getAttribute("data-bs-toggle") === "collapse";
      const href = link.getAttribute("href");
      const isRealLink = href && href !== "#" && href.trim() !== "";

      if (window.innerWidth <= 991 && isRealLink && !isDropdownToggle) {
        closeSidebar();
      }
    });
  });

  // Reset sidebar on desktop resize
  window.addEventListener("resize", function () {
    if (window.innerWidth > 991) closeSidebar();
  });
});
