<?php 
require_once 'includes/header.php'; 

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$db = getDB();
$stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.status = 'active'");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='container' style='padding-top:150px; text-align:center;'><h2>Product not found.</h2><a href='shop.php'>Return to Shop</a></div>";
    require_once 'includes/footer.php';
    exit;
}

$is_wishlisted = false;
if (isset($_SESSION['user_id'])) {
    $ws = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $ws->execute([$_SESSION['user_id'], $id]);
    $is_wishlisted = (bool)$ws->fetch();
}

$related = $db->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4");
$related->execute([$product['category_id'], $id]);
$related_products = $related->fetchAll();
?>

<div class="container" style="padding-top: 150px;">
    <div style="display: flex; gap: 60px; flex-wrap: wrap;">
        <!-- Product Images -->
        <div style="flex: 1; min-width: 350px;">
            <div style="border: 1px solid var(--border); overflow: hidden;">
                <img src="<?php echo UPLOAD_URL . $product['image']; ?>" alt="<?php echo $product['name']; ?>" style="width: 100%; height: 600px; object-fit: cover;">
            </div>
        </div>

        <!-- Product Details -->
        <div style="flex: 1; min-width: 350px;">
            <p style="color: var(--accent); text-transform: uppercase; letter-spacing: 1px; font-size: 0.9rem; margin-bottom: 10px;">
                <?php echo $product['category_name'] ?: 'Apparel'; ?>
            </p>
            <h1 class="brand-font" style="font-size: 3rem; margin-bottom: 15px;"><?php echo $product['name']; ?></h1>
            <p style="font-size: 1.8rem; color: var(--text-primary); margin-bottom: 30px; font-weight: 700;">
                <?php echo formatPrice($product['price']); ?>
            </p>
            
            <div style="border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); padding: 30px 0; margin-bottom: 30px;">
                <p style="color: var(--text-secondary);"><?php echo nl2br($product['description']); ?></p>
            </div>

            <form id="addToCartForm">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                
                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 10px; color: var(--text-secondary);">Select Size</label>
                    <div style="display: flex; gap: 10px;">
                        <?php foreach(['S', 'M', 'L', 'XL'] as $size): ?>
                            <label class="size-btn">
                                <input type="radio" name="size" value="<?php echo $size; ?>" required style="display: none;">
                                <span><?php echo $size; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div style="margin-bottom: 35px;">
                    <label style="display: block; margin-bottom: 10px; color: var(--text-secondary);">Quantity</label>
                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" 
                           style="width: 80px; padding: 12px; background: var(--bg-secondary); border: 1px solid var(--border); color: white;">
                    <span style="margin-left: 15px; color: var(--text-muted); font-size: 0.9rem;">
                        <?php echo $product['stock']; ?> items available
                    </span>
                </div>

                <div style="display: flex; gap: 15px;">
                    <button type="submit" class="btn-primary" style="flex: 1; padding: 20px;">Add to Basket</button>
                    <button type="button" id="wishlistBtn" style="background:transparent; border:1px solid var(--border); color: <?php echo $is_wishlisted ? 'var(--accent)' : 'var(--muted)'; ?>; width: 60px; display:flex; align-items:center; justify-content:center; cursor:none; transition:all 0.3s;">
                        <i class="<?php echo $is_wishlisted ? 'fas' : 'far'; ?> fa-heart" id="wishlistIcon" style="font-size: 1.2rem;"></i>
                    </button>
                </div>
            </form>

            <div style="margin-top: 40px; display: flex; gap: 30px; color: var(--text-muted); font-size: 0.9rem;">
                <span><i class="fas fa-truck" style="margin-right: 8px;"></i> Free Worldwide Shipping</span>
                <span><i class="fas fa-undo" style="margin-right: 8px;"></i> 30-Day Returns</span>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if ($related_products): ?>
    <section style="margin-top: 100px;">
        <h2 class="brand-font" style="margin-bottom: 40px;">You May Also Like</h2>
        <div class="product-grid">
            <?php foreach ($related_products as $rp): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo UPLOAD_URL . $rp['image']; ?>" alt="<?php echo $rp['name']; ?>">
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><a href="product.php?id=<?php echo $rp['id']; ?>"><?php echo $rp['name']; ?></a></h3>
                        <p class="product-price"><?php echo formatPrice($rp['price']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<style>
.size-btn span {
    display: flex; align-items: center; justify-content: center;
    width: 50px; height: 50px;
    border: 1px solid var(--border);
    cursor: pointer;
    transition: var(--transition);
}
.size-btn input:checked + span {
    border-color: var(--accent);
    color: var(--accent);
    background: rgba(212, 165, 116, 0.1);
}
</style>

<script>
document.getElementById('addToCartForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const response = await fetch('api/cart.php?action=add', {
        method: 'POST',
        body: formData
    });
    const result = await response.json();
    if (result.success) {
        updateCartCount();
        alert('Added to your basket!');
    } else {
        if (response.status === 401) window.location.href = 'login.php';
        else alert(result.error);
    }
});

document.getElementById('wishlistBtn')?.addEventListener('click', async () => {
    try {
        const response = await fetch('api/wishlist.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({product_id: <?php echo $product['id']; ?>})
        });
        const result = await response.json();
        if (result.success) {
            const icon = document.getElementById('wishlistIcon');
            const btn = document.getElementById('wishlistBtn');
            if (result.removed) {
                icon.className = 'far fa-heart';
                btn.style.color = 'var(--muted)';
            } else {
                icon.className = 'fas fa-heart';
                btn.style.color = 'var(--accent)';
            }
        } else {
            if (response.status === 401) window.location.href = 'login.php';
            else alert(result.error);
        }
    } catch(e) {
        console.error(e);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
