<?php 
require_once '../api/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Run Python Analytics Script
$python_output = shell_exec("python ../python/analytics.py");
$analytics = json_decode($python_output, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Analytics — LUMINARR</title>
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
            <a href="orders.php" class="sidebar-link"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="users.php" class="sidebar-link"><i class="fas fa-users"></i> Customers</a>
            <a href="sales.php" class="sidebar-link active"><i class="fas fa-chart-bar"></i> Sales Analytics</a>
        </nav>
    </aside>

    <main class="admin-main">
        <header style="margin-bottom: 40px;">
            <h1 class="brand-font">Advanced Sales Analytics (Python Powered)</h1>
            <p style="color: var(--text-secondary);">Last updated: <?php echo $analytics['generated_at'] ?? 'N/A'; ?></p>
        </header>

        <?php if (isset($analytics['error'])): ?>
            <div style="background: rgba(255, 77, 77, 0.1); border: 1px solid var(--error); padding: 20px; color: var(--error);">
                <strong>Error running analytics:</strong> <?php echo $analytics['error']; ?><br>
                <small>Make sure 'mysql-connector-python' is installed (pip install mysql-connector-python)</small>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <!-- Top Products -->
                <div style="background: var(--bg-secondary); padding: 30px; border: 1px solid var(--border);">
                    <h3 class="brand-font" style="margin-bottom: 20px;">Top Selling Products</h3>
                    <?php foreach($analytics['top_products'] as $tp): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                            <span><?php echo $tp['name']; ?></span>
                            <span style="color: var(--accent); font-weight: 700;"><?php echo $tp['total_sold']; ?> Units</span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Category Distribution -->
                <div style="background: var(--bg-secondary); padding: 30px; border: 1px solid var(--border);">
                    <h3 class="brand-font" style="margin-bottom: 20px;">Product Distribution</h3>
                    <?php foreach($analytics['category_distribution'] as $cd): ?>
                        <div style="margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem;">
                                <span><?php echo $cd['name']; ?></span>
                                <span><?php echo $cd['product_count']; ?> Items</span>
                            </div>
                            <div style="height: 6px; background: var(--bg-tertiary); width: 100%;">
                                <div style="height: 100%; background: var(--accent); width: <?php echo min(100, $cd['product_count'] * 10); ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Sales History -->
                <div style="grid-column: 1/-1; background: var(--bg-secondary); padding: 30px; border: 1px solid var(--border);">
                    <h3 class="brand-font" style="margin-bottom: 20px;">Recent Revenue (Last 7 Days)</h3>
                    <div style="display: flex; align-items: flex-end; gap: 20px; height: 200px; padding-top: 20px;">
                        <?php foreach($analytics['sales_history'] as $sh): ?>
                            <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 10px;">
                                <div style="width: 40px; background: var(--accent); height: <?php echo min(100, ($sh['revenue'] / 1000) * 100); ?>px;"></div>
                                <span style="font-size: 0.7rem; color: var(--text-muted);"><?php echo date('M d', strtotime($sh['date'])); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
