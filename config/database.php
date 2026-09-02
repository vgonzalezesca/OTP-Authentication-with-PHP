<?php
$servername = getenv('GESEX_DB_HOST') ?: 'localhost';
$port = getenv('GESEX_DB_PORT') ?: '3306';
$username = getenv('GESEX_DB_USER') ?: 'root';
$password = getenv('GESEX_DB_PASSWORD') ?: '';
$dbname = getenv('GESEX_DB_NAME') ?: 'auth_system';
$conn = null;
$databaseError = null;

try {
  $conn = new PDO("mysql:host=$servername;port=$port;dbname={$dbname};charset=utf8mb4", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  $databaseError = 'No fue posible conectar con la base de datos.';
  error_log('Error de conexión MySQL: ' . $e->getMessage());
}
?>
