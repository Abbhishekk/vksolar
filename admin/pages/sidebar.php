<?php
// pages/sidebar.php
require_once '../admin/connect/auth_middleware.php';
$current_role = $_SESSION['role'] ?? '';
$current_user_name = $_SESSION['full_name'] ?? 'User';
?>
<div>
  <nav id="sidebar">
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
        <a class="nav-link <?php if($title=='maindash') {echo 'active';} ?>" href="../dashboard">
          <i class="bi bi-grid-1x2-fill"></i>
          <span class="nav-link-text">Dashboard</span>
        </a>
      </li>

      <!-- Frontend Management - Check Permission -->
      <?php if ($auth->checkPermission('frontend_management', 'view')): ?>
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
            <?php if ($auth->checkPermission('logo_management', 'view')): ?>
            <li class="nav-item ">
              <a class="nav-link <?php if($title=='logo') {echo 'active';} ?>" href=" <?php if($title=='maindash') {echo 'pages/logo_&_brand-name.php';} else {echo 'logo_&_brand-name.php';} ?>">
                <span class="nav-link-text">Logo Management</span>
              </a>
            </li>
            <?php endif; ?>

            <?php if ($auth->checkPermission('slider_management', 'view')): ?>
            <li class="nav-item">
              <a class="nav-link <?php if($title=='carosel') {echo 'active';} ?>" href=" <?php if($title=='maindash') {echo 'pages/carousel.php';} else {echo 'carousel.php';} ?>">
                <span class="nav-link-text">Slider Management</span>
              </a>
            </li>
            <?php endif; ?>

            <?php if ($auth->checkPermission('project_management', 'view')): ?>
            <li class="nav-item">
              <a class="nav-link <?php if($title=='view-project') {echo 'active';} ?>" href=" <?php if($title=='maindash') {echo 'pages/view-project.php';} else {echo 'view-project.php';} ?>">
                <span class="nav-link-text">Project Management</span>
              </a>
            </li>
            <?php endif; ?>

            <?php if ($auth->checkPermission('product_management', 'view')): ?>
            <li class="nav-item">
              <a class="nav-link <?php if($title=='view-product') {echo 'active';} ?>" href=" <?php if($title=='maindash') {echo 'pages/view-product.php';} else {echo 'view-product.php';} ?>">
                <span class="nav-link-text">Product Management</span>
              </a>
            </li>
            <?php endif; ?>
          </ul>
        </div>
      </li>
      <?php endif; ?>

      <!-- Additional Modules with Role-based Access -->
      
      <!-- Customer Management -->
      <?php if ($auth->checkPermission('customer_management', 'view')): ?>
      <li class="nav-item">
        <a class="nav-link" href="../customer-management">
          <i class="bi bi-people-fill"></i>
          <span class="nav-link-text">Customer Management</span>
        </a>
      </li>
      <?php endif; ?>

      <!-- Quotation Management -->
      <?php if ($auth->checkPermission('quotation_management', 'view')): ?>
      <li class="nav-item">
        <a class="nav-link" href="../quotation-management">
          <i class="bi bi-file-earmark-text"></i>
          <span class="nav-link-text">Quotation Management</span>
        </a>
      </li>
      <?php endif; ?>

      <!-- Inventory Management -->
      <?php if ($auth->checkPermission('inventory_management', 'view')): ?>
      <li class="nav-item">
        <a class="nav-link" href="../inventory-management">
          <i class="bi bi-box-seam"></i>
          <span class="nav-link-text">Inventory Management</span>
        </a>
      </li>
      <?php endif; ?>

      <!-- Employee Management -->
      <?php if ($auth->checkPermission('employee_management', 'view')): ?>
      <li class="nav-item">
        <a class="nav-link" href="../employee-management">
          <i class="bi bi-person-badge"></i>
          <span class="nav-link-text">Employee Management</span>
        </a>
      </li>
      <?php endif; ?>

      <!-- Reports -->
      <?php if ($auth->checkPermission('reports', 'view')): ?>
      <li class="nav-item">
        <a class="nav-link" href="../reports">
          <i class="bi bi-graph-up"></i>
          <span class="nav-link-text">Reports & Analytics</span>
        </a>
      </li>
      <?php endif; ?>

      <!-- Settings - Only for Admin roles -->
      <?php if ($auth->checkPermission('settings', 'view')): ?>
      <li class="nav-item">
        <a class="nav-link" href="../settings">
          <i class="bi bi-gear"></i>
          <span class="nav-link-text">Settings</span>
        </a>
      </li>
      <?php endif; ?>
    </ul>

    <!-- User Info in Sidebar Footer -->
    <div class="sidebar-footer mt-auto">
      <div class="user-info-sidebar">
        <div class="user-avatar-sidebar">
          <i class="bi bi-person-circle"></i>
        </div>
        <div class="user-details-sidebar">
          <div class="user-name-sidebar"><?php echo $current_user_name; ?></div>
          <div class="user-role-sidebar">
            <?php 
            $role_names = [
              'super_admin' => 'Super Admin',
              'admin' => 'Admin',
              'office_staff' => 'Office Staff',
              'sales_marketing' => 'Sales Team',
              'warehouse_staff' => 'Warehouse Staff'
            ];
            echo $role_names[$current_role] ?? 'User';
            ?>
          </div>
        </div>
      </div>
    </div>
  </nav>
</div>