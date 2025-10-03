<?php
session_start();
$currentDate = date('Y-m-d');

if (!isset($_SESSION['Usuario'], $_SESSION['UsuarioFecha']) || $_SESSION['Usuario'] === '' || $_SESSION['UsuarioFecha'] !== $currentDate) {
    header('Location: ../Innet/505.html');
    exit();
}
ob_start();
//require('Config_Guias.php');
include '../LQS_EUQ/Auth.php';
include '../LQS_EUQ/Connect.php';

// Create connection
$conexion = new mysqli($servername, $username, $password, $dbname);


if (!isset($_FILES['dataGuias']) || $_FILES['dataGuias']['error'] === UPLOAD_ERR_NO_FILE) {
    header('Location: Guias_CargarGuia.php');
    exit;
}

$tipo = $_FILES['dataGuias']['type'];
$tamanio = $_FILES['dataGuias']['size'];
$archivotmp = $_FILES['dataGuias']['tmp_name'];
$lineas = file($archivotmp, FILE_IGNORE_NEW_LINES);

$columnas = ['Planta', 'FechaPedido', 'FechaEngrega', 'CodigoEANUPC', 'PosicionSD', 'Material', 'Descripcion', 'Cajas', 'UnidadMedida', 'Transporte', 'Entrega', 'Destino', 'NombreDestino', 'Direccion', 'PesoNeto', 'PesoBruto', 'LugarDestino', 'Agente', 'Transportista', 'Expedicion', 'Canal', 'Pais', 'Incoterms'];
$registros = array_map(function ($linea) use ($columnas) {
    $datos = explode(";", $linea);
    return array_combine($columnas, $datos);
}, array_slice($lineas, 1));

$columnas = utf8_encode(implode(',', $columnas));
$registros = utf8_encode(implode(',', array_map(function ($registro) {
    return "('" . implode("','", $registro) . "')";
}, $registros)));

$consulta = "INSERT INTO dbs9098416.Guia_PreCarga ($columnas) VALUES $registros";

try {
    $resultado = $conexion->query($consulta);
    header("Location: Guias_CargarGuia.php");
    exit;
} catch (Exception $e) {

   echo "Se encontraron errores en los datos del archivo, el sistema encontro esto: ".$e->getMessage();
    exit;
}
ob_end_flush();
?>


