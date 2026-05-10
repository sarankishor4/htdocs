<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Please login to manage your basket'], 401);
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';
$db = getDB();

if ($action === 'add') {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)($_POST['quantity'] ?? 1);
    $size = sanitize($_POST['size'] ?? '');
    $color = sanitize($_POST['color'] ?? '');

    // Check if item already in cart
    $stmt = $db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND size = ? AND color = ?");
    $stmt->execute([$user_id, $product_id, $size, $color]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $db->prepare("UPDATE cart SET quantity = quantity + ? WHERE id = ?");
        $stmt->execute([$quantity, $existing['id']]);
    } else {
        $stmt = $db->prepare("INSERT INTO cart (user_id, product_id, quantity, size, color) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity, $size, $color]);
    }
    jsonResponse(['success' => 'Added to basket']);
}

if ($action === 'remove') {
    $cart_id = (int)$_POST['cart_id'];
    $stmt = $db->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    jsonResponse(['success' => 'Removed from basket']);
}

if ($action === 'update') {
    $cart_id = (int)$_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];
    if ($quantity <= 0) {
        $stmt = $db->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $user_id]);
    } else {
        $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$quantity, $cart_id, $user_id]);
    }
    jsonResponse(['success' => 'Basket updated']);
}

if ($action === 'count') {
    $stmt = $db->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $count = $stmt->fetchColumn() ?: 0;
    jsonResponse(['count' => $count]);
}

if ($action === 'get') {
    $stmt = $db->prepare("SELECT c.*, p.name, p.price, p.image FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    $items = $stmt->fetchAll();
    jsonResponse(['items' => $items]);
}
?>
