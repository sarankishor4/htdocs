<?php
require_once 'config.php';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
    
    if (!$email) {
        jsonResponse(['error' => 'Invalid email address'], 400);
    }
    
    try {
        $stmt = $db->prepare("INSERT INTO subscribers (email) VALUES (?)");
        $stmt->execute([$email]);
        jsonResponse(['success' => true, 'message' => 'Subscribed successfully!']);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            jsonResponse(['success' => true, 'message' => 'You are already subscribed!']);
        }
        jsonResponse(['error' => 'Database error'], 500);
    }
}
