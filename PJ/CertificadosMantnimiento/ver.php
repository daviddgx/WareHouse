<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../schema_certificados_mantenimiento.php';

$conn = db();
ensure_certificados_mantenimiento_schema($conn);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  http_response_code(400);
  die('ID inválido');
}

$stmt = $conn->prepare('SELECT * FROM certificados_mantenimiento WHERE id=? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$certificado = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$certificado) {
  http_response_code(404);
  die('Certificado no encontrado.');
}

$esImpresion = isset($_GET['print']) && $_GET['print'] === '1';
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl = strtok($scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? ''), '?');
$publicUrl = $baseUrl . '?id=' . $id;
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . rawurlencode($publicUrl);
$firmaPayload = implode('|', [
  (string)$certificado['id'],
  (string)$certificado['numero_certificado'],
  (string)$certificado['marca_modelo_serie'],
  (string)$certificado['fecha_inicio_vigencia'],
  (string)$certificado['fecha_fin_vigencia'],
  (string)$certificado['autorizado_por'],
]);
$codigoFirmaDigital = strtoupper(substr(hash('sha256', $firmaPayload), 0, 24));

function ffecha(string $fecha): string {
  $ts = strtotime($fecha);
  return $ts ? date('d/m/Y', $ts) : $fecha;
}

