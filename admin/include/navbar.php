<?php
// include/navbar.php
//require_once 'connect/auth_middleware.php';
require_once __DIR__ . '/../connect/auth_middleware.php';

$current_user_name = $_SESSION['full_name'] ?? 'User';
$current_role = $_SESSION['role'] ?? '';
?>
  <style>
      /* Enhanced Secondary Navbar CSS */
      .secondary-navbar {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        height: 60px;
        display: flex;
        align-items: center;
        padding: 0 2rem;
        border-bottom: 1px solid #e2e8f0;
        position: fixed;
        width: 100%;
        top: 70px;
        z-index: 99;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      }

      .secondary-navbar.shifted {
        width: calc(100% - var(--sidebar-width));
        margin-left: var(--sidebar-width);
      }

      .secondary-navbar.collapsed {
        width: calc(100% - var(--sidebar-collapsed-width));
        margin-left: var(--sidebar-collapsed-width);
      }

      /* Container for better alignment */
      .nav-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
      }

      .nav-items {
        display: flex;
        align-items: center;
        gap: 0.5rem;
      }

      .nav-item {
        position: relative;
      }

      .nav-link {
        color: #475569;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        background: transparent;
        font-size: 0.95rem;
        white-space: nowrap;
      }

      .nav-link:hover {
        background: #f1f5f9;
        color: var(--primary-color);
      }

      .nav-link.active {
        background: var(--primary-color);
        color: white;
      }

      .nav-link.active:hover {
        background: #e48f0a;
      }

      .nav-icon {
        font-size: 1.1rem;
      }

      .nav-text {
        font-weight: 500;
      }

      .nav-dropdown-arrow {
        font-size: 0.7rem;
        transition: transform 0.3s;
        margin-left: 0.25rem;
      }

      .nav-item.active .nav-dropdown-arrow {
        transform: rotate(180deg);
      }

      /* Enhanced Dropdown Styles */
      .nav-dropdown {
        position: absolute;
        top: calc(100% + 5px);
        left: 0;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        min-width: 280px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        z-index: 1000;
        border: 1px solid #e2e8f0;
        overflow: hidden;
      }

      .nav-dropdown.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
      }

      .dropdown-header {
        padding: 1.25rem 1.5rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
      }

      .dropdown-header h4 {
        font-size: 1.1rem;
        color: var(--text-dark);
        margin-bottom: 0.25rem;
      }

      .dropdown-header p {
        font-size: 0.85rem;
        color: #64748b;
      }

      .nav-dropdown-menu {
        list-style: none;
        padding: 0.75rem 0;
      }

      .nav-dropdown-item {
        padding: 0.85rem 1.5rem;
        color: var(--text-dark);
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 1rem;
      }

      .nav-dropdown-item:hover {
        background: #f1f5f9;
        color: var(--primary-color);
      }

      .nav-dropdown-item .item-icon {
        font-size: 1.2rem;
        width: 24px;
        flex-shrink: 0;
      }

      .nav-dropdown-item .item-text {
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
      }

      .nav-dropdown-item .item-text strong {
        font-weight: 600;
        font-size: 0.95rem;
      }

      .nav-dropdown-item .item-text small {
        color: #64748b;
        font-size: 0.8rem;
      }

      .dropdown-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
      }

      .view-all-link {
        color: var(--secondary-color);
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
      }

      .view-all-link:hover {
        text-decoration: underline;
      }

      /* Additional nav items styling */
      .secondary-nav-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
      }

      .search-box {
        display: flex;
        align-items: center;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        width: 250px;
        transition: all 0.3s;
      }

      .search-box:focus-within {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
      }

      .search-icon {
        color: #94a3b8;
        margin-right: 0.5rem;
      }

      .search-input {
        border: none;
        outline: none;
        width: 100%;
        font-size: 0.9rem;
        color: var(--text-dark);
      }

      .search-input::placeholder {
        color: #94a3b8;
      }

      .quick-action-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 0.75rem 1.25rem;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 0.9rem;
      }

      .quick-action-btn:hover {
        background: #e48f0a;
        transform: translateY(-2px);
      }

      /* Mobile Responsive Styles for Secondary Navbar */
      @media (max-width: 1024px) {
        .search-box {
          width: 200px;
        }

        .nav-link {
          padding: 0.5rem 0.75rem;
          font-size: 0.9rem;
        }
      }

      @media (max-width: 768px) {
        .secondary-navbar {
          padding: 0 1rem;
          height: auto;
          min-height: 50px;
          overflow: visible;
        }

        .nav-container {
          flex-direction: column;
          align-items: stretch;
          gap: 0.75rem;
          padding: 0.5rem 0;
        }

        .nav-items {
          overflow-x: auto;
          padding-bottom: 0.5rem;
          justify-content: flex-start;
        }

        .nav-item {
          flex-shrink: 0;
        }

        .secondary-nav-actions {
          width: 100%;
          justify-content: space-between;
        }

        .search-box {
          width: 60%;
        }

        .quick-action-btn span {
          display: none;
        }

        .quick-action-btn {
          padding: 0.75rem;
          border-radius: 50%;
          width: 42px;
          height: 42px;
          justify-content: center;
        }

        .nav-dropdown {
          position: fixed !important;
          top: 125px !important;
          left: 1rem !important;
          right: 1rem !important;
          width: auto !important;
          max-width: none;
          z-index: 1001 !important;
        }
      }
    </style>
