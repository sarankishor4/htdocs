<?php 
require_once 'includes/header.php'; 
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$db = getDB();
$user_id = $_SESSION['user_id'];
$cart_items = $db->prepare("SELECT c.*, p.name, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$cart_items->execute([$user_id]);
$items = $cart_items->fetchAll();

if (empty($items)) {
    echo "<script>window.location.href='shop.php';</script>";
    exit;
}

$subtotal = 0;
foreach($items as $item) $subtotal += ($item['price'] * $item['quantity']);
?>

<div class="container" style="padding-top: 150px; min-height: 80vh;">
    <h1 class="brand-font" style="margin-bottom: 40px;">Checkout</h1>

    <form id="checkoutForm" style="display: grid; grid-template-columns: 1fr 400px; gap: 60px;">
        <!-- Billing Details -->
        <div>
            <h2 class="brand-font" style="margin-bottom: 30px; font-size: 1.8rem;">Billing & Shipping</h2>
            
            <div class="form-group">
                <label>Shipping Address</label>
                <textarea name="shipping_address" rows="3" required placeholder="Full address including city and zip"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" required>
                </div>
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" style="width: 100%; padding: 12px; background: var(--bg-tertiary); color: white; border: 1px solid var(--border);">
                        <option value="cod">Cash on Delivery</option>
                        <option value="card">Credit/Debit Card (Stripe)</option>
                        <option value="bank">Bank Transfer</option>
                    </select>
                </div>
            </div>

            <div style="margin-top: 40px; background: rgba(212, 165, 116, 0.1); padding: 30px; border: 1px solid var(--accent);">
                <h3 class="brand-font" style="color: var(--accent); margin-bottom: 15px;">Secure Payment</h3>
                <p style="font-size: 0.9rem; color: var(--text-secondary);">Your transaction is encrypted and secure. We do not store your full card details on our servers.</p>
            </div>
        </div>

        <!-- Order Summary -->
        <div style="background: var(--bg-secondary); padding: 40px; border: 1px solid var(--border); height: fit-content;">
            <h3 class="brand-font" style="margin-bottom: 30px;">Your Order</h3>
            <div style="max-height: 300px; overflow-y: auto; margin-bottom: 30px;">
                <?php foreach($items as $item): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 0.9rem;">
                        <span><?php echo $item['name']; ?> x <?php echo $item['quantity']; ?></span>
                        <span style="font-weight: 600;"><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="border-top: 1px solid var(--border); padding-top: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span>Subtotal</span>
                    <span><?php echo formatPrice($subtotal); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span>Shipping</span>
                    <span style="color: var(--success);">FREE</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 1.5rem; margin-top: 20px; color: var(--accent);">
                    <span>Total</span>
                    <span><?php echo formatPrice($subtotal); ?></span>
                </div>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; margin-top: 40px; padding: 20px;">Place Order</button>
        </div>
    </form>
</div>

<script>
document.getElementById('checkoutForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const response = await fetch('api/orders.php?action=create', {
        method: 'POST',
        body: formData
    });
    const result = await response.json();
    if (result.success) {
        alert('Order placed successfully! Redirecting to your orders...');
        window.location.href = 'orders.php';
    } else {
        alert(result.error);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
