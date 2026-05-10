<?php
header('Content-Type: application/json');
require_once __DIR__.'/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD']!=='POST') jsonResponse(['status'=>'error','message'=>'POST only'],405);

$in = json_decode(file_get_contents('php://input'), true);
$login = trim($in['login']??'');
$pass = $in['password']??'';

if (!$login||!$pass) jsonResponse(['status'=>'error','message'=>'Enter username/email and password']);

$pdo = getDB();
$s = $pdo->prepare("SELECT id,password_hash,is_active FROM users WHERE username=? OR email=?");
$s->execute([$login,$login]);
$u = $s->fetch();

if (!$u||!password_verify($pass,$u['password_hash']))
    jsonResponse(['status'=>'error','message'=>'Invalid credentials']);
if (!$u['is_active'])
    jsonResponse(['status'=>'error','message'=>'Account deactivated']);

loginUser($u['id']);
jsonResponse(['status'=>'success','message'=>'Welcome back!']);
