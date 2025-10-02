<?php
ob_start();
//require('Config_Guias.php');
include '../LQS_EUQ/Auth.php';
include '../LQS_EUQ/Connect.php';

// Create connection
$conexion = new mysqli($servername, $username, $password, $dbname);




$consulta = "TRUNCATE `dbs9098416`.`Guia_PreCarga`;";

try {
    $resultado = $conexion->query($consulta);
    header("Location: Guias_CargarGuia.php");
    exit;
} catch (Exception $e) {

    header('Location: Guias_CargarGuia.php');
    exit;
}
ob_end_flush();
?>


