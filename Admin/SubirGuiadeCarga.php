<?php
$modoDiagnosticoCarga = isset($_GET['debug']) && $_GET['debug'] === '1';

if ($modoDiagnosticoCarga) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    set_time_limit(120);
    ob_start();
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    header('Content-Type: text/html; charset=utf-8');

    echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8">';
    echo '<title>Diagnóstico de carga de guías</title>';
    echo '<style>'
        . 'body{font-family:Arial,sans-serif;margin:24px;background:#f5f6f8;color:#263238}'
        . '.panel{max-width:960px;margin:auto;background:#fff;border-radius:12px;padding:24px;box-shadow:0 8px 24px rgba(0,0,0,.08)}'
        . '.paso{padding:10px 12px;margin:8px 0;border-left:4px solid #1976d2;background:#eef6ff;white-space:pre-wrap}'
        . '.exito{border-color:#28a745;background:#effaf2}.error{border-color:#dc3545;background:#fff1f2}'
        . '.hora{color:#607d8b;font-size:12px;margin-right:8px}.volver{display:inline-block;margin-top:18px;padding:10px 14px;background:#ed3131;color:#fff;text-decoration:none;border-radius:6px}'
        . '</style></head><body><main class="panel">';
    echo '<h1>Diagnóstico de carga de guías</h1>';
    echo '<p>Los cambios se confirman únicamente si todas las filas terminan correctamente.</p>';
}

function mostrarDiagnosticoCarga($mensaje, $tipo = 'paso')
{
    global $modoDiagnosticoCarga;

    if (!$modoDiagnosticoCarga) {
        return;
    }

    $clase = in_array($tipo, ['paso', 'exito', 'error'], true) ? $tipo : 'paso';
    echo '<div class="paso ' . $clase . '"><span class="hora">'
        . htmlspecialchars(date('H:i:s'), ENT_QUOTES, 'UTF-8')
        . '</span>'
        . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8')
        . '</div>';

    if (ob_get_level() > 0) {
        @ob_flush();
    }
    flush();
}

if ($modoDiagnosticoCarga) {
    register_shutdown_function(function () {
        global $modoDiagnosticoCarga;

        if (!$modoDiagnosticoCarga) {
            return;
        }

        $ultimoError = error_get_last();
        $erroresFatales = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];

        if ($ultimoError && in_array($ultimoError['type'], $erroresFatales, true)) {
            mostrarDiagnosticoCarga(
                'ERROR FATAL: ' . $ultimoError['message']
                . ' | Archivo: ' . $ultimoError['file']
                . ' | Línea: ' . $ultimoError['line'],
                'error'
            );
        }

        echo '<a class="volver" href="Guias_CargarGuia.php">Regresar a cargar guías</a>';
        echo '</main></body></html>';

        if (ob_get_level() > 0) {
            @ob_end_flush();
        }
    });
}

mostrarDiagnosticoCarga('Paso 1: inició SubirGuiadeCarga.php.');

if ($modoDiagnosticoCarga && (!isset($_SESSION['Usuario']) || $_SESSION['Usuario'] === '')) {
    mostrarDiagnosticoCarga(
        'La sesión administrativa no está activa. Inicie sesión nuevamente antes de cargar el archivo.',
        'error'
    );
    exit;
}

require_once __DIR__ . '/session_guard.php';
mostrarDiagnosticoCarga('Paso 2: sesión administrativa validada.');

require_once '../LQS_EUQ/Auth.php';
mostrarDiagnosticoCarga('Paso 3: archivo de conexión cargado.');

const GUIAS_CSV_COLUMNAS = 23;
const GUIAS_CSV_TAMANO_MAXIMO = 10485760; // 10 MB

function regresarConAlertaCarga($titulo, $mensaje, $icono = 'error')
{
    global $modoDiagnosticoCarga;

    if ($modoDiagnosticoCarga) {
        mostrarDiagnosticoCarga(
            $titulo . ': ' . $mensaje,
            $icono === 'success' ? 'exito' : 'error'
        );
        exit;
    }

    $_SESSION['guias_alerta_validacion'] = [
        'title' => $titulo,
        'text' => $mensaje,
        'icon' => $icono,
    ];

    header('Location: Guias_CargarGuia.php', true, 303);
    exit;
}

