<?php
$servername = getenv('GESEX_DB_HOST') ?: 'localhost';
$port = getenv('GESEX_DB_PORT') ?: '3306';
$username = getenv('GESEX_DB_USER') ?: 'root';
$password = getenv('GESEX_DB_PASSWORD') ?: '';
$dbname = getenv('GESEX_DB_NAME') ?: 'auth_system';

try {
  $conn = new PDO("mysql:host=$servername;port=$port;dbname={$dbname};charset=utf8mb4", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
  echo "Error de conexión: " . $e->getMessage();
}
?>
