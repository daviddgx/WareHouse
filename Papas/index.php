<?php
session_start();
if (!isset($_SESSION['pedidos'])) {
    $_SESSION['pedidos'] = [];
}

// Helper para formatear diferencias de tiempo de forma amigable
function pp_tiempo_humano($seconds) {
    $seconds = (int) max(0, $seconds);
    $h = intdiv($seconds, 3600);
    $m = intdiv($seconds % 3600, 60);
    $s = $seconds % 60;
    if ($h > 0) return sprintf('%dh %02dm %02ds', $h, $m, $s);
    if ($m > 0) return sprintf('%dm %02ds', $m, $s);
    return sprintf('%ds', $s);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion']) && $_POST['accion'] === 'registrar') {
        $pedido = [
            'condominio' => $_POST['condominio'] ?? '',
            'sector' => $_POST['sector'] ?? '',
            'casa' => $_POST['casa'] ?? '',
            'codigo' => $_POST['codigo'] ?? '',
            'porciones' => $_POST['porciones'] ?? '',
            'tipo_pago' => $_POST['tipo_pago'] ?? '',
            'cambio' => $_POST['cambio'] ?? '',
            'status' => 'Pendiente',
            'created_at' => time(),
            'attended_at' => null
        ];
        $_SESSION['pedidos'][] = $pedido;
        $_SESSION['flash'] = ['type' => 'success', 'msg' => '¡Pedido registrado! 🧾✨'];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_POST['accion']) && $_POST['accion'] === 'atendido') {
        $index = $_POST['index'] ?? '';
        if ($index !== '' && isset($_SESSION['pedidos'][$index])) {
            // Marcar como atendido y conservar registro
            if (!isset($_SESSION['pedidos'][$index]['created_at'])) {
                $_SESSION['pedidos'][$index]['created_at'] = time();
            }
            $_SESSION['pedidos'][$index]['status'] = 'Atendido';
            $_SESSION['pedidos'][$index]['attended_at'] = time();
        }
        $_SESSION['flash'] = ['type' => 'info', 'msg' => 'Pedido marcado como atendido ✅'];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro de pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
      :root {
        --pp-primary: #ff7a59; /* papaya-ish */
        --pp-secondary: #7a5cff; /* playful purple */
        --pp-accent: #00c2a8; /* mint */
      }
      body {
        background: radial-gradient(1200px 600px at 10% -10%, rgba(255,122,89,.18), transparent 60%),
                    radial-gradient(900px 500px at 110% 0%, rgba(122,92,255,.18), transparent 60%),
                    linear-gradient(180deg, #fff, #fff);
        min-height: 100vh;
      }
      .navbar-brand {
        font-weight: 800;
        letter-spacing: .5px;
      }
      .card-hero {
        border: 0;
        box-shadow: 0 1rem 2.5rem rgba(0,0,0,.08);
        overflow: hidden;
      }
      .card-hero .card-header {
        background: linear-gradient(135deg, var(--pp-primary), var(--pp-secondary));
        color: #fff;
      }
      .btn-gradient {
        background: linear-gradient(135deg, var(--pp-primary), var(--pp-secondary));
        border: 0;
      }
      .btn-gradient:hover { filter: brightness(1.05); }
      .form-floating > label > .hint { opacity: .7; font-weight: 500; }
      .table thead th { position: sticky; top: 0; background: #fff; z-index: 1; }
      .badge-pill { border-radius: 50rem; padding: .5em .75em; }
      .floating-select select.form-select { padding-top: 1.625rem; padding-bottom: .625rem; }
      .floating-select label { opacity: .65; }

      /* Page loader */
      #pageLoader {
        position: fixed; inset: 0; z-index: 2000;
        display: flex; align-items: center; justify-content: center;
        background:
          radial-gradient(900px 500px at 20% 0%, rgba(255,122,89,.12), transparent 60%),
          radial-gradient(900px 500px at 80% 0%, rgba(122,92,255,.12), transparent 60%),
          rgba(255,255,255,.95);
        transition: opacity .35s ease;
      }
      #pageLoader.hidden { opacity: 0; pointer-events: none; }
      .loader-box { text-align: center; }
      .loader-icon {
        font-size: 3rem;
        display: inline-block;
        animation: loader-spin 1.1s linear infinite;
        filter: drop-shadow(0 6px 16px rgba(0,0,0,.12));
      }
      .loader-text { margin-top: .5rem; color: #444; font-weight: 600; letter-spacing: .3px; }
      @keyframes loader-spin { from { transform: rotate(0deg) } to { transform: rotate(360deg) } }
    </style>
</head>
<body>
    <!-- Loader overlay -->
    <div id="pageLoader" aria-live="polite" aria-busy="true">
      <div class="loader-box">
        <div class="loader-icon">⏳</div>
        <div class="loader-text">Procesando...</div>
      </div>
    </div>
    <nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm">
      <div class="container-lg">
        <a class="navbar-brand d-flex align-items-center gap-2" href="#">
          <span class="fs-4">🥔</span>
          <span>Pedidos Papas</span>
        </a>
      </div>
    </nav>

    <main class="container-lg py-4 py-lg-5">
    <?php if (!empty($_SESSION['flash'])): $f = $_SESSION['flash']; unset($_SESSION['flash']); ?>
      <div class="alert alert-<?= htmlspecialchars($f['type']) ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($f['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
    <?php endif; ?>

    <div class="card card-hero mb-4">
      <div class="card-header py-3">
        <h1 class="h4 mb-0">
          <i class="bi bi-clipboard2-plus me-2"></i>
          Registrar pedido
        </h1>
      </div>
      <div class="card-body">
    <form method="post" class="row g-3">
        <input type="hidden" name="accion" value="registrar">
        <div class="col-md-6">
          <div class="form-floating floating-select">
            <select name="condominio" id="condominio" class="form-select" required aria-label="Condominio">
              <option value="" selected>Seleccione...</option>
              <option value="Almeria">Almeria</option>
              <option value="Florenza">Florenza</option>
              <option value="Navarra">Navarra</option>
            </select>
            <label for="condominio"><span class="hint">Condominio</span> 🏘️</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating floating-select">
            <select name="sector" id="sector" class="form-select" required disabled aria-label="Sector"></select>
            <label for="sector"><span class="hint">Sector</span> 🧭</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating">
            <input type="text" name="casa" id="casa" class="form-control" placeholder="Casa" required>
            <label for="casa"><span class="hint">Casa</span> 🏠</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating">
            <input type="text" name="codigo" id="codigo" class="form-control" placeholder="Código" required>
            <label for="codigo"><span class="hint">Código</span> 🔖</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating">
            <input type="number" min="1" name="porciones" id="porciones" class="form-control" placeholder="Porciones" required>
            <label for="porciones"><span class="hint">Porciones</span> 🍟</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating floating-select">
            <select name="tipo_pago" id="tipo_pago" class="form-select" required aria-label="Tipo de pago">
              <option value="" selected>Seleccione...</option>
              <option value="Efectivo">Efectivo</option>
              <option value="Transferencia">Transferencia</option>
            </select>
            <label for="tipo_pago"><span class="hint">Tipo de pago</span> 💳</label>
          </div>
        </div>
        <div class="col-md-6 d-none" id="cambio_wrapper">
          <div class="form-floating">
            <input type="number" name="cambio" id="cambio" class="form-control" step="any" placeholder="Cambio">
            <label for="cambio"><span class="hint">Cambio</span> 💵</label>
          </div>
        </div>
        <div class="col-12 d-grid d-md-flex gap-2">
          <button type="submit" class="btn btn-gradient btn-lg px-4">
            <i class="bi bi-check2-circle me-2"></i>Registrar
          </button>
          <button type="reset" class="btn btn-outline-secondary btn-lg">
            <i class="bi bi-eraser me-2"></i>Limpiar
          </button>
        </div>
    </form>
      </div>
    </div>

    <?php if (!empty($_SESSION['pedidos'])): ?>
    <div class="card border-0 shadow-sm mt-4">
      <div class="card-header bg-white">
        <h2 class="h5 mb-0"><i class="bi bi-list-check me-2"></i>Pedidos pendientes</h2>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Condominio</th>
                <th>Sector</th>
                <th>Casa</th>
                <th>Código</th>
                <th>Porciones</th>
                <th>Pago</th>
                <th>Cambio</th>
                <th>Estado</th>
                <th>Tiempo entrega</th>
                <th class="text-end">Acción</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($_SESSION['pedidos'] as $i => $p): ?>
              <tr>
                <td><?= htmlspecialchars($p['condominio']) ?></td>
                <td><?= htmlspecialchars($p['sector']) ?></td>
                <td><?= htmlspecialchars($p['casa']) ?></td>
                <td><span class="badge text-bg-secondary badge-pill"><?= htmlspecialchars($p['codigo']) ?></span></td>
                <td><span class="fw-semibold"><?= htmlspecialchars($p['porciones']) ?></span></td>
                <td>
                  <?php if (strcasecmp($p['tipo_pago'], 'Efectivo') === 0): ?>
                    <span class="badge text-bg-warning-subtle border border-warning-subtle text-warning-emphasis badge-pill"><i class="bi bi-cash-coin me-1"></i>Efectivo</span>
                  <?php else: ?>
                    <span class="badge text-bg-info-subtle border border-info-subtle text-info-emphasis badge-pill"><i class="bi bi-bank me-1"></i>Transferencia</span>
                  <?php endif; ?>
                </td>
                <td><?= $p['cambio'] !== '' ? htmlspecialchars($p['cambio']) : '-' ?></td>
                <td>
                  <?php $status = $p['status'] ?? 'Pendiente'; ?>
                  <?php if (strcasecmp($status, 'Atendido') === 0): ?>
                    <span class="badge text-bg-success-subtle border border-success-subtle text-success-emphasis badge-pill"><i class="bi bi-check2-circle me-1"></i>Atendido</span>
                  <?php else: ?>
                    <span class="badge text-bg-secondary-subtle border border-secondary-subtle text-secondary-emphasis badge-pill"><i class="bi bi-hourglass-split me-1"></i>Pendiente</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($p['attended_at']) && !empty($p['created_at'])): ?>
                    <?php $diff = (int)$p['attended_at'] - (int)$p['created_at']; ?>
                    <?= htmlspecialchars(pp_tiempo_humano($diff)) ?>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <?php if (strcasecmp($p['status'] ?? 'Pendiente', 'Atendido') !== 0): ?>
                    <form method="post" class="d-inline">
                      <input type="hidden" name="accion" value="atendido">
                      <input type="hidden" name="index" value="<?= $i ?>">
                      <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-check2"></i> Atendido
                      </button>
                    </form>
                  <?php else: ?>
                    <button type="button" class="btn btn-outline-success btn-sm" disabled>
                      <i class="bi bi-check2-all"></i> Atendido
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <?php else: ?>
      <div class="text-center text-body-secondary mt-4">
        <div class="display-6">📝</div>
        <p class="mt-2 mb-0">Aún no hay pedidos. ¡Registra el primero!</p>
      </div>
    <?php endif; ?>

    </main>

<script>
const sectorOptions = {
    'Almeria': ['Arboleas','Montillana','Cardelas','Villamena','Paules','Almazora'],
    'Navarra': ['Havienda','vistas','jardines','encinos','celajes','pasajea_afuera'],
    'Florenza': []
};
const condominioSelect = document.getElementById('condominio');
const sectorSelect = document.getElementById('sector');
condominioSelect.addEventListener('change', function () {
    const sectors = sectorOptions[this.value] || [];
    sectorSelect.innerHTML = '';
    if (sectors.length) {
        sectorSelect.disabled = false;
        sectors.forEach(function (sec) {
            const opt = document.createElement('option');
            opt.value = sec;
            opt.textContent = sec;
            sectorSelect.appendChild(opt);
        });
    } else {
        sectorSelect.disabled = true;
    }
});
const tipoPagoSelect = document.getElementById('tipo_pago');
const cambioWrapper = document.getElementById('cambio_wrapper');
tipoPagoSelect.addEventListener('change', function () {
    if (this.value === 'Efectivo') {
        cambioWrapper.classList.remove('d-none');
    } else {
        cambioWrapper.classList.add('d-none');
    }
});
</script>
<script>
  // Loader control: hide when loaded, show on form submissions
  (function(){
    const loader = document.getElementById('pageLoader');
    const show = () => loader && loader.classList.remove('hidden');
    const hide = () => loader && loader.classList.add('hidden');
    // Hide after page fully loads (slight delay for a smooth feel)
    window.addEventListener('load', () => setTimeout(hide, 250));
    // Show when submitting any form (navigation starts)
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('form').forEach(f => {
        f.addEventListener('submit', () => {
          show();
        });
      });
    });
  })();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Auto-dismiss flash alerts after 30s
  (function(){
    const AUTO_DISMISS_MS = 30000;
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(el => {
      setTimeout(() => {
        try {
          if (window.bootstrap && bootstrap.Alert) {
            bootstrap.Alert.getOrCreateInstance(el).close();
          } else {
            // Fallback fade-out
            el.classList.add('fade');
            el.classList.remove('show');
            el.addEventListener('transitionend', () => el.remove(), { once: true });
          }
        } catch (e) { el.remove(); }
      }, AUTO_DISMISS_MS);
    });
  })();
  </script>
</body>
</html>
