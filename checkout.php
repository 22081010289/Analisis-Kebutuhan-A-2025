<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get user information
$query = "SELECT * FROM users WHERE id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get cart items
$query = "SELECT c.*, p.name, p.price, p.image FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $_SESSION['user_id']);
$stmt->execute();
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Create order
    $query = "INSERT INTO orders (user_id, total_amount, status, shipping_address, phone) 
              VALUES (:user_id, :total_amount, 'pending', :shipping_address, :phone)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $_SESSION['user_id']);
    $stmt->bindParam(":total_amount", $total);
    $stmt->bindParam(":shipping_address", $_POST['shipping_address']);
    $stmt->bindParam(":phone", $_POST['phone']);
    
    if ($stmt->execute()) {
        $order_id = $db->lastInsertId();
        
        // Add order items
        foreach ($cart_items as $item) {
            $query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                      VALUES (:order_id, :product_id, :quantity, :price)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":order_id", $order_id);
            $stmt->bindParam(":product_id", $item['product_id']);
            $stmt->bindParam(":quantity", $item['quantity']);
            $stmt->bindParam(":price", $item['price']);
            $stmt->execute();
            
            // Update product stock
            $query = "UPDATE products SET stock = stock - :quantity WHERE id = :product_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":quantity", $item['quantity']);
            $stmt->bindParam(":product_id", $item['product_id']);
            $stmt->execute();
        }
        
        // Clear cart
        $query = "DELETE FROM cart WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $_SESSION['user_id']);
        $stmt->execute();
        
        $success = "Pesanan berhasil dibuat! Nomor pesanan: #" . $order_id;
    } else {
        $error = "Terjadi kesalahan. Silakan coba lagi.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Metode Pembayaran - Swalayan Bagus</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: #fff; }
        .main-header { margin-bottom: 2.5rem; }
        .payment-container {
            max-width: 1200px;
            margin: 40px auto 0 auto;
            padding: 0 1rem;
        }
        .payment-title {
            font-size: 2.3rem;
            font-weight: 800;
            margin-bottom: 2.5rem;
            color: #222;
        }
        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        .payment-method {
            display: flex;
            align-items: center;
            background: #f44336;
            color: #fff;
            border-radius: 1.2rem;
            padding: 1.5rem 2rem;
            font-size: 1.5rem;
            font-weight: 700;
            cursor: pointer;
            border: 3px solid transparent;
            transition: border 0.2s, background 0.2s;
        }
        .payment-method.selected, .payment-method:hover {
            border: 3px solid #222;
            background: #d32f2f;
        }
        .payment-method input[type="radio"] {
            accent-color: #fff;
            width: 1.7em;
            height: 1.7em;
            margin-right: 1.5rem;
        }
        .payment-method .pm-icon {
            margin-left: auto;
            font-size: 2.2rem;
        }
        .payment-summary {
            display: flex;
            justify-content: flex-end;
            margin-top: 2.5rem;
        }
        .btn-lanjutkan {
            background: #f44336;
            color: #fff;
            font-size: 1.3rem;
            font-weight: 700;
            border-radius: 0.7rem;
            padding: 1.1rem 3.5rem;
            border: none;
            transition: background 0.2s;
        }
        .btn-lanjutkan:hover {
            background: #b71c1c;
        }
        @media (max-width: 700px) {
            .payment-title { font-size: 1.5rem; }
            .payment-method { font-size: 1.1rem; padding: 1rem 1rem; }
            .btn-lanjutkan { width: 100%; padding: 1.1rem 0; }
            .payment-summary { justify-content: center; }
        }
    </style>
    <script>
        function selectPaymentMethod(el) {
            document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
            el.classList.add('selected');
            el.querySelector('input[type=radio]').checked = true;
        }
    </script>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="payment-container">
        <div class="payment-title">Pilih metode pembayaran</div>
        <form method="POST" action="">
            <div class="payment-methods">
                <label class="payment-method" onclick="selectPaymentMethod(this)">
                    <input type="radio" name="payment_method" value="mbanking" required>
                    M-Banking
                    <span class="pm-icon">🏦</span>
                </label>
                <label class="payment-method" onclick="selectPaymentMethod(this)">
                    <input type="radio" name="payment_method" value="creditcard">
                    Kartu Kredit
                    <span class="pm-icon">💳</span>
                </label>
                <label class="payment-method" onclick="selectPaymentMethod(this)">
                    <input type="radio" name="payment_method" value="cod">
                    Cash on Delivery
                    <span class="pm-icon">💵</span>
                </label>
            </div>
            <div class="payment-summary">
                <button type="submit" class="btn-lanjutkan">Lanjutkan</button>
            </div>
        </form>
    </div>
</body>
</html> 