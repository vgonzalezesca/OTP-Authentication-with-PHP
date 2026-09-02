<?php
session_start();
require_once 'config/loader.php';
require_once 'config/fortigate.php';

if (empty($_SESSION['otp_verified'])) {
    header('Location: portal.php');
    exit;
}

$email = strtolower(trim($_SESSION['otp_email'] ?? ''));
$fortiGateHandoff = gesexFortiGateHandoff();

if ($email !== '' && $conn instanceof PDO) {
    try {
        $stmt = $conn->prepare(
            'INSERT INTO access_logs (user_id, email) '
            . 'SELECT id, email FROM users WHERE LOWER(TRIM(email)) = :email LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
    } catch (PDOException $e) {
        error_log('No fue posible registrar el acceso OTP: ' . $e->getMessage());
    }
}

unset($_SESSION['terms_accepted']);
unset($_SESSION['otp_email']);
unset($_SESSION['otp_hash']);
unset($_SESSION['otp_expires_at']);
unset($_SESSION['otp_verified']);
unset($_SESSION['fortigate_magic']);
unset($_SESSION['fortigate_context']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Acceso Concedido | GESEX</title>
  <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body class="captive-page success-page">
  <main class="message-container success-container">
    <div class="success-icon" aria-hidden="true">✓</div>
    <?php if ($fortiGateHandoff['fortigate_flow'] && $fortiGateHandoff['valid']): ?>
      <h1 class="text-centered">Autenticando su conexion</h1>
      <p class="subtitle text-centered">El codigo OTP fue validado. Espere mientras se autoriza el acceso en el gateway.</p>
      <p class="text-centered">Conectando con la puerta de enlace Wi-Fi…</p>
      <form id="fortigate-auth-form" method="post" action="<?php echo htmlspecialchars($fortiGateHandoff['action'], ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="magic" value="<?php echo htmlspecialchars($fortiGateHandoff['magic'], ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="username" value="<?php echo htmlspecialchars($fortiGateHandoff['username'], ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="password" value="<?php echo htmlspecialchars($fortiGateHandoff['password'], ENT_QUOTES, 'UTF-8'); ?>">
        <noscript><button class="primary" type="submit">Continuar a Internet</button></noscript>
      </form>
      <script>
        document.getElementById('fortigate-auth-form').submit();
      </script>
    <?php elseif ($fortiGateHandoff['fortigate_flow']): ?>
      <h1 class="text-centered">No fue posible autorizar la conexion</h1>
      <p class="note" role="alert"><?php echo htmlspecialchars($fortiGateHandoff['error'], ENT_QUOTES, 'UTF-8'); ?></p>
    <?php else: ?>
      <h1 class="text-centered">OTP validado</h1>
      <p class="subtitle text-centered">Modo de prueba local: no se recibio una sesion FortiGate para autorizar.</p>
    <?php endif; ?>
  </main>
</body>
</html>
