<?php
require_once __DIR__ . '/../core/includes/db.php';
require_once __DIR__ . '/../core/includes/auth_guard.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'register') {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'error' => 'All fields are required.']);
            exit;
        }

        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Email already registered.']);
            exit;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
        $verifyToken = bin2hex(random_bytes(32));
        
        $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, email, password_hash, verification_token) VALUES (?, ?, ?, ?, ?)');
        if ($stmt->execute([$firstName, $lastName, $email, $hash, $verifyToken])) {
            $userId = $pdo->lastInsertId();
            
            // Create default USD wallet
            $stmt = $pdo->prepare("INSERT INTO wallets (user_id, currency, balance) VALUES (?, 'USD', 0)");
            $stmt->execute([$userId]);

            setUserSession([
                'id' => $userId,
                'name' => $firstName . ' ' . $lastName,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'is_verified' => 0
            ]);
            
            // SIMULATE EMAIL SENDING
            $emailContent = "To: $email\nSubject: Verify your GlobalBank Account\nClick here to verify: " . APP_URL . "/verify.php?token=" . $verifyToken . "\n\n";
            file_put_contents(__DIR__ . '/../simulated_emails.txt', $emailContent, FILE_APPEND);
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Registration failed.']);
        }
    } elseif ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            setUserSession([
                'id' => $user['id'],
                'name' => $user['first_name'] . ' ' . $user['last_name'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'is_verified' => $user['is_verified']
            ]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid email or password.']);
        }
    } elseif ($action === 'logout') {
        destroySession();
        echo json_encode(['success' => true]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
