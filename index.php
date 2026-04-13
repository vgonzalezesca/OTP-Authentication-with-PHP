<?php 
require_once('config/loader.php');
?>

<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <title>Iniciar Sesión | GESEX</title>
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
    <div class="form-container sign-up">
      <form method="POST" action="action/sign-up.php">
        <h1>Crear cuenta</h1>
        <div class="social-icons">
          <a href="#" class="icons"><i class="fa-brands fa-google-plus-g"></i></a>
          <a href="#" class="icons"><i class="fa-brands fa-facebook-f"></i></a>
          <a href="#" class="icons"><i class="fa-brands fa-github"></i></a>
          <a href="#" class="icons"><i class="fa-brands fa-linkedin-in"></i></a>
        </div>
        <span>O usa tu correo electrónico para registrarte</span>
        <input type="text" name="username" placeholder="Nombre de usuario">
        <input type="email" name="email" placeholder="Correo electrónico">
        <input type="text" name="mobile" placeholder="Teléfono">
        <input type="password" name="password" placeholder="Contraseña">
        <button type="submit" name="signup">Registrarse</button>
      </form>
    </div>
    <div class="form-container sign-in">
      <form method="POST" action="action/sign-in.php">
        <h1>Iniciar sesión</h1>
        <span>O usa tu correo/usuario/teléfono</span>
        <input type="text" name="key" placeholder="Teléfono / Usuario / Correo">
        <input type="password" name="password" placeholder="Contraseña">
        <a href="#">¿Olvidó su contraseña?</a>
        <div style="display: inline;">
            <button type="submit" name="signin">Ingresar</button>
            <a class="secondary-link" href="otp.php">Enviar OTP</a>
        </div>
        <?php if(isset($_GET['notuser'])) {?>
          <p style="width:100%" class="alert alert-danger">Usuario no encontrado.</p>
        <?php } else if(isset($_GET['loginned'])) { ?>
          <p style="width:100%" class="alert alert-success">¡Ingreso exitoso!</p>
        <?php } ?>
      </form>
    </div>
    <div class="toggle-container">
      <div class="toggle">
        <div class="toggle-panel toggle-left">
          <h1>¡Bienvenido de nuevo!</h1>
          <p>Ingresa tus datos personales para usar todas las funciones.</p>
          <button class="hidden" id="login">Iniciar sesión</button>
        </div>
        <div class="toggle-panel toggle-right">
          <h1>¡Hola, amigo!</h1>
          <p>Regístrate con tus datos personales para acceder.</p>
          <button class="hidden" id="register">Registrarse</button>
        </div>
      </div>
    </div>
  </div>
</body>
<script src="./assets/script/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</html>
