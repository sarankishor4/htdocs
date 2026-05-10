<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Authentication required'], 401);
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';
$db = getDB();

if ($action === 'create') {
    $shipping_address = sanitize($_POST['shipping_address'] ?? '');
    $payment_method = sanitize($_POST['payment_method'] ?? 'cod');
    $phone = sanitize($_POST['phone'] ?? '');

    if (!$shipping_address) jsonResponse(['error' => 'Shipping address is required'], 400);

    // Get cart items
    $stmt = $db->prepare("SELECT c.*, p.price, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    $items = $stmt->fetchAll();

    if (empty($items)) jsonResponse(['error' => 'Your basket is empty'], 400);

    $total = 0;
    foreach($items as $item) $total += ($item['price'] * $item['quantity']);

    try {
        $db->beginTransaction();

        // 1. Create Order
        $stmt = $db->prepare("INSERT INTO orders (user_id, total, status, payment_method, shipping_address) VALUES (?, ?, 'pending', ?, ?)");
        $stmt->execute([$user_id, $total, $payment_method, $shipping_address]);
        $order_id = $db->lastInsertId();

        // 2. Create Order Items & Update Stock
        $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, size, color) VALUES (?, ?, ?, ?, ?, ?)");
        $stockStmt = $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

        foreach ($items as $item) {
            $itemStmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price'], $item['size'], $item['color']]);
            $stockStmt->execute([$item['quantity'], $item['product_id']]);
        }

        // 3. Clear Cart
        $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);

        $db->commit();
        jsonResponse(['success' => 'Order created', 'order_id' => $order_id]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['error' => 'Failed to place order: ' . $e->getMessage()], 500);
    }
}

if ($action === 'list') {
    $stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll();
    jsonResponse(['orders' => $orders]);
}
?>
