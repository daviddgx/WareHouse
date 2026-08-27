<?php
require_once 'ValidarSesion.php';
require_once '../LQS_EUQ/Auth.php';

date_default_timezone_set('America/Guatemala');

function redirigirReubicaciones()
{
    header('Location: Lista_Reubicaciones.php', true, 303);
    exit;
}

function finalizarConErrorReubicacion($mensaje)
{
    $_SESSION['mensaje_error_reubicacion'] = $mensaje;
    redirigirReubicaciones();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigirReubicaciones();
}

$tokenRecibido = isset($_POST['token']) ? (string) $_POST['token'] : '';
$tokenSesion = isset($_SESSION['token_reubicacion']) ? (string) $_SESSION['token_reubicacion'] : '';

if (
    $tokenSesion === ''
    || $tokenRecibido === ''
    || !hash_equals($tokenSesion, $tokenRecibido)
) {
    finalizarConErrorReubicacion(
        'La solicitud venció o ya fue procesada. Actualice la página e intente nuevamente.'
    );
}

$idRegistro = isset($_POST['Guia'])
    ? filter_var($_POST['Guia'], FILTER_VALIDATE_INT)
    : false;

if ($idRegistro === false || $idRegistro <= 0) {
    finalizarConErrorReubicacion('El identificador de la reubicación no es válido.');
}

if (!isset($pdo) || !($pdo instanceof PDO)) {
    finalizarConErrorReubicacion('No fue posible establecer conexión con la base de datos.');
}

