<?php
require 'db.php';
require 'google_config.php';
session_start();

if (!isset($_GET['code'])) {
    die("Authorization failed. No code received.");
}

$code = $_GET['code'];

// 1. Exchange Code for Access/Refresh Tokens
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'code'          => $code,
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code'
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$token_data = json_decode($response, true);
curl_close($ch);

if (!isset($token_data['access_token'])) {
    die("Error fetching tokens: " . print_r($token_data, true));
}

$access_token  = $token_data['access_token'];
$refresh_token = $token_data['refresh_token'] ?? ''; // Only provided on first consent

// 2. Get User Email
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v2/userinfo');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $access_token]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$user_info = json_decode(curl_exec($ch), true);
curl_close($ch);

$email = $user_info['email'] ?? 'Unknown Account';
$uid = $_SESSION['user_id'];

// 3. Save to Database
$stmt = $pdo->prepare("INSERT INTO cloud_accounts (user_id, service_name, account_email, access_token, refresh_token) 
                      VALUES (?, 'google_drive', ?, ?, ?)
                      ON DUPLICATE KEY UPDATE access_token = VALUES(access_token), refresh_token = VALUES(refresh_token)");
$stmt->execute([$uid, $email, $access_token, $refresh_token]);

header("Location: cloud_hub.php?status=connected");
exit();
?>
