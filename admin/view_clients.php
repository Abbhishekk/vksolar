<?php
include "connect/auth_middleware.php";
$auth->requireAuth();
$auth->requirePermission('customer_management', 'view');

include "connect/db1.php";
include "connect/fun.php";

$connect = new connect();
$fun = new fun($connect->dbconnect());
$title='view_customer';
// Filter for complete/incomplete clients
$filter = $_GET['filter'] ?? 'all'; // all, complete, incomplete

// Get clients based on filter
if ($filter === 'complete') {
    $clients = $fun->getCompleteClients();
} elseif ($filter === 'incomplete') {
    $clients = $fun->getIncompleteClients();
} else {
    $clients = $fun->fetchClients();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Clients - Solar Quick</title>

    <?php require('include/head.php'); ?>
    <style>
        .status-badge {
            font-size: 0.8em;
            padding: 4px 8px;
            border-radius: 12px;
        }
        .status-complete {
            background-color: #28a745;
            color: white;
        }
        .status-incomplete {
            background-color: #dc3545;
            color: white;
        }
        .filter-buttons .btn {
            margin-right: 5px;
            margin-bottom: 10px;
        }
        .action-buttons .btn {
            margin-right: 5px;
        }
    </style>
</head>
<body>

    <!-- ======= Sidebar ======= -->
    <?php include "include/sidebar.php"; ?>
    <!-- End Sidebar-->
    
    <!-- Main Content -->
    <div id="main-content">
    

        <!-- Fixed Header -->
        <?php require('include/navbar.php') ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>View All Clients</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">View Clients</li>
                </ol>
            </nav>
        </div>

        <div class="container-fluid">
            <!-- Filter Buttons -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="filter-buttons">
                        <a href="?filter=all" class="btn btn-<?php echo $filter === 'all' ? 'primary' : 'outline-primary'; ?>">
                            All Clients
                        </a>
                        <a href="?filter=complete" class="btn btn-<?php echo $filter === 'complete' ? 'success' : 'outline-success'; ?>">
                            Complete Only
                        </a>
                        <a href="?filter=incomplete" class="btn btn-<?php echo $filter === 'incomplete' ? 'warning' : 'outline-warning'; ?>">
                            Incomplete Only
                        </a>
                    </div>
                </div>
            </div>

            <!-- Clients Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Client List</h5>
                            
                            <?php if(mysqli_num_rows($clients) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Consumer Number</th>
                                            <th>Mobile</th>
                                            <th>District</th>
                                            <th>Status</th>
                                            <th>Created Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($client = mysqli_fetch_assoc($clients)): 
                                            $isComplete = isClientComplete($client);
                                        ?>
                                        <tr>
                                            <td><?php echo $client['id']; ?></td>
                                            <td><?php echo htmlspecialchars($client['name']); ?></td>
                                            <td><?php echo htmlspecialchars($client['consumer_number']); ?></td>
                                            <td><?php echo htmlspecialchars($client['mobile']); ?></td>
                                            <td><?php echo htmlspecialchars($client['district']); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $isComplete ? 'status-complete' : 'status-incomplete'; ?>">
                                                    <?php echo $isComplete ? 'Complete' : 'In Progress'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($client['created_at'])); ?></td>
                                            <td class="action-buttons">
                                                <a href="client_details.php?id=<?php echo $client['id']; ?>" 
                                                   class="btn btn-sm btn-info" title="View Client Details">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <a href="client_edit2.php?id=<?php echo $client['id']; ?>" 
                                                   class="btn btn-sm btn-primary" title="Edit Client">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <button onclick="confirmDelete(<?php echo $client['id']; ?>)" 
                                                        class="btn btn-sm btn-danger" title="Delete Client">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info">
                                No clients found.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this client? This action cannot be undone and will permanently delete all client data and uploaded files.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Client</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    let clientToDelete = null;

    function confirmDelete(clientId) {
        clientToDelete = clientId;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (clientToDelete) {
            // Show loading
            const btn = this;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';
            btn.disabled = true;

            // Send delete request
            fetch(`api/workflow_api.php?action=delete_client&client_id=${clientToDelete}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Error deleting client: ' + (data.message || 'Unknown error'));
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting client');
                location.reload();
            });
        }
    });
    </script>
</body>
</html>

<?php
// Helper function to check if client is complete
function isClientComplete($client) {
    // Check if all critical fields are filled
    $requiredFields = [
        'name', 'consumer_number', 'mobile', 'email', 'district',
        'mahadiscom_email', 'mahadiscom_user_id', 'load_change_application_number',
        'inverter_company_name', 'dcr_certificate_number', 'meter_number'
    ];
    
    foreach($requiredFields as $field) {
        if(empty($client[$field])) {
            return false;
        }
    }
    return true;
}
?>