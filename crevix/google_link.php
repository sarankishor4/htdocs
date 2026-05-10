<?php
require 'google_config.php';
session_start();

if (!isset($_SESSION['user_id'])) { die("Access Denied."); }

$params = [
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => implode(' ', $google_scopes),
    'access_type'   => 'offline',
    'prompt'        => 'consent',
    'state'         => $_SESSION['user_id']
];

$auth_url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);

header("Location: $auth_url");
exit();
?>
