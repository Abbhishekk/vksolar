<?php

$title = "view-product";

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
          <!-- Header Section -->
    <header class="page-header">
      <div class="container">
        <div class="header-content">
          <div class="header-text">
            <h1 class="display-5 fw-bold">Solar Products Portfolio</h1>
            <p class="lead mb-0">
              Browse and manage all your sustainable energy products
            </p>
          </div>
          <div class="header-actions">
            <a href="../pages/add-product.php" class="btn btn-light btn-lg" id="add-product-btn">
              <i class="bi bi-plus-circle me-2"></i> Add New Product
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
              <div class="stats-number">6</div>
              <div class="stats-label">Total Products</div>
            </div>
          </div>
          <div class="col-md-8">
            <div class="search-container">
              <input
                type="text"
                class="form-control search-box"
                id="search-input"
                placeholder="Search products by name, category, or price..."
              />
              <div class="search-buttons">
                <button class="btn btn-brand" id="search-button">
                  <i class="bi bi-search me-1"></i> Search
                </button>
                <button class="btn btn-outline-danger" id="reset-button">
                  <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Products Grid Section -->
      <section class="products-section">
        <div class="empty-results" id="empty-results">
          <i class="bi bi-inbox display-1 text-muted"></i>
          <h3 class="mt-3">No products found</h3>
          <p class="text-muted">
            Try adjusting your search criteria or add a new product
          </p>
        </div>

        <div class="row" id="products-container">
          <!-- Product 1 -->
          <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="product-card">
              <img
                src="https://images.unsplash.com/photo-1616627893827-1f637c2a7c12?ixlib=rb-4.0.3&auto=format&fit=crop&w=1472&q=80"
                class="product-image"
                alt="Eco Solar Charger"
              />
              <div class="card-body">
                <h5 class="product-title">Eco Solar Charger</h5>
                <p class="product-price">₹3,000 - ₹20,000</p>
                <p class="product-warranty">
                  <i class="bi bi-shield-check me-1"></i> Warranty: 2 Years
                </p>
                <p class="product-summary">
                  Portable solar charger for outdoor use, suitable for mobile
                  devices and small electronics.
                </p>
              </div>
              <div class="card-footer">
                <div class="action-buttons">
                  <button
                    class="btn btn-sm btn-outline-primary view-details"
                    data-bs-toggle="modal"
                    data-bs-target="#productDetailsModal1"
                  >
                    <i class="bi bi-eye me-1"></i> View
                  </button>
                  <button class="btn btn-sm btn-outline-secondary" disabled>
                    <i class="bi bi-pencil me-1"></i> Edit
                  </button>
                  <button class="btn btn-sm btn-outline-danger" disabled>
                    <i class="bi bi-trash me-1"></i> Delete
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Product 2 -->
          <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="product-card">
              <img
                src="https://images.unsplash.com/photo-1616627913587-4d8e9f9b3a8d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80"
                class="product-image"
                alt="Residential Solar Panel Kit"
              />
              <div class="card-body">
                <h5 class="product-title">Residential Solar Panel Kit</h5>
                <p class="product-price">₹50,000 - ₹1,50,000</p>
                <p class="product-warranty">
                  <i class="bi bi-shield-check me-1"></i> Warranty: 5 Years
                </p>
                <p class="product-summary">
                  Complete solar panel kit for homes including inverter,
                  batteries, and installation guide.
                </p>
              </div>
              <div class="card-footer">
                <div class="action-buttons">
                  <button
                    class="btn btn-sm btn-outline-primary view-details"
                    data-bs-toggle="modal"
                    data-bs-target="#productDetailsModal2"
                  >
                    <i class="bi bi-eye me-1"></i> View
                  </button>
                  <button class="btn btn-sm btn-outline-secondary" disabled>
                    <i class="bi bi-pencil me-1"></i> Edit
                  </button>
                  <button class="btn btn-sm btn-outline-danger" disabled>
                    <i class="bi bi-trash me-1"></i> Delete
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Product 3 -->
          <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="product-card">
              <img
                src="https://images.unsplash.com/photo-1602045485037-3a2f3b5c1b87?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80"
                class="product-image"
                alt="Solar Water Heater"
              />
              <div class="card-body">
                <h5 class="product-title">Solar Water Heater</h5>
                <p class="product-price">₹25,000 - ₹1,00,000</p>
                <p class="product-warranty">
                  <i class="bi bi-shield-check me-1"></i> Warranty: 3 Years
                </p>
                <p class="product-summary">
                  High-efficiency water heater using solar energy for homes and
                  small commercial buildings.
                </p>
              </div>
              <div class="card-footer">
                <div class="action-buttons">
                  <button
                    class="btn btn-sm btn-outline-primary view-details"
                    data-bs-toggle="modal"
                    data-bs-target="#productDetailsModal3"
                  >
                    <i class="bi bi-eye me-1"></i> View
                  </button>
                  <button class="btn btn-sm btn-outline-secondary" disabled>
                    <i class="bi bi-pencil me-1"></i> Edit
                  </button>
                  <button class="btn btn-sm btn-outline-danger" disabled>
                    <i class="bi bi-trash me-1"></i> Delete
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Product 4 -->
          <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="product-card">
              <img
                src="https://images.unsplash.com/photo-1621905252507-b35492cc74b4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1469&q=80"
                class="product-image"
                alt="Solar Power Inverter"
              />
              <div class="card-body">
                <h5 class="product-title">Solar Power Inverter</h5>
                <p class="product-price">₹15,000 - ₹50,000</p>
                <p class="product-warranty">
                  <i class="bi bi-shield-check me-1"></i> Warranty: 4 Years
                </p>
                <p class="product-summary">
                  High-capacity solar inverter with advanced MPPT technology for
                  optimal energy conversion.
                </p>
              </div>
              <div class="card-footer">
                <div class="action-buttons">
                  <button
                    class="btn btn-sm btn-outline-primary view-details"
                    data-bs-toggle="modal"
                    data-bs-target="#productDetailsModal4"
                  >
                    <i class="bi bi-eye me-1"></i> View
                  </button>
                  <button class="btn btn-sm btn-outline-secondary" disabled>
                    <i class="bi bi-pencil me-1"></i> Edit
                  </button>
                  <button class="btn btn-sm btn-outline-danger" disabled>
                    <i class="bi bi-trash me-1"></i> Delete
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Product 5 -->
          <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="product-card">
              <img
                src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1558&q=80"
                class="product-image"
                alt="Solar Battery Storage"
              />
              <div class="card-body">
                <h5 class="product-title">Solar Battery Storage</h5>
                <p class="product-price">₹20,000 - ₹80,000</p>
                <p class="product-warranty">
                  <i class="bi bi-shield-check me-1"></i> Warranty: 5 Years
                </p>
                <p class="product-summary">
                  Deep cycle solar batteries for energy storage with long
                  lifespan and high efficiency.
                </p>
              </div>
              <div class="card-footer">
                <div class="action-buttons">
                  <button
                    class="btn btn-sm btn-outline-primary view-details"
                    data-bs-toggle="modal"
                    data-bs-target="#productDetailsModal5"
                  >
                    <i class="bi bi-eye me-1"></i> View
                  </button>
                  <button class="btn btn-sm btn-outline-secondary" disabled>
                    <i class="bi bi-pencil me-1"></i> Edit
                  </button>
                  <button class="btn btn-sm btn-outline-danger" disabled>
                    <i class="bi bi-trash me-1"></i> Delete
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Product 6 -->
          <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="product-card">
              <img
                src="https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80"
                class="product-image"
                alt="Solar Street Lights"
              />
              <div class="card-body">
                <h5 class="product-title">Solar Street Lights</h5>
                <p class="product-price">₹5,000 - ₹25,000</p>
                <p class="product-warranty">
                  <i class="bi bi-shield-check me-1"></i> Warranty: 3 Years
                </p>
                <p class="product-summary">
                  Energy-efficient solar street lights with motion sensors and
                  automatic dusk-to-dawn operation.
                </p>
              </div>
              <div class="card-footer">
                <div class="action-buttons">
                  <button
                    class="btn btn-sm btn-outline-primary view-details"
                    data-bs-toggle="modal"
                    data-bs-target="#productDetailsModal6"
                  >
                    <i class="bi bi-eye me-1"></i> View
                  </button>
                  <button class="btn btn-sm btn-outline-secondary" disabled>
                    <i class="bi bi-pencil me-1"></i> Edit
                  </button>
                  <button class="btn btn-sm btn-outline-danger" disabled>
                    <i class="bi bi-trash me-1"></i> Delete
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
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
  <script src="../css&js/add-product.js"></script>
  
  </body>
</html>