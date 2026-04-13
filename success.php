<?php
session_start();
// Limpiar sesión después de éxito
unset($_SESSION['terms_accepted']);
unset($_SESSION['otp_email']);
?>

<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Acceso Concedido</title>
  <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
  <div class="container" id="container">
    <div class="form-container sign-in">
      <h1>Acceso Concedido</h1>
      <p>Ha sido autenticado exitosamente. Puede navegar en internet ahora.</p>
      <p>Esta página se cerrará automáticamente en 5 segundos.</p>
      <script>
        setTimeout(function(){
          window.location.href = 'http://192.168.201.1/?res=success'; // Ajustar según Fortigate
        }, 5000);
      </script>
    </div>
  </div>
</body>
</html>