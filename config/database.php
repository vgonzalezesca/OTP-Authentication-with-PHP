<?php
$servername = "localhost";
$username = "root";
$password = "Evelynsoledad#@2112";
$dbname = "auth_system";

try {
  $conn = new PDO("mysql:host=$servername;dbname={$dbname};charset=utf8mb4", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
  echo "Error de conexión: " . $e->getMessage();
}
?>
