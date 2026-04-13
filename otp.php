<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'config/loader.php';
session_start();
if(!isset($_SESSION['terms_accepted'])){
    header('Location: portal.php');
    exit;
}
$msg = "";
$otpSent = false;

if(isset($_POST['send-email'])){
    $email = trim($_POST['email']);
    $otp = rand(100000, 999999);
    $_SESSION['otp_email'] = $email;

    try {
        $stmt = $conn->prepare("UPDATE users SET otp = :otp WHERE email = :email");
        $stmt->execute([':otp' => $otp, ':email' => $email]);

        require 'config/smtp.php';
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;

        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Código de verificación OTP';
        $mail->Body    = "Su código de verificación es: <b>$otp</b>";

        $mail->send();
        $msg = "✅ OTP enviado a su email.";
        $otpSent = true;
    } catch (Exception $e) {
        $msg = "❌ Error al enviar el correo: " . $mail->ErrorInfo;
    } catch (PDOException $e) {
        $msg = "❌ Error de base de datos: " . $e->getMessage();
    }
}

if(isset($_POST['verify-otp'])){
    $enteredOtp = trim($_POST['otp']);
    $email = $_SESSION['otp_email'];

    try {
        $stmt = $conn->prepare("SELECT otp FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $dbOtp = $stmt->fetchColumn();

        if($dbOtp && $enteredOtp === $dbOtp){
            header('Location: success.php');
            exit;
        }
        $msg = "❌ OTP incorrecto.";
    } catch (PDOException $e) {
        $msg = "❌ Error de base de datos: " . $e->getMessage();
    }
}
?>

<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>OTP - Portal de Visitas</title>
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
      <h1>Autenticación OTP</h1>
      <?php if($msg) echo "<p class='message'>$msg</p>"; ?>
      <?php if(!$otpSent){ ?>
        <form method="POST">
          <input type="email" name="email" placeholder="Ingrese su correo electrónico" required>
          <button type='submit' name="send-email">Enviar OTP</button>
        </form>
      <?php } else { ?>
        <form method="POST">
          <input type="text" name="otp" placeholder="Ingrese el código OTP" required>
          <button type='submit' name="verify-otp">Verificar OTP</button>
        </form>
      <?php } ?>
    </div>
  </div>
</body>
</html>

