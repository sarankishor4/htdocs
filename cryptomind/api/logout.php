<?php
header('Content-Type: application/json');
require_once __DIR__.'/../includes/auth.php';
logoutUser();
jsonResponse(['status'=>'success']);
