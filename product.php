<?php
session_start();
require_once 'config/database.php';

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get product details
$product_id = (int)$_GET['id'];
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: products.php');
    exit();
}

// Get related products
$related_query = "SELECT * FROM products 
                 WHERE category_id = ? AND id != ? 
                 LIMIT 4";
$stmt = $db->prepare($related_query);
$stmt->execute([$product['category_id'], $product_id]);
$related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php?redirect=product.php?id=' . $product_id);
        exit();
    }

    $quantity = (int)$_POST['quantity'];
    if ($quantity > 0 && $quantity <= $product['stock']) {
        // Check if product already in cart
        $check_query = "SELECT * FROM cart 
                       WHERE user_id = ? AND product_id = ?";
        $stmt = $db->prepare($check_query);
        $stmt->execute([$_SESSION['user_id'], $product_id]);
        $existing_cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_cart) {
            // Update quantity
            $update_query = "UPDATE cart 
                           SET quantity = quantity + ? 
                           WHERE user_id = ? AND product_id = ?";
            $stmt = $db->prepare($update_query);
            $stmt->execute([$quantity, $_SESSION['user_id'], $product_id]);
        } else {
            // Add new item
            $insert_query = "INSERT INTO cart (user_id, product_id, quantity) 
                           VALUES (?, ?, ?)";
            $stmt = $db->prepare($insert_query);
            $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
        }

        header('Location: cart.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Swalayan Bagus</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: #fff; }
        .main-header { margin-bottom: 2.5rem; }
        .product-detail-container {
            max-width: 1200px;
            margin: 40px auto 0 auto;
            display: flex;
            gap: 2.5rem;
            align-items: flex-start;
            flex-wrap: wrap;
        }
        .product-image-box {
            flex: 0 0 420px;
            background: #fafafa;
            border-radius: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.5rem;
            min-width: 320px;
        }
        .product-image-box img {
            width: 100%;
            max-width: 340px;
            border-radius: 1.2rem;
            object-fit: contain;
        }
        .product-info-box {
            flex: 1 1 340px;
            min-width: 320px;
        }
        .product-title {
            font-size: 2.1rem;
            font-weight: 800;
            margin-bottom: 0.7rem;
        }
        .product-price {
            color: #f44336;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1.2rem;
        }
        .product-desc {
            font-size: 1.1rem;
            color: #444;
            margin-bottom: 1.5rem;
        }
        .product-category {
            color: #888;
            font-size: 1.05rem;
            margin-bottom: 0.7rem;
        }
        .product-form {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            margin-bottom: 1.5rem;
        }
        .product-form .qty-label {
            font-size: 1.05rem;
            margin-right: 0.5rem;
        }
        .product-form input[type="number"] {
            width: 60px;
            border-radius: 0.5rem;
            border: 1.5px solid #bbb;
            padding: 0.5rem 0.7rem;
            font-size: 1.1rem;
            text-align: center;
        }
        .product-form .btn-add {
            background: #f44336;
            color: #fff;
            font-size: 1.15rem;
            font-weight: 700;
            border-radius: 0.5rem;
            padding: 0.7rem 2.2rem;
            border: none;
            transition: background 0.2s;
        }
        .product-form .btn-add:hover {
            background: #d32f2f;
        }
        .product-stock {
            color: #1976d2;
            font-size: 1.05rem;
            margin-bottom: 1.2rem;
        }
        .related-section {
            max-width: 1200px;
            margin: 60px auto 0 auto;
        }
        .related-scroll {
            display: flex;
            gap: 1.2rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }
        .related-card {
            min-width: 200px;
            background: #fff;
            border-radius: 1.2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            text-align: center;
            padding: 1.2rem 1rem 1rem 1rem;
            flex-shrink: 0;
            transition: box-shadow 0.2s;
        }
        .related-card img {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin-bottom: 0.7rem;
            border-radius: 0.7rem;
        }
        .related-card .related-title {
            font-size: 1.05rem;
            font-weight: 600;
            color: #222;
            margin-bottom: 0.3rem;
        }
        .related-card .related-price {
            color: #f44336;
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .related-card .related-link {
            color: #1976d2;
            font-size: 0.95rem;
            text-decoration: none;
        }
        .related-card .related-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 900px) {
            .product-detail-container {
                flex-direction: column;
                gap: 1.5rem;
            }
            .product-image-box {
                margin-bottom: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="product-detail-container">
        <div class="product-image-box">
            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="product-info-box">
            <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
            <div class="product-category">Kategori: <?php echo htmlspecialchars($product['category_name']); ?></div>
            <div class="product-price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></div>
            <div class="product-desc"><?php echo nl2br(htmlspecialchars($product['description'])); ?></div>
            <div class="product-stock">Stok: <?php echo $product['stock']; ?> tersedia</div>
            <?php if ($product['stock'] > 0): ?>
                <form class="product-form" method="POST" action="">
                    <span class="qty-label">JUMLAH</span>
                    <input type="number" id="quantity" name="quantity" min="1" max="<?php echo $product['stock']; ?>" value="1" required>
                    <button type="submit" name="add_to_cart" class="btn-add">Tambah ke keranjang</button>
                </form>
            <?php else: ?>
                <div class="alert alert-warning">Produk ini sedang habis stok.</div>
            <?php endif; ?>
        </div>
    </div>
    <?php if (!empty($related_products)): ?>
        <div class="related-section">
            <h3 style="font-weight:700;margin-bottom:1.2rem;">Produk Terkait</h3>
            <div class="related-scroll">
                <?php foreach ($related_products as $related): ?>
                    <div class="related-card">
                        <img src="<?php echo htmlspecialchars($related['image_url']); ?>" alt="<?php echo htmlspecialchars($related['name']); ?>">
                        <div class="related-title"><?php echo htmlspecialchars($related['name']); ?></div>
                        <div class="related-price">Rp <?php echo number_format($related['price'], 0, ',', '.'); ?></div>
                        <a class="related-link" href="product.php?id=<?php echo $related['id']; ?>">Lihat Detail</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</body>
</html> 