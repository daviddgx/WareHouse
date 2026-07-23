<?php
require_once __DIR__ . '/session_guard.php';


ob_start();
include '../LQS_EUQ/Auth.php';
include '../LQS_EUQ/Connect.php';

// habilitar errores de mysqli como excepciones (opcional pero recomendado)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Crear conexión
$conexion = new mysqli($servername, $username, $password, $dbname);

if (!isset($_FILES['dataGuias']) || $_FILES['dataGuias']['error'] === UPLOAD_ERR_NO_FILE) {
    header('Location: Guias_CargarGuia.php');
    exit;
}

$archivotmp = $_FILES['dataGuias']['tmp_name'];
$lineas = file($archivotmp, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

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

    $datos = str_getcsv($linea, ','); // <-- separador CORRECTO

    // validar cantidad de columnas
    if (count($datos) !== count($columnas)) {
        // puedes loguear o mostrar un error más claro aquí
        continue;
    }

    $registros[] = array_combine($columnas, $datos);
}

if (empty($registros)) {
    echo "No se encontraron registros válidos en el archivo.";
    exit;
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
} catch (Exception $e) {
    echo "Se encontraron errores en los datos del archivo, el sistema encontró esto: " . $e->getMessage();
    exit;
}

ob_end_flush();
?>