<header class="fixed-header d-flex justify-content-between align-items-center">
  <div class="d-flex align-items-center">
    <!-- Sidebar Toggle Button - Always Visible -->
    <button class="sidebar-toggle-btn me-3" onclick="toggleSidebar()">
      <i class="bi bi-list"></i>
    </button>
    <div class="search-wrapper d-none d-md-flex">
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input
          type="text"
          class="form-control"
          placeholder="Search or type command..."
        />
      </div>
    </div>
  </div>
  <div class="d-flex align-items-center">
 <!-- some managements for only super admin -->
 

      <div class="nav-items">
         <!-- EMPLOYEE MANAGEMENT -->
      <?php if ($auth->checkPermission('employee_management', 'view')): ?>
        <div class="nav-item">
          <a
            href="#"
            class="nav-link fw-bold"
            onclick="toggleNavDropdown(event, 'employee')"
          >
            Employees
            <span class="nav-dropdown-arrow">▼</span>
          </a>
          <div class="nav-dropdown" id="employee-dropdown">
            <ul class="nav-dropdown-menu">
                
               <?php if ($auth->checkPermission('employee_management','create')): ?>
                <li class="text-bold">
                    <a style="text-decoration:none;"class=" nav-dropdown-item "
                 href="/admin/add_employee.php">
                         Add Employee
                        </a>
                </li>
                <?php endif; ?>
                <?php if ($auth->checkPermission('employee_management','view')): ?>
                <li class="">
                    <a style="text-decoration:none;"class=" nav-dropdown-item "
                 href="/admin/view_employees.php">
                 View Employees
                        </a>
                </li>
                <?php endif; ?>
            </ul>
          </div>
        </div>
          <?php endif; ?>
        <!-- Employee Management end -->
        <!-- User Management -->
        <div class="nav-item ">
          <a
            href="#"
            class="nav-link fw-bold"
            onclick="toggleNavDropdown(event, 'users')"
          >
            Users
            <span class="nav-dropdown-arrow">▼</span>
          </a>
          <div class="nav-dropdown" id="users-dropdown">
            <ul class="nav-dropdown-menu">
                <li class="">
                    <a style="text-decoration:none;"class=" nav-dropdown-item <?php if($title=='view_users') echo 'active'; ?>"
                     href="/admin/view_users">
                     View Users
                    </a>
                </li>
                <?php if ($auth->checkPermission('user_management','create')): ?>
                <li class="">
                    <a style="text-decoration:none;"class=" nav-dropdown-item <?php if($title=='add_user') echo 'active'; ?>"
                 href="/admin/add_user">
                         Add User
                        </a>
                </li>
                <?php endif; ?>
                <?php if ($auth->checkPermission('user_management','edit')): ?>
                <li class="">
                    <a style="text-decoration:none;"class=" nav-dropdown-item <?php if($title=='view_permissions') echo 'active'; ?>"
                 href="/admin/view_permissions">
                 User Permissions
                        </a>
                </li>
                <?php endif; ?>
            </ul>
          </div>
        </div>
        <!-- User Management end -->
        <div class="nav-item">
          <a
            href="#"
            class="nav-link fw-bold"
            onclick="toggleNavDropdown(event, 'analytics')"
          >
            Analytics
            <span class="nav-dropdown-arrow">▼</span>
          </a>
          <div class="nav-dropdown" id="analytics-dropdown">
            <ul class="nav-dropdown-menu">
              <li class="nav-dropdown-item">Energy Reports</li>
              <li class="nav-dropdown-item">Performance Metrics</li>
              <li class="nav-dropdown-item">Usage Statistics</li>
              <li class="nav-dropdown-item">Trends Analysis</li>
            </ul>
          </div>
        </div>
        <div class="nav-item">
          <a
            href="#"
            class="nav-link fw-bold"
            onclick="toggleNavDropdown(event, 'reports')"
          >
            Reports
            <span class="nav-dropdown-arrow">▼</span>
          </a>
          <div class="nav-dropdown" id="reports-dropdown">
            <ul class="nav-dropdown-menu">
              <li class="nav-dropdown-item">Monthly Reports</li>
              <li class="nav-dropdown-item">Annual Reports</li>
              <li class="nav-dropdown-item">Custom Reports</li>
              <li class="nav-dropdown-item">Export Data</li>
            </ul>
          </div>
        </div>
      </div>

