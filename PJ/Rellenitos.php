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
require_once __DIR__ . '/schema_rellenitos.php';

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
  ensure_rellenitos_schema($conn);
} catch (Throwable $e) {
  http_response_code(500);
  echo '<p>Error al preparar la base de datos: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
  exit;
}

function get_open_rellenito_period(mysqli $c) {
  $res = $c->query("SELECT * FROM rellenito_periods WHERE status='abierto' ORDER BY id DESC LIMIT 1");
  return $res && $res->num_rows ? $res->fetch_assoc() : null;
}

function open_new_rellenito_period(mysqli $c) {
  $c->query("INSERT INTO rellenito_periods(opened_at,status) VALUES (NOW(),'abierto')");
  return $c->insert_id;
}

$period = get_open_rellenito_period($conn);
if (!$period) {
  $period_id = open_new_rellenito_period($conn);
  $period = $conn->query("SELECT * FROM rellenito_periods WHERE id=$period_id")->fetch_assoc();
}

$current_period_id = (int)$period['id'];

if (isset($_POST['action']) && $_POST['action'] === 'add_order') {
  $PRECIO = 15.00;

  $topings = ['Azucar', 'Dulce de Leche', 'Chocolate', 'Mix', 'Sin Toping'];

  $sector    = $_POST['sector'] ?? '';
  $casa      = trim($_POST['casa'] ?? '');
  $codigo    = (int)($_POST['codigo'] ?? 0);
  $porciones = max(0, (int)($_POST['porciones'] ?? 0));
  $toping    = $_POST['toping'] ?? 'Sin Toping';
  $notas     = trim($_POST['notas'] ?? '');
  $tipo_pago = $_POST['tipo_pago'] ?? 'Transferencia';
  $cambio    = ($tipo_pago === 'Efectivo' && $_POST['cambio'] !== '') ? number_format((float)$_POST['cambio'], 2, '.', '') : null;

  if (!in_array($toping, $topings, true)) {
    $toping = 'Sin Toping';
  }

  if ($porciones > 0 && $casa !== '' && $codigo > 0) {
    $total = $porciones * $PRECIO;
    $stmt = $conn->prepare("INSERT INTO rellenito_orders
      (period_id, sector, casa, codigo, porciones, toping, notas, tipo_pago, cambio, precio_unitario, total, estado, hora_registro)
      VALUES (?,?,?,?,?,?,?,?,?,?,?, 'Registrado', NOW())");
    $stmt->bind_param(
      'issiissssdd',
      $current_period_id,
      $sector,
      $casa,
      $codigo,
      $porciones,
      $toping,
      $notas,
      $tipo_pago,
      $cambio,
      $PRECIO,
      $total
    );
    if (!$stmt->execute()) {
      die('Error al registrar pedido: ' . $stmt->error);
    }
    $stmt->close();
  }

  header('Location: Rellenitos.php');
  exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'update_estado') {
  $id = (int)$_POST['id'];
  $nuevo = $_POST['estado'] === 'Despachado' ? 'Despachado' : 'Registrado';
  if ($nuevo === 'Despachado') {
    $stmt = $conn->prepare("UPDATE rellenito_orders SET estado='Despachado', hora_entrega=NOW() WHERE id=? AND period_id=?");
  } else {
    $stmt = $conn->prepare("UPDATE rellenito_orders SET estado='Registrado', hora_entrega=NULL WHERE id=? AND period_id=?");
  }
  $stmt->bind_param('ii', $id, $current_period_id);
  $stmt->execute();
  $stmt->close();
  header('Location: Rellenitos.php');
  exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'cerrar_periodo') {
  $conn->query("UPDATE rellenito_periods SET status='cerrado', closed_at=NOW() WHERE id=$current_period_id AND status='abierto'");
  open_new_rellenito_period($conn);
  header('Location: Rellenitos.php');
  exit;
}

$orders = $conn->query("SELECT * FROM rellenito_orders WHERE period_id=$current_period_id ORDER BY id DESC");
$totales = $conn->query(<<<SQL
  SELECT COALESCE(SUM(porciones),0) AS total_porciones,
         COALESCE(SUM(total),0) AS total_q
  FROM rellenito_orders
  WHERE period_id=$current_period_id
SQL)->fetch_assoc();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Venta de Rellenitos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/ui-enhancements.css">
  <style>
    body{background:#fdf6f0;color:#4a312a;}
    .card{background:#fff6ef;border:1px solid #f0d5c4;border-radius:18px;box-shadow:0 12px 28px rgba(171,108,71,.12);}
    .badge-soft{background:#ffe9d6;color:#b45309;font-weight:600;padding:.35rem .85rem;}
    .pill{border-radius:999px;}
    .table thead th{background:#fde7d9;border-bottom:1px solid #f5c9aa;color:#9a3412;font-weight:600;}
    .table td, .table th{border-color:#fce1cc;}
    .shadow-soft{box-shadow:0 14px 32px rgba(171,108,71,.18);}
    .btn-grad{background:linear-gradient(135deg,#ffb347,#ffcc33);border:none;color:#7c2d12;font-weight:600;}
    .btn-grad:hover{color:#7c2d12;background:linear-gradient(135deg,#ffa726,#ffbf24);}
    .text-muted-light{color:#a16243!important;}
    .form-control, .form-select{background:#fff;color:#4a312a;border-color:#f0d5c4;}
    .form-control:focus, .form-select:focus{border-color:#f59e0b;box-shadow:0 0 0 .25rem rgba(245,158,11,.25);}
    .form-control::placeholder{color:#c08457;}
    .table-responsive{border-radius:14px;overflow:hidden;}
  </style>
</head>
<body>
  <div id="pageLoader" class="page-loader">
    <div class="page-loader__spinner"></div>
    <div class="page-loader__progress"></div>
    <p class="page-loader__text">Listando pedidos de rellenitos...</p>
  </div>
  <div class="container py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
      <div>
        <h1 class="h3 mb-1">Rellenitos</h1>
        <span class="badge badge-soft pill">Periodo #<?= $current_period_id ?> · abierto desde <?= htmlspecialchars($period['opened_at']) ?></span>
      </div>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <a class="btn btn-outline-secondary pill" href="index.php"><i class="bi bi-house"></i> Ir al inicio</a>
        <a class="btn btn-outline-primary pill" href="Tortillas.php"><i class="bi bi-arrow-left"></i> Volver a Tortillas</a>
        <form method="post" onsubmit="return confirm('¿Cerrar el periodo actual? Se abrirá uno nuevo para los siguientes pedidos.');">
          <input type="hidden" name="action" value="cerrar_periodo">
          <button class="btn btn-outline-primary pill"><i class="bi bi-lock"></i> Cerrar pedidos</button>
        </form>
      </div>
    </div>

    <div class="card shadow-soft p-4 mb-4">
      <form method="post" class="row g-3" id="formPedido">
        <input type="hidden" name="action" value="add_order">
        <div class="col-md-3">
          <label class="form-label">Sector</label>
          <select name="sector" class="form-select" required>
            <?php foreach(['Arboleas','Montillana','Cardelas','Villamena','Paules','Almazora','Florenza','Navarra'] as $s): ?>
              <option value="<?= $s ?>"><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Casa</label>
          <input type="text" name="casa" class="form-control" maxlength="50" placeholder="Ej: B12" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Código</label>
          <input type="number" name="codigo" class="form-control" min="1" step="1" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Porciones</label>
          <input type="number" id="porciones" name="porciones" class="form-control" min="1" step="1" value="1" required>
        </div>
        <div class="col-md-2">
          <label class="form-label d-flex justify-content-between">

            <span>Total</span><small class="text-muted-light">(Q15 c/u)</small>
          </label>
          <input type="text" id="total" class="form-control" value="Q15.00" readonly>

        </div>
        <div class="col-md-3">
          <label class="form-label">Toping</label>
          <select name="toping" class="form-select" required>
            <?php foreach(['Azucar','Dulce de Leche','Chocolate','Mix','Sin Toping'] as $t): ?>
              <option value="<?= $t ?>"><?= $t ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Notas</label>
          <textarea name="notas" class="form-control" rows="2" placeholder="Comentarios..."></textarea>
        </div>
        <div class="col-md-3">
          <label class="form-label">Tipo de pago</label>
          <select name="tipo_pago" id="tipo_pago" class="form-select" required>
            <option value="Transferencia">Transferencia</option>
            <option value="Efectivo">Efectivo</option>
          </select>
        </div>
        <div class="col-md-3 d-none" id="grpCambio">
          <label class="form-label">Cambio (Q)</label>
          <input type="number" name="cambio" id="cambio" step="0.01" min="0" class="form-control" placeholder="Ej: 5.00">
        </div>
        <div class="col-12 d-flex gap-2 flex-wrap">
          <button class="btn btn-grad pill px-4" type="submit"><i class="bi bi-plus-lg"></i> Agregar pedido</button>
          <a href="Rellenitos.php" class="btn btn-outline-secondary pill"><i class="bi bi-arrow-clockwise"></i> Refrescar</a>
        </div>
      </form>
    </div>

    <div class="card p-3 shadow-soft">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h5 mb-0">Resumen del periodo</h2>
        <div class="text-end">
          <div class="small text-muted-light">Total porciones: <strong><?= (int)$totales['total_porciones'] ?></strong></div>
          <div class="small text-muted-light">Total Q: <strong>Q<?= number_format($totales['total_q'],2) ?></strong></div>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-sm align-middle table-hover">
          <thead>
            <tr>
              <th>#</th><th>Sector</th><th>Casa</th><th>Código</th>
              <th>Toping</th><th class="text-end">Porciones</th><th class="text-end">Total (Q)</th>
              <th>Pago</th><th class="text-end">Cambio</th>
              <th>Estado</th><th>Registro</th><th>Entrega</th><th></th>
            </tr>
          </thead>
          <tbody>
          <?php if ($orders && $orders->num_rows): while($r = $orders->fetch_assoc()): ?>
            <tr>
              <td><?= $r['id'] ?></td>
              <td><?= htmlspecialchars($r['sector']) ?></td>
              <td><?= htmlspecialchars($r['casa']) ?></td>
              <td><?= (int)$r['codigo'] ?></td>
              <td><?= htmlspecialchars($r['toping']) ?></td>
              <td class="text-end"><?= (int)$r['porciones'] ?></td>
              <td class="text-end">Q<?= number_format($r['total'],2) ?></td>
              <td><?= $r['tipo_pago'] ?></td>
              <td class="text-end"><?= $r['cambio']!==null ? 'Q'.number_format($r['cambio'],2) : '—' ?></td>
              <td>
                <span class="badge rounded-pill <?= $r['estado']==='Despachado' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?>">
                  <?= $r['estado'] ?>
                </span>
              </td>
              <td><small class="text-muted-light"><?= $r['hora_registro'] ?></small></td>
              <td><small class="text-muted-light"><?= $r['hora_entrega'] ?: '—' ?></small></td>
              <td>
                <form method="post" class="d-flex gap-1">
                  <input type="hidden" name="action" value="update_estado">
                  <input type="hidden" name="id" value="<?= $r['id'] ?>">
                  <?php if ($r['estado'] === 'Registrado'): ?>
                    <input type="hidden" name="estado" value="Despachado">
                    <button class="btn btn-sm btn-success pill" title="Marcar como Despachado">
                      <i class="bi bi-check2-circle"></i>
                    </button>
                  <?php else: ?>
                    <input type="hidden" name="estado" value="Registrado">
                    <button class="btn btn-sm btn-outline-secondary pill" title="Revertir a Registrado">
                      <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                  <?php endif; ?>
                </form>
              </td>
            </tr>
          <?php endwhile; else: ?>
            <tr><td colspan="13" class="text-center text-muted-light py-4">Sin pedidos aún en este periodo.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <p class="text-muted-light small mt-3">* Cada pedido corresponde únicamente al periodo abierto de rellenitos. Al presionar <strong>Cerrar pedidos</strong>, se archivará el actual y se iniciará uno nuevo.</p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/ui-enhancements.js"></script>
  <script>

    const precio = 15;

    const qty = document.getElementById('porciones');
    const total = document.getElementById('total');
    const tipoPago = document.getElementById('tipo_pago');
    const grpCambio = document.getElementById('grpCambio');
    const inpCambio = document.getElementById('cambio');

    function updateTotal(){
      const n = Math.max(0, parseInt(qty.value || 0, 10));
      total.value = 'Q' + (n * precio).toFixed(2);
    }

    function toggleCambio(){
      if (tipoPago.value === 'Efectivo') {
        grpCambio.classList.remove('d-none');
      } else {
        grpCambio.classList.add('d-none');
        inpCambio.value = '';
      }
    }

    qty.addEventListener('input', updateTotal);
    tipoPago.addEventListener('change', toggleCambio);
    updateTotal();
    toggleCambio();
  </script>
</body>
</html>