function ffechaHora(?string $fecha): string {
  if (!$fecha) return 'N/D';
  $ts = strtotime($fecha);
  return $ts ? date('d/m/Y H:i', $ts) : $fecha;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Certificado <?= htmlspecialchars($certificado['numero_certificado'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #ececec; font-family: Arial, Helvetica, sans-serif; }
    .certificado {
      max-width: 880px;
      margin: 1.5rem auto;
      background: #fff;
      border: 1px solid #ddd;
      position: relative;
      overflow: hidden;
    }
    .header-bg, .footer-bg {
      height: 78px;
      background: linear-gradient(90deg, rgba(247,147,30,.18), rgba(247,147,30,.35), rgba(247,147,30,.12));
      position: relative;
    }
    .header-bg::after, .footer-bg::after {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at 20% 40%, rgba(255,255,255,.45), rgba(255,255,255,0) 55%),
                  radial-gradient(circle at 80% 20%, rgba(255,255,255,.35), rgba(255,255,255,0) 42%);
    }
    .contenido {
      padding: 2rem 3rem;
      text-align: center;
      color: #1f1f1f;
      line-height: 1.35;
    }
    .titulo {
      font-size: 3rem;
      font-weight: 700;
      margin: .5rem 0 0;
    }
    .logo-encabezado {
      max-width: 170px;
      width: 100%;
      height: auto;
      margin-bottom: .35rem;
    }
    .num-cert {
      font-size: 1.4rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
    }
    .logo-cliente {
      font-size: 2rem;
      font-weight: 700;
      color: #b90e0a;
      margin-bottom: 1.3rem;
    }
    .bloque-principal { font-size: 1.05rem; }
    .destacado { font-weight: 700; font-size: 1.25rem; }
    .vigencia { font-size: 1.05rem; margin-top: 1.2rem; }
    .firma-area {
      display: flex;
      justify-content: space-between;
      align-items: end;
      margin-top: 2rem;
      gap: 1rem;
    }
    .firma {
      text-align: center;
      min-width: 280px;
    }
    .firma-linea {
      border-top: 6px solid #000;
      margin-top: 2.5rem;
      padding-top: .65rem;
    }
    .logo-sertero {
      max-width: 220px;
      width: 100%;
      height: auto;
    }
    .qr-box {
      text-align: center;
      font-size: .85rem;
    }
    .qr-box img { width: 130px; height: 130px; }
    .codigo-firma {
      margin-top: .35rem;
      font-size: .75rem;
      font-family: "Courier New", Courier, monospace;
      letter-spacing: .08em;
      word-break: break-all;
    }
    .footer-contacto {
      text-align: center;
      font-size: 1rem;
      margin-top: 1.6rem;
      margin-bottom: .3rem;
    }
    @media print {
      body { background: #fff; }
      .no-print { display: none !important; }
      .certificado { margin: 0; border: none; max-width: 100%; }
    }

    .anulado-wrap {
      max-width: 760px;
      margin: 2rem auto;
    }
  </style>
</head>
<body>
  <?php if ((int)$certificado['anulado'] === 1): ?>
    <div class="container anulado-wrap">
      <div class="card border-danger shadow-sm">
        <div class="card-body p-4 p-md-5 text-center">
          <h1 class="h3 text-danger mb-3">Certificado anulado</h1>
          <p class="mb-2">El certificado <strong><?= htmlspecialchars($certificado['numero_certificado'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong> se encuentra anulado.</p>
          <p class="mb-4">Fecha de anulación: <strong><?= htmlspecialchars(ffechaHora((string)$certificado['fecha_anulacion']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>.</p>
          <a href="listado.php" class="btn btn-outline-secondary">Volver al listado</a>
        </div>
      </div>
    </div>
  <?php else: ?>
  <div class="container py-3 no-print">
    <div class="d-flex gap-2">
      <button class="btn btn-dark" onclick="window.print()">Imprimir</button>
    </div>
    <?php if (isset($_GET['creado'])): ?>
      <div class="alert alert-success mt-3">Certificado creado correctamente.</div>
    <?php endif; ?>
    <?php if (isset($_GET['editado'])): ?>
      <div class="alert alert-success mt-3">Certificado actualizado correctamente.</div>
    <?php endif; ?>
  </div>

  <section class="certificado">
    <div class="header-bg"></div>
    <div class="contenido">
      <img src="../assets/logo.png" alt="Logo Sertero" class="logo-encabezado">
      <div class="titulo">Certificación</div>
      <div class="num-cert">N° <?= htmlspecialchars($certificado['numero_certificado'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
      <div class="mb-2">Otorgada a:</div>
      <div class="logo-cliente"><?= htmlspecialchars($certificado['otorgada_a'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>

      <div class="bloque-principal">
        Por medio de la presente SERTERO certifica que:<br>
        <?= htmlspecialchars($certificado['texto_encabezado_equipo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><br>
        <span class="destacado"><?= htmlspecialchars($certificado['marca_modelo_serie'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span><br>
        <?= nl2br(htmlspecialchars($certificado['texto_cuerpo_certificacion'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) ?>
      </div>

      <div class="vigencia">
        Este Certificado es válido <strong>desde el <?= ffecha((string)$certificado['fecha_inicio_vigencia']) ?></strong><br>
        <strong>hasta el <?= ffecha((string)$certificado['fecha_fin_vigencia']) ?></strong> y su validez está sujeta a auditorías de<br>
        seguimiento con resultado <strong><?= htmlspecialchars($certificado['resultado_auditoria'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong> en los mantenimientos programados.<br>
        Fecha de auditoría de Recertificación antes del <strong><?= ffecha((string)$certificado['fecha_recertificacion']) ?></strong>
      </div>

      <div class="firma-area">
        <div class="firma">
          <div class="firma-linea">
            <strong>Firma:</strong><br>
            <span class="codigo-firma"><?= htmlspecialchars($codigoFirmaDigital, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
          </div>
        </div>
        <div class="qr-box">
          <img src="<?= htmlspecialchars($qrUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" alt="QR certificado">
          <div>Escanear para vista web</div>
        </div>
      </div>

      <div class="footer-contacto">
        Para más detalles en relación a esta certificación puede escribir a<br>
        <strong><?= htmlspecialchars($certificado['correo_contacto'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>
      </div>
    </div>
    <div class="footer-bg"></div>
  </section>

  <?php if ($esImpresion): ?>
    <script>window.print();</script>
  <?php endif; ?>
  <?php endif; ?>
</body>
</html>