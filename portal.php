<?php
session_start();
if(isset($_POST['accept_terms'])){
    $_SESSION['terms_accepted'] = true;
    header('Location: otp.php');
    exit;
}
?>

<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Portal de Visitas - Términos y Condiciones</title>
  <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h1>Términos y Condiciones de Uso</h1>
          </div>
          <div class="card-body">
            <p>Por favor, lea atentamente los siguientes términos y condiciones antes de acceder a la red WiFi de visitas.</p>
            <h5>1. Aceptación de Términos</h5>
            <p>Al aceptar estos términos, usted acuerda cumplir con las políticas de uso de la red.</p>
            <h5>2. Uso Responsable</h5>
            <p>El acceso a la red es únicamente para fines legítimos. No se permite el uso para actividades ilegales.</p>
            <h5>3. Privacidad</h5>
            <p>Su email será utilizado solo para enviar el código OTP y no será almacenado permanentemente.</p>
            <h5>4. Responsabilidad</h5>
            <p>La organización no se hace responsable por el uso indebido de la red.</p>
            <p>Si acepta estos términos, haga clic en "Aceptar" para continuar.</p>
            <form method="POST">
              <button type="submit" name="accept_terms" class="btn btn-primary">Aceptar Términos y Continuar</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>