function regresarConErrorCarga($mensaje)
{
    regresarConAlertaCarga('No se pudo cargar el archivo', $mensaje, 'error');
}

function convertirCsvAUtf8($texto)
{
    $texto = (string) $texto;

    if (preg_match('//u', $texto) === 1) {
        return $texto;
    }

    // Los CSV exportados por Excel suelen venir en Windows-1252.
    if (function_exists('mb_convert_encoding')) {
        return mb_convert_encoding($texto, 'UTF-8', 'Windows-1252');
    }

    if (function_exists('iconv')) {
        $convertido = iconv('Windows-1252', 'UTF-8//IGNORE', $texto);
        if ($convertido !== false) {
            return $convertido;
        }
    }

    return $texto;
}

function detectarSeparadorCsv($encabezado)
{
    $separadores = [';', ',', "\t"];

    foreach ($separadores as $separador) {
        if (count(str_getcsv($encabezado, $separador)) === GUIAS_CSV_COLUMNAS) {
            return $separador;
        }
    }

    return null;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mostrarDiagnosticoCarga('El método recibido no es POST.', 'error');
    header('Location: Guias_CargarGuia.php', true, 303);
    exit;
}

mostrarDiagnosticoCarga('Paso 4: solicitud POST recibida.');

if (!isset($_FILES['dataGuias']) || $_FILES['dataGuias']['error'] !== UPLOAD_ERR_OK) {
    regresarConErrorCarga(
        'No se recibió correctamente el archivo CSV. Selecciónelo e intente nuevamente.'
    );
}

$archivoSubido = $_FILES['dataGuias'];
$nombreArchivo = isset($archivoSubido['name']) ? (string) $archivoSubido['name'] : '';
$tamanoArchivo = isset($archivoSubido['size']) ? (int) $archivoSubido['size'] : 0;
$archivoTemporal = isset($archivoSubido['tmp_name']) ? (string) $archivoSubido['tmp_name'] : '';

mostrarDiagnosticoCarga(
    'Paso 5: archivo recibido: ' . $nombreArchivo . ' (' . $tamanoArchivo . ' bytes).'
);

if (strtolower((string) pathinfo($nombreArchivo, PATHINFO_EXTENSION)) !== 'csv') {
    regresarConErrorCarga('El archivo seleccionado debe tener extensión .csv.');
}

if ($tamanoArchivo <= 0 || $tamanoArchivo > GUIAS_CSV_TAMANO_MAXIMO) {
    regresarConErrorCarga('El archivo está vacío o supera el tamaño máximo permitido de 10 MB.');
}

$manejador = fopen($archivoTemporal, 'rb');
if ($manejador === false) {
    regresarConErrorCarga('No fue posible leer el archivo CSV.');
}

mostrarDiagnosticoCarga('Paso 6: archivo temporal abierto correctamente.');

$encabezadoTexto = fgets($manejador);
if ($encabezadoTexto === false) {
    fclose($manejador);
    regresarConErrorCarga('El archivo CSV no contiene un encabezado.');
}

$encabezadoTexto = convertirCsvAUtf8($encabezadoTexto);
$encabezadoTexto = preg_replace('/^\xEF\xBB\xBF/', '', $encabezadoTexto);
$separador = detectarSeparadorCsv($encabezadoTexto);

if ($separador === null) {
    fclose($manejador);
    regresarConErrorCarga(
        'No se reconoció el formato del CSV. Debe contener 23 columnas separadas por punto y coma, coma o tabulación.'
    );
}

$nombreSeparador = $separador === ';'
    ? 'punto y coma (;)'
    : ($separador === ',' ? 'coma (,)' : 'tabulación');
mostrarDiagnosticoCarga('Paso 7: separador detectado: ' . $nombreSeparador . '.');

$columnas = [
    'Planta', 'FechaPedido', 'FechaEngrega', 'CodigoEANUPC', 'PosicionSD',
    'Material', 'Descripcion', 'Cajas', 'UnidadMedida', 'Transporte',
    'Entrega', 'Destino', 'NombreDestino', 'Direccion', 'PesoNeto',
    'PesoBruto', 'LugarDestino', 'Agente', 'Transportista', 'Expedicion',
    'Canal', 'Pais', 'Incoterms'
];

