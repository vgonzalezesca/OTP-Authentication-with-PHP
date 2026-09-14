<?php
require_once 'config/loader.php';

$reportUser = getenv('GESEX_REPORT_USERNAME') ?: '';
$reportPassword = getenv('GESEX_REPORT_PASSWORD') ?: '';
if ($reportUser === '' || $reportPassword === '') {
    http_response_code(503);
    exit('El reporte no está configurado. Defina GESEX_REPORT_USERNAME y GESEX_REPORT_PASSWORD.');
}

$providedUser = $_SERVER['PHP_AUTH_USER'] ?? '';
$providedPassword = $_SERVER['PHP_AUTH_PW'] ?? '';
if (!hash_equals($reportUser, $providedUser) || !hash_equals($reportPassword, $providedPassword)) {
    header('WWW-Authenticate: Basic realm="Reporte de accesos GESEX"');
    http_response_code(401);
    exit('Autenticación requerida.');
}

$date = trim($_GET['date'] ?? '');
$user = trim($_GET['user'] ?? '');
if ($date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = '';
}

$where = [];
$params = [];
if ($date !== '') {
    $where[] = 'accessed_at >= :start AND accessed_at < :end';
    $params[':start'] = $date . ' 00:00:00';
    $params[':end'] = date('Y-m-d H:i:s', strtotime($date . ' +1 day'));
}
if ($user !== '') {
    $where[] = 'email LIKE :user';
    $params[':user'] = '%' . $user . '%';
}
$sql = 'SELECT id, email, accessed_at FROM access_logs';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY accessed_at DESC';

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['download']) && $_GET['download'] === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="reporte-accesos-gesex.csv"');
    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF");
    fputcsv($output, ['ID', 'Correo electrónico', 'Fecha y hora de acceso']);
    foreach ($rows as $row) {
        fputcsv($output, [$row['id'], $row['email'], $row['accessed_at']]);
    }
    fclose($output);
    exit;
}

function h(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
$query = http_build_query(['date' => $date, 'user' => $user, 'download' => 'csv']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reporte de accesos | GESEX</title>
  <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body class="report-page">
  <main class="report-container">
    <h1>Reporte de accesos</h1>
    <p>Conexiones autenticadas mediante codigo de acceso.</p>
    <form class="report-filters" method="get">
      <label>Fecha <input type="date" name="date" value="<?= h($date) ?>"></label>
      <label>Usuario o correo <input type="search" name="user" value="<?= h($user) ?>" placeholder="correo@ejemplo.com"></label>
      <button type="submit">Filtrar</button>
      <a class="report-link" href="report.php">Limpiar</a>
      <a class="report-link" href="report.php?<?= h($query) ?>">Descargar CSV</a>
    </form>
    <p class="report-count"><?= count($rows) ?> acceso(s) encontrado(s).</p>
    <div class="report-table-wrap">
      <table>
        <thead><tr><th>ID</th><th>Correo electrónico</th><th>Fecha y hora de acceso</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
          <tr><td><?= h((string) $row['id']) ?></td><td><?= h($row['email']) ?></td><td><?= h($row['accessed_at']) ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="3">No hay accesos para los filtros seleccionados.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>
