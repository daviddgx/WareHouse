<?php
require_once __DIR__ . '/session_guard.php';


ob_start();
include '../LQS_EUQ/Auth.php';
include '../LQS_EUQ/Connect.php';

// habilitar errores de mysqli como excepciones (opcional pero recomendado)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function regresarConErrorCarga($mensaje)
{
    $_SESSION['guias_alerta_validacion'] = [
        'title' => 'No se pudo cargar el archivo',
        'text' => $mensaje,
        'icon' => 'error',
    ];

    header('Location: Guias_CargarGuia.php');
    exit;
}

function convertirCsvAUtf8($texto)
{
    // Si ya es UTF-8 válido, no se modifica.
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

// Crear conexión
$conexion = new mysqli($servername, $username, $password, $dbname);
$conexion->set_charset('utf8mb4');

if (!isset($_FILES['dataGuias']) || $_FILES['dataGuias']['error'] !== UPLOAD_ERR_OK) {
    regresarConErrorCarga('No se recibió correctamente el archivo CSV. Selecciónelo e intente nuevamente.');
}

$archivotmp = $_FILES['dataGuias']['tmp_name'];
$lineas = file($archivotmp, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

if ($lineas === false) {
    regresarConErrorCarga('No fue posible leer el archivo CSV. Verifique el archivo e intente nuevamente.');
}

// Columnas de la tabla
$columnas = [
    'Planta', 'FechaPedido', 'FechaEngrega', 'CodigoEANUPC', 'PosicionSD',
    'Material', 'Descripcion', 'Cajas', 'UnidadMedida', 'Transporte',
    'Entrega', 'Destino', 'NombreDestino', 'Direccion', 'PesoNeto',
    'PesoBruto', 'LugarDestino', 'Agente', 'Transportista', 'Expedicion',
    'Canal', 'Pais', 'Incoterms'
];

$registros = [];
foreach ($lineas as $i => $linea) {
    if ($i === 0) continue; // saltar encabezado

    $linea = convertirCsvAUtf8($linea);
    $datos = str_getcsv($linea, ','); // <-- separador CORRECTO

    // validar cantidad de columnas
    if (count($datos) !== count($columnas)) {
        // puedes loguear o mostrar un error más claro aquí
        continue;
    }

    $registros[] = array_combine($columnas, $datos);
}

if (empty($registros)) {
    regresarConErrorCarga('No se encontraron registros válidos. Verifique que el CSV tenga el encabezado y las 23 columnas esperadas.');
}

$columnasSql = implode(',', $columnas);

$values = [];
foreach ($registros as $registro) {
    $registroEscapado = array_map(function ($valor) use ($conexion) {
        return $conexion->real_escape_string($valor);
    }, $registro);

    $values[] = "('" . implode("','", $registroEscapado) . "')";
}

$registrosSql = implode(',', $values);

$consulta = "INSERT INTO dbs9098416.Guia_PreCarga ($columnasSql) VALUES $registrosSql";

try {
    $resultado = $conexion->query($consulta);
    header("Location: Guias_CargarGuia.php");
    exit;
} catch (Throwable $e) {
    error_log('Error al importar guías desde CSV: ' . $e->getMessage());

    $detalle = $e->getMessage();
    if (stripos($detalle, 'Incorrect string value') !== false) {
        $detalle = 'El archivo contiene uno o más caracteres que la columna indicada no admite. '
            . 'Revise la codificación y los caracteres especiales del CSV. Detalle: ' . $detalle;
    }

    regresarConErrorCarga('Se encontraron errores en los datos del archivo. ' . $detalle);
}

ob_end_flush();
?>
