<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
require_once 'config/loader.php';
require_once 'config/smtp.php';
if (!isset($_SESSION['terms_accepted'])) {
    header('Location: portal.php');
    exit;
}

$databaseAvailable = $conn instanceof PDO;
$msg = $databaseAvailable ? '' : 'El servicio de autenticacion no esta disponible. Intente nuevamente en unos minutos.';
$otpSent = false;

if (isset($_POST['send-email']) && $databaseAvailable) {
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = 'Ingrese un correo electrónico válido.';
    } else {
        try {
            if (SMTP_PASSWORD === '') {
                throw new RuntimeException('Falta configurar GESEX_SMTP_PASSWORD en el entorno del servidor.');
            }

            /* Mantiene un unico OTP activo por correo para evitar que dos envios
             * cercanos dejen al usuario con un codigo de un correo anterior. */
            $conn->beginTransaction();
            $existing = $conn->prepare(
                'SELECT otp, UNIX_TIMESTAMP(otp_expires_at) AS expires_at '
                . 'FROM users WHERE LOWER(TRIM(email)) = :email FOR UPDATE'
            );
            $existing->execute([':email' => $email]);
            $activeOtp = $existing->fetch(PDO::FETCH_ASSOC);

            if ($activeOtp && $activeOtp['otp'] !== null && (int) $activeOtp['expires_at'] > time()) {
                $otp = (string) $activeOtp['otp'];
                $otpExpiresAt = (int) $activeOtp['expires_at'];
            } else {
                $otp = (string) random_int(100000, 999999);
                $username = substr(strstr($email, '@', true) ?: $email, 0, 100);
                $stmt = $conn->prepare(
                    'INSERT INTO users (username, email, otp, otp_expires_at) '
                    . 'VALUES (:username, :email, :otp, DATE_ADD(NOW(), INTERVAL 5 MINUTE)) '
                    . 'ON DUPLICATE KEY UPDATE otp = VALUES(otp), otp_expires_at = VALUES(otp_expires_at)'
                );
                $stmt->execute([':username' => $username, ':email' => $email, ':otp' => $otp]);
                $otpExpiresAt = time() + 300;
            }
            $conn->commit();

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->CharSet    = 'UTF-8';
            $mail->Encoding   = 'base64';
            $mail->Username   = SMTP_USERNAME; // Buzón usado para autenticarse.
            $mail->Password   = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_SECURE;   // STARTTLS.
            $mail->Port       = SMTP_PORT;
            $mail->setFrom(FROM_EMAIL, FROM_NAME); // Alias con permiso Send As.
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Codigo de verificacion OTP | Visitas GESEX';
            $mail->Body = 'Su codigo de verificacion para acceder a la red Wi-Fi de visitas GESEX es: <strong>' . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') . '</strong><br><br>Este codigo expira en 5 minutos.';
            $mail->AltBody = 'Su codigo de verificacion para acceder a la red Wi-Fi de visitas GESEX es: ' . $otp . '. Este codigo expira en 5 minutos.';
            $mail->send();
            $_SESSION['otp_email'] = $email;
            $_SESSION['otp_hash'] = password_hash($otp, PASSWORD_DEFAULT);
            $_SESSION['otp_expires_at'] = $otpExpiresAt;
            $msg = 'OTP enviado a su email.';
            $otpSent = true;
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $msg = 'No fue posible enviar el OTP: ' . $e->getMessage();
        }
    }
}

if (isset($_POST['verify-otp']) && $databaseAvailable) {
    $enteredOtp = preg_replace('/\D/', '', trim($_POST['otp'] ?? ''));
    $postedEmail = strtolower(trim($_POST['email'] ?? ''));
    $sessionEmail = strtolower(trim($_SESSION['otp_email'] ?? ''));
    $email = filter_var($postedEmail, FILTER_VALIDATE_EMAIL) ? $postedEmail : $sessionEmail;
    try {
        $stmt = $conn->prepare(
            'SELECT otp FROM users '
            . 'WHERE LOWER(TRIM(email)) = :email AND otp_expires_at >= NOW() LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $dbOtp = (string) $stmt->fetchColumn();
        $dbOtpValid = $dbOtp !== '' && hash_equals($dbOtp, $enteredOtp);
        if ($email !== '' && $dbOtpValid) {
            $clearOtp = $conn->prepare(
                'UPDATE users SET otp = NULL, otp_expires_at = NULL '
                . 'WHERE LOWER(TRIM(email)) = :email'
            );
            $clearOtp->execute([':email' => $email]);
            $_SESSION['otp_email'] = $email;
            header('Location: success.php');
            exit;
        }
        $msg = 'OTP incorrecto o expirado. Solicite un nuevo codigo.';
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
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['otp_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="field"><label for="otp">Código OTP</label><input id="otp" name="otp" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required></div>
        <div class="form-footer"><button class="primary" type="submit" name="verify-otp">Verificar OTP</button><a class="secondary-link" href="otp.php">Reenviar OTP</a></div>
      </form>
    <?php endif; ?>
  </main>
</body>
</html>
