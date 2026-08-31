<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'config/loader.php';
require_once 'config/smtp.php';
session_start();
if (!isset($_SESSION['terms_accepted'])) {
    header('Location: portal.php');
    exit;
}

$msg = '';
$otpSent = false;

if (isset($_POST['send-email'])) {
    $email = trim($_POST['email'] ?? '');
    $otp = (string) random_int(100000, 999999);
    $_SESSION['otp_email'] = $email;

    try {
        if (SMTP_PASSWORD === '') {
            throw new RuntimeException('Falta configurar GESEX_SMTP_PASSWORD en el entorno del servidor.');
        }
        $stmt = $conn->prepare('UPDATE users SET otp = :otp WHERE email = :email');
        $stmt->execute([':otp' => $otp, ':email' => $email]);

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME; // Buzón usado para autenticarse.
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;   // STARTTLS.
        $mail->Port       = SMTP_PORT;
        $mail->setFrom(FROM_EMAIL, FROM_NAME); // Alias con permiso Send As.
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Código de verificación OTP | Visitas GESEX';
        $mail->Body = 'Su código de verificación para acceder a la red Wi-Fi de visitas GESEX es: <strong>' . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') . '</strong>';
        $mail->AltBody = 'Su código de verificación para acceder a la red Wi-Fi de visitas GESEX es: ' . $otp;
        $mail->send();
        $msg = 'OTP enviado a su email.';
        $otpSent = true;
    } catch (Throwable $e) {
        $msg = 'No fue posible enviar el OTP: ' . $e->getMessage();
    }
}

if (isset($_POST['verify-otp'])) {
    $enteredOtp = trim($_POST['otp'] ?? '');
    $email = $_SESSION['otp_email'] ?? '';
    try {
        $stmt = $conn->prepare('SELECT otp FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $dbOtp = (string) $stmt->fetchColumn();
        if ($dbOtp !== '' && hash_equals($dbOtp, $enteredOtp)) {
            header('Location: success.php');
            exit;
        }
        $msg = 'OTP incorrecto.';
        $otpSent = true;
    } catch (PDOException $e) {
        $msg = 'Error de base de datos: ' . $e->getMessage();
        $otpSent = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Verificación de acceso | Visitas GESEX</title>
  <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body class="captive-page">
  <main class="message-container">
    <div class="logo"><img src="./assets/img/gesex-logo.jpg" alt="GESEX"></div>
    <h1 class="text-centered">Verificación de acceso – Visitas GESEX</h1>
    <p class="subtitle text-centered">Ingrese su correo electrónico para recibir el código OTP.</p>
    <div class="text-scrollable">Al conectarse a la red Wi-Fi de invitados de GESEX, usted asume la total responsabilidad por su uso y acepta que la empresa no controla ni se responsabiliza por el contenido de Internet, las políticas de privacidad de terceros o la seguridad de su dispositivo. Es su obligación utilizar esta conexión cumpliendo con la ley chilena, evitando el acceso o distribución de material ilegal, ofensivo, malicioso o protegido por derechos de autor. Por motivos de seguridad y gestión del servicio, GESEX supervisa y registra el tráfico de esta red, información que será tratada conforme a la Ley N° 19.628 sobre protección de la vida privada y podrá ser entregada a las autoridades si fuese requerido. Al acceder a Internet mediante este portal, usted declara comprender y aceptar íntegramente estas condiciones de monitoreo, uso y exención de responsabilidad.</div>
    <?php if ($msg !== ''): ?><p class="note" role="status"><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
    <?php if (!$otpSent): ?>
      <form method="POST">
        <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" autocomplete="email" required></div>
        <div class="form-footer"><button class="primary" type="submit" name="send-email">Enviar OTP</button></div>
      </form>
    <?php else: ?>
      <form method="POST">
        <div class="field"><label for="otp">Código OTP</label><input id="otp" name="otp" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required></div>
        <div class="form-footer"><button class="primary" type="submit" name="verify-otp">Verificar OTP</button></div>
      </form>
    <?php endif; ?>
  </main>
</body>
</html>
