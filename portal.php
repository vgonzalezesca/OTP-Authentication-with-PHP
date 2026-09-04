<?php
session_start();
require_once 'config/fortigate.php';
gesexCaptureFortiGateContext('portal');

$fortiGateAuthFailed = isset($_GET['Auth'])
    && is_string($_GET['Auth'])
    && strcasecmp($_GET['Auth'], 'Failed') === 0;
if ($fortiGateAuthFailed) {
    error_log('FortiGate authentication result=failed stage=portal');
}

if (isset($_POST['accept_terms'])) {
    session_regenerate_id(true);
    $_SESSION['terms_accepted'] = true;
    header('Location: otp.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Aviso de acceso a Internet | Visitas GESEX</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="captive-page">
  <main class="message-container">
    <div class="logo"><img src="/assets/img/gesex-logo.jpg" alt="GESEX"></div>
    <h1 class="text-centered">Aviso de acceso a Internet – Visitas GESEX</h1>
    <p class="subtitle text-centered">Favor de leer esta política de uso de nuestra red corporativa:</p>
    <?php if ($fortiGateAuthFailed): ?><p class="note" role="alert">No fue posible autorizar la conexion en el gateway. Intente nuevamente.</p><?php endif; ?>
    <form method="POST" action="/portal.php">
      <div class="text-scrollable">Al conectarse a la red Wi-Fi de invitados de GESEX, usted asume la total responsabilidad por su uso y acepta que la empresa no controla ni se responsabiliza por el contenido de Internet, las políticas de privacidad de terceros o la seguridad de su dispositivo. Es su obligación utilizar esta conexión cumpliendo con la ley chilena, evitando el acceso o distribución de material ilegal, ofensivo, malicioso o protegido por derechos de autor. Por motivos de seguridad y gestión del servicio, GESEX supervisa y registra el tráfico de esta red, información que será tratada conforme a la Ley N° 19.628 sobre protección de la vida privada y podrá ser entregada a las autoridades si fuese requerido. Al acceder a Internet mediante este portal, usted declara comprender y aceptar íntegramente estas condiciones de monitoreo, uso y exención de responsabilidad.</div>
      <label class="consent" for="terms_accepted"><input type="checkbox" name="accept_terms" id="terms_accepted" value="1" required><span>Acepto los terminos y condiciones</span></label>
      <div class="form-footer"><button class="primary" type="submit">Siguiente</button></div>
    </form>
  </main>
</body>
</html>
