<?php
require_once __DIR__ . '/../core/includes/db.php';
require_once __DIR__ . '/../core/includes/auth_guard.php';

requireLogin();
header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$pdo = getDB();

try {
    function tableExists(PDO $pdo, string $table): bool {
        try {
            $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            return (bool)$stmt->fetchColumn();
        } catch (Exception $e) {
            return false;
        }
    }

    $assetPrices = [
        'USD' => 1,
        'USD_STAKED' => 1,
        'BTC' => 67000,
        'ETH' => 3500,
        'SOL' => 178.40,
        'AAPL' => 189.50,
        'NVDA' => 875.40
    ];

    // Get Wallets
    $stmt = $pdo->prepare("SELECT currency, balance FROM wallets WHERE user_id = ?");
    $stmt->execute([$userId]);
    $wallets = $stmt->fetchAll();
    
    $fiatBal = 0;
    $cryptoBal = 0;
    $walletBreakdown = [];
    
    foreach ($wallets as $wallet) {
        $currency = $wallet['currency'];
        $balance = (float)$wallet['balance'];
        $usdValue = $balance * ($assetPrices[$currency] ?? 1);

        if ($currency === 'USD' || $currency === 'USD_STAKED') {
            $fiatBal += $usdValue;
        } else {
            $cryptoBal += $usdValue;
        }

        $walletBreakdown[] = [
            'currency' => $currency,
            'balance' => $balance,
            'usd_value' => $usdValue
        ];
    }

    $totalPortfolio = $fiatBal + $cryptoBal;
    
    // Get AI Score
    $stmt = $pdo->prepare("SELECT ai_credit_score, kyc_status, is_verified FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch() ?: [];
    $score = (int)($profile['ai_credit_score'] ?? 0);

    $stmt = $pdo->prepare("
        SELECT
            COALESCE(SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END), 0) AS inflow,
            COALESCE(SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END), 0) AS outflow,
            COUNT(*) AS transaction_count
        FROM transactions
        WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute([$userId]);
    $cashflow = $stmt->fetch() ?: ['inflow' => 0, 'outflow' => 0, 'transaction_count' => 0];

    $cards = [];
    if (tableExists($pdo, 'virtual_cards')) {
        $stmt = $pdo->prepare("SELECT * FROM virtual_cards WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
        $stmt->execute([$userId]);
        $cards = $stmt->fetchAll();
    }

    $budgets = [];
    if (tableExists($pdo, 'budgets')) {
        $stmt = $pdo->prepare("SELECT * FROM budgets WHERE user_id = ? ORDER BY category ASC");
        $stmt->execute([$userId]);
        $budgets = $stmt->fetchAll();
    }

    $beneficiaries = [];
    if (tableExists($pdo, 'beneficiaries')) {
        $stmt = $pdo->prepare("SELECT label, recipient_email, currency, transfer_limit, risk_level FROM beneficiaries WHERE user_id = ? ORDER BY created_at DESC LIMIT 4");
        $stmt->execute([$userId]);
        $beneficiaries = $stmt->fetchAll();
    }

    $unreadNotifications = 0;
    if (tableExists($pdo, 'notifications')) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        $unreadNotifications = (int)$stmt->fetchColumn();
    }

    $stmt = $pdo->prepare("SELECT status, amount, due_date, repaid_amount FROM loans WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
    $stmt->execute([$userId]);
    $loans = $stmt->fetchAll();

    $cryptoExposure = $totalPortfolio > 0 ? round(($cryptoBal / $totalPortfolio) * 100, 1) : 0;
    $riskLevel = 'low';
    if ($cryptoExposure > 45 || $score < 520) {
        $riskLevel = 'high';
    } elseif ($cryptoExposure > 25 || $score < 680) {
        $riskLevel = 'medium';
    }

    $insights = [];
    $insights[] = [
        'title' => 'Portfolio risk',
        'value' => strtoupper($riskLevel),
        'detail' => $cryptoExposure . '% of portfolio is in market-linked assets.'
    ];
    $insights[] = [
        'title' => 'Cashflow',
        'value' => '$' . number_format((float)$cashflow['inflow'] - (float)$cashflow['outflow'], 2),
        'detail' => 'Net movement over the last 30 days.'
    ];
    $insights[] = [
        'title' => 'Trust level',
        'value' => strtoupper($profile['kyc_status'] ?? 'pending'),
        'detail' => ((int)($profile['is_verified'] ?? 0) === 1) ? 'Email verified and profile active.' : 'Email verification is still pending.'
    ];

    $cardSpend = array_reduce($cards, function ($sum, $card) {
        return $sum + (float)($card['spent_this_month'] ?? 0);
    }, 0.0);
    $cardLimit = array_reduce($cards, function ($sum, $card) {
        return $sum + (float)($card['monthly_limit'] ?? 0);
    }, 0.0);
    $budgetLimit = array_reduce($budgets, function ($sum, $budget) {
        return $sum + (float)($budget['monthly_limit'] ?? 0);
    }, 0.0);
    $budgetSpend = array_reduce($budgets, function ($sum, $budget) {
        return $sum + (float)($budget['spent'] ?? 0);
    }, 0.0);
    $activeLoanBalance = array_reduce($loans, function ($sum, $loan) {
        if (($loan['status'] ?? '') === 'active') {
            return $sum + max(0, (float)$loan['amount'] - (float)$loan['repaid_amount']);
        }
        return $sum;
    }, 0.0);
    $projectedBalance = $fiatBal + ((float)$cashflow['inflow'] - (float)$cashflow['outflow']) - ($activeLoanBalance * 0.08);

    $securityCenter = [
        [
            'label' => 'KYC status',
            'status' => $profile['kyc_status'] ?? 'pending',
            'detail' => 'Identity review controls account limits.'
        ],
        [
            'label' => 'Email verification',
            'status' => ((int)($profile['is_verified'] ?? 0) === 1) ? 'verified' : 'pending',
            'detail' => 'Required for trusted account recovery.'
        ],
        [
            'label' => 'Card utilization',
            'status' => $cardLimit > 0 ? round(($cardSpend / $cardLimit) * 100, 1) . '%' : 'not configured',
            'detail' => 'Virtual card spend this month.'
        ],
        [
            'label' => 'Budget utilization',
            'status' => $budgetLimit > 0 ? round(($budgetSpend / $budgetLimit) * 100, 1) . '%' : 'not configured',
            'detail' => 'Tracked spend against monthly budgets.'
        ]
    ];

    $nextActions = [];
    if ((int)($profile['is_verified'] ?? 0) !== 1) {
        $nextActions[] = ['title' => 'Verify email', 'detail' => 'Unlock full account recovery and trust signals.', 'href' => 'profile.php'];
    }
    if (($profile['kyc_status'] ?? 'pending') !== 'verified') {
        $nextActions[] = ['title' => 'Complete KYC', 'detail' => 'Raise limits and reduce transaction reviews.', 'href' => 'profile.php'];
    }
    if ($cryptoExposure > 35) {
        $nextActions[] = ['title' => 'Rebalance exposure', 'detail' => 'Market-linked assets are above the preferred range.', 'href' => 'trade.php'];
    }
    if (!$cards) {
        $nextActions[] = ['title' => 'Create virtual card', 'detail' => 'Separate online spending from your main wallet.', 'href' => 'settings.php'];
    }
    if (!$nextActions) {
        $nextActions[] = ['title' => 'Review activity', 'detail' => 'Your account posture looks stable today.', 'href' => 'history.php'];
    }

    $transactionCount = (int)($cashflow['transaction_count'] ?? 0);
    $fraudPulse = 18;
    $fraudPulse += $riskLevel === 'high' ? 38 : ($riskLevel === 'medium' ? 18 : 6);
    $fraudPulse += $transactionCount > 20 ? 16 : ($transactionCount > 8 ? 8 : 2);
    $fraudPulse += $cryptoExposure > 45 ? 14 : ($cryptoExposure > 25 ? 7 : 2);
    $fraudPulse = min(100, $fraudPulse);
    $fraudStatus = $fraudPulse >= 70 ? 'review' : ($fraudPulse >= 45 ? 'watch' : 'normal');

    $fraudSignals = [
        [
            'label' => 'Velocity check',
            'status' => $transactionCount > 20 ? 'review' : 'clear',
            'detail' => $transactionCount . ' transactions scored in the last 30 days.'
        ],
        [
            'label' => 'Exposure drift',
            'status' => $cryptoExposure > 35 ? 'watch' : 'clear',
            'detail' => $cryptoExposure . '% market-linked exposure against current portfolio value.'
        ],
        [
            'label' => 'Loan pressure',
            'status' => $activeLoanBalance > 1500 ? 'watch' : 'clear',
            'detail' => '$' . number_format($activeLoanBalance, 2) . ' active loan balance after repayments.'
        ]
    ];

    $verifiedProfile = (($profile['kyc_status'] ?? 'pending') === 'verified') && ((int)($profile['is_verified'] ?? 0) === 1);
    $dailyTransferLimit = $verifiedProfile ? 5000 : 1000;
    if ($score >= 800) {
        $dailyTransferLimit += 2500;
    } elseif ($score < 600) {
        $dailyTransferLimit = max(500, $dailyTransferLimit - 500);
    }
    $tradeLimit = $riskLevel === 'high' ? 1000 : ($riskLevel === 'medium' ? 2500 : 5000);
    $cardLimitSuggestion = $cardLimit > 0 ? $cardLimit : 1500;

    $smartLimits = [
        [
            'label' => 'Daily transfer',
            'value' => '$' . number_format($dailyTransferLimit, 0),
            'detail' => $verifiedProfile ? 'Verified profile limit' : 'Limited until verification'
        ],
        [
            'label' => 'Trade limit',
            'value' => '$' . number_format($tradeLimit, 0),
            'detail' => 'Adjusted by exposure risk'
        ],
        [
            'label' => 'Card envelope',
            'value' => '$' . number_format($cardLimitSuggestion, 0),
            'detail' => 'Suggested monthly card ceiling'
        ]
    ];

    $complianceTimeline = [
        [
            'title' => ((int)($profile['is_verified'] ?? 0) === 1) ? 'Email verified' : 'Email pending',
            'detail' => ((int)($profile['is_verified'] ?? 0) === 1) ? 'Recovery and alerts are active.' : 'Verify email to activate trusted recovery.'
        ],
        [
            'title' => 'KYC ' . ($profile['kyc_status'] ?? 'pending'),
            'detail' => $verifiedProfile ? 'Account limits are fully enabled.' : 'Complete KYC to unlock higher limits.'
        ],
        [
            'title' => 'Monitoring enabled',
            'detail' => 'Transfers, trades, cards and loans are scored automatically.'
        ]
    ];
    
    echo json_encode([
        'success' => true,
        'fiat_balance' => $fiatBal,
        'crypto_balance' => $cryptoBal,
        'total_portfolio' => $totalPortfolio,
        'ai_score' => $score,
        'risk_level' => $riskLevel,
        'crypto_exposure' => $cryptoExposure,
        'cashflow' => [
            'inflow' => (float)$cashflow['inflow'],
            'outflow' => (float)$cashflow['outflow'],
            'net' => (float)$cashflow['inflow'] - (float)$cashflow['outflow'],
            'transaction_count' => (int)$cashflow['transaction_count']
        ],
        'wallets' => $walletBreakdown,
        'cards' => $cards,
        'budgets' => $budgets,
        'beneficiaries' => $beneficiaries,
        'loans' => $loans,
        'unread_notifications' => $unreadNotifications,
        'insights' => $insights,
        'security_center' => $securityCenter,
        'forecast' => [
            'projected_balance' => $projectedBalance,
            'card_utilization' => $cardLimit > 0 ? round(($cardSpend / $cardLimit) * 100, 1) : 0,
            'budget_utilization' => $budgetLimit > 0 ? round(($budgetSpend / $budgetLimit) * 100, 1) : 0,
            'active_loan_balance' => $activeLoanBalance
        ],
        'next_actions' => array_slice($nextActions, 0, 4),
        'fraud_monitor' => [
            'pulse' => $fraudPulse,
            'status' => $fraudStatus,
            'signals' => $fraudSignals
        ],
        'smart_limits' => $smartLimits,
        'compliance_timeline' => $complianceTimeline
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
