<?php
// admin/include/sidebar.php
require_once __DIR__ . '/../connect/auth_middleware.php';

$current_role = $_SESSION['role'] ?? '';
$current_user_name = $_SESSION['full_name'] ?? 'User';
?>

<div>
  <nav id="sidebar">

    <div class="sidebar-logo">
      <i class="bi bi-sun-fill"></i>
      <span class="sidebar-logo-text">VK Solar</span>
    </div>

    <!-- MOBILE CLOSE BTN -->
    <button class="sidebar-close-btn" onclick="closeSidebar()">
      <i class="bi bi-x"></i>
    </button>

    <ul class="nav flex-column sidebar-nav">

      <!-- DASHBOARD -->
      <li class="nav-item">
        <a class="nav-link <?php if($title=='maindash') echo 'active'; ?>" 
           href="/admin/">
          <i class="bi bi-grid-1x2-fill"></i>
          <span class="nav-link-text">Dashboard</span>
        </a>
      </li>

      <!-- FRONTEND -->
      <?php if ($auth->checkPermission('frontend_management','view')): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#frontendCollapse">
          <i class="bi bi-journal-text"></i>
          <span class="nav-link-text">Frontend</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>

        <div class="collapse" id="frontendCollapse">
          <ul class="nav flex-column ps-4">

            <?php if ($auth->checkPermission('logo_management','view')): ?>
            <li class="nav-item">
              <a class="nav-link <?php if($title=='logo') echo 'active'; ?>" 
                 href="/admin/pages/logo_&_brand-name.php">
                Logo Management
              </a>
            </li>
            <?php endif; ?>

            <?php if ($auth->checkPermission('slider_management','view')): ?>
            <li class="nav-item">
              <a class="nav-link <?php if($title=='carosel') echo 'active'; ?>" 
                 href="/admin/pages/carousel.php">
                Slider Management
              </a>
            </li>
            <?php endif; ?>

            <?php if ($auth->checkPermission('project_management','view')): ?>
            <li class="nav-item">
              <a class="nav-link <?php if($title=='view-project') echo 'active'; ?>" 
                 href="/admin/pages/view-project.php">
                Project Management
              </a>
            </li>
            <?php endif; ?>

            <?php if ($auth->checkPermission('product_management','view')): ?>
            <li class="nav-item">
              <a class="nav-link <?php if($title=='view-product') echo 'active'; ?>" 
                 href="/admin/pages/view-product.php">
                Product Management
              </a>
            </li>
            <?php endif; ?>

          </ul>
        </div>
      </li>
      <?php endif; ?>

      <!-- USER MANAGEMENT -->
      <?php if ($auth->checkPermission('user_management','view')): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#userCollapse">
          <i class="bi bi-people"></i>
          <span class="nav-link-text">User Management</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <div class="collapse" id="userCollapse">
          <ul class="nav flex-column ps-4">

            <li class="nav-item">
              <a class="nav-link <?php if($title=='view_users') echo 'active'; ?>"
                 href="/admin/view_users">
                 View Users
              </a>
            </li>

            <?php if ($auth->checkPermission('user_management','create')): ?>
            <li class="nav-item">
              <a class="nav-link <?php if($title=='add_user') echo 'active'; ?>"
                 href="/admin/add_user">
                 Add User
              </a>
            </li>
            <?php endif; ?>

            <?php if ($auth->checkPermission('user_management','edit')): ?>
            <li class="nav-item">
              <a class="nav-link <?php if($title=='view_permissions') echo 'active'; ?>"
                 href="/admin/view_permissions">
                 User Permissions
              </a>
            </li>
            <?php endif; ?>

          </ul>
        </div>
      </li>
      <?php endif; ?>

      <!-- MY PROFILE -->
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#profileCollapse">
          <i class="bi bi-person"></i>
          <span class="nav-link-text">My Profile</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <div class="collapse" id="profileCollapse">
          <ul class="nav flex-column ps-4">
            <li class="nav-item">
              <a class="nav-link <?php if($title=='view_profile') echo 'active'; ?>"
                 href="/admin/view_profile">
                View Profile
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php if($title=='edit_profile') echo 'active'; ?>"
                 href="/admin/edit_profile">
                Edit Profile
              </a>
            </li>
          </ul>
        </div>
      </li>
      
       <!--Bank Details -->
      <?php if ($auth->checkPermission('bank_details_management','view')): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#bank_detailsCollapse">
          <i class="bi bi-bank"></i>
          <span class="nav-link-text">Bank Management</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <div class="collapse" id="bank_detailsCollapse">
          <ul class="nav flex-column ps-4">
            <?php if ($auth->checkPermission('bank_details_management','create')): ?>
            <li class="nav-item">
              <a class="nav-link <?php if($title=='Add Bank Details') echo 'active'; ?>"
                 href="/admin/bank_details/create">
                Add Bank
              </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
              <a class="nav-link <?php if($title=='Company Bank Details') echo 'active'; ?>"
                 href="/admin/bank_details/index">
                View Banks
              </a>
            </li>
          </ul>
        </div>
      </li>
      <?php endif; ?>
      
      <!-- EMPLOYEE MANAGEMENT -->
      <?php if ($auth->checkPermission('employee_management', 'view')): ?>
      <li class="nav-item ">
        <a
          class="nav-link"
          data-bs-toggle="collapse"
          href="#employeeCollapse"
          role="button"
          aria-expanded="false"
          aria-controls="employeeCollapse"
        >
          <i class="bi bi-file-text"></i>
          <span class="nav-link-text"> Employee Management</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <div class="collapse" id="employeeCollapse">
          <ul class="nav flex-column ps-4">
           <?php if ($auth->checkPermission('employee_management','create')): ?>
            <li class="nav-item">
                <a class="nav-link" href="/admin/add_employee.php">Add Employee</a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
              <a class="nav-link" href="/admin/view_employees.php">View Employees</a>
            </li>
          </ul>
        </div>
      </li>
      <?php endif; ?>
      
      <!-- CUSTOMER MANAGEMENT -->
      <?php if ($auth->checkPermission('customer_management', 'view')): ?>
      <li class="nav-item ">
        <a
          class="nav-link"
          data-bs-toggle="collapse"
          href="#customerCollapse"
          role="button"
          aria-expanded="false"
          aria-controls="customerCollapse"
        >
          <i class="bi bi-file-text"></i>
          <span class="nav-link-text">Customer Management</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <div class="collapse" id="customerCollapse">
          <ul class="nav flex-column ps-4">
           <?php if ($auth->checkPermission('customer_management','create')): ?>
            <li class="nav-item">
               <a class="nav-link <?php if($title=='customer_status') echo 'active'; ?>"
                   href="/admin/customer_status.php">
                   Customer Status
                </a>
            </li>
            <li class="nav-item">
               <a class="nav-link <?php if($title=='customer_workflow') echo 'active'; ?>"
                   href="/admin/workflow.php">
                   Customer Workflow
                </a></li>
            </li>
            <?php endif; ?>
            <li class="nav-item">
               <a class="nav-link <?php if($title=='view_customer') echo 'active'; ?>"
                   href="/admin/view_clients.php">
                   View Customer
                </a></li>
            </li>
          </ul>
        </div>
      </li>
      <?php endif; ?>

      <!-- QUOTATION MANAGEMENT -->
      <?php if ($auth->checkPermission('quotation_management', 'view')): ?>
      <li class="nav-item ">
        <a
          class="nav-link"
          data-bs-toggle="collapse"
          href="#quotationCollapse"
          role="button"
          aria-expanded="false"
          aria-controls="quotationCollapse"
        >
          <i class="bi bi-file-text"></i>
          <span class="nav-link-text"> Quotation Management</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <div class="collapse" id="quotationCollapse">
          <ul class="nav flex-column ps-4">
           <?php if ($auth->checkPermission('quotation_management','create')): ?>
            <li class="nav-item">
                <a class="nav-link" href="/admin/reqiure">
                    <span class="nav-link-text">Generate Quotation</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/admin/documentmanagement/select_client">
                    <span class="nav-link-text">Bank Quotation</span>
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
              <a class="nav-link" href="/admin/view_quotations">
                <span class="nav-link-text">View Quotations</span>
              </a>
            </li>
          </ul>
        </div>
      </li>
      <?php endif; ?>
      
      <!-- BANK QUOTATION MANAGEMENT - For Admin, Office Staff -->
      <?php if ($auth->checkPermission('quotation_management', 'view')): ?>
      <li class="nav-item ">
        <a
          class="nav-link"
          data-bs-toggle="collapse"
          href="#bankQuotationCollapse"
          role="button"
          aria-expanded="false"
          aria-controls="bankQuotationCollapse"
        >
          <i class="bi bi-currency-rupee"></i>
          <span class="nav-link-text">Bank Quotation</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <div class="collapse" id="bankQuotationCollapse">
          <ul class="nav flex-column ps-4">
            <li class="nav-item">
              <a class="nav-link <?php if($title=='bank_quotation_dashboard') {echo 'active';} ?>" href="/admin/bankquotation/quotation_dashboard.php">
                <span class="nav-link-text">Quotation Dashboard</span>
              </a>
            </li>
            <?php if ($auth->checkPermission('quotation_management','create')): ?>
            <li class="nav-item">
              <a class="nav-link <?php if($title=='bank_quotation_create') {echo 'active';} ?>" href="/admin/bankquotation/select_client.php">
                <span class="nav-link-text">Create New <br>Quotation</span>
              </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
              <a class="nav-link <?php if($title=='bank_quotation_list') {echo 'active';} ?>" href="/admin/bankquotation/quotation_list.php">
                <span class="nav-link-text">Edit Previous <br>Quotations</span>
              </a>
            </li>
          </ul>
        </div>
      </li>
      <?php endif; ?>
      
      <!-- DOCUMENT MANAGEMENT - For Admin, Warehouse Staff -->
      <?php if ($auth->checkPermission('reports', 'view')): ?>
      <li class="nav-item ">
        <a
          class="nav-link"
          data-bs-toggle="collapse"
          href="#docCollapse"
          role="button"
          aria-expanded="false"
          aria-controls="docCollapse"
        >
          <i class="bi bi-people-fill"></i>
          <span class="nav-link-text">Document Management</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <div class="collapse" id="docCollapse">
          <ul class="nav flex-column ps-4">
            <?php if ($auth->checkPermission('reports','create')): ?>
            <li class="nav-item">
              <a class="nav-link <?php if($title=='client_document_dashboard') {echo 'active';} ?>" href="/admin/documentmanagement/select_client.php">
                <span class="nav-link-text">New Document <br>Generate</span>
              </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
              <a class="nav-link <?php if($title=='document_dashboard') {echo 'active';} ?>" href="/admin/documentmanagement/document_dashboard.php">
                <span class="nav-link-text">Old Document <br>Edit</span>
              </a>
            </li>
          </ul>
        </div>
      </li>
      <?php endif; ?>

        <!-- INVENTORY MODULE -->
        <?php if ($auth->checkPermission('inventory_management','view')): ?>
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="collapse" href="#inventoryCollapse">
            <i class="bi bi-box-seam"></i>
            <span class="nav-link-text">Inventory Management</span>
            <i class="bi bi-chevron-down ms-auto"></i>
          </a>
        
          <div class="collapse" id="inventoryCollapse">
            <ul class="nav flex-column ps-4">
        
              <!-- Warehouses -->
              <?php if ($auth->checkPermission('inventory_management','create')): ?>
              <li class="nav-item">
                <a class="nav-link <?php if($title=='warehouse_create') echo 'active'; ?>"
                   href="/admin/inventory/warehouse_create.php">
                  Add Warehouse
                </a>
              </li>
              <?php endif; ?>
        
              <li class="nav-item">
                <a class="nav-link <?php if($title=='warehouses') echo 'active'; ?>"
                   href="/admin/inventory/warehouses.php">
                  View Warehouses
                </a>
              </li>
        
              <!-- PRODUCTS -->
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#inventoryProductsCollapse">
                  Products
                  <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="inventoryProductsCollapse">
                  <ul class="nav flex-column ps-4">
                    <?php if ($auth->checkPermission('inventory_management','create')): ?>
                    <li>
                      <a class="nav-link <?php if($title=='product_create') echo 'active'; ?>"
                         href="/admin/inventory/product_form.php">
                        Add Product
                      </a>
                    </li>
                    <?php endif; ?>
        
                    <li>
                      <a class="nav-link <?php if($title=='products') echo 'active'; ?>"
                         href="/admin/inventory/products.php">
                        Product List
                      </a>
                    </li>
        
                    <li>
                      <a class="nav-link <?php if($title=='product_categories') echo 'active'; ?>"
                         href="/admin/inventory/product_categories.php">
                        Product Categories
                      </a>
                    </li>
        
                    <li>
                      <a class="nav-link <?php if($title=='suppliers') echo 'active'; ?>"
                         href="/admin/inventory/suppliers.php">
                        Suppliers
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
        
              <!-- STOCK -->
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#inventoryStockCollapse">
                  Stock
                  <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="inventoryStockCollapse">
                  <ul class="nav flex-column ps-4">
        
                    <li>
                      <a class="nav-link <?php if($title=='inventory_dashboard') echo 'active'; ?>"
                         href="/admin/inventory/inventory_dashboard.php">
                        Inventory Dashboard
                      </a>
                    </li>
        
                    <li>
                      <a class="nav-link <?php if($title=='product_stock_view') echo 'active'; ?>"
                         href="/admin/inventory/product_stock_view.php">
                        Product Stock <br>(Select)
                      </a>
                    </li>
        
                    <li>
                      <a class="nav-link <?php if($title=='warehouse_stock_add') echo 'active'; ?>"
                         href="/admin/inventory/warehouse_stock_add.php">
                        Add Stock
                      </a>
                    </li>
        
                    <li>
                      <a class="nav-link <?php if($title=='stock_transfer') echo 'active'; ?>"
                         href="/admin/inventory/stock_transfer.php">
                        Transfer Stock <br>(Warehouse)
                      </a>
                    </li>
        
                    <li>
                      <a class="nav-link <?php if($title=='stock_issue') echo 'active'; ?>"
                         href="/admin/inventory/stock_issue.php">
                        Issue Stock <br>(Customer / Retailer)
                      </a>
                    </li>
        
                    <li>
                      <a class="nav-link <?php if($title=='stock_movements') echo 'active'; ?>"
                         href="/admin/inventory/stock_movements.php">
                        Stock Movements <br>/ Audit
                      </a>
                    </li>
        
                  </ul>
                </div>
              </li>
        
              <!-- SERIALS -->
              <li class="nav-item">
                <a class="nav-link <?php if($title=='product_serials') echo 'active'; ?>"
                   href="/admin/inventory/product_serials.php">
                  Product Serials
                </a>
              </li>
        
            </ul>
          </div>
        </li>
        <?php endif; ?>

        <!-- INVOICE MANAGEMENT -->
        <?php if ($auth->checkPermission('invoice_management','view')): ?>
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="collapse" href="#invoiceCollapse">
            <i class="bi bi-receipt"></i>
            <span class="nav-link-text">Invoice Management</span>
            <i class="bi bi-chevron-down ms-auto"></i>
          </a>
        
          <div class="collapse" id="invoiceCollapse">
            <ul class="nav flex-column ps-4">
        
              <?php if ($auth->checkPermission('invoice_management','create')): ?>
              <li class="nav-item">
                <a class="nav-link" href="/admin/invoice/invoice_create.php">
                  Create Invoice
                </a>
              </li>
              <?php endif; ?>
        
              <li class="nav-item">
                <a class="nav-link" href="/admin/invoice/invoices.php">
                  View Invoices
                </a>
              </li>
              
              <li class="nav-item">
                <a class="nav-link" href="/admin/invoice/generate_receipt_voucher.php">
                  Receipt Voucher
                </a>
              </li>
        
            </ul>
          </div>
        </li>
        <?php endif; ?>



      <!-- REPORTS -->
      <!--<?php // if ($auth->checkPermission('reports','view')): ?>-->
      <!--<li class="nav-item">-->
      <!--  <a class="nav-link" href="/admin/reports">-->
      <!--    <i class="bi bi-graph-up"></i>-->
      <!--    Reports & Analytics-->
      <!--  </a>-->
      <!--</li>-->
      <!--<?php //endif; ?>-->

      <!-- SETTINGS -->
    <!--  <?php //if ($auth->checkPermission('settings','view')): ?>-->
    <!--  <li class="nav-item">-->
    <!--    <a class="nav-link" href="/admin/settings">-->
    <!--      <i class="bi bi-gear"></i>-->
    <!--      Settings-->
    <!--    </a>-->
    <!--  </li>-->
    <!--  <?php // endif; ?>-->

    <!--</ul>-->

    <!-- FOOTER -->
    <!--<div class="sidebar-footer mt-auto">-->
    <!--  <div class="user-info-sidebar">-->
    <!--    <i class="bi bi-person-circle"></i>-->
    <!--    <div>-->
          <!--<strong><?php //echo $current_user_name; ?></strong><br>-->
    <!--      <small>-->
            <?php 
            // $role_names = [
            //   'super_admin'=>'Super Admin',
            //   'admin'=>'Admin',
            //   'office_staff'=>'Office Staff',
            //   'sales_marketing'=>'Sales Team',
            //   'warehouse_staff'=>'Warehouse Staff'
            // ];
            // echo $role_names[$current_role] ?? 'User';
            ?>
    <!--      </small>-->
    <!--    </div>-->
    <!--  </div>-->
    <!--</div>-->

  </nav>
</div>
