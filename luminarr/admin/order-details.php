<?php 
require_once '../api/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$db = getDB();

// Fetch Order
$stmt = $db->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone 
                      FROM orders o 
                      JOIN users u ON o.user_id = u.id 
                      WHERE o.id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Fetch Items
$stmt = $db->prepare("SELECT oi.*, p.name, p.image 
                      FROM order_items oi 
                      JOIN products p ON oi.product_id = p.id 
                      WHERE oi.order_id = ?");
$stmt->execute([$id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $id; ?> — LUMINARR</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-layout">
    <aside class="admin-sidebar">
        <div style="padding: 0 30px 40px;">
            <a href="../index.php" class="logo" style="font-size: 1.5rem;">LUMINARR</a>
        </div>
        <nav>
            <a href="index.php" class="sidebar-link"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="products.php" class="sidebar-link"><i class="fas fa-tshirt"></i> Products</a>
            <a href="categories.php" class="sidebar-link"><i class="fas fa-tags"></i> Categories</a>
            <a href="orders.php" class="sidebar-link active"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="users.php" class="sidebar-link"><i class="fas fa-users"></i> Customers</a>
        </nav>
    </aside>

    <main class="admin-main">
        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
            <div>
                <a href="orders.php" style="color: var(--text-secondary); font-size: 0.9rem;"><i class="fas fa-arrow-left"></i> Back to Orders</a>
                <h1 class="brand-font" style="margin-top: 10px;">Order #<?php echo str_pad($id, 5, '0', STR_PAD_LEFT); ?></h1>
            </div>
            <div style="text-align: right;">
                <span class="status-badge status-<?php echo $order['status']; ?>"><?php echo strtoupper($order['status']); ?></span>
                <p style="margin-top: 10px; color: var(--text-secondary);"><?php echo date('F d, Y H:i', strtotime($order['created_at'])); ?></p>
            </div>
        </header>

        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 40px;">
            <!-- Items List -->
            <div>
                <section style="background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 8px; padding: 30px;">
                    <h3 class="brand-font" style="margin-bottom: 25px;">Order Items</h3>
                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th style="text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td style="display: flex; align-items: center; gap: 15px;">
                                        <img src="<?php echo UPLOAD_URL . $item['image']; ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                        <div>
                                            <p style="font-weight: 600;"><?php echo $item['name']; ?></p>
                                            <p style="font-size: 0.8rem; color: var(--text-muted);">Size: <?php echo $item['size']; ?> | Color: <?php echo $item['color']; ?></p>
                                        </div>
                                    </td>
                                    <td><?php echo formatPrice($item['price']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td style="text-align: right; font-weight: 600;"><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="text-align: right; padding-top: 20px; font-weight: 700;">Grand Total:</td>
                                <td style="text-align: right; padding-top: 20px; font-weight: 700; color: var(--accent); font-size: 1.2rem;"><?php echo formatPrice($order['total']); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </section>
            </div>

            <!-- Customer Details -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <section style="background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 8px; padding: 25px;">
                    <h3 class="brand-font" style="margin-bottom: 20px; font-size: 1.2rem;">Customer Details</h3>
                    <div style="margin-bottom: 15px;">
                        <p style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">Name</p>
                        <p><?php echo $order['customer_name']; ?></p>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <p style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">Email</p>
                        <p><?php echo $order['customer_email']; ?></p>
                    </div>
                    <div>
                        <p style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">Phone</p>
                        <p><?php echo $order['customer_phone']; ?></p>
                    </div>
                </section>

                <section style="background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 8px; padding: 25px;">
                    <h3 class="brand-font" style="margin-bottom: 20px; font-size: 1.2rem;">Shipping Address</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;"><?php echo nl2br($order['shipping_address']); ?></p>
                </section>

                <section style="background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 8px; padding: 25px;">
                    <h3 class="brand-font" style="margin-bottom: 20px; font-size: 1.2rem;">Payment Info</h3>
                    <p style="text-transform: uppercase;"><?php echo $order['payment_method']; ?></p>
                </section>
            </div>
        </div>
    </main>

    <style>
        .status-badge { padding: 6px 15px; border-radius: 4px; font-weight: 600; font-size: 0.8rem; letter-spacing: 1px; }
        .status-pending { background: #3a2a1a; color: #d4a574; }
        .status-processing { background: #1a2a3a; color: #74a5d4; }
        .status-shipped { background: #1a3a2a; color: #74d4a5; }
        .status-delivered { background: #1a3a1a; color: #00e676; }
        .status-cancelled { background: #3a1a1a; color: #ff4d4d; }
    </style>
</body>
</html>
