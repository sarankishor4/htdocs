<?php
header('Content-Type: application/json');
require_once __DIR__.'/../includes/auth.php';
if (isset($_SESSION['user_id'])) {
    jsonResponse(['status'=>'success','authenticated'=>true]);
} else {
    jsonResponse(['status'=>'success','authenticated'=>false]);
}
