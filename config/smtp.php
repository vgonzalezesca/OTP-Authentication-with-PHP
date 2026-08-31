<?php
// Microsoft Exchange Online: autenticación con el buzón y envío desde su alias.
define('SMTP_HOST', getenv('GESEX_SMTP_HOST') ?: 'smtp.office365.com');
define('SMTP_PORT', (int) (getenv('GESEX_SMTP_PORT') ?: 587));
define('SMTP_SECURE', getenv('GESEX_SMTP_SECURE') ?: 'tls');
define('SMTP_USERNAME', getenv('GESEX_SMTP_USERNAME') ?: 'alertas@geexsa.com');
define('SMTP_PASSWORD', getenv('GESEX_SMTP_PASSWORD') ?: '');
define('FROM_EMAIL', getenv('GESEX_SMTP_FROM') ?: 'wifivisitas@geexsa.com');
define('FROM_NAME', getenv('GESEX_SMTP_FROM_NAME') ?: 'Visitas GESEX');
?>
