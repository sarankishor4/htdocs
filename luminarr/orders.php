<?php 
require_once 'includes/header.php'; 
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$db = getDB();
$user_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>

<div class="container" style="padding-top: 150px; min-height: 80vh;">
    <h1 class="brand-font" style="margin-bottom: 40px;">My Orders</h1>

    <?php if (empty($orders)): ?>
        <div style="text-align: center; padding: 100px 0; background: var(--bg-secondary); border: 1px solid var(--border);">
            <i class="fas fa-box-open" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 20px;"></i>
            <h2 class="brand-font">No orders yet</h2>
            <p style="margin-top: 20px;"><a href="shop.php" class="btn-primary">Start Shopping</a></p>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 25px;">
            <?php foreach ($orders as $order): ?>
                <div style="background: var(--bg-secondary); border: 1px solid var(--border); padding: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid var(--border); padding-bottom: 20px; margin-bottom: 20px;">
                        <div>
                            <p style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">Order Number</p>
                            <h3 style="margin-top: 5px;">#<?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?></h3>
                        </div>
                        <div style="text-align: right;">
                            <p style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">Status</p>
                            <span class="status-badge status-<?php echo $order['status']; ?>" style="margin-top: 5px; display: inline-block;">
                                <?php echo strtoupper($order['status']); ?>
                            </span>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <p style="color: var(--text-secondary);"><i class="far fa-calendar-alt"></i> Placed on <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                            <p style="color: var(--text-secondary); margin-top: 5px;"><i class="fas fa-map-marker-alt"></i> <?php echo $order['shipping_address']; ?></p>
                        </div>
                        <div style="text-align: right;">
                            <p style="color: var(--text-muted); font-size: 0.9rem;">Total Amount</p>
                            <p style="font-size: 1.5rem; font-weight: 700; color: var(--accent);"><?php echo formatPrice($order['total']); ?></p>
                            <a href="order-details.php?id=<?php echo $order['id']; ?>" style="color: var(--accent); font-size: 0.9rem; text-decoration: underline; margin-top: 10px; display: block;">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.status-badge {
    padding: 6px 15px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.75rem;
    letter-spacing: 1px;
}
.status-pending { background: #3a2a1a; color: #d4a574; }
.status-processing { background: #1a2a3a; color: #74a5d4; }
.status-shipped { background: #1a3a2a; color: #74d4a5; }
.status-delivered { background: #1a3a1a; color: #00e676; }
</style>

<?php require_once 'includes/footer.php'; ?>
