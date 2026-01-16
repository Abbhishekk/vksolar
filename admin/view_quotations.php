<?php
// admin/view_quotations.php
require_once "connect/db.php";
require_once "connect/fun.php";
require_once 'connect/auth_middleware.php';
$auth->requireAuth();
$auth->requireAnyRole(['super_admin', 'admin', 'office_staff', 'sales_marketing']);
$auth->checkPermission('quotation_management', 'view');

$auth->requirePermission('quotation_management', 'view');
$title = "View Quotations";

// Get current user info for role-based filtering with safety checks
$current_user_id = $_SESSION['user_id'] ?? 0;
$current_user_role = $_SESSION['user_role'] ?? 'guest';

// Validate session data
if ($current_user_id <= 0) {
    header("Location: login.php");
    exit();
}

// If role is not set in session, try to get from database
if ($current_user_role === 'guest') {
    require_once 'connect/db.php';
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $current_user_role = $user['role'];
        $_SESSION['user_role'] = $current_user_role;
    } else {
        // User not found in database, logout
        session_destroy();
        header("Location: login.php");
        exit();
    }
}

$qobj = new quote($conn);
$quoteStats = $qobj->getQuotationStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require('include/head.php'); ?>
  <title>View Quotations - VK Solar Energy</title>
  <style>
    :root {
      --primary-color: #2c5aa0;
      --success-color: #28a745;
      --warning-color: #ffc107;
      --danger-color: #dc3545;
      --info-color: #17a2b8;
      --secondary-color: #6c757d;
    }
    
    .status-badge {
        padding: 0.35rem 0.65rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid transparent;
    }
    .status-draft { background: #fff3cd; color: #856404; border-color: #ffeaa7; }
    .status-sent { background: #cce7ff; color: #004085; border-color: #b3d7ff; }
    .status-viewed { background: #d1ecf1; color: #0c5460; border-color: #bee5eb; }
    .status-negotiation { background: #e2e3ff; color: #383d41; border-color: #d6d8db; }
    .status-accepted { background: #d4edda; color: #155724; border-color: #c3e6cb; }
    .status-rejected { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }
    
    .action-btn {
        padding: 0.3rem 0.6rem;
        font-size: 0.8rem;
        border-radius: 5px;
        transition: all 0.2s ease;
    }
    
    .action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .table-card {
        border: none;
        box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        overflow: hidden;
    }
    
    .filter-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid #e3e6f0;
    }
    
    .stat-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }
    
    .customer-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1rem;
    }
    
    .amount-badge {
        background: #f8f9fa;
        border: 1px solid #e3e6f0;
        border-radius: 6px;
        padding: 0.25rem 0.5rem;
        font-weight: 600;
        color: #2c5aa0;
    }
    
    .quotation-row {
        transition: background-color 0.2s ease;
    }
    
    .quotation-row:hover {
        background-color: #f8f9fa !important;
    }
    
    .export-btn {
        background: var(--primary-color);
        border: none;
        border-radius: 6px;
        padding: 0.5rem 1rem;
        color: white;
        transition: all 0.2s ease;
    }
    
    .export-btn:hover {
        background: #1e4a8a;
        transform: translateY(-1px);
    }
    
    .pagination-container {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1rem;
    }
  </style>
</head>
<body class="bg-light">
  <!-- Sidebar -->
  <?php require('include/sidebar.php') ?>

  <!-- Main Content -->
  <div id="main-content">
    <div class="sidebar-overlay"></div>

    <!-- Fixed Header -->
    <?php require('include/navbar.php') ?>

    <!-- Main Content -->
    <main class="py-4">
      <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h1 class="h3 mb-2 text-gray-800">Quotation Management</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="./" class="text-decoration-none"><i class="bi bi-house-door"></i> Dashboard</a></li>
                <li class="breadcrumb-item active text-gray-600">View Quotations</li>
              </ol>
            </nav>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="export-btn" onclick="exportQuotations()">
              <i class="bi bi-download me-2"></i>Export
            </button>
            <?php if ($auth->checkPermission('quotation_management', 'create')): ?>
            <a href="quotation_generator" class="btn btn-success px-3">
              <i class="bi bi-plus-circle me-2"></i>Create New Quotation
            </a>
            <?php endif; ?>
          </div>
        </div>

        <!-- Message Alert -->
        <div id="messageAlert" class="alert d-none alert-dismissible fade show">
          <i id="messageIcon" class="bi me-2"></i>
          <span id="messageText"></span>
          <button type="button" class="btn-close" onclick="hideMessage()"></button>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
          <div class=" col-md-4 col-6 mb-4">
            <div class="card stat-card border-left-primary">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div>
                    <div class="text-primary font-weight-bold">Total</div>
                    <h2 id="totalQuotations" class="mb-0"><?= $quoteStats['total']; ?></h2>
                  </div>
                  <div class="align-self-center">
                    <i class="bi bi-file-text fs-3 text-primary opacity-50"></i>
                  </div>
                </div>
                <small class="text-muted">All quotations</small>
              </div>
            </div>
          </div>
          <div class=" col-md-4 col-6 mb-4">
            <div class="card stat-card border-left-success">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div>
                    <div class="text-success font-weight-bold">Accepted</div>
                    <h2 id="acceptedQuotations" class="mb-0"><?= $quoteStats['accepted']; ?></h2>
                  </div>
                  <div class="align-self-center">
                    <i class="bi bi-check-circle fs-3 text-success opacity-50"></i>
                  </div>
                </div>
                <small class="text-muted">Approved quotes</small>
              </div>
            </div>
          </div>
          <div class=" col-md-4 col-6 mb-4">
            <div class="card stat-card border-left-warning">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div>
                    <div class="text-warning font-weight-bold">Pending</div>
                    <h2 id="pendingQuotations" class="mb-0"><?= $quoteStats['pending']; ?></h2>
                  </div>
                  <div class="align-self-center">
                    <i class="bi bi-clock fs-3 text-warning opacity-50"></i>
                  </div>
                </div>
                <small class="text-muted">Awaiting response</small>
              </div>
            </div>
          </div>
          <div class=" col-md-4 col-6 mb-4">
            <div class="card stat-card border-left-danger">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div>
                    <div class="text-danger font-weight-bold">Rejected</div>
                    <h2 id="rejectedQuotations" class="mb-0"><?= $quoteStats['declined']; ?></h2>
                  </div>
                  <div class="align-self-center">
                    <i class="bi bi-x-circle fs-3 text-danger opacity-50"></i>
                  </div>
                </div>
                <small class="text-muted">Declined quotes</small>
              </div>
            </div>
          </div>
          <div class=" col-md-4 col-6 mb-4">
            <div class="card stat-card border-left-info">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div>
                    <div class="text-info font-weight-bold">Awaiting Response</div>
                    <h2 id="myQuotations" class="mb-0"><?= $quoteStats['awaiting']; ?></h2>
                  </div>
                  <div class="align-self-center">
                    <i class="bi bi-person fs-3 text-info opacity-50"></i>
                  </div>
                </div>
                <small class="text-muted">Awaiting Response</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters Section -->
        <div class="filter-section">
          <h5 class="mb-3"><i class="bi bi-funnel"></i> Filter Quotations</h5>
          <div class="row g-3 align-items-end">
            <div class="col-lg-2 col-md-4">
              <label for="statusFilter" class="form-label fw-semibold">Status</label>
              <select class="form-select shadow-sm" id="statusFilter">
                <option value="">All Status</option>
                <option value="draft">Draft</option>
                <option value="sent">Sent</option>
                <option value="viewed">Viewed</option>
                <option value="negotiation">Negotiation</option>
                <option value="accepted">Accepted</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>
            <?php if (in_array($current_user_role, ['super_admin', 'admin', 'office_staff'])): ?>
            <div class="col-lg-2 col-md-4">
              <label for="userFilter" class="form-label fw-semibold">Sales Person</label>
              <select class="form-select shadow-sm" id="userFilter">
                <option value="">All Users</option>
                <!-- Will be populated by JavaScript -->
              </select>
            </div>
            <?php endif; ?>
            <div class="col-lg-2 col-md-4">
              <label for="dateFrom" class="form-label fw-semibold">From Date</label>
              <input type="date" class="form-control shadow-sm" id="dateFrom">
            </div>
            <div class="col-lg-2 col-md-4">
              <label for="dateTo" class="form-label fw-semibold">To Date</label>
              <input type="date" class="form-control shadow-sm" id="dateTo">
            </div>
            <div class="col-lg-3 col-md-6">
              <label for="searchInput" class="form-label fw-semibold">Search</label>
              <input type="text" class="form-control shadow-sm" id="searchInput" placeholder="Customer name, phone, quotation no...">
            </div>
            <div class="col-lg-1 col-md-3">
              <button type="button" class="btn btn-outline-secondary w-100 h-100" id="resetFilters" title="Reset Filters">
                <i class="bi bi-arrow-clockwise"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Quotations Table -->
        <div class="card mt-4">  <!-- outer card already matches theme -->
    <div class="card-header">
        <h2 class="text-center text-white fw-bold">Solar Rooftop Quotations</h2>
    </div>

    <div class="card-body p-3" style="overflow:auto">
        <table class="table table-bordered text-center align-middle quotation-table">
            <thead class="table-primary fw-bold">  <!-- Keep your color theme untouched -->
                <tr>
                    <th>Customer ID</th>
                    <th>Customer Name</th>
                    <th>Quotation ID</th>
                    <th>Amount (₹)</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Generate</th>
                    <th>Transfer</th>
                    <th colspan='2'>Actions</th>
                </tr>
            </thead>

            <tbody>
            <?php
            $result = $conn->query("SELECT quotation_id, customer_name, quote_number, final_cost, created_date, status FROM solar_rooftop_quotations ORDER BY created_date DESC");


$colors = [
    'sent'        => '#1C39BB', // Persian Blue (close standard hex)
    'approved'    => 'green',
    'declined'    => 'red',
    'under_review'=> 'yellow'
];

            while ($row = $result->fetch_assoc()):
                $status = $row['status'] ?? '';
                $color = $colors[$status] ?? '';
            ?>
                <tr>
                    <td><?= $row['quotation_id'] ?></td>
                    <td><?= $row['customer_name'] ?></td>
                    <td><?= $row['quote_number'] ?></td>
                    <td><?= number_format($row['final_cost'], 0) ?></td>
                    <td><?= date('d-m-Y', strtotime($row['created_date'])) ?></td>
                    <td style="background:<?php echo $color; ?>; color:#fff; font-weight:bold; text-align:center;"><?= ucfirst(str_replace('_',' ', $row['status'])) ?></td>

                    <!-- Buttons -->
                    <td>
                        <a href="merged_template.php?quote_id=<?= $row['quotation_id']; ?>" 
                           class="btn btn-success btn-xs p-0" target="_blank">
                            Generate Quotation
                        </a>
                    </td>
                    <td>
                        <a href="transfer_to_customer.php?quote_id=<?= $row['quotation_id'] ?>" class="btn btn-warning btn-sm">Transfer</a>
                    </td>

                    <!-- Action buttons -->
                    <td colspan='2'>
                        <div class="d-flex justify-content-center gap-2">
                            
                            <!-- VIEW (Eye icon) -->
                            <a href="view_quotation_details.php?quote_id=<?= $row['quotation_id']; ?>"
                               class="btn btn-primary btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                    
                            <!-- EDIT (Pencil icon) -->
                            <a href="edit_quotation.php?quote_id=<?= $row['quotation_id']; ?>"
                               class="btn btn-info btn-sm">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                    
                            <!-- DELETE (Trash icon) -->
                            <a href="delete_quotation.php?quote_id=<?= $row['quotation_id']; ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Delete this quotation permanently?')">
                                <i class="bi bi-trash"></i>
                            </a>
                    
                        </div>
</td>

                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

      </div>
    </main>
  </div>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Global variables
    let allQuotations = [];
    let filteredQuotations = [];
    let allUsers = [];
    let currentPage = 1;
    const itemsPerPage = 10;

    document.addEventListener('DOMContentLoaded', function() {
      loadQuotations();
      loadUsers();
      setupEventListeners();
    });

    function setupEventListeners() {
      const statusFilter = document.getElementById('statusFilter');
      const userFilter = document.getElementById('userFilter');
      const searchInput = document.getElementById('searchInput');
      const dateFrom = document.getElementById('dateFrom');
      const dateTo = document.getElementById('dateTo');
      const resetFilters = document.getElementById('resetFilters');

      if (statusFilter) statusFilter.addEventListener('change', filterQuotations);
      if (userFilter) userFilter.addEventListener('change', filterQuotations);
      if (searchInput) searchInput.addEventListener('input', debounce(filterQuotations, 300));
      if (dateFrom) dateFrom.addEventListener('change', filterQuotations);
      if (dateTo) dateTo.addEventListener('change', filterQuotations);
      if (resetFilters) resetFilters.addEventListener('click', resetAllFilters);
    }

    function debounce(func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    }

    function loadQuotations() {
      showLoadingState();
      
      const params = new URLSearchParams();
      const statusValue = document.getElementById('statusFilter')?.value || '';
      const userValue = document.getElementById('userFilter')?.value || '';
      const searchValue = document.getElementById('searchInput')?.value || '';
      const dateFromValue = document.getElementById('dateFrom')?.value || '';
      const dateToValue = document.getElementById('dateTo')?.value || '';
      
      if (statusValue) params.append('status', statusValue);
      if (userValue) params.append('user_id', userValue);
      if (searchValue) params.append('search', searchValue);
      if (dateFromValue) params.append('date_from', dateFromValue);
      if (dateToValue) params.append('date_to', dateToValue);
      
      const queryString = params.toString();
      const url = `api/quotation_api.php?action=get_quotations&${queryString}`;
      
      fetch(url)
        .then(response => {
          if (!response.ok) throw new Error('Network response was not ok');
          return response.json();
        })
        .then(data => {
          if (data.success) {
            allQuotations = data.data || [];
            filteredQuotations = [...allQuotations];
            updateStatistics();
            renderQuotations();
            updatePagination();
            hideLoadingState();
          } else {
            throw new Error(data.message || 'Failed to load quotations');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showMessage('error', 'Failed to load quotations: ' + error.message);
          hideLoadingState();
        });
    }

    function loadUsers() {
      const userFilter = document.getElementById('userFilter');
      if (!userFilter) return;
      
      fetch('api/user_api.php?action=get_users')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            allUsers = data.data || [];
            populateUserFilter();
          }
        })
        .catch(error => {
          console.error('Error loading users:', error);
        });
    }

    function populateUserFilter() {
      const userFilter = document.getElementById('userFilter');
      if (!userFilter) return;
      
      const salesUsers = allUsers.filter(user => 
        ['sales_marketing', 'admin', 'super_admin'].includes(user.role)
      );
      
      userFilter.innerHTML = '<option value="">All Users</option>';
      salesUsers.forEach(user => {
        userFilter.innerHTML += `<option value="${user.id}">${user.full_name || user.username} (${formatRole(user.role)})</option>`;
      });
    }

    function updateStatistics() {
      const total = allQuotations.length;
      const accepted = allQuotations.filter(q => q.status === 'accepted').length;
      const pending = allQuotations.filter(q => 
        ['draft', 'sent', 'viewed', 'negotiation'].includes(q.status)
      ).length;
      const rejected = allQuotations.filter(q => q.status === 'rejected').length;
      
      const myQuotations = allQuotations.filter(q => q.created_by == <?php echo $current_user_id; ?>).length;
      const myAccepted = allQuotations.filter(q => 
        q.status === 'accepted' && q.created_by == <?php echo $current_user_id; ?>
      ).length;
      
      const conversionRate = myQuotations > 0 ? ((myAccepted / myQuotations) * 100).toFixed(1) : 0;

      document.getElementById('totalQuotations').textContent = total.toLocaleString();
      document.getElementById('acceptedQuotations').textContent = accepted.toLocaleString();
      document.getElementById('pendingQuotations').textContent = pending.toLocaleString();
      document.getElementById('rejectedQuotations').textContent = rejected.toLocaleString();
      document.getElementById('myQuotations').textContent = myQuotations.toLocaleString();
      document.getElementById('conversionRate').textContent = conversionRate + '%';
      
      document.getElementById('totalCount').textContent = total.toLocaleString();
    }

    function filterQuotations() {
      const statusValue = document.getElementById('statusFilter')?.value || '';
      const userValue = document.getElementById('userFilter')?.value || '';
      const searchValue = (document.getElementById('searchInput')?.value || '').toLowerCase();
      const dateFromValue = document.getElementById('dateFrom')?.value || '';
      const dateToValue = document.getElementById('dateTo')?.value || '';

      filteredQuotations = allQuotations.filter(quotation => {
        // Status filter
        const matchesStatus = !statusValue || quotation.status === statusValue;
        
        // User filter
        let matchesUser = true;
        if (userValue) {
          matchesUser = quotation.created_by == userValue;
        } else if ('<?php echo $current_user_role; ?>' === 'sales_marketing') {
          matchesUser = quotation.created_by == <?php echo $current_user_id; ?>;
        }
        
        // Search filter
        const matchesSearch = !searchValue || 
          (quotation.customer_name && quotation.customer_name.toLowerCase().includes(searchValue)) ||
          (quotation.customer_phone && quotation.customer_phone.includes(searchValue)) ||
          (quotation.quotation_number && quotation.quotation_number.toLowerCase().includes(searchValue));
        
        // Date filter
        let matchesDate = true;
        if (dateFromValue || dateToValue) {
          const createdDate = new Date(quotation.created_at).toISOString().split('T')[0];
          if (dateFromValue && createdDate < dateFromValue) matchesDate = false;
          if (dateToValue && createdDate > dateToValue) matchesDate = false;
        }

        return matchesStatus && matchesUser && matchesSearch && matchesDate;
      });

      currentPage = 1;
      renderQuotations();
      updatePagination();
    }

    function renderQuotations() {
      const tableBody = document.getElementById('quotationsTableBody');
      const emptyState = document.getElementById('emptyState');
      const filteredCount = document.getElementById('filteredCount');
      
      filteredCount.textContent = filteredQuotations.length.toLocaleString();

      if (filteredQuotations.length === 0) {
        tableBody.innerHTML = '';
        emptyState.classList.remove('d-none');
        return;
      }

      emptyState.classList.add('d-none');
      
      // Calculate pagination
      const startIndex = (currentPage - 1) * itemsPerPage;
      const endIndex = Math.min(startIndex + itemsPerPage, filteredQuotations.length);
      const currentItems = filteredQuotations.slice(startIndex, endIndex);

      tableBody.innerHTML = currentItems.map(quotation => `
        <tr class="quotation-row align-middle">
          <td class="ps-4">
            <div class="fw-bold text-primary">${escapeHtml(quotation.quotation_number)}</div>
            <small class="text-muted">ID: ${quotation.id}</small>
          </td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="customer-avatar">
                ${getInitials(quotation.customer_name)}
              </div>
              <div>
                <div class="fw-semibold">${escapeHtml(quotation.customer_name)}</div>
                <small class="text-muted">${escapeHtml(quotation.property_type || 'N/A')}</small>
              </div>
            </div>
          </td>
          <td>
            <div class="fw-medium">${escapeHtml(quotation.customer_phone)}</div>
            <small class="text-muted">${escapeHtml(quotation.customer_email || 'No email')}</small>
          </td>
          <td>
            <span class="fw-bold">${quotation.system_size || '0'} kW</span>
          </td>
          <td>
            <span class="amount-badge">₹${parseFloat(quotation.final_cost || 0).toLocaleString('en-IN')}</span>
          </td>
          <td>
            <span class="status-badge status-${quotation.status}">
              ${formatStatus(quotation.status)}
            </span>
          </td>
          <td>
            <div class="small">
              <div class="fw-medium">${escapeHtml(quotation.creator_name || 'Unknown')}</div>
              <span class="text-muted">${formatRole(quotation.creator_role)}</span>
            </div>
          </td>
          <td>
            <div class="small">
              <div>${formatDate(quotation.created_at)}</div>
              <div class="text-muted">${formatTime(quotation.created_at)}</div>
            </div>
          </td>
          <td class="text-center pe-4">
            <div class="btn-group btn-group-sm" role="group">
              <button type="button" class="btn btn-outline-primary action-btn" onclick="viewQuotation(${quotation.id})" title="View Details">
                <i class="bi bi-eye"></i>
              </button>
              <button type="button" class="btn btn-outline-success action-btn" onclick="editQuotation(${quotation.id})" title="Edit">
                <i class="bi bi-pencil"></i>
              </button>
              <?php if ($auth->checkPermission('quotation_management', 'edit')): ?>
              <button type="button" class="btn btn-outline-warning action-btn" onclick="changeStatus(${quotation.id}, '${quotation.status}')" title="Change Status">
                <i class="bi bi-arrow-repeat"></i>
              </button>
              <?php endif; ?>
              <?php if (in_array($current_user_role, ['admin', 'super_admin', 'office_staff']) && $auth->checkPermission('quotation_management', 'delete')): ?>
              <button type="button" class="btn btn-outline-danger action-btn" onclick="deleteQuotation(${quotation.id})" title="Delete">
                <i class="bi bi-trash"></i>
              </button>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      `).join('');
    }

    function updatePagination() {
      const pagination = document.getElementById('pagination');
      const pageStart = document.getElementById('pageStart');
      const pageEnd = document.getElementById('pageEnd');
      const totalRecords = document.getElementById('totalRecords');
      
      if (!pagination) return;
      
      const totalPages = Math.ceil(filteredQuotations.length / itemsPerPage);
      const startIndex = (currentPage - 1) * itemsPerPage;
      const endIndex = Math.min(startIndex + itemsPerPage, filteredQuotations.length);
      
      pageStart.textContent = (startIndex + 1).toLocaleString();
      pageEnd.textContent = endIndex.toLocaleString();
      totalRecords.textContent = filteredQuotations.length.toLocaleString();
      
      let paginationHTML = '';
      
      // Previous button
      paginationHTML += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
          <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Previous</a>
        </li>
      `;
      
      // Page numbers
      for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
          paginationHTML += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
              <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
            </li>
          `;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
          paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
      }
      
      // Next button
      paginationHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
          <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Next</a>
        </li>
      `;
      
      pagination.innerHTML = paginationHTML;
    }

    function changePage(page) {
      if (page < 1 || page > Math.ceil(filteredQuotations.length / itemsPerPage)) return;
      currentPage = page;
      renderQuotations();
      updatePagination();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetAllFilters() {
      document.getElementById('statusFilter').value = '';
      if (document.getElementById('userFilter')) {
        document.getElementById('userFilter').value = '';
      }
      document.getElementById('searchInput').value = '';
      document.getElementById('dateFrom').value = '';
      document.getElementById('dateTo').value = '';
      filterQuotations();
    }

    function refreshData() {
      loadQuotations();
      showMessage('info', 'Refreshing data...');
    }

    function showLoadingState() {
      document.getElementById('loadingState').style.display = 'block';
      document.getElementById('emptyState').classList.add('d-none');
      document.getElementById('quotationsTableBody').innerHTML = '';
    }

    function hideLoadingState() {
      document.getElementById('loadingState').style.display = 'none';
    }

    // Utility functions
    function escapeHtml(unsafe) {
      if (unsafe === null || unsafe === undefined) return '';
      return unsafe
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }

    function getInitials(name) {
      if (!name) return '?';
      return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
    }

    function formatStatus(status) {
      const statusMap = {
        'draft': 'Draft',
        'sent': 'Sent',
        'viewed': 'Viewed',
        'negotiation': 'Negotiation',
        'accepted': 'Accepted',
        'rejected': 'Rejected'
      };
      return statusMap[status] || status;
    }

    function formatRole(role) {
      const roleMap = {
        'super_admin': 'Super Admin',
        'admin': 'Admin',
        'office_staff': 'Office Staff',
        'sales_marketing': 'Sales',
        'warehouse_staff': 'Warehouse'
      };
      return roleMap[role] || role;
    }

    function formatDate(dateString) {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-IN');
    }

    function formatTime(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
    }

    function showMessage(type, message) {
      const messageAlert = document.getElementById('messageAlert');
      const messageIcon = document.getElementById('messageIcon');
      const messageText = document.getElementById('messageText');
      
      const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
      }[type] || 'alert-info';
      
      const iconClass = {
        'success': 'bi-check-circle-fill',
        'error': 'bi-exclamation-triangle-fill',
        'warning': 'bi-exclamation-triangle-fill',
        'info': 'bi-info-circle-fill'
      }[type] || 'bi-info-circle-fill';
      
      messageAlert.className = `alert ${alertClass} d-block`;
      messageIcon.className = `bi ${iconClass} me-2`;
      messageText.textContent = message;
      
      messageAlert.classList.remove('d-none');
    }

    function hideMessage() {
      document.getElementById('messageAlert').classList.add('d-none');
    }

    // Action functions
    window.viewQuotation = function(quotationId) {
      window.location.href = `view_quotation_details.php?id=${quotationId}`;
    };

    window.editQuotation = function(quotationId) {
      window.location.href = `edit_quotation.php?id=${quotationId}`;
    };

    window.changeStatus = function(quotationId, currentStatus) {
      const newStatus = prompt(`Change quotation status from ${formatStatus(currentStatus)} to:\n\nOptions: draft, sent, viewed, negotiation, accepted, rejected`, currentStatus);
      
      if (newStatus && newStatus !== currentStatus) {
        const notes = prompt('Add notes (optional):');
        
        const statusData = {
          action: 'update_quotation_status',
          quotation_id: quotationId,
          new_status: newStatus,
          notes: notes || ''
        };

        fetch('api/quotation_api.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(statusData)
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showMessage('success', data.message);
            loadQuotations();
          } else {
            showMessage('error', data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showMessage('error', 'Failed to update quotation status');
        });
      }
    };

    window.deleteQuotation = function(quotationId) {
      if (confirm('Are you sure you want to delete this quotation?\n\nThis action cannot be undone.')) {
        const deleteData = {
          action: 'delete_quotation',
          quotation_id: quotationId
        };

        fetch('api/quotation_api.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(deleteData)
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showMessage('success', data.message);
            loadQuotations();
          } else {
            showMessage('error', data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showMessage('error', 'Failed to delete quotation');
        });
      }
    };

    window.transferToCustomer = function(quotationId) {
      if (confirm('Are you sure you want to transfer this quotation to permanent customers?\n\nThis will create a new customer record and cannot be undone.')) {
        const transferData = {
          action: 'transfer_to_customer',
          quotation_id: quotationId
        };

        fetch('api/quotation_api.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(transferData)
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showMessage('success', `${data.message} - Customer: ${data.customer_name}`);
            loadQuotations();
          } else {
            showMessage('error', data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showMessage('error', 'Failed to transfer to customer');
        });
      }
    };

    window.exportQuotations = function() {
      showMessage('info', 'Preparing export...');
      // Implement export functionality here
      setTimeout(() => {
        showMessage('warning', 'Export feature coming soon!');
      }, 1000);
    };
  </script>
</body>
</html>