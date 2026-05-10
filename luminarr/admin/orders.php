<?php 
require_once '../api/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$db = getDB();

if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = sanitize($_POST['status']);
    $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);
    header('Location: orders.php?msg=Status updated');
    exit;
}

$orders = $db->query("SELECT o.*, u.name as customer_name, u.email as customer_email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management — LUMINARR</title>
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
        <header style="margin-bottom: 40px;">
            <h1 class="brand-font">Order Management</h1>
        </header>

        <section style="background: var(--bg-secondary); padding: 30px; border: 1px solid var(--border); border-radius: 8px;">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>#<?php echo str_pad($o['id'], 5, '0', STR_PAD_LEFT); ?></td>
                            <td>
                                <strong><?php echo $o['customer_name']; ?></strong><br>
                                <span style="font-size: 0.8rem; color: var(--text-muted);"><?php echo $o['customer_email']; ?></span>
                            </td>
                            <td style="color: var(--accent); font-weight: 600;"><?php echo formatPrice($o['total']); ?></td>
                            <td>
                                <form action="orders.php" method="POST" style="display: flex; gap: 10px; align-items: center;">
                                    <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                                    <select name="status" style="padding: 5px; background: var(--bg-tertiary); color: white; border: 1px solid var(--border);">
                                        <option value="pending" <?php echo $o['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo $o['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="shipped" <?php echo $o['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="delivered" <?php echo $o['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo $o['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="action-btn" style="background: var(--accent); color: #000; border: none;">Update</button>
                                </form>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($o['created_at'])); ?></td>
                            <td>
                                <a href="order-details.php?id=<?php echo $o['id']; ?>" class="action-btn" style="background: var(--bg-tertiary); color: white;">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
