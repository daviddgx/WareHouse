<?php
session_start();

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/schema_finanzas.php';
require_once __DIR__ . '/auth_finanzas.php';

/**
 * Obtiene todas las filas de un mysqli_stmt sin depender de mysqlnd/get_result().
 *
 * @return array<int, array<string, mixed>>
 */
function finanzas_stmt_fetch_all(mysqli_stmt $stmt): array
{
  $rows = [];
  $metadata = $stmt->result_metadata();
  if (!$metadata) {
    return $rows;
  }

  $fields = $metadata->fetch_fields();
  $values = [];
  $refs = [];

  foreach ($fields as $field) {
    $values[$field->name] = null;
    $refs[] = &$values[$field->name];
  }

  $stmt->bind_result(...$refs);

  while ($stmt->fetch()) {
    $row = [];
    foreach ($values as $key => $value) {
      $row[$key] = $value;
    }
    $rows[] = $row;
  }

  $metadata->free();

  return $rows;
}

finanzas_handle_auth_post();
finanzas_require_auth('Proyecto Finanzas - Detalles');

$conn = db();
ensure_finanzas_schema($conn);

$mesSeleccionado = (string)($_GET['mes'] ?? date('Y-m'));
if (!preg_match('/^\d{4}-\d{2}$/', $mesSeleccionado)) {
  $mesSeleccionado = date('Y-m');
}

