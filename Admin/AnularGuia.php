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
if (isset($_GET['Guia'])) {
    $conexion = new mysqli($servername, $username, $password, $dbname);
    $conexion2 = new mysqli($servername, $username, $password, $dbname);
$Transporte = $_GET['Guia'];


    $consulta = "Delete FROM dbs9098416.Guias where Transporte ='".$Transporte."' ;";

    try {
        $conexion->query($consulta);

        $consulta2 = "Delete FROM dbs9098416.DetalleGuias where Transporte ='".$Transporte."' ;";

        $conexion2->query($consulta2);
        header('Location: Traking_Guias.php');
        exit;
    } catch (Exception $e) {

        echo "Error al eliminar: ".$e->getMessage();
        exit;
    }
}else{
    header('Location: Traking_Guias.php');
}

ob_end_flush();
?>


