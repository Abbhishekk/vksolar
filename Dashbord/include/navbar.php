<header
        class="fixed-header d-flex justify-content-between align-items-center"
      >
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
                src="https://placehold.co/40x40/3C50E0/FFFFFF?text=VK"
                alt="User"
                class="rounded-circle"
                width="40"
                height="40"
              />
              <div class="ms-2 d-none d-md-block text-start">
                <h6 class="mb-0 fw-bold">VK Solar</h6>
                <small class="text-muted">Administrator</small>
              </div>
              <i class="bi bi-chevron-down ms-2"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="#"
                  ><i class="bi bi-person me-2"></i> Profile</a
                >
              </li>
              <li>
                <a class="dropdown-item" href="#"
                  ><i class="bi bi-gear me-2"></i> Settings</a
                >
              </li>
              <li>
                <a class="dropdown-item" href="#"
                  ><i class="bi bi-credit-card me-2"></i> Billing</a
                >
              </li>
              <li><hr class="dropdown-divider" /></li>
              <li>
                <a class="dropdown-item text-danger" href="#"
                  ><i class="bi bi-box-arrow-right me-2"></i> Logout</a
                >
              </li>
            </ul>
          </div>
        </div>
      </header>