<?php
session_start();
unset($_SESSION['terms_accepted']);
unset($_SESSION['otp_email']);
?>

<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Acceso Concedido | GESEX</title>
  <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
  <header class="brand-header">
    <img src="./assets/img/gesex-logo.jpg" alt="GESEX" class="brand-logo">
    <div class="brand-copy">
      <h1>GESEX</h1>
      <p>Smart Fruit for Good</p>
    </div>
  </header>
  <div class="container" id="container">
    <div class="form-container sign-in">
      <h1>Acceso concedido</h1>
      <p>Ha sido autenticado exitosamente. Ahora puede navegar.</p>
      <p>Se abrirá la página de Fortigate en 5 segundos.</p>
      <script>
        setTimeout(function(){
          window.location.href = <?php echo json_encode(getenv('GESEX_SUCCESS_URL') ?: 'http://192.168.201.1/?res=success'); ?>;
        }, 5000);
      </script>
    </div>
  </div>
</body>
</html>
