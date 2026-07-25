<?php
require_once __DIR__ . '/session_guard.php';
require_once '../LQS_EUQ/Connect.php';

/**
 * Consume todos los resultados pendientes dejados por un procedimiento almacenado.
 */
function consumirResultadosPendientes(mysqli $conexion): void
{
    while ($conexion->more_results()) {
        $conexion->next_result();
        if ($resultado = $conexion->store_result()) {
            $resultado->free();
        }
    }
}

/**
 * Redirige al listado mostrando un mensaje controlado al usuario.
 */
function redirigirConAlerta(string $titulo, string $mensaje, string $icono = 'warning'): void
{
    $_SESSION['alerta_calculo_ubicaciones'] = [
        'title' => $titulo,
        'text' => $mensaje,
        'icon' => $icono,
    ];

    header('Location: AsignarUbicaciones.php');
    exit;
}

/**
 * Registra un fallo después del rollback mediante una conexión independiente.
 * Un error de la propia bitácora nunca debe ocultar el error original.
 */
function registrarErrorProceso(
    string $codigoError,
    string $proceso,
    string $referencia,
    Throwable $excepcion,
    string $servidor,
    string $usuarioBaseDatos,
    string $claveBaseDatos,
    string $baseDatos
): bool {
    $conexionBitacora = null;

    try {
        $conexionBitacora = new mysqli(
            $servidor,
            $usuarioBaseDatos,
            $claveBaseDatos,
            $baseDatos
        );
        $conexionBitacora->set_charset('utf8mb4');

        $usuarioAplicacion = (string) (
            $_SESSION['USR']
            ?? $_SESSION['Usuario']
            ?? 'desconocido'
        );
        $mensaje = substr($excepcion->getMessage(), 0, 65000);
        $claseExcepcion = get_class($excepcion);
        $archivo = substr($excepcion->getFile(), 0, 500);
        $linea = $excepcion->getLine();
        $traza = substr($excepcion->getTraceAsString(), 0, 65000);
        $direccionIp = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
        $agenteUsuario = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);

        // Nunca guardar el token de un solo uso ni otros secretos de autenticación.
        $datosSolicitud = [
            'metodo' => $_SERVER['REQUEST_METHOD'] ?? '',
            'ruta' => $_SERVER['REQUEST_URI'] ?? '',
            'guia' => $_POST['Guia'] ?? null,
        ];
        $datosSolicitudJson = json_encode(
            $datosSolicitud,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        if ($datosSolicitudJson === false) {
            $datosSolicitudJson = null;
        }

        $nivel = 'ERROR';
        $sentencia = $conexionBitacora->prepare(
            'INSERT INTO dbs9098416.BitacoraErroresProcesos
                (CodigoError, Proceso, Nivel, Referencia, Usuario, Mensaje,
                 ClaseExcepcion, Archivo, Linea, Traza, DatosSolicitud,
                 DireccionIP, AgenteUsuario)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $sentencia->bind_param(
            'ssssssssissss',
            $codigoError,
            $proceso,
            $nivel,
            $referencia,
            $usuarioAplicacion,
            $mensaje,
            $claseExcepcion,
            $archivo,
            $linea,
            $traza,
            $datosSolicitudJson,
            $direccionIp,
            $agenteUsuario
        );
        $sentencia->execute();
        $sentencia->close();
        $conexionBitacora->close();

        return true;
    } catch (Throwable $errorBitacora) {
        if ($conexionBitacora instanceof mysqli) {
            try {
                $conexionBitacora->close();
            } catch (Throwable $errorCierre) {
                // No reemplazar el error original por un fallo al cerrar.
            }
        }

        error_log(
            sprintf(
                '[BitacoraErroresProcesos][%s] No se pudo registrar el error: %s',
                $codigoError,
                $errorBitacora->getMessage()
            )
        );

        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: AsignarUbicaciones.php');
    exit;
}

$transporte = trim($_POST['Guia'] ?? '');
$tokenRecibido = $_POST['CalculoToken'] ?? '';
$tokenEsperado = $_SESSION['calculo_ubicaciones_tokens'][$transporte] ?? '';

if (
    $transporte === ''
    || !ctype_digit($transporte)
    || (int) $transporte <= 0
    || (int) $transporte > 2147483647
    || $tokenEsperado === ''
    || !is_string($tokenRecibido)
    || !hash_equals($tokenEsperado, $tokenRecibido)
) {
    redirigirConAlerta(
        'Proceso no ejecutado',
        'La solicitud ya fue utilizada, expiró o no es válida. Actualice la página antes de intentarlo nuevamente.'
    );
}

// Consumir el token antes de iniciar impide dobles envíos desde esta sesión.
unset($_SESSION['calculo_ubicaciones_tokens'][$transporte]);

$conexion = null;

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $conexion = new mysqli($servername, $username, $password, $dbname);
    $conexion->set_charset('utf8mb4');
    $conexion->begin_transaction();

    // El bloqueo evita que dos usuarios calculen la misma guía simultáneamente.
    $sentencia = $conexion->prepare(
        'SELECT Estatus
         FROM dbs9098416.Guias
         WHERE Transporte = ?
         LIMIT 1
         FOR UPDATE'
    );
    $sentencia->bind_param('s', $transporte);
    $sentencia->execute();
    $sentencia->bind_result($estatusActual);
    $guiaExiste = $sentencia->fetch();
    $sentencia->close();

    if (!$guiaExiste) {
        throw new DomainException('La guía indicada no existe.');
    }

    if (strcasecmp(trim((string) $estatusActual), 'Pendiente') !== 0) {
        throw new DomainException('La guía ya no se encuentra en estado Pendiente.');
    }

    /*
     * Ajustar cantidades para clientes con configuración de despacho especial.
     * NULLIF evita divisiones entre cero y SUM evita subconsultas con varias filas.
     */
    $sentencia = $conexion->prepare(
        'SELECT especiales.IDH,
                FLOOR(SUM(detalle.Cajas) / NULLIF(especiales.Actual * especiales.Nuevo, 0)) AS NuevoValor
         FROM dbs9098416.dspachos_especiales AS especiales
         INNER JOIN dbs9098416.Guias AS guias
             ON guias.NombreDestino = especiales.Cliente
            AND guias.Transporte = ?
         INNER JOIN dbs9098416.DetalleGuias AS detalle
             ON detalle.Transporte = guias.Transporte
            AND detalle.Material = especiales.IDH
         GROUP BY especiales.IDH, especiales.Actual, especiales.Nuevo'
    );
    $sentencia->bind_param('s', $transporte);
    $sentencia->execute();
    $sentencia->bind_result($materialEspecial, $nuevoValor);

    $ajustesEspeciales = [];
    while ($sentencia->fetch()) {
        if ($nuevoValor === null || (int) $nuevoValor < 0) {
            throw new DomainException(
                'La configuración de despacho especial contiene cantidades no válidas.'
            );
        }

        $ajustesEspeciales[] = [
            'material' => (string) $materialEspecial,
            'cajas' => (int) $nuevoValor,
        ];
    }
    $sentencia->close();

    if ($ajustesEspeciales) {
        $actualizarCajas = $conexion->prepare(
            'UPDATE dbs9098416.DetalleGuias
             SET Cajas = ?
             WHERE Material = ?
               AND Transporte = ?'
        );

        foreach ($ajustesEspeciales as $ajuste) {
            $cajasAjustadas = $ajuste['cajas'];
            $materialAjustado = $ajuste['material'];
            $actualizarCajas->bind_param(
                'iss',
                $cajasAjustadas,
                $materialAjustado,
                $transporte
            );
            $actualizarCajas->execute();
        }
        $actualizarCajas->close();
    }

    // Evitar duplicados si existieran datos temporales de un intento anterior.
    $sentencia = $conexion->prepare(
        'DELETE FROM dbs9098416.DetalleGuias_Carga WHERE Transporte = ?'
    );
    $sentencia->bind_param('s', $transporte);
    $sentencia->execute();
    $sentencia->close();

    $sentencia = $conexion->prepare('CALL dbs9098416.sp_insertar_detalle_guias_carga(?)');
    $sentencia->bind_param('s', $transporte);
    $sentencia->execute();
    $sentencia->close();
    consumirResultadosPendientes($conexion);

    $sentencia = $conexion->prepare(
        'SELECT COUNT(*)
         FROM dbs9098416.DetalleGuias_Carga
         WHERE Transporte = ?'
    );
    $sentencia->bind_param('s', $transporte);
    $sentencia->execute();
    $sentencia->bind_result($cantidadCarga);
    $sentencia->fetch();
    $sentencia->close();

    if ((int) $cantidadCarga === 0) {
        throw new DomainException(
            'No se generaron registros válidos para calcular las ubicaciones de esta guía.'
        );
    }

    $sentencia = $conexion->prepare(
        'DELETE FROM dbs9098416.DetalleGuias WHERE Transporte = ?'
    );
    $sentencia->bind_param('s', $transporte);
    $sentencia->execute();
    $sentencia->close();

    /*
     * Insertar los pallets reutilizando una sola sentencia y conexión.
     * Esto conserva el comportamiento actual sin abrir una conexión por pallet.
     */
    $sentencia = $conexion->prepare(
        "SELECT Transporte, Entrega, Material, Cajas, PesoNeto, PesoBruto, Tipo, Pallets
         FROM dbs9098416.DetalleGuias_Carga
         WHERE Transporte = ?
           AND Pallets > 0"
    );
    $sentencia->bind_param('s', $transporte);
    $sentencia->execute();
    $sentencia->bind_result(
        $cargaTransporte,
        $entrega,
        $material,
        $cajas,
        $pesoNeto,
        $pesoBruto,
        $tipo,
        $cantidadPallets
    );

    $pallets = [];
    while ($sentencia->fetch()) {
        $pallets[] = [
            'transporte' => $cargaTransporte,
            'entrega' => $entrega,
            'material' => $material,
            'cajas' => $cajas,
            'pesoNeto' => $pesoNeto,
            'pesoBruto' => $pesoBruto,
            'tipo' => $tipo,
            'cantidad' => (int) $cantidadPallets,
        ];
    }
    $sentencia->close();

    $insertarPallet = $conexion->prepare(
        "INSERT INTO dbs9098416.DetalleGuias
            (Transporte, Entrega, Material, Cajas, PesoNeto, PesoBruto, Ubicacion, Estatus, Tipo)
         VALUES (?, ?, ?, ?, ?, ?, '', '', ?)"
    );

    foreach ($pallets as $pallet) {
        $palletTransporte = (string) $pallet['transporte'];
        $palletEntrega = (string) $pallet['entrega'];
        $palletMaterial = (string) $pallet['material'];
        $palletCajas = (int) $pallet['cajas'];
        $palletPesoNeto = (float) $pallet['pesoNeto'];
        $palletPesoBruto = (float) $pallet['pesoBruto'];
        $palletTipo = (string) $pallet['tipo'];

        $insertarPallet->bind_param(
            'sssidds',
            $palletTransporte,
            $palletEntrega,
            $palletMaterial,
            $palletCajas,
            $palletPesoNeto,
            $palletPesoBruto,
            $palletTipo
        );

        for ($i = 0; $i < $pallet['cantidad']; $i++) {
            $insertarPallet->execute();
        }
    }
    $insertarPallet->close();

    $sentencia = $conexion->prepare(
        "INSERT INTO dbs9098416.DetalleGuias
            (Transporte, Entrega, Material, Cajas, PesoNeto, PesoBruto, Ubicacion, Estatus, Tipo)
         SELECT Transporte, Entrega, Material, Cajas, PesoNeto, PesoBruto, '', '', Tipo
         FROM dbs9098416.DetalleGuias_Carga
         WHERE Transporte = ?
           AND Tipo = 'Piking'"
    );
    $sentencia->bind_param('s', $transporte);
    $sentencia->execute();
    $sentencia->close();

    $sentencia = $conexion->prepare('CALL dbs9098416.CalcularUbicacionesDespacho(?)');
    $transporteEntero = (int) $transporte;
    $sentencia->bind_param('i', $transporteEntero);
    $sentencia->execute();
    $sentencia->close();
    consumirResultadosPendientes($conexion);

    $sentencia = $conexion->prepare(
        "UPDATE dbs9098416.DetalleGuias
         SET Ubicacion = 'Piking'
         WHERE Transporte = ?
           AND Tipo = 'Piking'"
    );
    $sentencia->bind_param('s', $transporte);
    $sentencia->execute();
    $sentencia->close();

    $sentencia = $conexion->prepare(
        "SELECT COUNT(*)
         FROM dbs9098416.DetalleGuias
         WHERE Transporte = ?
           AND Tipo = 'Pallets'
           AND (Ubicacion IS NULL OR Ubicacion = '')"
    );
    $sentencia->bind_param('s', $transporte);
    $sentencia->execute();
    $sentencia->bind_result($palletsSinUbicacion);
    $sentencia->fetch();
    $sentencia->close();

    if ((int) $palletsSinUbicacion > 0) {
        $sentencia = $conexion->prepare(
            "UPDATE dbs9098416.Guias
             SET Estatus = 'Corregir'
             WHERE Transporte = ?"
        );
        $sentencia->bind_param('s', $transporte);
        $sentencia->execute();
        $sentencia->close();
    }

    $conexion->commit();
    $conexion->close();

    error_log(
        sprintf(
            '[CalcularUbicaciones] Guia %s calculada por usuario %s.',
            $transporte,
            $_SESSION['USR'] ?? $_SESSION['Usuario'] ?? 'desconocido'
        )
    );

    header('Location: DetalleUbicacionesCalculadas.php?Guia=' . urlencode($transporte));
    exit;
} catch (DomainException $exception) {
    if ($conexion instanceof mysqli) {
        try {
            $conexion->rollback();
            $conexion->close();
        } catch (Throwable $errorCierre) {
            error_log('[CalcularUbicaciones] Error durante rollback: ' . $errorCierre->getMessage());
        }
    }

    error_log(
        sprintf(
            '[CalcularUbicaciones] Validacion rechazada para guia %s: %s',
            $transporte,
            $exception->getMessage()
        )
    );

    redirigirConAlerta('Proceso no ejecutado', $exception->getMessage());
} catch (Throwable $exception) {
    if ($conexion instanceof mysqli) {
        try {
            $conexion->rollback();
            $conexion->close();
        } catch (Throwable $errorCierre) {
            error_log('[CalcularUbicaciones] Error durante rollback: ' . $errorCierre->getMessage());
        }
    }

    $idError = date('YmdHis') . '-' . bin2hex(random_bytes(3));

    registrarErrorProceso(
        $idError,
        'CALCULO_UBICACIONES',
        $transporte,
        $exception,
        $servername,
        $username,
        $password,
        $dbname
    );

    error_log(
        sprintf(
            '[CalcularUbicaciones][%s] Error en guia %s, usuario %s: %s',
            $idError,
            $transporte,
            $_SESSION['USR'] ?? $_SESSION['Usuario'] ?? 'desconocido',
            $exception->getMessage()
        )
    );

    redirigirConAlerta(
        'No se pudo calcular la guía',
        'El proceso fue cancelado sin guardar cambios. Código de error: ' . $idError,
        'error'
    );
}
