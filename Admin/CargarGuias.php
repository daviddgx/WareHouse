<?php
require_once __DIR__ . '/session_guard.php';

ob_start();
//require('Config_Guias.php');
include '../LQS_EUQ/Auth.php';
include '../LQS_EUQ/Connect.php';

// Create connection
$conexion = new mysqli($servername, $username, $password, $dbname);

if ($conexion->connect_errno) {
    $_SESSION['guias_alerta_validacion'] = [
        'title' => 'No se pudo validar la carga',
        'text' => 'No fue posible consultar el estado actual de las guías. Intente nuevamente.',
        'icon' => 'error',
    ];
    header('Location: Guias_CargarGuia.php');
    exit;
}

$sqlValidacion = "
    SELECT DISTINCT d.Transporte, d.Estatus
    FROM dbs9098416.detalleguias AS d
    INNER JOIN dbs9098416.Guia_PreCarga AS p
        ON p.Transporte = d.Transporte
    ORDER BY d.Transporte
";
$resultadoValidacion = $conexion->query($sqlValidacion);

if ($resultadoValidacion === false) {
    $_SESSION['guias_alerta_validacion'] = [
        'title' => 'No se pudo validar la carga',
        'text' => 'No fue posible consultar el estado actual de las guías. Intente nuevamente.',
        'icon' => 'error',
    ];
    header('Location: Guias_CargarGuia.php');
    exit;
}

$guiasExistentes = [];
$guiasBloqueadas = [];
while ($guia = $resultadoValidacion->fetch_assoc()) {
    $guiasExistentes[] = $guia;
    if (strcasecmp(trim((string) $guia['Estatus']), 'Pendiente') !== 0) {
        $guiasBloqueadas[] = $guia;
    }
}

if ($guiasBloqueadas) {
    $mensajes = [];
    foreach ($guiasBloqueadas as $guia) {
        $mensajes[] = 'La guía ' . $guia['Transporte'] . ' ya existe y está en estatus ' . $guia['Estatus'] . '.';
    }
    $_SESSION['guias_alerta_validacion'] = [
        'title' => 'Guía existente',
        'text' => implode("\n", $mensajes)
            . "\nNo se puede cargar más información de esta guía en el estado actual.",
        'icon' => 'error',
    ];
    header('Location: Guias_CargarGuia.php');
    exit;
}

if ($guiasExistentes && ($_POST['ContinuarPendiente'] ?? '0') !== '1') {
    $_SESSION['guias_alerta_validacion'] = [
        'title' => 'Confirmación requerida',
        'text' => 'La guía ya existe en estatus Pendiente. Confirme que desea continuar.',
        'icon' => 'warning',
    ];
    header('Location: Guias_CargarGuia.php');
    exit;
}

$sql = "CALL PonerGuiasEnFirme()";
if (mysqli_query($conexion, $sql)) {
    header("Location: Traking_Guias.php");
} else {
    header("Location: Traking_Guias.php");
}

ob_end_flush();
?>


