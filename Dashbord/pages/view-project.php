<?php 
require('../../database/connection.php');
require('../../database/function.php');
$title = "view_project";

$conn = new connection();
$db = $conn->my_connect();

$fun = new fun($db);

$result = $fun->projectFetch();

$total = $result->num_rows;

if(isset($_POST['searchBtn']))
{
  $searchData = $_POST['searchInput'];
  $result = $fun->searchProject($searchData);
  $total = $result->num_rows;

  // $arr = $search_result->fetch_assoc();
  
  // echo "<pre>";
  // print_r($arr);
  // echo "</pre>";
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
          <!-- view project section -->
           <!-- Header Section -->
    <header class="page-header">
      <div class="container">
        <div class="header-content">
          <div class="header-text">
            <h1 class="display-5 fw-bold">Solar Projects Management</h1>
            <p class="lead mb-0">
              Manage and track all your sustainable energy projects in one place
            </p>
          </div>
          <div class="header-actions">
            <a href="projects.php" class="btn btn-light btn-lg" id="add-project-btn">
              <i class="bi bi-plus-circle me-2"></i> Add New Project
            </a>
          </div>
        </div>
      </div>
    </header>

    <main class="container">
      <!-- Filter and Search Section -->
      <section class="filter-section">
        <div class="row align-items-center">
          <div class="col-md-4">
            <div class="stats-card">
              <div class="stats-number"><?php echo $total; ?></div>
              <div class="stats-label">Total Projects</div>
            </div>
          </div>
          
          <div class="col-md-8">
            <form action="" method="POST">
            <div class="search-container">
              <input
                type="text"
                class="form-control search-box"
                id="search-input"
                name="searchInput"
                placeholder="Search projects by title..."
              />
              <div class="search-buttons">
                <button class="btn btn-brand" name="searchBtn" id="search-button">
                  <i class="bi bi-search me-1"></i> Search
                </button>
                <button class="btn btn-outline-danger" id="reset-button">
                  <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </button>
              </div>
            </div>
            </form>
          </div>
          
        </div>
      </section>

      <!-- Projects Grid Section -->
      <section class="projects-section">
        <div class="empty-results" id="empty-results">
          <i class="bi bi-inbox display-1 text-muted"></i>
          <h3 class="mt-3">No projects found</h3>
          <p class="text-muted">
            Try adjusting your search criteria or add a new project
          </p>
        </div>

        <div class="row" id="projects-container">
            <?php foreach($result as $arr) { ?>
          <!-- Project 1 -->
          <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="project-card">
              <img
                src="<?php echo $arr['image_path']; ?>"
                class="project-image"
                alt="Residential Solar Installation"
              />
              <div class="card-body">
                <div
                  class="d-flex justify-content-between align-items-start mb-2"
                >
                  <h5 class="project-title"><?php echo $arr['project_name']; ?></h5>
                  <span class="status-badge status-completed">Completed</span>
                </div>
                <p class="project-summary">
                  <?php echo $arr['project_summary']; ?>
                </p>
              </div>
              <div class="card-footer">
                <div class="action-buttons">
                  <button class="btn btn-sm btn-outline-primary view-details">
                    <i class="bi bi-eye me-1"></i> View
                  </button>
                  <a href="project_edit.php?id=<?php echo $arr['id']; ?>" class="btn btn-sm btn-outline-success edit-project">
                    <i class="bi bi-pencil me-1"></i> Edit
                  </a>
                  <button class="btn btn-sm btn-outline-danger delete-project">
                    <i class="bi bi-trash me-1"></i> Delete
                  </button>
                </div>
              </div>
            </div>
          </div>
          <?php } ?>
        </div>
        
      </section>
      <!-- <footer> -->
                <?php require('../include/footer.php') ?>
    </main>

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