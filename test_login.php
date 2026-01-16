<?php
// test_login.php - Complete login system test
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login System Test - VK Solar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #2e8b57 0%, #3cb371 100%);
            min-height: 100vh;
            padding: 20px;
            font-family: 'Inter', sans-serif;
        }
        .test-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        .status-box {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        .status-success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }
        .status-error {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #7f1d1d;
        }
        .status-warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            color: #92400e;
        }
        .test-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        .btn-test {
            background: linear-gradient(135deg, #2e8b57 0%, #3cb371 100%);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            overflow-x: auto;
            font-size: 14px;
        }
        .login-test-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="test-card">
            <div class="text-center mb-4">
                <i class="bi bi-sun-fill text-success" style="font-size: 3rem;"></i>
                <h1 class="h3 mt-2">VK Solar - Login System Test</h1>
                <p class="text-muted">Testing Complete Login Flow</p>
            </div>

            <!-- Database Connection Test -->
            <div class="test-section">
                <h4 class="mb-3"><i class="bi bi-database me-2"></i>Database Connection Test</h4>
                <?php
                try {
                    require_once 'admin/connect/db.php';
                    echo '<div class="status-box status-success">';
                    echo '<i class="bi bi-check-circle-fill me-2"></i><strong>SUCCESS:</strong> Database connected successfully!';
                    echo '</div>';
                    
                    // Test users table
                    $result = $conn->query("SELECT id, username, password_hash, role, is_active FROM users");
                    if ($result) {
                        echo '<div class="status-box status-success">';
                        echo '<i class="bi bi-check-circle-fill me-2"></i><strong>USERS TABLE:</strong> Accessible (' . $result->num_rows . ' users found)';
                        echo '</div>';
                        
                        // Display users for testing
                        echo '<div class="mt-3">';
                        echo '<strong>Available Users for Testing:</strong>';
                        echo '<div class="table-responsive mt-2">';
                        echo '<table class="table table-sm table-bordered">';
                        echo '<thead><tr><th>ID</th><th>Username</th><th>Password</th><th>Role</th><th>Active</th></tr></thead>';
                        echo '<tbody>';
                        while ($user = $result->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . $user['id'] . '</td>';
                            echo '<td>' . $user['username'] . '</td>';
                            echo '<td>' . $user['password_hash'] . '</td>';
                            echo '<td>' . $user['role'] . '</td>';
                            echo '<td>' . ($user['is_active'] ? 'Yes' : 'No') . '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table></div></div>';
                    }
                } catch (Exception $e) {
                    echo '<div class="status-box status-error">';
                    echo '<i class="bi bi-x-circle-fill me-2"></i><strong>ERROR:</strong> ' . $e->getMessage();
                    echo '</div>';
                }
                ?>
            </div>

            <!-- Session Test -->
            <div class="test-section">
                <h4 class="mb-3"><i class="bi bi-person-check me-2"></i>Session Test</h4>
                <?php
                echo '<div class="status-box ' . (isset($_SESSION['user_id']) ? 'status-success' : 'status-warning') . '">';
                if (isset($_SESSION['user_id'])) {
                    echo '<i class="bi bi-check-circle-fill me-2"></i><strong>LOGGED IN:</strong> Session is active<br>';
                    echo '<small>User ID: ' . $_SESSION['user_id'] . ' | Username: ' . ($_SESSION['username'] ?? 'N/A') . ' | Role: ' . ($_SESSION['role'] ?? 'N/A') . '</small>';
                } else {
                    echo '<i class="bi bi-exclamation-triangle me-2"></i><strong>NOT LOGGED IN:</strong> No active session found';
                }
                echo '</div>';
                ?>
            </div>

            <!-- Login Form Test -->
            <div class="test-section">
                <h4 class="mb-3"><i class="bi bi-box-arrow-in-right me-2"></i>Login Form Test</h4>
                
                <div class="login-test-form">
                    <h5>Test Login Credentials</h5>
                    <p class="text-muted">Use these credentials to test the login system:</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6>Super Admin</h6>
                                    <p><strong>Username:</strong> superadmin</p>
                                    <p><strong>Password:</strong> admin123</p>
                                    <p><strong>Role:</strong> super_admin</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <a href="login.php" class="btn btn-test">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Go to Login Page
                        </a>
                    </div>
                </div>

                <!-- Test Login Process -->
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_login'])) {
                    echo '<div class="mt-3">';
                    echo '<h6>Manual Login Test:</h6>';
                    
                    // Simulate login
                    require_once 'admin/connect/db.php';
                    $username = 'superadmin';
                    $password = 'admin123';
                    $role = 'super_admin';
                    
                    $stmt = $conn->prepare("SELECT id, username, password_hash, role, full_name FROM users WHERE username = ? AND role = ? AND is_active = 1");
                    $stmt->bind_param("ss", $username, $role);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows === 1) {
                        $user = $result->fetch_assoc();
                        
                        if ($password === $user['password_hash']) {
                            echo '<div class="status-box status-success">';
                            echo '<i class="bi bi-check-circle-fill me-2"></i><strong>LOGIN SUCCESS:</strong> Credentials are valid!';
                            echo '<br><small>User: ' . $user['full_name'] . ' (' . $user['role'] . ')</small>';
                            echo '</div>';
                            
                            // Test session setting
                            $_SESSION['test_user_id'] = $user['id'];
                            $_SESSION['test_username'] = $user['username'];
                            $_SESSION['test_role'] = $user['role'];
                            
                            echo '<div class="status-box status-success">';
                            echo '<i class="bi bi-check-circle-fill me-2"></i><strong>SESSION SET:</strong> Test session variables set successfully';
                            echo '</div>';
                            
                        } else {
                            echo '<div class="status-box status-error">';
                            echo '<i class="bi bi-x-circle-fill me-2"></i><strong>PASSWORD MISMATCH:</strong> Password verification failed';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="status-box status-error">';
                        echo '<i class="bi bi-x-circle-fill me-2"></i><strong>USER NOT FOUND:</strong> No active user found with these credentials';
                        echo '</div>';
                    }
                    echo '</div>';
                }
                ?>
                
                <form method="POST" class="mt-3">
                    <button type="submit" name="test_login" class="btn btn-outline-primary">
                        <i class="bi bi-play-circle me-2"></i>Test Login Process
                    </button>
                </form>
            </div>

            <!-- File Path Test -->
            <div class="test-section">
                <h4 class="mb-3"><i class="bi bi-folder me-2"></i>File Path Test</h4>
                <?php
                $critical_files = [
                    'admin/connect/db.php' => 'Database Connection',
                    'admin/connect/auth_middleware.php' => 'Auth Middleware',
                    'admin/connect/login_process.php' => 'Login Processor',
                    'login.php' => 'Login Page',
                    'admin/index.php' => 'Admin Dashboard',
                    'admin/include/sidebar.php' => 'Sidebar',
                    'admin/include/navbar.php' => 'Navbar'
                ];
                
                foreach ($critical_files as $file => $description) {
                    $exists = file_exists($file);
                    echo '<div class="status-box ' . ($exists ? 'status-success' : 'status-error') . ' mb-2">';
                    echo $exists ? '<i class="bi bi-check-circle-fill me-2"></i>' : '<i class="bi bi-x-circle-fill me-2"></i>';
                    echo '<strong>' . $description . ':</strong> ' . $file;
                    if (!$exists) {
                        echo '<br><small class="text-danger">File not found at this location!</small>';
                    }
                    echo '</div>';
                }
                ?>
            </div>

            <!-- Quick Actions -->
            <div class="text-center mt-4">
                <a href="login.php" class="btn btn-test me-2">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Test Login Page
                </a>
                <?php if (file_exists('admin/index.php')): ?>
                <a href="admin/" class="btn btn-outline-success me-2">
                    <i class="bi bi-speedometer2 me-2"></i>Go to Dashboard
                </a>
                <?php endif; ?>
                <a href="test_connection.php" class="btn btn-outline-primary me-2">
                    <i class="bi bi-database me-2"></i>Connection Test
                </a>
                <a href="test_login.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-repeat me-2"></i>Refresh Test
                </a>
            </div>

            <div class="mt-4 text-center">
                <small class="text-muted">
                    Test generated on: <?php echo date('Y-m-d H:i:s'); ?><br>
                    Session ID: <?php echo session_id(); ?>
                </small>
            </div>
        </div>

        <!-- Debug Information -->
        <div class="test-card">
            <h4><i class="bi bi-bug me-2"></i>Debug Information</h4>
            <pre><?php
            echo "SESSION DATA:\n";
            print_r($_SESSION);
            echo "\n\nPOST DATA:\n"; 
            print_r($_POST);
            echo "\n\nSERVER INFO:\n";
            echo "PHP Version: " . PHP_VERSION . "\n";
            echo "Session Save Path: " . session_save_path() . "\n";
            echo "Session Status: " . session_status() . "\n";
            ?></pre>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>