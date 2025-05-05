<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get user's orders
$query = "SELECT o.*, COUNT(oi.id) as item_count 
          FROM orders o 
          LEFT JOIN order_items oi ON o.id = oi.order_id 
          WHERE o.user_id = :user_id 
          GROUP BY o.id 
          ORDER BY o.created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Swalayan Bagus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .orders-container {
            max-width: 1200px;
            margin: 50px auto;
        }
        .card {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .order-item {
            border-bottom: 1px solid #dee2e6;
            padding: 1rem 0;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-shipped {
            background-color: #d4edda;
            color: #155724;
        }
        .status-delivered {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <div class="orders-container">
            <h2 class="mb-4">Pesanan Saya</h2>
            
            <?php if (empty($orders)): ?>
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-shopping-bag fa-3x mb-3 text-muted"></i>
                        <h4>Anda belum memiliki pesanan</h4>
                        <p class="text-muted">Mulai belanja sekarang dan temukan produk menarik</p>
                        <a href="index.php" class="btn btn-primary">Mulai Belanja</a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">Pesanan #<?php echo $order['id']; ?></h5>
                                <small class="text-muted">Tanggal: <?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></small>
                            </div>
                            <div>
                                <?php
                                $status_class = '';
                                switch ($order['status']) {
                                    case 'pending':
                                        $status_class = 'status-pending';
                                        break;
                                    case 'processing':
                                        $status_class = 'status-processing';
                                        break;
                                    case 'shipped':
                                        $status_class = 'status-shipped';
                                        break;
                                    case 'delivered':
                                        $status_class = 'status-delivered';
                                        break;
                                    case 'cancelled':
                                        $status_class = 'status-cancelled';
                                        break;
                                }
                                ?>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php
                            // Get order items
                            $query = "SELECT oi.*, p.name, p.image FROM order_items oi 
                                     JOIN products p ON oi.product_id = p.id 
                                     WHERE oi.order_id = :order_id";
                            $stmt = $db->prepare($query);
                            $stmt->bindParam(":order_id", $order['id']);
                            $stmt->execute();
                            $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            
                            <?php foreach ($order_items as $item): ?>
                                <div class="order-item row align-items-center">
                                    <div class="col-md-2">
                                        <img src="uploads/products/<?php echo htmlspecialchars($item['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             class="product-image">
                                    </div>
                                    <div class="col-md-6">
                                        <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                                        <p class="mb-0">Jumlah: <?php echo $item['quantity']; ?></p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <p class="mb-0">Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <h6>Alamat Pengiriman</h6>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                                    <p class="mb-0">Telepon: <?php echo htmlspecialchars($order['phone']); ?></p>
                                </div>
                                <div class="col-md-6 text-end">
                                    <h6>Total Pesanan</h6>
                                    <h4 class="mb-0">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 