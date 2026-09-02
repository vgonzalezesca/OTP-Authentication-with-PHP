<?php
session_start();
require_once 'config/loader.php';
$email = strtolower(trim($_SESSION['otp_email'] ?? ''));

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
    <h1 class="text-centered">Acceso concedido</h1>
    <p class="subtitle text-centered">Ha sido autenticado exitosamente. Ahora puede navegar.</p>
    <p class="text-centered">Redirigiendo a FortiGate en <strong id="countdown">5</strong> segundos…</p>
    <div class="progress-track" role="progressbar" aria-label="Redirección en curso" aria-valuemin="0" aria-valuemax="5" aria-valuenow="0">
      <div class="progress-bar" id="progress-bar"></div>
    </div>
  </main>
  <script>
    const targetUrl = <?php echo json_encode(getenv('GESEX_SUCCESS_URL') ?: 'http://192.168.201.1/?res=success'); ?>;
    const duration = 5;
    const startedAt = Date.now();
    const countdown = document.getElementById('countdown');
    const progressBar = document.getElementById('progress-bar');
    const progressTrack = progressBar.parentElement;

    function updateProgress() {
      const elapsed = Math.min((Date.now() - startedAt) / 1000, duration);
      progressBar.style.width = `${(elapsed / duration) * 100}%`;
      progressTrack.setAttribute('aria-valuenow', String(Math.floor(elapsed)));
      countdown.textContent = String(Math.max(0, Math.ceil(duration - elapsed)));
      if (elapsed < duration) {
        requestAnimationFrame(updateProgress);
      } else {
        window.location.assign(targetUrl);
      }
    }
    requestAnimationFrame(updateProgress);
  </script>
</body>
</html>
