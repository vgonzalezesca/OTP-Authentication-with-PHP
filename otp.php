<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
if(!isset($_SESSION['terms_accepted'])){
    header('Location: portal.php');
    exit;
}
$msg = "";
$otpSent = false;

$conn = new mysqli("localhost", "root", "", "auth_system");
if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

// ارسال OTP
if(isset($_POST['send-email'])){
    $email = $_POST['email'];
    $otp = rand(100000, 999999);

    $_SESSION['otp_email'] = $email;

    // ذخیره OTP در جدول کاربران
    $stmt = $conn->prepare("UPDATE users SET otp=? WHERE email=?");
    if(!$stmt){
        die("❌ Prepare failed: " . $conn->error . " (Check if table 'users' and columns 'otp','email' exist)");
    }
    $stmt->bind_param("ss", $otp, $email);
    $stmt->execute();
    $stmt->close();

    require 'config/smtp.php';
    $mail = new PHPMailer(true);

    try {
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
        $msg = "❌ خطا در ارسال ایمیل: {$mail->ErrorInfo}";
    }
}

// بررسی OTP
if(isset($_POST['verify-otp'])){
    $enteredOtp = $_POST['otp'];
    $email = $_SESSION['otp_email'];

    $stmt = $conn->prepare("SELECT otp FROM users WHERE email=? LIMIT 1");
    if(!$stmt){
        die("❌ Prepare failed: " . $conn->error . " (Check if table 'users' and column 'otp' exist)");
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($dbOtp);
    $stmt->fetch();
    $stmt->close();

    if($dbOtp && $enteredOtp == $dbOtp){
        $msg = "✅ OTP correcto. Acceso concedido.";
        header('Location: success.php');
        exit;
    } else {
        $msg = "❌ OTP incorrecto.";
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
  <div class="container" id="container">
    <div class="form-container sign-in">
      <h1>Autenticación OTP</h1>
      <br>

      <?php if($msg) echo "<p style='color:blue;'>$msg</p>"; ?>

      <?php if(!$otpSent){ ?>
        <form method="POST">
          <input type="email" name="email" placeholder="Ingrese su Email" required>
          <button type='submit' name="send-email">Enviar OTP</button>
        </form>
      <?php } else { ?>
        <form method="POST">
          <input type="text" name="otp" placeholder="Ingrese OTP" required>
          <button type='submit' name="verify-otp">Verificar OTP</button>
        </form>
      <?php } ?>
    </div>
  </div>
</body>
</html>
