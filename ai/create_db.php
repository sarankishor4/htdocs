<?php
$host = 'localhost';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

echo "Creating Database ai_nexus...\n";
$conn->query("CREATE DATABASE IF NOT EXISTS ai_nexus");
$conn->close();

echo "Database Created.\n";
?>
