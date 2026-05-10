<?php 
require_once 'includes/header.php'; 
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];
$db = getDB();

// Fetch Order (ensure it belongs to the logged-in user)
$stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    echo "<script>window.location.href='orders.php';</script>";
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

<div class="container" style="padding-top: 150px; min-height: 80vh;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
        <div>
            <a href="orders.php" style="color: var(--text-secondary); font-size: 0.9rem;"><i class="fas fa-arrow-left"></i> Back to Orders</a>
            <h1 class="brand-font" style="margin-top: 10px;">Order #<?php echo str_pad($id, 5, '0', STR_PAD_LEFT); ?></h1>
        </div>
        <div style="text-align: right;">
            <span class="status-badge status-<?php echo $order['status']; ?>"><?php echo strtoupper($order['status']); ?></span>
            <p style="margin-top: 10px; color: var(--text-secondary);"><?php echo date('F d, Y H:i', strtotime($order['created_at'])); ?></p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 40px;">
        <!-- Items List -->
        <div>
            <section style="background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 8px; padding: 30px;">
                <h3 class="brand-font" style="margin-bottom: 25px;">Order Items</h3>
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="text-align: left;">Product</th>
                            <th style="text-align: left;">Price</th>
                            <th style="text-align: left;">Qty</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td style="display: flex; align-items: center; gap: 15px; padding: 15px 0;">
                                    <img src="<?php echo UPLOAD_URL . $item['image']; ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                    <div>
                                        <p style="font-weight: 600;"><?php echo $item['name']; ?></p>
                                        <p style="font-size: 0.8rem; color: var(--text-muted);">Size: <?php echo $item['size'] ?: 'N/A'; ?> | Color: <?php echo $item['color'] ?: 'N/A'; ?></p>
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

        <!-- Order Details -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
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
</div>

<style>
    .status-badge { padding: 6px 15px; border-radius: 4px; font-weight: 600; font-size: 0.8rem; letter-spacing: 1px; }
    .status-pending { background: #3a2a1a; color: #d4a574; }
    .status-processing { background: #1a2a3a; color: #74a5d4; }
    .status-shipped { background: #1a3a2a; color: #74d4a5; }
    .status-delivered { background: #1a3a1a; color: #00e676; }
    .status-cancelled { background: #3a1a1a; color: #ff4d4d; }
</style>

<?php require_once 'includes/footer.php'; ?>
