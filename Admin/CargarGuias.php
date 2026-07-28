<?php
require_once __DIR__ . '/session_guard.php';

ob_start();
$pasoActual = 'Inicio del proceso';

try {
    $pasoActual = 'Cargando configuración de autenticación y conexión';
    //require('Config_Guias.php');
    include '../LQS_EUQ/Auth.php';
    include '../LQS_EUQ/Connect.php';

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $pasoActual = 'Creando conexión con MySQL';
    $conexion = new mysqli($servername, $username, $password, $dbname);
    $conexion->set_charset('utf8mb4');

    $pasoActual = 'Consultando guías existentes';
    $sqlValidacion = "
        SELECT DISTINCT d.Transporte, d.Estatus
        FROM dbs9098416.DetalleGuias AS d
        INNER JOIN dbs9098416.Guia_PreCarga AS p
            ON p.Transporte = d.Transporte
        ORDER BY d.Transporte
    ";
    $resultadoValidacion = $conexion->query($sqlValidacion);

    $pasoActual = 'Revisando el estado de las guías existentes';
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

    $pasoActual = 'Identificando las guías que se guardarán';
    $resultadoGuiasCargadas = $conexion->query(
        'SELECT DISTINCT Transporte FROM dbs9098416.Guia_PreCarga'
    );
    $transportesCargados = [];
    while ($guiaCargada = $resultadoGuiasCargadas->fetch_assoc()) {
        $transportesCargados[] = (string) $guiaCargada['Transporte'];
    }

    $pasoActual = 'Ejecutando el procedimiento PonerGuiasEnFirme';
    $conexion->query('CALL PonerGuiasEnFirme()');

    $_SESSION['guias_recien_cargadas'] = $transportesCargados;

    $pasoActual = 'Redirigiendo al seguimiento de guías';
    header("Location: Traking_Guias.php");
    exit;
} catch (Throwable $e) {
    error_log(
        '[CargarGuias] Error en "' . $pasoActual . '": '
        . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine()
    );

    if (ob_get_length()) {
        ob_clean();
    }

    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');

    echo '<h2>Error al guardar la carga</h2>';
    echo '<p><strong>Paso donde se detuvo:</strong> '
        . htmlspecialchars($pasoActual, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<p><strong>Error de PHP/MySQL:</strong> '
        . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<p><strong>Archivo:</strong> '
        . htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<p><strong>Línea:</strong> ' . (int) $e->getLine() . '</p>';
    echo '<p><a href="Guias_CargarGuia.php">Regresar a la carga de guías</a></p>';
}

ob_end_flush();
?>


