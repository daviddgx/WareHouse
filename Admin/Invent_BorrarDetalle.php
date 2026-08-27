<?php
require_once __DIR__ . '/session_guard.php';

include '../LQS_EUQ/Connect.php';
date_default_timezone_set('America/Guatemala');

function volverAInventario($parametros = array())
{
    header('Location: Invent_Eliminar.php?' . http_build_query($parametros));
    exit;
}

$bodegaRetorno = isset($_POST['bodega']) && is_scalar($_POST['bodega'])
    ? trim((string) $_POST['bodega'])
    : '';
$carrilRetorno = isset($_POST['carril']) && is_scalar($_POST['carril'])
    ? trim((string) $_POST['carril'])
    : '';
$parametrosRetorno = array();

if ($bodegaRetorno !== '') {
    $parametrosRetorno['Bodega'] = $bodegaRetorno;
}

if ($carrilRetorno !== '') {
    $parametrosRetorno['Carril'] = $carrilRetorno;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $parametrosRetorno['ERR'] = 'METODO';
    volverAInventario($parametrosRetorno);
}

$tokenRecibido = isset($_POST['csrf_token']) && is_scalar($_POST['csrf_token'])
    ? (string) $_POST['csrf_token']
    : '';
$tokenSesion = isset($_SESSION['csrf_invent_eliminar'])
    ? (string) $_SESSION['csrf_invent_eliminar']
    : '';

if ($tokenSesion === '' || !hash_equals($tokenSesion, $tokenRecibido)) {
    $parametrosRetorno['ERR'] = 'SESION';
    volverAInventario($parametrosRetorno);
}

$posicionesRecibidas = isset($_POST['posiciones']) ? $_POST['posiciones'] : array();
$motivo = isset($_POST['motivo']) && is_scalar($_POST['motivo'])
    ? trim((string) $_POST['motivo'])
    : '';
$motivo = preg_replace('/\s+/', ' ', $motivo);

if (!is_array($posicionesRecibidas)) {
    $posicionesRecibidas = array();
}

$posiciones = array();
foreach ($posicionesRecibidas as $posicionRecibida) {
    if (!is_scalar($posicionRecibida)) {
        continue;
    }

    $posicion = trim((string) $posicionRecibida);
    if ($posicion !== '' && strlen($posicion) <= 100) {
        $posiciones[$posicion] = $posicion;
    }
}
$posiciones = array_values($posiciones);

if (count($posiciones) === 0) {
    $parametrosRetorno['ERR'] = 'SIN_SELECCION';
    volverAInventario($parametrosRetorno);
}

if ($motivo === '') {
    $parametrosRetorno['ERR'] = 'SIN_MOTIVO';
    volverAInventario($parametrosRetorno);
}

if (strlen($motivo) > 500) {
    $parametrosRetorno['ERR'] = 'MOTIVO_LARGO';
    volverAInventario($parametrosRetorno);
}

$usuarioActual = isset($_SESSION['Usuario']) ? (string) $_SESSION['Usuario'] : '';
$fechaEliminacion = date('Y-m-d H:i:s');
$eliminadas = 0;

try {
    $conn = new PDO(
        'mysql:host=' . $servername . ';dbname=' . $dbname . ';charset=utf8',
        $username,
        $password,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
    $conn->beginTransaction();

    $seleccionar = $conn->prepare(
        "SELECT Ubicacion, IDH, PaletCompleto, UnidadesEnPallet, Origen,
                FechaIngreso, FechaProduccion, LoteProduccion
         FROM posiciones
         WHERE Ubicacion = ? AND Estado = 'Ocupada'
         FOR UPDATE"
    );

    $limpiar = $conn->prepare(
        "UPDATE posiciones
         SET Estado = 'Libre',
             IDH = NULL,
             PaletCompleto = NULL,
             UnidadesEnPallet = NULL,
             Origen = NULL,
             FechaProduccion = NULL,
             LoteProduccion = NULL,
             FechaIngreso = NULL,
             FechaVencimiento = NULL,
             FechaCuarentena = NULL,
             Cantidad = NULL,
             EstatusProducto = NULL,
             Verificador = NULL,
             UsuarioMontaCargas = NULL,
             turno = NULL,
             EstatusUbicacion = NULL,
             observaciones = NULL
         WHERE Ubicacion = ? AND Estado = 'Ocupada'"
    );

    $registrarBitacora = $conn->prepare(
        "INSERT INTO Bitar_ConteoCiego
            (Ubicacion, Accion, Fecha, Usuario, Comentarios)
         VALUES (?, 'Eliminar', ?, ?, ?)"
    );

    foreach ($posiciones as $posicion) {
        $seleccionar->execute(array($posicion));
        $datosAnteriores = $seleccionar->fetch(PDO::FETCH_ASSOC);

        if (!$datosAnteriores) {
            continue;
        }

        $limpiar->execute(array($posicion));
        if ($limpiar->rowCount() !== 1) {
            throw new RuntimeException(
                'La ubicación ' . $posicion . ' no pudo actualizarse de forma individual.'
            );
        }

        $detalleBitacora = sprintf(
            'Motivo: %s | Ubicacion: %s | IDH: %s | Pallet completo: %s | Unidades: %s | Produccion: %s | Lote: %s | Fecha de ingreso: %s | Origen: %s | Usuario: %s',
            $motivo,
            $datosAnteriores['Ubicacion'],
            $datosAnteriores['IDH'],
            $datosAnteriores['PaletCompleto'],
            $datosAnteriores['UnidadesEnPallet'],
            $datosAnteriores['FechaProduccion'],
            $datosAnteriores['LoteProduccion'],
            $datosAnteriores['FechaIngreso'],
            $datosAnteriores['Origen'],
            $usuarioActual
        );
        $detalleBitacora = function_exists('mb_substr')
            ? mb_substr($detalleBitacora, 0, 1000, 'UTF-8')
            : substr($detalleBitacora, 0, 1000);

        $registrarBitacora->execute(array(
            $posicion,
            $fechaEliminacion,
            $usuarioActual,
            $detalleBitacora
        ));
        $eliminadas++;
    }

    if ($eliminadas === 0) {
        $conn->rollBack();
        $parametrosRetorno['ERR'] = 'SIN_CAMBIOS';
        volverAInventario($parametrosRetorno);
    }

    $conn->commit();
    unset($_SESSION['csrf_invent_eliminar']);

    $parametrosRetorno['MSG'] = 'SCS';
    $parametrosRetorno['eliminadas'] = $eliminadas;
    $omitidas = count($posiciones) - $eliminadas;
    if ($omitidas > 0) {
        $parametrosRetorno['omitidas'] = $omitidas;
    }
    volverAInventario($parametrosRetorno);
} catch (Exception $ex) {
    if (isset($conn) && $conn instanceof PDO && $conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log('Invent_BorrarDetalle: ' . $ex->getMessage());
    $parametrosRetorno['ERR'] = 'ERROR';
    volverAInventario($parametrosRetorno);
}
?>
