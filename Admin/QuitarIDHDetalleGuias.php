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
$Transporte = $_GET['Guia'];
$Entrega = $_GET['Entrega'];
$Material = $_GET['IDH'];

    $consulta = "Delete FROM dbs9098416.DetalleGuias where Transporte ='".$Transporte."' and Material ='".$Material."';";

    try {
        $resultado = $conexion->query($consulta);
        header("Location: DetalleGuias.php?Guia=".$Transporte."&Entrega=".$Entrega."");
        exit;
    } catch (Exception $e) {

        header('Location: Guias_CargarGuia.php');
        exit;
    }
}else{
    header('Location: Traking_Guias.php');
}

ob_end_flush();
?>


