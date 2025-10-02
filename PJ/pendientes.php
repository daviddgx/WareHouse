<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/schema_tortillas.php';

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
  ensure_tortilla_schema($conn);
} catch (Throwable $e) {
  http_response_code(500);

  echo '<p>Error al preparar la base de datos (tortillas): ' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
  exit;
}

try {
  ensure_rellenitos_schema($conn);
} catch (Throwable $e) {
  http_response_code(500);
  echo '<p>Error al preparar la base de datos (rellenitos): ' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
  exit;
}

function get_open_tortilla_period(mysqli $c) {

  $res = $c->query("SELECT * FROM tortilla_periods WHERE status='abierto' ORDER BY id DESC LIMIT 1");
  return $res && $res->num_rows ? $res->fetch_assoc() : null;
}
function open_new_tortilla_period(mysqli $c) {

  $c->query("INSERT INTO tortilla_periods(opened_at,status) VALUES (NOW(),'abierto')");
  return $c->insert_id;
}


function get_open_rellenito_period(mysqli $c) {
  $res = $c->query("SELECT * FROM rellenito_periods WHERE status='abierto' ORDER BY id DESC LIMIT 1");
  return $res && $res->num_rows ? $res->fetch_assoc() : null;
}

function open_new_rellenito_period(mysqli $c) {
  $c->query("INSERT INTO rellenito_periods(opened_at,status) VALUES (NOW(),'abierto')");
  return $c->insert_id;
}

$tortilla_period = get_open_tortilla_period($conn);
if (!$tortilla_period) {
  $period_id = open_new_tortilla_period($conn);
  $tortilla_period = $conn->query("SELECT * FROM tortilla_periods WHERE id=$period_id")->fetch_assoc();
}
$tortilla_period_id = (int)$tortilla_period['id'];

$rellenito_period = get_open_rellenito_period($conn);
if (!$rellenito_period) {
  $period_id = open_new_rellenito_period($conn);
  $rellenito_period = $conn->query("SELECT * FROM rellenito_periods WHERE id=$period_id")->fetch_assoc();
}
$rellenito_period_id = (int)$rellenito_period['id'];

$pending_tortilla_orders = [];
$pending_tortilla_totals = ['total_tortillas' => 0, 'total_q' => 0.00];

$pending_stmt = $conn->prepare("SELECT id, sector, casa, codigo, tortillas, notas, tipo_pago, cambio, total, hora_registro FROM tortilla_orders WHERE period_id=? AND estado='Registrado' ORDER BY hora_registro ASC");
$pending_stmt->bind_param('i', $tortilla_period_id);
$pending_stmt->execute();
$pending_tortilla_orders = $pending_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$pending_stmt->close();

$totals_stmt = $conn->prepare("SELECT COALESCE(SUM(tortillas),0) AS total_tortillas, COALESCE(SUM(total),0) AS total_q FROM tortilla_orders WHERE period_id=? AND estado='Registrado'");
$totals_stmt->bind_param('i', $tortilla_period_id);
$totals_stmt->execute();
$pending_tortilla_totals = $totals_stmt->get_result()->fetch_assoc() ?: $pending_tortilla_totals;
$totals_stmt->close();

$pending_rellenito_orders = [];
$pending_rellenito_totals = ['total_porciones' => 0, 'total_q' => 0.00];

$rellenito_stmt = $conn->prepare("SELECT id, sector, casa, codigo, porciones, toping, notas, tipo_pago, cambio, total, hora_registro FROM rellenito_orders WHERE period_id=? AND estado='Registrado' ORDER BY hora_registro ASC");
$rellenito_stmt->bind_param('i', $rellenito_period_id);
$rellenito_stmt->execute();
$pending_rellenito_orders = $rellenito_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$rellenito_stmt->close();

$rellenito_totals_stmt = $conn->prepare("SELECT COALESCE(SUM(porciones),0) AS total_porciones, COALESCE(SUM(total),0) AS total_q FROM rellenito_orders WHERE period_id=? AND estado='Registrado'");
$rellenito_totals_stmt->bind_param('i', $rellenito_period_id);
$rellenito_totals_stmt->execute();
$pending_rellenito_totals = $rellenito_totals_stmt->get_result()->fetch_assoc() ?: $pending_rellenito_totals;
$rellenito_totals_stmt->close();


