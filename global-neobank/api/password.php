<?php
require_once __DIR__ . '/../core/includes/db.php';
require_once __DIR__ . '/../core/includes/auth_guard.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDB();

    if ($action === 'forgot') {
        $email = trim($_POST['email'] ?? '');
        if (empty($email)) {
            echo json_encode(['success' => false, 'error' => 'Email is required.']);
            exit;
        }

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $pdo->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?');
            $stmt->execute([$token, $expires, $user['id']]);

            // SIMULATE EMAIL SENDING
            $emailContent = "To: $email\nSubject: GlobalBank Password Reset\nClick here to reset: " . APP_URL . "/reset.php?token=" . $token . "\n\n";
            file_put_contents(__DIR__ . '/../core/logs/simulated_emails.log', $emailContent, FILE_APPEND);
        }

        // Always return true to prevent email enumeration
        echo json_encode(['success' => true]);

    } elseif ($action === 'reset') {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($token) || empty($password)) {
            echo json_encode(['success' => false, 'error' => 'Missing fields.']);
            exit;
        }

        $stmt = $pdo->prepare('SELECT id, reset_expires FROM users WHERE reset_token = ?');
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'Invalid reset token.']);
            exit;
        }

        if (strtotime($user['reset_expires']) < time()) {
            echo json_encode(['success' => false, 'error' => 'Reset token has expired.']);
            exit;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?');
        if ($stmt->execute([$hash, $user['id']])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to reset password.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid method.']);
}
