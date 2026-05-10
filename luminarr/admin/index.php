<?php 
require_once '../api/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$db = getDB();
$total_orders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue = $db->query("SELECT SUM(total) FROM orders")->fetchColumn() ?: 0;
$total_users = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_products = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();

$recent_orders = $db->query("SELECT o.*, u.name as customer FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — LUMINARR</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .stat-card {
            background: var(--bg-secondary);
            padding: 25px;
            border: 1px solid var(--border);
            border-radius: 8px;
        }
        .stat-card h3 { color: var(--text-secondary); font-size: 0.9rem; text-transform: uppercase; margin-bottom: 10px; }
        .stat-card .value { font-size: 2rem; font-weight: 700; color: var(--accent); }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border); }
        th { color: var(--text-secondary); font-weight: 500; }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            text-transform: capitalize;
        }
        .status-pending { background: rgba(212, 165, 116, 0.2); color: var(--accent); }
        .status-delivered { background: rgba(0, 230, 118, 0.2); color: var(--success); }
    </style>
</head>
<body class="admin-layout">
    <aside class="admin-sidebar">
        <div style="padding: 0 30px 40px;">
            <a href="../index.php" class="logo" style="font-size: 1.5rem;">LUMINARR</a>
        </div>
        <nav>
            <a href="index.php" class="sidebar-link active"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="products.php" class="sidebar-link"><i class="fas fa-tshirt"></i> Products</a>
            <a href="categories.php" class="sidebar-link"><i class="fas fa-tags"></i> Categories</a>
            <a href="orders.php" class="sidebar-link"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="users.php" class="sidebar-link"><i class="fas fa-users"></i> Customers</a>
            <a href="sales.php" class="sidebar-link"><i class="fas fa-dollar-sign"></i> Sales Report</a>
            <div style="margin-top: 40px; border-top: 1px solid var(--border); padding-top: 20px;">
                <a href="../api/auth.php?action=logout" class="sidebar-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
    </aside>

    <main class="admin-main">
        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
            <h1 class="brand-font">Dashboard Overview</h1>
            <div style="display: flex; align-items: center; gap: 15px;">
                <span>Welcome, <?php echo $_SESSION['user_name']; ?></span>
                <img src="https://ui-avatars.com/api/?name=Admin&background=d4a574&color=000" style="width: 40px; border-radius: 50%;">
            </div>
        </header>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 25px; margin-bottom: 50px;">
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <div class="value"><?php echo formatPrice($total_revenue); ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="value"><?php echo $total_orders; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Customers</h3>
                <div class="value"><?php echo $total_users; ?></div>
            </div>
            <div class="stat-card">
                <h3>Active Products</h3>
                <div class="value"><?php echo $total_products; ?></div>
            </div>
        </div>

        <section style="background: var(--bg-secondary); padding: 30px; border: 1px solid var(--border); border-radius: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="brand-font">Recent Orders</h2>
                <a href="orders.php" style="color: var(--accent); font-size: 0.9rem;">View All</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td>#<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo $order['customer']; ?></td>
                            <td><?php echo formatPrice($order['total']); ?></td>
                            <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo $order['status']; ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recent_orders)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 40px;">No orders yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
