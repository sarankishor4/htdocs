<?php
require_once __DIR__ . '/../core/includes/auth_guard.php';
require_once __DIR__ . '/../core/includes/db.php';
requireLogin();
header('Content-Type: application/json');

$pdo = getDB();
$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
}
elseif ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject  = trim($_POST['subject'] ?? '');
    $message  = trim($_POST['message'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    if (empty($subject) || empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Subject and message required.']);
        exit;
    }
    $stmt = $pdo->prepare("INSERT INTO support_tickets (user_id, subject, message, priority) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $subject, $message, $priority]);
    echo json_encode(['success' => true]);
}
// Admin endpoints
elseif ($action === 'all_tickets') {
    // Admin only
    $stmt = $pdo->prepare("SELECT st.*, u.email, u.first_name, u.last_name FROM support_tickets st LEFT JOIN users u ON st.user_id = u.id ORDER BY FIELD(st.priority,'urgent','high','medium','low'), st.created_at DESC");
    $stmt->execute();
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
}
elseif ($action === 'reply' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticketId = (int)($_POST['ticket_id'] ?? 0);
    $reply    = trim($_POST['reply'] ?? '');
    $status   = $_POST['status'] ?? 'resolved';
    if (empty($reply)) {
        echo json_encode(['success' => false, 'error' => 'Reply cannot be empty.']);
        exit;
    }
    $stmt = $pdo->prepare("UPDATE support_tickets SET admin_reply = ?, status = ? WHERE id = ?");
    $stmt->execute([$reply, $status, $ticketId]);
    echo json_encode(['success' => true]);
}
