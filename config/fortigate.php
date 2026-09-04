<?php

/**
 * Conserva el contexto entregado por FortiGate durante un flujo de portal.
 * El valor magic identifica la sesion cautiva del cliente y nunca se toma de
 * configuracion local.
 */
function gesexCaptureFortiGateContext(string $stage = 'unknown'): void
{
    $requestContext = [];
    foreach (['GET', 'POST'] as $source) {
        foreach ($GLOBALS['_' . $source] ?? [] as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $requestContext[$key] = $value;
            }
        }
    }

    if (!isset($requestContext['magic']) || !is_string($requestContext['magic'])) {
        // Las peticiones internas del portal (aceptar términos, enviar OTP y
        // validar OTP) normalmente no repiten los parámetros de FortiGate.
        // Nunca borrar aquí un contexto ya capturado: magic pertenece a esta
        // sesión cautiva y debe llegar intacto hasta success.php.
        error_log('FortiGate context received=no magic received=no stage=' . $stage);
        return;
    }

    $magic = trim($requestContext['magic']);
    if ($magic === '' || strlen($magic) > 2048) {
        error_log('FortiGate context received=yes magic received=no stage=' . $stage);
        return;
    }

    // No permitir que una URL posterior cambie el contexto durante el OTP.
    if (isset($_SESSION['fortigate_magic']) && isset($_SESSION['terms_accepted'])) {
        return;
    }

    $context = [];
    foreach ($requestContext as $key => $value) {
        if (!is_string($key) || !is_string($value) || strlen($key) > 64 || strlen($value) > 2048) {
            continue;
        }
        if (preg_match('/^[A-Za-z0-9_.-]+$/', $key)) {
            $context[$key] = $value;
        }
    }

    $_SESSION['fortigate_magic'] = $magic;
    $_SESSION['fortigate_post'] = isset($context['post']) ? trim($context['post']) : '';
    $_SESSION['fortigate_context'] = $context;
    error_log('FortiGate context received=yes magic received=yes post received='
        . ($_SESSION['fortigate_post'] !== '' ? 'yes' : 'no') . ' stage=' . $stage);
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
        error_log('FortiGate endpoint received=no validated=no stage=success');
        return [
            'fortigate_flow' => false,
            'valid' => false,
            'reason' => 'no_magic',
            'error' => 'FortiGate no entrego el contexto de sesion al portal.',
        ];
    }

    $username = trim((string) getenv('FORTIGATE_AUTH_USERNAME'));
    $password = (string) getenv('FORTIGATE_AUTH_PASSWORD');

    $expectedPostHost = trim((string) getenv('FORTIGATE_AUTH_HOST'));
    $expectedPort = filter_var(getenv('FORTIGATE_AUTH_PORT'), FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 65535],
    ]);
    $expectedScheme = strtolower(trim((string) getenv('FORTIGATE_AUTH_SCHEME')));
    $fortigatePost = trim((string) ($_SESSION['fortigate_post'] ?? ''));
    $endpointReceived = $fortigatePost !== '' ? 'yes' : 'no';

    if ($username === '' || $password === '' || $expectedPostHost === ''
        || $expectedPort === false || !in_array($expectedScheme, ['http', 'https'], true)) {
        error_log('FortiGate endpoint received=' . $endpointReceived . ' validated=no stage=success');
        return [
            'fortigate_flow' => true,
            'valid' => false,
            'reason' => $username === '' || $password === '' ? 'missing_credentials' : 'invalid_config',
            'error' => $username === '' || $password === ''
                ? 'El portal no tiene configuradas las credenciales de autenticacion del gateway.'
                : 'La configuracion del endpoint de FortiGate no es valida.',
        ];
    }

    if ($fortigatePost !== '') {
        $parsedPost = parse_url($fortigatePost);
        $postHost = is_array($parsedPost) ? ($parsedPost['host'] ?? '') : '';
        $postPort = is_array($parsedPost) ? ($parsedPost['port'] ?? null) : null;
        $postPath = is_array($parsedPost) ? ($parsedPost['path'] ?? '') : '';
        $postScheme = is_array($parsedPost) ? strtolower((string) ($parsedPost['scheme'] ?? '')) : '';
        $postIsValid = is_array($parsedPost)
            && in_array($postScheme, ['http', 'https'], true)
            && strcasecmp((string) $postHost, $expectedPostHost) === 0
            && $postPort === $expectedPort
            && $postPath === '/fgtauth'
            && !isset($parsedPost['user'], $parsedPost['pass'], $parsedPost['query'], $parsedPost['fragment']);
        if (!$postIsValid) {
            error_log('FortiGate endpoint received=yes validated=no stage=success');
            return [
                'fortigate_flow' => true,
                'valid' => false,
                'reason' => 'invalid_post',
                'error' => 'No fue posible autorizar la conexion en el gateway.',
            ];
        }
        // Se conserva exactamente el esquema y endpoint entregados por FortiGate.
        $action = $fortigatePost;
        error_log('FortiGate endpoint received=yes validated=yes stage=success');
    } else {
        // Fallback solo cuando existe magic pero FortiGate no envió post.
        $urlHost = filter_var($expectedPostHost, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
            ? '[' . $expectedPostHost . ']'
            : $expectedPostHost;
        $action = $expectedScheme . '://' . $urlHost . ':' . $expectedPort . '/fgtauth';
        error_log('FortiGate endpoint received=no validated=yes stage=success');
    }

    return [
        'fortigate_flow' => true,
        'valid' => true,
        'action' => $action,
        'magic' => $magic,
        'username' => $username,
        'password' => $password,
    ];
}
