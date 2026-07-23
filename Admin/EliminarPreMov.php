<?php
require_once __DIR__ . '/session_guard.php';

ob_start();
//require('Config_Guias.php');
include '../LQS_EUQ/Auth.php';
include '../LQS_EUQ/Connect.php';

// Create connection
$conexion = new mysqli($servername, $username, $password, $dbname);


// Eliminar el estatus de las ubicaciones que estan contempladas en la tabla

$consulta = "update `posiciones` set Estado = 'Libre' where Ubicacion in (SELECT UbicacionDestino FROM `reubicacionesmasivas` where Estatus = 'Pendiente');";


try {
    $resultado = $conexion->query($consulta);
    header("Location: ReubicarProductoCarril.php");

    $conexion2 = new mysqli($servername, $username, $password, $dbname);


// Eliminar el estatus de las ubicaciones que estan contempladas en la tabla

    $consulta2 = " Delete FROM `reubicacionesmasivas` where Estatus = 'Pendiente';";

    $resultado2 = $conexion->query($consulta2);

    exit;
} catch (Exception $e) {

    echo $e-> getMessage();
}
ob_end_flush();
?>


