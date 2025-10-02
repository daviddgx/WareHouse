<?php
ob_start();
//require('Config_Guias.php');
include '../LQS_EUQ/Auth.php';
include '../LQS_EUQ/Connect.php';

// Create connection
if (isset($_GET['Guia'])) {
    $conexion = new mysqli($servername, $username, $password, $dbname);
$Transporte = $_GET['Guia'];
;

    $consulta = "Delete FROM dbs9098416.asignacionesPL where Numero ='".$Transporte."';";

    try {
        $resultado = $conexion->query($consulta);
        header("Location: Print_CardexMasivo.php");
        exit;
    } catch (Exception $e) {

        header('Location: Print_CardexMasivo.php');
        exit;
    }
}else{
    header('Location: Print_CardexMasivo.php');
}

ob_end_flush();
?>