$columnasSql = implode(', ', array_map(function ($columna) {
    return '`' . $columna . '`';
}, $columnas));
$marcadores = implode(', ', array_fill(0, count($columnas), '?'));

$numeroRegistro = 1;
$filasInsertadas = 0;
$transportes = [];

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new RuntimeException('No fue posible establecer conexión con la base de datos.');
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();
    mostrarDiagnosticoCarga('Paso 8: transacción iniciada.');

    // Evita mezclar el archivo nuevo con una precarga que todavía no se ha confirmado.
    $consultaPrecarga = $pdo->query(
        'SELECT Transporte FROM dbs9098416.Guia_PreCarga LIMIT 1 FOR UPDATE'
    );
    if ($consultaPrecarga->fetch(PDO::FETCH_ASSOC)) {
        throw new RuntimeException(
            'Ya existe una carga pendiente. Guárdela o elimínela antes de subir otro archivo.'
        );
    }

    mostrarDiagnosticoCarga('Paso 9: no existe otra precarga pendiente.');

    $insertar = $pdo->prepare(
        "INSERT INTO dbs9098416.Guia_PreCarga ($columnasSql) VALUES ($marcadores)"
    );

    while (($datos = fgetcsv($manejador, 0, $separador)) !== false) {
        $numeroRegistro++;

        if (count($datos) === 1 && trim((string) $datos[0]) === '') {
            continue;
        }

        if (count($datos) !== count($columnas)) {
            throw new RuntimeException(
                'La fila ' . $numeroRegistro . ' contiene ' . count($datos)
                . ' columnas; se esperaban ' . count($columnas) . '.'
            );
        }

        $datos = array_map(function ($valor) {
            return trim(convertirCsvAUtf8($valor));
        }, $datos);

        $material = $datos[5];
        $cajas = $datos[7];
        $transporte = $datos[9];
        $entrega = $datos[10];

        if ($material === '' || $cajas === '' || $transporte === '' || $entrega === '') {
            throw new RuntimeException(
                'La fila ' . $numeroRegistro
                . ' debe incluir Material, Cajas, Transporte y Entrega.'
            );
        }

        $insertar->execute($datos);
        if ($insertar->rowCount() !== 1) {
            throw new RuntimeException('No fue posible importar la fila ' . $numeroRegistro . '.');
        }

        $filasInsertadas++;
        $transportes[$transporte] = true;
        mostrarDiagnosticoCarga(
            'Paso 10: fila ' . $numeroRegistro . ' importada; transporte ' . $transporte . '.'
        );
    }

    fclose($manejador);
    $manejador = null;

    if ($filasInsertadas === 0) {
        throw new RuntimeException('El archivo no contiene registros para importar.');
    }

    $pdo->commit();
    mostrarDiagnosticoCarga(
        'Paso 11: COMMIT completado. Se guardaron ' . $filasInsertadas . ' filas.',
        'exito'
    );

    $listaTransportes = array_keys($transportes);
    sort($listaTransportes, SORT_NATURAL);
    regresarConAlertaCarga(
        'Archivo importado correctamente',
        'Se importaron ' . $filasInsertadas . ' filas correspondientes a '
            . count($listaTransportes) . ' transporte(s): '
            . implode(', ', $listaTransportes) . '.',
        'success'
    );
} catch (Throwable $error) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if (is_resource($manejador)) {
        fclose($manejador);
    }

    error_log('Error al importar guías desde CSV: ' . $error->getMessage());

    mostrarDiagnosticoCarga(
        'EXCEPCIÓN: ' . get_class($error) . ': ' . $error->getMessage()
        . ' | Archivo: ' . $error->getFile()
        . ' | Línea: ' . $error->getLine()
        . ' | Se ejecutó ROLLBACK cuando correspondía.',
        'error'
    );

    $mensaje = $error instanceof RuntimeException
        ? $error->getMessage()
        : 'Se encontraron errores en los datos. Ninguna fila del archivo fue importada.';

    regresarConErrorCarga($mensaje);
}
