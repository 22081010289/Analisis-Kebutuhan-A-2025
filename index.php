<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swalayan Bagus - Your Favorite Minimarket</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .main-header {
            display: flex;
            align-items: center;
            padding: 1.5rem 2rem 1rem 2rem;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .main-header .logo {
            width: 56px;
            height: 56px;
            margin-right: 1.5rem;
        }
        .main-header .category-dropdown {
            font-size: 1.1rem;
            font-weight: 500;
            margin-right: 1.5rem;
            cursor: pointer;
            border: none;
            background: none;
        }
        .main-header .search-bar {
            flex: 1;
            margin-right: 1.5rem;
        }
        .main-header .search-bar input {
            width: 100%;
            border-radius: 2rem;
            border: 1px solid #ddd;
            padding: 0.7rem 2.5rem 0.7rem 1.2rem;
            font-size: 1rem;
        }
        .main-header .icon-btn {
            background: none;
            border: none;
            margin-left: 1.2rem;
            font-size: 1.7rem;
            color: #222;
            cursor: pointer;
            position: relative;
        }
        .main-header .icon-btn .cart-badge {
            position: absolute;
            top: -6px;
            right: -8px;
            background: #f44336;
            color: #fff;
            border-radius: 50%;
            font-size: 0.8rem;
            padding: 2px 6px;
        }
        .welcome-section {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 2rem 1.5rem 2rem;
            background: #fff;
        }
        .welcome-section .store-img {
            width: 340px;
            max-width: 100%;
            border-radius: 1.2rem;
            margin-right: 2.5rem;
            margin-bottom: 1rem;
        }
        .welcome-section .welcome-text {
            max-width: 500px;
        }
        .welcome-section .welcome-text h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        .welcome-section .welcome-text span {
            color: #f44336;
            font-weight: 700;
        }
        .category-section {
            margin: 2.5rem 0 1.5rem 0;
            padding: 0 2rem;
        }
        .category-section h2 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1.2rem;
        }
        .category-scroll {
            display: flex;
            overflow-x: auto;
            gap: 1.2rem;
            padding-bottom: 0.5rem;
        }
        .category-card {
            min-width: 180px;
            background: #fff;
            border-radius: 1.2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            text-align: center;
            padding: 1.2rem 1rem 1rem 1rem;
            flex-shrink: 0;
            transition: box-shadow 0.2s;
        }
        .category-card:hover {
            box-shadow: 0 4px 16px rgba(244,67,54,0.13);
        }
        .category-card img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            margin-bottom: 0.7rem;
        }
        .category-card .cat-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #222;
        }
        .category-card .cat-desc {
            font-size: 0.95rem;
            color: #888;
            margin-bottom: 0.5rem;
        }
        .category-card .cat-link {
            color: #1976d2;
            font-size: 0.95rem;
            text-decoration: none;
        }
        .category-card .cat-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 900px) {
            .welcome-section {
                flex-direction: column;
                text-align: center;
            }
            .welcome-section .store-img {
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <img src="assets/img/logo.png" alt="Logo" class="logo">
        <button class="category-dropdown">Kategori <span style="font-size:1.1em">&#9660;</span></button>
        <div class="search-bar">
            <input type="text" placeholder="Saya ingin mencari...">
        </div>
        <a href="cart.php" class="icon-btn" title="Keranjang">
            <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h2l.4 2M7 13h10l4-8H5.4"/></svg>
            <span class="cart-badge"><?php echo isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0 ? $_SESSION['cart_count'] : ''; ?></span>
        </a>
        <a href="<?php echo isset($_SESSION['user_id']) ? 'profile.php' : 'login.php'; ?>" class="icon-btn" title="Akun">
            <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M2 20c0-4 8-6 10-6s10 2 10 6"/></svg>
        </a>
    </header>
    <section class="welcome-section">
        <img src="assets/img/login-bg.png" alt="Toko Swalayan Bagus" class="store-img">
        <div class="welcome-text">
            <div style="color:#f44336;font-weight:700;">SELAMAT DATANG DI</div>
            <h1>Toko Swalayan <span>Bagus</span></h1>
            <div style="font-size:1.1rem;color:#444;margin-top:0.5rem;">Jl. Raya Menganti No.45</div>
        </div>
    </section>
    <section class="category-section">
        <h2>Kategori Belanja</h2>
        <div class="category-scroll">
            <?php
            $database = new Database();
            $db = $database->getConnection();
            $query = "SELECT * FROM categories ORDER BY name";
            $stmt = $db->prepare($query);
            $stmt->execute();
            while ($cat = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<div class="category-card">';
                // Use a placeholder if no image field exists
                echo '<img src="assets/img/logo.png" alt="' . htmlspecialchars($cat['name']) . '">';
                echo '<div class="cat-title">' . htmlspecialchars($cat['name']) . '</div>';
                echo '<div class="cat-desc">' . htmlspecialchars(mb_strimwidth($cat['description'], 0, 32, '...')) . '</div>';
                echo '<a class="cat-link" href="products.php?category=' . $cat['id'] . '">Lihat Produk</a>';
                echo '</div>';
            }
            ?>
        </div>
    </section>
</body>
</html> 