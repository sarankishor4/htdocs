<?php
require_once 'config.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'get_accounts') {
        // For demonstration, we will just fetch all users from both systems
        // In a real app, you would have an authentication layer
        
        $cryptoUsers = $crypto_pdo->query("SELECT id, username, email, balance FROM users")->fetchAll();
        $bankUsers = $bank_pdo->query("SELECT id, first_name, last_name, email FROM users")->fetchAll();
        
        $bankWallets = $bank_pdo->query("SELECT user_id, balance, currency FROM wallets")->fetchAll();
        
        // Group wallets by user
        $walletsByUserId = [];
        foreach ($bankWallets as $w) {
            if (!isset($walletsByUserId[$w['user_id']])) $walletsByUserId[$w['user_id']] = [];
            $walletsByUserId[$w['user_id']][] = $w;
        }

        foreach ($bankUsers as &$bu) {
            $bu['wallets'] = $walletsByUserId[$bu['id']] ?? [];
        }

        jsonResponse([
            'crypto_users' => $cryptoUsers,
            'bank_users' => $bankUsers
        ]);
    }
    
    if ($action === 'get_user_details') {
        $cryptoId = intval($_GET['crypto_id'] ?? 0);
        $bankId = intval($_GET['bank_id'] ?? 0);
        
        $response = ['portfolio' => [], 'transactions' => []];
        
        if ($cryptoId > 0) {
            $response['portfolio'] = $crypto_pdo->query("SELECT coin_symbol, coin_name, amount, avg_buy_price FROM portfolio WHERE user_id = $cryptoId")->fetchAll();
        }
        
        if ($bankId > 0) {
            $response['transactions'] = $bank_pdo->query("SELECT type, amount, status, description, created_at FROM transactions WHERE user_id = $bankId ORDER BY created_at DESC LIMIT 10")->fetchAll();
        }
        
        jsonResponse($response);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($action === 'transfer') {
        $cryptoUserId = $data['crypto_user_id'] ?? null;
        $bankUserId = $data['bank_user_id'] ?? null;
        $amount = floatval($data['amount'] ?? 0);
        $direction = $data['direction'] ?? ''; // 'bank_to_crypto' or 'crypto_to_bank'
        
        if (!$cryptoUserId || !$bankUserId || $amount <= 0 || !in_array($direction, ['bank_to_crypto', 'crypto_to_bank'])) {
            jsonResponse(['error' => 'Invalid parameters'], 400);
        }
        
        try {
            $crypto_pdo->beginTransaction();
            $bank_pdo->beginTransaction();
            
            // Get current balances
            $cUser = $crypto_pdo->query("SELECT balance FROM users WHERE id = " . intval($cryptoUserId))->fetch();
            $bWallet = $bank_pdo->query("SELECT id, balance FROM wallets WHERE user_id = " . intval($bankUserId) . " AND currency = 'USD'")->fetch();
            
            if (!$cUser || !$bWallet) {
                throw new Exception("Account not found");
            }
            
            if ($direction === 'bank_to_crypto') {
                if ($bWallet['balance'] < $amount) throw new Exception("Insufficient bank balance");
                
                // Deduct from bank
                $bank_pdo->exec("UPDATE wallets SET balance = balance - $amount WHERE id = " . $bWallet['id']);
                // Log bank transaction
                $stmt = $bank_pdo->prepare("INSERT INTO transactions (user_id, type, amount, currency, status, description) VALUES (?, 'transfer', ?, 'USD', 'completed', ?)");
                $stmt->execute([$bankUserId, $amount, 'Transfer to CryptoMind']);
                
                // Add to crypto
                $crypto_pdo->exec("UPDATE users SET balance = balance + $amount WHERE id = " . intval($cryptoUserId));
            } else {
                if ($cUser['balance'] < $amount) throw new Exception("Insufficient crypto balance");
                
                // Deduct from crypto
                $crypto_pdo->exec("UPDATE users SET balance = balance - $amount WHERE id = " . intval($cryptoUserId));
                
                // Add to bank
                $bank_pdo->exec("UPDATE wallets SET balance = balance + $amount WHERE id = " . $bWallet['id']);
                // Log bank transaction
                $stmt = $bank_pdo->prepare("INSERT INTO transactions (user_id, type, amount, currency, status, description) VALUES (?, 'transfer', ?, 'USD', 'completed', ?)");
                $stmt->execute([$bankUserId, $amount, 'Deposit from CryptoMind']);
            }
            
            $crypto_pdo->commit();
            $bank_pdo->commit();
            
            jsonResponse(['success' => true, 'message' => 'Transfer successful']);
            
        } catch (Exception $e) {
            $crypto_pdo->rollBack();
            $bank_pdo->rollBack();
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }
    if ($action === 'connect_identity') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $bankEmail = trim($data['bank_email'] ?? '');
        $bankPass = $data['bank_password'] ?? '';
        
        $cryptoEmail = trim($data['crypto_email'] ?? '');
        $cryptoPass = $data['crypto_password'] ?? '';
        
        $bId = null;
        $cId = null;
        $verifiedBankEmail = '';
        $verifiedCryptoEmail = '';
        
        // Scenario 1: User provides Bank credentials
        if (!empty($bankEmail) && !empty($bankPass)) {
            $stmtB = $bank_pdo->prepare("SELECT id, email, password_hash FROM users WHERE email = ?");
            $stmtB->execute([$bankEmail]);
            $bankUser = $stmtB->fetch();
            
            if ($bankUser && password_verify($bankPass, $bankUser['password_hash'])) {
                $bId = $bankUser['id'];
                $verifiedBankEmail = $bankUser['email'];
            }
        }
        
        // Scenario 2: User provides Crypto credentials
        if (!empty($cryptoEmail) && !empty($cryptoPass)) {
            $stmtC = $crypto_pdo->prepare("SELECT id, email, password_hash FROM users WHERE email = ?");
            $stmtC->execute([$cryptoEmail]);
            $cryptoUser = $stmtC->fetch();
            
            if ($cryptoUser && password_verify($cryptoPass, $cryptoUser['password_hash'])) {
                $cId = $cryptoUser['id'];
                $verifiedCryptoEmail = $cryptoUser['email'];
            }
        }
        
        if (!$bId && !$cId) {
            jsonResponse(['error' => 'Invalid credentials provided. Please check your email and password.'], 401);
        }
        
        // If we only verified Bank, check if a saved connection exists with matching email
        if ($bId && !$cId) {
            $stmt = $nexus_pdo->prepare("SELECT crypto_user_id, crypto_email FROM connections WHERE bank_user_id = ? AND bank_email = ?");
            $stmt->execute([$bId, $verifiedBankEmail]);
            $conn = $stmt->fetch();
            if ($conn) {
                $cId = $conn['crypto_user_id'];
                $verifiedCryptoEmail = $conn['crypto_email'];
            } else {
                jsonResponse(['error' => 'No linked CryptoMind account found for this email. Please provide both credentials to create a new link.'], 404);
            }
        }
        
        // If we only verified Crypto, check if a saved connection exists with matching email
        if ($cId && !$bId) {
            $stmt = $nexus_pdo->prepare("SELECT bank_user_id, bank_email FROM connections WHERE crypto_user_id = ? AND crypto_email = ?");
            $stmt->execute([$cId, $verifiedCryptoEmail]);
            $conn = $stmt->fetch();
            if ($conn) {
                $bId = $conn['bank_user_id'];
                $verifiedBankEmail = $conn['bank_email'];
            } else {
                jsonResponse(['error' => 'No linked Global Neobank account found for this email. Please provide both credentials to create a new link.'], 404);
            }
        }
        
        // If we verified both manually, save the connection with emails
        if (!empty($bankPass) && !empty($cryptoPass)) {
            try {
                $stmtNexus = $nexus_pdo->prepare("INSERT INTO connections (bank_user_id, bank_email, crypto_user_id, crypto_email) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE bank_email = VALUES(bank_email), crypto_email = VALUES(crypto_email)");
                $stmtNexus->execute([$bId, $verifiedBankEmail, $cId, $verifiedCryptoEmail]);
            } catch (Exception $e) {}
        }
        
        // Update last_login timestamp
        try {
            $nexus_pdo->prepare("UPDATE connections SET last_login = NOW() WHERE bank_user_id = ? AND crypto_user_id = ?")->execute([$bId, $cId]);
        } catch (Exception $e) {}
        
        jsonResponse([
            'success' => true,
            'crypto_user_id' => $cId,
            'bank_user_id' => $bId,
            'bank_email' => $verifiedBankEmail,
            'crypto_email' => $verifiedCryptoEmail,
            'message' => 'Identities verified and securely linked via Nexus Hub.'
        ]);
    }
}
