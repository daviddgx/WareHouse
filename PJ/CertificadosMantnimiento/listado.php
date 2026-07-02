<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../schema_certificados_mantenimiento.php';

$conn = db();
ensure_certificados_mantenimiento_schema($conn);

$filtro = trim((string)($_GET['q'] ?? ''));

$sql = "SELECT id, numero_certificado, otorgada_a, marca_modelo_serie, fecha_inicio_vigencia, fecha_fin_vigencia, fecha_creacion, anulado, fecha_anulacion FROM certificados_mantenimiento";
$params = [];
$types = '';

if ($filtro !== '') {
  $sql .= " WHERE numero_certificado LIKE ? OR otorgada_a LIKE ? OR marca_modelo_serie LIKE ?";
  $like = "%$filtro%";
  $params = [$like, $like, $like];
  $types = 'sss';
}

$sql .= ' ORDER BY id DESC';
$stmt = $conn->prepare($sql);
if ($params) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$certificados = $stmt->get_result();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Consultar certificados</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --sertero-primary: #0b4f82;
      --sertero-secondary: #1369aa;
      --sertero-soft: #f3f8fc;
    }

    body {
      background: radial-gradient(circle at top right, #e8f2fb 0, #f7fbff 35%, #f5f7fa 100%);
      min-height: 100vh;
    }

    .page-shell {
      max-width: 1250px;
    }

    .header-panel {
      background: linear-gradient(115deg, var(--sertero-primary), var(--sertero-secondary));
      color: #fff;
      border-radius: 1rem;
      padding: 1.35rem 1.5rem;
      box-shadow: 0 12px 28px rgba(8, 56, 92, 0.16);
    }

    .header-panel h1 {
      margin-bottom: .2rem;
    }

    .header-panel .subtitle {
      opacity: .85;
      margin-bottom: 0;
    }

    .search-card,
    .table-card {
      border: 0;
      border-radius: 1rem;
      box-shadow: 0 14px 30px rgba(14, 56, 90, 0.1);
      overflow: hidden;
    }

    .search-card .card-body {
      background: #fff;
      padding: 1rem;
    }

    .table thead th {
      white-space: nowrap;
      font-size: .84rem;
      letter-spacing: .02em;
    }

    .table tbody td {
      vertical-align: middle;
    }

    .table tbody tr:hover {
      background-color: #f6fbff;
    }

    .actions-group {
      display: flex;
      flex-wrap: wrap;
      gap: .35rem;
    }

    .btn {
      border-radius: .65rem;
    }
  </style>
</head>
<body>
  <div class="container page-shell py-4 py-md-5">
    <div class="header-panel d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
      <div>
        <h1 class="h3">Certificados guardados</h1>
        <p class="subtitle">Consulta, edita o anula certificados de mantenimiento de forma rápida.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a href="formulario.php" class="btn btn-light">Nuevo certificado</a>
        <a href="../index.php" class="btn btn-outline-light">Inicio</a>
      </div>
    </div>

    <?php if (isset($_GET['anulado'])): ?>
      <?php if ((string)$_GET['anulado'] === '1'): ?>
        <div class="alert alert-success border-0 shadow-sm">El certificado fue anulado correctamente.</div>
      <?php else: ?>
        <div class="alert alert-warning border-0 shadow-sm">
          No se pudo anular el certificado.
          <?php if ((string)($_GET['motivo'] ?? '') === 'ya_anulado'): ?>
            <strong>Motivo:</strong> el certificado ya estaba anulado.
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <div class="card search-card mb-3">
      <div class="card-body">
        <form class="row g-2 align-items-center" method="get">
          <div class="col-md-6 col-lg-5">
            <input type="search" name="q" class="form-control" placeholder="Buscar por número, empresa o equipo" value="<?= htmlspecialchars($filtro, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
          </div>
          <div class="col-auto">
            <button class="btn btn-primary" type="submit">Buscar</button>
          </div>
          <?php if ($filtro !== ''): ?>
            <div class="col-auto">
              <a href="listado.php" class="btn btn-outline-secondary">Limpiar</a>
            </div>
          <?php endif; ?>
        </form>
      </div>
    </div>

    <div class="card table-card">
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>Certificado</th>
              <th>Otorgada a</th>
              <th>Equipo / serie</th>
              <th>Vigencia</th>
              <th>Creado</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($certificados->num_rows === 0): ?>
              <tr><td colspan="8" class="text-center py-4">Sin registros</td></tr>
            <?php else: ?>
              <?php while ($c = $certificados->fetch_assoc()): ?>
                <tr>
                  <td><?= (int)$c['id'] ?></td>
                  <td><strong><?= htmlspecialchars($c['numero_certificado'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong></td>
                  <td><?= htmlspecialchars($c['otorgada_a'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($c['marca_modelo_serie'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($c['fecha_inicio_vigencia'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> a <?= htmlspecialchars($c['fecha_fin_vigencia'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($c['fecha_creacion'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                  <td>
                    <?php if ((int)$c['anulado'] === 1): ?>
                      <span class="badge text-bg-danger">Anulado</span>
                    <?php else: ?>
                      <span class="badge text-bg-success">Vigente</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-nowrap">
                    <div class="actions-group">
                      <a href="ver.php?id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                      <a href="ver.php?id=<?= (int)$c['id'] ?>&print=1" class="btn btn-sm btn-outline-dark" target="_blank" rel="noopener">Imprimir</a>
                      <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editarCertificado(<?= (int)$c['id'] ?>)">Editar</button>
                    <?php if ((int)$c['anulado'] === 0): ?>
                      <button type="button" class="btn btn-sm btn-outline-danger" onclick="anularCertificado(<?= (int)$c['id'] ?>)">Anular</button>
                    <?php else: ?>
                      <span class="small text-danger ms-1">
                        <?= htmlspecialchars((string)$c['fecha_anulacion'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                      </span>
                    <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <form method="post" action="anular.php" id="form-anular-certificado" class="d-none">
    <input type="hidden" name="id" id="anular-id" value="">
  </form>
  <script>
    function editarCertificado(id) {
      window.location.href = 'editar.php?id=' + encodeURIComponent(id);
    }

    function anularCertificado(id) {
      if (!confirm('¿Seguro que deseas anular este certificado?')) {
        return;
      }

      document.getElementById('anular-id').value = id;
      document.getElementById('form-anular-certificado').submit();
    }
  </script>
</body>
</html>