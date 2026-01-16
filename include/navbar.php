 <?php 

// require('../../database/connection.php');
// require('../../database/function.php');

// $conn = new connection();
// $db = $conn->my_connect();

// $fun = new fun($db);

// $fetchLogo = $fun->fetchLogo();
// if ($fetchLogo && $fetchLogo->num_rows > 0) {
//   $arr = $fetchLogo->fetch_assoc();
// }

?> 

<!-- Navbar Start -->
<nav class="navbar navbar-expand-lg navbar-light fixed-top p-0">
    <a
        href="index.php"
        class="navbar-brand d-flex align-items-center border-end px-4 px-lg-5">
        <!-- Logo Space -->
        <div class="logo-container me-3">
            <img
                src="img/logo.jpg"
                alt="VK Solar Energy Logo"
                class="logo-img"
                style="height: 50px; width: auto" />
        </div>
        <h2 class="m-0">VK Solar Energy</h2>
    </a>
    <button
        type="button"
        class="navbar-toggler me-4"
        data-bs-toggle="collapse"
        data-bs-target="#navbarCollapse">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <div class="navbar-nav ms-auto p-4 p-lg-0">
            <a href="index.php" class="nav-item nav-link active">Home</a>
            <a href="#about" class="nav-item nav-link">About</a>
            <a href="#services" class="nav-item nav-link">Services</a>
            <a href="#projects" class="nav-item nav-link">Projects</a>
            <a href="#products" class="nav-item nav-link">Products</a>
            <a href="#contact" class="nav-item nav-link">Contact</a>
            
        </div>
        <a
            href="login"
            class="text-white me-2 btn-primary rounded-pill py-3 px-4 d-none d-lg-block">LOGIN<i class="fa fa-sign-in "></i></a>
        <a
            href="#quote"
            class="text-white me-2 btn-primary rounded-pill py-3 px-4 d-none d-lg-block">Get A Quote<i class="fa fa-arrow-right ms-2"></i></a>
    </div>
</nav>