$usuarioActual = isset($_SESSION['Usuario']) ? (string) $_SESSION['Usuario'] : '';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();

    // Impide que dos solicitudes procesen simultáneamente la misma reubicación.
    $consultaMovimiento = $pdo->prepare(
        "SELECT IDH, Origen, Destino
         FROM dbs9098416.Reubicaciones
         WHERE id = ? AND Montacarguista = ? AND Estado = 'Pendiente'
         LIMIT 1
         FOR UPDATE"
    );
    $consultaMovimiento->execute([$idRegistro, $usuarioActual]);
    $movimiento = $consultaMovimiento->fetch(PDO::FETCH_ASSOC);

    if (!$movimiento) {
        throw new RuntimeException(
            'La reubicación ya fue procesada o no pertenece al usuario actual.'
        );
    }

    $idh = (int) $movimiento['IDH'];
    $origen = trim((string) $movimiento['Origen']);
    $destino = trim((string) $movimiento['Destino']);
    $fecha = date('Y-m-d H:i:s');

    if ($idh <= 0 || $origen === '' || $destino === '' || $origen === $destino) {
        throw new RuntimeException('Los datos de la reubicación no son válidos.');
    }

    // Bloquea las dos ubicaciones en un orden estable para evitar cambios concurrentes.
    $consultaUbicaciones = $pdo->prepare(
        "SELECT Ubicacion, Estado, IDH
         FROM dbs9098416.posiciones
         WHERE Ubicacion IN (?, ?)
         ORDER BY Ubicacion
         FOR UPDATE"
    );
    $consultaUbicaciones->execute([$origen, $destino]);

    $ubicaciones = [];
    while ($ubicacion = $consultaUbicaciones->fetch(PDO::FETCH_ASSOC)) {
        $ubicaciones[$ubicacion['Ubicacion']] = $ubicacion;
    }

    if (!isset($ubicaciones[$origen], $ubicaciones[$destino])) {
        throw new RuntimeException('No se encontraron las ubicaciones de origen y destino.');
    }

    $estadoOrigen = strtolower(trim((string) $ubicaciones[$origen]['Estado']));
    if ($estadoOrigen === 'libre' || (int) $ubicaciones[$origen]['IDH'] !== $idh) {
        throw new RuntimeException(
            'La ubicación origen ya está libre o contiene un producto diferente.'
        );
    }

    $estadoDestino = strtolower(trim((string) $ubicaciones[$destino]['Estado']));
    $estadosDestinoPermitidos = ['libre', 'movimiento-dest'];
    if (
        $ubicaciones[$destino]['IDH'] !== null
        || !in_array($estadoDestino, $estadosDestinoPermitidos, true)
    ) {
        throw new RuntimeException(
            'La ubicación destino ya está ocupada o no está disponible para esta reubicación.'
        );
    }

    $historico = $pdo->prepare(
        "INSERT INTO dbs9098416.posiciones_historico
         SELECT posiciones.*, ?, ?
         FROM dbs9098416.posiciones
         WHERE Ubicacion = ?"
    );
    $historico->execute(['Reubicacion', $idRegistro, $origen]);

    if ($historico->rowCount() !== 1) {
        throw new RuntimeException('No fue posible guardar el histórico de la ubicación origen.');
    }

    $bitacora = $pdo->prepare(
        "INSERT INTO dbs9098416.bitacora
            (Movimiento, TipoEvento, Horafecha, IDH, usuario, estado_anterior, estado_nuevo)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $bitacora->execute([
        'Reubicacion',
        'Operacion',
        $fecha,
        $idh,
        $usuarioActual,
        'En ubicación: ' . $origen,
        'A ubicación: ' . $destino . '. Reubicación: ' . $idRegistro
    ]);

    if ($bitacora->rowCount() !== 1) {
        throw new RuntimeException('No fue posible registrar la reubicación en la bitácora.');
    }

    // 1. Copia el material al destino. El siguiente paso solo se ejecuta si se movió una fila.
    $moverMaterial = $pdo->prepare(
        "UPDATE dbs9098416.posiciones AS destino
         JOIN dbs9098416.posiciones AS origen
           ON origen.Ubicacion = ?
         SET destino.Estado = origen.Estado,
             destino.IDH = origen.IDH,
             destino.PaletCompleto = origen.PaletCompleto,
             destino.UnidadesEnPallet = origen.UnidadesEnPallet,
             destino.Origen = origen.Origen,
             destino.FechaProduccion = origen.FechaProduccion,
             destino.LoteProduccion = origen.LoteProduccion,
             destino.FechaIngreso = origen.FechaIngreso,
             destino.FechaVencimiento = origen.FechaVencimiento,
             destino.FechaCuarentena = origen.FechaCuarentena,
             destino.Cantidad = origen.Cantidad,
             destino.EstatusProducto = origen.EstatusProducto,
             destino.Verificador = origen.Verificador,
             destino.UsuarioMontaCargas = origen.UsuarioMontaCargas,
             destino.Turno = origen.Turno,
             destino.EstatusUbicacion = origen.EstatusUbicacion,
             destino.Observaciones = origen.Observaciones
         WHERE destino.Ubicacion = ?
           AND destino.IDH IS NULL
           AND origen.IDH = ?"
    );
    $moverMaterial->execute([$origen, $destino, $idh]);

    if ($moverMaterial->rowCount() !== 1) {
        throw new RuntimeException('No fue posible mover el material a la ubicación destino.');
    }

    // 2. Libera el origen únicamente después de copiar correctamente el material.
    $liberarOrigen = $pdo->prepare(
        "UPDATE dbs9098416.posiciones
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
             Turno = NULL,
             EstatusUbicacion = NULL,
             Observaciones = NULL
         WHERE Ubicacion = ? AND IDH = ?"
    );
    $liberarOrigen->execute([$origen, $idh]);

    if ($liberarOrigen->rowCount() !== 1) {
        throw new RuntimeException('No fue posible liberar la ubicación origen.');
    }

    // 3. Corrige el estado del destino únicamente después de liberar el origen.
    $corregirDestino = $pdo->prepare(
        "UPDATE dbs9098416.posiciones
         SET Estado = 'Ocupada'
         WHERE Ubicacion = ? AND IDH = ?"
    );
    $corregirDestino->execute([$destino, $idh]);

    // La actualización puede reportar cero filas si el origen ya tenía estado Ocupada.
    // Por eso se comprueba el estado final, que es la condición realmente requerida.
    $verificarResultado = $pdo->prepare(
        "SELECT Ubicacion, Estado, IDH
         FROM dbs9098416.posiciones
         WHERE Ubicacion IN (?, ?)"
    );
    $verificarResultado->execute([$origen, $destino]);

    $resultado = [];
    while ($ubicacion = $verificarResultado->fetch(PDO::FETCH_ASSOC)) {
        $resultado[$ubicacion['Ubicacion']] = $ubicacion;
    }

    $origenLibre = isset($resultado[$origen])
        && strcasecmp((string) $resultado[$origen]['Estado'], 'Libre') === 0
        && $resultado[$origen]['IDH'] === null;
    $destinoOcupado = isset($resultado[$destino])
        && strcasecmp((string) $resultado[$destino]['Estado'], 'Ocupada') === 0
        && (int) $resultado[$destino]['IDH'] === $idh;

    if (!$origenLibre || !$destinoOcupado) {
        throw new RuntimeException(
            'La validación final de las ubicaciones falló. Ningún cambio fue aplicado.'
        );
    }

    $bitacora->execute([
        'Ubicacion Liberada',
        'Operacion',
        $fecha,
        $idh,
        $usuarioActual,
        'Ocupada por: ' . $idh,
        'Reubicación, registro: ' . $idRegistro
    ]);

    if ($bitacora->rowCount() !== 1) {
        throw new RuntimeException('No fue posible registrar la liberación del origen.');
    }

    $historico->execute(['Reubicacion ID: ' . $idRegistro, $idRegistro, $destino]);

    if ($historico->rowCount() !== 1) {
        throw new RuntimeException('No fue posible guardar el histórico de la ubicación destino.');
    }

    $actualizarMovimiento = $pdo->prepare(
        "UPDATE dbs9098416.Reubicaciones
         SET Estado = 'Reubicada', Fecha_Movimiento = ?
         WHERE id = ? AND Montacarguista = ? AND Estado = 'Pendiente'"
    );
    $actualizarMovimiento->execute([$fecha, $idRegistro, $usuarioActual]);

    if ($actualizarMovimiento->rowCount() !== 1) {
        throw new RuntimeException('No fue posible finalizar el registro de la reubicación.');
    }

    $pdo->commit();
    unset($_SESSION['token_reubicacion']);
    $_SESSION['mensaje_exito_reubicacion'] = 'La reubicación se completó correctamente.';
} catch (Throwable $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'MoverProducto.php | Reubicacion ' . $idRegistro
        . ' | Usuario ' . $usuarioActual
        . ' | ' . $error->getMessage()
    );

    $_SESSION['mensaje_error_reubicacion'] = $error instanceof RuntimeException
        ? $error->getMessage()
        : 'Ocurrió un error al procesar la reubicación. Ningún cambio fue aplicado.';
}

redirigirReubicaciones();
