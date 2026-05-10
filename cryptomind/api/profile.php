<?php
header('Content-Type: application/json');
require_once __DIR__.'/../includes/auth.php';
$userId = requireAuth();
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD']==='GET') {
    $u = getCurrentUser();
    if (!$u) jsonResponse(['status'=>'error','message'=>'Not found'],404);

    $u['balance'] = (float)$u['balance'];

    // Stats
    $s = $pdo->prepare("SELECT COUNT(*) FROM trades WHERE user_id=?"); $s->execute([$userId]);
    $u['total_trades'] = (int)$s->fetchColumn();

    $s = $pdo->prepare("SELECT COUNT(*) FROM analysis_log WHERE user_id=?"); $s->execute([$userId]);
    $u['total_analyses'] = (int)$s->fetchColumn();

    $s = $pdo->prepare("SELECT COALESCE(SUM(profit_loss),0) FROM trades WHERE user_id=? AND profit_loss IS NOT NULL"); $s->execute([$userId]);
    $u['total_pnl'] = (float)$s->fetchColumn();

    $s = $pdo->prepare("SELECT COUNT(*) FROM trades WHERE user_id=? AND profit_loss>0"); $s->execute([$userId]);
    $wins = (int)$s->fetchColumn();
    $s = $pdo->prepare("SELECT COUNT(*) FROM trades WHERE user_id=? AND profit_loss IS NOT NULL"); $s->execute([$userId]);
    $closed = (int)$s->fetchColumn();
    $u['win_rate'] = $closed>0 ? round(($wins/$closed)*100) : 0;

    $u['member_days'] = max(1, (int)((time()-strtotime($u['created_at']))/86400));

    jsonResponse(['status'=>'success','data'=>$u]);
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $in = json_decode(file_get_contents('php://input'), true);
    $action = $in['action']??'update';

    if ($action==='update') {
        $name = trim($in['full_name']??'');
        $bio = trim($in['bio']??'');
        $email = trim($in['email']??'');

        if ($email && !filter_var($email,FILTER_VALIDATE_EMAIL))
            jsonResponse(['status'=>'error','message'=>'Invalid email']);
        if ($email) {
            $c = $pdo->prepare("SELECT id FROM users WHERE email=? AND id!=?"); $c->execute([$email,$userId]);
            if ($c->fetch()) jsonResponse(['status'=>'error','message'=>'Email taken']);
        }

        $pdo->prepare("UPDATE users SET full_name=?, bio=?, email=COALESCE(NULLIF(?,''),email) WHERE id=?")
            ->execute([$name,$bio,$email,$userId]);
        jsonResponse(['status'=>'success','message'=>'Profile updated']);
    }

    if ($action==='change_password') {
        $cur = $in['current_password']??'';
        $new = $in['new_password']??'';
        if (strlen($new)<6) jsonResponse(['status'=>'error','message'=>'Min 6 characters']);

        $s = $pdo->prepare("SELECT password_hash FROM users WHERE id=?"); $s->execute([$userId]);
        if (!password_verify($cur,$s->fetchColumn()))
            jsonResponse(['status'=>'error','message'=>'Current password wrong']);

        $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([password_hash($new,PASSWORD_BCRYPT),$userId]);
        jsonResponse(['status'=>'success','message'=>'Password changed']);
    }

    if ($action==='delete_account') {
        $pw = $in['password']??'';
        $s = $pdo->prepare("SELECT password_hash FROM users WHERE id=?"); $s->execute([$userId]);
        if (!password_verify($pw,$s->fetchColumn()))
            jsonResponse(['status'=>'error','message'=>'Wrong password']);
        $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$userId]);
        logoutUser();
        jsonResponse(['status'=>'success','message'=>'Account deleted']);
    }
}
