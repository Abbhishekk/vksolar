// Simple search functionality
document.getElementById("search-button").addEventListener("click", function () {
  const searchTerm = document
    .getElementById("search-input")
    .value.toLowerCase();
  const projectCards = document.querySelectorAll(".project-card");
  let visibleCount = 0;

  projectCards.forEach((card) => {
    const title = card
      .querySelector(".project-title")
      .textContent.toLowerCase();
    const summary = card
      .querySelector(".project-summary")
      .textContent.toLowerCase();
    const location = card
      .querySelector(".project-meta span:first-child")
      .textContent.toLowerCase();
    const status = card
      .querySelector(".status-badge")
      .textContent.toLowerCase();

    if (
      title.includes(searchTerm) ||
      summary.includes(searchTerm) ||
      location.includes(searchTerm) ||
      status.includes(searchTerm)
    ) {
      card.style.display = "";
      visibleCount++;
    } else {
      card.style.display = "none";
    }
  });

  // Show/hide empty results message
  const emptyResults = document.getElementById("empty-results");
  if (visibleCount === 0) {
    emptyResults.style.display = "block";
  } else {
    emptyResults.style.display = "none";
  }
});

// Reset functionality
document.getElementById("reset-button").addEventListener("click", function () {
  document.getElementById("search-input").value = "";
  const projectCards = document.querySelectorAll(".project-card");
  projectCards.forEach((card) => {
    card.style.display = "";
  });
  document.getElementById("empty-results").style.display = "none";
});

// Add project button
document
  .getElementById("add-project-btn")
  .addEventListener("click", function () {
    const modal = new bootstrap.Modal(
      document.getElementById("projectFormModal")
    );
    modal.show();
  });
