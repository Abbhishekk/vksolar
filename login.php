<?php
// login.php
session_start();
// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: admin/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - VK Solar Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <!-- Bootstrap Icons -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
    />
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />

    <style>
      /* VK Solar Energy - Green Theme Custom CSS */
      :root {
        --primary: #2e8b57; /* Forest Green */
        --primary-light: #3cb371; /* Medium Sea Green */
        --primary-dark: #1e6b47; /* Darker Green */
        --secondary: #f5f5f5; /* Off-white */
        --accent: #87ceeb; /* Sky Blue */
        --accent-light: #b0e2ff; /* Light Sky Blue */
        --earth: #d2b48c; /* Tan/Earth tone */
        --earth-light: #f0e6d6; /* Light Beige */
        --dark: #2c3e50; /* Dark Blue-Gray */
        --light: #f8f9fa; /* Light Gray */
        --gradient-primary: linear-gradient(
          135deg,
          var(--primary) 0%,
          var(--primary-light) 100%
        );
        --gradient-secondary: linear-gradient(
          135deg,
          var(--earth-light) 0%,
          var(--secondary) 100%
        );
        --gradient-hero: linear-gradient(
          135deg,
          rgba(46, 139, 87, 0.85) 0%,
          rgba(60, 179, 113, 0.85) 100%
        );
        --gradient-nature: linear-gradient(
          135deg,
          var(--primary) 0%,
          var(--accent) 100%
        );
        --background-light: #f1f5f9;
        --text-light: #64748b;
        --card-bg-light: #ffffff;
        --border-color-light: #e2e8f0;
        --success-color: #10b981;
        --danger-color: #f87171;
        --warning-color: #fbbf24;
      }

      body {
        font-family: "Inter", sans-serif;
        background: var(--gradient-primary);
        color: var(--text-light);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        position: relative;
        overflow-x: hidden;
      }

      /* Background Elements */
      .login-bg-shape {
        position: absolute;
        z-index: -1;
        opacity: 0.1;
        border-radius: 50%;
      }

      .shape-1 {
        width: 300px;
        height: 300px;
        background: var(--gradient-nature);
        top: 10%;
        left: 10%;
        animation: float 8s ease-in-out infinite;
      }

      .shape-2 {
        width: 200px;
        height: 200px;
        background: var(--gradient-primary);
        bottom: 15%;
        right: 10%;
        animation: float 6s ease-in-out infinite reverse;
      }

      .shape-3 {
        width: 150px;
        height: 150px;
        background: var(--accent);
        top: 50%;
        left: 5%;
        animation: float 7s ease-in-out infinite;
      }

      @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(10deg); }
      }

      /* Login Card */
      .login-card {
        background: var(--card-bg-light);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        width: 100%;
        max-width: 400px;
        transition: all 0.3s ease;
        border: 1px solid var(--border-color-light);
        position: relative;
        z-index: 10;
      }

      .login-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
      }

      .login-header {
        background: var(--gradient-primary);
        padding: 30px 20px 20px;
        text-align: center;
        color: white;
      }

      .login-logo {
        font-size: 2.5rem;
        margin-bottom: 10px;
      }

      .login-title {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 5px;
      }

      .login-subtitle {
        font-size: 1rem;
        opacity: 0.9;
      }

      .login-body {
        padding: 30px;
      }

      /* Form Elements */
      .form-group {
        margin-bottom: 1.5rem;
        position: relative;
      }

      .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
        color: var(--dark);
        display: flex;
        align-items: center;
      }

      .form-label i {
        margin-right: 8px;
        color: var(--primary);
      }

      .form-control {
        border-radius: 10px;
        padding: 12px 16px;
        border: 1px solid var(--border-color-light);
        transition: all 0.3s ease;
        background-color: var(--card-bg-light);
        color: var(--text-light);
        font-size: 1rem;
      }

      .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(46, 139, 87, 0.25);
      }

      .input-group-text {
        background-color: transparent;
        border: 1px solid var(--border-color-light);
        border-right: none;
        border-radius: 10px 0 0 10px;
      }

      .form-control.with-icon {
        border-left: none;
        border-radius: 0 10px 10px 0;
      }

      /* Button */
      .btn-login {
        background: var(--gradient-primary);
        border: none;
        border-radius: 10px;
        padding: 12px 20px;
        font-weight: 600;
        font-size: 1rem;
        color: white;
        transition: all 0.3s ease;
        width: 100%;
        box-shadow: 0 4px 15px rgba(46, 139, 87, 0.3);
        margin-top: 10px;
      }

      .btn-login:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(46, 139, 87, 0.4);
        background: linear-gradient(
          135deg,
          var(--primary-light) 0%,
          var(--primary) 100%
        );
      }

      .btn-login:active {
        transform: translateY(-1px);
      }

      /* Remember Me & Forgot Password */
      .form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
      }

      .form-check {
        display: flex;
        align-items: center;
      }

      .form-check-input {
        margin-right: 8px;
      }

      .form-check-label {
        font-size: 0.9rem;
        color: var(--text-light);
      }

      .forgot-password {
        font-size: 0.9rem;
        color: var(--primary);
        text-decoration: none;
        transition: color 0.3s ease;
      }

      .forgot-password:hover {
        color: var(--primary-dark);
        text-decoration: underline;
      }

      /* Footer */
      .login-footer {
        text-align: center;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-color-light);
      }

      .login-footer p {
        font-size: 0.9rem;
        color: var(--text-light);
        margin-bottom: 0.5rem;
      }

      .support-link {
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
      }

      .support-link:hover {
        color: var(--primary-dark);
        text-decoration: underline;
      }

      /* Alert */
      .alert {
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 1.5rem;
        border: none;
        font-size: 0.9rem;
      }

      .alert-danger {
        background-color: rgba(248, 113, 113, 0.1);
        color: var(--danger-color);
        border-left: 4px solid var(--danger-color);
      }

      .alert-success {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--success-color);
        border-left: 4px solid var(--success-color);
      }

      /* Responsive */
      @media (max-width: 576px) {
        .login-card {
          max-width: 100%;
        }
        
        .login-header {
          padding: 25px 20px;
        }
        
        .login-body {
          padding: 25px 20px;
        }
        
        .login-title {
          font-size: 1.5rem;
        }
        
        .form-options {
          flex-direction: column;
          align-items: flex-start;
          gap: 10px;
        }
        
        .shape-1, .shape-2, .shape-3 {
          display: none;
        }
      }

      @media (max-width: 400px) {
        .login-header {
          padding: 20px 15px;
        }
        
        .login-body {
          padding: 20px 15px;
        }
        
        .login-title {
          font-size: 1.3rem;
        }
        
        .login-logo {
          font-size: 2rem;
        }
      }
    </style>
  </head>
  <body>
    <!-- Background Elements -->
    <div class="login-bg-shape shape-1"></div>
    <div class="login-bg-shape shape-2"></div>
    <div class="login-bg-shape shape-3"></div>

    <!-- Login Card -->
    <div class="login-card">
      <div class="login-header">
        <div class="login-logo">
          <i class="bi bi-sun-fill"></i>
        </div>
        <h1 class="login-title">VK Solar Energy</h1>
        <p class="login-subtitle">Admin Dashboard Login</p>
      </div>
      
      <div class="login-body">
        <!-- Success/Error Alert -->
        <?php if (isset($_SESSION['success_message'])): ?>
          <div class="alert alert-success">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php 
              echo $_SESSION['success_message']; 
              unset($_SESSION['success_message']);
            ?>
          </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php 
              echo $_SESSION['error_message']; 
              unset($_SESSION['error_message']);
            ?>
          </div>
        <?php endif; ?>
        
        <!-- Simple form without role selection -->
        <form id="loginForm" action="admin/connect/login_process" method="POST">
          <div class="form-group">
            <label for="username" class="form-label">
              <i class="bi bi-person-fill"></i> Username
            </label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="bi bi-person"></i>
              </span>
              <input 
                type="text" 
                class="form-control with-icon" 
                id="username" 
                name="username"
                placeholder="Enter your username"
                required
                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
              >
            </div>
          </div>
          
          <div class="form-group">
            <label for="password" class="form-label">
              <i class="bi bi-lock-fill"></i> Password
            </label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="bi bi-key"></i>
              </span>
              <input 
                type="password" 
                class="form-control with-icon" 
                id="password" 
                name="password"
                placeholder="Enter your password"
                required
              >
            </div>
          </div>
          
          <div class="form-options">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="rememberMe" name="rememberMe">
              <label class="form-check-label" for="rememberMe">
                Remember me
              </label>
            </div>
            <a href="#" class="forgot-password">Forgot password?</a>
          </div>
          
          <input type="submit" class="btn btn-login" name="submit" value="Sign In">
            
        </form>
        
        <div class="login-footer">
          <p>Need help? <a href="#" class="support-link">Contact Support</a></p>
          <p class="small text-muted">© 2024 VK Solar Energy. All rights reserved.</p>
        </div>
      </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>