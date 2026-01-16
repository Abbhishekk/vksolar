<div><nav id="sidebar">
      <div class="sidebar-logo">
        <i class="bi bi-sun-fill"></i>
        <span class="sidebar-logo-text">VK Solar</span>
      </div>

      <!-- Close Button for Mobile/Tablet Only - Hidden on Desktop -->
      <button class="sidebar-close-btn" onclick="closeSidebar()">
        <i class="bi bi-x"></i>
      </button>

      <ul class="nav flex-column sidebar-nav">
        <li class="nav-item">
          <a class="nav-link <?php if($title=='maindash') {echo 'active';} ?> " href="../dashbord.php">
            <i class="bi bi-grid-1x2-fill"></i>
            <span class="nav-link-text ">Dashboard</span>
          </a>
        </li>

        <li class="nav-item">
          <a
            class="nav-link"
            data-bs-toggle="collapse"
            href="#formsCollapse"
            role="button"
            aria-expanded="false"
            aria-controls="formsCollapse"
          >
            <i class="bi bi-journal-text"></i>
            <span class="nav-link-text">Frontend</span>
            <i class="bi bi-chevron-down ms-auto"></i>
          </a>
          <div class="collapse" id="formsCollapse">
            <ul class="nav flex-column ps-4">
              <li class="nav-item ">
                <a class="nav-link <?php if($title=='logo') {echo 'active';} ?>" href=" <?php if($title=='maindash') {echo 'pages/logo_&_brand-name.php';} else {echo 'logo_&_brand-name.php';} ?>">
                  <span class="nav-link-text">Logo Management</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?php if($title=='carosel') {echo 'active';} ?>" href=" <?php if($title=='maindash') {echo 'pages/carousel.php';} else {echo 'carousel.php';} ?>">
                  <span class="nav-link-text">Slider Management</span>
                </a>
              </li>

              <li class="nav-item">
                <a class="nav-link <?php if($title=='view-project') {echo 'active';} ?>" href=" <?php if($title=='maindash') {echo 'pages/view-project.php';} else {echo 'view-project.php';} ?>">
                  <span class="nav-link-text">Project Management</span>
                </a>
              </li>

              <li class="nav-item">
                <a class="nav-link <?php if($title=='view-product') {echo 'active';} ?>" href=" <?php if($title=='maindash') {echo 'pages/view-product.php';} else {echo 'view-product.php';} ?>">
                  <span class="nav-link-text">Product Management</span>
                </a>
              </li>
              
              
            </ul>
          </div>
        </li>
      </ul>
    </nav>
    </div>