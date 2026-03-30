<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

const FINANZAS_PASSWORD = 'Inicio94';

function finanzas_handle_auth_post(): void
{
  if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    return;
  }

  $action = (string)($_POST['action'] ?? '');
  $redirect = (string)($_POST['redirect_to'] ?? basename($_SERVER['PHP_SELF']));
  $redirect = basename($redirect);

  if ($action === 'cerrar_sesion_finanzas') {
    $_SESSION['finanzas_auth'] = false;
    header('Location: ' . $redirect);
    exit;
  }

  if ($action === 'acceso_finanzas') {
    $claveIngresada = (string)($_POST['clave_finanzas'] ?? '');
    if (hash_equals(FINANZAS_PASSWORD, $claveIngresada)) {
      $_SESSION['finanzas_auth'] = true;
      header('Location: ' . $redirect);
      exit;
    }
    $_SESSION['finanzas_auth_error'] = 'Clave incorrecta. Intenta nuevamente.';
  }
}

function finanzas_require_auth(string $pageTitle = 'Proyecto Finanzas'): void
{
  if ((bool)($_SESSION['finanzas_auth'] ?? false)) {
    return;
  }

  $authError = $_SESSION['finanzas_auth_error'] ?? null;
  unset($_SESSION['finanzas_auth_error']);
  ?>
  <!doctype html>
  <html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso - <?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
      body { background: linear-gradient(135deg, #eef4ff 0%, #f8fafc 100%); }
      .login-card { border-radius: 1.25rem; overflow: hidden; }
      .login-hero { background: linear-gradient(135deg, #0d6efd, #3b82f6); color: #fff; }
      .password-toggle { cursor: pointer; }
    </style>
  </head>
  <body class="bg-light">
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-4">
          <div class="card shadow-sm border-0 login-card">
            <div class="login-hero p-4 text-center">
              <h1 class="h4 mb-2"><?= htmlspecialchars($pageTitle) ?></h1>
              <p class="mb-0 opacity-75">Acceso protegido del módulo de finanzas.</p>
            </div>
            <div class="card-body p-4">
              <p class="text-muted text-center">Ingresa tu clave para continuar.</p>
              <form method="post">
                <input type="hidden" name="action" value="acceso_finanzas">
                <input type="hidden" name="redirect_to" value="<?= htmlspecialchars(basename($_SERVER['PHP_SELF'])) ?>">
                <div class="mb-3">
                  <label class="form-label">Clave de acceso</label>
                  <div class="input-group">
                    <input id="clave_finanzas" type="password" name="clave_finanzas" class="form-control" required autofocus>
                    <button id="toggleClaveFinanzas" class="btn btn-outline-secondary password-toggle" type="button" aria-label="Mostrar u ocultar clave">
                      <i class="bi bi-eye"></i>
                    </button>
                  </div>
                  <div class="form-text">La sesión se mantendrá activa hasta que cierres sesión manualmente.</div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php if (!empty($authError)): ?>
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
      <script>
        Swal.fire({ icon: 'error', title: 'Acceso denegado', text: <?= json_encode($authError, JSON_UNESCAPED_UNICODE) ?> });
      </script>
    <?php endif; ?>
    <script>
      const inputClave = document.getElementById('clave_finanzas');
      const toggleClave = document.getElementById('toggleClaveFinanzas');
      if (inputClave && toggleClave) {
        toggleClave.addEventListener('click', () => {
          const mostrar = inputClave.type === 'password';
          inputClave.type = mostrar ? 'text' : 'password';
          toggleClave.innerHTML = mostrar ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
        });
      }
    </script>
  </body>
  </html>
  <?php
  exit;
}