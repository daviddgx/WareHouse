<?php
require_once __DIR__ . '/session_guard.php';

include '../LQS_EUQ/Auth.php';
include '../LQS_EUQ/Connect.php';

if (!isset($_GET['Guia'], $_GET['Entrega'], $_GET['IDH'])) {
    header('Location: Traking_Guias.php');
    exit;
}

$transporte = trim((string) $_GET['Guia']);
$entrega = trim((string) $_GET['Entrega']);
$material = trim((string) $_GET['IDH']);

if ($transporte === '' || $entrega === '' || $material === '') {
    header('Location: Traking_Guias.php');
    exit;
}

$urlDetalle = 'DetalleGuias.php?' . http_build_query([
    'Guia' => $transporte,
    'Entrega' => $entrega
]);

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();

    // Se bloquean y conservan los datos completos que serán eliminados.
    $consultarDetalle = $pdo->prepare(
        'SELECT IDRegistro, Transporte, Entrega, Material, Cajas, PesoNeto,
                PesoBruto, Ubicacion, Estatus, Tipo
         FROM dbs9098416.DetalleGuias
         WHERE Transporte = :Transporte AND Material = :Material
         FOR UPDATE'
    );
    $consultarDetalle->execute([
        ':Transporte' => $transporte,
        ':Material' => $material
    ]);
    $registrosEliminados = $consultarDetalle->fetchAll(PDO::FETCH_ASSOC);

    if (!$registrosEliminados) {
        throw new RuntimeException('No se encontró el IDH solicitado en la guía.');
    }

    $detalleJson = json_encode(
        $registrosEliminados,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
    );

    if ($detalleJson === false) {
        throw new RuntimeException('No fue posible serializar el detalle de la eliminación.');
    }

    $insertarLog = $pdo->prepare(
        'INSERT INTO dbs9098416.AuditoriaEliminacionesDetalleGuias
            (IdEvento, Transporte, EntregaSolicitada, IDH, RegistrosEliminados,
             DetalleEliminado, Usuario, DireccionIP, IPsProxy, AgenteUsuario,
             IdSesionHash)
         VALUES
            (:IdEvento, :Transporte, :EntregaSolicitada, :IDH, :RegistrosEliminados,
             :DetalleEliminado, :Usuario, :DireccionIP, :IPsProxy, :AgenteUsuario,
             :IdSesionHash)'
    );
    $insertarLog->execute([
        ':IdEvento' => bin2hex(random_bytes(16)),
        ':Transporte' => $transporte,
        ':EntregaSolicitada' => $entrega,
        ':IDH' => $material,
        ':RegistrosEliminados' => count($registrosEliminados),
        ':DetalleEliminado' => $detalleJson,
        ':Usuario' => (string) ($_SESSION['Usuario'] ?? 'Usuario no identificado'),
        ':DireccionIP' => substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45) ?: null,
        ':IPsProxy' => substr((string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''), 0, 500) ?: null,
        ':AgenteUsuario' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 1000) ?: null,
        ':IdSesionHash' => session_id() !== '' ? hash('sha256', session_id()) : null
    ]);

    $eliminarDetalle = $pdo->prepare(
        'DELETE FROM dbs9098416.DetalleGuias
         WHERE Transporte = :Transporte AND Material = :Material'
    );
    $eliminarDetalle->execute([
        ':Transporte' => $transporte,
        ':Material' => $material
    ]);

    if ($eliminarDetalle->rowCount() !== count($registrosEliminados)) {
        throw new RuntimeException('La cantidad eliminada no coincide con la cantidad auditada.');
    }

    $pdo->commit();
    header('Location: ' . $urlDetalle);
    exit;
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'Error al eliminar/auditar IDH de DetalleGuias. Guia: '
        . $transporte . ', IDH: ' . $material . '. ' . $e->getMessage()
    );
    header('Location: ' . $urlDetalle);
    exit;
}


