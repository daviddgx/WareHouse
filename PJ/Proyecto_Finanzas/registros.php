<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/schema_finanzas.php';
require_once __DIR__ . '/auth_finanzas.php';

finanzas_handle_auth_post();
finanzas_require_auth('Proyecto Finanzas - Registros');

$conn = db();
ensure_finanzas_schema($conn);

$successMessage = null;
$errorMessage = null;
$formCategoria = [
  'nombre_categoria' => '',
  'tipo_categoria' => '',
];
$formMovimiento = [
  'tipo_movimiento' => 'Ingreso',
  'categoria_id' => 0,
  'descripcion' => '',
  'monto' => '',
  'fecha_movimiento' => date('Y-m-d'),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $action = (string)($_POST['action'] ?? '');
    $formCategoria['nombre_categoria'] = trim((string)($_POST['nombre_categoria'] ?? ''));
    $formCategoria['tipo_categoria'] = (string)($_POST['tipo_categoria'] ?? '');
    $formMovimiento['tipo_movimiento'] = (string)($_POST['tipo_movimiento'] ?? $formMovimiento['tipo_movimiento']);
    $formMovimiento['categoria_id'] = (int)($_POST['categoria_id'] ?? 0);
    $formMovimiento['descripcion'] = trim((string)($_POST['descripcion'] ?? ''));
    $formMovimiento['monto'] = trim((string)($_POST['monto'] ?? ''));
    $formMovimiento['fecha_movimiento'] = (string)($_POST['fecha_movimiento'] ?? $formMovimiento['fecha_movimiento']);

    if ($action === 'crear_categoria') {
      $nombre = $formCategoria['nombre_categoria'];
      $tipo = $formCategoria['tipo_categoria'];
      if ($nombre === '' || !in_array($tipo, ['Ingreso', 'Gasto'], true)) {
        throw new RuntimeException('Completa correctamente el nombre y tipo de categoría.');
      }

      $stmtExiste = $conn->prepare('SELECT id FROM finanzas_categorias WHERE nombre = ? AND tipo = ? LIMIT 1');
      $stmtExiste->bind_param('ss', $nombre, $tipo);
      $stmtExiste->execute();
      $categoriaExistente = $stmtExiste->get_result()->fetch_assoc();
      $stmtExiste->close();

      if ($categoriaExistente) {
        throw new RuntimeException('Ya existe una categoría con ese nombre para el tipo seleccionado.');
      }

      $stmt = $conn->prepare('INSERT INTO finanzas_categorias (nombre, tipo, estado) VALUES (?, ?, "Activa")');
      $stmt->bind_param('ss', $nombre, $tipo);
      $stmt->execute();
      $stmt->close();

      $formCategoria = [
        'nombre_categoria' => '',
        'tipo_categoria' => '',
      ];
      $successMessage = 'Categoría registrada correctamente.';
    }

    if ($action === 'crear_movimiento') {
      $tipo = $formMovimiento['tipo_movimiento'];
      $categoriaId = $formMovimiento['categoria_id'];
      $descripcion = $formMovimiento['descripcion'];
      $monto = (float)$formMovimiento['monto'];
      $fecha = $formMovimiento['fecha_movimiento'];

      if (
        !in_array($tipo, ['Ingreso', 'Gasto'], true) ||
        $categoriaId <= 0 ||
        $descripcion === '' ||
        $monto <= 0 ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)
      ) {
        throw new RuntimeException('Verifica los datos del movimiento antes de guardar.');
      }

      $stmtCategoria = $conn->prepare('SELECT id FROM finanzas_categorias WHERE id = ? AND tipo = ? AND estado = "Activa" LIMIT 1');
      $stmtCategoria->bind_param('is', $categoriaId, $tipo);
      $stmtCategoria->execute();
      $categoriaValida = $stmtCategoria->get_result()->fetch_assoc();
      $stmtCategoria->close();

      if (!$categoriaValida) {
        throw new RuntimeException('La categoría seleccionada no corresponde al tipo elegido o está inactiva.');
      }

      $stmt = $conn->prepare('INSERT INTO finanzas_movimientos (categoria_id, tipo, descripcion, monto, fecha_movimiento) VALUES (?, ?, ?, ?, ?)');
      $stmt->bind_param('issds', $categoriaId, $tipo, $descripcion, $monto, $fecha);
      $stmt->execute();
      $stmt->close();

      $formMovimiento = [
        'tipo_movimiento' => $tipo,
        'categoria_id' => 0,
        'descripcion' => '',
        'monto' => '',
        'fecha_movimiento' => date('Y-m-d'),
      ];
      $successMessage = 'Movimiento guardado exitosamente.';
    }
  } catch (Throwable $e) {
    $errorMessage = $e->getMessage();
  }
}