$ultima_actualizacion = date('d/m/Y H:i:s');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">

  <title>Pedidos pendientes · Todos los productos</title>

  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/ui-enhancements.css">
  <style>
    body{background:#f4f6fb;color:#1f2933;}
    .pill{border-radius:999px;}
    .text-muted-light{color:#64748b!important;}
    .shadow-soft{box-shadow:0 14px 32px rgba(15,23,42,.12);}

    .summary-card{background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:1.5rem;}
    .summary-card h2{font-size:.75rem;letter-spacing:.08em;text-transform:uppercase;color:#64748b;margin-bottom:.25rem;}
    .summary-card .summary-value{font-size:2rem;font-weight:600;color:#1f2933;margin-bottom:.35rem;}
    .summary-card .summary-amount{font-weight:600;color:#0f172a;}
    .badge-soft{background:#eef2ff;color:#3730a3;font-weight:600;padding:.35rem .85rem;border-radius:999px;}
    .badge-payment{background:#ecfdf5;color:#047857;padding:.35rem .8rem;border-radius:999px;font-weight:500;}

    .pending-card{border:1px solid #e2e8f0;border-radius:18px;background:#fff;padding:1.25rem 1.5rem;box-shadow:0 12px 28px rgba(15,23,42,.08);}
    .pending-card + .pending-card{margin-top:1rem;}
    .order-grid{display:flex;flex-wrap:wrap;gap:.75rem 1.5rem;}
    .order-grid span.value{font-weight:600;color:#1f2933;display:block;font-size:1rem;}
    .order-grid span.label{display:block;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-bottom:.1rem;}

    .product-section{margin-bottom:3rem;}
    .product-section:last-of-type{margin-bottom:1.5rem;}
    .empty-state{border:1px dashed #cbd5f5;border-radius:18px;background:#fff;padding:2.5rem 1.5rem;}
    @media (max-width: 575.98px){
      .pending-card{padding:1.1rem;}
      .order-grid{flex-direction:column;gap:.65rem;}
      .summary-card{padding:1.25rem;}
      .summary-card .summary-value{font-size:1.6rem;}

    }
  </style>
</head>
<body>
  <div id="pageLoader" class="page-loader">
    <div class="page-loader__spinner"></div>
    <div class="page-loader__progress"></div>
    <p class="page-loader__text">Sincronizando pedidos...</p>
  </div>
  <div class="container py-4">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
      <div>
        <h1 class="h4 mb-1">Pedidos pendientes</h1>
        <div class="text-muted-light">Monitorea en tiempo real los pedidos activos de todos los productos.</div>

        <div class="text-muted-light small">Última actualización: <?= $ultima_actualizacion ?> · Refresco automático en <span id="refreshCountdown">30</span>s</div>
      </div>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <a class="btn btn-outline-secondary pill" href="index.php"><i class="bi bi-house"></i> Ir al inicio</a>
        <a class="btn btn-outline-secondary pill" href="Tortillas.php"><i class="bi bi-arrow-left-circle"></i> Volver</a>
        <button class="btn btn-outline-primary pill" type="button" id="refreshNow"><i class="bi bi-arrow-clockwise"></i> Actualizar ahora</button>
      </div>
    </div>


    <div class="d-flex flex-wrap gap-2 mb-4">
      <span class="badge-soft pill">Tortillas · Periodo #<?= $tortilla_period_id ?> · abierto desde <?= htmlspecialchars($tortilla_period['opened_at']) ?></span>
      <span class="badge-soft pill">Rellenitos · Periodo #<?= $rellenito_period_id ?> · abierto desde <?= htmlspecialchars($rellenito_period['opened_at']) ?></span>
    </div>

    <div class="row g-3 mb-5">
      <div class="col-md-6">
        <div class="summary-card shadow-soft h-100">
          <h2>Totales pendientes · Tortillas</h2>
          <div class="summary-value"><?= (int)$pending_tortilla_totals['total_tortillas'] ?></div>
          <div class="text-muted-light mb-2">tortillas pendientes</div>
          <div class="summary-amount">Total: Q<?= number_format($pending_tortilla_totals['total_q'], 2) ?></div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="summary-card shadow-soft h-100">
          <h2>Totales pendientes · Rellenitos</h2>
          <div class="summary-value"><?= (int)$pending_rellenito_totals['total_porciones'] ?></div>
          <div class="text-muted-light mb-2">porciones pendientes</div>
          <div class="summary-amount">Total: Q<?= number_format($pending_rellenito_totals['total_q'], 2) ?></div>

        </div>
      </div>
    </div>


    <section class="product-section">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
          <h2 class="h5 mb-1">Tortillas de Harina</h2>
          <div class="text-muted-light small">Pedidos registrados en espera de despacho.</div>
        </div>
        <span class="badge-soft pill">Pendientes: <?= count($pending_tortilla_orders) ?></span>
      </div>
      <?php if ($pending_tortilla_orders): ?>
        <?php foreach ($pending_tortilla_orders as $order): ?>
          <div class="pending-card">
            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-2">
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge-soft">Pedido #<?= $order['id'] ?></span>
                <span class="fw-semibold fs-5"><?= htmlspecialchars($order['sector']) ?> · <?= htmlspecialchars($order['casa']) ?></span>
              </div>
              <span class="text-muted-light"><i class="bi bi-clock"></i> <?= htmlspecialchars($order['hora_registro']) ?></span>
            </div>
            <div class="order-grid mb-2">
              <div>
                <span class="label">Cantidad</span>
                <span class="value"><?= (int)$order['tortillas'] ?> tortillas</span>
              </div>
              <div>
                <span class="label">Total</span>
                <span class="value">Q<?= number_format($order['total'], 2) ?></span>
              </div>
              <div>
                <span class="label">Código</span>
                <span class="value"><?= (int)$order['codigo'] ?></span>
              </div>
              <div>
                <span class="label">Pago</span>
                <span class="value">
                  <span class="badge-payment"><i class="bi bi-wallet2"></i> <?= htmlspecialchars($order['tipo_pago']) ?></span>
                  <?php if ($order['cambio'] !== null): ?>
                    <small class="d-block text-muted-light">Cambio: Q<?= number_format($order['cambio'], 2) ?></small>
                  <?php endif; ?>
                </span>
              </div>
            </div>
            <?php if (trim((string)$order['notas']) !== ''): ?>
              <div class="pt-2 border-top border-light-subtle">
                <span class="label d-block mb-1">Notas</span>
                <p class="mb-0 text-muted-light"><?= nl2br(htmlspecialchars($order['notas'])) ?></p>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state text-center text-muted-light">
          <i class="bi bi-emoji-smile" style="font-size:2.5rem;"></i>
          <p class="mt-3 mb-0">No hay pedidos de tortillas pendientes por despachar.</p>
        </div>
      <?php endif; ?>
    </section>

    <section class="product-section">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
          <h2 class="h5 mb-1">Rellenitos</h2>
          <div class="text-muted-light small">Pedidos con topping en espera de entrega.</div>
        </div>
        <span class="badge-soft pill">Pendientes: <?= count($pending_rellenito_orders) ?></span>
      </div>
      <?php if ($pending_rellenito_orders): ?>
        <?php foreach ($pending_rellenito_orders as $order): ?>
          <div class="pending-card">
            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-2">
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge-soft">Pedido #<?= $order['id'] ?></span>
                <span class="fw-semibold fs-5"><?= htmlspecialchars($order['sector']) ?> · <?= htmlspecialchars($order['casa']) ?></span>
              </div>
              <span class="text-muted-light"><i class="bi bi-clock"></i> <?= htmlspecialchars($order['hora_registro']) ?></span>
            </div>
            <div class="order-grid mb-2">
              <div>
                <span class="label">Porciones</span>
                <span class="value"><?= (int)$order['porciones'] ?> porciones</span>
              </div>
              <div>
                <span class="label">Toping</span>
                <span class="value"><?= htmlspecialchars($order['toping']) ?></span>
              </div>
              <div>
                <span class="label">Total</span>
                <span class="value">Q<?= number_format($order['total'], 2) ?></span>
              </div>
              <div>
                <span class="label">Código</span>
                <span class="value"><?= (int)$order['codigo'] ?></span>
              </div>
              <div>
                <span class="label">Pago</span>
                <span class="value">
                  <span class="badge-payment"><i class="bi bi-wallet2"></i> <?= htmlspecialchars($order['tipo_pago']) ?></span>
                  <?php if ($order['cambio'] !== null): ?>
                    <small class="d-block text-muted-light">Cambio: Q<?= number_format($order['cambio'], 2) ?></small>
                  <?php endif; ?>
                </span>
              </div>
            </div>
            <?php if (trim((string)$order['notas']) !== ''): ?>
              <div class="pt-2 border-top border-light-subtle">
                <span class="label d-block mb-1">Notas</span>
                <p class="mb-0 text-muted-light"><?= nl2br(htmlspecialchars($order['notas'])) ?></p>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state text-center text-muted-light">
          <i class="bi bi-emoji-smile" style="font-size:2.5rem;"></i>
          <p class="mt-3 mb-0">No hay pedidos de rellenitos pendientes por despachar.</p>
        </div>
      <?php endif; ?>
    </section>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/ui-enhancements.js"></script>
  <script>
    const refreshSeconds = 30;
    const countdownEl = document.getElementById('refreshCountdown');
    const refreshBtn = document.getElementById('refreshNow');
    let remaining = refreshSeconds;

    if (countdownEl) {
      countdownEl.textContent = remaining.toString();
    }

    const timer = setInterval(() => {
      remaining -= 1;
      if (remaining <= 0) {
        clearInterval(timer);
        window.location.reload();
      } else if (countdownEl) {
        countdownEl.textContent = remaining.toString();
      }
    }, 1000);

    if (refreshBtn) {
      refreshBtn.addEventListener('click', () => window.location.reload());
    }
  </script>
</body>
</html>
