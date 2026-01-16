<?php
// admin/inventory/stock_movements.php
require_once __DIR__ . '/../connect/auth_middleware.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
$auth->requirePermission('reports', 'create');

$title = 'fetch_client';
// Fetch clients
$sql = "SELECT id, name, consumer_number FROM clients ORDER BY name ASC";
$result = $conn->query($sql);

$clients = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $clients[] = $row;
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Stock Movements</title>
<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
/* ===== Luxurious Styling ===== */

.doc-wrapper {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.doc-card {
    width: 100%;
 
    border: none;
    border-radius: 16px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.08);
    background: #ffffff;
}

.doc-card-header {
    background: linear-gradient(135deg, #30935C);
    color: #fff;
    padding: 22px;
    border-radius: 16px 16px 0 0;
    text-align: center;
}

.doc-card-header h4 {
    margin: 0;
    font-weight: 600;
    letter-spacing: 0.3px;
}

.doc-card-body {
    padding: 30px;
}

.doc-label {
    font-weight: 600;
    margin-bottom: 6px;
    color: #343a40;
}

.doc-input {
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 15px;
}

.doc-input:focus {
    box-shadow: 0 0 0 0.15rem rgba(13,110,253,.25);
}

.doc-btn {
    border-radius: 10px;
    padding: 12px;
    font-weight: 600;
    letter-spacing: 0.4px;
}

.doc-hint {
    font-size: 13px;
    color: #6c757d;
    margin-top: 6px;
}
</style>


</head>
<body>

<?php
$cwd=getcwd(); chdir(__DIR__.'/..'); include 'include/sidebar.php'; chdir($cwd);
?>
<div id="main-content">
<?php
$cwd=getcwd(); chdir(__DIR__.'/..'); include 'include/navbar.php'; chdir($cwd);
?>


<main class="container-fluid ">

    <div class="doc-card">

        <div class="doc-card-header">
            <h4>Document Management</h4>
            <small>Select Client to Generate Documents</small>
        </div>

        <div class="doc-card-body">

            <form method="post" action="client_dashboard" autocomplete="off">

                <div class="mb-3">
                    <label class="doc-label">
                        Client Name / Consumer Number / Client ID
                    </label>

                    <input
                        type="text"
                        id="client_search"
                        name="client_search"
                        list="clientList"
                        class="form-control doc-input"
                        placeholder="Start typing client name, consumer number or ID"
                        required
                    >

                    <datalist id="clientList">
                        <?php foreach ($clients as $client): ?>
                            <option
                                data-id="<?= $client['id']; ?>"
                                value="<?= htmlspecialchars(
                                    $client['name'] .
                                    " | CN: " . $client['consumer_number'] .
                                    " | ID: " . $client['id']
                                ); ?>">
                            </option>
                        <?php endforeach; ?>
                    </datalist>

                    <div class="doc-hint">
                        Example: Aniket Atkari | CN: 410012345 | ID: 5
                    </div>
                </div>

                <input type="hidden" name="client_id" id="client_id">

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary doc-btn">
                        Proceed to Document Dashboard
                    </button>
                </div>

            </form>

        </div>
    </div>




</main>
</div>
<script>
document.getElementById('client_search').addEventListener('change', function () {
    const options = document.getElementById('clientList').options;
    const inputValue = this.value;
    let found = false;

    for (let i = 0; i < options.length; i++) {
        if (options[i].value === inputValue) {
            document.getElementById('client_id').value = options[i].dataset.id;
            found = true;
            break;
        }
    }

    if (!found) {
        document.getElementById('client_id').value = '';
    }
});
</script>

</body>
</html>
