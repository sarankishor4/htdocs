<?php 
require_once 'includes/header.php'; 

$db = getDB();
$cat_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;

$products = [];
$categories = [];

try {
    $query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 'active'";
    $params = [];

    if ($cat_id) {
        $query .= " AND p.category_id = ?";
        $params[] = $cat_id;
    }
    if ($search) {
        $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $query .= " ORDER BY p.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    $categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    // Database tables might not exist yet
}
?>

<div class="container" style="padding-top: 120px;">
    <div style="display: flex; gap: 40px;">
        <!-- Sidebar Filters -->
        <aside style="width: 250px; flex-shrink: 0;">
            <h3 class="brand-font" style="margin-bottom: 20px;">Categories</h3>
            <ul style="list-style: none;">
                <li style="margin-bottom: 10px;">
                    <a href="shop.php" style="color: <?php echo !$cat_id ? 'var(--accent)' : 'inherit'; ?>">All Products</a>
                </li>
                <?php foreach ($categories as $cat): ?>
                    <li style="margin-bottom: 10px;">
                        <a href="shop.php?category=<?php echo $cat['id']; ?>" 
                           style="color: <?php echo $cat_id == $cat['id'] ? 'var(--accent)' : 'inherit'; ?>">
                            <?php echo $cat['name']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <!-- Product Grid -->
        <main style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h2 class="brand-font">
                    <?php 
                    if ($search) {
                        echo 'Search Results: ' . htmlspecialchars($search);
                    } else if ($cat_id) {
                        $curr_cat = array_filter($categories, fn($c) => $c['id'] == $cat_id);
                        echo !empty($curr_cat) ? reset($curr_cat)['name'] : 'Shop';
                    } else {
                        echo 'All Products';
                    }
                    ?>
                </h2>
                <p style="color: var(--text-secondary);"><?php echo count($products); ?> Items Found</p>
            </div>

            <div class="product-grid">
                <?php foreach ($products as $p): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo UPLOAD_URL . $p['image']; ?>" alt="<?php echo $p['name']; ?>">
                            <div class="product-overlay">
                                <button class="btn-add-cart" data-id="<?php echo $p['id']; ?>">Add to Basket</button>
                            </div>
                        </div>
                        <div class="product-info">
                            <p class="product-category"><?php echo $p['category_name'] ?: 'Apparel'; ?></p>
                            <h3 class="product-name"><a href="product.php?id=<?php echo $p['id']; ?>"><?php echo $p['name']; ?></a></h3>
                            <p class="product-price"><?php echo formatPrice($p['price']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                    <div style="text-align: center; padding: 100px 0; grid-column: 1/-1;">
                        <i class="fas fa-search" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 20px;"></i>
                        <p>No products found in this category.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<style>
.product-overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: var(--transition);
}
.product-card:hover .product-overlay { opacity: 1; }
.btn-add-cart {
    background: var(--accent);
    color: #000;
    padding: 12px 25px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transform: translateY(20px);
    transition: var(--transition);
}
.product-card:hover .btn-add-cart { transform: translateY(0); }
</style>

<?php require_once 'includes/footer.php'; ?>
