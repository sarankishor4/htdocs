<?php 
require_once 'includes/header.php'; 
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
?>

<div class="container" style="padding-top: 120px; min-height: 80vh;">
    <h1 class="brand-font" style="margin-bottom: 40px;">Your Basket</h1>

    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 40px;" id="cartContainer">
        <!-- Cart Items -->
        <div id="cartItemsList">
            <div style="text-align: center; padding: 50px;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem;"></i>
                <p>Loading your basket...</p>
            </div>
        </div>

        <!-- Summary -->
        <div style="background: var(--bg-secondary); padding: 30px; border: 1px solid var(--border); height: fit-content;">
            <h3 class="brand-font" style="margin-bottom: 25px;">Order Summary</h3>
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <span>Subtotal</span>
                <span id="subtotal">$0.00</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <span>Shipping</span>
                <span>Calculated at checkout</span>
            </div>
            <div style="border-top: 1px solid var(--border); margin-top: 20px; padding-top: 20px; display: flex; justify-content: space-between; font-weight: 700; font-size: 1.2rem;">
                <span>Total</span>
                <span id="totalAmount" style="color: var(--accent);">$0.00</span>
            </div>
            <a href="checkout.php" class="btn-primary" style="width: 100%; margin-top: 30px; text-align: center;">Proceed to Checkout</a>
        </div>
    </div>
</div>

<script>
async function loadCart() {
    const response = await fetch('api/cart.php?action=get');
    const data = await response.json();
    const container = document.getElementById('cartItemsList');
    
    if (data.items.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 100px 0; background: var(--bg-secondary); border: 1px solid var(--border);">
                <i class="fas fa-shopping-basket" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 20px;"></i>
                <h2 class="brand-font">Your basket is empty</h2>
                <p style="margin-top: 15px;"><a href="shop.php" class="btn-primary">Go Shopping</a></p>
            </div>
        `;
        document.getElementById('subtotal').innerText = '$0.00';
        document.getElementById('totalAmount').innerText = '$0.00';
        return;
    }

    let subtotal = 0;
    container.innerHTML = data.items.map(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        return `
            <div style="display: flex; gap: 20px; background: var(--bg-secondary); border: 1px solid var(--border); padding: 20px; margin-bottom: 20px; align-items: center;">
                <img src="uploads/products/${item.image}" style="width: 100px; height: 120px; object-fit: cover;">
                <div style="flex: 1;">
                    <h3 class="brand-font">${item.name}</h3>
                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 5px 0;">
                        Size: ${item.size || 'N/A'} | Color: ${item.color || 'N/A'}
                    </p>
                    <p style="color: var(--accent); font-weight: 600;">$${parseFloat(item.price).toFixed(2)}</p>
                </div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button onclick="updateQty(${item.id}, ${item.quantity - 1})" style="background: transparent; border: 1px solid var(--border); color: white; width: 30px; height: 30px;">-</button>
                    <span>${item.quantity}</span>
                    <button onclick="updateQty(${item.id}, ${item.quantity + 1})" style="background: transparent; border: 1px solid var(--border); color: white; width: 30px; height: 30px;">+</button>
                </div>
                <div style="width: 100px; text-align: right; font-weight: 600;">
                    $${itemTotal.toFixed(2)}
                </div>
                <button onclick="removeItem(${item.id})" style="background: transparent; border: none; color: var(--error); cursor: pointer; padding: 10px;">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    }).join('');

    document.getElementById('subtotal').innerText = '$' + subtotal.toFixed(2);
    document.getElementById('totalAmount').innerText = '$' + subtotal.toFixed(2);
    updateCartCount();
}

async function updateQty(cartId, qty) {
    const formData = new FormData();
    formData.append('cart_id', cartId);
    formData.append('quantity', qty);
    await fetch('api/cart.php?action=update', { method: 'POST', body: formData });
    loadCart();
}

async function removeItem(cartId) {
    if (!confirm('Remove this item?')) return;
    const formData = new FormData();
    formData.append('cart_id', cartId);
    await fetch('api/cart.php?action=remove', { method: 'POST', body: formData });
    loadCart();
}

loadCart();
</script>

<?php require_once 'includes/footer.php'; ?>