<!-- some managements for only super admin -->
    <div class="dropdown me-3">
      <a
        href="#"
        class="nav-link position-relative"
        data-bs-toggle="dropdown"
      >
        <i class="bi bi-bell header-icon"></i>
        <span class="notification-badge">3</span>
      </a>
      <ul
        class="dropdown-menu dropdown-menu-end p-2"
        style="width: 300px"
      >
        <li class="d-flex justify-content-between align-items-center p-2">
          <h6 class="mb-0">Notifications</h6>
          <span class="badge bg-primary rounded-pill">3 new</span>
        </li>
        <li><hr class="dropdown-divider" /></li>
        <li>
          <a class="dropdown-item d-flex align-items-center p-2" href="#">
            <div class="flex-shrink-0 me-3">
              <div class="notification-icon bg-primary text-white">
                <i class="bi bi-cart-check"></i>
              </div>
            </div>
            <div class="notification-content">
              <h6 class="mb-0">New order received</h6>
              <small class="text-muted">2 minutes ago</small>
            </div>
          </a>
        </li>
        <li>
          <a class="dropdown-item d-flex align-items-center p-2" href="#">
            <div class="flex-shrink-0 me-3">
              <div class="notification-icon bg-success text-white">
                <i class="bi bi-person-check"></i>
              </div>
            </div>
            <div class="notification-content">
              <h6 class="mb-0">New user registered</h6>
              <small class="text-muted">5 minutes ago</small>
            </div>
          </a>
        </li>
        <li>
          <a class="dropdown-item d-flex align-items-center p-2" href="#">
            <div class="flex-shrink-0 me-3">
              <div class="notification-icon bg-warning text-white">
                <i class="bi bi-exclamation-triangle"></i>
              </div>
            </div>
            <div class="notification-content">
              <h6 class="mb-0">Server alert</h6>
              <small class="text-muted">10 minutes ago</small>
            </div>
          </a>
        </li>
        <li><hr class="dropdown-divider" /></li>
        <li>
          <a class="dropdown-item text-center text-primary" href="#"
            >View all notifications</a
          >
        </li>
      </ul>
    </div>

    <div class="dropdown">
      <button
        class="btn user-dropdown d-flex align-items-center p-1 rounded"
        data-bs-toggle="dropdown"
      >
        <img
          src="https://placehold.co/40x40/3C50E0/FFFFFF?text=<?php echo substr($current_user_name, 0, 2); ?>"
          alt="User"
          class="rounded-circle"
          width="40"
          height="40"
        />
        <div class="ms-2 d-none d-md-block text-start">
          <h6 class="mb-0 fw-bold"><?php echo $current_user_name; ?></h6>
          <small class="text-muted">
            <?php 
            $role_names = [
              'super_admin' => 'Super Administrator',
              'admin' => 'Administrator',
              'office_staff' => 'Office Staff',
              'sales_marketing' => 'Sales Team',
              'warehouse_staff' => 'Warehouse Staff'
            ];
            echo $role_names[$current_role] ?? 'User';
            ?>
          </small>
        </div>
        <i class="bi bi-chevron-down ms-2"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li>
          <a class="dropdown-item" href="view_profile">
            <i class="bi bi-person me-2"></i> Profile
          </a>
        </li>
        <li>
          <a class="dropdown-item" href="settings">
            <i class="bi bi-gear me-2"></i> Settings
          </a>
        </li>
        <li>
          <a class="dropdown-item" href="#">
            <i class="bi bi-credit-card me-2"></i> Billing
          </a>
        </li>
        <li><hr class="dropdown-divider" /></li>
        <li>
          <a class="dropdown-item text-danger" href="/logout.php">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
          </a>
        </li>
      </ul>
    </div>
  </div>
    
    <script>
      // Enhanced JavaScript for Secondary Navbar
      // Toggle navbar dropdown with better functionality
      function toggleNavDropdown(event, dropdownId) {
        event.preventDefault();
        event.stopPropagation();

        const dropdown = document.getElementById(dropdownId + "-dropdown");
        const navItem = dropdown.parentElement;
        const navLink = navItem.querySelector(".nav-link");
        const allDropdowns = document.querySelectorAll(".nav-dropdown");
        const allNavItems = document.querySelectorAll(".nav-item");
        const allNavLinks = document.querySelectorAll(".nav-link");

        // Check if the clicked dropdown is already active
        const isAlreadyActive = dropdown.classList.contains("active");

        // Close all dropdowns and remove active states first
        allDropdowns.forEach((dd) => {
          dd.classList.remove("active");
        });

        allNavItems.forEach((item) => {
          item.classList.remove("active");
        });

        allNavLinks.forEach((link) => {
          link.classList.remove("active");
        });

        // If the clicked dropdown wasn't already active, open it
        if (!isAlreadyActive) {
          dropdown.classList.add("active");
          navItem.classList.add("active");
          navLink.classList.add("active");
        }
      }

      // Close all dropdowns when clicking outside
      document.addEventListener("click", (e) => {
        if (!e.target.closest(".nav-item")) {
          const allDropdowns = document.querySelectorAll(".nav-dropdown");
          const allNavItems = document.querySelectorAll(".nav-item");
          const allNavLinks = document.querySelectorAll(".nav-link");

          allDropdowns.forEach((dropdown) => {
            dropdown.classList.remove("active");
          });

          allNavItems.forEach((item) => {
            item.classList.remove("active");
          });

          // Keep first nav link active when closing dropdowns
          const firstNavLink = document.querySelector(".nav-link");
          if (firstNavLink) {
            allNavLinks.forEach((link) => link.classList.remove("active"));
            firstNavLink.classList.add("active");
          }
        }
      });

      // Close dropdowns on escape key
      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
          const allDropdowns = document.querySelectorAll(".nav-dropdown");
          const allNavItems = document.querySelectorAll(".nav-item");
          const allNavLinks = document.querySelectorAll(".nav-link");

          allDropdowns.forEach((dropdown) => {
            dropdown.classList.remove("active");
          });

          allNavItems.forEach((item) => {
            item.classList.remove("active");
          });

          // Keep first nav link active when closing with escape
          const firstNavLink = document.querySelector(".nav-link");
          if (firstNavLink) {
            allNavLinks.forEach((link) => link.classList.remove("active"));
            firstNavLink.classList.add("active");
          }
        }
      });

      // Handle nav dropdown item clicks
      function handleNavDropdownItemClick(itemText) {
        console.log("Selected:", itemText);
        // Add your custom logic here
        // Example: navigate to a page, filter content, etc.
      }

      // Initialize secondary navbar functionality
      document.addEventListener("DOMContentLoaded", function () {
        // Add active class to first nav item by default
        const firstNavItem = document.querySelector(".nav-item");
        const firstNavLink = document.querySelector(".nav-link");
        if (firstNavItem && firstNavLink) {
          firstNavLink.classList.add("active");
        }

        // Add click handlers to nav dropdown items
        const navDropdownItems =
          document.querySelectorAll(".nav-dropdown-item");
        navDropdownItems.forEach((item) => {
          item.addEventListener("click", function () {
            const itemText = this.textContent || this.innerText;
            handleNavDropdownItemClick(itemText);

            // Close all dropdowns after selection
            document.querySelectorAll(".nav-dropdown").forEach((dropdown) => {
              dropdown.classList.remove("active");
            });
            document.querySelectorAll(".nav-item").forEach((navItem) => {
              navItem.classList.remove("active");
            });

            // Update active nav link to the parent
            const parentLink =
              this.closest(".nav-item").querySelector(".nav-link");
            const allNavLinks = document.querySelectorAll(".nav-link");
            allNavLinks.forEach((link) => link.classList.remove("active"));
            parentLink.classList.add("active");
          });
        });

        // Handle window resize for secondary navbar
        window.addEventListener("resize", function () {
          // Close all dropdowns on mobile when resizing
          if (window.innerWidth <= 768) {
            document.querySelectorAll(".nav-dropdown").forEach((dropdown) => {
              dropdown.classList.remove("active");
            });
            document.querySelectorAll(".nav-item").forEach((item) => {
              item.classList.remove("active");
            });
          }
        });

        // Add smooth hover effects
        const navLinks = document.querySelectorAll(".nav-link");
        navLinks.forEach((link) => {
          link.addEventListener("mouseenter", function () {
            if (!this.classList.contains("active")) {
              this.style.transform = "translateY(-2px)";
            }
          });

          link.addEventListener("mouseleave", function () {
            this.style.transform = "translateY(0)";
          });
        });
      });

      // Update sidebar toggle to also handle secondary navbar
      const menuToggle = document.getElementById("menuToggle");
      const sidebar = document.getElementById("sidebar");
      const navbar = document.getElementById("navbar");
      const secondaryNavbar = document.getElementById("secondaryNavbar");

      if (menuToggle && sidebar && navbar && secondaryNavbar) {
        // Add event listener for sidebar toggle
        menuToggle.addEventListener("click", function () {
          // This is handled in your existing code
          // We're just ensuring secondary navbar also gets the classes
          setTimeout(() => {
            if (sidebar.classList.contains("collapsed")) {
              secondaryNavbar.classList.add("collapsed");
              secondaryNavbar.classList.remove("shifted");
            } else {
              secondaryNavbar.classList.remove("collapsed");
              secondaryNavbar.classList.add("shifted");
            }
          }, 10);
        });
      }

      // Add utility function to check if element is in viewport
      function isElementInViewport(el) {
        const rect = el.getBoundingClientRect();
        return (
          rect.top >= 0 &&
          rect.left >= 0 &&
          rect.bottom <=
            (window.innerHeight || document.documentElement.clientHeight) &&
          rect.right <=
            (window.innerWidth || document.documentElement.clientWidth)
        );
      }

      // Add scroll effect to secondary navbar
      let lastScrollTop = 0;
      window.addEventListener("scroll", function () {
        const currentScroll =
          window.pageYOffset || document.documentElement.scrollTop;

        if (currentScroll > lastScrollTop && currentScroll > 100) {
          // Scrolling down
          secondaryNavbar.style.transform = "translateY(-100%)";
          secondaryNavbar.style.transition = "transform 0.3s ease";
        } else {
          // Scrolling up
          secondaryNavbar.style.transform = "translateY(0)";
        }

        lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
      });
    </script>
</header>