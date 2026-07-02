<?php
session_start();

const ACCESS_PASSWORD = 'Inicio1994=';
const ACCESS_SESSION_FLAG = 'menu_access_granted';

$claveError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clave_acceso'])) {
  $claveIngresada = trim((string)($_POST['clave_acceso'] ?? ''));
  if ($claveIngresada !== '' && hash_equals(ACCESS_PASSWORD, $claveIngresada)) {
    $_SESSION[ACCESS_SESSION_FLAG] = true;
    header('Location: ' . basename(__FILE__));
    exit;
  }
  $claveError = 'La clave ingresada no es correcta. Inténtalo de nuevo.';
}

if (empty($_SESSION[ACCESS_SESSION_FLAG])) {
  ?><!doctype html>
  <html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Acceso requerido</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/ui-enhancements.css">
  </head>
  <body class="bg-dark text-white d-flex align-items-center" style="min-height:100vh;">
    <div id="pageLoader" class="page-loader">
      <div class="page-loader__spinner"></div>
      <div class="page-loader__progress"></div>
      <p class="page-loader__text">Preparando acceso seguro...</p>
    </div>
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
          <div class="card shadow-lg border-0">
            <div class="card-body">
              <h1 class="h5 text-center mb-3">Introduce la clave de acceso</h1>
              <p class="text-muted text-center">Ingresa la clave para continuar con el panel.</p>
              <?php if ($claveError !== ''): ?>
                <div class="alert alert-danger py-2" role="alert">
                  <?= htmlspecialchars($claveError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </div>
              <?php endif; ?>
              <form method="post" class="d-grid gap-3">
                <div>
                  <label for="clave_acceso" class="form-label">Clave</label>
                  <input type="password" class="form-control" name="clave_acceso" id="clave_acceso" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary">Ingresar</button>
                <a href="index.php" class="btn btn-outline-secondary">Volver al inicio</a>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/ui-enhancements.js"></script>
  </body>
  </html>
  <?php
  exit;
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/schema_chiles.php';

$conn = db();

if (!($conn instanceof mysqli)) {
  echo '<p>No se pudo obtener una conexión a la base de datos.</p>';
  exit;
}

if ($conn->connect_errno) {
  echo '<p>Error de conexión a la base de datos: ' . htmlspecialchars($conn->connect_error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
  exit;
}

try {
  ensure_chiles_schema($conn);
} catch (Throwable $e) {
  http_response_code(500);
  echo '<p>Error al preparar la base de datos: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
  exit;
}

function get_open_chiles_period(mysqli $c) {
  $res = $c->query("SELECT * FROM chiles_periods WHERE status='abierto' ORDER BY id DESC LIMIT 1");
  return $res && $res->num_rows ? $res->fetch_assoc() : null;
}

function open_new_chiles_period(mysqli $c) {
  $c->query("INSERT INTO chiles_periods(opened_at,status) VALUES (NOW(),'abierto')");
  return $c->insert_id;
}

$period = get_open_chiles_period($conn);
if (!$period) {
  $period_id = open_new_chiles_period($conn);
  $period = $conn->query("SELECT * FROM chiles_periods WHERE id=$period_id")->fetch_assoc();
}

$current_period_id = (int)$period['id'];

if (isset($_POST['action']) && $_POST['action'] === 'add_order') {
  $PRECIO_PORCION = 15.00;
  $PRECIO_UNIDAD = 7.00;

  $sectoresPermitidos = [
    'Arboleas-1', 'Montillana-2', 'Cardelas-3',
    'Villamena-4', 'Paules-5', 'Almazora-6'
  ];

  $sector = $_POST['sector'] ?? '';
  $casa = strtoupper(trim((string)($_POST['casa'] ?? '')));
  $codigo = trim((string)($_POST['codigo'] ?? ''));
  $codigo = preg_replace('/\D+/', '', $codigo ?? '');
  $porciones = max(0, (int)($_POST['porciones'] ?? 0));
  $unidades = max(0, (int)($_POST['unidades'] ?? 0));
  $comentarios = trim((string)($_POST['comentarios'] ?? ''));
  $tipo_pago = $_POST['tipo_pago'] ?? 'Transferencia';
  $monto_pagado = null;
  $cambio = null;

  if (!in_array($sector, $sectoresPermitidos, true)) {
    $sector = $sectoresPermitidos[0];
  }

  if ($tipo_pago === 'Efectivo') {
    $monto_pagadoRaw = str_replace(',', '.', (string)($_POST['monto_pagado'] ?? ''));
    if ($monto_pagadoRaw !== '' && is_numeric($monto_pagadoRaw)) {
      $monto_pagado = max(0, (float)$monto_pagadoRaw);
    }
  } else {
    $tipo_pago = 'Transferencia';
  }

  if (($porciones + $unidades) > 0 && $casa !== '' && $codigo !== '') {
    $total = ($porciones * $PRECIO_PORCION) + ($unidades * $PRECIO_UNIDAD);

    if ($tipo_pago === 'Efectivo' && $monto_pagado !== null) {
      $cambio = $monto_pagado - $total;
    }

    $stmt = $conn->prepare("INSERT INTO chiles_orders
      (period_id, sector, casa, codigo, porciones, unidades, comentarios, tipo_pago, monto_pagado, cambio, precio_porcion, precio_unidad, total, estado, hora_registro)
      VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?, 'Registrado', NOW())");

    $montoPagadoParam = $monto_pagado !== null ? number_format($monto_pagado, 2, '.', '') : null;
    $cambioParam = $cambio !== null ? number_format($cambio, 2, '.', '') : null;

    $stmt->bind_param(
      'isssiissssddd',
      $current_period_id,
      $sector,
      $casa,
      $codigo,
      $porciones,
      $unidades,
      $comentarios,
      $tipo_pago,
      $montoPagadoParam,
      $cambioParam,
      $PRECIO_PORCION,
      $PRECIO_UNIDAD,
      $total
    );

    if (!$stmt->execute()) {
      die('Error al registrar pedido: ' . $stmt->error);
    }
    $stmt->close();
  }

  header('Location: Chiles.php');
  exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'update_estado') {
  $id = (int)$_POST['id'];
  $nuevo = $_POST['estado'] === 'Despachado' ? 'Despachado' : 'Registrado';
  if ($nuevo === 'Despachado') {
    $stmt = $conn->prepare("UPDATE chiles_orders SET estado='Despachado', hora_entrega=NOW() WHERE id=? AND period_id=?");
  } else {
    $stmt = $conn->prepare("UPDATE chiles_orders SET estado='Registrado', hora_entrega=NULL WHERE id=? AND period_id=?");
  }
  $stmt->bind_param('ii', $id, $current_period_id);
  $stmt->execute();
  $stmt->close();
  header('Location: Chiles.php');
  exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'cerrar_periodo') {
  $conn->query("UPDATE chiles_periods SET status='cerrado', closed_at=NOW() WHERE id=$current_period_id AND status='abierto'");
  open_new_chiles_period($conn);
  header('Location: Chiles.php');
  exit;
}

$orders = $conn->query("SELECT * FROM chiles_orders WHERE period_id=$current_period_id ORDER BY id DESC");
$totales = $conn->query(<<<SQL
  SELECT COALESCE(SUM(porciones),0) AS total_porciones,
         COALESCE(SUM(unidades),0) AS total_unidades,
         COALESCE(SUM(total),0) AS total_q
  FROM chiles_orders
  WHERE period_id=$current_period_id
SQL)->fetch_assoc();

$resumenEstado = $conn->query(<<<SQL
  SELECT
    COALESCE(SUM(CASE WHEN estado='Despachado' THEN (porciones * 3) + unidades ELSE 0 END), 0) AS chiles_vendidos,
    COALESCE(SUM(CASE WHEN estado='Registrado' THEN (porciones * 3) + unidades ELSE 0 END), 0) AS chiles_pendientes,
    COALESCE(SUM(CASE WHEN estado='Despachado' THEN 1 ELSE 0 END), 0) AS pedidos_vendidos,
    COALESCE(SUM(CASE WHEN estado='Registrado' THEN 1 ELSE 0 END), 0) AS pedidos_pendientes,
    COALESCE(SUM(CASE WHEN estado='Despachado' THEN total ELSE 0 END), 0) AS dinero_vendido,
    COALESCE(SUM(CASE WHEN estado='Registrado' THEN total ELSE 0 END), 0) AS dinero_pendiente
  FROM chiles_orders
  WHERE period_id=$current_period_id
SQL)->fetch_assoc();

$totalPedidosPeriodo = (int)$resumenEstado['pedidos_vendidos'] + (int)$resumenEstado['pedidos_pendientes'];
$totalChilesPeriodo = (int)$resumenEstado['chiles_vendidos'] + (int)$resumenEstado['chiles_pendientes'];
$totalDineroPeriodo = (float)$resumenEstado['dinero_vendido'] + (float)$resumenEstado['dinero_pendiente'];

$pedidosSectorRows = $conn->query(<<<SQL
  SELECT sector, COUNT(*) AS total_pedidos
  FROM chiles_orders
  WHERE period_id=$current_period_id
  GROUP BY sector
  ORDER BY total_pedidos DESC, sector ASC
SQL);

$labelsSector = [];
$totalesSector = [];
if ($pedidosSectorRows && $pedidosSectorRows->num_rows) {
  while ($rowSector = $pedidosSectorRows->fetch_assoc()) {
    $labelsSector[] = (string)$rowSector['sector'];
    $totalesSector[] = (int)$rowSector['total_pedidos'];
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Control de Pedidos de Chiles</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/ui-enhancements.css">
</head>
<body class="bg-light">
  <div id="pageLoader" class="page-loader">
    <div class="page-loader__spinner"></div>
    <div class="page-loader__progress"></div>
    <p class="page-loader__text">Cargando control de pedidos de chiles...</p>
  </div>

  <div class="container py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
      <div>
        <h1 class="h3 mb-1">Control de pedidos de Chiles</h1>
        <span class="badge text-bg-secondary">Periodo #<?= $current_period_id ?> · abierto desde <?= htmlspecialchars($period['opened_at']) ?></span>
      </div>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <a class="btn btn-outline-secondary" href="index.php"><i class="bi bi-house"></i> Ir al inicio</a>
        <form method="post" onsubmit="return confirm('¿Cerrar el periodo actual? Se abrirá uno nuevo para los siguientes pedidos.');">
          <input type="hidden" name="action" value="cerrar_periodo">
          <button class="btn btn-outline-primary"><i class="bi bi-lock"></i> Cerrar pedidos</button>
        </form>
      </div>
    </div>

    <div class="card p-4 mb-4 shadow-sm">
      <form method="post" class="row g-3" id="formPedidoChiles">
        <input type="hidden" name="action" value="add_order">

        <div class="col-md-3">
          <label class="form-label">Sector</label>
          <select name="sector" class="form-select" required>
            <?php foreach(['Arboleas-1','Montillana-2','Cardelas-3','Villamena-4','Paules-5','Almazora-6'] as $s): ?>
              <option value="<?= $s ?>"><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Casa</label>
          <input type="text" name="casa" id="casa" class="form-control" maxlength="50" placeholder="Ej: B12" required>
        </div>

        <div class="col-md-2">
          <label class="form-label">Código</label>
          <input type="text" name="codigo" class="form-control" inputmode="numeric" pattern="[0-9]+" maxlength="30" placeholder="Ej: 00125" required>
          <div class="form-text">Se guardan los ceros a la izquierda.</div>
        </div>

        <div class="col-md-2">
          <label class="form-label">Porciones</label>
          <input type="number" id="porciones" name="porciones" class="form-control" min="0" step="1" value="0" required>
          <div class="form-text">3 rellenitos = Q15.00</div>
        </div>

        <div class="col-md-2">
          <label class="form-label">Unidades</label>
          <input type="number" id="unidades" name="unidades" class="form-control" min="0" step="1" value="0" required>
          <div class="form-text">Q7.00 por unidad</div>
        </div>

        <div class="col-12">
          <label class="form-label">Comentarios</label>
          <textarea name="comentarios" class="form-control" rows="2" placeholder="Observaciones del pedido..."></textarea>
        </div>

        <div class="col-md-3">
          <label class="form-label">Tipo de pago</label>
          <select name="tipo_pago" id="tipo_pago" class="form-select" required>
            <option value="Transferencia">Transferencia</option>
            <option value="Efectivo">Efectivo</option>
          </select>
        </div>

        <div class="col-md-3 d-none" id="grpMontoPagado">
          <label class="form-label">Paga con (Q)</label>
          <input type="number" name="monto_pagado" id="monto_pagado" class="form-control" step="0.01" min="0" placeholder="Ej: 50.00">
        </div>

        <div class="col-md-3">
          <label class="form-label">Total</label>
          <input type="text" id="total" class="form-control" value="Q0.00" readonly>
        </div>

        <div class="col-md-3 d-none" id="grpCambioCalculado">
          <label class="form-label">Cambio estimado</label>
          <input type="text" id="cambio_calculado" class="form-control" value="Q0.00" readonly>
        </div>

        <div class="col-12 d-flex gap-2 flex-wrap">
          <button class="btn btn-primary" type="submit"><i class="bi bi-plus-lg"></i> Guardar pedido</button>
          <a href="Chiles.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i> Refrescar</a>
        </div>
      </form>
    </div>

    <div class="card p-3 mb-4 shadow-sm">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="h5 mb-0">Resumen gráfico del periodo</h2>
        <small class="text-muted">Vendidos vs pendientes (estado del pedido)</small>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-12 col-md-4">
          <div class="border rounded-3 p-3 h-100">
            <div class="text-muted small">Chiles del periodo</div>
            <div class="fs-4 fw-semibold"><?= $totalChilesPeriodo ?></div>
            <div class="small text-success">Vendidos: <?= (int)$resumenEstado['chiles_vendidos'] ?></div>
            <div class="small text-warning">Pendientes: <?= (int)$resumenEstado['chiles_pendientes'] ?></div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="border rounded-3 p-3 h-100">
            <div class="text-muted small">Pedidos del periodo</div>
            <div class="fs-4 fw-semibold"><?= $totalPedidosPeriodo ?></div>
            <div class="small text-success">Vendidos: <?= (int)$resumenEstado['pedidos_vendidos'] ?></div>
            <div class="small text-warning">Pendientes: <?= (int)$resumenEstado['pedidos_pendientes'] ?></div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="border rounded-3 p-3 h-100">
            <div class="text-muted small">Dinero del periodo (Q)</div>
            <div class="fs-4 fw-semibold">Q<?= number_format($totalDineroPeriodo, 2) ?></div>
            <div class="small text-success">Vendido: Q<?= number_format((float)$resumenEstado['dinero_vendido'], 2) ?></div>
            <div class="small text-warning">Pendiente: Q<?= number_format((float)$resumenEstado['dinero_pendiente'], 2) ?></div>
          </div>
        </div>
      </div>
      <div class="row g-3">
        <div class="col-12 col-lg-6">
          <div class="border rounded-3 p-3">
            <h3 class="h6 mb-3">Chiles vendidos y pendientes</h3>
            <div class="w-50 mx-auto">
              <canvas id="chartChiles"></canvas>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <div class="border rounded-3 p-3">
            <h3 class="h6 mb-3">Pedidos y dinero por estado</h3>
            <canvas id="chartPedidosDinero"></canvas>
          </div>
        </div>
        <div class="col-12">
          <div class="border rounded-3 p-3">
            <h3 class="h6 mb-3">Cantidad de pedidos por sector</h3>
            <div style="height:180px;">
              <canvas id="chartPedidosSector"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card p-3 shadow-sm">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h5 mb-0">Resumen del periodo</h2>
        <div class="text-end small text-muted">
          <div>Porciones: <strong><?= (int)$totales['total_porciones'] ?></strong></div>
          <div>Unidades: <strong><?= (int)$totales['total_unidades'] ?></strong></div>
          <div>Total Q: <strong>Q<?= number_format((float)$totales['total_q'], 2) ?></strong></div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-sm align-middle table-hover">
          <thead>
            <tr>
              <th>#</th><th>Sector</th><th>Casa</th><th>Código</th>
              <th class="text-end">Porciones</th><th class="text-end">Unidades</th><th class="text-end">Total (Q)</th>
              <th>Pago</th><th class="text-end">Paga con</th><th class="text-end">Cambio</th>
              <th>Estado</th><th>Registro</th><th>Entrega</th><th>Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php if ($orders && $orders->num_rows): while ($r = $orders->fetch_assoc()): ?>
            <tr>
              <td><?= $r['id'] ?></td>
              <td><?= htmlspecialchars($r['sector']) ?></td>
              <td><?= htmlspecialchars($r['casa']) ?></td>
              <td><?= htmlspecialchars($r['codigo']) ?></td>
              <td class="text-end"><?= (int)$r['porciones'] ?></td>
              <td class="text-end"><?= (int)$r['unidades'] ?></td>
              <td class="text-end">Q<?= number_format((float)$r['total'], 2) ?></td>
              <td><?= htmlspecialchars($r['tipo_pago']) ?></td>
              <td class="text-end"><?= $r['monto_pagado'] !== null ? 'Q' . number_format((float)$r['monto_pagado'], 2) : '—' ?></td>
              <td class="text-end"><?= $r['cambio'] !== null ? 'Q' . number_format((float)$r['cambio'], 2) : '—' ?></td>
              <td>
                <span class="badge rounded-pill <?= $r['estado'] === 'Despachado' ? 'text-bg-success' : 'text-bg-warning' ?>">
                  <?= $r['estado'] ?>
                </span>
              </td>
              <td><small class="text-muted"><?= $r['hora_registro'] ?></small></td>
              <td><small class="text-muted"><?= $r['hora_entrega'] ?: '—' ?></small></td>
              <td>
                <div class="d-flex gap-1">
                  <button
                    type="button"
                    class="btn btn-sm btn-info text-white"
                    title="Ver detalle"
                    data-bs-toggle="modal"
                    data-bs-target="#detallePedidoModal"
                    data-id="<?= (int)$r['id'] ?>"
                    data-sector="<?= htmlspecialchars($r['sector'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                    data-casa="<?= htmlspecialchars($r['casa'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                    data-codigo="<?= htmlspecialchars($r['codigo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                    data-porciones="<?= (int)$r['porciones'] ?>"
                    data-unidades="<?= (int)$r['unidades'] ?>"
                    data-tipo-pago="<?= htmlspecialchars($r['tipo_pago'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                    data-paga-con="<?= $r['monto_pagado'] !== null ? 'Q' . number_format((float)$r['monto_pagado'], 2) : '—' ?>"
                    data-cambio="<?= $r['cambio'] !== null ? 'Q' . number_format((float)$r['cambio'], 2) : '—' ?>"
                    data-total="<?= 'Q' . number_format((float)$r['total'], 2) ?>"
                    data-estado="<?= htmlspecialchars($r['estado'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                    data-comentarios="<?= htmlspecialchars((string)($r['comentarios'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                    data-registro="<?= htmlspecialchars($r['hora_registro'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                    data-entrega="<?= htmlspecialchars((string)($r['hora_entrega'] ?: '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                  >
                    <i class="bi bi-eye"></i>
                  </button>
                  <form method="post" class="d-flex gap-1">
                    <input type="hidden" name="action" value="update_estado">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <?php if ($r['estado'] === 'Registrado'): ?>
                      <input type="hidden" name="estado" value="Despachado">
                      <button class="btn btn-sm btn-success" title="Marcar como Despachado"><i class="bi bi-check2-circle"></i></button>
                    <?php else: ?>
                      <input type="hidden" name="estado" value="Registrado">
                      <button class="btn btn-sm btn-outline-secondary" title="Revertir a Registrado"><i class="bi bi-arrow-counterclockwise"></i></button>
                    <?php endif; ?>
                  </form>
                </div>
              </td>
            </tr>
          <?php endwhile; else: ?>
            <tr><td colspan="14" class="text-center text-muted py-4">Sin pedidos aún en este periodo.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="modal fade" id="detallePedidoModal" tabindex="-1" aria-labelledby="detallePedidoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="detallePedidoModalLabel">Detalle del pedido</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-6 col-md-4"><small class="text-muted d-block">ID</small><strong id="detalle_id">—</strong></div>
            <div class="col-6 col-md-4"><small class="text-muted d-block">Sector</small><strong id="detalle_sector">—</strong></div>
            <div class="col-6 col-md-4"><small class="text-muted d-block">Casa</small><strong id="detalle_casa">—</strong></div>
            <div class="col-6 col-md-4"><small class="text-muted d-block">Código</small><strong id="detalle_codigo">—</strong></div>
            <div class="col-6 col-md-4"><small class="text-muted d-block">Porciones</small><strong id="detalle_porciones">—</strong></div>
            <div class="col-6 col-md-4"><small class="text-muted d-block">Unidades</small><strong id="detalle_unidades">—</strong></div>
            <div class="col-6 col-md-4"><small class="text-muted d-block">Tipo pago</small><strong id="detalle_tipo_pago">—</strong></div>
            <div class="col-6 col-md-4"><small class="text-muted d-block">Paga con</small><strong id="detalle_paga_con">—</strong></div>
            <div class="col-6 col-md-4"><small class="text-muted d-block">Cambio</small><strong id="detalle_cambio">—</strong></div>
            <div class="col-6 col-md-4"><small class="text-muted d-block">Total</small><strong id="detalle_total">—</strong></div>
            <div class="col-6 col-md-4"><small class="text-muted d-block">Estado</small><strong id="detalle_estado">—</strong></div>
            <div class="col-6 col-md-4"><small class="text-muted d-block">Registro</small><strong id="detalle_registro">—</strong></div>
            <div class="col-12"><small class="text-muted d-block">Entrega</small><strong id="detalle_entrega">—</strong></div>
            <div class="col-12"><small class="text-muted d-block">Comentarios</small><div id="detalle_comentarios" class="border rounded p-2 bg-light-subtle">—</div></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/ui-enhancements.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script>
    const resumenEstado = {
      chilesVendidos: <?= (int)$resumenEstado['chiles_vendidos'] ?>,
      chilesPendientes: <?= (int)$resumenEstado['chiles_pendientes'] ?>,
      pedidosVendidos: <?= (int)$resumenEstado['pedidos_vendidos'] ?>,
      pedidosPendientes: <?= (int)$resumenEstado['pedidos_pendientes'] ?>,
      dineroVendido: <?= number_format((float)$resumenEstado['dinero_vendido'], 2, '.', '') ?>,
      dineroPendiente: <?= number_format((float)$resumenEstado['dinero_pendiente'], 2, '.', '') ?>
    };
    const pedidosPorSector = {
      labels: <?= json_encode($labelsSector, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
      data: <?= json_encode($totalesSector, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
    };

    const precioPorcion = 15;
    const precioUnidad = 7;

    const casaInput = document.getElementById('casa');
    const porcionesInput = document.getElementById('porciones');
    const unidadesInput = document.getElementById('unidades');
    const tipoPagoInput = document.getElementById('tipo_pago');
    const montoPagadoInput = document.getElementById('monto_pagado');
    const totalInput = document.getElementById('total');
    const cambioInput = document.getElementById('cambio_calculado');
    const grpMontoPagado = document.getElementById('grpMontoPagado');
    const grpCambioCalculado = document.getElementById('grpCambioCalculado');

    function updateTotal() {
      const porciones = Math.max(0, parseInt(porcionesInput.value || 0, 10));
      const unidades = Math.max(0, parseInt(unidadesInput.value || 0, 10));
      const total = (porciones * precioPorcion) + (unidades * precioUnidad);
      totalInput.value = 'Q' + total.toFixed(2);
      updateCambio(total);
    }

    function updateCambio(totalActual) {
      if (tipoPagoInput.value !== 'Efectivo') {
        return;
      }
      const montoPagado = parseFloat(montoPagadoInput.value || 0);
      const cambio = montoPagado - totalActual;
      cambioInput.value = 'Q' + cambio.toFixed(2);
    }

    function toggleEfectivoFields() {
      if (tipoPagoInput.value === 'Efectivo') {
        grpMontoPagado.classList.remove('d-none');
        grpCambioCalculado.classList.remove('d-none');
      } else {
        grpMontoPagado.classList.add('d-none');
        grpCambioCalculado.classList.add('d-none');
        montoPagadoInput.value = '';
        cambioInput.value = 'Q0.00';
      }
      updateTotal();
    }

    casaInput.addEventListener('input', () => {
      casaInput.value = casaInput.value.toUpperCase();
    });

    porcionesInput.addEventListener('input', updateTotal);
    unidadesInput.addEventListener('input', updateTotal);
    tipoPagoInput.addEventListener('change', toggleEfectivoFields);
    montoPagadoInput.addEventListener('input', () => {
      const porciones = Math.max(0, parseInt(porcionesInput.value || 0, 10));
      const unidades = Math.max(0, parseInt(unidadesInput.value || 0, 10));
      const total = (porciones * precioPorcion) + (unidades * precioUnidad);
      updateCambio(total);
    });

    updateTotal();
    toggleEfectivoFields();

    const chartChilesCtx = document.getElementById('chartChiles');
    if (chartChilesCtx && window.Chart) {
      new Chart(chartChilesCtx, {
        type: 'doughnut',
        data: {
          labels: ['Vendidos', 'Pendientes'],
          datasets: [{
            data: [resumenEstado.chilesVendidos, resumenEstado.chilesPendientes],
            backgroundColor: ['#198754', '#ffc107'],
            borderWidth: 0
          }]
        },
        options: {
          plugins: {
            legend: { position: 'bottom' }
          }
        }
      });
    }

    const chartPedidosDineroCtx = document.getElementById('chartPedidosDinero');
    if (chartPedidosDineroCtx && window.Chart) {
      new Chart(chartPedidosDineroCtx, {
        type: 'bar',
        data: {
          labels: ['Vendidos', 'Pendientes'],
          datasets: [
            {
              label: 'Pedidos',
              data: [resumenEstado.pedidosVendidos, resumenEstado.pedidosPendientes],
              backgroundColor: '#0d6efd'
            },
            {
              label: 'Dinero (Q)',
              data: [resumenEstado.dineroVendido, resumenEstado.dineroPendiente],
              backgroundColor: '#6f42c1'
            }
          ]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { position: 'bottom' }
          }
        }
      });
    }

    const chartPedidosSectorCtx = document.getElementById('chartPedidosSector');
    if (chartPedidosSectorCtx && window.Chart) {
      new Chart(chartPedidosSectorCtx, {
        type: 'bar',
        data: {
          labels: pedidosPorSector.labels,
          datasets: [{
            label: 'Pedidos',
            data: pedidosPorSector.data,
            backgroundColor: '#fd7e14'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { precision: 0 }
            }
          }
        }
      });
    }

    const detallePedidoModal = document.getElementById('detallePedidoModal');
    if (detallePedidoModal) {
      const detailFields = {
        id: document.getElementById('detalle_id'),
        sector: document.getElementById('detalle_sector'),
        casa: document.getElementById('detalle_casa'),
        codigo: document.getElementById('detalle_codigo'),
        porciones: document.getElementById('detalle_porciones'),
        unidades: document.getElementById('detalle_unidades'),
        tipoPago: document.getElementById('detalle_tipo_pago'),
        pagaCon: document.getElementById('detalle_paga_con'),
        cambio: document.getElementById('detalle_cambio'),
        total: document.getElementById('detalle_total'),
        estado: document.getElementById('detalle_estado'),
        registro: document.getElementById('detalle_registro'),
        entrega: document.getElementById('detalle_entrega'),
        comentarios: document.getElementById('detalle_comentarios')
      };

      detallePedidoModal.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        if (!trigger) return;

        detailFields.id.textContent = trigger.getAttribute('data-id') || '—';
        detailFields.sector.textContent = trigger.getAttribute('data-sector') || '—';
        detailFields.casa.textContent = trigger.getAttribute('data-casa') || '—';
        detailFields.codigo.textContent = trigger.getAttribute('data-codigo') || '—';
        detailFields.porciones.textContent = trigger.getAttribute('data-porciones') || '0';
        detailFields.unidades.textContent = trigger.getAttribute('data-unidades') || '0';
        detailFields.tipoPago.textContent = trigger.getAttribute('data-tipo-pago') || '—';
        detailFields.pagaCon.textContent = trigger.getAttribute('data-paga-con') || '—';
        detailFields.cambio.textContent = trigger.getAttribute('data-cambio') || '—';
        detailFields.total.textContent = trigger.getAttribute('data-total') || 'Q0.00';
        detailFields.estado.textContent = trigger.getAttribute('data-estado') || '—';
        detailFields.registro.textContent = trigger.getAttribute('data-registro') || '—';
        detailFields.entrega.textContent = trigger.getAttribute('data-entrega') || '—';
        detailFields.comentarios.textContent = trigger.getAttribute('data-comentarios') || 'Sin comentarios';
      });
    }
  </script>
</body>
</html>