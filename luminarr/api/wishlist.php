<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $product_id = (int)($data['product_id'] ?? 0);
    
    if (!$product_id) {
        jsonResponse(['error' => 'Invalid product'], 400);
    }
    
    try {
        $stmt = $db->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $product_id]);
        jsonResponse(['success' => true, 'message' => 'Added to wishlist!']);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            // Remove from wishlist if it already exists (toggle)
            $stmt = $db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$_SESSION['user_id'], $product_id]);
            jsonResponse(['success' => true, 'message' => 'Removed from wishlist!', 'removed' => true]);
        }
        jsonResponse(['error' => 'Database error'], 500);
    }
}