$categoriasIngreso = [];
$categoriasGasto = [];
$catResult = $conn->query('SELECT id, nombre, tipo FROM finanzas_categorias WHERE estado = "Activa" ORDER BY tipo, nombre');
while ($row = $catResult->fetch_assoc()) {
  if ($row['tipo'] === 'Ingreso') {
    $categoriasIngreso[] = $row;
  } else {
    $categoriasGasto[] = $row;
  }
}
$catResult->free();

$resumenRegistros = [
  'categorias_activas' => count($categoriasIngreso) + count($categoriasGasto),
  'movimientos_hoy' => 0,
  'movimientos_mes' => 0,
];
$resumenQuery = $conn->query(
  "SELECT
      SUM(CASE WHEN fecha_movimiento = CURDATE() THEN 1 ELSE 0 END) AS movimientos_hoy,
      SUM(CASE WHEN DATE_FORMAT(fecha_movimiento, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') THEN 1 ELSE 0 END) AS movimientos_mes
   FROM finanzas_movimientos"
);
if ($resumenQuery && ($rowResumen = $resumenQuery->fetch_assoc())) {
  $resumenRegistros['movimientos_hoy'] = (int)($rowResumen['movimientos_hoy'] ?? 0);
  $resumenRegistros['movimientos_mes'] = (int)($rowResumen['movimientos_mes'] ?? 0);
  $resumenQuery->free();
}

$movimientosRecientes = [];
$movimientosRecientesQuery = $conn->query(
  "SELECT m.tipo, m.descripcion, m.monto, m.fecha_movimiento, c.nombre AS categoria
   FROM finanzas_movimientos m
   INNER JOIN finanzas_categorias c ON c.id = m.categoria_id
   ORDER BY m.fecha_movimiento DESC, m.id DESC
   LIMIT 5"
);
if ($movimientosRecientesQuery) {
  while ($row = $movimientosRecientesQuery->fetch_assoc()) {
    $movimientosRecientes[] = $row;
  }
  $movimientosRecientesQuery->free();
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Proyecto Finanzas - Registros</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background: #f3f6fb; transition: background .25s ease, color .25s ease; }
    .top-actions, .card, .mini-stat { transition: transform .25s ease, box-shadow .25s ease, background-color .25s ease, color .25s ease; }
    .animate-entry { opacity: 0; transform: translateY(20px); animation: fadeSlideIn .65s ease forwards; animation-delay: var(--delay, 0ms); }
    .mini-stat { border: 0; border-radius: 1rem; }
    .mini-stat:hover, .card:hover { transform: translateY(-4px); box-shadow: 0 1rem 2rem rgba(15, 23, 42, 0.10) !important; }
    .helper-chip { display: inline-flex; align-items: center; gap: .35rem; border-radius: 999px; padding: .35rem .75rem; background: rgba(13, 110, 253, 0.08); color: #0d6efd; font-size: .85rem; }
    @keyframes fadeSlideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    body.dark-mode { background: #0f172a; color: #e2e8f0; }
    body.dark-mode .card { background-color: #1e293b !important; color: #e2e8f0 !important; }
    body.dark-mode .text-muted { color: #94a3b8 !important; }
    body.dark-mode .helper-chip { background: rgba(96, 165, 250, 0.12); color: #93c5fd; }
    @media (prefers-reduced-motion: reduce) {
      .animate-entry, .top-actions, .card, .mini-stat {
        animation: none !important;
        transition: none !important;
        transform: none !important;
        opacity: 1 !important;
      }
    }
  </style>
</head>
<body>
<div class="container py-3 py-md-4">
  <div class="d-flex flex-wrap justify-content-between gap-2 mb-3 top-actions animate-entry" style="--delay: 40ms;">
    <div class="d-flex gap-2 flex-wrap">
      <a href="index.php" class="btn btn-primary btn-sm"><i class="bi bi-bar-chart-line"></i> Ir a detalles</a>
      <a href="../index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Menú</a>
    </div>
    <div class="d-flex gap-2">
      <button id="toggleDark" class="btn btn-outline-dark btn-sm"><i class="bi bi-moon-stars"></i> Modo oscuro</button>
      <form method="post">
        <input type="hidden" name="action" value="cerrar_sesion_finanzas">
        <input type="hidden" name="redirect_to" value="registros.php">
        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right"></i></button>
      </form>
    </div>
  </div>

  <h1 class="h4 animate-entry" style="--delay: 80ms;">Página de registros</h1>
  <p class="text-muted animate-entry" style="--delay: 120ms;">Ingresa categorías, registra movimientos y consulta un resumen rápido del módulo.</p>

  <div class="row g-3 mb-3">
    <div class="col-12 col-md-4 animate-entry" style="--delay: 160ms;">
      <div class="card shadow-sm mini-stat">
        <div class="card-body">
          <p class="text-muted mb-1">Categorías activas</p>
          <h2 class="h3 mb-0"><?= (int)$resumenRegistros['categorias_activas'] ?></h2>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4 animate-entry" style="--delay: 220ms;">
      <div class="card shadow-sm mini-stat">
        <div class="card-body">
          <p class="text-muted mb-1">Movimientos hoy</p>
          <h2 class="h3 mb-0"><?= (int)$resumenRegistros['movimientos_hoy'] ?></h2>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4 animate-entry" style="--delay: 280ms;">
      <div class="card shadow-sm mini-stat">
        <div class="card-body">
          <p class="text-muted mb-1">Movimientos este mes</p>
          <h2 class="h3 mb-0"><?= (int)$resumenRegistros['movimientos_mes'] ?></h2>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-5 animate-entry" style="--delay: 340ms;">
      <div class="card shadow-sm mb-3">
        <div class="card-header bg-white"><strong>Nueva categoría</strong></div>
        <div class="card-body">
          <form method="post" class="needs-validation" novalidate>
            <input type="hidden" name="action" value="crear_categoria">
            <div class="mb-3">
              <label class="form-label">Nombre</label>
              <input type="text" class="form-control" name="nombre_categoria" value="<?= htmlspecialchars($formCategoria['nombre_categoria']) ?>" required maxlength="120" placeholder="Ej. Freelance, Bonos, Alquiler...">
            </div>
            <div class="mb-3">
              <label class="form-label">Tipo</label>
              <select name="tipo_categoria" class="form-select" required>
                <option value="">Selecciona...</option>
                <option value="Ingreso" <?= $formCategoria['tipo_categoria'] === 'Ingreso' ? 'selected' : '' ?>>Ingreso</option>
                <option value="Gasto" <?= $formCategoria['tipo_categoria'] === 'Gasto' ? 'selected' : '' ?>>Gasto</option>
              </select>
            </div>
            <button class="btn btn-primary w-100" type="submit">Guardar categoría</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-7 animate-entry" style="--delay: 400ms;">
      <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
          <strong>Registro rápido de movimiento</strong>
          <span id="resumenCategorias" class="helper-chip"><i class="bi bi-tags"></i> Cargando categorías...</span>
        </div>
        <div class="card-body">
          <form method="post" class="needs-validation" novalidate>
            <input type="hidden" name="action" value="crear_movimiento">
            <div class="row g-2">
              <div class="col-12 col-md-6">
                <label class="form-label">Tipo</label>
                <select id="tipo_movimiento" name="tipo_movimiento" class="form-select" required>
                  <option value="Ingreso" <?= $formMovimiento['tipo_movimiento'] === 'Ingreso' ? 'selected' : '' ?>>Ingreso</option>
                  <option value="Gasto" <?= $formMovimiento['tipo_movimiento'] === 'Gasto' ? 'selected' : '' ?>>Gasto</option>
                </select>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Categoría</label>
                <select id="categoria_id" name="categoria_id" class="form-select" required></select>
                <div id="ayudaCategorias" class="form-text">Selecciona el tipo para cargar categorías disponibles.</div>
              </div>
              <div class="col-12">
                <label class="form-label">Descripción</label>
                <input type="text" class="form-control" name="descripcion" value="<?= htmlspecialchars($formMovimiento['descripcion']) ?>" required maxlength="255" placeholder="Describe brevemente el movimiento">
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Monto (Q)</label>
                <input type="number" class="form-control" name="monto" value="<?= htmlspecialchars($formMovimiento['monto']) ?>" required step="0.01" min="0.01" inputmode="decimal">
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Fecha</label>
                <input type="date" class="form-control" name="fecha_movimiento" value="<?= htmlspecialchars($formMovimiento['fecha_movimiento']) ?>" required>
              </div>
            </div>
            <button id="guardarMovimientoBtn" class="btn btn-success w-100 mt-3" type="submit">Guardar movimiento</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 animate-entry" style="--delay: 460ms;">
      <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>Últimos movimientos registrados</strong></div>
        <div class="card-body">
          <?php if (!empty($movimientosRecientes)): ?>
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead>
                  <tr><th>Fecha</th><th>Tipo</th><th>Categoría</th><th>Descripción</th><th>Monto</th></tr>
                </thead>
                <tbody>
                <?php foreach ($movimientosRecientes as $movimientoReciente): ?>
                  <tr>
                    <td><?= htmlspecialchars($movimientoReciente['fecha_movimiento']) ?></td>
                    <td><span class="badge <?= $movimientoReciente['tipo'] === 'Ingreso' ? 'text-bg-success' : 'text-bg-danger' ?>"><?= htmlspecialchars($movimientoReciente['tipo']) ?></span></td>
                    <td><?= htmlspecialchars($movimientoReciente['categoria']) ?></td>
                    <td><?= htmlspecialchars($movimientoReciente['descripcion']) ?></td>
                    <td class="fw-semibold">Q <?= number_format((float)$movimientoReciente['monto'], 2) ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="text-muted mb-0">Aún no hay movimientos registrados.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  const categoriasIngreso = <?= json_encode($categoriasIngreso, JSON_UNESCAPED_UNICODE) ?>;
  const categoriasGasto = <?= json_encode($categoriasGasto, JSON_UNESCAPED_UNICODE) ?>;
  const categoriaSeleccionadaInicial = <?= (int)$formMovimiento['categoria_id'] ?>;
  const tipoMovimiento = document.getElementById('tipo_movimiento');
  const categoriaSelect = document.getElementById('categoria_id');
  const ayudaCategorias = document.getElementById('ayudaCategorias');
  const resumenCategorias = document.getElementById('resumenCategorias');
  const guardarMovimientoBtn = document.getElementById('guardarMovimientoBtn');

  function renderCategorias() {
    const tipo = tipoMovimiento.value;
    const categorias = tipo === 'Ingreso' ? categoriasIngreso : categoriasGasto;
    const selectedActual = Number(categoriaSelect.value || categoriaSeleccionadaInicial || 0);

    categoriaSelect.innerHTML = '';

    if (!categorias.length) {
      const option = document.createElement('option');
      option.value = '';
      option.textContent = 'No hay categorías activas para este tipo';
      categoriaSelect.appendChild(option);
      categoriaSelect.disabled = true;
      guardarMovimientoBtn.disabled = true;
      ayudaCategorias.textContent = 'Primero crea una categoría activa para este tipo.';
      resumenCategorias.innerHTML = '<i class="bi bi-tags"></i> 0 categorías disponibles';
      return;
    }

    categoriaSelect.disabled = false;
    guardarMovimientoBtn.disabled = false;
    ayudaCategorias.textContent = `Tienes ${categorias.length} categoría(s) disponibles para ${tipo.toLowerCase()}.`;
    resumenCategorias.innerHTML = `<i class="bi bi-tags"></i> ${categorias.length} categoría(s) de ${tipo.toLowerCase()}`;

    categorias.forEach(cat => {
      const option = document.createElement('option');
      option.value = cat.id;
      option.textContent = cat.nombre;
      option.selected = Number(cat.id) === selectedActual;
      categoriaSelect.appendChild(option);
    });
  }

  tipoMovimiento.addEventListener('change', renderCategorias);
  renderCategorias();

  (() => {
    'use strict';
    document.querySelectorAll('.needs-validation').forEach(form => {
      form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      });
    });
  })();

  <?php if ($successMessage): ?>
  Swal.fire({ icon: 'success', title: 'Éxito', text: <?= json_encode($successMessage, JSON_UNESCAPED_UNICODE) ?> });
  <?php endif; ?>
  <?php if ($errorMessage): ?>
  Swal.fire({ icon: 'error', title: 'Error', text: <?= json_encode($errorMessage, JSON_UNESCAPED_UNICODE) ?> });
  <?php endif; ?>

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