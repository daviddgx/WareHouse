<?php
require_once 'ValidarSesion.php';
require_once "../LQS_EUQ/Auth.php";

date_default_timezone_set('America/Guatemala');

function redirigirPiking()
{
    header('Location: Lista_Piking.php', true, 303);
    exit;
}

function finalizarConErrorPiking($mensaje)
{
    $_SESSION['mensaje_error_piking'] = $mensaje;
    redirigirPiking();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigirPiking();
}

$tokenRecibido = isset($_POST['token']) ? (string) $_POST['token'] : '';
$tokenSesion = isset($_SESSION['token_piking']) ? (string) $_SESSION['token_piking'] : '';

if (
    $tokenSesion === ''
    || $tokenRecibido === ''
    || !hash_equals($tokenSesion, $tokenRecibido)
) {
    finalizarConErrorPiking('La solicitud venció o ya fue procesada. Actualice la página e intente nuevamente.');
}

$idRegistro = isset($_POST['Guia']) ? filter_var($_POST['Guia'], FILTER_VALIDATE_INT) : false;

if ($idRegistro === false || $idRegistro <= 0) {
    finalizarConErrorPiking('El identificador del movimiento no es válido.');
}

if (!isset($pdo) || !($pdo instanceof PDO)) {
    finalizarConErrorPiking('No fue posible establecer conexión con la base de datos.');
}

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();

    // Bloquea el registro para impedir que dos clics procesen el mismo movimiento.
    $consultaMovimiento = $pdo->prepare(
        "SELECT IDH, Origen, Destino
         FROM dbs9098416.piking
         WHERE id = ? AND Montacarguista = ? AND Estado = 'Pendiente'
         LIMIT 1
         FOR UPDATE"
    );
    $consultaMovimiento->execute([$idRegistro, $_SESSION['Usuario']]);
    $movimiento = $consultaMovimiento->fetch(PDO::FETCH_ASSOC);

    if (!$movimiento) {
        throw new RuntimeException('El movimiento ya fue procesado o no pertenece al usuario actual.');
    }

    $idh = $movimiento['IDH'];
    $origen = $movimiento['Origen'];
    $destino = $movimiento['Destino'];
    $fecha = date('Y-m-d H:i:s');

    // Verifica y bloquea la ubicación origen antes de modificar cualquier dato.
    $consultaOrigen = $pdo->prepare(
        "SELECT UnidadesEnPallet
         FROM dbs9098416.posiciones
         WHERE Ubicacion = ? AND Estado <> 'Libre'
         LIMIT 1
         FOR UPDATE"
    );
    $consultaOrigen->execute([$origen]);
    $ubicacionOrigen = $consultaOrigen->fetch(PDO::FETCH_ASSOC);

    if (!$ubicacionOrigen) {
        throw new RuntimeException('La ubicación origen no existe o ya se encuentra libre.');
    }

    $cantidadBultos = (int) $ubicacionOrigen['UnidadesEnPallet'];
    if ($cantidadBultos <= 0) {
        throw new RuntimeException('La ubicación origen no tiene bultos disponibles.');
    }

    $historico = $pdo->prepare(
        "INSERT INTO dbs9098416.posiciones_historico
         SELECT posiciones.*, ?, ?
         FROM dbs9098416.posiciones
         WHERE Ubicacion = ?"
    );
    $historico->execute(['Piking', $idRegistro, $origen]);

    if ($historico->rowCount() !== 1) {
        throw new RuntimeException('No fue posible guardar el histórico de la ubicación origen.');
    }

    $bitacora = $pdo->prepare(
        "INSERT INTO dbs9098416.bitacora
            (Movimiento, TipoEvento, Horafecha, IDH, usuario, estado_anterior, estado_nuevo)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $bitacora->execute([
        'MoverAPiking',
        'Operacion',
        $fecha,
        $idh,
        $_SESSION['Usuario'],
        'En ubicación: ' . $origen,
        'A ubicación: ' . $destino . '. Reabastecimiento: ' . $idRegistro
    ]);

    // Se reutiliza la misma conexión y sentencia para todos los bultos.
    $insertarDetalle = $pdo->prepare(
        "INSERT INTO dbs9098416.detalle_piking
         SELECT Bodega, Carril, Posicion, Nivel, Ubicacion, Estado, IDH,
                PaletCompleto, 1, Origen, FechaProduccion, LoteProduccion,
                FechaIngreso, FechaVencimiento, FechaCuarentena, Cantidad,
                EstatusProducto, Verificador, UsuarioMontaCargas, Turno,
                EstatusUbicacion, Observaciones, NULL, NULL, NULL
         FROM dbs9098416.posiciones
         WHERE Ubicacion = ?"
    );

    for ($bulto = 0; $bulto < $cantidadBultos; $bulto++) {
        $insertarDetalle->execute([$origen]);
        if ($insertarDetalle->rowCount() !== 1) {
            throw new RuntimeException('No fue posible registrar todos los bultos en el detalle de picking.');
        }
    }

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
             turno = NULL,
             EstatusUbicacion = NULL,
             observaciones = NULL
         WHERE Ubicacion = ?"
    );
    $liberarOrigen->execute([$origen]);

    if ($liberarOrigen->rowCount() !== 1) {
        throw new RuntimeException('No fue posible liberar la ubicación origen.');
    }

    $moverDetalle = $pdo->prepare(
        "UPDATE dbs9098416.detalle_piking
         SET Estado = 'Ocupada', Ubicacion = ?
         WHERE Ubicacion = ?"
    );
    $moverDetalle->execute([$destino, $origen]);

    if ($moverDetalle->rowCount() !== $cantidadBultos) {
        throw new RuntimeException('La cantidad de bultos trasladados no coincide con la cantidad esperada.');
    }

    $bitacora->execute([
        'Ubicacion Liberada',
        'Operacion',
        $fecha,
        $idh,
        $_SESSION['Usuario'],
        'Ocupada por: ' . $idh,
        'Reubicación a picking: ' . $idRegistro
    ]);

    $actualizarMovimiento = $pdo->prepare(
        "UPDATE dbs9098416.piking
         SET Estado = 'Reubicada', Fecha_Movimiento = ?
         WHERE id = ? AND Montacarguista = ? AND Estado = 'Pendiente'"
    );
    $actualizarMovimiento->execute([$fecha, $idRegistro, $_SESSION['Usuario']]);

    if ($actualizarMovimiento->rowCount() !== 1) {
        throw new RuntimeException('No fue posible finalizar el movimiento de picking.');
    }

    $pdo->commit();
    unset($_SESSION['token_piking']);
    $_SESSION['mensaje_exito_piking'] = 'El abastecimiento de picking se completó correctamente.';
} catch (Exception $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'MoverProductoPiking.php | Movimiento ' . $idRegistro
        . ' | Usuario ' . $_SESSION['Usuario']
        . ' | ' . $error->getMessage()
    );

    $_SESSION['mensaje_error_piking'] = $error instanceof RuntimeException
        ? $error->getMessage()
        : 'Ocurrió un error al procesar el movimiento. Ningún cambio fue aplicado.';
}

redirigirPiking();
