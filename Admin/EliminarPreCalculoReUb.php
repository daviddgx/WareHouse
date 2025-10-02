<?php
ob_start();
//require('Config_Guias.php');
include '../LQS_EUQ/Auth.php';
include '../LQS_EUQ/Connect.php';

// Create connection
$conexion = new mysqli($servername, $username, $password, $dbname);




$consulta = "delete from `reubicacionesmasivas` where Estatus = 'Pendiente';";

try {
    $resultado = $conexion->query($consulta);
    header("Location: ReubicarProductoCarril.php");
    exit;
} catch (Exception $e) {

    header('Location: ReubicarProductoCarril.php');
    exit;
}
ob_end_flush();
?>


