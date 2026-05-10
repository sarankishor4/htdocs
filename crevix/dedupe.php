<?php
require 'db.php';
$res = $conn->query("SELECT id, file_path FROM media");
$sizes = [];
$deleted = 0;
while($row = $res->fetch_assoc()) {
    if (!file_exists($row['file_path'])) {
        $conn->query("DELETE FROM media WHERE id=" . $row['id']);
        continue;
    }
    $size = filesize($row['file_path']);
    if ($size > 1000 && isset($sizes[$size])) {
        echo "🗑️ Deleting duplicate: " . $row['file_path'] . " (Matches ID " . $sizes[$size] . ")\n";
        $conn->query("DELETE FROM media WHERE id=" . $row['id']);
        $deleted++;
    } else {
        $sizes[$size] = $row['id'];
    }
}
echo "\n✅ Done! Deleted $deleted duplicates.\n";
?>
