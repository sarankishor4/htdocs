<?php
require 'db.php';
$res = $conn->query("SELECT * FROM media ORDER BY id DESC LIMIT 5");
echo "LAST 5 MEDIA ITEMS:\n";
while($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Path: " . $row['file_path'] . " | Exists: " . (file_exists($row['file_path']) ? 'YES' : 'NO') . "\n";
    if (file_exists($row['file_path'])) {
        echo "   Size: " . filesize($row['file_path']) . " bytes\n";
    }
}
?>
