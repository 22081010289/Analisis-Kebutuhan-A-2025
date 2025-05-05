<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">Swalayan Bagus</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" 
                       href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'products.php' ? 'active' : ''; ?>" 
                       href="products.php">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'categories.php' ? 'active' : ''; ?>" 
                       href="categories.php">Categories</a>
                </li>
            </ul>
            <div class="d-flex">
                <a href="cart.php" class="btn btn-light me-2">
                    <i class="fas fa-shopping-cart"></i> Cart
                    <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                        <span class="badge bg-danger"><?php echo $_SESSION['cart_count']; ?></span>
                    <?php endif; ?>
                </a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="btn btn-light me-2">Profile</a>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-light me-2">Login</a>
                    <a href="register.php" class="btn btn-light">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav> 