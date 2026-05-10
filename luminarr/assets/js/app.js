async function updateCartCount() {
    const cartBadge = document.getElementById('cart-count');
    if (!cartBadge) return;
    
    try {
        const response = await fetch('api/cart.php?action=count');
        const data = await response.json();
        cartBadge.innerText = data.count || 0;
        cartBadge.style.display = data.count > 0 ? 'flex' : 'none';
    } catch (e) {
        cartBadge.style.display = 'none';
    }
}

// Initial count on page load
document.addEventListener('DOMContentLoaded', updateCartCount);

// Add to Cart Logic (Global)
document.addEventListener('click', async (e) => {
    if (e.target.classList.contains('btn-add-cart')) {
        const productId = e.target.getAttribute('data-id');
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('quantity', 1);
        
        const response = await fetch('api/cart.php?action=add', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        if (data.success) {
            updateCartCount();
            alert('Added to basket!');
        } else if (data.error) {
            if (response.status === 401) {
                window.location.href = 'login.php';
            } else {
                alert(data.error);
            }
        }
    }
});
