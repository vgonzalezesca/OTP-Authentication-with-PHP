<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
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

    require 'vendor/autoload.php';
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your-email@gmail.com';
        $mail->Password   = 'your-app-password';
        $mail->SMTPSecure = 'tls'; 
        $mail->Port       = 587;

        $mail->setFrom('your-email@gmail.com', 'YourApp');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'کد تایید شما';
        $mail->Body    = "کد تایید شما: <b>$otp</b>";

        $mail->send();
        $msg = "✅ OTP به ایمیل شما ارسال شد و در دیتابیس ذخیره گردید.";
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
        $msg = "✅ OTP صحیح است. ورود موفق بود.";
    } else {
        $msg = "❌ OTP اشتباه است.";
    }
}
?>

<html lang="fa">
<head>
  <meta charset="UTF-8">
  <title>OTP</title>
  <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
  <div class="container" id="container">
    <div class="form-container sign-in">
      <h1>OTP</h1>
      <br>

      <?php if($msg) echo "<p style='color:blue;'>$msg</p>"; ?>

      <?php if(!$otpSent){ ?>
        <form method="POST">
          <input type="email" name="email" placeholder="Enter your Email" required>
          <button type='submit' name="send-email">Send To Email</button>
        </form>
      <?php } else { ?>
        <form method="POST">
          <input type="text" name="otp" placeholder="Enter OTP" required>
          <button type='submit' name="verify-otp">Check OTP</button>
        </form>
      <?php } ?>
    </div>
  </div>
</body>
</html>