$fechaMes = DateTime::createFromFormat('Y-m-d', $mesSeleccionado . '-01') ?: new DateTime('first day of this month');
$mesesNombres = [1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'];
$nombreMesSeleccionado = ($mesesNombres[(int)$fechaMes->format('n')] ?? $fechaMes->format('F')) . ' ' . $fechaMes->format('Y');

$resumen = [
  'diario' => ['ingreso' => 0.0, 'gasto' => 0.0],
  'semanal' => ['ingreso' => 0.0, 'gasto' => 0.0],
  'mensual' => ['ingreso' => 0.0, 'gasto' => 0.0],
];

$queries = [
  'diario' => "SELECT tipo, COALESCE(SUM(monto),0) AS total FROM finanzas_movimientos WHERE fecha_movimiento = CURDATE() GROUP BY tipo",
  'semanal' => "SELECT tipo, COALESCE(SUM(monto),0) AS total FROM finanzas_movimientos WHERE YEARWEEK(fecha_movimiento, 1) = YEARWEEK(CURDATE(), 1) GROUP BY tipo",
  'mensual' => "SELECT tipo, COALESCE(SUM(monto),0) AS total FROM finanzas_movimientos WHERE YEAR(fecha_movimiento) = YEAR(CURDATE()) AND MONTH(fecha_movimiento) = MONTH(CURDATE()) GROUP BY tipo",
];
foreach ($queries as $periodo => $query) {
  if ($result = $conn->query($query)) {
    while ($row = $result->fetch_assoc()) {
      $resumen[$periodo][strtolower((string)$row['tipo'])] = (float)$row['total'];
    }
    $result->free();
  }
}

$mesesDisponibles = [];
$rMeses = $conn->query(
  "SELECT DATE_FORMAT(fecha_movimiento, '%Y-%m') AS valor_mes,
          DATE_FORMAT(MIN(fecha_movimiento), '%m/%Y') AS etiqueta
   FROM finanzas_movimientos
   GROUP BY DATE_FORMAT(fecha_movimiento, '%Y-%m')
   ORDER BY valor_mes DESC"
);
while ($row = $rMeses->fetch_assoc()) {
  $mesesDisponibles[] = $row;
}
$rMeses->free();
if (!$mesesDisponibles) {
  $mesesDisponibles[] = ['valor_mes' => $mesSeleccionado, 'etiqueta' => $fechaMes->format('m/Y')];
}

$gastosPorCategoria = [];
$stmt = $conn->prepare(
  "SELECT c.nombre AS categoria, COALESCE(SUM(m.monto), 0) AS total
   FROM finanzas_movimientos m
   INNER JOIN finanzas_categorias c ON c.id = m.categoria_id
   WHERE m.tipo = 'Gasto' AND DATE_FORMAT(m.fecha_movimiento, '%Y-%m') = ?
   GROUP BY c.nombre
   ORDER BY total DESC"
);
$stmt->bind_param('s', $mesSeleccionado);
$stmt->execute();
foreach (finanzas_stmt_fetch_all($stmt) as $row) {
  $gastosPorCategoria[] = [
    'categoria' => $row['categoria'],
    'total' => (float)$row['total'],
  ];
}
$stmt->close();

$diasMayorGasto = [];
$stmt = $conn->prepare(
  "SELECT DATE_FORMAT(fecha_movimiento, '%d/%m/%Y') AS dia, COALESCE(SUM(monto), 0) AS total
   FROM finanzas_movimientos
   WHERE tipo = 'Gasto' AND DATE_FORMAT(fecha_movimiento, '%Y-%m') = ?
   GROUP BY fecha_movimiento
   ORDER BY total DESC
   LIMIT 7"
);
$stmt->bind_param('s', $mesSeleccionado);
$stmt->execute();
foreach (finanzas_stmt_fetch_all($stmt) as $row) {
  $diasMayorGasto[] = [
    'dia' => $row['dia'],
    'total' => (float)$row['total'],
  ];
}
$stmt->close();

$serieMensual = [];
$rSerie = $conn->query(
  "SELECT DATE_FORMAT(fecha_movimiento, '%Y-%m') AS valor_mes,
          DATE_FORMAT(MIN(fecha_movimiento), '%m/%Y') AS etiqueta,
          SUM(CASE WHEN tipo = 'Ingreso' THEN monto ELSE 0 END) AS ingresos,
          SUM(CASE WHEN tipo = 'Gasto' THEN monto ELSE 0 END) AS gastos
   FROM finanzas_movimientos
   GROUP BY DATE_FORMAT(fecha_movimiento, '%Y-%m')
   ORDER BY valor_mes ASC"
);
while ($row = $rSerie->fetch_assoc()) {
  $serieMensual[] = [
    'mes' => $row['etiqueta'],
    'ingresos' => (float)$row['ingresos'],
    'gastos' => (float)$row['gastos'],
  ];
}
$rSerie->free();

$ingresosPorCategoria = [];
$stmt = $conn->prepare(
  "SELECT c.nombre AS categoria, COALESCE(SUM(m.monto), 0) AS total
   FROM finanzas_movimientos m
   INNER JOIN finanzas_categorias c ON c.id = m.categoria_id
   WHERE m.tipo = 'Ingreso' AND DATE_FORMAT(m.fecha_movimiento, '%Y-%m') = ?
   GROUP BY c.nombre
   ORDER BY total DESC"
);
$stmt->bind_param('s', $mesSeleccionado);
$stmt->execute();
foreach (finanzas_stmt_fetch_all($stmt) as $row) {
  $ingresosPorCategoria[] = [
    'categoria' => $row['categoria'],
    'total' => (float)$row['total'],
  ];
}
$stmt->close();

$balanceSemanal = [];
$stmt = $conn->prepare(
  "SELECT CONCAT('Sem ', LPAD(WEEK(fecha_movimiento, 1) - WEEK(DATE_SUB(fecha_movimiento, INTERVAL DAYOFMONTH(fecha_movimiento) - 1 DAY), 1) + 1, 2, '0')) AS semana,
          SUM(CASE WHEN tipo = 'Ingreso' THEN monto ELSE 0 END) AS ingresos,
          SUM(CASE WHEN tipo = 'Gasto' THEN monto ELSE 0 END) AS gastos
   FROM finanzas_movimientos
   WHERE DATE_FORMAT(fecha_movimiento, '%Y-%m') = ?
   GROUP BY YEAR(fecha_movimiento), MONTH(fecha_movimiento), WEEK(fecha_movimiento, 1)
   ORDER BY MIN(fecha_movimiento) ASC"
);
$stmt->bind_param('s', $mesSeleccionado);
$stmt->execute();
foreach (finanzas_stmt_fetch_all($stmt) as $row) {
  $ingresos = (float)$row['ingresos'];
  $gastos = (float)$row['gastos'];
  $balanceSemanal[] = [
    'semana' => $row['semana'],
    'ingresos' => $ingresos,
    'gastos' => $gastos,
    'balance' => $ingresos - $gastos,
  ];
}
$stmt->close();

$balanceAcumulado = [];
$gastosDiarios = [];
$flujoDiario = [];
$gastoAcumulado = [];
$stmt = $conn->prepare(
  "SELECT DATE_FORMAT(fecha_movimiento, '%d/%m') AS dia,
          SUM(CASE WHEN tipo = 'Ingreso' THEN monto ELSE -monto END) AS neto,
          SUM(CASE WHEN tipo = 'Ingreso' THEN monto ELSE 0 END) AS ingresos,
          SUM(CASE WHEN tipo = 'Gasto' THEN monto ELSE 0 END) AS gastos
   FROM finanzas_movimientos
   WHERE DATE_FORMAT(fecha_movimiento, '%Y-%m') = ?
   GROUP BY fecha_movimiento
   ORDER BY fecha_movimiento ASC"
);
$stmt->bind_param('s', $mesSeleccionado);
$stmt->execute();
$acumulado = 0.0;
$gastoAcumuladoTotal = 0.0;
foreach (finanzas_stmt_fetch_all($stmt) as $row) {
  $ingresosDia = (float)$row['ingresos'];
  $gastosDia = (float)$row['gastos'];
  $acumulado += (float)$row['neto'];
  $gastoAcumuladoTotal += $gastosDia;
  $balanceAcumulado[] = [
    'dia' => $row['dia'],
    'balance' => $acumulado,
  ];
  $gastosDiarios[] = [
    'dia' => $row['dia'],
    'total' => $gastosDia,
  ];
  $flujoDiario[] = [
    'dia' => $row['dia'],
    'ingresos' => $ingresosDia,
    'gastos' => $gastosDia,
  ];
  $gastoAcumulado[] = [
    'dia' => $row['dia'],
    'total' => $gastoAcumuladoTotal,
  ];
}
$stmt->close();

$resumenMensualSeleccionado = [
  ['tipo' => 'Ingresos', 'total' => 0.0],
  ['tipo' => 'Gastos', 'total' => 0.0],
  ['tipo' => 'Ahorro', 'total' => 0.0],
];
$stmt = $conn->prepare(
  "SELECT
      SUM(CASE WHEN tipo = 'Ingreso' THEN monto ELSE 0 END) AS ingresos,
      SUM(CASE WHEN tipo = 'Gasto' THEN monto ELSE 0 END) AS gastos
   FROM finanzas_movimientos
   WHERE DATE_FORMAT(fecha_movimiento, '%Y-%m') = ?"
);
$stmt->bind_param('s', $mesSeleccionado);
$stmt->execute();
$totalesMes = finanzas_stmt_fetch_all($stmt);
$stmt->close();
if ($totalesMes) {
  $ingresosMes = (float)($totalesMes[0]['ingresos'] ?? 0);
  $gastosMes = (float)($totalesMes[0]['gastos'] ?? 0);
  $ahorroMes = max($ingresosMes - $gastosMes, 0);
  $resumenMensualSeleccionado = [
    ['tipo' => 'Ingresos', 'total' => $ingresosMes],
    ['tipo' => 'Gastos', 'total' => $gastosMes],
    ['tipo' => 'Ahorro', 'total' => $ahorroMes],
  ];
}

$movimientos = [];
$rMov = $conn->query("SELECT m.id, m.tipo, c.nombre AS categoria, m.descripcion, m.monto, m.fecha_movimiento FROM finanzas_movimientos m INNER JOIN finanzas_categorias c ON c.id = m.categoria_id ORDER BY m.fecha_movimiento DESC, m.id DESC");
while ($row = $rMov->fetch_assoc()) {
  $movimientos[] = $row;
}
$rMov->free();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Proyecto Finanzas - Detalles</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background: linear-gradient(180deg, #eef4ff 0%, #f8fbff 48%, #eef2ff 100%); transition: background .25s ease, color .25s ease; }
    .card-kpi, .panel-animated { border: 0; border-radius: 1rem; }
    .kpi-amount { font-size: 1.2rem; font-weight: 700; }
    .top-actions { position: sticky; top: 0; z-index: 10; backdrop-filter: blur(10px); }
    .hero-banner { position: relative; overflow: hidden; background: linear-gradient(135deg, #0f172a, #1d4ed8 58%, #22c55e); color: #fff; border-radius: 1.5rem; box-shadow: 0 20px 45px rgba(37, 99, 235, .18); }
    .hero-banner.panel-animated { background: linear-gradient(135deg, #0f172a, #1d4ed8 58%, #22c55e); color: #fff; }
    .hero-banner::before { content: ''; position: absolute; inset: 0; background: linear-gradient(120deg, rgba(15, 23, 42, .94), rgba(29, 78, 216, .78) 58%, rgba(34, 197, 94, .6)); pointer-events: none; }
    .hero-banner::after { content: ''; position: absolute; inset: 0; background: radial-gradient(circle at top right, rgba(255,255,255,.25), transparent 35%); pointer-events: none; }
    .hero-banner > * { position: relative; z-index: 1; }
    .stat-chip { display: inline-flex; align-items: center; gap: .45rem; padding: .55rem .9rem; border-radius: 999px; background: rgba(15,23,42,.34); border: 1px solid rgba(255,255,255,.24); color: #eff6ff; font-weight: 600; box-shadow: 0 10px 24px rgba(15, 23, 42, .24); }
    .stat-chip i { color: #fef08a; }
    .floating-icon { width: 3rem; height: 3rem; border-radius: 1rem; display: grid; place-items: center; background: rgba(255,255,255,.2); color: #f8fafc; box-shadow: 0 12px 30px rgba(15, 23, 42, .18); animation: floatY 4s ease-in-out infinite; }
    .panel-animated { background: rgba(255, 255, 255, .96); box-shadow: 0 16px 40px rgba(15, 23, 42, .08); transition: transform .3s ease, box-shadow .3s ease; animation: revealUp .8s ease both; }
    .card-kpi { background: linear-gradient(135deg, #ffffff, #eef4ff); box-shadow: 0 14px 35px rgba(37, 99, 235, .08); }
    .card-kpi .text-uppercase.text-muted { color: #475569 !important; letter-spacing: .08em; font-weight: 700; }
    .card-kpi .d-flex span:first-child { color: #0f172a; }
    .panel-animated:hover { transform: translateY(-4px); box-shadow: 0 20px 48px rgba(15, 23, 42, .13); }
    .chart-panel { position: relative; overflow: hidden; }
    .chart-panel::before { content: ''; position: absolute; inset: 0 0 auto; height: 4px; background: linear-gradient(90deg, #2563eb, #22c55e, #f59e0b); }
    [data-delay='1'] { animation-delay: .1s; }
    [data-delay='2'] { animation-delay: .2s; }
    [data-delay='3'] { animation-delay: .3s; }
    [data-delay='4'] { animation-delay: .4s; }
    [data-delay='5'] { animation-delay: .5s; }
    [data-delay='6'] { animation-delay: .6s; }
    [data-delay='7'] { animation-delay: .7s; }
    [data-delay='8'] { animation-delay: .8s; }
    @keyframes revealUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes floatY { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
    body.dark-mode { background: linear-gradient(180deg, #020617, #0f172a 55%, #111827 100%); color: #e2e8f0; }
    body.dark-mode .card,
    body.dark-mode .border,
    body.dark-mode .table,
    body.dark-mode .top-actions,
    body.dark-mode .panel-animated { background-color: #1e293b !important; color: #e2e8f0 !important; }
    body.dark-mode .hero-banner,
    body.dark-mode .hero-banner.panel-animated { box-shadow: 0 20px 55px rgba(14, 165, 233, .18); background: linear-gradient(135deg, #020617, #0f3ea8 58%, #15803d) !important; }
    body.dark-mode .text-muted { color: #94a3b8 !important; }
    body.dark-mode .table-striped > tbody > tr:nth-of-type(odd) > * { color: #e2e8f0; }
    @media (max-width: 767px) { .kpi-amount { font-size: 1rem; } .hero-banner { border-radius: 1.25rem; } }
  </style>
</head>
<body>
<div class="container py-3 py-md-4">
  <div class="top-actions d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2 p-2 rounded bg-body">
    <div class="d-flex gap-2 flex-wrap">
      <a href="registros.php" class="btn btn-success btn-sm"><i class="bi bi-pencil-square"></i> Ir a registros</a>
      <a href="../index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Menú</a>
    </div>
    <div class="d-flex gap-2">
      <button id="toggleDark" class="btn btn-outline-dark btn-sm"><i class="bi bi-moon-stars"></i> Modo oscuro</button>
      <form method="post">
        <input type="hidden" name="action" value="cerrar_sesion_finanzas">
        <input type="hidden" name="redirect_to" value="index.php">
        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right"></i></button>
      </form>
    </div>
  </div>

  <section class="hero-banner p-4 p-lg-5 mb-4 panel-animated" data-delay="1">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <span class="stat-chip mb-3"><i class="bi bi-graph-up-arrow"></i> Panel inteligente para el mes seleccionado</span>
        <h1 class="display-6 fw-bold mb-2">Detalles financieros con una lectura visual más clara</h1>
        <p class="mb-0 text-white-50">Ahora el tablero muestra tendencias, ahorro acumulado y comportamiento semanal para ayudarte a detectar oportunidades de mejora.</p>
      </div>
      <div class="col-lg-4">
        <div class="d-flex flex-column gap-3 align-items-lg-end">
          <div class="floating-icon"><i class="bi bi-pie-chart-fill fs-4"></i></div>
          <div class="stat-chip"><i class="bi bi-calendar-month"></i> <?= htmlspecialchars(ucfirst($nombreMesSeleccionado)) ?></div>
          <div class="stat-chip"><i class="bi bi-lightning-charge"></i> 10 gráficas dinámicas</div>
        </div>
      </div>
    </div>
  </section>

  <div class="card shadow-sm mb-4 panel-animated" data-delay="2">
    <div class="card-body">
      <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
        <h2 class="h6 mb-0">Análisis mensual: <?= htmlspecialchars(ucfirst($nombreMesSeleccionado)) ?></h2>
        <form method="get" class="d-flex gap-2">
          <select name="mes" class="form-select form-select-sm">
            <?php foreach ($mesesDisponibles as $mes): ?>
              <option value="<?= htmlspecialchars($mes['valor_mes']) ?>" <?= $mesSeleccionado === $mes['valor_mes'] ? 'selected' : '' ?>><?= htmlspecialchars($mes['etiqueta']) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-primary btn-sm" type="submit">Ver</button>
        </form>
      </div>
      <div class="row g-3">
        <div class="col-12 col-md-6 col-xl-4"><div class="border rounded p-3 h-100 panel-animated chart-panel" data-delay="3"><h3 class="h6">Gastos por categoría</h3><p class="text-muted small">Visualiza rápidamente qué rubros absorben más presupuesto.</p><canvas id="chartGastosCategoria" height="220"></canvas></div></div>
        <div class="col-12 col-md-6 col-xl-4"><div class="border rounded p-3 h-100 panel-animated chart-panel" data-delay="4"><h3 class="h6">Días con más gastos</h3><p class="text-muted small">Identifica picos de gasto y fechas con mayor presión financiera.</p><canvas id="chartDiasGasto" height="220"></canvas></div></div>
        <div class="col-12 col-xl-4"><div class="border rounded p-3 h-100 panel-animated chart-panel" data-delay="5"><h3 class="h6">Evolución mensual</h3><p class="text-muted small">Compara ingresos y gastos a lo largo del historial registrado.</p><canvas id="chartSerieMensual" height="220"></canvas></div></div>
        <div class="col-12 col-md-6 col-xl-4"><div class="border rounded p-3 h-100 panel-animated chart-panel" data-delay="6"><h3 class="h6">Ingresos por categoría</h3><p class="text-muted small">Detecta las fuentes de ingreso más fuertes del mes.</p><canvas id="chartIngresosCategoria" height="220"></canvas></div></div>
        <div class="col-12 col-md-6 col-xl-4"><div class="border rounded p-3 h-100 panel-animated chart-panel" data-delay="7"><h3 class="h6">Balance semanal</h3><p class="text-muted small">Revisa qué semanas cerraron con superávit o déficit.</p><canvas id="chartBalanceSemanal" height="220"></canvas></div></div>
        <div class="col-12 col-md-6 col-xl-4"><div class="border rounded p-3 h-100 panel-animated chart-panel" data-delay="8"><h3 class="h6">Balance acumulado del mes</h3><p class="text-muted small">Sigue la trayectoria del ahorro neto día tras día.</p><canvas id="chartBalanceAcumulado" height="220"></canvas></div></div>
        <div class="col-12 col-md-6 col-xl-4"><div class="border rounded p-3 h-100 panel-animated chart-panel" data-delay="8"><h3 class="h6">Gastos diarios del mes</h3><p class="text-muted small">Observa el gasto registrado cada día para ubicar cambios puntuales.</p><canvas id="chartGastosDiarios" height="220"></canvas></div></div>
        <div class="col-12 col-md-6 col-xl-4"><div class="border rounded p-3 h-100 panel-animated chart-panel" data-delay="8"><h3 class="h6">Flujo diario</h3><p class="text-muted small">Compara ingresos y gastos por día para detectar desbalances inmediatos.</p><canvas id="chartFlujoDiario" height="220"></canvas></div></div>
        <div class="col-12 col-md-6 col-xl-4"><div class="border rounded p-3 h-100 panel-animated chart-panel" data-delay="8"><h3 class="h6">Gasto acumulado del mes</h3><p class="text-muted small">Revisa cuánto presupuesto se ha consumido conforme avanzan los días.</p><canvas id="chartGastoAcumulado" height="220"></canvas></div></div>
      </div>
      <div class="row g-3 mt-1">
        <div class="col-12"><div class="border rounded p-3 panel-animated chart-panel" data-delay="8"><h3 class="h6">Resumen financiero del mes</h3><p class="text-muted small">Distribución comparativa entre ingresos, gastos y ahorro estimado.</p><canvas id="chartResumenMensual" height="120"></canvas></div></div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <?php foreach ($resumen as $periodo => $data): $balance = $data['ingreso'] - $data['gasto']; ?>
      <div class="col-12 col-md-4">
        <div class="card shadow-sm card-kpi h-100"><div class="card-body">
          <h2 class="h6 text-uppercase text-muted"><?= htmlspecialchars($periodo) ?></h2>
          <div class="d-flex justify-content-between"><span>Ingresos</span><span class="kpi-amount text-success">Q <?= number_format($data['ingreso'], 2) ?></span></div>
          <div class="d-flex justify-content-between"><span>Gastos</span><span class="kpi-amount text-danger">Q <?= number_format($data['gasto'], 2) ?></span></div>
          <hr><div class="d-flex justify-content-between"><span>Balance</span><span class="badge <?= $balance >= 0 ? 'text-bg-success' : 'text-bg-danger' ?>">Q <?= number_format($balance, 2) ?></span></div>
        </div></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="card shadow-sm panel-animated" data-delay="8">
    <div class="card-header bg-white"><strong>Historial de movimientos</strong></div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tablaMovimientos" class="table table-striped table-hover align-middle" style="width:100%">
          <thead><tr><th>ID</th><th>Fecha</th><th>Tipo</th><th>Categoría</th><th>Descripción</th><th>Monto (Q)</th></tr></thead>
          <tbody>
          <?php foreach ($movimientos as $mov): ?>
            <tr>
              <td><?= (int)$mov['id'] ?></td>
              <td><?= htmlspecialchars((string)$mov['fecha_movimiento']) ?></td>
              <td><span class="badge <?= $mov['tipo'] === 'Ingreso' ? 'text-bg-success' : 'text-bg-danger' ?>"><?= htmlspecialchars((string)$mov['tipo']) ?></span></td>
              <td><?= htmlspecialchars((string)$mov['categoria']) ?></td>
              <td><?= htmlspecialchars((string)$mov['descripcion']) ?></td>
              <td class="fw-semibold">Q <?= number_format((float)$mov['monto'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  $('#tablaMovimientos').DataTable({
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json' },
    order: [[1, 'desc']],
    pageLength: 10
  });

  const gastosPorCategoria = <?= json_encode($gastosPorCategoria, JSON_UNESCAPED_UNICODE) ?>;
  const diasMayorGasto = <?= json_encode($diasMayorGasto, JSON_UNESCAPED_UNICODE) ?>;
  const serieMensual = <?= json_encode($serieMensual, JSON_UNESCAPED_UNICODE) ?>;
  const ingresosPorCategoria = <?= json_encode($ingresosPorCategoria, JSON_UNESCAPED_UNICODE) ?>;
  const balanceSemanal = <?= json_encode($balanceSemanal, JSON_UNESCAPED_UNICODE) ?>;
  const balanceAcumulado = <?= json_encode($balanceAcumulado, JSON_UNESCAPED_UNICODE) ?>;
  const gastosDiarios = <?= json_encode($gastosDiarios, JSON_UNESCAPED_UNICODE) ?>;
  const flujoDiario = <?= json_encode($flujoDiario, JSON_UNESCAPED_UNICODE) ?>;
  const gastoAcumulado = <?= json_encode($gastoAcumulado, JSON_UNESCAPED_UNICODE) ?>;
  const resumenMensualSeleccionado = <?= json_encode($resumenMensualSeleccionado, JSON_UNESCAPED_UNICODE) ?>;

  const moneyFormatter = (value) => `Q ${Number(value).toLocaleString('es-GT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  const chartAnimation = { duration: 1500, easing: 'easeOutQuart' };
  const chartTooltip = { callbacks: { label: (context) => `${context.dataset.label ?? context.label}: ${moneyFormatter(context.parsed.y ?? context.parsed ?? 0)}` } };

  const emptyState = (id, text) => {
    const canvas = document.getElementById(id);
    if (!canvas) return;
    const p = document.createElement('p');
    p.className = 'text-muted mb-0';
    p.textContent = text;
    canvas.replaceWith(p);
  };

  if (gastosPorCategoria.length) {
    new Chart(document.getElementById('chartGastosCategoria'), {
      type: 'doughnut',
      data: {
        labels: gastosPorCategoria.map(i => i.categoria),
        datasets: [{ data: gastosPorCategoria.map(i => i.total) }]
      },
      options: { responsive: true, animation: chartAnimation, plugins: { tooltip: chartTooltip } }
    });
  } else {
    emptyState('chartGastosCategoria', 'Sin datos del mes seleccionado.');
  }

  if (diasMayorGasto.length) {
    new Chart(document.getElementById('chartDiasGasto'), {
      type: 'bar',
      data: {
        labels: diasMayorGasto.map(i => i.dia),
        datasets: [{ label: 'Gastos', data: diasMayorGasto.map(i => i.total), backgroundColor: '#dc3545' }]
      },
      options: { responsive: true, animation: chartAnimation, plugins: { legend: { display: false }, tooltip: chartTooltip }, scales: { y: { ticks: { callback: value => moneyFormatter(value) } } } }
    });
  } else {
    emptyState('chartDiasGasto', 'Sin datos del mes seleccionado.');
  }

  if (serieMensual.length) {
    new Chart(document.getElementById('chartSerieMensual'), {
      type: 'line',
      data: {
        labels: serieMensual.map(i => i.mes),
        datasets: [
          { label: 'Ingresos', data: serieMensual.map(i => i.ingresos), borderColor: '#198754' },
          { label: 'Gastos', data: serieMensual.map(i => i.gastos), borderColor: '#dc3545' }
        ]
      },
      options: { responsive: true, animation: chartAnimation, plugins: { tooltip: chartTooltip }, scales: { y: { ticks: { callback: value => moneyFormatter(value) } } } }
    });
  } else {
    emptyState('chartSerieMensual', 'Sin datos históricos.');
  }


  if (ingresosPorCategoria.length) {
    new Chart(document.getElementById('chartIngresosCategoria'), {
      type: 'polarArea',
      data: {
        labels: ingresosPorCategoria.map(i => i.categoria),
        datasets: [{
          label: 'Ingresos',
          data: ingresosPorCategoria.map(i => i.total),
          backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b', '#14b8a6', '#8b5cf6', '#ef4444']
        }]
      },
      options: { responsive: true, animation: chartAnimation, plugins: { tooltip: chartTooltip } }
    });
  } else {
    emptyState('chartIngresosCategoria', 'No hay ingresos clasificados en este mes.');
  }

  if (balanceSemanal.length) {
    new Chart(document.getElementById('chartBalanceSemanal'), {
      type: 'bar',
      data: {
        labels: balanceSemanal.map(i => i.semana),
        datasets: [
          { label: 'Balance', data: balanceSemanal.map(i => i.balance), backgroundColor: balanceSemanal.map(i => i.balance >= 0 ? '#16a34a' : '#dc2626'), borderRadius: 10 }
        ]
      },
      options: { responsive: true, animation: chartAnimation, plugins: { tooltip: chartTooltip }, scales: { y: { ticks: { callback: value => moneyFormatter(value) } } } }
    });
  } else {
    emptyState('chartBalanceSemanal', 'No hay suficientes movimientos para calcular balance semanal.');
  }

  if (balanceAcumulado.length) {
    new Chart(document.getElementById('chartBalanceAcumulado'), {
      type: 'line',
      data: {
        labels: balanceAcumulado.map(i => i.dia),
        datasets: [{
          label: 'Balance acumulado',
          data: balanceAcumulado.map(i => i.balance),
          borderColor: '#2563eb',
          backgroundColor: 'rgba(37, 99, 235, .15)',
          fill: true,
          tension: .35
        }]
      },
      options: { responsive: true, animation: chartAnimation, plugins: { tooltip: chartTooltip }, scales: { y: { ticks: { callback: value => moneyFormatter(value) } } } }
    });
  } else {
    emptyState('chartBalanceAcumulado', 'No hay movimientos suficientes para mostrar balance acumulado.');
  }

  if (gastosDiarios.some(item => item.total > 0)) {
    new Chart(document.getElementById('chartGastosDiarios'), {
      type: 'line',
      data: {
        labels: gastosDiarios.map(i => i.dia),
        datasets: [{
          label: 'Gastos diarios',
          data: gastosDiarios.map(i => i.total),
          borderColor: '#dc2626',
          backgroundColor: 'rgba(220, 38, 38, .14)',
          pointBackgroundColor: '#ef4444',
          pointRadius: 4,
          fill: true,
          tension: .3
        }]
      },
      options: { responsive: true, animation: chartAnimation, plugins: { tooltip: chartTooltip }, scales: { y: { beginAtZero: true, ticks: { callback: value => moneyFormatter(value) } } } }
    });
  } else {
    emptyState('chartGastosDiarios', 'No hay gastos diarios para el mes seleccionado.');
  }

  if (flujoDiario.some(item => item.ingresos > 0 || item.gastos > 0)) {
    new Chart(document.getElementById('chartFlujoDiario'), {
      type: 'bar',
      data: {
        labels: flujoDiario.map(i => i.dia),
        datasets: [
          { label: 'Ingresos', data: flujoDiario.map(i => i.ingresos), backgroundColor: 'rgba(22, 163, 74, .78)', borderRadius: 8 },
          { label: 'Gastos', data: flujoDiario.map(i => i.gastos), backgroundColor: 'rgba(220, 38, 38, .78)', borderRadius: 8 }
        ]
      },
      options: { responsive: true, animation: chartAnimation, plugins: { tooltip: chartTooltip }, scales: { y: { beginAtZero: true, ticks: { callback: value => moneyFormatter(value) } } } }
    });
  } else {
    emptyState('chartFlujoDiario', 'No hay movimientos diarios para comparar ingresos y gastos.');
  }

  if (gastoAcumulado.some(item => item.total > 0)) {
    new Chart(document.getElementById('chartGastoAcumulado'), {
      type: 'line',
      data: {
        labels: gastoAcumulado.map(i => i.dia),
        datasets: [{
          label: 'Gasto acumulado',
          data: gastoAcumulado.map(i => i.total),
          borderColor: '#ea580c',
          backgroundColor: 'rgba(234, 88, 12, .15)',
          pointBackgroundColor: '#f97316',
          fill: true,
          tension: .32
        }]
      },
      options: { responsive: true, animation: chartAnimation, plugins: { tooltip: chartTooltip }, scales: { y: { beginAtZero: true, ticks: { callback: value => moneyFormatter(value) } } } }
    });
  } else {
    emptyState('chartGastoAcumulado', 'No hay gastos suficientes para calcular el acumulado del mes.');
  }

  if (resumenMensualSeleccionado.some(item => item.total > 0)) {
    new Chart(document.getElementById('chartResumenMensual'), {
      type: 'bar',
      data: {
        labels: resumenMensualSeleccionado.map(i => i.tipo),
        datasets: [{
          label: 'Monto',
          data: resumenMensualSeleccionado.map(i => i.total),
          backgroundColor: ['#16a34a', '#ef4444', '#2563eb'],
          borderRadius: 12
        }]
      },
      options: { responsive: true, animation: chartAnimation, plugins: { legend: { display: false }, tooltip: chartTooltip }, scales: { y: { ticks: { callback: value => moneyFormatter(value) } } } }
    });
  } else {
    emptyState('chartResumenMensual', 'Aún no hay datos del mes para construir el resumen financiero.');
  }

  const darkBtn = document.getElementById('toggleDark');
  const applyMode = (isDark) => {
    document.body.classList.toggle('dark-mode', isDark);
    darkBtn.innerHTML = isDark ? '<i class="bi bi-sun"></i> Modo claro' : '<i class="bi bi-moon-stars"></i> Modo oscuro';
  };
  let isDark = localStorage.getItem('finanzas-dark') === '1';
  applyMode(isDark);
  darkBtn.addEventListener('click', () => {
    isDark = !isDark;
    localStorage.setItem('finanzas-dark', isDark ? '1' : '0');
    applyMode(isDark);
  });
</script>
</body>
</html>