<?php
namespace AI\Core;

class Utils {
    public static function formatCurrency($amount) {
        return '$' . number_format($amount, 2);
    }

    public static function timeAgo($timestamp) {
        $time = strtotime($timestamp);
        $diff = time() - $time;
        
        if ($diff < 60) return "Just now";
        if ($diff < 3600) return round($diff / 60) . "m ago";
        if ($diff < 86400) return round($diff / 3600) . "h ago";
        
        return date('M d', $time);
    }

    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
}
?>
