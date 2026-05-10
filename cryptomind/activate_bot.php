<?php
require_once 'c:\xampp\htdocs\cryptomind\includes\db.php';
$pdo = getDB();
$pdo->exec("UPDATE users SET bot_active=1, bot_risk='high', bot_allocation=25.00");
echo "Bots activated.";
