<?php

$MensajeExito = '';
$Mensajeerror = '';
$txtIDH = '';
$txtLote = '';
$txtBultos = '';
$txtDescripcionEliminacion = '';

if (!function_exists('piking_valor_entero_positivo')) {
    function piking_valor_entero_positivo($valor)
    {
        if (!is_scalar($valor)) {
            return 0;
        }

        $entero = filter_var($valor, FILTER_VALIDATE_INT);
        return ($entero !== false && $entero > 0) ? (int) $entero : 0;
    }
}

if (!isset($_SESSION['csrf_quitar_inventario_piking'])
    || !is_string($_SESSION['csrf_quitar_inventario_piking'])) {
    $_SESSION['csrf_quitar_inventario_piking'] = bin2hex(random_bytes(32));
}
$csrfQuitarPiking = $_SESSION['csrf_quitar_inventario_piking'];

$accion = isset($_POST['accion']) && is_scalar($_POST['accion'])
    ? (string) $_POST['accion']
    : '';

if ($accion === 'btnAbregarPiking') {
    $txtIDH = piking_valor_entero_positivo(isset($_POST['txtIDH']) ? $_POST['txtIDH'] : null);
    $txtLote = isset($_POST['txtLote']) && is_scalar($_POST['txtLote'])
        ? trim((string) $_POST['txtLote'])
        : '';
    $txtBultos = piking_valor_entero_positivo(isset($_POST['txtBultos']) ? $_POST['txtBultos'] : null);
    $txtDescripcionEliminacion = isset($_POST['txtDescripcionEliminacion'])
        && is_scalar($_POST['txtDescripcionEliminacion'])
        ? trim((string) $_POST['txtDescripcionEliminacion'])
        : '';
    $txtDescripcionEliminacion = preg_replace('/\s+/', ' ', $txtDescripcionEliminacion);

    $tokenRecibido = isset($_POST['csrf_token']) && is_scalar($_POST['csrf_token'])
        ? (string) $_POST['csrf_token']
        : '';
    $descripcionLarga = function_exists('mb_strlen')
        ? mb_strlen($txtDescripcionEliminacion, 'UTF-8') > 1000
        : strlen($txtDescripcionEliminacion) > 1000;

    if (!hash_equals($csrfQuitarPiking, $tokenRecibido)) {
        $Mensajeerror = '<div class="alert alert-danger" role="alert">La sesión del formulario venció. Intente nuevamente.</div>';
    } elseif ($txtIDH <= 0 || $txtLote === '' || $txtBultos <= 0) {
        $Mensajeerror = '<div class="alert alert-danger" role="alert">Complete correctamente el IDH, lote y cantidad a eliminar.</div>';
    } elseif (strlen($txtLote) > 50) {
        $Mensajeerror = '<div class="alert alert-danger" role="alert">El lote seleccionado no es válido.</div>';
    } elseif ($txtDescripcionEliminacion === '') {
        $Mensajeerror = '<div class="alert alert-danger" role="alert">Debe ingresar una descripción para la eliminación.</div>';
    } elseif ($descripcionLarga) {
        $Mensajeerror = '<div class="alert alert-danger" role="alert">La descripción no puede exceder 1000 caracteres.</div>';
    } else {
        try {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS BitacoraMantenimientoPiking (
                    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    Fecha DATETIME NOT NULL,
                    Usuario VARCHAR(250) NOT NULL,
                    IDH INT NOT NULL,
                    LoteProduccion VARCHAR(50) NOT NULL,
                    BultosSolicitados INT UNSIGNED NOT NULL,
                    BultosEliminados INT UNSIGNED NOT NULL,
                    Descripcion VARCHAR(1000) NOT NULL,
                    DetalleEliminacion LONGTEXT NOT NULL,
                    PRIMARY KEY (Id),
                    INDEX IDX_BitacoraMantenimientoPiking_Fecha (Fecha),
                    INDEX IDX_BitacoraMantenimientoPiking_IDH_Lote (IDH, LoteProduccion)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
            );

            $columnasDetallePiking = array(
                'Bodega', 'Carril', 'Posicion', 'Nivel', 'Ubicacion', 'Estado',
                'IDH', 'PaletCompleto', 'UnidadesEnPallet', 'Origen',
                'FechaProduccion', 'LoteProduccion', 'FechaIngreso',
                'FechaVencimiento', 'FechaCuarentena', 'Cantidad',
                'EstatusProducto', 'Verificador', 'UsuarioMontaCargas', 'Turno',
                'EstatusUbicacion', 'Observaciones', 'Estatus', 'ID_Despacho',
                'Transporte'
            );
            $columnasSql = array();
            $condicionesEliminar = array();
            foreach ($columnasDetallePiking as $columna) {
                $columnasSql[] = '`' . $columna . '`';
                $condicionesEliminar[] = '`' . $columna . '` <=> ?';
            }

            $pdo->beginTransaction();

            $seleccionar = $pdo->prepare(
                'SELECT ' . implode(', ', $columnasSql) . '
                 FROM detalle_piking
                 WHERE Estatus IS NULL AND IDH = ? AND LoteProduccion = ?
                 LIMIT ' . $txtBultos . ' FOR UPDATE'
            );
            $seleccionar->execute(array($txtIDH, $txtLote));
            $registrosEliminados = $seleccionar->fetchAll(PDO::FETCH_ASSOC);

            if (count($registrosEliminados) !== $txtBultos) {
                $disponibles = count($registrosEliminados);
                $pdo->rollBack();
                $Mensajeerror = '<div class="alert alert-warning" role="alert">Solo hay '
                    . $disponibles . ' bulto(s) disponibles para ese lote. No se eliminó ningún registro.</div>';
            } else {
                $eliminar = $pdo->prepare(
                    'DELETE FROM detalle_piking WHERE '
                    . implode(' AND ', $condicionesEliminar)
                    . ' LIMIT 1'
                );

                foreach ($registrosEliminados as $registro) {
                    $valoresEliminar = array();
                    foreach ($columnasDetallePiking as $columna) {
                        $valoresEliminar[] = $registro[$columna];
                    }
                    $eliminar->execute($valoresEliminar);

                    if ($eliminar->rowCount() !== 1) {
                        throw new RuntimeException('No se pudo eliminar exactamente uno de los registros seleccionados.');
                    }
                }

                $detalleJson = json_encode(
                    $registrosEliminados,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
                if ($detalleJson === false) {
                    throw new RuntimeException('No se pudo generar el detalle de auditoría.');
                }

                $registrarBitacora = $pdo->prepare(
                    'INSERT INTO BitacoraMantenimientoPiking
                        (Fecha, Usuario, IDH, LoteProduccion, BultosSolicitados,
                         BultosEliminados, Descripcion, DetalleEliminacion)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $registrarBitacora->execute(array(
                    date('Y-m-d H:i:s'),
                    isset($_SESSION['Usuario']) ? (string) $_SESSION['Usuario'] : '',
                    $txtIDH,
                    $txtLote,
                    $txtBultos,
                    count($registrosEliminados),
                    $txtDescripcionEliminacion,
                    $detalleJson
                ));

                $pdo->commit();
                unset($_SESSION['csrf_quitar_inventario_piking']);
                header(
                    'Location: Mantenimiento_Piking_QuitarInventario.php?IDH='
                    . rawurlencode($txtIDH)
                    . '&MSG=SCS&eliminados=' . count($registrosEliminados)
                );
                exit;
            }
        } catch (Exception $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Mantenimiento_Piking_QuitarInventario: ' . $ex->getMessage());
            $Mensajeerror = '<div class="alert alert-danger" role="alert">No fue posible completar la eliminación. No se aplicó ningún cambio.</div>';
        }
    }
} else {
    $txtIDH = piking_valor_entero_positivo(isset($_GET['IDH']) ? $_GET['IDH'] : null);

    if ($txtIDH <= 0) {
        header('Location: ../Innet/505.html');
        exit;
    }

    if (isset($_GET['MSG']) && $_GET['MSG'] === 'SCS') {
        $eliminados = isset($_GET['eliminados'])
            ? piking_valor_entero_positivo($_GET['eliminados'])
            : 0;
        $MensajeExito = '<div class="alert alert-success" role="alert"><strong>Eliminación registrada.</strong> Se eliminaron '
            . $eliminados . ' bulto(s) y el detalle quedó guardado en la bitácora.</div>';
    }
}

$lotesDisponibles = array();
if ($txtIDH > 0) {
    try {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $consultarLotes = $pdo->prepare(
            "SELECT LoteProduccion, COUNT(*) AS Bultos
             FROM detalle_piking
             WHERE Estatus IS NULL AND IDH = ?
               AND LoteProduccion IS NOT NULL AND LoteProduccion <> ''
             GROUP BY LoteProduccion
             ORDER BY LoteProduccion"
        );
        $consultarLotes->execute(array($txtIDH));
        $lotesDisponibles = $consultarLotes->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $ex) {
        error_log('Mantenimiento_Piking_QuitarInventario lotes: ' . $ex->getMessage());
        if ($Mensajeerror === '') {
            $Mensajeerror = '<div class="alert alert-danger" role="alert">No fue posible consultar los lotes disponibles.</div>';
        }
    }
}

?>
