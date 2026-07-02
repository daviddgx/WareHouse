<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../schema_certificados_mantenimiento.php';

$conn = db();
ensure_certificados_mantenimiento_schema($conn);

$errores = [];
$exito = '';

$datos = [
  'otorgada_a' => '',
  'marca_modelo_serie' => '',
  'ubicacion' => '',
  'capacidad_dias' => '45',
  'texto_encabezado_equipo' => 'El sistema de Grabación DVR marca Hikvision serie:',
  'texto_cuerpo_certificacion' => '',
  'fecha_inicio_vigencia' => date('Y-m-d'),
  'fecha_fin_vigencia' => date('Y-m-d', strtotime('+6 months')),
  'resultado_auditoria' => 'SATISFACTORIO',
  'fecha_recertificacion' => date('Y-m-d', strtotime('+6 months')),
  'autorizado_por' => 'Ricardo A. Orantes',
  'correo_contacto' => 'servicioalcliente@sertero.com'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  foreach ($datos as $k => $v) {
    $datos[$k] = trim((string)($_POST[$k] ?? $v));
  }



  $textoAuto = 'Ubicado en ' . $datos['ubicacion']
    . '. Cuenta con la capacidad de almacenamiento de ' . (int)$datos['capacidad_dias']
    . ' días de grabación continua.';

  if ($datos['texto_cuerpo_certificacion'] === '' || isset($_POST['regenerar_texto'])) {
    $datos['texto_cuerpo_certificacion'] = $textoAuto;
  }

  if ($datos['otorgada_a'] === '') $errores[] = 'El campo "Otorgada a" es obligatorio.';
  if ($datos['marca_modelo_serie'] === '') $errores[] = 'El campo "Sistema / marca / serie" es obligatorio.';
  if ($datos['ubicacion'] === '') $errores[] = 'El campo "Ubicación" es obligatorio.';
  if ((int)$datos['capacidad_dias'] <= 0) $errores[] = 'La capacidad de días debe ser mayor a 0.';

  if (!$errores && !isset($_POST['regenerar_texto'])) {
    $stmt = $conn->prepare("INSERT INTO certificados_mantenimiento
      (numero_certificado, otorgada_a, marca_modelo_serie, ubicacion, capacidad_dias, texto_encabezado_equipo, texto_cuerpo_certificacion, fecha_inicio_vigencia, fecha_fin_vigencia, resultado_auditoria, fecha_recertificacion, autorizado_por, correo_contacto)
      VALUES ('TEMP', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $capacidad = (int)$datos['capacidad_dias'];
    $stmt->bind_param(
      'sssissssssss',
      $datos['otorgada_a'],
      $datos['marca_modelo_serie'],
      $datos['ubicacion'],
      $capacidad,
      $datos['texto_encabezado_equipo'],
      $datos['texto_cuerpo_certificacion'],
      $datos['fecha_inicio_vigencia'],
      $datos['fecha_fin_vigencia'],
      $datos['resultado_auditoria'],
      $datos['fecha_recertificacion'],
      $datos['autorizado_por'],
      $datos['correo_contacto']
    );
    $stmt->execute();
    $nuevoId = (int)$conn->insert_id;
    $stmt->close();

    $numeroCertificado = sprintf('CM-%s-%04d', date('Y'), $nuevoId);
    $upd = $conn->prepare('UPDATE certificados_mantenimiento SET numero_certificado=? WHERE id=?');
    $upd->bind_param('si', $numeroCertificado, $nuevoId);
    $upd->execute();
    $upd->close();

    header('Location: ver.php?id=' . $nuevoId . '&creado=1');
    exit;
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nuevo Certificado de Mantenimiento</title>
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
      max-width: 1100px;
    }

    .header-panel {
      background: linear-gradient(115deg, var(--sertero-primary), var(--sertero-secondary));
      color: #fff;
      border-radius: 1rem;
      padding: 1.5rem;
      box-shadow: 0 10px 24px rgba(8, 56, 92, 0.16);
    }

    .main-card {
      border: 0;
      border-radius: 1rem;
      box-shadow: 0 18px 40px rgba(14, 56, 90, 0.12);
      overflow: hidden;
    }

    .main-card .card-body {
      padding: 1.75rem;
    }

    .brand-block {
      background: var(--sertero-soft);
      border: 1px solid #dce9f6;
      border-radius: 0.85rem;
      padding: 1rem;
    }

    .form-label {
      font-weight: 600;
      color: #274d72;
      margin-bottom: .35rem;
    }

    .form-control {
      border-radius: .65rem;
      border-color: #c9d9ea;
      padding-top: .58rem;
      padding-bottom: .58rem;
    }

    .form-control:focus {
      border-color: #88b7dd;
      box-shadow: 0 0 0 .2rem rgba(19, 105, 170, .15);
    }

    .section-title {
      font-size: .95rem;
      letter-spacing: .03em;
      text-transform: uppercase;
      color: #52789e;
      font-weight: 700;
      margin-top: .35rem;
      margin-bottom: .15rem;
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
        <h1 class="h3 mb-1">Certificados de Mantenimiento</h1>
        <p class="mb-0 opacity-75">Captura la información para generar un certificado claro y profesional.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a href="listado.php" class="btn btn-light">Consultar certificados</a>
        
      </div>
    </div>

    <?php if ($errores): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errores as $error): ?>
            <li><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="card main-card">
      <div class="card-body">
        <div class="brand-block text-center mb-4">
          <img src="../assets/logo.png" alt="Logo Sertero" style="max-width:260px;width:100%;height:auto;">
        </div>
        <h2 class="h5 mb-3 text-primary-emphasis">Formulario de certificado</h2>
        <form method="post" class="row g-3">
          <div class="col-12">
            <p class="section-title">Datos generales</p>
          </div>
          <div class="col-md-6">
            <label class="form-label">Otorgada a</label>
            <input type="text" name="otorgada_a" class="form-control" required value="<?= htmlspecialchars($datos['otorgada_a'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Sistema / Marca / Serie</label>
            <input type="text" name="marca_modelo_serie" class="form-control" required value="<?= htmlspecialchars($datos['marca_modelo_serie'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
          </div>
          <div class="col-12">
            <label class="form-label">Ubicación</label>
            <input type="text" name="ubicacion" class="form-control" required value="<?= htmlspecialchars($datos['ubicacion'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Capacidad (días)</label>
            <input type="number" min="1" name="capacidad_dias" class="form-control" required value="<?= htmlspecialchars($datos['capacidad_dias'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
          </div>
          <div class="col-12 mt-2">
            <p class="section-title">Textos del certificado</p>
          </div>
          <div class="col-12">
            <label class="form-label">Texto de encabezado del equipo (editable)</label>
            <input type="text" name="texto_encabezado_equipo" class="form-control" required value="<?= htmlspecialchars($datos['texto_encabezado_equipo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
          </div>
          <div class="col-12">
            <label class="form-label">Texto de lo que se está certificando (auto-generado y editable)</label>
            <textarea name="texto_cuerpo_certificacion" class="form-control" rows="3" required><?= htmlspecialchars($datos['texto_cuerpo_certificacion'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
            <button type="submit" name="regenerar_texto" value="1" class="btn btn-sm btn-outline-secondary mt-2">Regenerar texto automático</button>
          </div>
          <div class="col-12 mt-2">
            <p class="section-title">Vigencia y autorización</p>
          </div>
          <div class="col-md-3">
            <label class="form-label">Inicio vigencia</label>
            <input type="date" name="fecha_inicio_vigencia" class="form-control" required value="<?= htmlspecialchars($datos['fecha_inicio_vigencia'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Fin vigencia</label>
            <input type="date" name="fecha_fin_vigencia" class="form-control" required value="<?= htmlspecialchars($datos['fecha_fin_vigencia'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Fecha recertificación</label>
            <input type="date" name="fecha_recertificacion" class="form-control" required value="<?= htmlspecialchars($datos['fecha_recertificacion'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Resultado auditoría</label>
            <input type="text" name="resultado_auditoria" class="form-control" required value="<?= htmlspecialchars($datos['resultado_auditoria'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Autorizado por</label>
            <input type="text" name="autorizado_por" class="form-control" required value="<?= htmlspecialchars($datos['autorizado_por'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Correo de contacto</label>
            <input type="email" name="correo_contacto" class="form-control" required value="<?= htmlspecialchars($datos['correo_contacto'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
          </div>
          <div class="col-12 d-grid d-md-flex justify-content-md-end mt-3">
            <button type="submit" class="btn btn-primary px-4">Guardar y generar certificado</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>