<?php

/**
 * Conserva el contexto entregado por FortiGate durante un flujo de portal.
 * El valor magic identifica la sesion cautiva del cliente y nunca se toma de
 * configuracion local.
 */
function gesexCaptureFortiGateContext(): void
{
    if (!isset($_GET['magic']) || !is_string($_GET['magic'])) {
        return;
    }

    $magic = trim($_GET['magic']);
    if ($magic === '' || strlen($magic) > 2048) {
        return;
    }

    // No permitir que una URL posterior cambie el contexto durante el OTP.
    if (isset($_SESSION['fortigate_magic']) && isset($_SESSION['terms_accepted'])) {
        return;
    }

    $context = [];
    foreach ($_GET as $key => $value) {
        if (!is_string($key) || !is_string($value) || strlen($key) > 64 || strlen($value) > 2048) {
            continue;
        }
        if (preg_match('/^[A-Za-z0-9_.-]+$/', $key)) {
            $context[$key] = $value;
        }
    }

    $_SESSION['fortigate_magic'] = $magic;
    $_SESSION['fortigate_context'] = $context;
}

/**
 * Devuelve el POST que el navegador debe enviar a FortiGate despues del OTP.
 * No usa HTTP_HOST: en un portal externo ese valor es el host del portal,
 * no necesariamente la puerta de enlace del SSID.
 */
function gesexFortiGateHandoff(): array
{
    $magic = $_SESSION['fortigate_magic'] ?? '';
    if (!is_string($magic) || $magic === '') {
        return ['fortigate_flow' => false, 'valid' => false];
    }

    $host = trim((string) getenv('FORTIGATE_AUTH_HOST'));
    $port = getenv('FORTIGATE_AUTH_PORT') ?: '1000';
    $scheme = strtolower(trim((string) (getenv('FORTIGATE_AUTH_SCHEME') ?: 'https')));
    $username = trim((string) getenv('FORTIGATE_AUTH_USERNAME'));
    $password = (string) getenv('FORTIGATE_AUTH_PASSWORD');

    $isIpv4 = filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    $isIpv6 = filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    $isHostname = (bool) preg_match(
        '/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i',
        $host
    );
    $portNumber = filter_var($port, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 65535],
    ]);

    if ((!$isIpv4 && !$isIpv6 && !$isHostname)
        || $portNumber === false
        || !in_array($scheme, ['http', 'https'], true)
        || $username === ''
        || $password === '') {
        return [
            'fortigate_flow' => true,
            'valid' => false,
            'error' => 'No fue posible autorizar la conexion en el gateway.',
        ];
    }

    $endpointHost = $isIpv6 ? '[' . $host . ']' : $host;
    return [
        'fortigate_flow' => true,
        'valid' => true,
        'action' => $scheme . '://' . $endpointHost . ':' . $portNumber . '/fgtauth',
        'magic' => $magic,
        'username' => $username,
        'password' => $password,
    ];
}
