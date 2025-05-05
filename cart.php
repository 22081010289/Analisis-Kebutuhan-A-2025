<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle cart operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantity'] as $product_id => $quantity) {
            if ($quantity <= 0) {
                // Remove item if quantity is 0 or negative
                $query = "DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":user_id", $_SESSION['user_id']);
                $stmt->bindParam(":product_id", $product_id);
                $stmt->execute();
            } else {
                // Update quantity
                $query = "UPDATE cart SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":quantity", $quantity);
                $stmt->bindParam(":user_id", $_SESSION['user_id']);
                $stmt->bindParam(":product_id", $product_id);
                $stmt->execute();
            }
        }
    } elseif (isset($_POST['remove_item'])) {
        $product_id = $_POST['product_id'];
        $query = "DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $_SESSION['user_id']);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->execute();
    }
}

// Get cart items with product details
$query = "SELECT c.*, p.name, p.price, p.image_url FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $_SESSION['user_id']);
$stmt->execute();
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Swalayan Bagus</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: #fff; }
        .main-header { margin-bottom: 2.5rem; }
        .cart-main-container {
            max-width: 1200px;
            margin: 40px auto 0 auto;
            display: flex;
            gap: 2.5rem;
            align-items: flex-start;
            flex-wrap: wrap;
        }
        .cart-list-box {
            flex: 2 1 500px;
            min-width: 340px;
        }
        .cart-item-row {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            background: #fafafa;
            border-radius: 1.2rem;
            margin-bottom: 1.2rem;
            padding: 1.2rem 1.2rem;
        }
        .cart-item-img {
            width: 100px;
            height: 100px;
            object-fit: contain;
            border-radius: 1rem;
            background: #fff;
        }
        .cart-item-info {
            flex: 1 1 180px;
        }
        .cart-item-title {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
        }
        .cart-item-price {
            color: #f44336;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }
        .cart-item-qty {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.2rem;
        }
        .cart-item-qty input[type="number"] {
            width: 48px;
            border-radius: 0.5rem;
            border: 1.5px solid #bbb;
            padding: 0.4rem 0.7rem;
            font-size: 1.1rem;
            text-align: center;
        }
        .cart-item-delete {
            background: #aaa;
            color: #fff;
            border: none;
            border-radius: 0.4rem;
            padding: 0.4rem 1.1rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            margin-left: 1rem;
            transition: background 0.2s;
        }
        .cart-item-delete:hover {
            background: #f44336;
        }
        .cart-summary-box {
            flex: 1 1 320px;
            min-width: 280px;
            background: #fff;
            border-radius: 1.2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 2rem 1.5rem 1.5rem 1.5rem;
            margin-top: 0.5rem;
        }
        .cart-summary-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.2rem;
        }
        .cart-summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.7rem;
            font-size: 1.08rem;
        }
        .cart-summary-total {
            font-size: 1.25rem;
            font-weight: 800;
            color: #f44336;
            margin-bottom: 1.2rem;
        }
        .cart-summary-checkout {
            width: 100%;
            background: #f44336;
            color: #fff;
            font-size: 1.2rem;
            font-weight: 700;
            border-radius: 0.7rem;
            padding: 0.9rem 0;
            border: none;
            margin-top: 0.5rem;
            transition: background 0.2s;
            text-align: center;
            display: block;
            text-decoration: none;
        }
        .cart-summary-checkout:hover {
            background: #d32f2f;
        }
        @media (max-width: 900px) {
            .cart-main-container {
                flex-direction: column;
                gap: 1.5rem;
            }
            .cart-summary-box {
                margin-top: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="cart-main-container">
        <div class="cart-list-box">
            <h2 style="font-weight:800;margin-bottom:1.5rem;">Keranjang Belanja</h2>
            <?php if (empty($cart_items)): ?>
                <div style="background:#fafafa;border-radius:1.2rem;padding:2.5rem;text-align:center;">
                    <img src="assets/img/logo.png" alt="Empty Cart" style="width:80px;opacity:0.3;margin-bottom:1.2rem;">
                    <h4>Keranjang Anda kosong</h4>
                    <p style="color:#888;">Silakan tambahkan produk ke keranjang Anda</p>
                    <a href="index.php" class="cart-summary-checkout" style="background:#2563eb;">Lanjutkan Belanja</a>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item-row">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-img">
                            <div class="cart-item-info">
                                <div class="cart-item-title"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="cart-item-price">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></div>
                                <div class="cart-item-qty">
                                    <input type="number" name="quantity[<?php echo $item['product_id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1">
                                    <button type="submit" name="remove_item" value="1" class="cart-item-delete">Delete</button>
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                </div>
                            </div>
                            <div style="font-weight:700;font-size:1.1rem;min-width:90px;text-align:right;">Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></div>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" name="update_cart" class="cart-summary-checkout" style="background:#2563eb;margin-bottom:1.2rem;">Perbarui Keranjang</button>
                </form>
            <?php endif; ?>
        </div>
        <div class="cart-summary-box">
            <div class="cart-summary-title">Total</div>
            <div class="cart-summary-row">
                <span>Subtotal:</span>
                <span>Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
            </div>
            <div class="cart-summary-row">
                <span>Ongkos Kirim:</span>
                <span>Gratis</span>
            </div>
            <div class="cart-summary-total">
                Rp <?php echo number_format($total, 0, ',', '.'); ?>
            </div>
            <a href="checkout.php" class="cart-summary-checkout">Check Out</a>
        </div>
    </div>
</body>
</html> 